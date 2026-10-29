<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/app/Core/Database.php';


try {
    $db = \App\Core\Database::getInstance();
    
    // Check if columns already exist
    $cols = $db->query("SHOW COLUMNS FROM time_tables")->fetchAll(\PDO::FETCH_COLUMN);
    
    if (!in_array('lecture_type', $cols, true)) {
        $db->exec("ALTER TABLE time_tables ADD COLUMN lecture_type ENUM('theory', 'practical', 'tutorial') DEFAULT 'theory' AFTER subject_name");
        echo "Added column 'lecture_type' to time_tables.\n";
    } else {
        echo "Column 'lecture_type' already exists.\n";
    }
    
    if (!in_array('notes', $cols, true)) {
        $db->exec("ALTER TABLE time_tables ADD COLUMN notes TEXT NULL AFTER room_no");
        echo "Added column 'notes' to time_tables.\n";
    } else {
        echo "Column 'notes' already exists.\n";
    }
    
    echo "Migration completed successfully.\n";
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
