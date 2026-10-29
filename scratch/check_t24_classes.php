<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$classes = $db->query("SELECT * FROM classes WHERE tenant_id = 24")->fetchAll(PDO::FETCH_ASSOC);
print_r($classes);
