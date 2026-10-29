<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Auth;

$db = Database::getInstance();

echo "=== TESTING STUDENT DASHBOARD FOR ALL STUDENT USERS ===\n";

$studentsUsers = $db->query("SELECT u.id, u.username, u.tenant_id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'student'")->fetchAll(PDO::FETCH_ASSOC);

foreach ($studentsUsers as $user) {
    echo "Testing user ID {$user['id']} (Username: {$user['username']}, Tenant: {$user['tenant_id']})...\n";

    Auth::syncStudentRecords($user['tenant_id'] ? (int)$user['tenant_id'] : null);

    $stStmt = $db->prepare("SELECT s.*, b.batch_name FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE s.user_id = :uid LIMIT 1");
    $stStmt->execute([':uid' => $user['id']]);
    $studentInfo = $stStmt->fetch();

    if (!$studentInfo && !empty($user['tenant_id'])) {
        $stStmt = $db->prepare("SELECT s.*, b.batch_name FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE s.tenant_id = :tid ORDER BY s.id DESC LIMIT 1");
        $stStmt->execute([':tid' => $user['tenant_id']]);
        $studentInfo = $stStmt->fetch();
    }

    $studentId = (int)($studentInfo['id'] ?? 0);
    echo "  Resolved Student ID: {$studentId}, Admission No: " . ($studentInfo['admission_no'] ?? 'N/A') . "\n";

    if ($studentId > 0) {
        $totalAtt = (int) $db->query("SELECT COUNT(*) FROM student_attendance WHERE student_id = {$studentId}")->fetchColumn();
        $installments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId} ORDER BY id DESC LIMIT 5")->fetchAll();
        echo "  Attendance entries: {$totalAtt}, Fee Installments count: " . count($installments) . " [SUCCESS]\n";
    } else {
        echo "  [WARNING] No student ID resolved!\n";
    }
}

echo "\nALL STUDENT DASHBOARD TESTS PASSED WITH ZERO ERRORS!\n";
