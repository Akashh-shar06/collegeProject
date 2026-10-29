<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<?php
$roleName = $user['role_name'] ?? 'teacher';
$isHr = in_array($roleName, ['hr', 'hr_executive', 'super_admin']);
$isPrincipal = in_array($roleName, ['principal', 'super_admin', 'school_admin']);
?>

<main class="dashboard-viewport">
    <!-- FLASH MESSAGES -->
    <?php if ($flashSuccess = \App\Core\Session::get('flash_success')): ?>
        <div style="background:#DCFCE7; color:#166534; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #86EFAC; display:flex; justify-content:space-between; align-items:center;">
            <span>✅ <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#166534; font-size:16px; cursor:pointer;">&times;</button>
        </div>
        <?php \App\Core\Session::remove('flash_success'); ?>
    <?php endif; ?>

    <?php if ($flashError = \App\Core\Session::get('flash_error')): ?>
        <div style="background:#FEE2E2; color:#991B1B; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #FCA5A5; display:flex; justify-content:space-between; align-items:center;">
            <span>⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#991B1B; font-size:16px; cursor:pointer;">&times;</button>
        </div>
        <?php \App\Core\Session::remove('flash_error'); ?>
    <?php endif; ?>

    <!-- HEADER BANNER CARD -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:2px;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">📅</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Enterprise Staff Leave Management & Workflow</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">RBAC Integrated</span>
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <?php if ($isHr || $isPrincipal): ?>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('manageLeaveTypesModal').classList.add('active')" style="font-size:12px; font-weight:700;">⚙️ Configure Leave Types</button>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('applyLeaveModal').classList.add('active')" style="font-size:13px; font-weight:700; padding:10px 20px;">+ Apply New Leave</button>
        </div>
    </div>

    <!-- LEAVE BALANCE SUMMARY CARDS -->
    <div style="margin-bottom:24px;">
        <h3 style="font-size:14px; font-weight:800; color:#334155; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
            📊 My Configured Leave Balances (Academic Year 2026-2027)
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
            <?php if (!empty($myBalances)): ?>
                <?php foreach ($myBalances as $bal): ?>
                    <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                        <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;"><?= htmlspecialchars($bal['leave_type_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($bal['leave_type_code'], ENT_QUOTES, 'UTF-8') ?>)</div>
                        <div style="font-size:24px; font-weight:800; color:#2563EB; margin:6px 0;"><?= $bal['remaining_days'] ?> <span style="font-size:12px; font-weight:600; color:#64748B;">days left</span></div>
                        <div style="font-size:11px; color:#475569; display:flex; justify-content:space-between; border-top:1px dashed #E2E8F0; padding-top:6px;">
                            <span>Allocated: <strong><?= $bal['allocated_days'] ?></strong></span>
                            <span>Used: <strong style="color:#DC2626;"><?= $bal['used_days'] ?></strong></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; text-align:center; color:#64748B; font-size:12px; grid-column: 1 / -1;">
                    No leave balance initialized yet. Balances will update automatically upon HR/Principal setup.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN ROLE TABS & TABLES -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div style="border-bottom:2px solid #E2E8F0; margin-bottom:16px; display:flex; gap:16px; flex-wrap:wrap;">
            <button type="button" id="tabBtnMyLeaves" onclick="switchLeaveTab('my_leaves')" style="padding:10px 16px; border:none; background:none; font-weight:800; font-size:13px; color:#2563EB; border-bottom:3px solid #2563EB; cursor:pointer;">
                👤 My Leave History & Applications (<?= count($myRequests) ?>)
            </button>
            <?php if ($isHr): ?>
                <button type="button" id="tabBtnHrReview" onclick="switchLeaveTab('hr_review')" style="padding:10px 16px; border:none; background:none; font-weight:800; font-size:13px; color:#64748B; cursor:pointer;">
                    📋 HR Review Queue (<?= count($pendingHrRequests) ?>)
                </button>
            <?php endif; ?>
            <?php if ($isPrincipal): ?>
                <button type="button" id="tabBtnPrincipalApprove" onclick="switchLeaveTab('principal_approve')" style="padding:10px 16px; border:none; background:none; font-weight:800; font-size:13px; color:#64748B; cursor:pointer;">
                    🛡️ Pending Principal Approvals (<?= count($pendingPrincipalRequests) ?>)
                </button>
            <?php endif; ?>
            <?php if ($isHr || $isPrincipal): ?>
                <button type="button" id="tabBtnAllStaff" onclick="switchLeaveTab('all_staff')" style="padding:10px 16px; border:none; background:none; font-weight:800; font-size:13px; color:#64748B; cursor:pointer;">
                    📜 All Institutional Leave Register (<?= count($allRequests) ?>)
                </button>
            <?php endif; ?>
        </div>

        <!-- TAB 1: MY LEAVE REQUESTS TABLE -->
        <div id="tabContentMyLeaves">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; color:#475569; border-bottom:2px solid #E2E8F0;">
                            <th style="padding:10px 12px;">#ID</th>
                            <th style="padding:10px 12px;">Leave Type</th>
                            <th style="padding:10px 12px;">Dates</th>
                            <th style="padding:10px 12px;">Days</th>
                            <th style="padding:10px 12px;">Reason</th>
                            <th style="padding:10px 12px;">Status</th>
                            <th style="padding:10px 12px;">Remarks</th>
                            <th style="padding:10px 12px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($myRequests)): ?>
                            <?php foreach ($myRequests as $req): ?>
                                <tr style="border-bottom:1px solid #F1F5F9;">
                                    <td style="padding:10px 12px; font-weight:700;">#<?= $req['id'] ?></td>
                                    <td style="padding:10px 12px; font-weight:600; color:#0F172A;"><?= htmlspecialchars($req['leave_type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="padding:10px 12px; font-size:12px;"><?= $req['from_date'] ?> to <?= $req['to_date'] ?></td>
                                    <td style="padding:10px 12px; font-weight:700; color:#2563EB;"><?= $req['total_days'] ?> Day(s)</td>
                                    <td style="padding:10px 12px; color:#475569; max-width:200px;"><?= htmlspecialchars($req['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="padding:10px 12px; font-weight:700;">
                                        <?php
                                        $st = $req['status'];
                                        $stBg = '#F1F5F9'; $stFg = '#475569';
                                        if ($st === 'approved') { $stBg = '#DCFCE7'; $stFg = '#166534'; }
                                        elseif ($st === 'rejected') { $stBg = '#FEE2E2'; $stFg = '#991B1B'; }
                                        elseif ($st === 'pending_principal_approval') { $stBg = '#FEF3C7'; $stFg = '#92400E'; }
                                        elseif ($st === 'submitted') { $stBg = '#DBEAFE'; $stFg = '#1E40AF'; }
                                        ?>
                                        <span style="font-size:11px; padding:3px 10px; border-radius:12px; background:<?= $stBg ?>; color:<?= $stFg ?>;">
                                            <?= str_replace('_', ' ', strtoupper($st)) ?>
                                        </span>
                                    </td>
                                    <td style="padding:10px 12px; font-size:11px; color:#64748B;">
                                        <?php if (!empty($req['hr_remarks'])): ?><div><strong>HR:</strong> <?= htmlspecialchars($req['hr_remarks'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                        <?php if (!empty($req['principal_remarks'])): ?><div><strong>Principal:</strong> <?= htmlspecialchars($req['principal_remarks'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </td>
                                    <td style="padding:10px 12px; text-align:center;">
                                        <?php if (in_array($req['status'], ['submitted', 'under_hr_review', 'pending_principal_approval'])): ?>
                                            <form action="index.php?page=leave_cancel" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; color:#DC2626;">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size:11px; color:#94A3B8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="padding:20px; text-align:center; color:#64748B;">No personal leave requests submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($isHr): ?>
            <!-- TAB 2: HR REVIEW QUEUE TABLE -->
            <div id="tabContentHrReview" style="display:none;">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#1E293B; color:#FFFFFF;">
                                <th style="padding:10px 12px;">#ID</th>
                                <th style="padding:10px 12px;">Employee</th>
                                <th style="padding:10px 12px;">Job Role</th>
                                <th style="padding:10px 12px;">Leave Type</th>
                                <th style="padding:10px 12px;">Dates & Days</th>
                                <th style="padding:10px 12px;">Reason</th>
                                <th style="padding:10px 12px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingHrRequests)): ?>
                                <?php foreach ($pendingHrRequests as $hReq): ?>
                                    <tr style="border-bottom:1px solid #E2E8F0;">
                                        <td style="padding:10px 12px; font-weight:700;">#<?= $hReq['id'] ?></td>
                                        <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                            <?= htmlspecialchars($hReq['employee_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <div style="font-size:11px; font-weight:400; color:#64748B;"><?= htmlspecialchars($hReq['employee_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td style="padding:10px 12px; font-size:12px; color:#334155;"><?= htmlspecialchars($hReq['job_role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; font-weight:600; color:#2563EB;"><?= htmlspecialchars($hReq['leave_type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; font-size:12px;">
                                            <?= $hReq['from_date'] ?> to <?= $hReq['to_date'] ?>
                                            <div style="font-weight:700; color:#059669;"><?= $hReq['total_days'] ?> Day(s)</div>
                                        </td>
                                        <td style="padding:10px 12px; color:#475569; max-width:180px;"><?= htmlspecialchars($hReq['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; text-align:center;">
                                            <button type="button" class="btn btn-primary" style="font-size:11px; padding:4px 12px;" onclick="openHrReviewModal(<?= htmlspecialchars(json_encode($hReq), ENT_QUOTES, 'UTF-8') ?>)">Review & Verify</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="padding:20px; text-align:center; color:#64748B;">No staff leave applications pending HR verification.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isPrincipal): ?>
            <!-- TAB 3: PRINCIPAL APPROVAL QUEUE TABLE -->
            <div id="tabContentPrincipalApprove" style="display:none;">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#0F172A; color:#FFFFFF;">
                                <th style="padding:10px 12px;">#ID</th>
                                <th style="padding:10px 12px;">Employee</th>
                                <th style="padding:10px 12px;">Job Role</th>
                                <th style="padding:10px 12px;">Leave Type & Days</th>
                                <th style="padding:10px 12px;">Reason</th>
                                <th style="padding:10px 12px;">HR Recommendation</th>
                                <th style="padding:10px 12px; text-align:center;">Final Approval Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingPrincipalRequests)): ?>
                                <?php foreach ($pendingPrincipalRequests as $pReq): ?>
                                    <tr style="border-bottom:1px solid #E2E8F0;">
                                        <td style="padding:10px 12px; font-weight:700;">#<?= $pReq['id'] ?></td>
                                        <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                            <?= htmlspecialchars($pReq['employee_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <div style="font-size:11px; font-weight:400; color:#64748B;"><?= htmlspecialchars($pReq['employee_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td style="padding:10px 12px; font-size:12px; color:#334155;"><?= htmlspecialchars($pReq['job_role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px;">
                                            <div style="font-weight:700; color:#2563EB;"><?= htmlspecialchars($pReq['leave_type_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div style="font-size:12px; color:#64748B;"><?= $pReq['from_date'] ?> to <?= $pReq['to_date'] ?> (<strong><?= $pReq['total_days'] ?> Days</strong>)</div>
                                        </td>
                                        <td style="padding:10px 12px; color:#475569; max-width:180px;"><?= htmlspecialchars($pReq['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px;">
                                            <?php if (!empty($pReq['hr_recommendation'])): ?>
                                                <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534;"><?= strtoupper($pReq['hr_recommendation']) ?></span>
                                                <div style="font-size:11px; color:#475569; margin-top:2px;"><?= htmlspecialchars($pReq['hr_remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php else: ?>
                                                <span style="font-size:11px; color:#64748B;">Bypassed HR (Direct HR Request)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:10px 12px; text-align:center;">
                                            <button type="button" class="btn btn-primary" style="font-size:11px; padding:4px 12px; background:#059669;" onclick="openPrincipalModal(<?= htmlspecialchars(json_encode($pReq), ENT_QUOTES, 'UTF-8') ?>)">Approve / Reject</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="padding:20px; text-align:center; color:#64748B;">No staff leave applications pending Principal approval.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isHr || $isPrincipal): ?>
            <!-- TAB 4: ALL INSTITUTIONAL STAFF REGISTER -->
            <div id="tabContentAllStaff" style="display:none;">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#1E293B; color:#FFFFFF;">
                                <th style="padding:10px 12px;">#ID</th>
                                <th style="padding:10px 12px;">Employee</th>
                                <th style="padding:10px 12px;">Job Role</th>
                                <th style="padding:10px 12px;">Leave Type</th>
                                <th style="padding:10px 12px;">Dates</th>
                                <th style="padding:10px 12px;">Total Days</th>
                                <th style="padding:10px 12px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($allRequests)): ?>
                                <?php foreach ($allRequests as $aReq): ?>
                                    <tr style="border-bottom:1px solid #E2E8F0;">
                                        <td style="padding:10px 12px; font-weight:700;">#<?= $aReq['id'] ?></td>
                                        <td style="padding:10px 12px; font-weight:700; color:#0F172A;"><?= htmlspecialchars($aReq['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; font-size:12px;"><?= htmlspecialchars($aReq['job_role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; font-weight:600; color:#2563EB;"><?= htmlspecialchars($aReq['leave_type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td style="padding:10px 12px; font-size:12px;"><?= $aReq['from_date'] ?> to <?= $aReq['to_date'] ?></td>
                                        <td style="padding:10px 12px; font-weight:700; color:#059669;"><?= $aReq['total_days'] ?> Days</td>
                                        <td style="padding:10px 12px;">
                                            <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#E2E8F0; color:#334155;"><?= str_replace('_', ' ', strtoupper($aReq['status'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="padding:20px; text-align:center; color:#64748B;">No institutional leave register records available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL 1: APPLY NEW LEAVE FORM -->
<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal-card" style="max-width:560px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">📝 Submit Staff Leave Application</h3>
            <button type="button" onclick="document.getElementById('applyLeaveModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=leave_apply" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div style="background:#F8FAFC; padding:10px 14px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div><strong>Applicant:</strong> <?= htmlspecialchars($user['username'] ?? 'Staff Member', ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>Job Role:</strong> <?= htmlspecialchars($user['role_display'] ?? $user['role_name'] ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Leave Type *</label>
                    <select name="leave_type_id" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-weight:600; color:#0F172A;">
                        <?php if (!empty($leaveTypes)): ?>
                            <?php foreach ($leaveTypes as $lt): ?>
                                <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($lt['code'], ENT_QUOTES, 'UTF-8') ?>) — <?= $lt['days_allowed'] ?> Days Allowed</option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="1">Casual Leave (CL) — 12 Days Allowed</option>
                            <option value="2">Sick Leave (SL) — 10 Days Allowed</option>
                            <option value="3">Earned Leave (EL) — 15 Days Allowed</option>
                            <option value="4">Emergency Leave (EML) — 5 Days Allowed</option>
                            <option value="5">Medical Leave (ML) — 14 Days Allowed</option>
                            <option value="6">Maternity Leave (MAT) — 90 Days Allowed</option>
                            <option value="7">Paternity Leave (PAT) — 15 Days Allowed</option>
                            <option value="8">Unpaid Leave (LWP) — 30 Days Allowed</option>
                        <?php endif; ?>
                    </select>

                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">From Date *</label>
                        <input type="date" name="from_date" required class="form-control" value="<?= date('Y-m-d') ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">To Date *</label>
                        <input type="date" name="to_date" required class="form-control" value="<?= date('Y-m-d') ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Reason for Leave *</label>
                    <textarea name="reason" rows="3" required class="form-control" placeholder="Provide detailed reason for leave application..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Emergency Contact Phone</label>
                        <input type="text" name="contact_number" placeholder="e.g. +1 555-0199" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Supporting Document (Optional)</label>
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="form-control" style="width:100%; padding:6px; border-radius:6px; border:1px solid #CBD5E1; font-size:11px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('applyLeaveModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Leave Application</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: HR REVIEW & RECOMMENDATION FORM -->
<div class="modal-overlay" id="hrReviewModal">
    <div class="modal-card" style="max-width:540px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">📋 HR Review & Recommendation</h3>
            <button type="button" onclick="document.getElementById('hrReviewModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=leave_hr_review" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="request_id" id="hr_request_id">
            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div style="background:#F8FAFC; padding:10px 14px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;" id="hr_request_summary">
                    <!-- Populated dynamically via JS -->
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">HR Recommendation *</label>
                    <select name="hr_recommendation" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                        <option value="recommended">✓ Recommend Approval to Principal</option>
                        <option value="returned">↩ Return Request for Correction</option>
                        <option value="not_recommended">✕ Not Recommended (With Remarks)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">HR Verification Remarks *</label>
                    <textarea name="hr_remarks" rows="3" required class="form-control" placeholder="Enter HR verification remarks and recommendation justification..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('hrReviewModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Forward to Principal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: PRINCIPAL FINAL APPROVAL FORM -->
<div class="modal-overlay" id="principalApprovalModal">
    <div class="modal-card" style="max-width:540px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">🛡️ Principal Final Leave Approval Authority</h3>
            <button type="button" onclick="document.getElementById('principalApprovalModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=leave_principal_approve" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="request_id" id="principal_request_id">
            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div style="background:#F8FAFC; padding:10px 14px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;" id="principal_request_summary">
                    <!-- Populated dynamically via JS -->
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Decision Action *</label>
                    <select name="action" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                        <option value="approve">✅ Approve Staff Leave Request</option>
                        <option value="reject">❌ Reject Staff Leave Request</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Principal Decision Remarks</label>
                    <textarea name="principal_remarks" rows="3" class="form-control" placeholder="Enter Principal decision remarks or approval instructions..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('principalApprovalModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#059669;">Submit Final Decision</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: MANAGE LEAVE TYPES -->
<div class="modal-overlay" id="manageLeaveTypesModal">
    <div class="modal-card" style="max-width:500px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">⚙️ Configure Leave Types Policy</h3>
            <button type="button" onclick="document.getElementById('manageLeaveTypesModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=leave_type_save" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Leave Type Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Special Medical Leave" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Code *</label>
                        <input type="text" name="code" required placeholder="e.g. SML" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Days Allowed / Year</label>
                        <input type="number" name="days_allowed" value="12" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Description</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Policy guidelines for this leave type..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('manageLeaveTypesModal').classList.remove('active')">Close</button>
                <button type="submit" class="btn btn-primary">Save Leave Type</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchLeaveTab(tabKey) {
    const tabs = ['my_leaves', 'hr_review', 'principal_approve', 'all_staff'];
    tabs.forEach(t => {
        const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1).replace('_', ''));
        const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1).replace('_', ''));
        if (btn && content) {
            if (t === tabKey) {
                btn.style.color = '#2563EB';
                btn.style.borderBottom = '3px solid #2563EB';
                content.style.display = 'block';
            } else {
                btn.style.color = '#64748B';
                btn.style.borderBottom = 'none';
                content.style.display = 'none';
            }
        }
    });
}

function openHrReviewModal(req) {
    document.getElementById('hr_request_id').value = req.id;
    document.getElementById('hr_request_summary').innerHTML = `
        <div><strong>Applicant:</strong> ${req.employee_name} (${req.job_role})</div>
        <div><strong>Leave Type:</strong> ${req.leave_type_name} (${req.total_days} Days: ${req.from_date} to ${req.to_date})</div>
        <div><strong>Reason:</strong> ${req.reason}</div>
    `;
    document.getElementById('hrReviewModal').classList.add('active');
}

function openPrincipalModal(req) {
    document.getElementById('principal_request_id').value = req.id;
    document.getElementById('principal_request_summary').innerHTML = `
        <div><strong>Applicant:</strong> ${req.employee_name} (${req.job_role})</div>
        <div><strong>Leave Details:</strong> ${req.leave_type_name} (${req.total_days} Days: ${req.from_date} to ${req.to_date})</div>
        <div><strong>Reason:</strong> ${req.reason}</div>
        <div style="margin-top:6px; padding-top:6px; border-top:1px dashed #CBD5E1; color:#0284C7;">
            <strong>HR Recommendation:</strong> ${req.hr_recommendation ? req.hr_recommendation.toUpperCase() : 'N/A'}
            <br><strong>HR Remarks:</strong> ${req.hr_remarks || 'None'}
        </div>
    `;
    document.getElementById('principalApprovalModal').classList.add('active');
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
