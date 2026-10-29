<?php
/**
 * Campus360 Enterprise ERP - Communication Module CRUD Test
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

echo "========================================================\n";
echo "      COMMUNICATION MODULE CRUD TEST RUNNER             \n";
echo "========================================================\n\n";

// 1. Authenticate as schooladmin
Auth::attempt('schooladmin', 'Admin@123');
$user = Auth::user();
echo "  [PASS] Authenticated as " . $user['username'] . " (Tenant ID: " . $user['tenant_id'] . ")\n";

// Helper function for sub-process execution
function runSubprocess(string $code): string {
    $base = addslashes(BASE_PATH);
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; {$code}";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    return (string)shell_exec($cmd);
}

// 2. Create Communication Notice Record
$storeCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = ['module_key' => 'communication', 'title' => 'Emergency Campus Closure Notice', 'target_role' => 'all', 'content' => 'Campus will remain closed tomorrow due to weather.']; (new \\App\\Controllers\\ModuleController())->storeRecord();";
runSubprocess($storeCode);

$db = Database::getInstance();
$notice = $db->query("SELECT * FROM notices WHERE title = 'Emergency Campus Closure Notice' ORDER BY id DESC LIMIT 1")->fetch();

if (!empty($notice)) {
    echo "  [PASS] Store Record: Created notice ID #" . $notice['id'] . " (Status: " . $notice['status'] . ", Target: " . $notice['target_role'] . ")\n";
} else {
    echo "  [FAIL] Store Record: Notice not found in database!\n";
    exit(1);
}

// 3. Update Communication Notice Record
$noticeId = $notice['id'];
$updateCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = ['module_key' => 'communication', 'record_id' => {$noticeId}, 'title' => 'Updated Emergency Closure Notice', 'status' => 'published', 'content' => 'Updated text details']; (new \\App\\Controllers\\ModuleController())->updateRecord();";
runSubprocess($updateCode);

$updatedNotice = $db->query("SELECT * FROM notices WHERE id = {$noticeId}")->fetch();
if (!empty($updatedNotice) && $updatedNotice['title'] === 'Updated Emergency Closure Notice') {
    echo "  [PASS] Update Record: Successfully updated notice #" . $noticeId . " title to '" . $updatedNotice['title'] . "'\n";
} else {
    echo "  [FAIL] Update Record failed!\n";
    exit(1);
}

// 4. Delete Communication Notice Record
$deleteCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = ['module_key' => 'communication', 'record_id' => {$noticeId}]; (new \\App\\Controllers\\ModuleController())->deleteRecord();";
runSubprocess($deleteCode);

$deletedCheck = $db->query("SELECT * FROM notices WHERE id = {$noticeId}")->fetch();
if (empty($deletedCheck)) {
    echo "  [PASS] Delete Record: Successfully deleted notice #" . $noticeId . " from database\n";
} else {
    echo "  [FAIL] Delete Record failed!\n";
    exit(1);
}

echo "\n========================================================\n";
echo "COMMUNICATION CRUD VERIFICATION COMPLETE: ALL PASSED!\n";
echo "========================================================\n";
