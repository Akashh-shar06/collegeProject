<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;

class LeaveController
{
    public function showLeaveModule(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        Permission::enforce('leave_management');

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;

        // Fetch Leave Types with Auto-Provisioning
        $leaveTypes = [];
        try {
            $stmt = $db->prepare("SELECT * FROM leave_types WHERE (tenant_id = :tid OR tenant_id IS NULL OR 1=1) AND status = 'active' ORDER BY id ASC");
            $stmt->execute([':tid' => $tenantId]);
            $leaveTypes = $stmt->fetchAll();

            if (empty($leaveTypes)) {
                $defaults = [
                    ['Casual Leave', 'CL', 12, 'Routine personal casual leave allocation per year'],
                    ['Sick Leave', 'SL', 10, 'Medical and sickness leave with doctor verification'],
                    ['Earned Leave', 'EL', 15, 'Accumulated annual privilege earned leave'],
                    ['Emergency Leave', 'EML', 5, 'Immediate family emergency or urgent leave'],
                    ['Medical Leave', 'ML', 14, 'Extended medical care leave'],
                    ['Maternity Leave', 'MAT', 90, 'Maternity leave for eligible female staff'],
                    ['Paternity Leave', 'PAT', 15, 'Paternity leave for eligible male staff'],
                    ['Unpaid Leave', 'LWP', 30, 'Leave without pay']
                ];
                foreach ($defaults as $d) {
                    $insStmt = $db->prepare("INSERT INTO leave_types (tenant_id, name, code, days_allowed, description, status) 
                                              VALUES (:tid, :name, :code, :days, :desc, 'active')
                                              ON DUPLICATE KEY UPDATE name = VALUES(name)");
                    $insStmt->execute([
                        ':tid' => $tenantId ?? 1,
                        ':name' => $d[0],
                        ':code' => $d[1],
                        ':days' => $d[2],
                        ':desc' => $d[3]
                    ]);
                }
                $stmt = $db->query("SELECT * FROM leave_types WHERE status = 'active' ORDER BY id ASC");
                $leaveTypes = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $leaveTypes = [];
        }


        // Fetch Logged-In User Leave Balances
        $myBalances = [];
        try {
            $stmt = $db->prepare("SELECT lb.*, lt.name as leave_type_name, lt.code as leave_type_code 
                                  FROM leave_balances lb 
                                  JOIN leave_types lt ON lb.leave_type_id = lt.id 
                                  WHERE lb.user_id = :uid AND lb.academic_year = '2026-2027'");
            $stmt->execute([':uid' => $user['id']]);
            $myBalances = $stmt->fetchAll();

            // If no balances exist yet, initialize default balances for user
            if (empty($myBalances) && !empty($leaveTypes)) {
                foreach ($leaveTypes as $lt) {
                    $initStmt = $db->prepare("INSERT INTO leave_balances (tenant_id, user_id, leave_type_id, allocated_days, used_days, remaining_days, academic_year)
                                              VALUES (:tid, :uid, :ltid, :days, 0, :days, '2026-2027')
                                              ON DUPLICATE KEY UPDATE allocated_days = VALUES(allocated_days)");
                    $initStmt->execute([
                        ':tid' => $tenantId,
                        ':uid' => $user['id'],
                        ':ltid' => $lt['id'],
                        ':days' => $lt['days_allowed'] ?? 12
                    ]);
                }
                $stmt->execute([':uid' => $user['id']]);
                $myBalances = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $myBalances = [];
        }

        // Fetch My Leave Requests
        $myRequests = [];
        try {
            $stmt = $db->prepare("SELECT lr.*, lt.code as leave_code 
                                  FROM leave_requests lr 
                                  LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                                  WHERE lr.user_id = :uid 
                                  ORDER BY lr.id DESC");
            $stmt->execute([':uid' => $user['id']]);
            $myRequests = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $myRequests = [];
        }

        // HR Review Queue & All Staff Requests (for HR / Principal)
        $pendingHrRequests = [];
        $pendingPrincipalRequests = [];
        $allRequests = [];

        $roleName = $user['role_name'] ?? 'teacher';
        $isHr = in_array($roleName, ['hr', 'hr_executive', 'super_admin']);
        $isPrincipal = in_array($roleName, ['principal', 'super_admin', 'school_admin']);

        if ($isHr || $isPrincipal) {
            try {
                $allStmt = $db->query("SELECT lr.*, u.username, lt.code as leave_code 
                                       FROM leave_requests lr 
                                       JOIN users u ON lr.user_id = u.id 
                                       LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                                       ORDER BY lr.id DESC LIMIT 100");
                $allRequests = $allStmt->fetchAll();

                // HR Pending Queue (exclude HR's own requests from HR queue)
                $hrStmt = $db->prepare("SELECT lr.*, u.username, lt.code as leave_code 
                                        FROM leave_requests lr 
                                        JOIN users u ON lr.user_id = u.id 
                                        LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                                        WHERE lr.status IN ('submitted', 'under_hr_review') AND lr.user_id != :uid 
                                        ORDER BY lr.id ASC");
                $hrStmt->execute([':uid' => $user['id']]);
                $pendingHrRequests = $hrStmt->fetchAll();

                // Principal Pending Queue
                $pStmt = $db->query("SELECT lr.*, u.username, lt.code as leave_code 
                                     FROM leave_requests lr 
                                     JOIN users u ON lr.user_id = u.id 
                                     LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                                     WHERE lr.status = 'pending_principal_approval' 
                                     ORDER BY lr.id ASC");
                $pendingPrincipalRequests = $pStmt->fetchAll();
            } catch (\Throwable $e) {
            }
        }

        Response::view('modules/leave_management', [
            'user' => $user,
            'leaveTypes' => $leaveTypes,
            'myBalances' => $myBalances,
            'myRequests' => $myRequests,
            'pendingHrRequests' => $pendingHrRequests,
            'pendingPrincipalRequests' => $pendingPrincipalRequests,
            'allRequests' => $allRequests,
            'isHr' => $isHr,
            'isPrincipal' => $isPrincipal,
            'canManage' => Permission::canManage('leave_management'),
            'accessLevel' => Permission::getAccessLevel('leave_management'),
        ]);
    }

    public function applyLeave(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        Permission::enforce('leave_management');

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;

        $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
        $fromDate = Validator::sanitize($_POST['from_date'] ?? '');
        $toDate = Validator::sanitize($_POST['to_date'] ?? '');
        $reason = Validator::sanitize($_POST['reason'] ?? '');
        $contactNumber = Validator::sanitize($_POST['contact_number'] ?? '');

        if (!$leaveTypeId || empty($fromDate) || empty($toDate) || empty($reason)) {
            Session::set('flash_error', 'Please fill all required leave application fields.');
            header('Location: index.php?page=module&name=leave_management');
            exit;
        }

        // Calculate Days
        $fromTs = strtotime($fromDate);
        $toTs = strtotime($toDate);
        if ($toTs < $fromTs) {
            Session::set('flash_error', 'End date cannot be prior to start date.');
            header('Location: index.php?page=module&name=leave_management');
            exit;
        }
        $totalDays = (int)max(1, round(($toTs - $fromTs) / 86400) + 1);

        // Fetch Leave Type Info
        $ltStmt = $db->prepare("SELECT name FROM leave_types WHERE id = :id");
        $ltStmt->execute([':id' => $leaveTypeId]);
        $leaveTypeName = $ltStmt->fetchColumn() ?: 'Casual Leave';

        // Fetch Staff Details if available
        $staffRow = null;
        try {
            $stfStmt = $db->prepare("SELECT s.id as staff_id, s.emp_id, s.first_name, s.last_name, s.designation, d.name as dept_name 
                                     FROM staff s 
                                     LEFT JOIN departments d ON s.department_id = d.id 
                                     WHERE s.user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $staffRow = $stfStmt->fetch();
        } catch (\Throwable $e) {
        }

        $empName = $staffRow ? ($staffRow['first_name'] . ' ' . $staffRow['last_name']) : ($user['username'] ?? 'Employee');
        $empCode = $staffRow['emp_id'] ?? ('USR-' . $user['id']);
        $jobRole = $user['role_display'] ?? $user['role_name'] ?? 'Staff Member';
        $deptName = $staffRow['dept_name'] ?? 'Administration';
        $staffId = $staffRow['staff_id'] ?? null;

        // Handle Document Upload
        $documentPath = null;
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/leave_docs/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
            $fileName = 'leave_' . $user['id'] . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                $documentPath = 'public/uploads/leave_docs/' . $fileName;
            }
        }

        // Determine Initial Status & Workflow Route
        $roleName = $user['role_name'] ?? 'teacher';
        if ($roleName === 'principal') {
            // Principal Leave -> Sent to Super Admin for Final Approval
            $initialStatus = 'pending_super_admin_approval';
        } elseif (in_array($roleName, ['hr', 'hr_executive'])) {
            // HR Employee -> Direct to Principal (bypasses HR review)
            $initialStatus = 'pending_principal_approval';
        } else {
            // All Teaching & Non-Teaching Staff -> Submitted for HR Review
            $initialStatus = 'submitted';
        }


        try {
            $insertStmt = $db->prepare("INSERT INTO leave_requests 
                (tenant_id, user_id, staff_id, employee_name, employee_code, job_role, department_name, leave_type_id, leave_type_name, from_date, to_date, total_days, reason, document_path, contact_number, status)
                VALUES (:tid, :uid, :sid, :ename, :ecode, :jrole, :dname, :ltid, :ltname, :fdate, :tdate, :tdays, :reason, :doc, :contact, :status)");

            $insertStmt->execute([
                ':tid' => $tenantId,
                ':uid' => $user['id'],
                ':sid' => $staffId,
                ':ename' => $empName,
                ':ecode' => $empCode,
                ':jrole' => $jobRole,
                ':dname' => $deptName,
                ':ltid' => $leaveTypeId,
                ':ltname' => $leaveTypeName,
                ':fdate' => $fromDate,
                ':tdate' => $toDate,
                ':tdays' => $totalDays,
                ':reason' => $reason,
                ':doc' => $documentPath,
                ':contact' => $contactNumber,
                ':status' => $initialStatus
            ]);

            $requestId = (int)$db->lastInsertId();

            // Create Security Audit Log
            $auditStmt = $db->prepare("INSERT INTO leave_audit_logs (tenant_id, request_id, user_id, role_name, action, previous_status, new_status, remarks, ip_address)
                                       VALUES (:tid, :req_id, :uid, :rname, 'LEAVE_SUBMITTED', NULL, :nstatus, :remarks, :ip)");
            $auditStmt->execute([
                ':tid' => $tenantId,
                ':req_id' => $requestId,
                ':uid' => $user['id'],
                ':rname' => $roleName,
                ':nstatus' => $initialStatus,
                ':remarks' => 'Leave application submitted by employee.',
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            Session::set('flash_success', 'Leave application submitted successfully! Status: ' . str_replace('_', ' ', strtoupper($initialStatus)));
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to submit leave application: ' . $e->getMessage());
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }

    public function cancelLeave(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        if (!$requestId) {
            header('Location: index.php?page=module&name=leave_management');
            exit;
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("SELECT * FROM leave_requests WHERE id = :id AND user_id = :uid");
            $stmt->execute([':id' => $requestId, ':uid' => $user['id']]);
            $req = $stmt->fetch();

            if ($req && in_array($req['status'], ['draft', 'submitted', 'under_hr_review', 'pending_principal_approval'])) {
                $upd = $db->prepare("UPDATE leave_requests SET status = 'cancelled' WHERE id = :id");
                $upd->execute([':id' => $requestId]);

                $auditStmt = $db->prepare("INSERT INTO leave_audit_logs (tenant_id, request_id, user_id, role_name, action, previous_status, new_status, remarks, ip_address)
                                           VALUES (:tid, :req_id, :uid, :rname, 'LEAVE_CANCELLED', :pstatus, 'cancelled', 'Cancelled by employee.', :ip)");
                $auditStmt->execute([
                    ':tid' => $req['tenant_id'],
                    ':req_id' => $requestId,
                    ':uid' => $user['id'],
                    ':rname' => $user['role_name'] ?? 'staff',
                    ':pstatus' => $req['status'],
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);

                Session::set('flash_success', 'Leave request cancelled.');
            } else {
                Session::set('flash_error', 'Cannot cancel leave request in current status.');
            }
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Error cancelling leave request.');
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }

    public function hrReview(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role_name'] ?? '', ['hr', 'hr_executive', 'super_admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden - Only HR Executives can review leave applications.";
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $hrRemarks = Validator::sanitize($_POST['hr_remarks'] ?? '');
        $recommendation = Validator::sanitize($_POST['hr_recommendation'] ?? 'recommended');

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("SELECT * FROM leave_requests WHERE id = :id");
            $stmt->execute([':id' => $requestId]);
            $req = $stmt->fetch();

            if (!$req) {
                Session::set('flash_error', 'Leave request not found.');
                header('Location: index.php?page=module&name=leave_management');
                exit;
            }

            // Prevent HR from reviewing their own leave (must go directly to Principal)
            if ($req['user_id'] == $user['id']) {
                Session::set('flash_error', 'HR Executives cannot review their own leave applications. Request is routed directly to Principal.');
                header('Location: index.php?page=module&name=leave_management');
                exit;
            }

            $newStatus = ($recommendation === 'returned') ? 'submitted' : 'pending_principal_approval';
            $upd = $db->prepare("UPDATE leave_requests SET 
                status = :status, 
                hr_user_id = :hruid, 
                hr_remarks = :remarks, 
                hr_recommendation = :recom, 
                hr_reviewed_at = NOW() 
                WHERE id = :id");

            $upd->execute([
                ':status' => $newStatus,
                ':hruid' => $user['id'],
                ':remarks' => $hrRemarks,
                ':recom' => $recommendation,
                ':id' => $requestId
            ]);

            // Audit Log
            $auditStmt = $db->prepare("INSERT INTO leave_audit_logs (tenant_id, request_id, user_id, role_name, action, previous_status, new_status, remarks, ip_address)
                                       VALUES (:tid, :req_id, :uid, :rname, 'HR_REVIEWED', :pstatus, :nstatus, :remarks, :ip)");
            $auditStmt->execute([
                ':tid' => $req['tenant_id'],
                ':req_id' => $requestId,
                ':uid' => $user['id'],
                ':rname' => $user['role_name'],
                ':pstatus' => $req['status'],
                ':nstatus' => $newStatus,
                ':remarks' => 'HR Recommendation: ' . strtoupper($recommendation) . '. Remarks: ' . $hrRemarks,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            Session::set('flash_success', 'Leave request verified & forwarded to Principal for final approval.');
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to update HR review: ' . $e->getMessage());
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }

    public function principalApproval(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role_name'] ?? '', ['principal', 'super_admin', 'school_admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden - Only the Principal has final leave approval authority.";
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = Validator::sanitize($_POST['action'] ?? 'approve'); // approve or reject
        $principalRemarks = Validator::sanitize($_POST['principal_remarks'] ?? '');

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("SELECT * FROM leave_requests WHERE id = :id");
            $stmt->execute([':id' => $requestId]);
            $req = $stmt->fetch();

            if (!$req) {
                Session::set('flash_error', 'Leave request not found.');
                header('Location: index.php?page=module&name=leave_management');
                exit;
            }

            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

            $upd = $db->prepare("UPDATE leave_requests SET 
                status = :status, 
                principal_user_id = :puid, 
                principal_remarks = :remarks, 
                principal_approved_at = NOW() 
                WHERE id = :id");

            $upd->execute([
                ':status' => $newStatus,
                ':puid' => $user['id'],
                ':remarks' => $principalRemarks,
                ':id' => $requestId
            ]);

            // IF APPROVED: Deduct Leave Balance & Sync with Staff Attendance
            if ($newStatus === 'approved') {
                // 1. Deduct Leave Balance
                $balStmt = $db->prepare("UPDATE leave_balances SET 
                    used_days = used_days + :tdays, 
                    remaining_days = GREATEST(0, remaining_days - :tdays2) 
                    WHERE user_id = :uid AND leave_type_id = :ltid AND academic_year = '2026-2027'");

                $balStmt->execute([
                    ':tdays' => $req['total_days'],
                    ':tdays2' => $req['total_days'],
                    ':uid' => $req['user_id'],
                    ':ltid' => $req['leave_type_id']
                ]);

                // 2. Sync with Staff Attendance (mark 'leave' for date range)
                if (!empty($req['staff_id'])) {
                    $currDate = strtotime($req['from_date']);
                    $endDate = strtotime($req['to_date']);
                    while ($currDate <= $endDate) {
                        $dateStr = date('Y-m-d', $currDate);
                        $attStmt = $db->prepare("INSERT INTO staff_attendance (tenant_id, staff_id, date, status, remarks)
                                                 VALUES (:tid, :sid, :dt, 'leave', 'Approved Leave Request')
                                                 ON DUPLICATE KEY UPDATE status = 'leave', remarks = VALUES(remarks)");
                        $attStmt->execute([
                            ':tid' => $req['tenant_id'],
                            ':sid' => $req['staff_id'],
                            ':dt' => $dateStr
                        ]);
                        $currDate = strtotime('+1 day', $currDate);
                    }
                }
            }

            // Audit Log
            $auditAction = ($newStatus === 'approved') ? 'PRINCIPAL_APPROVED' : 'PRINCIPAL_REJECTED';
            $auditStmt = $db->prepare("INSERT INTO leave_audit_logs (tenant_id, request_id, user_id, role_name, action, previous_status, new_status, remarks, ip_address)
                                       VALUES (:tid, :req_id, :uid, :rname, :act, :pstatus, :nstatus, :remarks, :ip)");
            $auditStmt->execute([
                ':tid' => $req['tenant_id'],
                ':req_id' => $requestId,
                ':uid' => $user['id'],
                ':rname' => $user['role_name'],
                ':act' => $auditAction,
                ':pstatus' => $req['status'],
                ':nstatus' => $newStatus,
                ':remarks' => 'Principal Action: ' . strtoupper($newStatus) . '. Remarks: ' . $principalRemarks,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            Session::set('flash_success', 'Leave request has been ' . strtoupper($newStatus) . ' successfully!');
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to process Principal approval: ' . $e->getMessage());
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }

    public function saveLeaveType(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role_name'] ?? '', ['hr', 'hr_executive', 'principal', 'super_admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden";
            exit;
        }

        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = strtoupper(Validator::sanitize($_POST['code'] ?? ''));
        $daysAllowed = (int)($_POST['days_allowed'] ?? 12);
        $description = Validator::sanitize($_POST['description'] ?? '');

        if (empty($name) || empty($code)) {
            Session::set('flash_error', 'Leave Type Name and Code are required.');
            header('Location: index.php?page=module&name=leave_management');
            exit;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;

        try {
            $stmt = $db->prepare("INSERT INTO leave_types (tenant_id, name, code, days_allowed, description, status)
                                  VALUES (:tid, :name, :code, :days, :desc, 'active')
                                  ON DUPLICATE KEY UPDATE name = VALUES(name), days_allowed = VALUES(days_allowed)");
            $stmt->execute([
                ':tid' => $tenantId,
                ':name' => $name,
                ':code' => $code,
                ':days' => $daysAllowed,
                ':desc' => $description
            ]);

            Session::set('flash_success', 'Leave type configured successfully!');
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to save leave type: ' . $e->getMessage());
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }

    public function superAdminLeaveApproval(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role_name'] ?? '', ['super_admin', 'school_admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden - Only Super Admin can approve Principal leave requests.";
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = Validator::sanitize($_POST['action'] ?? 'approve');
        $remarks = Validator::sanitize($_POST['super_admin_remarks'] ?? '');

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("SELECT * FROM leave_requests WHERE id = :id");
            $stmt->execute([':id' => $requestId]);
            $req = $stmt->fetch();

            if (!$req) {
                Session::set('flash_error', 'Leave request not found.');
                header('Location: index.php?page=module&name=leave_management');
                exit;
            }

            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

            $upd = $db->prepare("UPDATE leave_requests SET 
                status = :status, 
                super_admin_user_id = :sauid, 
                super_admin_remarks = :remarks, 
                super_admin_approved_at = NOW() 
                WHERE id = :id");

            $upd->execute([
                ':status' => $newStatus,
                ':sauid' => $user['id'],
                ':remarks' => $remarks,
                ':id' => $requestId
            ]);

            if ($newStatus === 'approved') {
                $balStmt = $db->prepare("UPDATE leave_balances SET 
                    used_days = used_days + :tdays, 
                    remaining_days = GREATEST(0, remaining_days - :tdays2) 
                    WHERE user_id = :uid AND leave_type_id = :ltid AND academic_year = '2026-2027'");

                $balStmt->execute([
                    ':tdays' => $req['total_days'],
                    ':tdays2' => $req['total_days'],
                    ':uid' => $req['user_id'],
                    ':ltid' => $req['leave_type_id']
                ]);
            }

            Session::set('flash_success', 'Principal leave request ' . strtoupper($newStatus) . ' by Super Admin.');
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Error processing Super Admin approval: ' . $e->getMessage());
        }

        header('Location: index.php?page=module&name=leave_management');
        exit;
    }
}

