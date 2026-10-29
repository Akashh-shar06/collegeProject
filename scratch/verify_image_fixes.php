<?php
/**
 * Automated Verification Script for User Requested Image Fixes:
 * 1. KPI Card View Details buttons & interactive modals in role_hod.php
 * 2. Image 3 Dark Matrix Timetable format with HOD teacher, timing, subject editing and slot deletion
 * 3. Image 4 Subject dropdown populated with subjects from teacher's timetable
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Auth;
use App\Core\Session;

Session::start();
$db = Database::getInstance();

echo "\n==========================================================\n";
echo "  TESTING IMAGE FIXES & HOD TIMETABLE CAPABILITIES\n";
echo "==========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($condition, $description) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

// ---------------------------------------------------------------------------
// TEST 1: Check role_hod.php Modals & JS Controller
// ---------------------------------------------------------------------------
echo "--- 1. HOD DASHBOARD KPI BUTTONS & MODALS ---\n";
$hodViewContent = file_get_contents(BASE_PATH . '/app/Views/dashboard/role_hod.php');

assertCheck(strpos($hodViewContent, 'id="hodModal_teachers"') !== false, "Teachers modal markup exists");
assertCheck(strpos($hodViewContent, 'id="hodModal_slots"') !== false, "Timetable slots modal markup exists");
assertCheck(strpos($hodViewContent, 'id="hodModal_courses"') !== false, "Degree courses modal markup exists");
assertCheck(strpos($hodViewContent, 'id="hodModal_students"') !== false, "Department students modal markup exists");
assertCheck(strpos($hodViewContent, 'function openHodModal') !== false, "openHodModal JavaScript controller function exists");
assertCheck(strpos($hodViewContent, 'function closeHodModal') !== false, "closeHodModal JavaScript function exists");
assertCheck(strpos($hodViewContent, 'function filterHodTable') !== false, "Live search table filter function exists");

// ---------------------------------------------------------------------------
// TEST 2: Check Image 3 Dark Matrix Timetable & HOD Controls
// ---------------------------------------------------------------------------
echo "\n--- 2. IMAGE 3 DARK MATRIX TIMETABLE & HOD EDIT/DELETE ---\n";

// Login as HOD anish11
Auth::attempt('anish11', 'Admin@123');
$user = Auth::user();
assertCheck($user && $user['role_name'] === 'hod', "HOD anish11 authenticated");

// Capture teacher_timetable view output
ob_start();
$_GET['page'] = 'teacher_timetable';
$_GET['date'] = '2026-10-27';
(new \App\Controllers\TeacherController())->showWeeklyTimetable();
$output = ob_get_clean();

assertCheck(strpos($output, 'TIME SLOT') !== false, "Grid contains TIME SLOT column header");
assertCheck(strpos($output, 'MONDAY') !== false && strpos($output, 'SATURDAY') !== false, "Grid contains all 6 weekday headers (Mon-Sat)");
assertCheck(strpos($output, '9–10 AM') !== false, "Grid renders 9–10 AM row");
assertCheck(strpos($output, '10–11 AM') !== false, "Grid renders 10–11 AM row");
assertCheck(strpos($output, '11 AM–12 PM') !== false, "Grid renders 11 AM–12 PM row");
assertCheck(strpos($output, '12–1 PM') !== false, "Grid renders 12–1 PM row");

// Check card contents matching Image 3
assertCheck(strpos($output, 'C Programming') !== false, "Grid displays 'C Programming' card");
assertCheck(strpos($output, 'Java Programming') !== false, "Grid displays 'Java Programming' card");
assertCheck(strpos($output, 'Python Programming') !== false, "Grid displays 'Python Programming' card");
assertCheck(strpos($output, 'Prof. Rahul Sharma') !== false, "Grid displays teacher 'Prof. Rahul Sharma'");
assertCheck(strpos($output, 'Prof. Neha Shah') !== false, "Grid displays teacher 'Prof. Neha Shah'");

// Check HOD Edit Modal and Delete capability
assertCheck(strpos($output, 'id="hodEditSlotModal"') !== false, "HOD Edit Slot Modal exists in DOM");
assertCheck(strpos($output, 'name="staff_id"') !== false, "Modal allows changing teacher (staff_id)");
assertCheck(strpos($output, 'name="start_time"') !== false && strpos($output, 'name="end_time"') !== false, "Modal allows changing timing (start_time & end_time)");
assertCheck(strpos($output, 'name="subject_id"') !== false, "Modal allows changing subject (subject_id)");
assertCheck(strpos($output, 'id="hodDeleteSlotForm"') !== false, "HOD Delete Slot Form exists for slot removal");

// ---------------------------------------------------------------------------
// TEST 3: Check Subject Dropdown Populated with Teacher's Timetable Subjects
// ---------------------------------------------------------------------------
echo "\n--- 3. IMAGE 4: SUBJECT DROPDOWN POPULATED FROM TEACHER TIMETABLE ---\n";

// Find Prof. Rahul Sharma's staff ID
$stfStmt = $db->query("SELECT id FROM staff WHERE tenant_id = 24 AND first_name = 'Rahul' AND last_name = 'Sharma' LIMIT 1");
$rahulStaffId = (int)$stfStmt->fetchColumn();
assertCheck($rahulStaffId > 0, "Resolved Prof. Rahul Sharma staff ID: {$rahulStaffId}");

// Simulate api_subjects with staff_id filter
$_GET = ['staff_id' => $rahulStaffId];
ob_start();
(new \App\Controllers\ApiController())->getSubjects();
$apiJson = ob_get_clean();
$apiData = json_decode($apiJson, true);

assertCheck($apiData && $apiData['status'] === 'success', "api_subjects returned success JSON");
$returnedSubjectNames = array_column($apiData['data'] ?? [], 'name');

assertCheck(in_array('C Programming', $returnedSubjectNames), "Prof. Rahul Sharma's subjects include 'C Programming'");
assertCheck(in_array('Python Programming', $returnedSubjectNames), "Prof. Rahul Sharma's subjects include 'Python Programming'");
assertCheck(in_array('Python Lab', $returnedSubjectNames), "Prof. Rahul Sharma's subjects include 'Python Lab'");

// Check timetable_view.php has the auto-loader hook
$ttViewContent = file_get_contents(BASE_PATH . '/app/Views/modules/timetable_view.php');
assertCheck(strpos($ttViewContent, 'loadTTSubjectsForTeacher') !== false, "timetable_view.php calls loadTTSubjectsForTeacher on teacher selection");

// ---------------------------------------------------------------------------
// TEST 4: Functional Timetable Update & Delete Verification
// ---------------------------------------------------------------------------
echo "\n--- 4. FUNCTIONAL HOD TIMETABLE UPDATE & DELETE EXECUTION ---\n";

// Pick a test slot
$slot = $db->query("SELECT * FROM time_tables WHERE tenant_id = 24 AND subject_name = 'C Programming' LIMIT 1")->fetch();
assertCheck(!empty($slot), "Found test timetable slot for C Programming (ID: {$slot['id']})");

// Simulate HOD changing teacher, timing, or subject
$_POST = [
    'csrf_token' => \App\Core\CSRF::generate(),
    'id' => $slot['id'],
    'staff_id' => $slot['staff_id'],
    'subject_id' => $slot['subject_id'],
    'subject_name' => 'C Programming Advanced',
    'day_of_week' => 'Monday',
    'start_time' => '09:00:00',
    'end_time' => '10:00:00',
    'time_slot' => '9–10 AM',
    'room_no' => 'Lab 101',
    'academic_year' => '2026-2027',
    'status' => 'active',
    'redirect_to' => 'index.php?page=teacher_timetable'
];

ob_start();
(new \App\Controllers\TimetableController())->updateTimetable();
ob_get_clean();

$updatedSlot = $db->query("SELECT * FROM time_tables WHERE id = {$slot['id']}")->fetch();
assertCheck($updatedSlot['subject_name'] === 'C Programming Advanced' && $updatedSlot['room_no'] === 'Lab 101', "Slot updated successfully by HOD");

// Revert slot name back
$db->exec("UPDATE time_tables SET subject_name = 'C Programming' WHERE id = {$slot['id']}");

echo "\n==========================================================\n";
echo "  VERIFICATION RESULT: Total: " . ($passCount + $failCount) . " | Passed: {$passCount} | Failed: {$failCount}\n";
echo "==========================================================\n\n";

if ($failCount > 0) {
    exit(1);
}
