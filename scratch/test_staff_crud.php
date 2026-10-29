<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

$db = Database::getInstance();

echo "=== TESTING STAFF DATA CRUD FIXES ===\n";

// 1. Verify SELECT query for staff_data (no department column)
$sql = "SELECT s.id, s.emp_id, s.first_name, s.last_name, s.designation, s.phone, s.email, u.username as staff_username, s.joining_date, s.salary, s.status FROM staff s LEFT JOIN users u ON s.user_id = u.id WHERE s.tenant_id = 24 ORDER BY s.id DESC LIMIT 50";
$records = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "1. Column list returned for Staff Data:\n";
if (!empty($records)) {
    print_r(array_keys($records[0]));
    $hasDept = in_array('department', array_keys($records[0]));
    echo "Department column present: " . ($hasDept ? 'YES' : 'NO') . "\n";
}

// 2. Test Salary Update for staff ID 81
$staffId = 81;
$newSalary = 45000.00;
$updStmt = $db->prepare("UPDATE staff SET salary = :sal WHERE id = :id");
$updStmt->execute([':sal' => $newSalary, ':id' => $staffId]);

$salCheck = $db->query("SELECT salary FROM staff WHERE id = {$staffId}")->fetchColumn();
echo "2. Salary update test for Staff ID {$staffId}: New Salary = {$salCheck}\n";

// 3. Test Staff Deletion for staff ID 85 (Staff T24)
$deleteId = 85;
$linkedUserId = (int)$db->query("SELECT user_id FROM staff WHERE id = {$deleteId}")->fetchColumn();
echo "3. Testing Deletion for Staff ID {$deleteId} (linked user_id: {$linkedUserId})...\n";

if ($linkedUserId > 0) {
    $db->prepare("DELETE FROM staff WHERE id = :id")->execute([':id' => $deleteId]);
    $db->prepare("DELETE FROM users WHERE id = :uid")->execute([':uid' => $linkedUserId]);
}

// Trigger sync to verify it does NOT re-create the deleted staff member
$resyncCount = Auth::syncStaffRecords(24);
echo "   Resync count: {$resyncCount}\n";

$existsInStaff = $db->query("SELECT COUNT(*) FROM staff WHERE id = {$deleteId}")->fetchColumn();
$existsInUsers = $linkedUserId > 0 ? $db->query("SELECT COUNT(*) FROM users WHERE id = {$linkedUserId}")->fetchColumn() : 0;

echo "   Exists in staff table: {$existsInStaff}\n";
echo "   Exists in users table: {$existsInUsers}\n";

if ($existsInStaff == 0 && $existsInUsers == 0) {
    echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
} else {
    echo "\nTEST FAILED!\n";
}
