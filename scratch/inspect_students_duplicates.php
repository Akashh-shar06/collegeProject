<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== STUDENTS IN COURSES 24, 28, 30, 32, 34, 40 ===\n";
$st = $db->query('SELECT id, user_id, unique_id, admission_no, first_name, last_name, course_id, status FROM students WHERE course_id IN (24, 28, 30, 32, 34, 40)')->fetchAll(PDO::FETCH_ASSOC);
print_r($st);

echo "=== ENROLLMENTS IN COURSES 24, 28, 30, 32, 34, 40 ===\n";
$en = $db->query('SELECT id, student_id, course_id, academic_year, status FROM student_enrollments WHERE course_id IN (24, 28, 30, 32, 34, 40)')->fetchAll(PDO::FETCH_ASSOC);
print_r($en);

echo "=== EXAM RESULTS FOR THESE STUDENTS ===\n";
$er = $db->query('SELECT id, exam_id, student_id, marks_obtained, grade FROM exam_results WHERE student_id IN (SELECT id FROM students WHERE course_id IN (24, 28, 30, 32, 34, 40))')->fetchAll(PDO::FETCH_ASSOC);
print_r($er);

echo "=== ATTENDANCE FOR THESE STUDENTS ===\n";
$att = $db->query('SELECT id, student_id, date, status FROM student_attendance WHERE student_id IN (SELECT id FROM students WHERE course_id IN (24, 28, 30, 32, 34, 40))')->fetchAll(PDO::FETCH_ASSOC);
print_r($att);
