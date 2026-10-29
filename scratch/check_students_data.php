<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$students = $db->query("SELECT id, tenant_id, admission_no, roll_no, course_id, department_id, standard_id, class_id, status FROM students WHERE tenant_id IN (1, 24) LIMIT 20")->fetchAll();
print_r($students);
