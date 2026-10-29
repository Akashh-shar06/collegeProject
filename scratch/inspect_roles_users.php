<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();
echo "=== ROLES ===\n";
$roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $r) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Label: " . ($r['label'] ?? $r['display_name'] ?? '') . "\n";
}

echo "\n=== USERS (HOD or similar) ===\n";
$users = $db->query("SELECT u.id, u.username, u.role_id, r.name as role_name, r.display_name as role_label, u.tenant_id FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.username LIKE '%hod%' OR r.name LIKE '%hod%' OR r.display_name LIKE '%hod%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    print_r($u);
}

echo "\n=== ALL USERS ===\n";
$allUsers = $db->query("SELECT u.id, u.username, u.role_id, r.name as role_name, r.display_name as role_label FROM users u LEFT JOIN roles r ON u.role_id = r.id LIMIT 35")->fetchAll(PDO::FETCH_ASSOC);
foreach ($allUsers as $u) {
    echo "User: {$u['id']} | {$u['username']} | Role: {$u['role_name']} ({$u['role_label']})\n";
}
