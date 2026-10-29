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

echo "=== TEACHER_ASSIGNMENTS SCHEMA ===\n";
$cols = $db->query("DESCRIBE teacher_assignments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo sprintf("%-20s %-25s Null:%-4s Default:%s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}

echo "\n=== TEACHER_ASSIGNMENTS DATA ===\n";
$rows = $db->query("SELECT * FROM teacher_assignments LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
