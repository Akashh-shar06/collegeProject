<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== CREATING BACKUP BEFORE ACADEMIC DATA CLEANUP ===\n";

$backup = [
    'timestamp' => date('Y-m-d H:i:s'),
    'courses' => $db->query("SELECT * FROM courses WHERE id IN (22,23,24,25,27,28,29,30,31,32,33,34,39,40)")->fetchAll(PDO::FETCH_ASSOC),
    'departments' => $db->query("SELECT * FROM departments WHERE id IN (120,121,122,123,124,125,126,127)")->fetchAll(PDO::FETCH_ASSOC),
    'academic_levels' => $db->query("SELECT * FROM academic_levels WHERE course_id IN (22,23,24,25,27,28,29,30,31,32,33,34,39,40)")->fetchAll(PDO::FETCH_ASSOC),
    'semesters' => $db->query("SELECT * FROM semesters WHERE course_id IN (22,23,24,25,27,28,29,30,31,32,33,34,39,40)")->fetchAll(PDO::FETCH_ASSOC),
    'students' => $db->query("SELECT * FROM students WHERE course_id IN (24,28,30,32,34,40)")->fetchAll(PDO::FETCH_ASSOC),
    'enrollments' => $db->query("SELECT * FROM student_enrollments WHERE course_id IN (24,28,30,32,34,40)")->fetchAll(PDO::FETCH_ASSOC),
];

$backupFile = __DIR__ . '/backup_academic_cleanup_' . date('Ymd_His') . '.json';
file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));
echo "Backup created successfully at: {$backupFile}\n";
echo "Courses backed up: " . count($backup['courses']) . "\n";
echo "Departments backed up: " . count($backup['departments']) . "\n";
echo "Students backed up: " . count($backup['students']) . "\n";
echo "Enrollments backed up: " . count($backup['enrollments']) . "\n";
