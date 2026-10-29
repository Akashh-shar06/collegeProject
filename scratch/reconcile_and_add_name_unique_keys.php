<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "Reconciling student 308 to canonical course 40...\n";
$db->exec("UPDATE students SET course_id = 40 WHERE course_id = 42");
$db->exec("UPDATE student_enrollments SET course_id = 40 WHERE course_id = 42");

echo "Cleaning course 42 levels and semesters...\n";
$db->exec("DELETE FROM semesters WHERE course_id = 42");
$db->exec("DELETE FROM academic_levels WHERE course_id = 42");
$db->exec("DELETE FROM courses WHERE id = 42");

echo "Checking remaining duplicate course names in any department...\n";
$remCourses = $db->query("SELECT tenant_id, department_id, LOWER(TRIM(name)) as n, COUNT(*) as c FROM courses WHERE status = 'active' GROUP BY tenant_id, department_id, LOWER(TRIM(name)) HAVING c > 1")->fetchAll();
print_r($remCourses);

echo "Checking remaining duplicate department names in any tenant...\n";
$remDepts = $db->query("SELECT tenant_id, LOWER(TRIM(name)) as n, COUNT(*) as c FROM departments WHERE status = 'active' GROUP BY tenant_id, LOWER(TRIM(name)) HAVING c > 1")->fetchAll();
print_r($remDepts);

try {
    echo "Adding unique key unique_tenant_dept_course_name to courses...\n";
    $db->exec("ALTER TABLE courses ADD UNIQUE KEY unique_tenant_dept_course_name (tenant_id, department_id, name)");
    echo "Unique key added successfully to courses.\n";
} catch (\PDOException $e) {
    echo "Courses key note: " . $e->getMessage() . "\n";
}

try {
    echo "Adding unique key unique_tenant_dept_name to departments...\n";
    $db->exec("ALTER TABLE departments ADD UNIQUE KEY unique_tenant_dept_name (tenant_id, name)");
    echo "Unique key added successfully to departments.\n";
} catch (\PDOException $e) {
    echo "Departments key note: " . $e->getMessage() . "\n";
}
