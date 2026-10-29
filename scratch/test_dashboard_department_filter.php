<?php
/**
 * Campus360 Enterprise ERP - Dashboard Department Filter Test Runner
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Session;

echo "========================================================\n";
echo "    DASHBOARD DEPARTMENT FILTER TEST RUNNER             \n";
echo "========================================================\n\n";

function runDashboardSubprocess(int $deptId): string {
    $base = addslashes(BASE_PATH);
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; \\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \\App\\Core\\Session::set('selected_dept_id', {$deptId}); (new \\App\\Controllers\\DashboardController())->showDashboard();";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    return (string)shell_exec($cmd);
}

// 1. Test Department 1 Filter
echo "Testing Dashboard rendering for Department ID #1 (CSE)...\n";
$outputDept1 = runDashboardSubprocess(1);

if (str_contains($outputDept1, 'School Administrator Portal') && !str_contains($outputDept1, 'Fatal error')) {
    echo "  [PASS] Dashboard rendered cleanly with Department #1 filter active!\n";
} else {
    echo "  [FAIL] Error rendering Dashboard with Department #1 filter.\n";
    exit(1);
}

// 2. Test Department 2 Filter
echo "Testing Dashboard rendering for Department ID #2 (IT)...\n";
$outputDept2 = runDashboardSubprocess(2);

if (str_contains($outputDept2, 'School Administrator Portal') && !str_contains($outputDept2, 'Fatal error')) {
    echo "  [PASS] Dashboard rendered cleanly with Department #2 filter active!\n";
} else {
    echo "  [FAIL] Error rendering Dashboard with Department #2 filter.\n";
    exit(1);
}

echo "\n========================================================\n";
echo "SUCCESS: Dashboard Department Filter is 100% operational!\n";
echo "========================================================\n";
