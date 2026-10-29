<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "--- TENANT 24 DEPARTMENTS ---" . PHP_EOL;
$depts = $db->query("SELECT id, name, code FROM departments WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
print_r($depts);

echo "--- TENANT 24 COURSES ---" . PHP_EOL;
$courses = $db->query("SELECT id, department_id, name, code FROM courses WHERE tenant_id = 24 AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
print_r($courses);

echo "--- TENANT 24 STAFF / TEACHERS ---" . PHP_EOL;
$staff = $db->query("SELECT id, user_id, department_id, first_name, last_name, emp_id, designation FROM staff WHERE tenant_id = 24 AND (status = 'active' OR status IS NULL)")->fetchAll(PDO::FETCH_ASSOC);
print_r($staff);
