<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();

echo "--- DEPARTMENTS ---" . PHP_EOL;
$depts = $db->query("SELECT id, tenant_id, name, code, status FROM departments")->fetchAll(PDO::FETCH_ASSOC);
print_r($depts);

echo "--- COURSES ---" . PHP_EOL;
$courses = $db->query("SELECT id, tenant_id, department_id, name, code, duration_years, status FROM courses")->fetchAll(PDO::FETCH_ASSOC);
print_r($courses);

echo "--- ACADEMIC LEVELS ---" . PHP_EOL;
$levels = $db->query("SELECT id, tenant_id, course_id, name, level_order FROM academic_levels")->fetchAll(PDO::FETCH_ASSOC);
print_r($levels);

echo "--- CLASSES ---" . PHP_EOL;
$classes = $db->query("SELECT id, tenant_id, name, code FROM classes")->fetchAll(PDO::FETCH_ASSOC);
print_r($classes);

echo "--- STUDENTS COUNT BY DEPT, COURSE, CLASS ---" . PHP_EOL;
$stStats = $db->query("
    SELECT tenant_id, department_id, course_id, class_id, COUNT(*) as cnt 
    FROM students 
    GROUP BY tenant_id, department_id, course_id, class_id
")->fetchAll(PDO::FETCH_ASSOC);
print_r($stStats);
