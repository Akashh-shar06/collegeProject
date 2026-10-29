<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$c42 = $db->query("SELECT * FROM courses WHERE id = 42")->fetch(PDO::FETCH_ASSOC);
print_r($c42);
$st42 = $db->query("SELECT * FROM students WHERE course_id = 42")->fetchAll(PDO::FETCH_ASSOC);
print_r($st42);
