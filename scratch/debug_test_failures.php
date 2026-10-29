<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Session;
use App\Core\Database;
use App\Core\CSRF;
use App\Controllers\TeacherController;

Session::start();
$_SESSION = [];
Auth::attempt('dr_soap', 'Admin@123');
$user = Auth::user();
$db = Database::getInstance();

echo "User: ID={$user['id']}, role={$user['role_name']}, tenant={$user['tenant_id']}\n";

$tc = new TeacherController();
$ref = new ReflectionClass($tc);
$method = $ref->getMethod('resolveStaffRecord');
$method->setAccessible(true);
$staff = $method->invoke($tc, $user, $db);
echo "Resolved staff: ID=" . ($staff['id'] ?? 'null') . ", full_name=" . ($staff['first_name'] ?? '') . " " . ($staff['last_name'] ?? '') . "\n";

$tt = $db->query("SELECT * FROM time_tables WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
echo "Timetable 1: staff_id={$tt['staff_id']}, tenant_id={$tt['tenant_id']}, subject_name={$tt['subject_name']}\n";

// Let's test showLectureDetails output
$_GET = ['id' => 1, 'date' => '2026-10-12'];
ob_start();
$tc->showLectureDetails();
$out = ob_get_clean();
echo "Lecture details output length: " . strlen($out) . " bytes\n";
echo "Has 'Mark Attendance': " . (strpos($out, 'Mark Attendance') !== false ? 'YES' : 'NO') . "\n";
echo "Flash message: " . Session::getFlash('error') . "\n";

// Now test saveAttendance
$token = CSRF::getToken();
$_POST = [
    'csrf_token' => $token,
    'time_table_id' => 1,
    'lecture_date' => '2026-10-12',
    'attendance' => [6 => 'present'],
    'remarks' => [6 => 'Test remark']
];
echo "Testing saveAttendance...\n";
ob_start();
$tc->saveAttendance();
ob_get_clean();
echo "saveAttendance Flash success: " . Session::getFlash('success') . "\n";
echo "saveAttendance Flash error: " . Session::getFlash('error') . "\n";
