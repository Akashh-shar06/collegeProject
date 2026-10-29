<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/security.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

$db = Database::getInstance();

$ttCols = $db->query("SHOW COLUMNS FROM `time_tables`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('standard_id', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `standard_id` INT NULL AFTER `department_id`");
}

echo "Schema patch 3 applied successfully!\n";
