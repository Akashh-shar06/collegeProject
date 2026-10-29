<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$stList = $db->query("SELECT id, tenant_id, user_id, admission_no, roll_no, first_name, last_name, section_id FROM students WHERE tenant_id = 1 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "=== STUDENTS IN TENANT 1 ===\n";
print_r($stList);
