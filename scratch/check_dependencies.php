<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$courseIds = [24, 28, 30, 32, 34, 40, 22, 23, 25, 27, 29, 31, 33, 39];
echo "=== CHECKING DEPENDENCIES FOR SUSPECTED DUPLICATE COURSES ===\n";

foreach ($courseIds as $cid) {
    $c = $db->query("SELECT * FROM courses WHERE id = {$cid}")->fetch(PDO::FETCH_ASSOC);
    if (!$c) continue;
    $students = (int)$db->query("SELECT COUNT(*) FROM students WHERE course_id = {$cid}")->fetchColumn();
    $enrollments = (int)$db->query("SELECT COUNT(*) FROM student_enrollments WHERE course_id = {$cid}")->fetchColumn();
    $batches = (int)$db->query("SELECT COUNT(*) FROM batches WHERE course_id = {$cid}")->fetchColumn();
    $subjects = (int)$db->query("SELECT COUNT(*) FROM subjects WHERE course_id = {$cid}")->fetchColumn();
    $timetables = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE course_id = {$cid}")->fetchColumn();
    $semesters = (int)$db->query("SELECT COUNT(*) FROM semesters WHERE course_id = {$cid}")->fetchColumn();
    $levels = (int)$db->query("SELECT COUNT(*) FROM academic_levels WHERE course_id = {$cid}")->fetchColumn();
    $offerings = (int)$db->query("SELECT COUNT(*) FROM subject_offerings WHERE course_id = {$cid}")->fetchColumn();

    echo "Course ID: {$cid} | Name: {$c['name']} | Code: {$c['code']} | Status: {$c['status']}\n";
    echo "  -> Students: {$students}, Enrollments: {$enrollments}, Batches: {$batches}, Subjects: {$subjects}, TimeTables: {$timetables}, Semesters: {$semesters}, Levels: {$levels}, Offerings: {$offerings}\n";
}

echo "\n=== CHECKING DEPARTMENTS (120-127) ===\n";
foreach (range(120, 127) as $did) {
    $d = $db->query("SELECT * FROM departments WHERE id = {$did}")->fetch(PDO::FETCH_ASSOC);
    if (!$d) continue;
    $staff = (int)$db->query("SELECT COUNT(*) FROM staff WHERE department_id = {$did}")->fetchColumn();
    $courses = (int)$db->query("SELECT COUNT(*) FROM courses WHERE department_id = {$did}")->fetchColumn();
    $students = (int)$db->query("SELECT COUNT(*) FROM students WHERE department_id = {$did}")->fetchColumn();
    $timetables = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE department_id = {$did}")->fetchColumn();
    echo "Dept ID: {$did} | Name: {$d['name']} | Code: {$d['code']} | Status: {$d['status']}\n";
    echo "  -> Staff: {$staff}, Courses: {$courses}, Students: {$students}, TimeTables: {$timetables}\n";
}

echo "\n=== ALL DEPARTMENTS IN TENANT 24 ===\n";
$depts24 = $db->query("SELECT d.*, (SELECT COUNT(*) FROM courses c WHERE c.department_id = d.id) as course_count, (SELECT COUNT(*) FROM staff s WHERE s.department_id = d.id) as staff_count FROM departments d WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
foreach ($depts24 as $d) {
    echo "ID: {$d['id']} | Name: {$d['name']} | Code: {$d['code']} | Courses: {$d['course_count']} | Staff: {$d['staff_count']}\n";
}

echo "\n=== ALL COURSES IN TENANT 24 ===\n";
$courses24 = $db->query("SELECT c.*, (SELECT COUNT(*) FROM students s WHERE s.course_id = c.id) as student_count, (SELECT COUNT(*) FROM time_tables tt WHERE tt.course_id = c.id) as tt_count FROM courses c WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
foreach ($courses24 as $c) {
    echo "ID: {$c['id']} | DeptID: {$c['department_id']} | Name: {$c['name']} | Code: {$c['code']} | Status: {$c['status']} | Students: {$c['student_count']} | Timetable: {$c['tt_count']}\n";
}
