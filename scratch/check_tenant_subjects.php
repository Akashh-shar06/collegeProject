<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$s = $db->query("SELECT id, tenant_id, department_id, course_id, name, code FROM subjects WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
echo "Subjects in tenant 24: " . count($s) . PHP_EOL;
print_r($s);

$tt = $db->query("SELECT id, tenant_id, department_id, course_id, subject_id, subject_name, staff_id FROM time_tables WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
echo "Timetable slots in tenant 24: " . count($tt) . PHP_EOL;
print_r($tt);
