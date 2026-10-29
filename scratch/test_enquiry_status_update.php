<?php
/**
 * Campus360 Enterprise ERP - Enquiry Status Update Test Runner
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Database;
use App\Core\Session;

echo "========================================================\n";
echo "       ENQUIRY STATUS UPDATE TEST RUNNER                \n";
echo "========================================================\n\n";

$db = Database::getInstance();

// 1. Get or Insert a test enquiry
$stmt = $db->query("SELECT id FROM enquiries WHERE tenant_id = 1 ORDER BY id DESC LIMIT 1");
$enquiryId = $stmt->fetchColumn();

if (!$enquiryId) {
    $db->exec("INSERT INTO enquiries (tenant_id, student_name, email, phone, course_interested, status) VALUES (1, 'Harsh Singh', 'harsh@gmail.com', '65416546564', 'bsc-it', 'pending')");
    $enquiryId = $db->lastInsertId();
}

echo "Testing Update on Enquiry ID #{$enquiryId}...\n";

// Subprocess execution helper
function runUpdateSubprocess(array $postData): string {
    $base = addslashes(BASE_PATH);
    $encoded = base64_encode(serialize($postData));
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; \\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = unserialize(base64_decode('{$encoded}')); (new \\App\\Controllers\\ModuleController())->updateRecord();";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    return (string)shell_exec($cmd);
}

// Perform update attempt with status='active' (UI label value) and assigned_to='akash'
$postData = [
    'module_key' => 'enquiry',
    'record_id' => (int)$enquiryId,
    'student_name' => 'Harsh Singh',
    'email' => 'harsh@gmail.com',
    'phone' => '65416546564',
    'course_interested' => 'bsc-it',
    'status' => 'active', // Should automatically map to 'admitted'
    'assigned_to' => 'akash', // Non-numeric staff name string
    'source' => 'website'
];

runUpdateSubprocess($postData);

// Verify database status
$checkStmt = $db->prepare("SELECT status, assigned_to FROM enquiries WHERE id = :id");
$checkStmt->execute([':id' => $enquiryId]);
$record = $checkStmt->fetch(PDO::FETCH_ASSOC);

echo " Updated Status in Database: '{$record['status']}'\n";
echo " Updated Assigned To in DB: " . var_export($record['assigned_to'], true) . "\n\n";

if ($record['status'] === 'admitted') {
    echo " [SUCCESS] Enquiry Status successfully updated to 'admitted' (Approved)!\n";
    exit(0);
} else {
    echo " [FAILURE] Status remains '{$record['status']}'. Check error log.\n";
    if (file_exists(BASE_PATH . '/scratch/last_error.log')) {
        echo "Error detail: " . file_get_contents(BASE_PATH . '/scratch/last_error.log') . "\n";
    }
    exit(1);
}
