<?php
/**
 * Campus360 Enterprise ERP - Fee & Pending Installment Dues Controller
 * 
 * Handles accountant dashboard metrics, pending installment dues reporting,
 * student installment breakdown, search/filtering, and payment reconciliation.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use PDO;

class FeeController {
    /**
     * Enforce authentication and financial permission.
     */
    private function enforceAccess(): array {
        if (!Auth::check()) {
            if ($this->isAjaxRequest()) {
                Response::json(['status' => 'error', 'message' => 'Unauthorized. Please log in.'], 401);
                exit;
            }
            Response::redirect('index.php?page=login');
            exit;
        }

        $user = Auth::user();
        $roleName = strtolower($user['role_name'] ?? '');

        // Accountant, School Admin, Super Admin, and Principal are permitted financial administrators
        $allowedRoles = ['accountant', 'school_admin', 'super_admin', 'principal'];
        if (!in_array($roleName, $allowedRoles, true)) {
            if ($this->isAjaxRequest()) {
                Response::json(['status' => 'error', 'message' => 'Access denied. You do not have permission to view financial records.'], 403);
                exit;
            }
            Session::setFlash('error', 'Access denied. You do not have permission to access financial records.');
            Response::redirect('index.php?page=dashboard');
            exit;
        }

        return $user;
    }

    /**
     * Check if request expects JSON response
     */
    private function isAjaxRequest(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_GET['format']) && $_GET['format'] === 'json');
    }

    /**
     * Get active tenant ID and super admin status
     */
    private function getTenantContext(array $user): array {
        $isSuperAdmin = ($user['role_name'] ?? '') === 'super_admin';
        $tenantId = (int) ($user['tenant_id'] ?? Session::get('tenant_id') ?? 1);
        if ($isSuperAdmin && isset($_GET['tenant_id']) && (int)$_GET['tenant_id'] > 0) {
            $tenantId = (int)$_GET['tenant_id'];
        }
        return [$tenantId, $isSuperAdmin];
    }

    /**
     * Dedicated page displaying Pending Installment Dues
     */
    public function showPendingInstallments(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $db = Database::getInstance();

        // 1. Fetch available filter options for this tenant
        $deptStmt = $db->prepare("SELECT id, name, code FROM departments WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY name ASC");
        $deptStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
        $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

        $courseStmt = $db->prepare("SELECT id, department_id, name, code FROM courses WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY name ASC");
        $courseStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
        $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

        $classStmt = $db->prepare("SELECT id, name, code FROM classes WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY id ASC");
        $classStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
        $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Summary Statistics
        $summary = $this->calculatePendingSummary($db, $tenantId, $isSuperAdmin);

        // 3. Render view
        Response::view('modules/pending_installments', [
            'user' => $user,
            'tenantId' => $tenantId,
            'isSuperAdmin' => $isSuperAdmin,
            'departments' => $departments,
            'courses' => $courses,
            'classes' => $classes,
            'summary' => $summary
        ]);
    }

    /**
     * JSON API Endpoint: Fetch filtered pending installments with pagination
     */
    public function getPendingInstallmentsJson(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $db = Database::getInstance();

        $search = trim($_GET['search'] ?? '');
        $departmentId = (int)($_GET['department_id'] ?? 0);
        $courseId = (int)($_GET['course_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);
        $academicYear = trim($_GET['academic_year'] ?? '');
        $statusFilter = strtolower(trim($_GET['status'] ?? ''));
        $sortBy = strtolower(trim($_GET['sort_by'] ?? 'overdue_first'));
        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage = min(100, max(5, (int)($_GET['per_page'] ?? 15)));
        $groupByStudent = !empty($_GET['group_by_student']) && $_GET['group_by_student'] !== 'false';

        $data = $this->fetchInstallmentData(
            $db,
            $tenantId,
            $isSuperAdmin,
            $search,
            $departmentId,
            $courseId,
            $classId,
            $academicYear,
            $statusFilter,
            $sortBy,
            $page,
            $perPage,
            $groupByStudent
        );

        $summary = $this->calculatePendingSummary($db, $tenantId, $isSuperAdmin);

        Response::json([
            'status' => 'success',
            'summary' => $summary,
            'records' => $data['records'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $data['total_records'],
                'total_pages' => ceil($data['total_records'] / $perPage),
                'unique_students' => $data['unique_students']
            ]
        ]);
    }

    /**
     * JSON API Endpoint: Fetch full installment details and payment history for a single student
     */
    public function getStudentInstallmentDetailsJson(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $studentId = (int)($_GET['student_id'] ?? 0);
        if ($studentId <= 0) {
            Response::json(['status' => 'error', 'message' => 'Valid Student ID is required.'], 400);
            return;
        }

        $db = Database::getInstance();

        // 1. Fetch student info
        $stStmt = $db->prepare("
            SELECT s.*, 
                   d.name AS department_name, 
                   c.name AS course_name, 
                   c.code AS course_code, 
                   cl.name AS class_name, 
                   cl.code AS class_code
            FROM students s
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN classes cl ON s.class_id = cl.id
            WHERE s.id = :sid AND (s.tenant_id = :tid OR :is_super = 1)
        ");
        $stStmt->execute([
            ':sid' => $studentId,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ]);
        $student = $stStmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            Response::json(['status' => 'error', 'message' => 'Student record not found or access denied.'], 404);
            return;
        }

        // 2. Fetch all installments for this student
        $instStmt = $db->prepare("
            SELECT 
                fi.id,
                fi.title,
                fi.amount,
                fi.due_date,
                fi.status AS db_status,
                fi.fee_structure_id,
                fs.fee_type,
                fs.academic_year AS fs_academic_year,
                COALESCE(p.paid_amount, 0.00) AS amount_paid,
                ROUND(fi.amount - COALESCE(p.paid_amount, 0.00), 2) AS outstanding_balance,
                CASE 
                    WHEN ROUND(fi.amount - COALESCE(p.paid_amount, 0.00), 2) <= 0.00 THEN 'paid'
                    WHEN COALESCE(p.paid_amount, 0.00) > 0 AND fi.due_date < CURDATE() THEN 'overdue_partial'
                    WHEN COALESCE(p.paid_amount, 0.00) > 0 THEN 'partial'
                    WHEN fi.due_date < CURDATE() THEN 'overdue'
                    ELSE 'pending'
                END AS computed_status,
                CASE WHEN fi.due_date < CURDATE() THEN 1 ELSE 0 END AS is_overdue
            FROM fee_installments fi
            LEFT JOIN fee_structures fs ON fi.fee_structure_id = fs.id
            LEFT JOIN (
                SELECT installment_id, SUM(amount_paid) AS paid_amount
                FROM fee_collections
                WHERE LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
                  AND status NOT IN ('failed', 'cancelled', 'rejected')
                GROUP BY installment_id
            ) p ON p.installment_id = fi.id
            WHERE fi.student_id = :sid AND (fi.tenant_id = :tid OR :is_super = 1)
            ORDER BY fi.due_date ASC, fi.id ASC
        ");
        $instStmt->execute([
            ':sid' => $studentId,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ]);
        $installments = $instStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch payment history for this student
        $payStmt = $db->prepare("
            SELECT fc.*, fi.title AS installment_title
            FROM fee_collections fc
            LEFT JOIN fee_installments fi ON fc.installment_id = fi.id
            WHERE fc.student_id = :sid AND (fc.tenant_id = :tid OR :is_super = 1)
            ORDER BY fc.paid_at DESC, fc.id DESC
        ");
        $payStmt->execute([
            ':sid' => $studentId,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ]);
        $payments = $payStmt->fetchAll(PDO::FETCH_ASSOC);

        // Aggregate student fee totals
        $totalFees = 0.0;
        $totalPaid = 0.0;
        $totalOutstanding = 0.0;
        foreach ($installments as $inst) {
            $totalFees += (float)$inst['amount'];
            $totalPaid += (float)$inst['amount_paid'];
            $totalOutstanding += max(0.0, (float)$inst['outstanding_balance']);
        }

        Response::json([
            'status' => 'success',
            'student' => [
                'id' => $student['id'],
                'name' => trim(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
                'admission_no' => $student['admission_no'],
                'roll_no' => $student['roll_no'],
                'department_name' => $student['department_name'] ?? 'General',
                'course_name' => $student['course_name'] ?? 'General Course',
                'class_name' => $student['class_name'] ?? 'FY',
                'email' => $student['email'],
                'phone' => $student['phone'],
                'total_fees' => round($totalFees, 2),
                'total_paid' => round($totalPaid, 2),
                'total_outstanding' => round($totalOutstanding, 2)
            ],
            'installments' => $installments,
            'payments' => $payments
        ]);
    }

    /**
     * Record a payment against a student fee installment
     */
    public function recordPayment(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $db = Database::getInstance();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $amountPaid = round((float)($_POST['amount_paid'] ?? 0.0), 2);
        $paymentMode = strtolower(trim($_POST['payment_mode'] ?? 'cash'));
        $transactionId = trim($_POST['transaction_id'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');

        // Validation
        if ($studentId <= 0) {
            Response::json(['status' => 'error', 'message' => 'A valid Student ID is required.'], 400);
            return;
        }
        if ($installmentId <= 0) {
            Response::json(['status' => 'error', 'message' => 'Please select a specific installment to allocate payment.'], 400);
            return;
        }
        if ($amountPaid <= 0.0) {
            Response::json(['status' => 'error', 'message' => 'Payment amount must be greater than zero.'], 400);
            return;
        }
        if (!in_array($paymentMode, ['cash', 'card', 'online', 'cheque'], true)) {
            $paymentMode = 'cash';
        }

        // Fetch installment and verify ownership and balance
        $instStmt = $db->prepare("
            SELECT fi.*, s.first_name, s.last_name, s.admission_no
            FROM fee_installments fi
            JOIN students s ON fi.student_id = s.id
            WHERE fi.id = :iid AND fi.student_id = :sid AND (fi.tenant_id = :tid OR :is_super = 1)
        ");
        $instStmt->execute([
            ':iid' => $installmentId,
            ':sid' => $studentId,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ]);
        $installment = $instStmt->fetch(PDO::FETCH_ASSOC);

        if (!$installment) {
            Response::json(['status' => 'error', 'message' => 'Installment record not found or access denied.'], 404);
            return;
        }

        // Calculate current already paid amount for this installment
        $paidStmt = $db->prepare("
            SELECT COALESCE(SUM(amount_paid), 0.00) AS total_paid
            FROM fee_collections
            WHERE installment_id = :iid AND LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
              AND status NOT IN ('failed', 'cancelled', 'rejected')
        ");
        $paidStmt->execute([':iid' => $installmentId]);
        $alreadyPaid = (float)$paidStmt->fetchColumn();

        $instAmount = (float)$installment['amount'];
        $currentOutstanding = max(0.00, round($instAmount - $alreadyPaid, 2));

        if ($currentOutstanding <= 0.00) {
            Response::json(['status' => 'error', 'message' => 'This installment is already fully settled. No payment required.'], 400);
            return;
        }

        if ($amountPaid > $currentOutstanding + 0.01) {
            Response::json([
                'status' => 'error', 
                'message' => "Payment amount (₹" . number_format($amountPaid, 2) . ") exceeds current outstanding balance (₹" . number_format($currentOutstanding, 2) . ")."
            ], 400);
            return;
        }

        // Generate clean transaction ID if not provided
        if (empty($transactionId)) {
            $prefix = strtoupper(substr($paymentMode, 0, 3));
            $transactionId = $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } else {
            // Prevent duplicate transaction ID
            $dupCheck = $db->prepare("SELECT id FROM fee_collections WHERE transaction_id = :tx LIMIT 1");
            $dupCheck->execute([':tx' => $transactionId]);
            if ($dupCheck->fetchColumn()) {
                Response::json(['status' => 'error', 'message' => "Transaction ID '{$transactionId}' has already been recorded."], 409);
                return;
            }
        }

        $feeStructureId = (int)$installment['fee_structure_id'];

        try {
            $db->beginTransaction();

            // 1. Insert fee collection record
            $fcStmt = $db->prepare("
                INSERT INTO fee_collections 
                (tenant_id, student_id, fee_structure_id, installment_id, amount_paid, payment_mode, transaction_id, status, paid_at)
                VALUES 
                (:tid, :sid, :fsid, :iid, :amt, :mode, :tx, 'paid', NOW())
            ");
            $fcStmt->execute([
                ':tid' => $installment['tenant_id'],
                ':sid' => $studentId,
                ':fsid' => $feeStructureId,
                ':iid' => $installmentId,
                ':amt' => $amountPaid,
                ':mode' => $paymentMode,
                ':tx' => $transactionId
            ]);
            $collectionId = (int)$db->lastInsertId();

            // 2. Re-calculate remaining balance
            $newAlreadyPaid = $alreadyPaid + $amountPaid;
            $newOutstanding = max(0.00, round($instAmount - $newAlreadyPaid, 2));
            $newInstallmentStatus = ($newOutstanding <= 0.00) ? 'paid' : 'unpaid';

            // 3. Update installment status
            $updStmt = $db->prepare("UPDATE fee_installments SET status = :st WHERE id = :iid");
            $updStmt->execute([':st' => $newInstallmentStatus, ':iid' => $installmentId]);

            // 4. Log income transaction in accounts_transactions
            $studentName = trim($installment['first_name'] . ' ' . $installment['last_name']);
            $accDesc = "Fee collection from {$studentName} ({$installment['admission_no']}) - {$installment['title']}";
            if (!empty($remarks)) {
                $accDesc .= " | Remarks: {$remarks}";
            }

            $accStmt = $db->prepare("
                INSERT INTO accounts_transactions 
                (tenant_id, type, category, amount, description, transaction_date, reference_no, created_by, created_at)
                VALUES 
                (:tid, 'income', 'Student Fee Payment', :amt, :descr, CURDATE(), :ref, :uid, NOW())
            ");
            $accStmt->execute([
                ':tid' => $installment['tenant_id'],
                ':amt' => $amountPaid,
                ':descr' => $accDesc,
                ':ref' => $transactionId,
                ':uid' => (int)($user['id'] ?? 1)
            ]);

            // 5. Audit Log
            $auditStmt = $db->prepare("
                INSERT INTO security_audit_logs 
                (tenant_id, user_id, action, ip_address, details)
                VALUES 
                (:tid, :uid, 'FEE_PAYMENT_RECORDED', :ip, :details)
            ");
            $auditStmt->execute([
                ':tid' => $installment['tenant_id'],
                ':uid' => (int)($user['id'] ?? 1),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':details' => "Payment of ₹" . number_format($amountPaid, 2) . " recorded for student {$studentName} (Installment #{$installmentId}, Tx: {$transactionId})"
            ]);

            $db->commit();

            Response::json([
                'status' => 'success',
                'message' => 'Payment recorded successfully! Receipt generated.',
                'receipt' => [
                    'collection_id' => $collectionId,
                    'transaction_id' => $transactionId,
                    'student_name' => $studentName,
                    'admission_no' => $installment['admission_no'],
                    'installment_title' => $installment['title'],
                    'amount_paid' => $amountPaid,
                    'payment_mode' => strtoupper($paymentMode),
                    'remaining_balance' => $newOutstanding,
                    'is_settled' => ($newOutstanding <= 0.00),
                    'paid_at' => date('d M Y, h:i A')
                ]
            ]);

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Fee Payment Recording Error: " . $e->getMessage());
            Response::json(['status' => 'error', 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Auto-sync / generate fee installments for enrolled students based on published fee structures
     */
    public function generateInstallments(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $db = Database::getInstance();

        $courseId = (int)($_POST['course_id'] ?? 0);

        try {
            // Find all active fee structures for this tenant
            $fsQuery = "SELECT * FROM fee_structures WHERE (tenant_id = :t1 OR :is_super1 = 1)";
            $fsParams = [':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)];
            if ($courseId > 0) {
                $fsQuery .= " AND course_id = :cid";
                $fsParams[':cid'] = $courseId;
            }
            $fsStmt = $db->prepare($fsQuery);
            $fsStmt->execute($fsParams);
            $structures = $fsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($structures)) {
                Response::json(['status' => 'error', 'message' => 'No published fee structures found for this course or tenant.'], 404);
                return;
            }

            $totalCreated = 0;
            $db->beginTransaction();

            foreach ($structures as $fs) {
                $cId = (int)$fs['course_id'];
                $fsId = (int)$fs['id'];
                $fsAmount = (float)$fs['amount'];
                $dueDate = !empty($fs['due_date']) ? $fs['due_date'] : date('Y-m-d', strtotime('+30 days'));

                // Find active students in this course who do NOT yet have an installment for this fee structure
                $stStmt = $db->prepare("
                    SELECT s.id, s.first_name, s.last_name 
                    FROM students s
                    WHERE s.course_id = :cid 
                      AND (s.tenant_id = :t1 OR :is_super1 = 1)
                      AND (LOWER(s.status) = 'active' OR s.status IS NULL)
                      AND s.id NOT IN (
                          SELECT student_id FROM fee_installments WHERE fee_structure_id = :fsid
                      )
                ");
                $stStmt->execute([
                    ':cid' => $cId,
                    ':t1' => $tenantId,
                    ':is_super1' => ($isSuperAdmin ? 1 : 0),
                    ':fsid' => $fsId
                ]);
                $eligibleStudents = $stStmt->fetchAll(PDO::FETCH_ASSOC);

                $insStmt = $db->prepare("
                    INSERT INTO fee_installments 
                    (tenant_id, student_id, fee_structure_id, title, amount, due_date, status, created_at)
                    VALUES 
                    (:tid, :sid, :fsid, :title, :amt, :due, 'unpaid', NOW())
                ");

                foreach ($eligibleStudents as $st) {
                    $insStmt->execute([
                        ':tid' => $fs['tenant_id'],
                        ':sid' => $st['id'],
                        ':fsid' => $fsId,
                        ':title' => $fs['fee_type'] . ' - Term Installment',
                        ':amt' => $fsAmount,
                        ':due' => $dueDate
                    ]);
                    $totalCreated++;
                }
            }

            $db->commit();

            Response::json([
                'status' => 'success',
                'message' => "Successfully generated {$totalCreated} installment records for enrolled students.",
                'created_count' => $totalCreated
            ]);

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Generate Installments Error: " . $e->getMessage());
            Response::json(['status' => 'error', 'message' => 'Failed to generate installments: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export Pending Installment Dues to CSV
     */
    public function exportCsv(): void {
        $user = $this->enforceAccess();
        [$tenantId, $isSuperAdmin] = $this->getTenantContext($user);

        $db = Database::getInstance();

        $search = trim($_GET['search'] ?? '');
        $departmentId = (int)($_GET['department_id'] ?? 0);
        $courseId = (int)($_GET['course_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);
        $academicYear = trim($_GET['academic_year'] ?? '');
        $statusFilter = strtolower(trim($_GET['status'] ?? ''));

        $data = $this->fetchInstallmentData(
            $db,
            $tenantId,
            $isSuperAdmin,
            $search,
            $departmentId,
            $courseId,
            $classId,
            $academicYear,
            $statusFilter,
            'overdue_first',
            1,
            5000,
            false
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=pending_installment_dues_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Sr. No.',
            'Student Name',
            'Admission ID',
            'Roll No',
            'Department',
            'Degree Course',
            'Academic Year / Class',
            'Installment Title',
            'Installment Amount (INR)',
            'Amount Paid (INR)',
            'Outstanding Balance (INR)',
            'Due Date',
            'Payment Status'
        ]);

        $sr = 1;
        foreach ($data['records'] as $r) {
            $statusLabel = 'Pending';
            if ($r['computed_status'] === 'overdue') $statusLabel = 'Overdue';
            elseif ($r['computed_status'] === 'overdue_partial') $statusLabel = 'Overdue (Partially Paid)';
            elseif ($r['computed_status'] === 'partial') $statusLabel = 'Partially Paid';
            elseif ($r['computed_status'] === 'paid') $statusLabel = 'Paid';

            fputcsv($out, [
                $sr++,
                trim($r['first_name'] . ' ' . $r['last_name']),
                $r['admission_no'],
                $r['roll_no'],
                $r['department_name'],
                $r['course_name'],
                $r['class_name'],
                $r['installment_title'],
                number_format((float)$r['installment_amount'], 2, '.', ''),
                number_format((float)$r['installment_amount_paid'], 2, '.', ''),
                number_format((float)$r['outstanding_balance'], 2, '.', ''),
                $r['installment_due_date'],
                $statusLabel
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Compute authoritative dashboard summary for pending fee installments
     */
    public function calculatePendingSummary(PDO $db, int $tenantId, bool $isSuperAdmin): array {
        $sql = "
            SELECT 
                COUNT(DISTINCT fi.student_id) AS pending_students_count,
                COUNT(fi.id) AS pending_installments_count,
                COALESCE(SUM(ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2)), 0.00) AS total_outstanding_amount,
                COUNT(DISTINCT CASE WHEN fi.due_date < CURDATE() THEN fi.student_id END) AS overdue_students_count,
                COALESCE(SUM(CASE WHEN fi.due_date < CURDATE() THEN ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) ELSE 0 END), 0.00) AS total_overdue_amount,
                COUNT(DISTINCT CASE WHEN payments.paid_amount > 0 AND ROUND(fi.amount - payments.paid_amount, 2) > 0 THEN fi.student_id END) AS partial_students_count
            FROM fee_installments fi
            JOIN students s ON fi.student_id = s.id
            LEFT JOIN (
                SELECT 
                    installment_id,
                    SUM(amount_paid) AS paid_amount
                FROM fee_collections
                WHERE LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
                  AND status NOT IN ('failed', 'cancelled', 'rejected')
                GROUP BY installment_id
            ) payments ON payments.installment_id = fi.id
            WHERE (fi.tenant_id = :t1 OR :is_super1 = 1)
              AND (s.tenant_id = :t2 OR :is_super2 = 1)
              AND (ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) > 0.00)
              AND (fi.status != 'paid' OR ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) > 0.00)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t1' => $tenantId,
            ':is_super1' => ($isSuperAdmin ? 1 : 0),
            ':t2' => $tenantId,
            ':is_super2' => ($isSuperAdmin ? 1 : 0),
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Also fetch total collections recorded
        $colStmt = $db->prepare("
            SELECT COALESCE(SUM(amount_paid), 0.00) AS total_collected
            FROM fee_collections
            WHERE (tenant_id = :t1 OR :is_super1 = 1)
              AND LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
              AND status NOT IN ('failed', 'cancelled', 'rejected')
        ");
        $colStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
        $totalCollected = (float)$colStmt->fetchColumn();

        return [
            'pending_students_count' => (int)($row['pending_students_count'] ?? 0),
            'pending_installments_count' => (int)($row['pending_installments_count'] ?? 0),
            'total_outstanding_amount' => round((float)($row['total_outstanding_amount'] ?? 0.0), 2),
            'overdue_students_count' => (int)($row['overdue_students_count'] ?? 0),
            'total_overdue_amount' => round((float)($row['total_overdue_amount'] ?? 0.0), 2),
            'partial_students_count' => (int)($row['partial_students_count'] ?? 0),
            'total_collected_amount' => round($totalCollected, 2)
        ];
    }

    /**
     * Internal query builder to retrieve filtered installment records
     */
    private function fetchInstallmentData(
        PDO $db,
        int $tenantId,
        bool $isSuperAdmin,
        string $search,
        int $departmentId,
        int $courseId,
        int $classId,
        string $academicYear,
        string $statusFilter,
        string $sortBy,
        int $page,
        int $perPage,
        bool $groupByStudent
    ): array {
        $whereClauses = [
            "(fi.tenant_id = :t1 OR :is_super1 = 1)",
            "(s.tenant_id = :t2 OR :is_super2 = 1)",
            "(ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) > 0.00)",
            "(fi.status != 'paid' OR ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) > 0.00)"
        ];

        $params = [
            ':t1' => $tenantId,
            ':is_super1' => ($isSuperAdmin ? 1 : 0),
            ':t2' => $tenantId,
            ':is_super2' => ($isSuperAdmin ? 1 : 0),
        ];

        if (!empty($search)) {
            $whereClauses[] = "(
                s.first_name LIKE :srch1 
                OR s.last_name LIKE :srch2 
                OR s.admission_no LIKE :srch3 
                OR s.roll_no LIKE :srch4
                OR fi.title LIKE :srch5
            )";
            $srchVal = '%' . $search . '%';
            $params[':srch1'] = $srchVal;
            $params[':srch2'] = $srchVal;
            $params[':srch3'] = $srchVal;
            $params[':srch4'] = $srchVal;
            $params[':srch5'] = $srchVal;
        }

        if ($departmentId > 0) {
            $whereClauses[] = "s.department_id = :dept_id";
            $params[':dept_id'] = $departmentId;
        }

        if ($courseId > 0) {
            $whereClauses[] = "s.course_id = :course_id";
            $params[':course_id'] = $courseId;
        }

        if ($classId > 0) {
            $whereClauses[] = "s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        if (!empty($academicYear)) {
            $whereClauses[] = "(fs.academic_year = :ay OR cl.code = :ay_code OR cl.name LIKE :ay_name)";
            $params[':ay'] = $academicYear;
            $params[':ay_code'] = $academicYear;
            $params[':ay_name'] = '%' . $academicYear . '%';
        }

        // Installment Status Filter
        if ($statusFilter === 'overdue') {
            $whereClauses[] = "fi.due_date < CURDATE()";
        } elseif ($statusFilter === 'pending') {
            $whereClauses[] = "fi.due_date >= CURDATE()";
        } elseif ($statusFilter === 'partial') {
            $whereClauses[] = "COALESCE(payments.paid_amount, 0.00) > 0";
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Sorting
        $orderSql = "is_overdue DESC, fi.due_date ASC, fi.id ASC";
        if ($sortBy === 'due_date_asc') {
            $orderSql = "fi.due_date ASC, fi.id ASC";
        } elseif ($sortBy === 'due_date_desc') {
            $orderSql = "fi.due_date DESC, fi.id DESC";
        } elseif ($sortBy === 'amount_desc') {
            $orderSql = "outstanding_balance DESC, fi.due_date ASC";
        } elseif ($sortBy === 'amount_asc') {
            $orderSql = "outstanding_balance ASC, fi.due_date ASC";
        } elseif ($sortBy === 'student_name') {
            $orderSql = "s.first_name ASC, s.last_name ASC, fi.due_date ASC";
        }

        // Count total matching records and distinct students
        $countSql = "
            SELECT 
                COUNT(fi.id) AS total_count,
                COUNT(DISTINCT fi.student_id) AS student_count
            FROM fee_installments fi
            JOIN students s ON fi.student_id = s.id
            LEFT JOIN fee_structures fs ON fi.fee_structure_id = fs.id
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN classes cl ON s.class_id = cl.id
            LEFT JOIN (
                SELECT 
                    installment_id,
                    SUM(amount_paid) AS paid_amount
                FROM fee_collections
                WHERE LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
                  AND status NOT IN ('failed', 'cancelled', 'rejected')
                GROUP BY installment_id
            ) payments ON payments.installment_id = fi.id
            WHERE {$whereSql}
        ";
        $cStmt = $db->prepare($countSql);
        $cStmt->execute($params);
        $counts = $cStmt->fetch(PDO::FETCH_ASSOC);
        $totalRecords = (int)($counts['total_count'] ?? 0);
        $uniqueStudents = (int)($counts['student_count'] ?? 0);

        // Fetch records with pagination
        $offset = ($page - 1) * $perPage;

        $listSql = "
            SELECT 
                fi.id AS installment_id,
                fi.title AS installment_title,
                fi.amount AS installment_amount,
                fi.due_date AS installment_due_date,
                fi.status AS installment_status,
                fi.fee_structure_id,
                fs.fee_type,
                fs.academic_year AS fs_academic_year,
                fs.amount AS structure_amount,
                s.id AS student_id,
                s.admission_no,
                s.roll_no,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.email AS student_email,
                s.phone AS student_phone,
                d.id AS department_id,
                COALESCE(d.name, 'N/A') AS department_name,
                c.id AS course_id,
                COALESCE(c.name, 'N/A') AS course_name,
                c.code AS course_code,
                cl.id AS class_id,
                COALESCE(cl.name, 'N/A') AS class_name,
                COALESCE(cl.code, 'N/A') AS class_code,
                COALESCE(payments.paid_amount, 0.00) AS installment_amount_paid,
                ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) AS outstanding_balance,
                CASE 
                    WHEN ROUND(fi.amount - COALESCE(payments.paid_amount, 0.00), 2) <= 0.00 THEN 'paid'
                    WHEN COALESCE(payments.paid_amount, 0.00) > 0 AND fi.due_date < CURDATE() THEN 'overdue_partial'
                    WHEN COALESCE(payments.paid_amount, 0.00) > 0 THEN 'partial'
                    WHEN fi.due_date < CURDATE() THEN 'overdue'
                    ELSE 'pending'
                END AS computed_status,
                CASE WHEN fi.due_date < CURDATE() THEN 1 ELSE 0 END AS is_overdue
            FROM fee_installments fi
            JOIN students s ON fi.student_id = s.id
            LEFT JOIN fee_structures fs ON fi.fee_structure_id = fs.id
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN classes cl ON s.class_id = cl.id
            LEFT JOIN (
                SELECT 
                    installment_id,
                    SUM(amount_paid) AS paid_amount
                FROM fee_collections
                WHERE LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
                  AND status NOT IN ('failed', 'cancelled', 'rejected')
                GROUP BY installment_id
            ) payments ON payments.installment_id = fi.id
            WHERE {$whereSql}
            ORDER BY {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $db->prepare($listSql);
        $stmt->execute($params);
        $rawRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preload student total fees across all installments for students in current page
        $studentIds = array_unique(array_column($rawRecords, 'student_id'));
        $studentTotals = [];
        if (!empty($studentIds)) {
            $inClause = implode(',', array_map('intval', $studentIds));
            $stTotSql = "
                SELECT 
                    fi.student_id,
                    SUM(fi.amount) AS total_student_fees,
                    SUM(COALESCE(p.paid_amount, 0.00)) AS total_student_paid,
                    SUM(ROUND(fi.amount - COALESCE(p.paid_amount, 0.00), 2)) AS total_student_outstanding
                FROM fee_installments fi
                LEFT JOIN (
                    SELECT installment_id, SUM(amount_paid) AS paid_amount
                    FROM fee_collections
                    WHERE LOWER(status) IN ('paid', 'partial', 'approved', 'active', 'completed')
                      AND status NOT IN ('failed', 'cancelled', 'rejected')
                    GROUP BY installment_id
                ) p ON p.installment_id = fi.id
                WHERE fi.student_id IN ({$inClause})
                GROUP BY fi.student_id
            ";
            $stTotStmt = $db->query($stTotSql);
            while ($row = $stTotStmt->fetch(PDO::FETCH_ASSOC)) {
                $studentTotals[$row['student_id']] = [
                    'total_fees' => round((float)$row['total_student_fees'], 2),
                    'total_paid' => round((float)$row['total_student_paid'], 2),
                    'total_outstanding' => round((float)$row['total_student_outstanding'], 2)
                ];
            }
        }

        // Attach student totals to each record
        foreach ($rawRecords as &$rec) {
            $sId = $rec['student_id'];
            $tot = $studentTotals[$sId] ?? [
                'total_fees' => (float)$rec['installment_amount'],
                'total_paid' => (float)$rec['installment_amount_paid'],
                'total_outstanding' => (float)$rec['outstanding_balance']
            ];
            $rec['student_total_fees'] = $tot['total_fees'];
            $rec['student_total_paid'] = $tot['total_paid'];
            $rec['student_total_outstanding'] = $tot['total_outstanding'];
        }
        unset($rec);

        if ($groupByStudent) {
            $grouped = [];
            foreach ($rawRecords as $rec) {
                $sId = $rec['student_id'];
                if (!isset($grouped[$sId])) {
                    $grouped[$sId] = [
                        'student_id' => $sId,
                        'student_name' => trim($rec['first_name'] . ' ' . $rec['last_name']),
                        'admission_no' => $rec['admission_no'],
                        'roll_no' => $rec['roll_no'],
                        'department_name' => $rec['department_name'],
                        'course_name' => $rec['course_name'],
                        'class_name' => $rec['class_name'],
                        'total_fees' => $rec['student_total_fees'],
                        'total_paid' => $rec['student_total_paid'],
                        'outstanding_balance' => $rec['student_total_outstanding'],
                        'installments' => []
                    ];
                }
                $grouped[$sId]['installments'][] = [
                    'installment_id' => $rec['installment_id'],
                    'installment_title' => $rec['installment_title'],
                    'installment_amount' => $rec['installment_amount'],
                    'installment_due_date' => $rec['installment_due_date'],
                    'installment_amount_paid' => $rec['installment_amount_paid'],
                    'outstanding_balance' => $rec['outstanding_balance'],
                    'computed_status' => $rec['computed_status'],
                    'is_overdue' => $rec['is_overdue']
                ];
            }
            return [
                'records' => array_values($grouped),
                'total_records' => $totalRecords,
                'unique_students' => $uniqueStudents
            ];
        }

        return [
            'records' => $rawRecords,
            'total_records' => $totalRecords,
            'unique_students' => $uniqueStudents
        ];
    }
}
