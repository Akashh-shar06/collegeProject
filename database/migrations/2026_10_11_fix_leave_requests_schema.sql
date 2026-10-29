-- ==============================================================================
-- Campus360 Enterprise ERP - Leave Requests Schema Alignment Migration
-- Resolves: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'user_id' in 'field list'
-- ==============================================================================

-- 1. Ensure columns exist on leave_requests table
ALTER TABLE `leave_requests`
    ADD COLUMN IF NOT EXISTS `user_id` INT NULL AFTER `tenant_id`,
    ADD COLUMN IF NOT EXISTS `staff_id` INT NULL AFTER `user_id`,
    ADD COLUMN IF NOT EXISTS `employee_name` VARCHAR(150) NULL AFTER `staff_id`,
    ADD COLUMN IF NOT EXISTS `employee_code` VARCHAR(50) NULL AFTER `employee_name`,
    ADD COLUMN IF NOT EXISTS `job_role` VARCHAR(100) NULL AFTER `employee_code`,
    ADD COLUMN IF NOT EXISTS `department_name` VARCHAR(100) NULL AFTER `job_role`,
    ADD COLUMN IF NOT EXISTS `leave_type_id` INT NULL AFTER `department_name`,
    ADD COLUMN IF NOT EXISTS `leave_type_name` VARCHAR(100) NULL AFTER `leave_type_id`,
    ADD COLUMN IF NOT EXISTS `from_date` DATE NULL AFTER `leave_type_name`,
    ADD COLUMN IF NOT EXISTS `to_date` DATE NULL AFTER `from_date`,
    ADD COLUMN IF NOT EXISTS `total_days` INT DEFAULT 1 AFTER `to_date`,
    ADD COLUMN IF NOT EXISTS `document_path` VARCHAR(255) NULL AFTER `reason`,
    ADD COLUMN IF NOT EXISTS `contact_number` VARCHAR(30) NULL AFTER `document_path`,
    ADD COLUMN IF NOT EXISTS `hr_user_id` INT NULL,
    ADD COLUMN IF NOT EXISTS `hr_remarks` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `hr_recommendation` VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `hr_reviewed_at` DATETIME NULL,
    ADD COLUMN IF NOT EXISTS `principal_user_id` INT NULL,
    ADD COLUMN IF NOT EXISTS `principal_remarks` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `principal_approved_at` DATETIME NULL,
    ADD COLUMN IF NOT EXISTS `super_admin_user_id` INT NULL,
    ADD COLUMN IF NOT EXISTS `super_admin_remarks` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `super_admin_approved_at` DATETIME NULL,
    ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 2. Modify status to support complete enterprise workflow statuses
ALTER TABLE `leave_requests`
    MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'submitted',
    MODIFY COLUMN `applicant_user_id` INT NULL;

-- 3. Add supporting indexes
ALTER TABLE `leave_requests`
    ADD INDEX IF NOT EXISTS `idx_leave_req_user` (`user_id`),
    ADD INDEX IF NOT EXISTS `idx_leave_req_staff` (`staff_id`),
    ADD INDEX IF NOT EXISTS `idx_leave_req_tenant` (`tenant_id`),
    ADD INDEX IF NOT EXISTS `idx_leave_req_status` (`status`);
