<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== HOD USERS IN ANY TENANT ===\n";
$hods = $db->query("SELECT u.id, u.username, u.email, u.tenant_id, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'hod'")->fetchAll(PDO::FETCH_ASSOC);
print_r($hods);

echo "=== DEPARTMENTS IN TENANT 1 ===\n";
$depts1 = $db->query("SELECT id, name, code FROM departments WHERE tenant_id = 1 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($depts1);
