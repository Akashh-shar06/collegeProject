<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:linear-gradient(135deg, #EC4899, #DB2777); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h2 style="font-size:22px; font-weight:800;">HR & Monthly Payroll Processing Portal</h2>
            <p style="font-size:13px; opacity:0.9;">Employee directory, leave approvals & payroll processing calculated from staff attendance logs</p>
        </div>
        <a href="index.php?page=module&name=payroll" class="btn btn-secondary" style="background:#FFFFFF; color:#DB2777; font-weight:700; text-decoration:none;">+ Process Monthly Payroll</a>
    </div>

    <?php
    $pendingLeavesCount = 0;
    if (!empty($leaves)) {
        foreach ($leaves as $l) {
            if (($l['status'] ?? '') === 'pending') {
                $pendingLeavesCount++;
            }
        }
    }
    $totalMonthlyPayroll = 0;
    if (!empty($staffList)) {
        foreach ($staffList as $st) {
            $totalMonthlyPayroll += (float)($st['salary'] ?? 0);
        }
    }
    ?>
    <!-- Staff & Payroll Metrics -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="index.php?page=module&name=staff_data" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Total Active Employees</span></div>
            <div class="metric-value"><?= count($staffList) ?> Staff</div>
            <div class="metric-sub">Teaching & Non-Teaching</div>
        </a>
        <a href="index.php?page=module&name=leave_management" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Pending Leave Requests</span></div>
            <div class="metric-value" style="color:<?= $pendingLeavesCount > 0 ? 'var(--warning)' : 'var(--success)' ?>;"><?= $pendingLeavesCount ?> Requests</div>
            <div class="metric-sub"><?= $pendingLeavesCount > 0 ? 'Requires Approval' : 'All Leaves Reviewed ✅' ?></div>
        </a>
        <a href="index.php?page=module&name=payroll" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Monthly Payroll Ledger</span></div>
            <div class="metric-value" style="color:var(--success);">₹<?= number_format($totalMonthlyPayroll > 0 ? $totalMonthlyPayroll : 157000, 2) ?></div>
            <div class="metric-sub">Net Monthly Salary Disbursement</div>
        </a>
    </section>

    <!-- Staff Leave Request Approvals -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Staff Leave Requests & Approval Matrix</h3>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">APPLICANT</th>
                        <th style="padding:12px;">LEAVE TYPE</th>
                        <th style="padding:12px;">START DATE</th>
                        <th style="padding:12px;">END DATE</th>
                        <th style="padding:12px;">REASON</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $l): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($l['username'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= $l['leave_type'] ?></span></td>
                        <td style="padding:12px;"><?= $l['start_date'] ?></td>
                        <td style="padding:12px;"><?= $l['end_date'] ?></td>
                        <td style="padding:12px; color:var(--text-muted);"><?= htmlspecialchars($l['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><span style="background:var(--warning-light); color:var(--warning); padding:2px 8px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= $l['status'] ?></span></td>
                        <td style="padding:12px; text-align:right;">
                            <?php if ($l['status'] !== 'approved'): ?>
                                <form action="index.php?page=approve_leave" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                    <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:11px;">Approve</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:11px; color:var(--success); font-weight:700;">Approved ✅</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Staff Directory & Monthly Salary -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Employee Directory & Base Salary Records</h3>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">EMP ID</th>
                        <th style="padding:12px;">NAME</th>
                        <th style="padding:12px;">DESIGNATION</th>
                        <th style="padding:12px;">DEPARTMENT</th>
                        <th style="padding:12px;">PHONE NUMBER</th>
                        <th style="padding:12px;">BASE SALARY (₹)</th>
                        <th style="padding:12px; text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffList as $st): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px;"><code><?= htmlspecialchars($st['emp_id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><?= htmlspecialchars($st['designation'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><?= htmlspecialchars($st['department_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px; font-weight:700; color:#0F172A;"><?= !empty($st['phone']) ? htmlspecialchars($st['phone'], ENT_QUOTES, 'UTF-8') : '<span style="color:#94A3B8;">—</span>' ?></td>
                        <td style="padding:12px; font-weight:700; color:var(--success);">₹<?= number_format((float)$st['salary'], 2) ?></td>
                        <td style="padding:12px; text-align:right; display:flex; gap:6px; justify-content:flex-end;">
                            <a href="index.php?page=module&name=staff_management" class="btn btn-primary" style="padding:4px 10px; font-size:11px; text-decoration:none;">Manage Staff</a>
                            <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModal('<?= htmlspecialchars($st['emp_id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($st['designation'], ENT_QUOTES, 'UTF-8') ?>', '<?= number_format((float)$st['salary'], 2) ?>', '<?= htmlspecialchars($st['department_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($st['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>')">📄 Generate Payslip PDF</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>

