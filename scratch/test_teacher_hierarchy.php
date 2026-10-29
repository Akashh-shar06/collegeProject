<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Permission;
use App\Controllers\AcademicController;

Auth::attempt('Jayshree01', 'Admin@123');
$user = Auth::user();
echo "Logged in as: " . $user['username'] . " (role: " . $user['role_name'] . ")\n";
echo "Can access academic_structure? " . (Permission::can('academic_structure') ? 'YES' : 'NO') . "\n";
echo "Can manage academic_structure? " . (Permission::canManage('academic_structure') ? 'YES' : 'NO') . "\n";

$data = AcademicController::getAcademicHierarchyData((int)$user['tenant_id'], false, null);
echo "Hierarchy Stats:\n";
print_r($data['stats']);

foreach ($data['departments'] as $d) {
    echo "Dept: {$d['name']} ({$d['code']}), Course count: {$d['course_count']}, Students: {$d['student_count']}\n";
    foreach ($d['courses'] as $c) {
        echo "  Course: {$c['name']} ({$c['code']}), Total: {$c['total_students']}\n";
        foreach ($c['years'] as $y) {
            echo "    {$y['name']}: " . count($y['students']) . " students\n";
        }
    }
}
