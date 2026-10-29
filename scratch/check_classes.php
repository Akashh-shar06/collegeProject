<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$classes = $db->query("SELECT * FROM classes WHERE tenant_id = 24")->fetchAll();
print_r($classes);
