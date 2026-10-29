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

echo "=== ACADEMIC_SESSIONS ===\n";
$sessions = $db->query("SELECT * FROM academic_sessions")->fetchAll(PDO::FETCH_ASSOC);
print_r($sessions);

echo "=== ACADEMIC_STANDARDS ===\n";
$standards = $db->query("SELECT * FROM academic_standards")->fetchAll(PDO::FETCH_ASSOC);
print_r($standards);

echo "=== ACADEMIC_LEVELS ===\n";
$levels = $db->query("SELECT * FROM academic_levels")->fetchAll(PDO::FETCH_ASSOC);
print_r($levels);

echo "=== SEMESTERS ===\n";
$semesters = $db->query("SELECT * FROM semesters")->fetchAll(PDO::FETCH_ASSOC);
print_r($semesters);
