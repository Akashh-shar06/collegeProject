<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Controllers\ModuleController;

Auth::attempt('schooladmin', 'Admin@123');
$db = Database::getInstance();

echo "=== TESTING SINGLE STUDENT CREATION ===\n";
$email = 'student_single_' . rand(100, 999) . '@example.com';
$_POST = [
    'csrf_token' => \App\Core\CSRF::getToken(),
    'module_key' => 'student_mis',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => $email,
    'phone' => '1234567890',
    'username' => 'john_' . rand(100, 999),
    'password' => 'Pass@123'
];

// Execute store logic directly
$mc = new ModuleController();

// Use Reflection to execute storeRecord without exit/redirect
$ref = new ReflectionClass($mc);
$method = $ref->getMethod('storeRecord');

try {
    $method->invoke($mc);
} catch (\Throwable $e) {
    echo "EX: " . $e->getMessage() . "\n";
}

$row = $db->query("SELECT * FROM students WHERE email = '{$email}'")->fetch();
print_r($row);
