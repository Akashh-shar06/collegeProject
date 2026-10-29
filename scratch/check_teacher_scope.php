<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$u = $db->query("SELECT * FROM users WHERE username = 'Jayshree01'")->fetch();
echo "User:\n";
print_r($u);

$stf = $db->query("SELECT * FROM staff WHERE user_id = " . (int)$u['id'])->fetch();
echo "Staff:\n";
print_r($stf);

// Check teacher assignments
$ta = $db->query("SELECT * FROM teacher_assignments WHERE staff_id = " . (int)$stf['id'])->fetchAll();
echo "Teacher assignments count: " . count($ta) . "\n";
print_r($ta);

// Check timetable assignments
$tt = $db->query("SELECT * FROM time_tables WHERE staff_id = " . (int)$stf['id'])->fetchAll();
echo "Timetable count: " . count($tt) . "\n";
print_r($tt);

// Check students for this tenant and department
$stQuery = $db->prepare("
    SELECT COUNT(*) as count 
    FROM students s
    LEFT JOIN batches b ON s.batch_id = b.id
    LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id
    LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id
    WHERE s.tenant_id = :tid AND (COALESCE(s.department_id, c.department_id) = :dept_id)
");
$stQuery->execute([':tid' => $u['tenant_id'], ':dept_id' => $stf['department_id']]);
echo "Students in teacher's dept (" . $stf['department_id'] . "): " . $stQuery->fetchColumn() . "\n";

// Check students total in tenant 24
$stTotal = $db->query("SELECT COUNT(*) FROM students WHERE tenant_id = " . (int)$u['tenant_id'])->fetchColumn();
echo "Total students in tenant 24: " . $stTotal . "\n";

// Check students department_id distribution in tenant 24
$dist = $db->query("
    SELECT s.department_id, c.department_id as course_dept_id, COUNT(*) as cnt 
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE s.tenant_id = " . (int)$u['tenant_id'] . "
    GROUP BY s.department_id, c.department_id
")->fetchAll();
echo "Student Dept Distribution:\n";
print_r($dist);
