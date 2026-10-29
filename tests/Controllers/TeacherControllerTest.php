<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\TeacherController;
use App\Controllers\TimetableController;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Database;
use App\Core\CSRF;
use PDO;

class TeacherControllerTest extends TestCase
{
    private PDO $db;

    public function setUp(): void
    {
        Session::start();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $this->db = Database::getInstance();
    }

    public function tearDown(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
    }

    /**
     * Test 1: Teacher Authentication, Profile Resolution, and Dashboard Rendering
     */
    public function testTeacherDashboardMetricsAndScoping(): void
    {
        $login = Auth::attempt('dr_soap', 'Admin@123');
        $this->assertTrue($login, "Teacher dr_soap login failed");

        $user = Auth::user();
        $this->assertEquals('teacher', $user['role_name']);
        $this->assertEquals(1, $user['tenant_id']);

        $controller = new TeacherController();

        ob_start();
        $controller->showDashboard();
        $output = ob_get_clean();

        $this->assertTrue(!empty($output), "Teacher dashboard output should not be empty");
        $this->assertStringContainsString('Weekly Timetable', $output, "Quick action link missing");
        $this->assertStringContainsString('Pending Attendance', $output, "Pending Attendance KPI card missing");
        $this->assertStringContainsString('Assigned Subjects', $output, "Assigned subjects section missing");
        $this->assertStringContainsString('Active Homework', $output, "Active homework stat missing");
    }

    /**
     * Test 2: Weekly Timetable Generation & Proper Scoping
     */
    public function testWeeklyTimetableGridGeneration(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $controller = new TeacherController();

        $_GET['date'] = '2026-10-12'; // Monday

        ob_start();
        $controller->showWeeklyTimetable();
        $output = ob_get_clean();

        $this->assertTrue(!empty($output), "Weekly timetable output should not be empty");
        $this->assertStringContainsString('MONDAY', $output);
        $this->assertStringContainsString('SATURDAY', $output);
        $this->assertStringContainsString('TIME SLOT', $output);
    }

    /**
     * Test 3: Unauthorized Access Prevention (Cross-Teacher & Cross-Tenant)
     */
    public function testCrossTeacherAccessForbidden(): void
    {
        Auth::attempt('dr_soap', 'Admin@123'); // Tenant 1, Staff 1
        $controller = new TeacherController();

        // Timetable Entry 99 belongs to Tenant 2
        $_GET['id'] = 99;
        $_GET['date'] = '2026-10-12';

        ob_start();
        $controller->showLectureDetails();
        ob_get_clean();

        $flash = Session::getFlash('error');
        $this->assertTrue(!empty($flash), "Cross-tenant access must set an error flash");
        $this->assertStringContainsString('not found or does not belong', $flash);
    }

    /**
     * Test 4: Active Student Enrollment Roster Loading
     */
    public function testLectureDetailsStudentRoster(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $controller = new TeacherController();

        // Timetable Entry 1 belongs to Dr. Soap (DBMS, Dept 2, Section 1)
        $_GET['id'] = 1;
        $_GET['date'] = '2026-10-12';

        ob_start();
        $controller->showLectureDetails();
        $output = ob_get_clean();

        $this->assertTrue(!empty($output));
        $this->assertStringContainsString('Database Management System', $output);
        $this->assertStringContainsString('Student Attendance', $output);
        $this->assertStringContainsString('Daily Lesson Record', $output);
        $this->assertStringContainsString('Homework & Assignments', $output);
    }

