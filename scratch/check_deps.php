<?php
define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
$db = Database::getInstance();

echo "=== CHECKING DEPENDENT REFERENCES FOR COURSES (24, 28, 30, 32, 34) ===\n";
$courseIds = [24, 28, 30, 32, 34];
foreach (['students', 'batches', 'time_tables', 'subjects', 'online_exams'] as $tbl) {
    $in = implode(',', $courseIds);
    $cnt = $db->query("SELECT course_id, COUNT(*) as cnt FROM $tbl WHERE course_id IN ($in) GROUP BY course_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "$tbl with course_id in ($in):\n";
    print_r($cnt);
}

echo "=== CHECKING DEPENDENT REFERENCES FOR COURSES (22, 23, 25, 27, 29, 31, 33) ===\n";
$courseIds2 = [22, 23, 25, 27, 29, 31, 33];
foreach (['students', 'batches', 'time_tables', 'subjects', 'online_exams'] as $tbl) {
    $in = implode(',', $courseIds2);
    $cnt = $db->query("SELECT course_id, COUNT(*) as cnt FROM $tbl WHERE course_id IN ($in) GROUP BY course_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "$tbl with course_id in ($in):\n";
    print_r($cnt);
}

echo "=== CHECKING DEPENDENT REFERENCES FOR DEPARTMENTS (120..126) ===\n";
$deptIds = [120, 121, 122, 123, 124, 125, 126];
$inDept = implode(',', $deptIds);
foreach (['courses', 'staff', 'students', 'sections', 'time_tables'] as $tbl) {
    $cnt = $db->query("SELECT department_id, COUNT(*) as cnt FROM $tbl WHERE department_id IN ($inDept) GROUP BY department_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "$tbl with department_id in ($inDept):\n";
    print_r($cnt);
}

echo "=== CLASSES DUPLICATES ===\n";
$classes = $db->query("
    SELECT tenant_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids 
    FROM classes 
    GROUP BY tenant_id, name 
    HAVING cnt > 1
")->fetchAll(PDO::FETCH_ASSOC);
print_r($classes);

echo "=== SECTIONS DUPLICATES ===\n";
$sections = $db->query("
    SELECT tenant_id, class_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids 
    FROM sections 
    GROUP BY tenant_id, class_id, name 
    HAVING cnt > 1
")->fetchAll(PDO::FETCH_ASSOC);
print_r($sections);

echo "=== SUBJECTS DUPLICATES ===\n";
$subjects = $db->query("
    SELECT tenant_id, code, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids 
    FROM subjects 
    GROUP BY tenant_id, code 
    HAVING cnt > 1
")->fetchAll(PDO::FETCH_ASSOC);
print_r($subjects);
