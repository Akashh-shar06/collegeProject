<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$batches = $db->query("SELECT * FROM batches LIMIT 10")->fetchAll();
print_r($batches);
