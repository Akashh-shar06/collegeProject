<?php
require_once __DIR__ . '/../tests/bootstrap.php';

$db = App\Core\Database::getInstance();

echo "=== CHECKING & UPDATING ACADEMIC SCHEMA ===\n";

// 1. Standards / Academic Years Table
$db->exec("CREATE TABLE IF NOT EXISTS `academic_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `sequence_order` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// 2. Divisions / Classes Table (if sections doesn't have standard_id/department_id)
$colCheck = $db->query("SHOW COLUMNS FROM `sections`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('standard_id', $colCheck)) {
    $db->exec("ALTER TABLE `sections` ADD COLUMN `standard_id` INT NULL AFTER `class_id`");
}
if (!in_array('department_id', $colCheck)) {
    $db->exec("ALTER TABLE `sections` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
}

// 3. Subjects Table Columns check (department_id, standard_id)
$subjCols = $db->query("SHOW COLUMNS FROM `subjects`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('department_id', $subjCols)) {
    $db->exec("ALTER TABLE `subjects` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
}
if (!in_array('standard_id', $subjCols)) {
    $db->exec("ALTER TABLE `subjects` ADD COLUMN `standard_id` INT NULL AFTER `department_id`");
}

// 4. Students Table Columns check (department_id, standard_id, section_id, photo)
$stCols = $db->query("SHOW COLUMNS FROM `students`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('department_id', $stCols)) {
    $db->exec("ALTER TABLE `students` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
}
if (!in_array('standard_id', $stCols)) {
    $db->exec("ALTER TABLE `students` ADD COLUMN `standard_id` INT NULL AFTER `department_id`");
}
if (!in_array('photo', $stCols)) {
    $db->exec("ALTER TABLE `students` ADD COLUMN `photo` VARCHAR(255) NULL AFTER `last_name`");
}

// 5. Teacher Assignments Table
$db->exec("CREATE TABLE IF NOT EXISTS `teacher_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `teacher_user_id` INT NOT NULL,
    `staff_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `standard_id` INT NOT NULL,
    `section_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_teacher_assignment` (`tenant_id`, `staff_id`, `department_id`, `standard_id`, `section_id`, `subject_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// 6. Time Tables Columns check (department_id, standard_id, section_id, subject_id, start_time, end_time)
$ttCols = $db->query("SHOW COLUMNS FROM `time_tables`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('department_id', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
}
if (!in_array('standard_id', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `standard_id` INT NULL AFTER `department_id`");
}
if (!in_array('section_id', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `section_id` INT NULL AFTER `standard_id`");
}
if (!in_array('subject_id', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `subject_id` INT NULL AFTER `section_id`");
}
if (!in_array('start_time', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `start_time` TIME NULL AFTER `day_of_week`");
}
if (!in_array('end_time', $ttCols)) {
    $db->exec("ALTER TABLE `time_tables` ADD COLUMN `end_time` TIME NULL AFTER `start_time`");
}

// 7. Seed Default Standards & Assignments for tenant 1 & 24 if missing
$stdCount = $db->query("SELECT COUNT(*) FROM `academic_standards`")->fetchColumn();
if ($stdCount == 0) {
    // Seed for tenant 1
    $depts = $db->query("SELECT id, tenant_id, code FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($depts as $d) {
        $tid = $d['tenant_id'];
        $did = $d['id'];
        $db->exec("INSERT INTO `academic_standards` (tenant_id, department_id, name, code, sequence_order) VALUES 
            ({$tid}, {$did}, 'FY', 'FY', 1),
            ({$tid}, {$did}, 'SY', 'SY', 2),
            ({$tid}, {$did}, 'TY', 'TY', 3)");
    }
}

echo "Database schema successfully updated and seeded!\n";
