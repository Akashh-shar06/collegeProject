<?php
define('BASE_PATH', dirname(__DIR__));
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
        require $file;
    }
});

use App\Core\Database;

$db = Database::getInstance();
$testId = 'STU_' . rand(10000, 99999);
$email = strtolower($testId) . '@campus360.edu';

// Insert test user with role_id = 5 (student)
$passHash = password_hash('Student@123', PASSWORD_BCRYPT);
$uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (1, :u, :em, :pass, 5, 'active')");
$uStmt->execute([':u' => $testId, ':em' => $email, ':pass' => $passHash]);
$userId = (int)$db->lastInsertId();

$stmt = $db->prepare("
    INSERT INTO students 
    (tenant_id, user_id, admission_no, roll_no, first_name, last_name, gender, dob, phone, guardian_phone, mother_phone, email, guardian_name, department_id, standard_id, class_id, section_id, batch_id, photo, status)
    VALUES 
    (1, :uid, :adm, '101A', 'Rohan', 'Verma', 'male', '2005-04-12', '+91 9123456789', '+91 9876543210', '+91 9876543211', :em, 'Suresh Verma', 1, 1, 1, 1, 1, 'uploads/students/test.jpg', 'active')
");
$stmt->execute([
    ':uid' => $userId,
    ':adm' => $testId,
    ':em' => $email
]);
$studentId = (int)$db->lastInsertId();

echo "Successfully admitted student with ID {$studentId}!\n";
$st = $db->query("SELECT * FROM students WHERE id = {$studentId}")->fetch();
print_r($st);
