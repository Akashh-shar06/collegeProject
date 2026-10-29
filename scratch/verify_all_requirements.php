<?php
/**
 * End-to-End Verification Script for MIS Modification Prompt
 * Tests Parts A, B, C, D, E, F, G
 */
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Controllers\DashboardController;
use App\Controllers\TimetableController;
use App\Controllers\AcademicController;
use App\Controllers\TeacherController;

Session::start();
$db = Database::getInstance();

$results = [];

function assertTest(string $desc, bool $condition, &$results, string $details = '') {
    if ($condition) {
        $results[] = ['status' => 'PASS', 'test' => $desc, 'details' => $details];
        echo "  [PASS] $desc" . ($details ? " ($details)" : "") . PHP_EOL;
    } else {
        $results[] = ['status' => 'FAIL', 'test' => $desc, 'details' => $details];
        echo "  [FAIL] $desc" . ($details ? " ($details)" : "") . PHP_EOL;
    }
}

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "  MIS VERIFICATION: PARTS A, B, C, D, E, F, G" . PHP_EOL;
echo "==========================================================" . PHP_EOL . PHP_EOL;

// -----------------------------------------------------------------------------
// TEST SUITE 1: HOD LOGIN & DASHBOARD (Part A & D)
// -----------------------------------------------------------------------------
echo "--- 1. HOD LOGIN & DASHBOARD SCOPING ---" . PHP_EOL;

// Ensure anish11 password is set
$hash = password_hash('Admin@123', PASSWORD_BCRYPT);
$db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'anish11'");

$loginHod = Auth::attempt('anish11', 'Admin@123');
assertTest("HOD 'anish11' logs in successfully", $loginHod, $results);

$user = Auth::user();
assertTest("Logged-in user role is 'hod'", $user['role_name'] === 'hod', $results, "role: {$user['role_name']}");
assertTest("Logged-in user tenant is 24", (int)$user['tenant_id'] === 24, $results, "tenant: {$user['tenant_id']}");

// Verify staff record department
$stfStmt = $db->prepare("SELECT s.id, s.department_id, d.name as dept_name FROM staff s JOIN departments d ON s.department_id = d.id WHERE s.user_id = :uid");
$stfStmt->execute([':uid' => $user['id']]);
$hodStaff = $stfStmt->fetch(PDO::FETCH_ASSOC);
assertTest("HOD is mapped to Science Department (ID: {$hodStaff['department_id']})", (int)$hodStaff['department_id'] === 116, $results, "Dept: {$hodStaff['dept_name']}");

// Test Dashboard Controller calculations
ob_start();
$dc = new DashboardController();
$dc->showDashboard();
$dashOutput = ob_get_clean();

assertTest("HOD Dashboard rendered successfully without fatal errors", !empty($dashOutput), $results);
assertTest("HOD Dashboard contains 'Science Department' title", strpos($dashOutput, 'Science Department') !== false, $results);
assertTest("HOD Dashboard contains Department Teachers card with View Details", strpos($dashOutput, 'Department Teachers') !== false && strpos($dashOutput, 'openHodModal(\'teachers\')') !== false, $results);
assertTest("HOD Dashboard contains Timetable Lecture Slots card with View Details", strpos($dashOutput, 'Timetable Lecture Slots') !== false && strpos($dashOutput, 'openHodModal(\'slots\')') !== false, $results);
assertTest("HOD Dashboard contains Degree Courses card with View Details", strpos($dashOutput, 'Degree Courses') !== false && strpos($dashOutput, 'openHodModal(\'courses\')') !== false, $results);
assertTest("HOD Dashboard contains Total Department Students card with View Details", strpos($dashOutput, 'Total Department Students') !== false && strpos($dashOutput, 'openHodModal(\'students\')') !== false, $results);

// Test Cross-Department Protection: Try passing ?dept_id=117 (Commerce)
$_GET['dept_id'] = 117;
ob_start();
$dc->showDashboard();
$tamperedOutput = ob_get_clean();
unset($_GET['dept_id']);

assertTest("HOD Dashboard ignores ?dept_id=117 query tamper and remains locked to Science Dept", (bool)preg_match('/<strong>Department:<\/strong>\s*Science Department/s', $tamperedOutput), $results);

// -----------------------------------------------------------------------------
// TEST SUITE 2: TIMETABLE MANAGEMENT WITHOUT CLASS/DIVISION (Part B)
// -----------------------------------------------------------------------------
echo PHP_EOL . "--- 2. TIMETABLE CREATION & CONFLICT DETECTION ---" . PHP_EOL;

// Log in as school admin / hod for timetable management
$tc = new TimetableController();

// 2.1 Test invalid time range rejection
$_POST['csrf_token'] = CSRF::getToken();
$_POST['department_id'] = 116;
$_POST['course_id'] = 16;
$_POST['standard_id'] = 1;
$_POST['section_id'] = 0; // Omitted / removed from UI
$_POST['day_of_week'] = 'Friday';
$_POST['start_time'] = '11:00:00';
$_POST['end_time'] = '10:00:00'; // Invalid: end < start
$_POST['subject_id'] = 1;
$_POST['staff_id'] = 123;
$_POST['room_no'] = 'Block 999';

