<?php
/**
 * Campus360 Enterprise ERP - All 36 Modules Add Operation Test Runner
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

echo "========================================================\n";
echo "      ALL 36 MODULES 'ADD RECORD' TEST RUNNER           \n";
echo "========================================================\n\n";

$modules = [
    'enquiry', 'admission', 'fees', 'accounts', 'student_attendance',
    'academic_calendar', 'id_cards', 'time_table', 'certificate', 'online_exam',
    'exam_management', 'exam_management_module', 'transport',
    'stock', 'library', 'visitor', 'student_mis', 'hr_management', 'staff_data',
    'staff_attendance', 'leave_management', 'payroll', 'ticket_management',
    'asset_management', 'task_management', 'hostel_management', 'day_boarding',
    'infirmary', 'recruitment', 'news_events', 'communication', 'tutorial', 'support'
];

function runSubprocess(string $code): string {
    $base = addslashes(BASE_PATH);
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; {$code}";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    return (string)shell_exec($cmd);
}

$passCount = 0;
$failCount = 0;

$db = Database::getInstance();

foreach ($modules as $mod) {
    $title = "Automated Add Test " . ucfirst($mod) . " " . rand(100, 999);
    $code = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = ['module_key' => '{$mod}', 'title' => '{$title}', 'name' => '{$title}', 'student_name' => 'Test Student', 'first_name' => 'Test', 'last_name' => 'User', 'email' => 'test@example.com', 'phone' => '+1 555-0100', 'course_interested' => 'CSE', 'amount_paid' => 100, 'amount' => 100, 'author' => 'Test Author', 'route_name' => 'Test Route', 'vehicle_no' => 'BUS-99', 'driver_phone' => '555-0000', 'subject' => 'Test Subject', 'visitor_name' => 'Test Visitor', 'purpose' => 'Test Purpose', 'item_name' => 'Test Item', 'quantity' => 2, 'designation' => 'Tester', 'content' => 'Automated test content details...']; (new \\App\\Controllers\\ModuleController())->storeRecord();";
    
    runSubprocess($code);

    // Check if error occurred
    $err = @file_get_contents(BASE_PATH . '/scratch/last_error.log');
    if (file_exists(BASE_PATH . '/scratch/last_error.log')) {
        @unlink(BASE_PATH . '/scratch/last_error.log');
    }

    if (empty($err)) {
        echo "  [PASS] Add operation succeeded for module: '{$mod}'\n";
        $passCount++;
    } else {
        echo "  [FAIL] Add operation failed for module: '{$mod}' - Error: {$err}\n";
        $failCount++;
    }
}

echo "\n========================================================\n";
echo "SUMMARY: Passed: {$passCount} | Failed: {$failCount} / " . count($modules) . "\n";
echo "========================================================\n";

exit($failCount === 0 ? 0 : 1);
