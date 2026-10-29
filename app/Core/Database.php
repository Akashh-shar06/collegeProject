<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/config.php';
            $dbConfig = $config['db'];

            try {
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}";
                $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Ensure Database exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbConfig['name']}`");

                self::$instance = $pdo;
                self::bootstrapTablesIfNeeded($pdo);

            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database Connection Failed. Please ensure MySQL service is active.");
            }
        }
        return self::$instance;
    }

    private static function bootstrapTablesIfNeeded(PDO $pdo): void {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tenants'");
            if ($stmt->rowCount() === 0) {
                $schemaFile = BASE_PATH . '/database/schema.sql';
                $seedFile = BASE_PATH . '/database/seed.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $pdo->exec($sql);
                }
                if (file_exists($seedFile)) {
                    $sqlSeed = file_get_contents($seedFile);
                    $pdo->exec($sqlSeed);
                }
            }
            // Ensure password_resets table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(120) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `expires_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            self::runMigrationsIfNeeded($pdo);
        } catch (PDOException $e) {
            error_log("Bootstrap Table Error: " . $e->getMessage());
        }
    }

    private static function runMigrationsIfNeeded(PDO $pdo): void {
        try {
            // 1. Create semesters table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `semesters` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `course_id` INT NULL,
                `name` VARCHAR(50) NOT NULL,
                `semester_number` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 2. Create classrooms table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `classrooms` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `room_number` VARCHAR(50) NOT NULL,
                `building_block` VARCHAR(100) NULL,
                `capacity` INT DEFAULT 60,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 3. Add columns to students if missing
            $stCols = $pdo->query("SHOW COLUMNS FROM `students`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('parent_user_id', $stCols)) {
                $pdo->exec("ALTER TABLE `students` ADD COLUMN `parent_user_id` INT NULL AFTER `user_id`");
            }
            if (!in_array('department_id', $stCols)) {
                $pdo->exec("ALTER TABLE `students` ADD COLUMN `department_id` INT NULL AFTER `batch_id`");
            }
            if (!in_array('course_id', $stCols)) {
                $pdo->exec("ALTER TABLE `students` ADD COLUMN `course_id` INT NULL AFTER `department_id`");
            }
            if (!in_array('semester_id', $stCols)) {
                $pdo->exec("ALTER TABLE `students` ADD COLUMN `semester_id` INT NULL AFTER `course_id`");
            }

            // 4. Add columns to time_tables if missing
            $ttCols = $pdo->query("SHOW COLUMNS FROM `time_tables`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('academic_year', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `academic_year` VARCHAR(30) DEFAULT '2026-2027' AFTER `tenant_id`");
            }
            if (!in_array('department_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `department_id` INT NULL AFTER `academic_year`");
            }
            if (!in_array('course_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `course_id` INT NULL AFTER `department_id`");
            }
            if (!in_array('semester_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `semester_id` INT NULL AFTER `course_id`");
            }
            if (!in_array('section_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `section_id` INT NULL AFTER `semester_id`");
            }
            if (!in_array('subject_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `subject_id` INT NULL AFTER `section_id`");
            }
            if (!in_array('classroom_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `classroom_id` INT NULL AFTER `staff_id`");
            }
            if (!in_array('start_time', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `start_time` TIME NULL AFTER `time_slot`");
            }
            if (!in_array('end_time', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `end_time` TIME NULL AFTER `start_time`");
            }
            if (!in_array('created_by', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `created_by` INT NULL AFTER `room_no`");
            }
            if (!in_array('status', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `status` ENUM('active','inactive','cancelled') DEFAULT 'active' AFTER `room_no`");
            }
            if (!in_array('academic_session_id', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `academic_session_id` INT NULL AFTER `tenant_id`");
            }
            if (!in_array('updated_at', $ttCols)) {
                $pdo->exec("ALTER TABLE `time_tables` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }

            // 4b. Create timetable_periods table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `timetable_periods` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL DEFAULT 1,
                `period_key` VARCHAR(50) NOT NULL,
                `period_number` INT NOT NULL DEFAULT 1,
                `label` VARCHAR(100) NOT NULL,
                `start_time` TIME NOT NULL,
                `end_time` TIME NOT NULL,
                `status` ENUM('active','inactive','deleted') DEFAULT 'active',
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_tenant_period_time` (`tenant_id`, `start_time`, `end_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 5. Create attendance_sessions table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance_sessions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `time_table_id` INT NOT NULL,
                `staff_id` INT NOT NULL,
                `lecture_date` DATE NOT NULL,
                `subject_id` INT NULL,
                `section_id` INT NULL,
                `academic_session_id` INT NULL,
                `total_students` INT DEFAULT 0,
                `present_count` INT DEFAULT 0,
                `absent_count` INT DEFAULT 0,
                `late_count` INT DEFAULT 0,
                `excused_count` INT DEFAULT 0,
                `status` ENUM('completed', 'draft', 'submitted') DEFAULT 'completed',
                `created_by` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_lecture_date` (`tenant_id`, `time_table_id`, `lecture_date`),
                INDEX `idx_att_sess_tenant` (`tenant_id`),
                INDEX `idx_att_sess_tt` (`time_table_id`),
                INDEX `idx_att_sess_date` (`lecture_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 6. Create attendance_records table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance_records` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `session_id` INT NOT NULL,
                `time_table_id` INT NOT NULL,
                `student_id` INT NOT NULL,
                `status` ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
                `remarks` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_session_student` (`session_id`, `student_id`),
                INDEX `idx_att_rec_tenant` (`tenant_id`),
                INDEX `idx_att_rec_session` (`session_id`),
                INDEX `idx_att_rec_student` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 7. Create lesson_records table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `lesson_records` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `time_table_id` INT NOT NULL,
                `staff_id` INT NOT NULL,
                `subject_id` INT NULL,
                `section_id` INT NULL,
                `lecture_date` DATE NOT NULL,
                `topic_title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` ENUM('planned', 'completed', 'partially_completed') DEFAULT 'completed',
                `created_by` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_tt_lesson_date` (`tenant_id`, `time_table_id`, `lecture_date`),
                INDEX `idx_lesson_tenant` (`tenant_id`),
                INDEX `idx_lesson_tt` (`time_table_id`),
                INDEX `idx_lesson_staff` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 8. Create homework_assignments table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `homework_assignments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `staff_id` INT NOT NULL,
                `department_id` INT NULL,
                `course_id` INT NULL,
                `section_id` INT NULL,
                `subject_id` INT NOT NULL,
                `time_table_id` INT NULL,
                `assigned_date` DATE NOT NULL,
                `due_date` DATE NOT NULL,
                `attachment_path` VARCHAR(255) NULL,
                `status` ENUM('active', 'closed', 'archived') DEFAULT 'active',
                `created_by` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_hw_tenant` (`tenant_id`),
                INDEX `idx_hw_staff` (`staff_id`),
                INDEX `idx_hw_section` (`section_id`),
                INDEX `idx_hw_subject` (`subject_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 9. Create classroom_remarks table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `classroom_remarks` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `time_table_id` INT NOT NULL,
                `staff_id` INT NOT NULL,
                `student_id` INT NULL,
                `lecture_date` DATE NOT NULL,
                `remark_type` ENUM('general_class', 'academic_performance', 'participation', 'behavior') DEFAULT 'general_class',
                `remark_text` TEXT NOT NULL,
                `created_by` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_rem_tenant` (`tenant_id`),
                INDEX `idx_rem_tt` (`time_table_id`),
                INDEX `idx_rem_student` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 10. Sync leave_requests schema
            $lrCols = $pdo->query("SHOW COLUMNS FROM `leave_requests`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('user_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `user_id` INT NULL AFTER `tenant_id`");
            }
            if (!in_array('staff_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `staff_id` INT NULL AFTER `user_id`");
            }
            if (!in_array('employee_name', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `employee_name` VARCHAR(150) NULL AFTER `staff_id`");
            }
            if (!in_array('employee_code', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `employee_code` VARCHAR(50) NULL AFTER `employee_name`");
            }
            if (!in_array('job_role', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `job_role` VARCHAR(100) NULL AFTER `employee_code`");
            }
            if (!in_array('department_name', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `department_name` VARCHAR(100) NULL AFTER `job_role`");
            }
            if (!in_array('leave_type_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `leave_type_id` INT NULL AFTER `department_name`");
            }
            if (!in_array('leave_type_name', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `leave_type_name` VARCHAR(100) NULL AFTER `leave_type_id`");
            }
            if (!in_array('from_date', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `from_date` DATE NULL AFTER `leave_type_name`");
            }
            if (!in_array('to_date', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `to_date` DATE NULL AFTER `from_date`");
            }
            if (!in_array('total_days', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `total_days` INT DEFAULT 1 AFTER `to_date`");
            }
            if (!in_array('document_path', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `document_path` VARCHAR(255) NULL AFTER `reason`");
            }
            if (!in_array('contact_number', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `contact_number` VARCHAR(30) NULL AFTER `document_path`");
            }
            if (!in_array('hr_user_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `hr_user_id` INT NULL");
            }
            if (!in_array('hr_remarks', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `hr_remarks` TEXT NULL");
            }
            if (!in_array('hr_recommendation', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `hr_recommendation` VARCHAR(50) NULL");
            }
            if (!in_array('hr_reviewed_at', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `hr_reviewed_at` DATETIME NULL");
            }
            if (!in_array('principal_user_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `principal_user_id` INT NULL");
            }
            if (!in_array('principal_remarks', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `principal_remarks` TEXT NULL");
            }
            if (!in_array('principal_approved_at', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `principal_approved_at` DATETIME NULL");
            }
            if (!in_array('super_admin_user_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `super_admin_user_id` INT NULL");
            }
            if (!in_array('super_admin_remarks', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `super_admin_remarks` TEXT NULL");
            }
            if (!in_array('super_admin_approved_at', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `super_admin_approved_at` DATETIME NULL");
            }
            if (!in_array('updated_at', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }
            $pdo->exec("ALTER TABLE `leave_requests` MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'submitted'");
            if (in_array('applicant_user_id', $lrCols)) {
                $pdo->exec("ALTER TABLE `leave_requests` MODIFY COLUMN `applicant_user_id` INT NULL");
            }
        } catch (PDOException $e) {
            error_log("Schema Migration Error: " . $e->getMessage());
        }
    }

    /**
     * Centralized Multi-Tenant Query Execution Safeguard
     * Binds tenant_id strictly from trusted server-side session.
     */
    public static function tenantQuery(string $sql, array $params = []): \PDOStatement {
        $pdo = self::getInstance();
        Session::start();

        $tenantId = Session::get('tenant_id');
        $roleName = Session::get('role_name');

        // If user belongs to a specific tenant (not Super Admin), enforce tenant isolation
        if ($tenantId !== null && $roleName !== 'super_admin') {
            if (strripos($sql, 'WHERE') !== false) {
                $sql .= " AND tenant_id = :session_tenant_id";
            } else if (strripos($sql, 'INSERT INTO') === false && strripos($sql, 'SHOW TABLES') === false) {
                $sql .= " WHERE tenant_id = :session_tenant_id";
            }
            $params[':session_tenant_id'] = $tenantId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
