<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner -->
    <div style="background:linear-gradient(135deg, #F59E0B, #D97706); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; box-shadow:var(--shadow-md);">
        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:28px;">💼</span>
                <h2 style="font-size:22px; font-weight:800; margin:0;">Accountant Financial Management Portal</h2>
            </div>
            <p style="font-size:13px; opacity:0.95; margin:6px 0 0 0;">Class fee structures, student installment tracking, dues reconciliation & cash collections</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="index.php?page=pending_installments" class="btn btn-secondary" style="background:#0F172A; color:#FFFFFF; font-weight:700; text-decoration:none; padding:9px 16px; border:none; display:inline-flex; align-items:center; gap:6px;">
                ⏳ Pending Installment Dues
            </a>
            <a href="index.php?page=module&name=fees" class="btn btn-secondary" style="background:#FFFFFF; color:#D97706; font-weight:700; text-decoration:none; padding:9px 16px;">
                + Record Cash Collection
            </a>
        </div>
    </div>

    <!-- Financial Metrics Row -->
    <section class="metrics-row" style="margin-bottom:24px; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
        <!-- Card 1: Total Collections -->
        <a href="index.php?page=module&name=fees" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Total Fee Collections</span>
                <span style="font-size:20px;">💳</span>
            </div>
            <div class="metric-value" style="color:var(--success);">
                ₹<?= number_format($metrics['total_fee_collections'] ?? 0, 2) ?>
            </div>
            <div class="metric-sub">Reconciled Cash & Gateway Payments</div>
        </a>

        <!-- Card 2: REQUIREMENT 1 & 2 - PENDING INSTALLMENT DUES CARD -->
        <div class="metric-card interactive" id="cardPendingInstallmentDues" style="cursor:pointer; border-left:4px solid var(--danger); position:relative;" onclick="openPendingDuesModal()">
            <div class="metric-header">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:18px;">⏳</span>
                    <span class="metric-title" style="font-weight:700; color:var(--text-main);">Pending Installment Dues</span>
                </div>
                <?php if (!empty($metrics['overdue_students_count']) && $metrics['overdue_students_count'] > 0): ?>
                    <span style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; padding:2px 8px; border-radius:var(--radius-pill); font-size:11px; font-weight:700;">
                        ⚠️ <?= $metrics['overdue_students_count'] ?> Overdue
                    </span>
                <?php endif; ?>
            </div>

            <!-- Dynamic Student Count -->
            <div class="metric-value" style="color:var(--danger); display:flex; align-items:baseline; gap:8px;">
                <span id="dashPendingStudentCount" style="font-size:28px; font-weight:800;">
                    <?= (int)($metrics['pending_students_count'] ?? 0) ?>
                </span>
                <span style="font-size:14px; font-weight:600; color:var(--text-muted);">
                    <?= ((int)($metrics['pending_students_count'] ?? 0) === 1) ? 'Student Pending' : 'Students Pending' ?>
                </span>
            </div>

            <!-- Total Outstanding Amount & Subtitle -->
            <div class="metric-sub" style="font-weight:600; color:#B91C1C; margin-top:4px;">
                ₹<?= number_format($metrics['total_outstanding_amount'] ?? $metrics['pending_fees'] ?? 0, 2) ?> Outstanding Across Classes
            </div>

            <!-- Clear Action Button & State Indicator -->
            <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #FEE2E2; padding-top:10px;">
                <?php if ((int)($metrics['pending_students_count'] ?? 0) === 0): ?>
                    <span style="font-size:12px; font-weight:700; color:var(--success);">
                        ✅ All Installments Settled
                    </span>
                <?php else: ?>
                    <span style="font-size:11px; font-weight:600; color:var(--text-muted);">
                        <?= (int)($metrics['pending_installments_count'] ?? 0) ?> Installment<?= ((int)($metrics['pending_installments_count'] ?? 0) === 1) ? '' : 's' ?> Unpaid
                    </span>
                <?php endif; ?>
                
                <button type="button" class="btn btn-sm btn-primary" style="background:#EF4444; border:none; font-size:11px; font-weight:800; padding:5px 12px; border-radius:6px; color:#FFF; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" onclick="event.stopPropagation(); openPendingDuesModal();">
                    View Details ➔
                </button>
            </div>
        </div>

        <!-- Card 3: Fee Structures -->
        <a href="index.php?page=module&name=fees" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Fee Structures</span>
                <span style="font-size:20px;">📋</span>
            </div>
            <div class="metric-value"><?= count($structures ?? []) ?> Published</div>
            <div class="metric-sub">Active Department Fee Heads</div>
        </a>

        <!-- Card 4: General Ledger -->
        <a href="index.php?page=module&name=accounts" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">General Ledger</span>
                <span style="font-size:20px;">🏛️</span>
            </div>
            <div class="metric-value" style="color:var(--primary);">Audited</div>
            <div class="metric-sub">Single-Transaction Accuracy</div>
        </a>
    </section>

    <!-- Published Fee Structures -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Class Fee Structures & Installment Rules</h3>
            <div style="display:flex; gap:8px;">
                <a href="index.php?page=pending_installments" class="btn btn-secondary" style="font-size:12px; font-weight:700; text-decoration:none;">⏳ Pending Dues Roster</a>
                <a href="index.php?page=module&name=fees" class="btn btn-primary" style="text-decoration:none; font-size:12px; font-weight:700;">+ Define Fee Structure</a>
            </div>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">COURSE</th>
                        <th style="padding:12px;">ACADEMIC YEAR</th>
                        <th style="padding:12px;">FEE HEAD / TYPE</th>
                        <th style="padding:12px;">AMOUNT (₹)</th>
                        <th style="padding:12px;">DUE DATE</th>
                        <th style="padding:12px; text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($structures)): ?>
                        <tr><td colspan="6" style="padding:20px; text-align:center; color:var(--text-muted);">No fee structures defined yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($structures as $fs): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($fs['course_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($fs['academic_year'] ?? '2026-2027', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($fs['fee_type'] ?? 'Tuition Fee', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px; font-weight:700;">₹<?= number_format($fs['amount'], 2) ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($fs['due_date'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px; text-align:center;">
                                <a href="index.php?page=pending_installments&course_id=<?= $fs['course_id'] ?>" class="btn btn-sm btn-secondary" style="font-size:11px; padding:3px 8px; text-decoration:none; font-weight:700;">
                                    View Dues
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fee Collections Transaction Audit -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Recent Fee Collection Transactions</h3>
            <div style="display:flex; gap:8px;">
                <a href="index.php?page=pending_installments" class="btn btn-secondary" style="font-size:12px; font-weight:700; text-decoration:none;">Collect Pending Installments</a>
                <a href="index.php?page=module&name=accounts" class="btn btn-secondary" style="text-decoration:none; font-size:12px; font-weight:700;">View Accounts Ledger</a>
            </div>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">TRANSACTION ID</th>
                        <th style="padding:12px;">STUDENT</th>
                        <th style="padding:12px;">AMOUNT PAID</th>
                        <th style="padding:12px;">MODE</th>
                        <th style="padding:12px;">PAID AT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($collections)): ?>
                        <tr><td colspan="5" style="padding:20px; text-align:center; color:var(--text-muted);">No collection transactions recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($collections as $col): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px;"><code><?= htmlspecialchars($col['transaction_id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td style="padding:12px; font-weight:700;"><?= htmlspecialchars(($col['first_name'] ?? '') . ' ' . ($col['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px; font-weight:700; color:var(--success);">₹<?= number_format($col['amount_paid'], 2) ?></td>
                            <td style="padding:12px;"><span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= htmlspecialchars($col['payment_mode'] ?? 'cash', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td style="padding:12px; font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($col['paid_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ========================================================= -->
<!-- MODAL DRAWER: PENDING INSTALLMENT DUES (EMBEDDED)          -->
<!-- ========================================================= -->
<div class="modal-overlay" id="modalPendingInstallmentDues" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.72); backdrop-filter:blur(4px); z-index:2100; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:1080px; width:100%; max-height:92vh; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px -15px rgba(15,23,42,0.4); display:flex; flex-direction:column;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:24px;">⏳</span>
                    <div>
                        <h3 style="font-size:18px; font-weight:800; color:#FFFFFF; margin:0;">
                            Pending Installment Dues
                        </h3>
                        <div style="font-size:12px; color:#94A3B8; margin-top:2px;">
                            Active Unsettled Student Installments &bull; Authoritative Financial Audit
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="index.php?page=pending_installments" class="btn btn-sm" style="background:#4F46E5; color:#FFF; font-weight:700; font-size:11px; padding:7px 14px; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                    ↗ Open Dedicated Page
                </a>
                <button type="button" onclick="closePendingDuesModal()" style="background:none; border:none; color:#CBD5E1; font-size:28px; cursor:pointer; line-height:1;" onmouseover="this.style.color='#FFF'" onmouseout="this.style.color='#CBD5E1'">&times;</button>
            </div>
        </div>

        <!-- Filter Controls Bar -->
        <div style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 20px; display:flex; flex-direction:column; gap:10px;">
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:10px; align-items:center;">
                <!-- Search -->
                <div style="position:relative;">
                    <input type="text" id="dashModalSearch" placeholder="Search student name, admission ID, or roll..." 
                           style="width:100%; padding:8px 10px 8px 30px; border:1px solid var(--border-color); border-radius:6px; font-size:12px; outline:none;"
                           oninput="debounceDashModalSearch()">
                    <span style="position:absolute; left:8px; top:8px; color:var(--text-light); font-size:13px;">🔍</span>
                </div>

                <!-- Department Filter -->
                <select id="dashModalDept" style="padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; font-size:12px; background:#FFF;" onchange="loadDashModalInstallments(1)">
                    <option value="0">All Departments</option>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- Course Filter -->
                <select id="dashModalCourse" style="padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; font-size:12px; background:#FFF;" onchange="loadDashModalInstallments(1)">
                    <option value="0">All Courses</option>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- Status Filter -->
                <select id="dashModalStatus" style="padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; font-size:12px; background:#FFF;" onchange="loadDashModalInstallments(1)">
                    <option value="">All Statuses</option>
                    <option value="overdue">⚠️ Overdue Only</option>
                    <option value="partial">⏳ Partially Paid</option>
                    <option value="pending">📅 Pending</option>
                </select>

                <!-- Reset -->
                <button type="button" onclick="resetDashModalFilters()" class="btn btn-secondary" style="font-size:12px; padding:7px 12px; font-weight:700;">
                    Reset
                </button>
            </div>
        </div>

        <!-- Table Viewport -->
        <div style="overflow-y:auto; flex:1; min-height:300px; position:relative;">
            <div id="dashModalLoading" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px; color:var(--text-muted); gap:10px;">
                <div style="width:32px; height:32px; border:3px solid #E2E8F0; border-top-color:var(--primary); border-radius:50%; animation:spin 0.8s linear infinite;"></div>
                <div style="font-size:13px; font-weight:600;">Loading pending installment records...</div>
            </div>

            <table id="dashModalTable" style="width:100%; border-collapse:collapse; font-size:12px; display:none;">
                <thead>
                    <tr style="background:#F1F5F9; text-align:left; border-bottom:2px solid var(--border-color); color:var(--text-muted); font-size:11px; text-transform:uppercase;">
                        <th style="padding:10px 12px; width:40px;">SR.</th>
                        <th style="padding:10px 12px;">STUDENT NAME</th>
                        <th style="padding:10px 12px;">ADMISSION ID</th>
                        <th style="padding:10px 12px;">DEGREE COURSE</th>
                        <th style="padding:10px 12px;">YEAR</th>
                        <th style="padding:10px 12px; text-align:right;">TOTAL FEES</th>
                        <th style="padding:10px 12px; text-align:right;">PAID</th>
                        <th style="padding:10px 12px; text-align:right;">OUTSTANDING</th>
                        <th style="padding:10px 12px;">INSTALLMENT</th>
                        <th style="padding:10px 12px;">DUE DATE</th>
                        <th style="padding:10px 12px; text-align:center;">STATUS</th>
                        <th style="padding:10px 12px; text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="dashModalTableBody">
                    <!-- Dynamic -->
                </tbody>
            </table>

            <!-- Empty State -->
            <div id="dashModalEmpty" style="display:none; text-align:center; padding:48px 20px;">
                <div style="font-size:38px; margin-bottom:8px;">🎉</div>
                <h4 style="font-size:15px; font-weight:700; color:var(--text-main); margin-bottom:4px;">No Pending Installment Dues Found</h4>
                <p style="font-size:12px; color:var(--text-muted); margin:0 auto 12px; max-width:380px;">
                    All students matching current filter criteria have settled their installment payments.
                </p>
                <button type="button" onclick="resetDashModalFilters()" class="btn btn-secondary" style="font-size:11px; font-weight:700;">
                    Reset Filters
                </button>
            </div>

            <!-- Error State -->
            <div id="dashModalError" style="display:none; text-align:center; padding:40px 20px;">
                <div style="font-size:36px; margin-bottom:8px;">⚠️</div>
                <h4 style="font-size:15px; font-weight:700; color:var(--danger); margin-bottom:4px;">Error Loading Dues</h4>
                <p style="font-size:12px; color:var(--text-muted); margin:0 auto 12px;" id="dashModalErrorMsg">Failed to load records.</p>
                <button type="button" onclick="loadDashModalInstallments(dashModalCurrentPage)" class="btn btn-primary" style="font-size:11px; font-weight:700;">
                    Retry
                </button>
            </div>
        </div>

        <!-- Footer / Pagination -->
        <div style="padding:10px 20px; background:#F8FAFC; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="font-size:11px; color:var(--text-muted);" id="dashModalPaginationInfo">
                Showing 0 records
            </div>
            <div style="display:flex; gap:6px; align-items:center;" id="dashModalPaginationBtns">
            </div>
        </div>
    </div>
</div>

<!-- Quick Record Payment Modal for Dashboard -->
<div class="modal-overlay" id="dashQuickPaymentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); z-index:2200; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:520px; width:100%; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="background:linear-gradient(135deg, #059669, #047857); color:#FFFFFF; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:22px;">💵</span>
                <div>
                    <h3 style="font-size:15px; font-weight:800; color:#FFFFFF; margin:0;">Record Fee Payment</h3>
                    <div style="font-size:11px; opacity:0.9;">Authoritative Ledger Settlement</div>
                </div>
            </div>
            <button type="button" onclick="closeDashQuickPaymentModal()" style="background:none; border:none; color:#D1FAE5; font-size:26px; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <form id="dashQuickPaymentForm" onsubmit="submitDashQuickPayment(event)" style="padding:18px 20px;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" id="dashPayStudentId" name="student_id" value="">
            <input type="hidden" id="dashPayInstallmentId" name="installment_id" value="">
            <input type="hidden" id="dashPayMaxOutstanding" value="0">

            <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 12px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <strong style="color:#065F46; font-size:13px;" id="dashPayStudentName">Student Name</strong>
                        <div style="font-size:11px; color:#047857;" id="dashPayAdmissionNo">ADM-XXXX</div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px;" id="dashPayInstallmentTitle">Installment Title</div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:10px; text-transform:uppercase; color:#065F46; font-weight:700;">Outstanding</span>
                        <div style="font-size:15px; font-weight:800; color:var(--danger);" id="dashPayOutstanding">₹0.00</div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:4px;">Amount Paid (₹) *</label>
                <input type="number" step="0.01" min="1" id="dashPayAmount" name="amount_paid" required
                       style="width:100%; padding:9px 12px; border:2px solid #059669; border-radius:8px; font-size:15px; font-weight:800; outline:none;" placeholder="5000.00">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Payment Mode</label>
                    <select id="dashPayMode" name="payment_mode" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:8px; font-size:12px; background:#FFF;">
                        <option value="cash" selected>💵 Cash Collection</option>
                        <option value="card">💳 Card / POS</option>
                        <option value="online">🌐 Online / UPI</option>
                        <option value="cheque">📑 Cheque / DD</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Reference No</label>
                    <input type="text" id="dashPayTxnId" name="transaction_id" placeholder="Auto-generated if empty"
                           style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:8px; font-size:12px;">
                </div>
            </div>

            <div id="dashPaymentAlert" style="display:none; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px;"></div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeDashQuickPaymentModal()" class="btn btn-secondary" style="font-weight:700; font-size:12px;">Cancel</button>
                <button type="submit" id="btnDashSubmitPayment" class="btn btn-primary" style="background:#059669; border:none; font-weight:800; font-size:12px;">
                    ✓ Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.badge-status-overdue {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-status-partial {
    background: #EFF6FF;
    color: #2563EB;
    border: 1px solid #BFDBFE;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-status-pending {
    background: #FFFBEB;
    color: #D97706;
    border: 1px solid #FDE68A;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-status-paid {
    background: #ECFDF5;
    color: #059669;
    border: 1px solid #A7F3D0;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 10px;
}
</style>

<script>
let dashModalCurrentPage = 1;
let dashModalSearchTimeout = null;

function openPendingDuesModal() {
    document.getElementById('modalPendingInstallmentDues').style.display = 'flex';
    loadDashModalInstallments(1);
}

function closePendingDuesModal() {
    document.getElementById('modalPendingInstallmentDues').style.display = 'none';
}

function debounceDashModalSearch() {
    clearTimeout(dashModalSearchTimeout);
    dashModalSearchTimeout = setTimeout(() => loadDashModalInstallments(1), 300);
}

function resetDashModalFilters() {
    document.getElementById('dashModalSearch').value = '';
    document.getElementById('dashModalDept').value = '0';
    document.getElementById('dashModalCourse').value = '0';
    document.getElementById('dashModalStatus').value = '';
    loadDashModalInstallments(1);
}

function loadDashModalInstallments(page) {
    dashModalCurrentPage = page;
    const search = document.getElementById('dashModalSearch').value.trim();
    const departmentId = document.getElementById('dashModalDept').value;
    const courseId = document.getElementById('dashModalCourse').value;
    const status = document.getElementById('dashModalStatus').value;

    document.getElementById('dashModalLoading').style.display = 'flex';
    document.getElementById('dashModalTable').style.display = 'none';
    document.getElementById('dashModalEmpty').style.display = 'none';
    document.getElementById('dashModalError').style.display = 'none';

    const params = new URLSearchParams({
        search: search,
        department_id: departmentId,
        course_id: courseId,
        status: status,
        page_num: page,
        per_page: 10
    });

    fetch('index.php?page=api_pending_installments&' + params.toString(), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('dashModalLoading').style.display = 'none';
        if (data.status !== 'success') throw new Error(data.message || 'Error loading dues');

        // Update card count if summary provided
        if (data.summary && document.getElementById('dashPendingStudentCount')) {
            document.getElementById('dashPendingStudentCount').textContent = data.summary.pending_students_count || 0;
        }

        renderDashModalTable(data.records || [], data.pagination);
    })
    .catch(err => {
        document.getElementById('dashModalLoading').style.display = 'none';
        document.getElementById('dashModalError').style.display = 'block';
        document.getElementById('dashModalErrorMsg').textContent = err.message;
    });
}

function renderDashModalTable(records, p) {
    const tbody = document.getElementById('dashModalTableBody');
    tbody.innerHTML = '';

    if (!records || records.length === 0) {
        document.getElementById('dashModalEmpty').style.display = 'block';
        document.getElementById('dashModalTable').style.display = 'none';
        document.getElementById('dashModalPaginationInfo').textContent = 'Showing 0 records';
        document.getElementById('dashModalPaginationBtns').innerHTML = '';
        return;
    }

    document.getElementById('dashModalEmpty').style.display = 'none';
    document.getElementById('dashModalTable').style.display = 'table';

    let sr = (p.current_page - 1) * p.per_page + 1;
    records.forEach(r => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #E2E8F0';

        const sName = escapeHtml(r.first_name + ' ' + (r.last_name || ''));
        const adm = escapeHtml(r.admission_no || '-');
        const crs = escapeHtml(r.course_name || '-');
        const yr = escapeHtml(r.class_name || r.class_code || 'FY');
        const title = escapeHtml(r.installment_title || '-');

        const totFees = '₹' + Number(r.student_total_fees || r.installment_amount).toLocaleString('en-IN', {minimumFractionDigits:2});
        const paid = '₹' + Number(r.installment_amount_paid).toLocaleString('en-IN', {minimumFractionDigits:2});
        const out = '₹' + Number(r.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits:2});

        let badge = '<span class="badge-status-pending">📅 Pending</span>';
        if (r.computed_status === 'overdue' || r.is_overdue) {
            badge = '<span class="badge-status-overdue">⚠️ Overdue</span>';
        } else if (r.computed_status === 'partial') {
            badge = '<span class="badge-status-partial">⏳ Partial</span>';
        }

        tr.innerHTML = `
            <td style="padding:9px 12px; color:var(--text-muted); font-weight:700;">${sr++}</td>
            <td style="padding:9px 12px; font-weight:700; color:var(--text-main);">${sName}</td>
            <td style="padding:9px 12px;"><code>${adm}</code></td>
            <td style="padding:9px 12px;">${crs}</td>
            <td style="padding:9px 12px;"><span style="background:var(--primary-light); color:var(--primary); padding:1px 6px; border-radius:4px; font-weight:700; font-size:10px;">${yr}</span></td>
            <td style="padding:9px 12px; text-align:right; font-weight:600; color:var(--text-muted);">${totFees}</td>
            <td style="padding:9px 12px; text-align:right; font-weight:700; color:var(--success);">${paid}</td>
            <td style="padding:9px 12px; text-align:right; font-weight:800; color:var(--danger);">${out}</td>
            <td style="padding:9px 12px; font-weight:600;">${title}</td>
            <td style="padding:9px 12px; white-space:nowrap;">${r.installment_due_date}</td>
            <td style="padding:9px 12px; text-align:center;">${badge}</td>
            <td style="padding:9px 12px; text-align:center;">
                <button type="button" class="btn btn-sm btn-primary" style="padding:3px 8px; font-size:11px; background:#059669; border:none; font-weight:700;"
                        onclick="openDashQuickPaymentModal(${r.student_id}, '${escapeQuote(sName)}', '${escapeQuote(adm)}', ${r.installment_id}, '${escapeQuote(title)}', ${r.outstanding_balance})">
                    💳 Collect
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Pagination Info
    const start = (p.current_page - 1) * p.per_page + 1;
    const end = Math.min(p.current_page * p.per_page, p.total_records);
    document.getElementById('dashModalPaginationInfo').textContent = `Showing ${start} to ${end} of ${p.total_records} records (${p.unique_students} unique students)`;

    const btnContainer = document.getElementById('dashModalPaginationBtns');
    btnContainer.innerHTML = '';
    if (p.total_pages > 1) {
        const prev = document.createElement('button');
        prev.className = 'btn btn-secondary';
        prev.style.padding = '3px 8px';
        prev.style.fontSize = '11px';
        prev.disabled = (p.current_page <= 1);
        prev.innerHTML = '&larr;';
        prev.onclick = () => loadDashModalInstallments(p.current_page - 1);
        btnContainer.appendChild(prev);

        const next = document.createElement('button');
        next.className = 'btn btn-secondary';
        next.style.padding = '3px 8px';
        next.style.fontSize = '11px';
        next.disabled = (p.current_page >= p.total_pages);
        next.innerHTML = '&rarr;';
        next.onclick = () => loadDashModalInstallments(p.current_page + 1);
        btnContainer.appendChild(next);
    }
}

function openDashQuickPaymentModal(studentId, name, adm, instId, title, outstanding) {
    document.getElementById('dashPayStudentId').value = studentId;
    document.getElementById('dashPayInstallmentId').value = instId;
    document.getElementById('dashPayMaxOutstanding').value = outstanding;

    document.getElementById('dashPayStudentName').textContent = name;
    document.getElementById('dashPayAdmissionNo').textContent = adm;
    document.getElementById('dashPayInstallmentTitle').textContent = title;
    document.getElementById('dashPayOutstanding').textContent = '₹' + Number(outstanding).toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('dashPayAmount').value = Number(outstanding).toFixed(2);
    document.getElementById('dashPayAmount').max = outstanding;
    document.getElementById('dashPaymentAlert').style.display = 'none';

    document.getElementById('dashQuickPaymentModal').style.display = 'flex';
}

function closeDashQuickPaymentModal() {
    document.getElementById('dashQuickPaymentModal').style.display = 'none';
}

function submitDashQuickPayment(e) {
    e.preventDefault();
    const btn = document.getElementById('btnDashSubmitPayment');
    const alertBox = document.getElementById('dashPaymentAlert');
    alertBox.style.display = 'none';

    btn.disabled = true;
    btn.textContent = 'Processing...';

    const formData = new FormData(document.getElementById('dashQuickPaymentForm'));

    fetch('index.php?page=record_installment_payment', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.textContent = '✓ Record Payment';
        if (res.status === 'success') {
            closeDashQuickPaymentModal();
            loadDashModalInstallments(dashModalCurrentPage);
            // Refresh dashboard counts on parent page
            window.location.reload();
        } else {
            alertBox.style.display = 'block';
            alertBox.style.background = '#FEF2F2';
            alertBox.style.color = '#DC2626';
            alertBox.textContent = res.message || 'Payment recording failed.';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = '✓ Record Payment';
        alertBox.style.display = 'block';
        alertBox.style.background = '#FEF2F2';
        alertBox.style.color = '#DC2626';
        alertBox.textContent = 'Network error processing payment.';
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
    });
}

function escapeQuote(str) {
    if (!str) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
