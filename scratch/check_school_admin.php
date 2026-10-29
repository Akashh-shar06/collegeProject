<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$users = $db->query("SELECT u.id, u.username, u.tenant_id, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'school_admin' LIMIT 5")->fetchAll();
print_r($users);
