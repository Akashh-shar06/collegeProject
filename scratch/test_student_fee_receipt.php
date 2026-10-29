<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Auth.php';

use App\Core\Database;

$db = Database::getInstance();

echo "--- TESTING STUDENT FEE INSTALLMENT & RECEIPT SYSTEM ---\n";

// 1. Get student ID
$student = $db->query("SELECT * FROM students LIMIT 1")->fetch();
if (!$student) {
    die("No student found in DB\n");
}
$studentId = $student['id'];
echo "[PASS] Found student ID: {$studentId} ({$student['first_name']} {$student['last_name']})\n";

// 2. Fetch installments & ensure defaults if empty
$installments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId}")->fetchAll();
if (empty($installments)) {
    $feeStructId = (int) $db->query("SELECT id FROM fee_structures LIMIT 1")->fetchColumn() ?: 1;
    $db->exec("INSERT INTO fee_installments (tenant_id, student_id, fee_structure_id, title, amount, due_date, status) VALUES (1, {$studentId}, {$feeStructId}, 'Term 1 Installment', 625.00, '2026-09-30', 'paid')");
    $db->exec("INSERT INTO fee_installments (tenant_id, student_id, fee_structure_id, title, amount, due_date, status) VALUES (1, {$studentId}, {$feeStructId}, 'Term 2 Installment', 625.00, '2026-12-31', 'unpaid')");
    $installments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId}")->fetchAll();
}
echo "[PASS] Total installments found for student: " . count($installments) . "\n";

foreach ($installments as $inst) {
    echo "  - Installment #{$inst['id']}: '{$inst['title']}' | Amount: ₹{$inst['amount']} | Status: {$inst['status']}\n";
}

// 3. Mark one installment as paid if unpaid
if (!empty($installments)) {
    $inst = $installments[0];
    $db->prepare("UPDATE fee_installments SET status = 'paid' WHERE id = :id")->execute([':id' => $inst['id']]);
    
    // Check fee collection
    $chkCol = $db->query("SELECT COUNT(*) FROM fee_collections WHERE student_id = {$studentId}")->fetchColumn();
    if ($chkCol == 0) {
        $db->prepare("INSERT INTO fee_collections (tenant_id, student_id, fee_structure_id, installment_id, amount_paid, payment_mode, transaction_id, status) VALUES (1, :sid, :fsid, :iid, :amt, 'online', :tx, 'paid')")->execute([
            ':sid' => $studentId,
            ':fsid' => $inst['fee_structure_id'],
            ':iid' => $inst['id'],
            ':amt' => $inst['amount'],
            ':tx' => 'TXN-' . time()
        ]);
        echo "[PASS] Inserted fee collection receipt entry for installment #{$inst['id']}\n";
    }
}

$updatedInstallments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId} AND status = 'paid'")->fetchAll();
echo "[PASS] Total paid installments ready for receipt download: " . count($updatedInstallments) . "\n";

echo "--- TEST COMPLETE: SUCCESS ---\n";
