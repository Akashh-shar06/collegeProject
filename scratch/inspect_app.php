<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== TEACHER USERS ===\n";
$teachers = $db->query("SELECT u.id, u.tenant_id, u.username, u.email, r.name as role_name, s.id as staff_id, s.first_name, s.last_name, s.department_id 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    LEFT JOIN staff s ON u.id = s.user_id 
    WHERE r.name = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);
print_r($teachers);

echo "\n=== ADMIN USERS ===\n";
$admins = $db->query("SELECT u.id, u.tenant_id, u.username, u.email, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE r.name IN ('super_admin', 'school_admin', 'principal')")->fetchAll(PDO::FETCH_ASSOC);
print_r($admins);

echo "\n=== TIME TABLES COLUMNS ===\n";
$ttCols = $db->query("SHOW COLUMNS FROM time_tables")->fetchAll(PDO::FETCH_ASSOC);
print_r($ttCols);

echo "\n=== EXISTING TIMETABLE ROWS ===\n";
$tts = $db->query("SELECT * FROM time_tables LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($tts);

echo "\n=== EXISTING STUDENT ATTENDANCE COLUMNS ===\n";
$attCols = $db->query("SHOW COLUMNS FROM student_attendance")->fetchAll(PDO::FETCH_ASSOC);
print_r($attCols);
