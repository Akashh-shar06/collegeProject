<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\AcademicController;
use App\Core\Database;
use App\Core\Session;
use App\Core\Auth;
use App\Core\CSRF;
use PDO;

class AcademicControllerTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        $db = Database::getInstance();
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'admin'");
        Auth::attempt('admin', 'Admin@123');
    }

    public function testCourseCreationAndDuplicateCodeRejection(): void
    {
        $db = Database::getInstance();
        $code = 'TEST_CS_' . rand(1000, 9999);
        $name = 'B.Sc. Test CS ' . rand(1000, 9999);

        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = $name;
        $_POST['code'] = $code;
        $_POST['duration_years'] = 3;
        $_POST['department_id'] = 1;
        $_POST['description'] = 'Automated Test Course';
        $_POST['status'] = 'active';

        $controller = new AcademicController();
        $controller->storeCourse();

        // Verify inserted course in DB
        $cStmt = $db->prepare("SELECT * FROM courses WHERE code = :code LIMIT 1");
        $cStmt->execute([':code' => $code]);
        $course = $cStmt->fetch();

        $this->assertTrue(!empty($course), "Course with code {$code} should exist in DB.");
        $this->assertEquals($name, $course['name']);

        // Verify duplicate course code rejection
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = 'Different Name ' . rand(1000, 9999);
        $_POST['code'] = $code; // duplicate code
        $controller->storeCourse();
        $this->assertEquals("Course Code '{$code}' is already in use by another course.", Session::getFlash('error'));

        // Clean up test course
        if (!empty($course)) {
            $db->exec("DELETE FROM academic_levels WHERE course_id = {$course['id']}");
            $db->exec("DELETE FROM courses WHERE id = {$course['id']}");
        }
    }

    public function testDepartmentCreationWithParentCourse(): void
    {
        $db = Database::getInstance();
        $code = 'DEPT_' . rand(1000, 9999);
        $name = 'Department of Test Science ' . rand(1000, 9999);

        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = $name;
        $_POST['code'] = $code;
        $_POST['course_id'] = 1;
        $_POST['description'] = 'Test Dept';
        $_POST['status'] = 'active';

        $controller = new AcademicController();
        $controller->storeDepartment();

        $dStmt = $db->prepare("SELECT * FROM departments WHERE code = :code LIMIT 1");
        $dStmt->execute([':code' => $code]);
        $dept = $dStmt->fetch();

        $this->assertTrue(!empty($dept), "Department with code {$code} should be created.");
        $this->assertEquals($name, $dept['name']);

        // Verify duplicate department code rejection
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = 'Different Dept Name ' . rand(1000, 9999);
        $_POST['code'] = $code; // duplicate code
        $controller->storeDepartment();
        $this->assertEquals("Department Code '{$code}' is already registered.", Session::getFlash('error'));

        // Clean up test department
        if (!empty($dept)) {
            $db->exec("DELETE FROM departments WHERE id = {$dept['id']}");
        }
    }

    public function testSubjectCreationAndOffering(): void
    {
        $db = Database::getInstance();
        $code = 'SUB_' . rand(1000, 9999);

        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = 'Advanced Python Automation';
        $_POST['code'] = $code;
        $_POST['course_id'] = 1;
        $_POST['department_id'] = 1;
        $_POST['academic_level_id'] = 1;
        $_POST['semester_id'] = 1;
        $_POST['type'] = 'practical';
        $_POST['credits'] = 4;
        $_POST['max_marks'] = 100;
        $_POST['passing_marks'] = 40;
        $_POST['status'] = 'active';

        $controller = new AcademicController();
        $controller->storeSubject();

        $subStmt = $db->prepare("SELECT * FROM subjects WHERE code = :code LIMIT 1");
        $subStmt->execute([':code' => $code]);
        $subject = $subStmt->fetch();

        $this->assertTrue(!empty($subject), "Subject {$code} should be created.");
        $this->assertEquals(4, (int)$subject['credits']);

        // Create offering
        $subId = (int)$subject['id'];
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['subject_id'] = $subId;
        $_POST['course_id'] = 1;
        $_POST['department_id'] = 1;
        $_POST['academic_level_id'] = 1;
        $_POST['semester_id'] = 1;

        $controller->storeSubjectOffering();

        $offStmt = $db->prepare("SELECT * FROM subject_offerings WHERE subject_id = :sid LIMIT 1");
        $offStmt->execute([':sid' => $subId]);
        $offering = $offStmt->fetch();

        $this->assertTrue(!empty($offering), "Subject offering should be created.");

        // Clean up test subject and offering
        if (!empty($subId)) {
            $db->exec("DELETE FROM subject_offerings WHERE subject_id = {$subId}");
            $db->exec("DELETE FROM subjects WHERE id = {$subId}");
        }
    }

    public function testStudentBulkPromotionTransactionAndHistoryLogging(): void
    {
        $db = Database::getInstance();

        // Create test student
        $batchId = (int)$db->query("SELECT id FROM batches LIMIT 1")->fetchColumn() ?: 1;
        $uniq = 'TST' . rand(10000, 99999);
        $stIns = $db->prepare("
            INSERT INTO students (tenant_id, unique_id, admission_no, roll_no, first_name, last_name, gender, dob, course_id, department_id, standard_id, batch_id, status)
            VALUES (1, :u1, :u2, 'R101', 'TestStudent', 'Promote', 'male', '2005-05-05', 1, 1, 1, :bid, 'active')
        ");
        $stIns->execute([':u1' => $uniq, ':u2' => $uniq, ':bid' => $batchId]);
        $studentId = (int)$db->lastInsertId();

        // Create initial active enrollment
        $enrIns = $db->prepare("
            INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, academic_level_id, status)
            VALUES (1, :sid, '2026-2027', 1, 1, 1, 'active')
        ");
        $enrIns->execute([':sid' => $studentId]);
        $fromEnrId = (int)$db->lastInsertId();

        // Execute bulk promotion
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['student_ids'] = [$studentId];
        $_POST['source_session'] = '2026-2027';
        $_POST['target_session'] = '2027-2028';
        $_POST['target_academic_level_id'] = 2;
        $_POST['remarks'] = 'Automated Test Promotion to Second Year';

        $controller = new AcademicController();
        $controller->processBulkPromotion();

        // Verify old enrollment is updated to promoted
        $oldEnr = $db->query("SELECT status FROM student_enrollments WHERE id = {$fromEnrId}")->fetch();
        $this->assertEquals('promoted', $oldEnr['status'], "Old enrollment status should be updated to 'promoted'.");

        // Verify new active enrollment exists
        $newEnr = $db->query("SELECT * FROM student_enrollments WHERE student_id = {$studentId} AND status = 'active'")->fetch();
        $this->assertTrue(!empty($newEnr), "New active enrollment for target session should be created.");
        $this->assertEquals('2027-2028', $newEnr['academic_year']);
        $this->assertEquals(2, (int)$newEnr['academic_level_id']);

        // Verify promotion history entry
        $promoLog = $db->query("SELECT * FROM student_promotions WHERE student_id = {$studentId} ORDER BY id DESC LIMIT 1")->fetch();
        $this->assertTrue(!empty($promoLog), "Promotion audit record should be logged.");
        $this->assertEquals('2026-2027', $promoLog['from_academic_session']);
        $this->assertEquals('2027-2028', $promoLog['to_academic_session']);
        $this->assertEquals('success', $promoLog['status']);

        // Clean up test student, enrollments, and promotions
        $db->exec("DELETE FROM student_promotions WHERE student_id = {$studentId}");
        $db->exec("DELETE FROM student_enrollments WHERE student_id = {$studentId}");
        $db->exec("DELETE FROM students WHERE id = {$studentId}");
    }

    public function testSchoolAdminCanAddDepartmentsCoursesYearsAndStudentsHierarchy(): void
    {
        $db = Database::getInstance();
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'dsjc'");
        
        $loginSuccess = Auth::attempt('dsjc', 'Admin@123');
        $this->assertTrue($loginSuccess, "School Admin 'dsjc' should log in successfully.");
        
        $currentUser = Auth::user();
        $this->assertEquals('school_admin', $currentUser['role_name']);
        $this->assertEquals(24, (int)$currentUser['tenant_id']);

        // 1. Verify 2 Departments for School Admin
        $depts = $db->query("SELECT * FROM departments WHERE tenant_id = 24 AND status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->assertGreaterThanOrEqual(2, count($depts), "School Admin should have at least 2 active departments.");
        $deptNames = array_column($depts, 'name');
        $this->assertTrue(in_array('Science Department', $deptNames), "Science Department must exist.");
        $this->assertTrue(in_array('Commerce Department', $deptNames), "Commerce Department must exist.");

        // 2. Verify Courses inside Departments
        $sciDept = null;
        $comDept = null;
        foreach ($depts as $d) {
            if ($d['name'] === 'Science Department') $sciDept = $d;
            if ($d['name'] === 'Commerce Department') $comDept = $d;
        }
        $this->assertNotNull($sciDept);
        $this->assertNotNull($comDept);

        $sciCourses = $db->query("SELECT * FROM courses WHERE tenant_id = 24 AND department_id = {$sciDept['id']} AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        $this->assertGreaterThanOrEqual(2, count($sciCourses), "Science Department must have multiple courses.");

        $comCourses = $db->query("SELECT * FROM courses WHERE tenant_id = 24 AND department_id = {$comDept['id']} AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        $this->assertGreaterThanOrEqual(2, count($comCourses), "Commerce Department must have multiple courses.");

        // 3. Test Course Creation Auto-Provisions 3 Years (FY, SY, TY)
        $newCourseCode = 'BSC_AUTO_' . rand(100, 999);
        $newCourseName = 'B.Sc. Automation ' . rand(100, 999);
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['id'] = 0;
        $_POST['name'] = $newCourseName;
        $_POST['code'] = $newCourseCode;
        $_POST['duration_years'] = 3;
        $_POST['department_id'] = $sciDept['id'];
        $_POST['description'] = 'Auto provisioned 3-year course';
        $_POST['status'] = 'active';

        $controller = new AcademicController();
        $controller->storeCourse();

        $cRow = $db->query("SELECT * FROM courses WHERE code = '{$newCourseCode}' AND tenant_id = 24 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->assertTrue(!empty($cRow), "New course should be created under Science Department.");

        // Verify FY, SY, TY levels auto-provisioned
        $levels = $db->query("SELECT * FROM academic_levels WHERE course_id = {$cRow['id']} ORDER BY level_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(3, $levels, "New course must auto-provision exactly 3 Years (FY, SY, TY).");
        $this->assertEquals('First Year (FY)', $levels[0]['name']);
        $this->assertEquals(1, (int)$levels[0]['level_order']);
        $this->assertEquals('Second Year (SY)', $levels[1]['name']);
        $this->assertEquals(2, (int)$levels[1]['level_order']);
        $this->assertEquals('Third Year (TY)', $levels[2]['name']);
        $this->assertEquals(3, (int)$levels[2]['level_order']);

        // 4. Test Enrolling Student in FY, SY, TY via quickEnrollStudent
        $testRoll = 'TEST-FY-' . rand(100, 999);
        $_POST['csrf_token'] = CSRF::getToken();
        $_POST['department_id'] = $sciDept['id'];
        $_POST['course_id'] = $cRow['id'];
        $_POST['year_order'] = 1; // FY
        $_POST['first_name'] = 'Rajesh';
        $_POST['last_name'] = 'Kapadia';
        $_POST['email'] = 'rajesh.' . rand(100, 999) . '@testcollege.edu';
        $_POST['phone'] = '+91 9998887776';
        $_POST['gender'] = 'male';
        $_POST['roll_no'] = $testRoll;

        $controller->quickEnrollStudent();

        $enrolledStudent = $db->query("SELECT * FROM students WHERE roll_no = '{$testRoll}' AND tenant_id = 24 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->assertTrue(!empty($enrolledStudent), "Student should be enrolled in FY.");
        $this->assertEquals($cRow['id'], (int)$enrolledStudent['course_id']);
        $this->assertEquals(1, (int)$enrolledStudent['standard_id']);

        // Clean up test records to preserve pristine DB state
        if (!empty($enrolledStudent)) {
            $db->exec("DELETE FROM student_enrollments WHERE student_id = {$enrolledStudent['id']}");
            $db->exec("DELETE FROM students WHERE id = {$enrolledStudent['id']}");
        }
        if (!empty($cRow)) {
            $db->exec("DELETE FROM semesters WHERE course_id = {$cRow['id']}");
            $db->exec("DELETE FROM academic_levels WHERE course_id = {$cRow['id']}");
            $db->exec("DELETE FROM courses WHERE id = {$cRow['id']}");
        }

        // Restore admin auth for following tests
        Auth::attempt('admin', 'Admin@123');
    }

    public function testAcademicHierarchyDataStructureAndScoping(): void
    {
        // 1. Verify Tenant 24 hierarchy structure
        $data = AcademicController::getAcademicHierarchyData(24, false);
        $this->assertArrayHasKey('departments', $data);
        $this->assertArrayHasKey('stats', $data);

        $this->assertGreaterThanOrEqual(2, count($data['departments']), "Tenant 24 must contain at least 2 departments.");
        
        $stats = $data['stats'];
        $this->assertGreaterThanOrEqual(2, $stats['total_departments']);
        $this->assertGreaterThanOrEqual(4, $stats['total_courses']);
        $this->assertGreaterThanOrEqual(12, $stats['total_fy_students'], "Tenant 24 must have multiple FY students.");
        $this->assertGreaterThanOrEqual(12, $stats['total_sy_students'], "Tenant 24 must have multiple SY students.");
        $this->assertGreaterThanOrEqual(12, $stats['total_ty_students'], "Tenant 24 must have multiple TY students.");
        $this->assertGreaterThanOrEqual(36, $stats['total_students']);

        // Verify nested hierarchy levels: Department -> Course -> 3 Years (FY, SY, TY) -> Multiple Students
        foreach ($data['departments'] as $dept) {
            $this->assertNotEmpty($dept['name']);
            $this->assertGreaterThanOrEqual(1, count($dept['courses']), "Each department must contain courses.");
            
            foreach ($dept['courses'] as $course) {
                $this->assertNotEmpty($course['name']);
                $this->assertCount(3, $course['years'], "Each course must have exactly 3 years (FY, SY, TY).");
                
                $yearCodes = array_column($course['years'], 'code');
                $this->assertEquals(['FY', 'SY', 'TY'], $yearCodes, "Course years must be FY, SY, TY in order.");

                // If this is one of our primary seeded courses (16, 17, 18, 19), check students
                if (in_array((int)$course['id'], [16, 17, 18, 19])) {
                    foreach ($course['years'] as $yr) {
                        $this->assertGreaterThanOrEqual(2, count($yr['students']), "Year {$yr['code']} in {$course['name']} must have multiple students.");
                    }
                }
            }
        }

        // 2. Multi-tenant isolation check: Tenant 24 should not see Tenant 1 data
        $t1Data = AcademicController::getAcademicHierarchyData(1, false);
        $t1DeptIds = array_column($t1Data['departments'], 'id');
        $t24DeptIds = array_column($data['departments'], 'id');

        $intersection = array_intersect($t1DeptIds, $t24DeptIds);
        $this->assertEmpty($intersection, "Tenant 1 and Tenant 24 departments must be strictly isolated.");
    }
}

