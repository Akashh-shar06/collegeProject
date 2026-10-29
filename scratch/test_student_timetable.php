<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;

$db = Database::getInstance();

$ok = Auth::attempt('john_student', 'Admin@123');
echo "Auth attempt result: " . ($ok ? 'SUCCESS' : 'FAILED') . "\n";
$user = Auth::user();
echo "Logged in as: " . $user['username'] . " (role: " . $user['role_name'] . ", tenant: " . $user['tenant_id'] . ")\n";

// 1. Test backend permission: Attempt to delete timetable as student
$_POST['id'] = 1;
ob_start();
try {
    (new App\Controllers\TimetableController())->deleteTimetable();
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
$out = ob_get_clean();
$flash = \App\Core\Session::getFlash('error');
echo "Attempted deleteTimetable as student. Flash error: " . ($flash ?: 'NONE') . "\n";

// 2. Test showTimetable view rendering for student
ob_start();
try {
    (new App\Controllers\TimetableController())->showTimetable();
    $viewHtml = ob_get_clean();
    echo "Student Timetable View Rendered Successfully!\n";
    echo "Contains 'Student Timetable': " . (strpos($viewHtml, 'Student Timetable') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains 'VIEW ONLY': " . (strpos($viewHtml, 'VIEW ONLY') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains '+ Add Timetable Entry': " . (strpos($viewHtml, '+ Add Timetable Entry') !== false ? 'YES (UNEXPECTED)' : 'NO (CORRECT)') . "\n";
    echo "Contains 'Delete Slot': " . (strpos($viewHtml, 'Delete Slot') !== false ? 'YES (UNEXPECTED)' : 'NO (CORRECT)') . "\n";
    echo "Contains 'Today is': " . (strpos($viewHtml, 'Today is') !== false ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "Error rendering showTimetable: " . $e->getMessage() . "\n";
}
