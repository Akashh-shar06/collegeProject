-- Migration: Teacher Panel, Timetable Extensions, Attendance Sessions & Records, Lesson Records, Homework, Remarks
-- Target: MySQL 8.x (InnoDB, utf8mb4)

USE `college_erp_db`;

-- 1. Extend time_tables table if missing columns
ALTER TABLE `time_tables` 
    ADD COLUMN IF NOT EXISTS `academic_session_id` INT NULL AFTER `tenant_id`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active','inactive','cancelled') DEFAULT 'active' AFTER `room_no`;

-- 2. Create attendance_sessions table
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
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
    INDEX `idx_att_sess_date` (`lecture_date`),
    FOREIGN KEY (`time_table_id`) REFERENCES `time_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create attendance_records table
CREATE TABLE IF NOT EXISTS `attendance_records` (
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
    INDEX `idx_att_rec_student` (`student_id`),
    FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create lesson_records table
CREATE TABLE IF NOT EXISTS `lesson_records` (
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
    INDEX `idx_lesson_staff` (`staff_id`),
    FOREIGN KEY (`time_table_id`) REFERENCES `time_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create homework_assignments table
CREATE TABLE IF NOT EXISTS `homework_assignments` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create classroom_remarks table
CREATE TABLE IF NOT EXISTS `classroom_remarks` (
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
    INDEX `idx_rem_student` (`student_id`),
    FOREIGN KEY (`time_table_id`) REFERENCES `time_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
