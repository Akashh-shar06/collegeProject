<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

// 1. Update user 121 (anish11) to role_id = 15 (hod)
$db->exec("UPDATE users SET role_id = 15 WHERE id = 121 OR username = 'anish11'");

// 2. Update staff 123 (Anish11) designation to HOD / Head of Department
$db->exec("UPDATE staff SET designation = 'HOD / Head of Department' WHERE user_id = 121 OR email = 'Anish11@gmail.com'");

// 3. Ensure role 15 exists with correct names
$hodRole = $db->query("SELECT * FROM roles WHERE id = 15 OR name = 'hod'")->fetch(PDO::FETCH_ASSOC);
if (!$hodRole) {
    $db->exec("INSERT INTO roles (id, name, display_name, description) VALUES (15, 'hod', 'HOD / Head of Department', 'Head of Academic Department management, faculty oversight & curriculum')");
} else {
    $db->exec("UPDATE roles SET name = 'hod', display_name = 'HOD / Head of Department' WHERE id = {$hodRole['id']}");
}

// 4. Ensure demo 'hod' user exists with password Admin@123 for seamless testing
$demoHod = $db->query("SELECT * FROM users WHERE username = 'hod'")->fetch(PDO::FETCH_ASSOC);
$hash = password_hash('Admin@123', PASSWORD_BCRYPT);
if (!$demoHod) {
    $db->exec("INSERT INTO users (tenant_id, role_id, username, email, password_hash, status) VALUES (24, 15, 'hod', 'hod@campus360.edu', '{$hash}', 'active')");
    $newUid = (int)$db->lastInsertId();
    $db->exec("INSERT INTO staff (tenant_id, user_id, emp_id, first_name, last_name, designation, department_id, email, phone, status, salary) VALUES (24, {$newUid}, 'EMP-HOD-01', 'Head', 'Department', 'HOD / Head of Department', 116, 'hod@campus360.edu', '9876500999', 'active', 65000.00)");
} else {
    $db->exec("UPDATE users SET role_id = 15, password_hash = '{$hash}', status = 'active' WHERE id = {$demoHod['id']}");
}

echo "Database updated successfully!\n";
$u = $db->query("SELECT u.id, u.username, u.role_id, r.name as role_name, r.display_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username IN ('anish11', 'hod')")->fetchAll(PDO::FETCH_ASSOC);
print_r($u);