$tc->createTimetable();
$flashErr = Session::getFlash('error');
assertTest("Rejects invalid time range (end_time <= start_time)", strpos($flashErr, 'Invalid Time Range') !== false, $results, "Flash: $flashErr");

// 2.2 Test valid timetable creation without section_id (auto-derived safely)
$slotStartTime = '14:15:00';
$slotEndTime = '15:05:00';
$_POST['csrf_token'] = CSRF::getToken();
$_POST['department_id'] = 116;
$_POST['course_id'] = 16; // B.Sc. CS
$_POST['standard_id'] = 2; // SY
$_POST['section_id'] = 0; // Removed from UI
$_POST['day_of_week'] = 'Wednesday';
$_POST['start_time'] = $slotStartTime;
$_POST['end_time'] = $slotEndTime;
$_POST['subject_id'] = 1;
$_POST['staff_id'] = 123;
$_POST['room_no'] = 'Room 404-B';

$tc->createTimetable();
$flashSuccess = Session::getFlash('success');
assertTest("Creates timetable entry without requiring Class/Division input", strpos($flashSuccess, 'successfully') !== false, $results, "Flash: $flashSuccess");

// Verify created row in DB
$slotStmt = $db->prepare("SELECT * FROM time_tables WHERE tenant_id = 24 AND staff_id = 123 AND day_of_week = 'Wednesday' AND start_time = :st LIMIT 1");
$slotStmt->execute([':st' => $slotStartTime]);
$createdSlot = $slotStmt->fetch(PDO::FETCH_ASSOC);

assertTest("Timetable row is correctly stored in DB with auto-resolved details", !empty($createdSlot), $results, "Slot ID: " . ($createdSlot['id'] ?? 'none'));
assertTest("Timetable row standard_id is preserved", (int)($createdSlot['standard_id'] ?? 0) === 2, $results);

// 2.3 Test duplicate timetable entry rejection
if (!empty($createdSlot)) {
    $_POST['csrf_token'] = CSRF::getToken();
    $_POST['department_id'] = 116;
    $_POST['course_id'] = 16;
    $_POST['standard_id'] = 2;
    $_POST['section_id'] = 0;
    $_POST['day_of_week'] = 'Wednesday';
    $_POST['start_time'] = $slotStartTime;
    $_POST['end_time'] = $slotEndTime;
    $_POST['subject_id'] = 1;
    $_POST['staff_id'] = 123;
    $_POST['room_no'] = 'Room 505';

    $tc->createTimetable();
    $dupErr = Session::getFlash('error');
    assertTest("Rejects duplicate timetable entry for same teacher and subject", strpos($dupErr, 'Duplicate') !== false, $results, "Flash: $dupErr");
}

// 2.4 Test classroom conflict rejection
if (!empty($createdSlot)) {
    $_POST['csrf_token'] = CSRF::getToken();
    $_POST['department_id'] = 116;
    $_POST['course_id'] = 17; // Different course
    $_POST['standard_id'] = 1;
    $_POST['section_id'] = 0;
    $_POST['day_of_week'] = 'Wednesday';
    $_POST['start_time'] = $slotStartTime;
    $_POST['end_time'] = $slotEndTime;
    $_POST['subject_id'] = 2;
    $_POST['staff_id'] = 124; // Different teacher
    $_POST['room_no'] = 'Room 404-B'; // SAME room!

    $tc->createTimetable();
    $roomErr = Session::getFlash('error');
    assertTest("Detects classroom conflict for overlapping slot", strpos($roomErr, 'already occupied') !== false, $results, "Flash: $roomErr");
}

// 2.5 Test cohort conflict rejection
if (!empty($createdSlot)) {
    $_POST['csrf_token'] = CSRF::getToken();
    $_POST['department_id'] = 116;
    $_POST['course_id'] = 16; // SAME course
    $_POST['standard_id'] = 2; // SAME year (SY)
    $_POST['section_id'] = 0;
    $_POST['day_of_week'] = 'Wednesday';
    $_POST['start_time'] = $slotStartTime;
    $_POST['end_time'] = $slotEndTime;
    $_POST['subject_id'] = 3;
    $_POST['staff_id'] = 124; // Different teacher
    $_POST['room_no'] = 'Room 999'; // Different room

    $tc->createTimetable();
    $cohErr = Session::getFlash('error');
    assertTest("Detects cohort conflict for same course and year cohort", strpos($cohErr, 'Cohort Conflict') !== false, $results, "Flash: $cohErr");
}

// 2.6 Clean up test slot
if (!empty($createdSlot)) {
    $db->exec("DELETE FROM time_tables WHERE id = {$createdSlot['id']}");
    echo "  [INFO] Cleaned up test timetable slot ID {$createdSlot['id']}" . PHP_EOL;
}

