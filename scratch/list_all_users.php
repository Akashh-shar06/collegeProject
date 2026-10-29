<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$users = $db->query("
    SELECT u.id, u.tenant_id, u.username, u.email, u.role_id, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    WHERE u.tenant_id IN (1, 24)
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id']} | Tenant: {$u['tenant_id']} | User: {$u['username']} | Role: {$u['role_name']}" . PHP_EOL;
}
