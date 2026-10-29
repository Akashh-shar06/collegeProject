<?php
define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;

$db = Database::getInstance();

echo "\n=== BATCHES COLUMNS ===\n";
$cols = $db->query("DESCRIBE batches")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo sprintf("%-20s %-25s Null:%-4s Default:%s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}

echo "\n=== ALL BATCHES ===\n";
$batchesAll = $db->query("SELECT * FROM batches")->fetchAll(PDO::FETCH_ASSOC);
print_r($batchesAll);

echo "\n=== ALL DEPARTMENTS DUPLICATES CHECK ===\n";
$deptsAll = $db->query("SELECT id, tenant_id, code, name FROM departments ORDER BY tenant_id, name")->fetchAll(PDO::FETCH_ASSOC);
print_r($deptsAll);

echo "\n=== ALL COURSES FOR TENANT 24 AND 1 ===\n";
$coursesAll = $db->query("SELECT id, tenant_id, department_id, code, name FROM courses WHERE tenant_id IN (1, 24) ORDER BY tenant_id, department_id, name")->fetchAll(PDO::FETCH_ASSOC);
print_r($coursesAll);
