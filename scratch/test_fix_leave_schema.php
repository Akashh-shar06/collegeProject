<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();

$cols = $db->query("SHOW COLUMNS FROM `leave_requests`")->fetchAll(PDO::FETCH_COLUMN);
echo "Current columns: " . implode(', ', $cols) . "\n";

$sqls = [];

if (!in_array('user_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `user_id` INT NULL AFTER `tenant_id`";
}
if (!in_array('staff_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `staff_id` INT NULL AFTER `user_id`";
}
if (!in_array('employee_name', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `employee_name` VARCHAR(150) NULL AFTER `staff_id`";
}
if (!in_array('employee_code', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `employee_code` VARCHAR(50) NULL AFTER `employee_name`";
}
if (!in_array('job_role', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `job_role` VARCHAR(100) NULL AFTER `employee_code`";
}
if (!in_array('department_name', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `department_name` VARCHAR(100) NULL AFTER `job_role`";
}
if (!in_array('leave_type_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `leave_type_id` INT NULL AFTER `department_name`";
}
if (!in_array('leave_type_name', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `leave_type_name` VARCHAR(100) NULL AFTER `leave_type_id`";
}
if (!in_array('from_date', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `from_date` DATE NULL AFTER `leave_type_name`";
}
if (!in_array('to_date', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `to_date` DATE NULL AFTER `from_date`";
}
if (!in_array('total_days', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `total_days` INT DEFAULT 1 AFTER `to_date`";
}
if (!in_array('document_path', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `document_path` VARCHAR(255) NULL AFTER `reason`";
}
if (!in_array('contact_number', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `contact_number` VARCHAR(30) NULL AFTER `document_path`";
}
if (!in_array('hr_user_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `hr_user_id` INT NULL";
}
if (!in_array('hr_remarks', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `hr_remarks` TEXT NULL";
}
if (!in_array('hr_recommendation', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `hr_recommendation` VARCHAR(50) NULL";
}
if (!in_array('hr_reviewed_at', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `hr_reviewed_at` DATETIME NULL";
}
if (!in_array('principal_user_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `principal_user_id` INT NULL";
}
if (!in_array('principal_remarks', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `principal_remarks` TEXT NULL";
}
if (!in_array('principal_approved_at', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `principal_approved_at` DATETIME NULL";
}
if (!in_array('super_admin_user_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `super_admin_user_id` INT NULL";
}
if (!in_array('super_admin_remarks', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `super_admin_remarks` TEXT NULL";
}
if (!in_array('super_admin_approved_at', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `super_admin_approved_at` DATETIME NULL";
}
if (!in_array('updated_at', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
}

// Modify status to VARCHAR(50)
$sqls[] = "ALTER TABLE `leave_requests` MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'submitted'";
// Allow applicant_user_id to be NULL if present
if (in_array('applicant_user_id', $cols)) {
    $sqls[] = "ALTER TABLE `leave_requests` MODIFY COLUMN `applicant_user_id` INT NULL";
}

foreach ($sqls as $sql) {
    echo "Executing: $sql\n";
    $db->exec($sql);
}

echo "Schema updated successfully!\n";
$newCols = $db->query("SHOW COLUMNS FROM `leave_requests`")->fetchAll(PDO::FETCH_COLUMN);
echo "New columns: " . implode(', ', $newCols) . "\n";
