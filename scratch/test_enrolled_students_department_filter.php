<?php
/**
 * Campus360 Enterprise ERP - Enrolled Students Department Filter Test Runner
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

echo "========================================================\n";
echo "   ENROLLED STUDENTS DEPARTMENT FILTER TEST RUNNER      \n";
echo "========================================================\n\n";

function runStudentMisSubprocess(?int $deptId): string {
    $base = addslashes(BASE_PATH);
    $deptCode = $deptId !== null ? "\$_GET['dept_id'] = {$deptId}; \\App\\Core\\Session::set('selected_dept_id', {$deptId});" : "\$_GET['dept_id'] = 'all'; \\App\\Core\\Session::remove('selected_dept_id');";
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; \\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); {$deptCode} \$_GET['name'] = 'student_mis'; (new \\App\\Controllers\\ModuleController())->showModule();";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    $output = (string)shell_exec($cmd);
    
    $start = strpos($output, '<tbody>');
    $end = strpos($output, '</tbody>');
    if ($start !== false && $end !== false) {
        return substr($output, $start, $end - $start);
    }
    return $output;
}

// 1. Test CSE Department (Dept #1)
echo "1. Testing Enrolled Students Table Body for Department #1 (CSE)...\n";
$tbodyDept1 = runStudentMisSubprocess(1);

if (str_contains($tbodyDept1, '24CSE001') && !str_contains($tbodyDept1, '25IT002')) {
    echo "  [PASS] CSE Student roll number '24CSE001' in table; IT roll number '25IT002' excluded!\n";
} else {
    echo "  [FAIL] Incorrect table rows for Department #1.\n";
    exit(1);
}

// 2. Test IT Department (Dept #2)
echo "2. Testing Enrolled Students Table Body for Department #2 (IT)...\n";
$tbodyDept2 = runStudentMisSubprocess(2);

if (str_contains($tbodyDept2, '25IT002') && !str_contains($tbodyDept2, '24CSE001')) {
    echo "  [PASS] IT Student roll number '25IT002' in table; CSE roll number '24CSE001' excluded!\n";
} else {
    echo "  [FAIL] Incorrect table rows for Department #2.\n";
    exit(1);
}

// 3. Test All Departments (Global View)
echo "3. Testing Enrolled Students Table Body for All Departments...\n";
$tbodyAll = runStudentMisSubprocess(null);

if (str_contains($tbodyAll, '24CSE001') && str_contains($tbodyAll, '25IT002')) {
    echo "  [PASS] Both CSE ('24CSE001') & IT ('25IT002') present in table for Global View!\n";
} else {
    echo "  [FAIL] Incorrect table rows for Global View.\n";
    exit(1);
}

echo "\n========================================================\n";
echo "SUCCESS: Enrolled Students Department Filter is 100% OPERATIONAL!\n";
echo "========================================================\n";
