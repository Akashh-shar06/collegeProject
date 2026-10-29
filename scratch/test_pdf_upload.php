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

Session::start();
Session::set('user_id', 2);
Session::set('tenant_id', 1);
Session::set('role_name', 'admin_officer');

$uploadDir = BASE_PATH . '/public/uploads/students/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$dummyPdfPath = $uploadDir . 'test_photo_doc.pdf';
file_put_contents($dummyPdfPath, "%PDF-1.4 %Sample PDF Photo Document Content%");

$db = Database::getInstance();

$testId = 'STU_PDF_' . rand(1000, 9999);
$email = strtolower($testId) . '@campus360.edu';

$uHash = password_hash('Pass@123', PASSWORD_BCRYPT);
$db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (1, :u, :em, :p, 5, 'active')")
   ->execute([':u' => $testId, ':em' => $email, ':p' => $uHash]);
$uid = (int)$db->lastInsertId();

$pdfRelativePath = 'uploads/students/test_photo_doc.pdf';

$db->prepare("INSERT INTO students (tenant_id, user_id, admission_no, roll_no, first_name, last_name, gender, dob, phone, email, batch_id, photo, status) VALUES (1, :uid, :adm, '888P', 'Ankit', 'Test', 'male', '2005-02-10', '+91 9876543210', :em, 1, :ph, 'active')")
   ->execute([':uid' => $uid, ':adm' => $testId, ':em' => $email, ':ph' => $pdfRelativePath]);
$stId = (int)$db->lastInsertId();

echo "Created test student ID {$stId} with PDF photo '{$pdfRelativePath}'!\n";

$stRow = $db->query("SELECT * FROM students WHERE id = {$stId}")->fetch();
echo "\n--- VERIFICATION FROM DATABASE ---\n";
echo "Student ID: " . $stRow['admission_no'] . "\n";
echo "Name: " . $stRow['first_name'] . " " . $stRow['last_name'] . "\n";
echo "Photo Path: " . $stRow['photo'] . "\n";
echo "Is PDF File: " . (strtolower(pathinfo($stRow['photo'], PATHINFO_EXTENSION)) === 'pdf' ? 'YES ✅' : 'NO ❌') . "\n";
