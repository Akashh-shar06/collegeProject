<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$depts = $db->query("SELECT * FROM departments WHERE tenant_id = 24")->fetchAll();
$courses = $db->query("SELECT * FROM courses WHERE tenant_id = 24")->fetchAll();
echo "DEPTS:\n";
print_r($depts);
echo "COURSES:\n";
print_r($courses);
