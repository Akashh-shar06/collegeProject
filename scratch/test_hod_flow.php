<?php
define('BASE_PATH', dirname(__DIR__));
define('TESTING_MODE', true);
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Session.php';
require_once BASE_PATH . '/app/Core/Auth.php';
require_once BASE_PATH . '/app/Core/Permission.php';
require_once BASE_PATH . '/app/Core/Response.php';
require_once BASE_PATH . '/app/Core/Validator.php';
require_once BASE_PATH . '/app/Core/CSRF.php';
require_once BASE_PATH . '/app/Controllers/DashboardController.php';
require_once BASE_PATH . '/app/Controllers/TimetableController.php';
require_once BASE_PATH . '/app/Controllers/AcademicController.php';

use App\Core\Auth;
use App\Core\Session;
use App\Core\Permission;
use App\Core\Database;

Session::start();

echo "=== TEST 1: Login as anish11 (HOD) ===\n";
$loginSuccess = Auth::attempt('anish11', 'Admin@123');
if (!$loginSuccess) {
    die("FAILED: Auth::attempt failed for anish11\n");
}

$user = Auth::user();
echo "Logged in User: " . $user['username'] . "\n";
echo "Role Name: " . $user['role_name'] . "\n";
echo "Role Display: " . $user['role_display'] . "\n";
echo "Active Dept ID: " . Session::get('selected_dept_id') . "\n";

assert($user['role_name'] === 'hod', "Role name must be hod");
assert(strpos($user['role_display'], 'HOD') !== false, "Role display must include HOD");

echo "\n=== TEST 2: Permission Matrix Checks for HOD ===\n";
assert(Permission::can('time_table'), "HOD must have time_table permission");
echo "PASS: time_table permission granted\n";
assert(Permission::can('courses'), "HOD must have courses permission");
echo "PASS: courses permission granted\n";
assert(Permission::can('departments'), "HOD must have departments permission");
echo "PASS: departments permission granted\n";
assert(Permission::can('academic_structure'), "HOD must have academic_structure permission");
echo "PASS: academic_structure permission granted\n";

echo "\n=== TEST 3: Validate Role HOD View Loading ===\n";
ob_start();
try {
    (new \App\Controllers\DashboardController())->showDashboard();
    $html = ob_get_clean();
    echo "Dashboard rendered successfully! HTML length: " . strlen($html) . " bytes\n";
    assert(strpos($html, 'HOD / Head of Department Portal') !== false, "HTML must contain HOD / Head of Department Portal");
    assert(strpos($html, 'Create Teacher Timetable') !== false, "HTML must contain Create Teacher Timetable");
    echo "PASS: HOD Dashboard shows correct HOD title and Create Teacher Timetable buttons!\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR rendering HOD dashboard: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== TEST 4: Validate Timetable View for HOD ===\n";
ob_start();
try {
    (new \App\Controllers\TimetableController())->showTimetable();
    $ttHtml = ob_get_clean();
    echo "Timetable rendered successfully! HTML length: " . strlen($ttHtml) . " bytes\n";
    assert(strpos($ttHtml, '+ Add Timetable Entry') !== false, "HTML must contain + Add Timetable Entry for HOD");
    assert(strpos($ttHtml, 'Copy / Duplicate Schedule') !== false, "HTML must contain Copy / Duplicate Schedule for HOD");
    assert(strpos($ttHtml, 'addTimetableModal') !== false, "HTML must contain addTimetableModal for HOD");
    assert(strpos($ttHtml, 'editTimetableModal') !== false, "HTML must contain editTimetableModal for HOD");
    echo "PASS: HOD has full access to add, edit, and duplicate teacher timetables!\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR rendering Timetable View: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== TEST 5: Create a Timetable Entry as HOD ===\n";
$db = Database::getInstance();
// Find a teacher in the department or tenant
$teacherId = (int)$db->query("SELECT id FROM staff WHERE tenant_id = " . (int)$user['tenant_id'] . " LIMIT 1")->fetchColumn();
$courseId = (int)$db->query("SELECT id FROM courses WHERE department_id = " . (int)Session::get('selected_dept_id') . " LIMIT 1")->fetchColumn() ?: 1;
$sectionId = (int)$db->query("SELECT id FROM sections LIMIT 1")->fetchColumn() ?: 1;
$subjectId = (int)$db->query("SELECT id FROM subjects LIMIT 1")->fetchColumn() ?: 1;

$_POST = [
    'academic_year' => '2026-2027',
    'department_id' => Session::get('selected_dept_id'),
    'standard_id' => 3,
    'course_id' => $courseId,
    'section_id' => $sectionId,
    'day_of_week' => 'Friday',
    'start_time' => '15:00:00',
    'end_time' => '16:00:00',
    'subject_id' => $subjectId,
    'subject_name' => 'Advanced Robotics',
    'staff_id' => $teacherId,
    'room_no' => 'Lab 305',
];

// Clean any previous test slot at this time
$db->exec("DELETE FROM time_tables WHERE day_of_week = 'Friday' AND start_time = '15:00:00' AND staff_id = {$teacherId}");

(new \App\Controllers\TimetableController())->createTimetable();

$inserted = $db->query("SELECT * FROM time_tables WHERE day_of_week = 'Friday' AND start_time = '15:00:00' AND staff_id = {$teacherId} LIMIT 1")->fetch();
assert(!empty($inserted), "Timetable entry should be inserted by HOD");
echo "PASS: Timetable entry successfully created by HOD for Teacher ID {$teacherId}! Entry ID: " . $inserted['id'] . "\n";

// Cleanup test entry
$db->exec("DELETE FROM time_tables WHERE id = " . (int)$inserted['id']);
echo "PASS: Cleaned up test timetable entry.\n";

echo "\n=== ALL HOD TESTS PASSED SUCCESSFULLY! ===\n";
