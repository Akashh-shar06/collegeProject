<?php
require __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
echo "--- ROLES ---\n";
$roles = $db->query("SELECT id, name, display_name FROM roles")->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);

echo "--- ROLE_MODULES FOR HR ---\n";
$rm = $db->query("SELECT rm.*, r.name as role_name FROM role_modules rm JOIN roles r ON rm.role_id = r.id WHERE r.name LIKE '%hr%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($rm);

echo "--- PERMISSIONS FOR HR ---\n";
$rp = $db->query("SELECT rp.*, r.name as role_name, p.name as perm_name FROM role_permissions rp JOIN roles r ON rp.role_id = r.id JOIN permissions p ON rp.permission_id = p.id WHERE r.name LIKE '%hr%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($rp);
