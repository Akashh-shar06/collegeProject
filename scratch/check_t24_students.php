<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$students = $db->query("SELECT id, user_id, admission_no, roll_no, first_name, last_name, department_id, course_id, standard_id, class_id FROM students WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
print_r($students);
