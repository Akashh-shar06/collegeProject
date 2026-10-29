<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    echo "=== EXECUTING ACADEMIC MODULE DATABASE MIGRATION ===\n";

    // 1. Ensure `courses` columns
    $db->exec("
        CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            department_id INT NULL,
            name VARCHAR(150) NOT NULL,
            code VARCHAR(30) NOT NULL,
            duration_years INT DEFAULT 3,
            description TEXT NULL,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $colsCourses = $db->query("DESCRIBE courses")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('description', $colsCourses)) {
        $db->exec("ALTER TABLE courses ADD COLUMN description TEXT NULL AFTER duration_years;");
    }
    if (!in_array('status', $colsCourses)) {
        $db->exec("ALTER TABLE courses ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description;");
    }
    if (!in_array('updated_at', $colsCourses)) {
        $db->exec("ALTER TABLE courses ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    // 2. Ensure `departments` columns
    $db->exec("
        CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            course_id INT NULL,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL,
            description TEXT NULL,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $colsDepts = $db->query("DESCRIBE departments")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('course_id', $colsDepts)) {
        $db->exec("ALTER TABLE departments ADD COLUMN course_id INT NULL AFTER tenant_id;");
    }
    if (!in_array('description', $colsDepts)) {
        $db->exec("ALTER TABLE departments ADD COLUMN description TEXT NULL AFTER code;");
    }
    if (!in_array('status', $colsDepts)) {
        $db->exec("ALTER TABLE departments ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description;");
    }
    if (!in_array('updated_at', $colsDepts)) {
        $db->exec("ALTER TABLE departments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    // 3. Create `academic_sessions`
    $db->exec("
        CREATE TABLE IF NOT EXISTS academic_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            name VARCHAR(50) NOT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            is_current TINYINT(1) DEFAULT 0,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Create `academic_levels`
    $db->exec("
        CREATE TABLE IF NOT EXISTS academic_levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            course_id INT NULL,
            name VARCHAR(100) NOT NULL,
            level_order INT DEFAULT 1,
            description VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 5. Ensure `semesters` columns
    $db->exec("
        CREATE TABLE IF NOT EXISTS semesters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            course_id INT NULL,
            academic_level_id INT NULL,
            name VARCHAR(50) NOT NULL,
            semester_number INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $colsSem = $db->query("DESCRIBE semesters")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('academic_level_id', $colsSem)) {
        $db->exec("ALTER TABLE semesters ADD COLUMN academic_level_id INT NULL AFTER course_id;");
    }

    // 6. Enhance `subjects` table
    $colsSub = $db->query("DESCRIBE subjects")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('course_id', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN course_id INT NULL AFTER tenant_id;");
    }
    if (!in_array('academic_level_id', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN academic_level_id INT NULL AFTER standard_id;");
    }
    if (!in_array('semester_id', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN semester_id INT NULL AFTER academic_level_id;");
    }
    if (!in_array('credits', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN credits INT DEFAULT 3 AFTER type;");
    }
    if (!in_array('max_marks', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN max_marks INT DEFAULT 100 AFTER credits;");
    }
    if (!in_array('passing_marks', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN passing_marks INT DEFAULT 40 AFTER max_marks;");
    }
    if (!in_array('status', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER passing_marks;");
    }
    if (!in_array('updated_at', $colsSub)) {
        $db->exec("ALTER TABLE subjects ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    // 7. Create `subject_offerings`
    $db->exec("
        CREATE TABLE IF NOT EXISTS subject_offerings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            subject_id INT NOT NULL,
            course_id INT NULL,
            department_id INT NULL,
            academic_level_id INT NULL,
            semester_id INT NULL,
            section_id INT NULL,
            academic_session_id INT NULL,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (subject_id),
            INDEX (course_id),
            INDEX (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 8. Enhance `student_enrollments` table
    $colsEnr = $db->query("DESCRIBE student_enrollments")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('academic_session_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN academic_session_id INT NULL AFTER academic_year;");
    }
    if (!in_array('course_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN course_id INT NULL AFTER academic_session_id;");
    }
    if (!in_array('department_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN department_id INT NULL AFTER course_id;");
    }
    if (!in_array('academic_level_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN academic_level_id INT NULL AFTER department_id;");
    }
    if (!in_array('semester_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN semester_id INT NULL AFTER academic_level_id;");
    }
    if (!in_array('section_id', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN section_id INT NULL AFTER class_id;");
    }
    if (!in_array('enrolled_at', $colsEnr)) {
        $db->exec("ALTER TABLE student_enrollments ADD COLUMN enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status;");
    }

    // 9. Create `student_promotions` table
    $db->exec("
        CREATE TABLE IF NOT EXISTS student_promotions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            student_id INT NOT NULL,
            from_enrollment_id INT NOT NULL,
            to_enrollment_id INT NOT NULL,
            from_academic_session VARCHAR(50) NOT NULL,
            to_academic_session VARCHAR(50) NOT NULL,
            from_academic_level VARCHAR(50) NOT NULL,
            to_academic_level VARCHAR(50) NOT NULL,
            operator_id INT NOT NULL,
            status ENUM('success','rejected','override') DEFAULT 'success',
            remarks TEXT NULL,
            promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (tenant_id),
            INDEX (student_id),
            INDEX (from_enrollment_id),
            INDEX (to_enrollment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 10. Seed Default Academic Sessions & Levels for tenant_id = 1 if empty
    $sessCount = (int)$db->query("SELECT COUNT(*) FROM academic_sessions WHERE tenant_id = 1")->fetchColumn();
    if ($sessCount === 0) {
        $db->exec("INSERT INTO academic_sessions (tenant_id, name, start_date, end_date, is_current, status) VALUES 
            (1, '2025-2026', '2025-06-01', '2026-04-30', 0, 'active'),
            (1, '2026-2027', '2026-06-01', '2027-04-30', 1, 'active'),
            (1, '2027-2028', '2027-06-01', '2028-04-30', 0, 'active')
        ;");
    }

    $lvlCount = (int)$db->query("SELECT COUNT(*) FROM academic_levels WHERE tenant_id = 1")->fetchColumn();
    if ($lvlCount === 0) {
        $db->exec("INSERT INTO academic_levels (tenant_id, course_id, name, level_order, description) VALUES 
            (1, NULL, 'First Year (FY)', 1, 'First Year Undergraduate Studies'),
            (1, NULL, 'Second Year (SY)', 2, 'Second Year Undergraduate Studies'),
            (1, NULL, 'Third Year (TY)', 3, 'Third Year Undergraduate Studies'),
            (1, NULL, 'Fourth Year (4Y)', 4, 'Fourth Year Honors / Engineering Studies')
        ;");
    }

    echo "SUCCESS: Academic Module Database Migration Applied Seamlessly!\n";

} catch (\Throwable $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
