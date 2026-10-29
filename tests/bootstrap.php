<?php
/**
 * Campus360 ERP Test Suite Bootstrap
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('TESTING_MODE')) {
    define('TESTING_MODE', true);
}

// Load Security Headers class definition if needed
require_once BASE_PATH . '/config/security.php';

// Autoload App classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Ensure DB test user password synchronization & seed default prerequisite test data
try {
    $db = \App\Core\Database::getInstance();
    $db->exec("INSERT INTO tenants (id, name, code, domain, status) VALUES (1, 'Campus360 Main College Institution', 'CAMPUS_360', 'campus360.edu', 'active'), (2, 'St. Mark Higher Education Academy', 'ST_MARK', 'stmark.edu', 'active') ON DUPLICATE KEY UPDATE status = 'active'");
    $db->exec("INSERT INTO departments (id, tenant_id, name, code) VALUES 
        (1, 1, 'B.Sc. Computer Science', 'BSC-CS'), 
        (2, 1, 'B.Sc. IT', 'BSC-IT'), 
        (3, 1, 'B.Com', 'BCOM'), 
        (4, 1, 'BCA', 'BCA'),
        (99, 2, 'College B Dept', 'CBD') 
        ON DUPLICATE KEY UPDATE name = VALUES(name), code = VALUES(code)");
    $db->exec("INSERT INTO classes (id, tenant_id, name, code) VALUES (1, 1, '1st Year', 'YR1'), (2, 1, '2nd Year', 'YR2'), (3, 1, '3rd Year', 'YR3') ON DUPLICATE KEY UPDATE name = VALUES(name), code = VALUES(code)");
    $db->exec("INSERT INTO sections (id, tenant_id, class_id, name) VALUES (1, 1, 1, 'Division A'), (2, 1, 1, 'Division B') ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $db->exec("INSERT INTO courses (id, tenant_id, department_id, name, code, duration_years) VALUES 
        (1, 1, 1, 'B.Sc. Computer Science', 'BSC-CS', 3), 
        (2, 1, 2, 'B.Sc. IT', 'BSC-IT', 3), 
        (3, 1, 3, 'B.Com', 'BCOM', 3),
        (4, 1, 4, 'BCA', 'BCA', 3) 
        ON DUPLICATE KEY UPDATE name = VALUES(name), code = VALUES(code)");
    $db->exec("INSERT INTO semesters (id, tenant_id, course_id, name, semester_number) VALUES (1, 1, 2, 'Semester 1', 1), (5, 1, 2, 'Semester 5', 5) ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $db->exec("INSERT INTO classrooms (id, tenant_id, room_number, building_block, capacity) VALUES (1, 1, 'Block 101', 'Main Academic Building', 60), (2, 1, 'Block 102', 'Main Wing', 60) ON DUPLICATE KEY UPDATE room_number = VALUES(room_number)");
    $db->exec("INSERT INTO batches (id, tenant_id, course_id, batch_name, start_year, end_year) VALUES (1, 1, 1, 'CSE Batch 2024-2028', 2024, 2028), (2, 1, 2, 'B.Sc IT Sem 5 Div A Batch', 2024, 2027) ON DUPLICATE KEY UPDATE batch_name = VALUES(batch_name)");
    
    $validHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $db->exec("UPDATE users SET password_hash = '{$validHash}', failed_attempts = 0, status = 'active', lock_until = NULL");
    $db->exec("INSERT INTO users (id, tenant_id, username, email, password_hash, role_id, status) VALUES 
        (3, 1, 'dr_soap', 'soap@campus360.edu', '{$validHash}', 4, 'active'), 
        (5, 1, 'accountant_mary', 'mary@campus360.edu', '{$validHash}', 7, 'active'),
        (6, 1, 'john_student', 'john@campus360.edu', '{$validHash}', 5, 'active'),
        (7, 1, 'robert_parent', 'robert@campus360.edu', '{$validHash}', 6, 'active'),
        (8, 1, 'principal_john', 'principal@campus360.edu', '{$validHash}', 3, 'active'),
        (99, 2, 'college_b_student', 'colb@campus360.edu', '{$validHash}', 5, 'active')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), status = 'active'");
    
    $db->exec("INSERT INTO staff (id, tenant_id, user_id, emp_id, first_name, last_name, designation, department_id, phone, email, joining_date, salary) VALUES (1, 1, 3, 'EMP-001', 'Prof. Khan', 'Mactavish', 'Senior Professor', 2, '+1 555-0192', 'soap@campus360.edu', '2020-01-15', 85000.00) ON DUPLICATE KEY UPDATE first_name = VALUES(first_name)");

    $db->exec("INSERT INTO students (id, tenant_id, user_id, parent_user_id, admission_no, roll_no, first_name, last_name, gender, dob, phone, email, guardian_name, guardian_phone, batch_id, class_id, section_id, department_id, course_id, semester_id, status) VALUES 
        (1, 1, 6, 7, 'ADM-2024-001', '24IT001', 'John', 'Doe', 'male', '2004-05-14', '+1 555-0101', 'john@campus360.edu', 'Robert Doe', 'robert_parent', 2, 1, 1, 2, 2, 5, 'active'),
        (99, 2, 99, NULL, 'ADM-COLB-001', 'COLB001', 'Alice', 'Smith', 'female', '2005-01-01', '+1 555-9999', 'colb@campus360.edu', 'Parent Smith', '99999', 1, 1, 1, 4, 4, 1, 'active')
        ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), user_id = VALUES(user_id), department_id = VALUES(department_id), course_id = VALUES(course_id), semester_id = VALUES(semester_id), section_id = VALUES(section_id), batch_id = VALUES(batch_id)");

    $db->exec("INSERT INTO time_tables (id, tenant_id, academic_year, department_id, course_id, semester_id, section_id, batch_id, day_of_week, time_slot, start_time, end_time, subject_name, staff_id, classroom_id, room_no) VALUES 
        (1, 1, '2026-2027', 2, 2, 5, 1, 2, 'Monday', '07:10 AM - 08:00 AM', '07:10:00', '08:00:00', 'Database Management System', 1, 1, 'Block 101'),
        (2, 1, '2026-2027', 3, 3, 5, 1, 1, 'Monday', '07:10 AM - 08:00 AM', '07:10:00', '08:00:00', 'Financial Accounting', 1, 2, 'Block 102'),
        (99, 2, '2026-2027', 4, 4, 1, 1, 1, 'Monday', '09:00 AM - 10:00 AM', '09:00:00', '10:00:00', 'College B Course Subject', 1, 1, 'Room B')
        ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name), department_id = VALUES(department_id), course_id = VALUES(course_id), semester_id = VALUES(semester_id), section_id = VALUES(section_id), batch_id = VALUES(batch_id)");
} catch (\Throwable $e) {
    // Ignore DB setup error if DB not reachable during pure unit tests
}




