<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

$db = Database::getInstance();
$users = $db->query("SELECT id, username, password_hash, role_id, tenant_id FROM users WHERE username IN ('Jayshree01', 'dr_soap', 'Sahil02', 'admin')")->fetchAll();

foreach ($users as $u) {
    echo "User: " . $u['username'] . "\n";
    foreach (['Teacher@123', 'Admin@123', 'Password@123', 'password', '123456', 'Pass@123'] as $p) {
        if (password_verify($p, $u['password_hash'])) {
            echo "  Password match: '$p'\n";
        }
    }
}
