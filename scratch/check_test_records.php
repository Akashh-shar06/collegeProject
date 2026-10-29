<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "Checking courses with name containing 'Test':" . PHP_EOL;
$stmt = $db->query("SELECT id, tenant_id, department_id, name, code, status FROM courses WHERE name LIKE '%Test%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "Checking departments with name containing 'Test':" . PHP_EOL;
$stmt = $db->query("SELECT id, tenant_id, name, code, status FROM departments WHERE name LIKE '%Test%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
