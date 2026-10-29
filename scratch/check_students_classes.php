<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();

echo "=== STUDENTS CLASS/STANDARD/BATCH/COURSE IN TENANT 24 ===\n";
$stmt = $db->query("SELECT id, admission_no, first_name, course_id, department_id, class_id, standard_id, batch_id, status FROM students WHERE tenant_id = 24 LIMIT 15");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . "\n";
}

echo "\n=== DISTINCT CLASS_ID & STANDARD_ID IN STUDENTS ===\n";
$stmt = $db->query("SELECT tenant_id, class_id, standard_id, COUNT(*) as c FROM students GROUP BY tenant_id, class_id, standard_id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . "\n";
}