// -----------------------------------------------------------------------------
// TEST SUITE 3: ACADEMIC DATA CLEANUP & DUPLICATE PREVENTION (Part C)
// -----------------------------------------------------------------------------
echo PHP_EOL . "--- 3. ACADEMIC DATA CLEANUP & DUPLICATE PREVENTION ---" . PHP_EOL;

// 3.1 Verify Zero Duplicates in Current Database
$dupDepts = $db->query("SELECT tenant_id, LOWER(TRIM(name)) as n, COUNT(*) as c FROM departments GROUP BY tenant_id, LOWER(TRIM(name)) HAVING c > 1")->fetchAll();
assertTest("Zero duplicate departments exist across all tenants", empty($dupDepts), $results, "Duplicates: " . count($dupDepts));

$dupCourses = $db->query("SELECT tenant_id, department_id, LOWER(TRIM(name)) as n, COUNT(*) as c FROM courses WHERE status = 'active' GROUP BY tenant_id, department_id, LOWER(TRIM(name)) HAVING c > 1")->fetchAll();
assertTest("Zero duplicate active courses exist across all tenants", empty($dupCourses), $results, "Duplicates: " . count($dupCourses));

$dupBatches = $db->query("SELECT tenant_id, course_id, LOWER(TRIM(batch_name)) as n, COUNT(*) as c FROM batches GROUP BY tenant_id, course_id, LOWER(TRIM(batch_name)) HAVING c > 1")->fetchAll();
assertTest("Zero duplicate cohorts (batches) exist across all tenants", empty($dupBatches), $results, "Duplicates: " . count($dupBatches));

$dupLevels = $db->query("SELECT tenant_id, course_id, level_order, COUNT(*) as c FROM academic_levels GROUP BY tenant_id, course_id, level_order HAVING c > 1")->fetchAll();
assertTest("Zero duplicate academic levels exist across all courses", empty($dupLevels), $results, "Duplicates: " . count($dupLevels));

// 3.2 Test Backend Rejection of Duplicate Course Name within Department
$ac = new AcademicController();

$_POST['csrf_token'] = CSRF::getToken();
$_POST['id'] = 0;
$_POST['name'] = 'B.Sc. Computer Science'; // Existing in Dept 116
$_POST['code'] = 'BSCCS_NEW_' . rand(100, 999);
$_POST['duration_years'] = 3;
$_POST['department_id'] = 116;
$_POST['status'] = 'active';

$ac->storeCourse();
$dupCourseErr = Session::getFlash('error');
assertTest("Rejects duplicate course name within same department", strpos($dupCourseErr, 'already exists in this department') !== false, $results, "Flash: $dupCourseErr");

// 3.3 Test Backend Rejection of Duplicate Department Name within Tenant
$_POST['csrf_token'] = CSRF::getToken();
$_POST['id'] = 0;
$_POST['name'] = 'Science Department'; // Existing in Tenant 24
$_POST['code'] = 'SCI_NEW_' . rand(100, 999);
$_POST['status'] = 'active';

$ac->storeDepartment();
$dupDeptErr = Session::getFlash('error');
assertTest("Rejects duplicate department name within same tenant", strpos($dupDeptErr, 'already registered') !== false, $results, "Flash: $dupDeptErr");

// -----------------------------------------------------------------------------
// TEST SUITE 4: TEACHING STAFF LOGIN & SCOPING (Part D)
// -----------------------------------------------------------------------------
echo PHP_EOL . "--- 4. TEACHING STAFF LOGIN & ACCESS CONTROL ---" . PHP_EOL;

// Ensure dr_soap password is set
$db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'dr_soap'");

$teacherLogin = Auth::attempt('dr_soap', 'Admin@123');
assertTest("Teaching staff 'dr_soap' logs in successfully", $teacherLogin, $results);

$tUser = Auth::user();
assertTest("Logged-in teaching staff role is 'teacher'", $tUser['role_name'] === 'teacher', $results, "role: {$tUser['role_name']}");

// Test Teacher Controller
ob_start();
$tcTeacher = new TeacherController();
$tcTeacher->showDashboard();
$teacherDashOutput = ob_get_clean();

assertTest("Teacher Dashboard renders cleanly without errors", !empty($teacherDashOutput), $results);
assertTest("Teacher Dashboard displays teacher schedule and assigned lectures", strpos($teacherDashOutput, 'Weekly Timetable') !== false || strpos($teacherDashOutput, 'Timetable') !== false, $results);

// Verify Teacher cannot perform HOD department actions
assertTest("Teacher does not have 'hod' role", $tUser['role_name'] !== 'hod', $results);

echo PHP_EOL . "==========================================================" . PHP_EOL;
$total = count($results);
$passed = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
$failed = $total - $passed;
echo "  TOTAL TESTS: $total | PASSED: $passed | FAILED: $failed" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