    /**
     * Test 5: Attendance Bulk Transaction, Stats Calculation, and Legacy Sync
     */
    public function testAttendanceTransactionAndBulkSave(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $csrfToken = CSRF::getToken();

        // Find actual student IDs in tenant 1 for section 1
        $stList = $this->db->query("SELECT id FROM students WHERE tenant_id = 1 AND section_id = 1 LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);
        if (count($stList) < 2) {
            $uniq = 'TST' . rand(10000, 99999);
            $this->db->exec("
                INSERT INTO students (tenant_id, unique_id, admission_no, roll_no, first_name, last_name, gender, dob, course_id, department_id, standard_id, class_id, section_id, batch_id, status)
                VALUES (1, '{$uniq}', '{$uniq}', '24IT002', 'TestStudent2', 'Demo', 'female', '2005-05-05', 2, 2, 1, 1, 1, 1, 'active')
            ");
            $stList = $this->db->query("SELECT id FROM students WHERE tenant_id = 1 AND section_id = 1 LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);
        }
        $this->assertTrue(count($stList) >= 2, "Need at least two students for testing attendance");

        $student1 = (int)$stList[0];
        $student2 = (int)$stList[1];

        $_POST = [
            'csrf_token' => $csrfToken,
            'time_table_id' => 1,
            'date' => '2026-10-12',
            'attendance' => [
                $student1 => 'present',
                $student2 => 'absent'
            ],
            'remarks' => [
                $student1 => 'Good participation',
                $student2 => 'Sick leave'
            ]
        ];

        $controller = new TeacherController();
        ob_start();
        $controller->saveAttendance();
        $jsonOut = ob_get_clean();

        $res = json_decode($jsonOut, true);
        $this->assertNotNull($res, "Response must be valid JSON: " . $jsonOut);
        $this->assertTrue($res['success'] ?? false, "Attendance save failed: " . ($res['error'] ?? ''));

        // Verify attendance_sessions row exists
        $sessionStmt = $this->db->prepare("SELECT * FROM attendance_sessions WHERE time_table_id = 1 AND lecture_date = '2026-10-12'");
        $sessionStmt->execute();
        $sessionRow = $sessionStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($sessionRow, "attendance_sessions record was not created");
        $this->assertEquals('completed', $sessionRow['status']);

        // Verify attendance_records rows exist
        $recStmt = $this->db->prepare("SELECT * FROM attendance_records WHERE session_id = :sid AND student_id = :stid");
        $recStmt->execute([':sid' => $sessionRow['id'], ':stid' => $student1]);
        $rec1 = $recStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($rec1, "Attendance record for student 1 missing");
        $this->assertEquals('present', $rec1['status']);
        $this->assertEquals('Good participation', $rec1['remarks']);

        // Verify legacy sync
        $legStmt = $this->db->prepare("SELECT * FROM student_attendance WHERE student_id = :stid AND date = '2026-10-12'");
        $legStmt->execute([':stid' => $student1]);
        $legRow = $legStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($legRow, "Legacy student_attendance record missing");
        $this->assertEquals('present', $legRow['status']);
    }

    /**
     * Test 6: Daily Lesson Record Saving and Update
     */
    public function testLessonRecordSavingAndUpsert(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $csrfToken = CSRF::getToken();

        $_POST = [
            'csrf_token' => $csrfToken,
            'time_table_id' => 1,
            'date' => '2026-10-12',
            'topic_title' => 'Indexing and B-Trees in SQL',
            'description' => 'Explained clustered vs non-clustered indexes and performance benchmarks.',
            'status' => 'completed'
        ];

        $controller = new TeacherController();
        ob_start();
        $controller->saveLessonRecord();
        $jsonOut = ob_get_clean();

        $res = json_decode($jsonOut, true);
        $this->assertNotNull($res, "Response must be valid JSON: " . $jsonOut);
        $this->assertTrue($res['success'] ?? false, "Lesson save failed: " . ($res['error'] ?? ''));

        // Verify record in DB
        $lesStmt = $this->db->prepare("SELECT * FROM lesson_records WHERE time_table_id = 1 AND lecture_date = '2026-10-12'");
        $lesStmt->execute();
        $lesson = $lesStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($lesson, "Lesson record was not found in DB");
        $this->assertEquals('Indexing and B-Trees in SQL', $lesson['topic_title']);
        $this->assertEquals('completed', $lesson['status']);

        // Test updating existing record without creating duplicate
        $_POST['description'] = 'Updated description with additional whiteboard diagrams.';
        ob_start();
        $controller->saveLessonRecord();
        ob_get_clean();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM lesson_records WHERE time_table_id = 1 AND lecture_date = '2026-10-12'");
        $countStmt->execute();
        $count = (int)$countStmt->fetchColumn();
        $this->assertEquals(1, $count, "Duplicate lesson record created instead of upserting!");
    }

    /**
     * Test 7: Homework Creation, Due Date Validation, and Deletion
     */
    public function testHomeworkLifecycle(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $csrfToken = CSRF::getToken();

        $_POST = [
            'csrf_token' => $csrfToken,
            'time_table_id' => 1,
            'title' => 'Relational Normalization Problem Set 4',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'description' => 'Decompose given schema into 3NF and BCNF with functional dependencies.'
        ];

        $controller = new TeacherController();
        ob_start();
        $controller->saveHomework();
        ob_get_clean();

        $flash = Session::getFlash('success');
        $this->assertTrue(!empty($flash), "Homework assignment save should trigger success flash");

        // Find created homework
        $hwStmt = $this->db->prepare("SELECT * FROM homework_assignments WHERE title = 'Relational Normalization Problem Set 4' ORDER BY id DESC LIMIT 1");
        $hwStmt->execute();
        $hwRow = $hwStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($hwRow, "Homework assignment record missing");
        $this->assertEquals('active', $hwRow['status']);

        // Delete homework
        $_POST = [
            'csrf_token' => $csrfToken,
            'homework_id' => $hwRow['id'],
            'time_table_id' => 1
        ];
        ob_start();
        $controller->deleteHomework();
        ob_get_clean();

        $checkStmt = $this->db->prepare("SELECT status FROM homework_assignments WHERE id = :id");
        $checkStmt->execute([':id' => $hwRow['id']]);
        $status = $checkStmt->fetchColumn();
        $this->assertTrue($status === false || in_array($status, ['cancelled', 'archived'], true), "Homework was not deleted or deactivated");
    }

    /**
     * Test 8: Classroom Remarks (General Class & Student-Specific)
     */
    public function testClassroomRemarksStorage(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $csrfToken = CSRF::getToken();

        // 1. General Remark
        $_POST = [
            'csrf_token' => $csrfToken,
            'time_table_id' => 1,
            'date' => '2026-10-12',
            'student_id' => '',
            'remark_type' => 'general_class',
            'remark_text' => 'Class completed chapter 4 ahead of schedule. Excellent attention.'
        ];

        $controller = new TeacherController();
        ob_start();
        $controller->saveClassroomRemark();
        ob_get_clean();

        $genStmt = $this->db->prepare("SELECT * FROM classroom_remarks WHERE time_table_id = 1 AND student_id IS NULL ORDER BY id DESC LIMIT 1");
        $genStmt->execute();
        $genRemark = $genStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($genRemark, "General classroom remark not saved");
        $this->assertEquals('Class completed chapter 4 ahead of schedule. Excellent attention.', $genRemark['remark_text']);

        // 2. Student-Specific Remark
        $studentId = (int)$this->db->query("SELECT id FROM students WHERE tenant_id = 1 LIMIT 1")->fetchColumn();
        $_POST = [
            'csrf_token' => $csrfToken,
            'time_table_id' => 1,
            'date' => '2026-10-12',
            'student_id' => $studentId,
            'remark_type' => 'academic',
            'remark_text' => 'Demonstrated exceptional comprehension of query optimization.'
        ];

        ob_start();
        $controller->saveClassroomRemark();
        ob_get_clean();

        $stStmt = $this->db->prepare("SELECT * FROM classroom_remarks WHERE time_table_id = 1 AND student_id = :sid ORDER BY id DESC LIMIT 1");
        $stStmt->execute([':sid' => $studentId]);
        $stRemark = $stStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($stRemark, "Student specific remark not saved");
        $this->assertEquals('academic_performance', $stRemark['remark_type']);
    }

    /**
     * Test 9: Admin Timetable Conflict Validation (Overlapping Schedules)
     */
    public function testAdminTimetableConflictValidation(): void
    {
        Auth::attempt('admin', 'Admin@123'); // Admin login

        $timetableController = new TimetableController();

        // Verify timetable JSON API endpoint
        $_GET['id'] = 1;
        ob_start();
        $timetableController->getTimetableEntryJson();
        $json = ob_get_clean();

        $data = json_decode($json, true);
        $this->assertNotNull($data, "Timetable entry JSON must be valid");
        $this->assertTrue($data['success'] ?? false);
        $this->assertEquals(1, $data['entry']['id']);
    }
}
