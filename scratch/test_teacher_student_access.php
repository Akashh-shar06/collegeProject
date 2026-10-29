<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;

$db = Database::getInstance();
Auth::attempt('Jayshree01', 'Admin@123');
$user = Auth::user();
echo "Logged in as: " . $user['username'] . " (role: " . $user['role_name'] . ", tenant: " . $user['tenant_id'] . ")\n";

echo "Can view student_mis? " . (Permission::can('student_mis') ? 'YES' : 'NO') . "\n";
echo "Can view id_cards? " . (Permission::can('id_cards') ? 'YES' : 'NO') . "\n";
echo "Can view academic_structure? " . (Permission::can('academic_structure') ? 'YES' : 'NO') . "\n";

// Let's test filterStudentsForIdCards with dept 0
$_GET['department_id'] = 0;
$_GET['academic_year'] = 'all';

ob_start();
(new App\Controllers\ApiController())->filterStudentsForIdCards();
$resp = ob_get_clean();
echo "filterStudentsForIdCards (dept 0, all) response:\n" . substr($resp, 0, 300) . "\n";

// Now test with dept 116
$_GET['department_id'] = 116;
$_GET['academic_year'] = 'FY';
ob_start();
(new App\Controllers\ApiController())->filterStudentsForIdCards();
$resp2 = ob_get_clean();
$json2 = json_decode($resp2, true);
echo "filterStudentsForIdCards (dept 116, FY) count: " . ($json2['count'] ?? 'ERROR: ' . ($json2['message'] ?? '')) . "\n";

// Now test getModuleData for students
$_GET['module'] = 'students';
ob_start();
(new App\Controllers\ApiController())->getModuleData();
$resp3 = ob_get_clean();
echo "getModuleData (students):\n" . $resp3 . "\n";
