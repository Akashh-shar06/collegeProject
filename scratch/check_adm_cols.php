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

$db = App\Core\Database::getInstance();
foreach (['classes', 'sections', 'batches', 'courses'] as $table) {
    echo "--- {$table} COLUMNS ---\n";
    try {
        foreach($db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll() as $c) {
            echo $c['Field'] . ' (' . $c['Type'] . ")\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
