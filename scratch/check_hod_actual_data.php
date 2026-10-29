<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$tenantId = 24;
$deptId = 116;

echo "=== HOD DEPARTMENT DATA (Tenant: 24, Dept: 116) ===\n\n";

// Teachers
$teachers = $db->query("
    SELECT s.id, s.emp_id, s.first_name, s.last_name, s.designation, s.email, s.phone,
           (SELECT COUNT(*) FROM time_tables tt WHERE tt.staff_id = s.id) as slot_count
    FROM staff s 
    WHERE s.tenant_id = {$tenantId} AND s.department_id = {$deptId}
    ORDER BY s.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "Teachers count: " . count($teachers) . "\n";
print_r($teachers);

// Assigned subjects per teacher
echo "--- Assigned Subjects for these teachers ---\n";
foreach ($teachers as $t) {
    $subs = $db->query("
        SELECT DISTINCT COALESCE(sub.name, tt.subject_name) as subject_name, sub.code as subject_code
        FROM time_tables tt
        LEFT JOIN subjects sub ON tt.subject_id = sub.id
        WHERE tt.staff_id = {$t['id']}
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "Teacher {$t['first_name']} {$t['last_name']} (EMP: {$t['emp_id']}): " . json_encode($subs) . "\n";
}

// Timetable slots
$slots = $db->query("
    SELECT tt.*, 
           st.first_name, st.last_name, st.emp_id,
           c.name as course_name, c.code as course_code,
           sec.name as section_name,
           sub.name as resolved_subject_name, sub.code as subject_code,
           cr.room_number as classroom_number
    FROM time_tables tt
    LEFT JOIN staff st ON tt.staff_id = st.id
    LEFT JOIN courses c ON tt.course_id = c.id
    LEFT JOIN sections sec ON tt.section_id = sec.id
    LEFT JOIN subjects sub ON tt.subject_id = sub.id
    LEFT JOIN classrooms cr ON tt.classroom_id = cr.id
    WHERE tt.tenant_id = {$tenantId}
      AND (tt.department_id = {$deptId} OR st.department_id = {$deptId} OR c.department_id = {$deptId})
    ORDER BY FIELD(tt.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), tt.start_time ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "Timetable slots count: " . count($slots) . "\n";
print_r($slots);

// Courses
$courses = $db->query("
    SELECT c.*, 
           (SELECT COUNT(DISTINCT s.id) FROM students s WHERE s.course_id = c.id) as student_count,
           (SELECT COUNT(*) FROM subjects sub WHERE sub.course_id = c.id) as subject_count
    FROM courses c
    WHERE c.tenant_id = {$tenantId} AND c.department_id = {$deptId}
    ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "Courses count: " . count($courses) . "\n";
print_r($courses);

// Students
$students = $db->query("
    SELECT DISTINCT s.id, s.admission_no, s.roll_no, s.first_name, s.last_name, s.gender, s.email, s.phone,
           s.course_id, c.name as course_name, c.code as course_code,
           s.department_id, d.name as department_name,
           s.status
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE s.tenant_id = {$tenantId}
      AND (s.department_id = {$deptId} OR s.course_id IN (SELECT id FROM courses WHERE department_id = {$deptId}))
    ORDER BY s.first_name ASC, s.last_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "Students count: " . count($students) . "\n";
echo "First 5 students:\n";
print_r(array_slice($students, 0, 5));
