<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "=== TABLES IN DATABASE (" . count($tables) . ") ===\n";
    foreach ($tables as $t) {
        echo "- {$t}\n";
    }

    echo "\n=== DESCRIBE Key Tables ===\n";
    $keyTables = ['courses', 'departments', 'semesters', 'sections', 'subjects', 'students', 'batches', 'time_tables', 'tenants', 'users', 'roles', 'student_enrollments', 'student_promotions', 'academic_sessions', 'academic_levels', 'subject_offerings'];
    foreach ($keyTables as $kt) {
        if (in_array($kt, $tables, true)) {
            echo "\n--- TABLE: {$kt} ---\n";
            $cols = $db->query("DESCRIBE {$kt}")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                echo "  {$c['Field']} | {$c['Type']} | Null: {$c['Null']} | Key: {$c['Key']} | Default: {$c['Default']}\n";
            }
        } else {
            echo "\n--- TABLE: {$kt} (NOT FOUND) ---\n";
        }
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
