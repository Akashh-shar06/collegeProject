<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $cols = $db->query("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_COLUMN);
    echo "Current Columns in `students`: " . implode(', ', $cols) . "\n";

    if (!in_array('unique_id', $cols)) {
        $db->exec("ALTER TABLE `students` ADD COLUMN `unique_id` VARCHAR(50) NULL AFTER `user_id`");
        $db->exec("ALTER TABLE `students` ADD UNIQUE KEY `unique_tenant_unique_id` (`tenant_id`, `unique_id`)");
        echo "Added `unique_id` column to `students` table.\n";
    }

    if (!in_array('middle_name', $cols)) {
        $db->exec("ALTER TABLE `students` ADD COLUMN `middle_name` VARCHAR(60) NULL AFTER `first_name`");
        echo "Added `middle_name` column to `students` table.\n";
    }

    // Also check student_enrollments or promotions history table if needed
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('student_enrollments', $tables)) {
        $db->exec("CREATE TABLE IF NOT EXISTS `student_enrollments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT NOT NULL,
            `student_id` INT NOT NULL,
            `academic_year` VARCHAR(30) NOT NULL,
            `standard_id` INT NOT NULL,
            `class_id` INT NULL,
            `status` ENUM('active', 'promoted', 'completed', 'repeated') DEFAULT 'active',
            `promoted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_student_year` (`tenant_id`, `student_id`, `academic_year`),
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        echo "Created `student_enrollments` table.\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
