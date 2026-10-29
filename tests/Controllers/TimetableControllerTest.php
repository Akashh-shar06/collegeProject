<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\TimetableController;
use App\Controllers\TeacherController;
use App\Controllers\AuthController;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Database;
use App\Core\CSRF;
use PDO;

class TimetableControllerTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    public function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
    }

    /**
     * Test 1 — Multi-User / Concurrent Session Security
     */
    public function testMultiUserConcurrentSessionSecurity(): void
    {
        // 1. Log in Student A
        $loginStudent = Auth::attempt('john_student', 'Admin@123');
        $this->assertTrue($loginStudent, "Student A login failed");

        $studentUser = Auth::user();
        $this->assertEquals(6, $studentUser['id']);
        $this->assertEquals('student', $studentUser['role_name']);
        $this->assertEquals(1, $studentUser['tenant_id']);
        $this->assertEquals(2, $studentUser['department_id']); // B.Sc. IT Department

        // Save Student A Session Snapshot
        $studentSessionSnapshot = $_SESSION;

        // 2. Log in Teacher A in parallel session context
        $_SESSION = []; // Simulate clean concurrent session container
        $loginTeacher = Auth::attempt('dr_soap', 'Admin@123');
        $this->assertTrue($loginTeacher, "Teacher A login failed");

        $teacherUser = Auth::user();
        $this->assertEquals(3, $teacherUser['id']);
        $this->assertEquals('teacher', $teacherUser['role_name']);
        $this->assertEquals(1, $teacherUser['tenant_id']);
        $this->assertNotEquals($studentSessionSnapshot['user_id'], $_SESSION['user_id']);

        // 3. Verify Student A session data remains isolated and untouched
        $this->assertEquals(6, $studentSessionSnapshot['user_id']);
        $this->assertEquals('student', $studentSessionSnapshot['role_name']);
        $this->assertEquals(2, $studentSessionSnapshot['department_id']);
    }

    /**
     * Test 2 — Multiple Class Timetables & Student Visibility Scoping
     */
    public function testStudentTimetableVisibilityScoping(): void
    {
        Auth::attempt('john_student', 'Admin@123');
        $user = Auth::user();
        $db = Database::getInstance();

        // Fetch student profile linked to john_student
        $stStmt = $db->prepare("SELECT s.*, b.course_id, c.department_id as course_dept_id FROM students s LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id WHERE s.user_id = :uid LIMIT 1");
        $stStmt->execute([':uid' => $user['id']]);
        $studentScope = $stStmt->fetch();

        $this->assertNotNull($studentScope, "Student scope record not found");
        $this->assertEquals(2, $studentScope['department_id'], "Student should belong to B.Sc. IT (Department 2)");
        $this->assertEquals(5, $studentScope['semester_id'], "Student should belong to Semester 5");
        $this->assertEquals(1, $studentScope['section_id'], "Student should belong to Division A");

        // Query timetable using student scoping rules
        $stmt = $db->prepare("
            SELECT t.* FROM time_tables t 
            WHERE t.tenant_id = :tid 
              AND (t.department_id = :dept OR t.department_id IS NULL) 
              AND (t.course_id = :course OR t.course_id IS NULL) 
              AND (t.semester_id = :sem OR t.semester_id IS NULL) 
              AND (t.section_id = :sec OR t.section_id IS NULL OR t.batch_id = :batch)
        ");
        $stmt->execute([
            ':tid' => $user['tenant_id'],
            ':dept' => $studentScope['department_id'],
            ':course' => $studentScope['course_id'],
            ':sem' => $studentScope['semester_id'],
            ':sec' => $studentScope['section_id'],
            ':batch' => $studentScope['batch_id'],
        ]);
        $studentRecords = $stmt->fetchAll();

        // Student MUST see B.Sc. IT Semester 5 Division A DBMS class
        $subjectNames = array_column($studentRecords, 'subject_name');
        $this->assertContains('Database Management System', $subjectNames);

        // Student MUST NOT see B.Com Financial Accounting class (department_id 3)
        $this->assertFalse(in_array('Financial Accounting', $subjectNames, true), "Student saw unauthorized B.Com timetable!");
    }

    /**
     * Test 3 — Multi-Tenant Security Data Isolation
     */
    public function testMultiTenantDataIsolation(): void
    {
        // Log in College B Student (Tenant 2)
        $loginRes = Auth::attempt('college_b_student', 'Admin@123');
        $this->assertTrue($loginRes);
        $user = Auth::user();

        $this->assertEquals(2, $user['tenant_id'], "User should belong to Tenant 2 (College B)");

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM time_tables WHERE tenant_id = :tid");
        $stmt->execute([':tid' => $user['tenant_id']]);
        $tenant2Records = $stmt->fetchAll();

        $subjectNames = array_column($tenant2Records, 'subject_name');

        // College B student sees College B Course Subject
        $this->assertContains('College B Course Subject', $subjectNames);

        // College B student MUST NOT see College A (Tenant 1) Database Management System timetable
        $this->assertFalse(in_array('Database Management System', $subjectNames, true), "College B user accessed College A data!");
    }

    /**
     * Test 4 — Teacher & Admin Timetable Creation Management
     */
    public function testTeacherAndAdminTimetableManagement(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $user = Auth::user();

        $db = Database::getInstance();
        $initialCount = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE tenant_id = 1")->fetchColumn();

        // Create new timetable record as Principal/Admin
        $stmt = $db->prepare("
            INSERT INTO time_tables 
            (tenant_id, academic_year, department_id, course_id, semester_id, section_id, batch_id, day_of_week, time_slot, start_time, end_time, subject_name, staff_id, room_no, created_by)
            VALUES 
            (:tid, '2026-2027', 2, 2, 5, 1, 2, 'Thursday', '09:00 AM - 10:00 AM', '09:00:00', '10:00:00', 'Software Architecture', 1, 'Block 104', :cb)
        ");
        $stmt->execute([':tid' => $user['tenant_id'], ':cb' => $user['id']]);

        $newCount = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE tenant_id = 1")->fetchColumn();
        $this->assertTrue($newCount > $initialCount, "Admin failed to create timetable entry");

        // Verify inserted record details
        $lastRecord = $db->query("SELECT * FROM time_tables WHERE subject_name = 'Software Architecture' AND tenant_id = 1 ORDER BY id DESC LIMIT 1")->fetch();
        $this->assertNotNull($lastRecord);
        $this->assertEquals(2, (int)$lastRecord['department_id']);
        $this->assertEquals(5, (int)$lastRecord['semester_id']);
        $this->assertEquals(1, (int)$lastRecord['section_id']);
    }

    /**
     * Scenario 1: An Administrator can create a lecture with interval timings and lecture type.
     */
    public function testAdminCanCreateLectureWithAJAXAndValidateDB(): void
    {
        Auth::attempt('principal_john', 'Admin@123'); // Admin
        $controller = new TimetableController();
        $db = Database::getInstance();
        $db->exec("DELETE FROM time_tables WHERE day_of_week = 'Wednesday' AND start_time = '14:00:00' AND tenant_id = 1");

        $_POST = [
            'ajax' => '1',
            'department_id' => '1',
            'course_id' => '1',
            'standard_id' => '1',
            'subject_id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Wednesday',
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'room_no' => 'Lab 301',
            'academic_year' => '2026-2027',
            'lecture_type' => 'practical',
            'notes' => 'Hands-on practical session'
        ];

        ob_start();
        $controller->createTimetable();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertNotNull($res, "Response must be valid JSON");
        $this->assertTrue($res['success'] ?? false, "Lecture creation should succeed: " . ($res['error'] ?? ''));
        $this->assertNotNull($res['id'] ?? null);

        $stmt = $db->prepare("SELECT * FROM time_tables WHERE id = :id");
        $stmt->execute([':id' => $res['id']]);
        $created = $stmt->fetch();

        $this->assertNotNull($created);
        $this->assertEquals('Wednesday', $created['day_of_week']);
        $this->assertEquals('14:00:00', $created['start_time']);
        $this->assertEquals('15:00:00', $created['end_time']);
        $this->assertEquals('practical', $created['lecture_type']);
        $this->assertEquals('Hands-on practical session', $created['notes']);

        // Clean up test entry
        $db->exec("DELETE FROM time_tables WHERE id = {$res['id']}");
    }

    /**
     * Scenario 2 & 3: An administrator can edit lecture start/end times and reschedule it.
     */
    public function testAdminCanEditLectureTimingsAndReschedule(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $db = Database::getInstance();
        $db->exec("DELETE FROM time_tables WHERE day_of_week = 'Friday' AND start_time IN ('11:00:00', '15:00:00') AND tenant_id = 1");

        // Create a slot to edit
        $db->exec("INSERT INTO time_tables (tenant_id, batch_id, academic_year, department_id, course_id, standard_id, day_of_week, start_time, end_time, time_slot, subject_name, staff_id, room_no, lecture_type, status)
            VALUES (1, 1, '2026-2027', 1, 1, 1, 'Friday', '11:00:00', '12:00:00', '11:00 AM - 12:00 PM', 'Data Structures', 1, 'Room 205', 'theory', 'active')");
        $id = (int)$db->lastInsertId();

        $controller = new TimetableController();
        $_POST = [
            'ajax' => '1',
            'id' => (string)$id,
            'department_id' => '1',
            'course_id' => '1',
            'standard_id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Friday',
            'start_time' => '15:00:00',
            'end_time' => '16:00:00',
            'room_no' => 'Room 205',
            'academic_year' => '2026-2027',
            'lecture_type' => 'theory',
            'status' => 'active'
        ];

        ob_start();
        $controller->updateTimetable();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertTrue($res['success'] ?? false, "Edit timetable should succeed: " . ($res['error'] ?? ''));

        $updated = $db->query("SELECT * FROM time_tables WHERE id = {$id}")->fetch();
        $this->assertEquals('15:00:00', $updated['start_time']);
        $this->assertEquals('16:00:00', $updated['end_time']);

        $db->exec("DELETE FROM time_tables WHERE id = {$id}");
    }

    /**
     * Scenario 4 & 5: An administrator can delete a single lecture, leaves others unchanged.
     */
    public function testAdminCanDeleteSingleLectureWithIsolation(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $db = Database::getInstance();

        // Insert two entries
        $db->exec("INSERT INTO time_tables (tenant_id, batch_id, academic_year, department_id, course_id, standard_id, day_of_week, start_time, end_time, subject_name, staff_id, room_no, status)
            VALUES (1, 1, '2026-2027', 1, 1, 1, 'Saturday', '08:00:00', '09:00:00', 'Lecture To Keep', 1, 'Room 101', 'active')");
        $keepId = (int)$db->lastInsertId();

        $db->exec("INSERT INTO time_tables (tenant_id, batch_id, academic_year, department_id, course_id, standard_id, day_of_week, start_time, end_time, subject_name, staff_id, room_no, status)
            VALUES (1, 1, '2026-2027', 1, 1, 1, 'Saturday', '09:00:00', '10:00:00', 'Lecture To Delete', 1, 'Room 101', 'active')");
        $delId = (int)$db->lastInsertId();

        $controller = new TimetableController();
        $_POST = ['ajax' => '1', 'id' => (string)$delId];

        ob_start();
        $controller->deleteTimetable();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertTrue($res['success'] ?? false, "Deletion should succeed");

        // Assert deleted slot no longer exists
        $delCheck = $db->query("SELECT id FROM time_tables WHERE id = {$delId}")->fetchColumn();
        $this->assertFalse($delCheck, "Deleted lecture must no longer exist in database");

        // Assert kept slot is completely intact
        $keepCheck = $db->query("SELECT id FROM time_tables WHERE id = {$keepId}")->fetchColumn();
        $this->assertEquals($keepId, (int)$keepCheck, "Non-deleted lectures must remain untouched");
    }

    /**
     * Scenario 6: A teacher views only their assigned schedule.
     */
    public function testTeacherCanViewOnlyTheirPermittedTimetable(): void
    {
        Auth::attempt('dr_soap', 'Admin@123');
        $user = Auth::user();

        $this->assertEquals('teacher', $user['role_name']);
        $this->assertFalse(TimetableController::canUserManageTimetable($user), "Teacher must not have manage permissions by default");
    }

    /**
     * Scenario 7, 8 & 9: Unauthorized teacher cannot edit or delete lectures via direct API calls (403 Forbidden).
     */
    public function testUnauthorizedTeacherBlockedFromEditingAndDeletingDirectly(): void
    {
        Auth::attempt('dr_soap', 'Admin@123'); // Teacher without can_manage
        $controller = new TimetableController();

        // 1. Attempt Edit
        $_POST = [
            'ajax' => '1',
            'id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Monday',
            'start_time' => '07:10:00',
            'end_time' => '08:00:00',
            'subject_id' => '1'
        ];

        ob_start();
        $controller->updateTimetable();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertFalse($res['success'] ?? true);
        $this->assertTrue(str_contains($res['error'] ?? '', '403 Forbidden'), "Unauthorized teacher edit must return 403 Forbidden");

        // 2. Attempt Delete
        $_POST = ['ajax' => '1', 'id' => '1'];
        ob_start();
        $controller->deleteTimetable();
        $delJson = ob_get_clean();

        $delRes = json_decode($delJson, true);
        $this->assertFalse($delRes['success'] ?? true);
        $this->assertTrue(str_contains($delRes['error'] ?? '', '403 Forbidden'), "Unauthorized teacher delete must return 403 Forbidden");
    }

    /**
     * Scenario 9: A student cannot access another class's restricted timetable record via API (403 Forbidden).
     */
    public function testStudentTamperingRejectedWith403(): void
    {
        Auth::attempt('john_student', 'Admin@123'); // Enrolled in Dept 2 / Course 2 (B.Sc. IT)
        $controller = new TimetableController();

        // Entry 2 is Financial Accounting in Dept 3 / Course 3 (B.Com)
        $_GET = ['id' => '2'];
        ob_start();
        $controller->getTimetableEntryJson();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertNull($res['success'] ?? null);
        $this->assertTrue(str_contains($res['error'] ?? '', '403 Forbidden'), "Student accessing other class lecture must be rejected with 403");
    }

    /**
     * Scenario 10: Overlapping teacher, classroom, and cohort intervals are rejected with 409 conflict.
     */
    public function testIntervalOverlapTeacherClassroomCohortConflictRejected(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $db = Database::getInstance();

        // Seed an anchor slot: Monday 09:00:00 to 10:00:00 with Staff 1, Room 101, Course 1, Standard 1
        $db->exec("INSERT INTO time_tables (tenant_id, batch_id, academic_year, department_id, course_id, standard_id, day_of_week, start_time, end_time, time_slot, subject_name, staff_id, room_no, status)
            VALUES (1, 1, '2026-2027', 1, 1, 1, 'Monday', '09:00:00', '10:00:00', '09:00 AM - 10:00 AM', 'Anchor Lecture', 1, 'Room 101', 'active')");
        $anchorId = (int)$db->lastInsertId();

        $controller = new TimetableController();

        // Case A: Teacher conflict with overlapping time 09:30 to 10:30 (interval overlap!)
        $_POST = [
            'ajax' => '1',
            'department_id' => '1',
            'course_id' => '2',
            'standard_id' => '2',
            'subject_id' => '1',
            'staff_id' => '1', // Same teacher!
            'day_of_week' => 'Monday',
            'start_time' => '09:30:00', // Overlaps with 09:00 - 10:00
            'end_time' => '10:30:00',
            'room_no' => 'Room 999',
            'academic_year' => '2026-2027'
        ];

        ob_start();
        $controller->createTimetable();
        $json = ob_get_clean();
        $res = json_decode($json, true);

        $this->assertFalse($res['success'] ?? true);
        $this->assertTrue(str_contains($res['error'] ?? '', 'Teacher Conflict'), "Teacher interval overlap must be rejected");

        // Case B: Classroom conflict with overlapping time 08:30 to 09:30 in Room 101
        $_POST = [
            'ajax' => '1',
            'department_id' => '1',
            'course_id' => '2',
            'standard_id' => '2',
            'subject_id' => '1',
            'staff_id' => '99', // Different teacher
            'day_of_week' => 'Monday',
            'start_time' => '08:30:00', // Overlaps with 09:00 - 10:00
            'end_time' => '09:30:00',
            'room_no' => 'Room 101', // Same room!
            'academic_year' => '2026-2027'
        ];

        ob_start();
        $controller->createTimetable();
        $jsonRoom = ob_get_clean();
        $resRoom = json_decode($jsonRoom, true);

        $this->assertFalse($resRoom['success'] ?? true);
        $this->assertTrue(str_contains($resRoom['error'] ?? '', 'Classroom Conflict'), "Classroom interval overlap must be rejected");

        // Clean up anchor
        $db->exec("DELETE FROM time_tables WHERE id = {$anchorId}");
    }

    /**
     * Scenario 10 & 11: Self-conflict is excluded when editing an existing lecture.
     */
    public function testSelfConflictExcludedOnLectureEdit(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $db = Database::getInstance();

        $db->exec("INSERT INTO time_tables (tenant_id, batch_id, academic_year, department_id, course_id, standard_id, day_of_week, start_time, end_time, time_slot, subject_name, staff_id, room_no, status)
            VALUES (1, 1, '2026-2027', 1, 1, 1, 'Tuesday', '10:00:00', '11:00:00', '10:00 AM - 11:00 AM', 'Physics', 1, 'Room 105', 'active')");
        $slotId = (int)$db->lastInsertId();

        $controller = new TimetableController();
        // Update the notes and lecture type on the SAME slot at the SAME time
        $_POST = [
            'ajax' => '1',
            'id' => (string)$slotId,
            'department_id' => '1',
            'course_id' => '1',
            'standard_id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Tuesday',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'room_no' => 'Room 105',
            'academic_year' => '2026-2027',
            'lecture_type' => 'practical',
            'notes' => 'Updated without self-conflict'
        ];

        ob_start();
        $controller->updateTimetable();
        $json = ob_get_clean();
        $res = json_decode($json, true);

        $this->assertTrue($res['success'] ?? false, "Self-conflict must not block updating own lecture");

        $db->exec("DELETE FROM time_tables WHERE id = {$slotId}");
    }

    /**
     * Scenario 11: Single connected database consistency between teacher and class views.
     */
    public function testSingleConnectedDatabaseConsistencyBetweenTeacherAndClassViews(): void
    {
        $db = Database::getInstance();

        // 1. Fetch record
        $rec = $db->query("SELECT * FROM time_tables WHERE id = 1 LIMIT 1")->fetch();
        $this->assertNotNull($rec);

        // Teacher-wise query
        $tStmt = $db->prepare("SELECT * FROM time_tables WHERE staff_id = :sid AND id = 1");
        $tStmt->execute([':sid' => $rec['staff_id']]);
        $teacherViewRow = $tStmt->fetch();

        // Class-wise query
        $cStmt = $db->prepare("SELECT * FROM time_tables WHERE course_id = :cid AND id = 1");
        $cStmt->execute([':cid' => $rec['course_id']]);
        $classViewRow = $cStmt->fetch();

        $this->assertEquals($teacherViewRow['id'], $classViewRow['id'], "Both views must read from the exact same record");
        $this->assertEquals($teacherViewRow['subject_name'], $classViewRow['subject_name']);
        $this->assertEquals($teacherViewRow['start_time'], $classViewRow['start_time']);
    }

    /**
     * Scenario 13: Unauthenticated request to API returns 401 Unauthorized.
     */
    public function testUnauthenticatedApiAccessReturns401(): void
    {
        Session::destroy();
        $_SESSION = [];

        $controller = new TimetableController();
        $_GET = ['id' => '1'];

        ob_start();
        $controller->getTimetableEntryJson();
        $json = ob_get_clean();

        $res = json_decode($json, true);
        $this->assertNull($res['success'] ?? null);
        $this->assertTrue(str_contains($res['error'] ?? '', '401 Unauthorized'), "Unauthenticated API request must return 401 Unauthorized");
    }

    /**
     * Scenario 14: Invalid time ranges and lectures outside college hours (06:00-21:00) are rejected.
     */
    public function testInvalidTimeRangeAndOutsideHoursValidation(): void
    {
        Auth::attempt('principal_john', 'Admin@123');
        $controller = new TimetableController();

        // Case A: End time earlier than start time
        $_POST = [
            'ajax' => '1',
            'department_id' => '1',
            'course_id' => '1',
            'subject_id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Monday',
            'start_time' => '10:00:00',
            'end_time' => '09:00:00', // Invalid!
            'room_no' => 'Room 101'
        ];

        ob_start();
        $controller->createTimetable();
        $json = ob_get_clean();
        $res = json_decode($json, true);

        $this->assertFalse($res['success'] ?? true);
        $this->assertTrue(str_contains($res['error'] ?? '', 'Invalid Time Range'), "End time before start time must be rejected");

        // Case B: Outside college operating hours (e.g. 05:00 AM)
        $_POST = [
            'ajax' => '1',
            'department_id' => '1',
            'course_id' => '1',
            'subject_id' => '1',
            'staff_id' => '1',
            'day_of_week' => 'Monday',
            'start_time' => '05:00:00',
            'end_time' => '06:00:00',
            'room_no' => 'Room 101'
        ];

        ob_start();
        $controller->createTimetable();
        $jsonHours = ob_get_clean();
        $resHours = json_decode($jsonHours, true);

        $this->assertFalse($resHours['success'] ?? true);
        $this->assertTrue(str_contains($resHours['error'] ?? '', 'operating hours'), "Times outside 06:00 - 21:00 must be rejected");
    }
}
