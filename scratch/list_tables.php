<?php
define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
$db = Database::getInstance();

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "All tables (" . count($tables) . "):\n";
print_r($tables);
