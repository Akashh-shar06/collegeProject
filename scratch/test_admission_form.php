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

use App\Core\Session;
use App\Core\Database;
use App\Controllers\WorkflowController;

Session::start();
Session::set('user_id', 2);
Session::set('tenant_id', 1);
Session::set('role_name', 'admin_officer');

$testId = 'STU_TEST_' . rand(1000, 9999);
$email = strtolower($testId) . '@campus360.edu';

$_POST = [
    'first_name' => 'Aarav',
    'last_name' => 'Patel',
    'email' => $email,
    'phone' => '+91 9988776655',
    'guardian_phone' => '+91 9876500001',
    'mother_phone' => '+91 9876500002',
    'department_id' => 1,
    'standard_id' => 2,
    'class_id' => 1,
    'dob' => '2004-08-20',
    'student_id' => $testId,
    'password' => 'Pass@12345',
    'gender' => 'male',
    'photo' => 'uploads/students/sample_photo.jpg'
];

try {
    $controller = new WorkflowController();
    $controller->admitStudent();
} catch (\Throwable $e) {
    // catch any output header exceptions in CLI
}

// Verify record inserted into students table
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM students WHERE admission_no = :adm LIMIT 1");
$stmt->execute([':adm' => $testId]);
$st = $stmt->fetch();

if ($st) {
    echo "\n=== ADMISSION TEST SUCCESSFUL ===\n";
    echo "Student ID: " . $st['admission_no'] . "\n";
    echo "Name: " . $st['first_name'] . " " . $st['last_name'] . "\n";
    echo "Email: " . $st['email'] . "\n";
    echo "Student Phone: " . $st['phone'] . "\n";
    echo "Father Mobile: " . $st['guardian_phone'] . "\n";
    echo "Mother Mobile: " . $st['mother_phone'] . "\n";
    echo "Department ID: " . $st['department_id'] . "\n";
    echo "Year (Standard ID): " . $st['standard_id'] . "\n";
    echo "Class ID: " . $st['class_id'] . "\n";
    echo "DOB: " . $st['dob'] . "\n";
    echo "Photo: " . $st['photo'] . "\n";
    echo "User ID: " . $st['user_id'] . "\n";

    // Verify User Account Created
    $uStmt = $db->prepare("SELECT * FROM users WHERE id = :uid LIMIT 1");
    $uStmt->execute([':uid' => $st['user_id']]);
    $u = $uStmt->fetch();
    if ($u) {
        echo "User Account: " . $u['username'] . " (Role ID: " . $u['role_id'] . ")\n";
    }
} else {
    echo "\n=== ERROR: STUDENT RECORD NOT FOUND ===\n";
}
