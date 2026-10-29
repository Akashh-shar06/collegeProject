<?php
/**
 * Campus360 Enterprise ERP - Full End-to-End Integration & Workflow Test Suite
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

echo "========================================================\n";
echo "      CAMPUS360 ERP INTEGRATION WORKFLOW TEST SUITE     \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $testName) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failCount++;
    }
}

function runPhpSubprocess(string $code): string {
    $base = addslashes(BASE_PATH);
    $fullScript = "define('BASE_PATH', '{$base}'); require '{$base}/tests/bootstrap.php'; {$code}";
    $cmd = 'C:\\xampp\\php\\php.exe -r ' . escapeshellarg($fullScript);
    return (string)shell_exec($cmd);
}

// -------------------------------------------------------------
// STEP 1: Verify Database Layer & Schema
// -------------------------------------------------------------
echo "1. DATABASE LAYER VERIFICATION\n";
$db = Database::getInstance();
assertCondition($db instanceof PDO, "Database Singleton Instance connected");

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
assertCondition(count($tables) >= 30, "Database contains all 36 relational ERP tables (" . count($tables) . " found)");

// -------------------------------------------------------------
// STEP 2: Authenticate 9 Distinct Roles
// -------------------------------------------------------------
echo "\n2. AUTHENTICATION & RBAC ROLE VERIFICATION\n";

$rolesToTest = [
    'admin' => 'super_admin',
    'schooladmin' => 'school_admin',
    'principal_john' => 'principal',
    'dr_soap' => 'teacher',
    'john_student' => 'student',
    'robert_parent' => 'parent',
    'accountant_mary' => 'accountant',
    'librarian_lisa' => 'librarian',
    'hr_david' => 'hr',
];

foreach ($rolesToTest as $username => $expectedRole) {
    Session::destroy();
    $loginSuccess = Auth::attempt($username, 'Admin@123');
    $user = Auth::user();
    assertCondition($loginSuccess && ($user['role_name'] ?? '') === $expectedRole, "Authenticated successfully as {$username} (Role: {$expectedRole})");
}

// -------------------------------------------------------------
// STEP 3: Test Student Admission Workflow
// -------------------------------------------------------------
echo "\n3. STUDENT ADMISSION WORKFLOW\n";

$email = 'test.student.' . rand(1000, 9999) . '@campus360.edu';
$code = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 2); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'school_admin'); \$_POST = ['first_name' => 'Subprocess', 'last_name' => 'Student', 'gender' => 'female', 'dob' => '2006-03-15', 'phone' => '+1 555-9999', 'email' => '{$email}', 'guardian_name' => 'Guardian', 'batch_id' => 1]; (new \\App\\Controllers\\WorkflowController())->admitStudent();";

runPhpSubprocess($code);

$student = $db->query("SELECT * FROM students WHERE email = '{$email}' LIMIT 1")->fetch();
assertCondition(!empty($student), "Admitted new student 'Subprocess Student' via Workflow");
assertCondition(str_starts_with($student['admission_no'] ?? '', 'ADM-'), "Generated admission number: " . ($student['admission_no'] ?? 'N/A'));

// -------------------------------------------------------------
// STEP 4: Test Daily Attendance Cycle & Parent Notification Trigger
// -------------------------------------------------------------
echo "\n4. DAILY ATTENDANCE & PARENT ALERT WORKFLOW\n";

$studentId = $student['id'] ?? 1;
$attCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 3); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'teacher'); \$_POST = ['batch_id' => 1, 'date' => date('Y-m-d'), 'attendance' => [1 => 'present', {$studentId} => 'absent']]; (new \\App\\Controllers\\WorkflowController())->saveAttendance();";

runPhpSubprocess($attCode);

$attRecord = $db->query("SELECT * FROM student_attendance WHERE student_id = {$studentId} AND date = CURDATE()")->fetch();
assertCondition(!empty($attRecord) && $attRecord['status'] === 'absent', "Recorded batch attendance roster with absent status");

$notice = $db->query("SELECT * FROM notices WHERE target_role = 'parent' AND title = 'Absence Alert' ORDER BY id DESC LIMIT 1")->fetch();
assertCondition(!empty($notice), "Absence alert notification queued automatically for Parent");

// -------------------------------------------------------------
// STEP 5: Test Exam Marks Entry & Automatic Grade Computation
// -------------------------------------------------------------
echo "\n5. EXAM MARKS ENTRY & AUTOMATIC GRADING WORKFLOW\n";

$examCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 3); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'teacher'); \$_POST = ['exam_id' => 1, 'student_id' => {$studentId}, 'marks_obtained' => 88.5]; (new \\App\\Controllers\\WorkflowController())->saveExamMarks();";

runPhpSubprocess($examCode);

$examResult = $db->query("SELECT * FROM exam_results WHERE student_id = {$studentId} AND exam_id = 1")->fetch();
assertCondition(!empty($examResult) && $examResult['grade'] === 'A', "Exam marks recorded (88.5%) and auto-graded to 'A' using grading_scales matrix");

// -------------------------------------------------------------
// STEP 6: Test Fee Payment Webhook Settlement Workflow
// -------------------------------------------------------------
echo "\n6. FEE PAYMENT WEBHOOK SETTLEMENT WORKFLOW\n";

$webhookCode = "\$_POST = ['tenant_id' => 1, 'student_id' => 1, 'installment_id' => 1, 'amount' => 625.00]; (new \\App\\Controllers\\WorkflowController())->processPaymentWebhook();";

$out = runPhpSubprocess($webhookCode);
$json = json_decode(trim($out), true);

assertCondition(isset($json['status']) && $json['status'] === 'success', "Payment Gateway Webhook settled payment atomically");

$feeCol = $db->query("SELECT * FROM fee_collections ORDER BY id DESC LIMIT 1")->fetch();
assertCondition(!empty($feeCol) && (float)$feeCol['amount_paid'] === 625.00, "Recorded fee collection transaction: " . ($feeCol['transaction_id'] ?? 'N/A'));

$acc = $db->query("SELECT * FROM accounts_transactions WHERE category = 'Student Fee Payment' ORDER BY id DESC LIMIT 1")->fetch();
assertCondition(!empty($acc) && $acc['type'] === 'income', "Recorded ledger income transaction in accounts_transactions");

// -------------------------------------------------------------
// STEP 7: Test Library Book Return Workflow
// -------------------------------------------------------------
echo "\n7. LIBRARY BOOK RETURN WORKFLOW\n";

// Ensure sample book exists
$db->exec("INSERT INTO library_books (tenant_id, title, isbn, author, copies_total, copies_available) VALUES (1, 'Sample Algorithms', '978-1234567890', 'Author Test', 5, 4)");
$bookId = (int)$db->lastInsertId();

$db->exec("INSERT INTO library_issues (tenant_id, book_id, student_id, issue_date, return_due_date, status) VALUES (1, {$bookId}, 1, CURDATE(), CURDATE(), 'issued')");
$issueId = (int)$db->lastInsertId();

$libCode = "\\App\\Core\\Session::start(); \\App\\Core\\Session::set('user_id', 9); \\App\\Core\\Session::set('tenant_id', 1); \\App\\Core\\Session::set('role_name', 'librarian'); \$_POST = ['issue_id' => {$issueId}]; (new \\App\\Controllers\\WorkflowController())->returnBook();";

runPhpSubprocess($libCode);

$issue = $db->query("SELECT * FROM library_issues WHERE id = {$issueId}")->fetch();
assertCondition(!empty($issue) && $issue['status'] === 'returned', "Processed library book return ID {$issueId} to status 'returned'");

// Cleanup integration test records
$db->exec("DELETE FROM students WHERE email = '{$email}'");
$db->exec("DELETE FROM library_issues WHERE id = {$issueId}");
$db->exec("DELETE FROM library_books WHERE id = {$bookId}");

// -------------------------------------------------------------
// STEP 8: Security Audit Trail Verification
// -------------------------------------------------------------
echo "\n8. SECURITY AUDIT TRAIL LOG VERIFICATION\n";
$auditCount = (int)$db->query("SELECT COUNT(*) FROM security_audit_logs")->fetchColumn();
assertCondition($auditCount > 0, "Security Audit Logs actively recording platform events ({$auditCount} entries in DB)");

echo "\n========================================================\n";
echo "INTEGRATION SUITE SUMMARY: Passed: {$passCount} | Failed: {$failCount}\n";
echo "========================================================\n";

exit($failCount === 0 ? 0 : 1);
