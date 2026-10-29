<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$rows = $db->query("SELECT * FROM leave_requests ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($rows);
