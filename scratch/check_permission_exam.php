<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Permission;

Auth::attempt('Jayshree01', 'Admin@123');
$user = Auth::user();
echo "User: " . $user['username'] . " role: " . $user['role_name'] . "\n";
echo "getAccessLevel('online_exam'): " . Permission::getAccessLevel('online_exam') . "\n";
echo "canAction('online_exam', 'delete'): " . (Permission::canAction('online_exam', 'delete') ? 'YES' : 'NO') . "\n";
