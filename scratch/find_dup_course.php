<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();
$dup = $db->query("SELECT tenant_id, department_id, LOWER(TRIM(name)) as n, COUNT(*) as c, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes FROM courses WHERE status = 'active' GROUP BY tenant_id, department_id, LOWER(TRIM(name)) HAVING c > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($dup);
