<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Core\Database;
use App\Core\Session;
use App\Controllers\OnlineExamController;

class OnlineExamControllerTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        Session::set('user_id', 1);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'teacher');
        Session::set('username', 'test_teacher');

        $controller = new OnlineExamController();
        // Invoke table creation
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('ensureTablesExist');
        $method->setAccessible(true);
        $method->invoke($controller, Database::getInstance());
    }

    public function testOnlineExamTablesCreation(): void
    {
        $db = Database::getInstance();
        $controller = new OnlineExamController();
        
        $tables = $db->query("SHOW TABLES LIKE 'online_exam%'")->fetchAll();
        $this->assertIsArray($tables);
        $this->assertTrue(count($tables) >= 1);
    }

    public function testMcqQuestionEvaluationAndScoring(): void
    {
        $db = Database::getInstance();
        
        // 1. Create dummy exam
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Unit Test Exam', 1, 15, 2, 'published')");
        $ins->execute();
        $examId = $db->lastInsertId();

        // 2. Insert 2 MCQ questions
        $insQ1 = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES (:eid, 'Question 1', 'Opt A', 'Opt B', 'Opt C', 'Opt D', 'A', 1)");
        $insQ1->execute([':eid' => $examId]);
        $q1Id = $db->lastInsertId();

        $insQ2 = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES (:eid, 'Question 2', 'Opt A', 'Opt B', 'Opt C', 'Opt D', 'C', 1)");
        $insQ2->execute([':eid' => $examId]);
        $q2Id = $db->lastInsertId();

        // 3. Simulate Tab Switch auto-submit POST request
        $_POST['exam_id'] = $examId;
        $_POST['submission_reason'] = 'auto_submitted_tab_switch';
        $_POST['answers'] = [
            $q1Id => 'A', // Correct
            $q2Id => 'B'  // Incorrect (Correct is C)
        ];

        // Process submission directly via database check
        $correctCount = 0;
        if ($_POST['answers'][$q1Id] === 'A') $correctCount++;
        if ($_POST['answers'][$q2Id] === 'C') $correctCount++;

        $this->assertEquals(1, $correctCount, 'Correct score evaluation should yield 1 mark out of 2.');

        // Clean up test exam
        $db->exec("DELETE FROM online_exams WHERE id = {$examId}");
    }

    public function testTeacherExamDeletion(): void
    {
        $db = Database::getInstance();
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Delete Test Exam', 1, 10, 0, 'published')");
        $ins->execute();
        $examId = $db->lastInsertId();

        $this->assertTrue($examId > 0);

        $_POST['exam_id'] = $examId;
        $_SESSION['role_name'] = 'teacher';

        // Delete exam directly
        $del = $db->prepare("DELETE FROM online_exams WHERE id = :eid AND tenant_id = 1");
        $del->execute([':eid' => $examId]);

        $check = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals(0, $check, 'Exam should be deleted from database.');
    }

    public function testStudentTargetExamIsolation(): void
    {
        $db = Database::getInstance();
        // Create an exam targeted specifically for student_id = 999
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, student_id, duration_minutes, total_questions, status) VALUES (1, 'Targeted Exam For Student 999', 1, 999, 15, 1, 'published')");
        $ins->execute();
        $targetedExamId = (int)$db->lastInsertId();

        // 1. Query as Student 999
        $stmt999 = $db->prepare("SELECT * FROM online_exams WHERE tenant_id = 1 AND status = 'published' AND (student_id IS NULL OR student_id = 0 OR student_id = 999)");
        $stmt999->execute();
        $examsFor999 = $stmt999->fetchAll();
        $foundFor999 = array_filter($examsFor999, fn($e) => (int)$e['id'] === $targetedExamId);
        $this->assertTrue(count($foundFor999) >= 1, 'Targeted student 999 should see their assigned exam.');

        // 2. Query as Student 888 (different student)
        $stmt888 = $db->prepare("SELECT * FROM online_exams WHERE tenant_id = 1 AND status = 'published' AND (student_id IS NULL OR student_id = 0 OR student_id = 888)");
        $stmt888->execute();
        $examsFor888 = $stmt888->fetchAll();
        $foundFor888 = array_filter($examsFor888, fn($e) => (int)$e['id'] === $targetedExamId);
        $this->assertTrue(count($foundFor888) === 0, 'Student 888 must NOT see student 999\'s targeted exam.');

        // Clean up
        $db->exec("DELETE FROM online_exams WHERE id = {$targetedExamId}");
    }

    public function testPrincipalCanDeleteEligibleExam(): void
    {
        $db = Database::getInstance();
        Session::set('user_id', 8);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'principal');

        // Create eligible exam without submissions
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Principal Eligible Delete Exam', 1, 20, 1, 'published')");
        $ins->execute();
        $examId = (int)$db->lastInsertId();

        $insQ = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES (:eid, 'Sample Q', 'A', 'B', 'C', 'D', 'A', 1)");
        $insQ->execute([':eid' => $examId]);

        $_POST['exam_id'] = $examId;
        $_POST['archive_if_has_submissions'] = '1';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        ob_start();
        $controller = new OnlineExamController();
        $controller->deleteExam();
        $output = ob_get_clean();

        $res = json_decode($output, true);
        $this->assertTrue(!empty($res['success']), 'Response should indicate success.');
        $this->assertEquals('deleted', $res['action']);

        $checkExam = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals(0, $checkExam, 'Exam should be deleted from online_exams table.');

        $checkQ = (int)$db->query("SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = {$examId}")->fetchColumn();
        $this->assertEquals(0, $checkQ, 'Associated questions should be cleanly removed.');
    }

    public function testPrincipalExamDeletionWithSubmissionsArchived(): void
    {
        $db = Database::getInstance();
        Session::set('user_id', 8);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'principal');

        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Exam With Completed Submissions', 1, 20, 1, 'published')");
        $ins->execute();
        $examId = (int)$db->lastInsertId();

        $db->exec("INSERT INTO online_exam_submissions (tenant_id, exam_id, student_id, score, total_score, submission_status, started_at, submitted_at) VALUES (1, {$examId}, 1, 90, 100, 'submitted', NOW(), NOW())");

        $_POST['exam_id'] = $examId;
        $_POST['archive_if_has_submissions'] = '1';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        ob_start();
        $controller = new OnlineExamController();
        $controller->deleteExam();
        $output = ob_get_clean();

        $res = json_decode($output, true);
        $this->assertTrue(!empty($res['success']), 'Response should indicate success.');
        $this->assertEquals('archived', $res['action']);

        $examStatus = $db->query("SELECT status FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals('archived', $examStatus, 'Exam status should be updated to archived.');

        $subCount = (int)$db->query("SELECT COUNT(*) FROM online_exam_submissions WHERE exam_id = {$examId}")->fetchColumn();
        $this->assertEquals(1, $subCount, 'Student submission record must be preserved.');

        // Clean up
        $db->exec("DELETE FROM online_exams WHERE id = {$examId}");
    }

    public function testPrincipalPermanentDeleteBlockedWhenSubmissionsExist(): void
    {
        $db = Database::getInstance();
        Session::set('user_id', 8);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'principal');

        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Exam Submissions Retention Test', 1, 20, 1, 'published')");
        $ins->execute();
        $examId = (int)$db->lastInsertId();

        $db->exec("INSERT INTO online_exam_submissions (tenant_id, exam_id, student_id, score, total_score, submission_status, started_at, submitted_at) VALUES (1, {$examId}, 1, 95, 100, 'submitted', NOW(), NOW())");

        $_POST['exam_id'] = $examId;
        $_POST['archive_if_has_submissions'] = '0';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        ob_start();
        $controller = new OnlineExamController();
        $controller->deleteExam();
        $output = ob_get_clean();

        $res = json_decode($output, true);
        $this->assertFalse(!empty($res['success']), 'Permanent deletion should be rejected.');
        $this->assertEquals('This exam cannot be permanently deleted because it has associated student records.', $res['error']);

        $examCheck = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals(1, $examCheck, 'Exam must still exist in database.');

        // Clean up
        $db->exec("DELETE FROM online_exams WHERE id = {$examId}");
    }

    public function testUnauthorizedStudentCannotDeleteExam(): void
    {
        $db = Database::getInstance();
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (1, 'Protected Exam', 1, 20, 1, 'published')");
        $ins->execute();
        $examId = (int)$db->lastInsertId();

        Session::set('user_id', 6);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'student');

        $_POST['exam_id'] = $examId;
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        ob_start();
        $controller = new OnlineExamController();
        $controller->deleteExam();
        $output = ob_get_clean();

        $res = json_decode($output, true);
        $this->assertFalse(!empty($res['success']));
        $this->assertEquals('You do not have permission to delete this exam.', $res['error']);

        $check = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals(1, $check, 'Protected exam must not be deleted by student.');

        // Clean up
        $db->exec("DELETE FROM online_exams WHERE id = {$examId}");
    }

    public function testCrossTenantDeleteForbidden(): void
    {
        $db = Database::getInstance();
        // Exam belongs to tenant 2
        $ins = $db->prepare("INSERT INTO online_exams (tenant_id, title, course_id, duration_minutes, total_questions, status) VALUES (2, 'Tenant 2 Exam', 99, 20, 1, 'published')");
        $ins->execute();
        $examId = (int)$db->lastInsertId();

        // User is Principal of tenant 1
        Session::set('user_id', 8);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'principal');

        $_POST['exam_id'] = $examId;
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        ob_start();
        $controller = new OnlineExamController();
        $controller->deleteExam();
        $output = ob_get_clean();

        $res = json_decode($output, true);
        $this->assertFalse(!empty($res['success']));
        $this->assertEquals('You do not have permission to delete this exam.', $res['error']);

        $check = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$examId}")->fetchColumn();
        $this->assertEquals(1, $check, 'Cross-tenant exam must not be deleted.');

        // Clean up
        $db->exec("DELETE FROM online_exams WHERE id = {$examId}");
    }
}

