-- Campus360 Enterprise Multi-Tenant College ERP Database Schema
-- Target Engine: MySQL 8.x (InnoDB, utf8mb4)

CREATE DATABASE IF NOT EXISTS `college_erp_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `college_erp_db`;

-- 1. Tenants Table (Multi-Tenant Core)
CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `domain` VARCHAR(100) NULL,
    `status` ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Users Table (Tenant Scoped, except Super Admin)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NULL,
    `username` VARCHAR(60) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `status` ENUM('active', 'inactive', 'locked', 'unverified') DEFAULT 'active',
    `failed_attempts` INT DEFAULT 0,
    `lock_until` DATETIME NULL,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_username` (`tenant_id`, `username`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Departments Table
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Classes Table
CREATE TABLE IF NOT EXISTS `classes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Sections Table
CREATE TABLE IF NOT EXISTS `sections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Subjects Table
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `type` ENUM('theory', 'practical') DEFAULT 'theory',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Courses Table
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `duration_years` INT DEFAULT 3,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Batches Table
CREATE TABLE IF NOT EXISTS `batches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `batch_name` VARCHAR(100) NOT NULL,
    `start_year` INT NOT NULL,
    `end_year` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Students Table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `user_id` INT UNIQUE NULL,
    `admission_no` VARCHAR(50) NOT NULL,
    `roll_no` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(60) NOT NULL,
    `last_name` VARCHAR(60) NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `dob` DATE NOT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(120) NULL,
    `guardian_name` VARCHAR(120) NULL,
    `guardian_phone` VARCHAR(20) NULL,
    `batch_id` INT NOT NULL,
    `class_id` INT NULL,
    `section_id` INT NULL,
    `status` ENUM('active', 'graduated', 'transferred', 'suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_admission` (`tenant_id`, `admission_no`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Staff Table
CREATE TABLE IF NOT EXISTS `staff` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `user_id` INT UNIQUE NULL,
    `emp_id` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(60) NOT NULL,
    `last_name` VARCHAR(60) NOT NULL,
    `designation` VARCHAR(100) NOT NULL,
    `department_id` INT NOT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(120) NULL,
    `joining_date` DATE NOT NULL,
    `salary` DECIMAL(12,2) DEFAULT 0.00,
    `status` ENUM('active', 'on_leave', 'resigned') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_emp` (`tenant_id`, `emp_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Enquiries Table
CREATE TABLE IF NOT EXISTS `enquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `student_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NULL,
    `phone` VARCHAR(20) NOT NULL,
    `course_interested` VARCHAR(100) NOT NULL,
    `status` ENUM('pending', 'contacted', 'admitted', 'closed') DEFAULT 'pending',
    `source` VARCHAR(50) DEFAULT 'website',
    `assigned_to` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Admissions Table
CREATE TABLE IF NOT EXISTS `admissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `enquiry_id` INT NULL,
    `student_id` INT NOT NULL,
    `admission_date` DATE NOT NULL,
    `status` ENUM('pending_verification', 'approved', 'rejected') DEFAULT 'approved',
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Fee Structures Table
CREATE TABLE IF NOT EXISTS `fee_structures` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `academic_year` VARCHAR(20) NOT NULL,
    `fee_type` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `due_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Fee Installments Table (Section 6.5)
CREATE TABLE IF NOT EXISTS `fee_installments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `fee_structure_id` INT NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('unpaid', 'paid', 'overdue') DEFAULT 'unpaid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Fee Collections Table
CREATE TABLE IF NOT EXISTS `fee_collections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `fee_structure_id` INT NOT NULL,
    `installment_id` INT NULL,
    `amount_paid` DECIMAL(10,2) NOT NULL,
    `payment_mode` ENUM('cash', 'card', 'online', 'cheque') DEFAULT 'online',
    `transaction_id` VARCHAR(100) NOT NULL,
    `status` ENUM('paid', 'partial', 'failed') DEFAULT 'paid',
    `paid_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`installment_id`) REFERENCES `fee_installments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Accounts Transactions Table
CREATE TABLE IF NOT EXISTS `accounts_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `description` TEXT NULL,
    `transaction_date` DATE NOT NULL,
    `reference_no` VARCHAR(100) NOT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Student Attendance Table (Section 6.3)
CREATE TABLE IF NOT EXISTS `student_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `batch_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'late', 'leave') DEFAULT 'present',
    `marked_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_student_date` (`tenant_id`, `student_id`, `date`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Staff Attendance Table
CREATE TABLE IF NOT EXISTS `staff_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `staff_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'half_day', 'on_leave') DEFAULT 'present',
    `marked_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_staff_date` (`tenant_id`, `staff_id`, `date`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Grading Scales Table (Section 6.4)
CREATE TABLE IF NOT EXISTS `grading_scales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `grade` VARCHAR(5) NOT NULL,
    `min_percentage` DECIMAL(5,2) NOT NULL,
    `max_percentage` DECIMAL(5,2) NOT NULL,
    `grade_point` DECIMAL(3,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Exams Table
CREATE TABLE IF NOT EXISTS `exams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `course_id` INT NOT NULL,
    `exam_date` DATE NOT NULL,
    `total_marks` INT NOT NULL DEFAULT 100,
    `passing_marks` INT NOT NULL DEFAULT 40,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Exam Results Table (Section 6.4)
CREATE TABLE IF NOT EXISTS `exam_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `exam_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `marks_obtained` DECIMAL(5,2) NOT NULL,
    `grade` VARCHAR(5) NOT NULL,
    `class_rank` INT NULL,
    `remarks` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_exam_student` (`tenant_id`, `exam_id`, `student_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Online Exams Table
CREATE TABLE IF NOT EXISTS `online_exams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `course_id` INT NULL,
    `student_id` INT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 15,
    `total_questions` INT NOT NULL DEFAULT 5,
    `status` ENUM('draft', 'published', 'closed') DEFAULT 'published',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23b. Online Exam Questions Table
CREATE TABLE IF NOT EXISTS `online_exam_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `exam_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `option_a` VARCHAR(255) NOT NULL,
    `option_b` VARCHAR(255) NOT NULL,
    `option_c` VARCHAR(255) NOT NULL,
    `option_d` VARCHAR(255) NOT NULL,
    `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
    `marks` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23c. Online Exam Submissions Table
CREATE TABLE IF NOT EXISTS `online_exam_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `exam_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `score` DECIMAL(5,2) DEFAULT 0,
    `total_score` DECIMAL(5,2) DEFAULT 0,
    `submission_status` ENUM('submitted', 'auto_submitted_tab_switch', 'auto_submitted_back_button', 'auto_submitted_timeout') DEFAULT 'submitted',
    `submission_reason` VARCHAR(100) DEFAULT 'manual',
    `started_at` DATETIME NULL,
    `submitted_at` DATETIME NULL,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23d. Online Exam Answers Table
CREATE TABLE IF NOT EXISTS `online_exam_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `selected_option` ENUM('A', 'B', 'C', 'D') NULL,
    `is_correct` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`submission_id`) REFERENCES `online_exam_submissions` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `online_exam_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Certificates Table
CREATE TABLE IF NOT EXISTS `certificates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `certificate_type` ENUM('transfer', 'bonafide', 'character', 'degree') NOT NULL,
    `issue_date` DATE NOT NULL,
    `certificate_no` VARCHAR(100) NOT NULL,
    `status` ENUM('issued', 'revoked') DEFAULT 'issued',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Library Books Table
CREATE TABLE IF NOT EXISTS `library_books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `isbn` VARCHAR(50) NOT NULL,
    `author` VARCHAR(120) NOT NULL,
    `publisher` VARCHAR(120) NULL,
    `copies_total` INT DEFAULT 1,
    `copies_available` INT DEFAULT 1,
    `status` ENUM('active', 'damaged', 'lost') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Library Issues Table
CREATE TABLE IF NOT EXISTS `library_issues` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `issue_date` DATE NOT NULL,
    `return_due_date` DATE NOT NULL,
    `actual_return_date` DATE NULL,
    `status` ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Hostel Rooms Table
CREATE TABLE IF NOT EXISTS `hostel_rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `block` VARCHAR(50) NOT NULL,
    `room_no` VARCHAR(20) NOT NULL,
    `capacity` INT DEFAULT 2,
    `occupied` INT DEFAULT 0,
    `fee_per_term` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Hostel Allocations Table
CREATE TABLE IF NOT EXISTS `hostel_allocations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `allocated_date` DATE NOT NULL,
    `status` ENUM('active', 'vacated') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `hostel_rooms` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. Transport Routes Table
CREATE TABLE IF NOT EXISTS `transport_routes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `route_name` VARCHAR(100) NOT NULL,
    `vehicle_no` VARCHAR(30) NOT NULL,
    `driver_name` VARCHAR(100) NOT NULL,
    `driver_phone` VARCHAR(20) NOT NULL,
    `fare` DECIMAL(8,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. Inventory Assets Table
CREATE TABLE IF NOT EXISTS `inventory_assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `item_name` VARCHAR(120) NOT NULL,
    `category` ENUM('asset', 'stock') DEFAULT 'asset',
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('in_stock', 'issued', 'disposed') DEFAULT 'in_stock',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. Visitors Table
CREATE TABLE IF NOT EXISTS `visitors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `visitor_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `purpose` VARCHAR(200) NOT NULL,
    `person_to_meet` VARCHAR(100) NOT NULL,
    `check_in` DATETIME NOT NULL,
    `check_out` DATETIME NULL,
    `badge_no` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. Tickets Table
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) DEFAULT 'IT Support',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME NULL,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. Notices Table
CREATE TABLE IF NOT EXISTS `notices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `target_role` VARCHAR(50) DEFAULT 'all',
    `posted_by` INT NOT NULL,
    `publish_date` DATE NOT NULL,
    `status` ENUM('published', 'archived') DEFAULT 'published',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. Time Tables
CREATE TABLE IF NOT EXISTS `time_tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `batch_id` INT NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    `time_slot` VARCHAR(30) NOT NULL,
    `subject_name` VARCHAR(100) NOT NULL,
    `lecture_type` ENUM('theory', 'practical', 'tutorial') DEFAULT 'theory',
    `staff_id` INT NOT NULL,
    `room_no` VARCHAR(20) NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35. Leave Requests Table
CREATE TABLE IF NOT EXISTS `leave_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `applicant_user_id` INT NOT NULL,
    `leave_type` ENUM('casual', 'sick', 'academic', 'maternity') DEFAULT 'casual',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`applicant_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 36. Security Audit Logs Table
CREATE TABLE IF NOT EXISTS `security_audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NULL,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `details` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 37. Password Resets Table
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(120) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 38. Modules Table (RBAC Core)
CREATE TABLE IF NOT EXISTS `modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `module_key` VARCHAR(60) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) DEFAULT 'General',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 39. Permissions Master Table
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `action_key` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 40. Role Modules Allocation Table
CREATE TABLE IF NOT EXISTS `role_modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `module_key` VARCHAR(60) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_role_module` (`role_id`, `module_key`),
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 41. Role Permissions Granular Matrix Table
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `module_key` VARCHAR(60) NOT NULL,
    `permission_type` ENUM('view', 'create', 'edit', 'delete', 'approve', 'export', 'manage') NOT NULL,
    `is_allowed` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_role_module_perm` (`role_id`, `module_key`, `permission_type`),
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 42. User Roles Mapping Table
CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `is_primary` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 43. Configurable Leave Types Table
CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `days_allowed` INT DEFAULT 12,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tenant_leave_code` (`tenant_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 44. Employee Leave Balances Table
CREATE TABLE IF NOT EXISTS `leave_balances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT DEFAULT 1,
    `user_id` INT NOT NULL,
    `staff_id` INT NULL,
    `leave_type_id` INT NOT NULL,
    `allocated_days` INT DEFAULT 12,
    `used_days` INT DEFAULT 0,
    `remaining_days` INT DEFAULT 12,
    `academic_year` VARCHAR(20) DEFAULT '2026-2027',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_leave_type_year` (`user_id`, `leave_type_id`, `academic_year`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 45. Employee Leave Requests Table
CREATE TABLE IF NOT EXISTS `leave_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT DEFAULT 1,
    `user_id` INT NOT NULL,
    `staff_id` INT NULL,
    `employee_name` VARCHAR(150) NOT NULL,
    `employee_code` VARCHAR(50) NULL,
    `job_role` VARCHAR(100) NOT NULL,
    `department_name` VARCHAR(100) NULL,
    `leave_type_id` INT NOT NULL,
    `leave_type_name` VARCHAR(100) NOT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NOT NULL,
    `total_days` INT NOT NULL DEFAULT 1,
    `reason` TEXT NOT NULL,
    `document_path` VARCHAR(255) NULL,
    `contact_number` VARCHAR(30) NULL,
    `status` ENUM('draft', 'submitted', 'under_hr_review', 'hr_recommended', 'pending_principal_approval', 'pending_super_admin_approval', 'approved', 'rejected', 'cancelled') DEFAULT 'submitted',
    `hr_user_id` INT NULL,
    `hr_remarks` TEXT NULL,
    `hr_recommendation` ENUM('recommended', 'returned', 'not_recommended') NULL,
    `hr_reviewed_at` DATETIME NULL,
    `principal_user_id` INT NULL,
    `principal_remarks` TEXT NULL,
    `principal_approved_at` DATETIME NULL,
    `super_admin_user_id` INT NULL,
    `super_admin_remarks` TEXT NULL,
    `super_admin_approved_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 46. Leave Workflow Security Audit Log Table
CREATE TABLE IF NOT EXISTS `leave_audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT DEFAULT 1,
    `request_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role_name` VARCHAR(50) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `previous_status` VARCHAR(50) NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `remarks` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 47. Staff Task Management & Assignment Engine Table
CREATE TABLE IF NOT EXISTS `staff_tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `assigned_by_user_id` INT NOT NULL,
    `assigned_to_user_id` INT NOT NULL,
    `assigned_to_staff_id` INT NULL,
    `department_id` INT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `start_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('assigned', 'in_progress', 'completed', 'overdue', 'cancelled') DEFAULT 'assigned',
    `attachment_path` VARCHAR(255) NULL,
    `staff_remarks` TEXT NULL,
    `principal_remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



