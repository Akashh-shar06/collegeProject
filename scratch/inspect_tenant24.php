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

echo "=== STUDENTS IN TENANT 24 ===\n";
$st = $db->query("
    SELECT s.id, s.admission_no, s.roll_no, s.first_name, s.last_name, s.department_id, s.course_id, s.batch_id, s.standard_id, s.section_id,
           c.name as course_name, d.name as dept_name
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE s.tenant_id = 24
")->fetchAll(PDO::FETCH_ASSOC);
print_r($st);

echo "=== STAFF IN TENANT 24 ===\n";
$staff = $db->query("
    SELECT st.id, st.user_id, st.emp_id, st.first_name, st.last_name, st.designation, st.department_id, d.name as dept_name
    FROM staff st
    LEFT JOIN departments d ON st.department_id = d.id
    WHERE st.tenant_id = 24
")->fetchAll(PDO::FETCH_ASSOC);
print_r($staff);

echo "=== TIME_TABLES IN TENANT 24 ===\n";
$tt = $db->query("
    SELECT t.id, t.department_id, t.course_id, t.staff_id, t.subject_name, t.day_of_week, t.time_slot, t.room_no
    FROM time_tables t
    WHERE t.tenant_id = 24
")->fetchAll(PDO::FETCH_ASSOC);
print_r($tt);
