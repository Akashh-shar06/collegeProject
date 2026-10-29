<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "Checking batches duplicates:" . PHP_EOL;
$b = $db->query("SELECT tenant_id, course_id, batch_name, COUNT(*) as c FROM batches GROUP BY tenant_id, course_id, batch_name HAVING c > 1")->fetchAll();
print_r($b);

echo "Checking academic_sessions duplicates:" . PHP_EOL;
$s = $db->query("SELECT tenant_id, name, COUNT(*) as c FROM academic_sessions GROUP BY tenant_id, name HAVING c > 1")->fetchAll();
print_r($s);

echo "Checking academic_levels duplicates:" . PHP_EOL;
$l = $db->query("SELECT tenant_id, course_id, level_order, COUNT(*) as c FROM academic_levels GROUP BY tenant_id, course_id, level_order HAVING c > 1")->fetchAll();
print_r($l);
