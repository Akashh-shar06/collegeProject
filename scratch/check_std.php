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
try {
    foreach($db->query("SHOW COLUMNS FROM `standards`")->fetchAll() as $c) {
        echo $c['Field'] . ' (' . $c['Type'] . ")\n";
    }
} catch (\Throwable $e) {
    echo "standards table error: " . $e->getMessage() . "\n";
}
