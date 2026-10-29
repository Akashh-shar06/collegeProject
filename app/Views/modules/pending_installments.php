<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport" style="padding-bottom:60px;">
    <!-- Breadcrumb & Top Bar -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
            <a href="index.php?page=dashboard" style="color:var(--primary); text-decoration:none; font-weight:600;">Dashboard</a>
            <span>/</span>
            <a href="index.php?page=module&name=fees" style="color:var(--primary); text-decoration:none; font-weight:600;">Fees Management</a>
            <span>/</span>
            <span style="font-weight:700; color:var(--text-main);">Pending Installment Dues</span>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <a href="index.php?page=export_pending_installments_csv" id="btnExportCsv" class="btn btn-secondary" style="font-size:12px; font-weight:700; padding:8px 14px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                📥 Export CSV
            </a>
            <button type="button" onclick="openSyncInstallmentsModal()" class="btn btn-secondary" style="font-size:12px; font-weight:700; padding:8px 14px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                ⚡ Sync Course Installments
            </button>
            <a href="index.php?page=module&name=fees" class="btn btn-primary" style="font-size:12px; font-weight:700; padding:8px 14px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                + Record Cash Collection
            </a>
        </div>
    </div>

    <!-- Header Banner -->
    <div style="background:linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; box-shadow:var(--shadow-md); position:relative; overflow:hidden;">
        <div style="position:relative; z-index:1;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <span style="font-size:32px;">⏳</span>
                <div>
                    <h2 style="font-size:22px; font-weight:800; margin:0; letter-spacing:-0.5px;">Student Pending Installment Dues</h2>
                    <p style="font-size:13px; opacity:0.85; margin:4px 0 0 0;">Authoritative student installment audit, partial payment reconciliation & instant fee collection</p>
                </div>
            </div>
            <div style="display:flex; gap:12px; margin-top:12px; flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.12); padding:4px 10px; border-radius:var(--radius-pill); font-size:12px; font-weight:600;">
                    🏛️ Institution: <?= htmlspecialchars($user['tenant_name'] ?? 'College ERP', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span style="background:rgba(255,255,255,0.12); padding:4px 10px; border-radius:var(--radius-pill); font-size:12px; font-weight:600;">
                    👤 Role: Accountant (<?= htmlspecialchars($user['username'] ?? 'Accountant', ENT_QUOTES, 'UTF-8') ?>)
                </span>
                <span style="background:rgba(16,185,129,0.25); color:#6EE7B7; padding:4px 10px; border-radius:var(--radius-pill); font-size:12px; font-weight:700;">
                    🔒 Verified Financial Ledger
                </span>
            </div>
        </div>
        <div style="display:none; @media(min-width:768px){display:block;} text-align:right;">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#94A3B8; font-weight:700;">Total Pending Balance</div>
            <div style="font-size:28px; font-weight:800; color:#F87171;" id="bannerOutstandingAmount">
                ₹<?= number_format($summary['total_outstanding_amount'] ?? 0, 2) ?>
            </div>
        </div>
    </div>

    <!-- 1. KPI SUMMARY CARDS -->
    <section class="metrics-row" style="margin-bottom:24px; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
        <div class="metric-card" style="border-left:4px solid var(--warning);">
            <div class="metric-header">
                <span class="metric-title">Pending Students</span>
                <span style="font-size:20px;">👥</span>
            </div>
            <div class="metric-value" style="color:var(--warning);" id="kpiPendingStudents">
                <?= (int)($summary['pending_students_count'] ?? 0) ?>
            </div>
            <div class="metric-sub">
                Students with Active Unpaid Dues
            </div>
        </div>

        <div class="metric-card" style="border-left:4px solid var(--danger);">
            <div class="metric-header">
                <span class="metric-title">Total Outstanding Balance</span>
                <span style="font-size:20px;">⏳</span>
            </div>
            <div class="metric-value" style="color:var(--danger);" id="kpiTotalOutstanding">
                ₹<?= number_format($summary['total_outstanding_amount'] ?? 0, 2) ?>
            </div>
            <div class="metric-sub">
                Net Outstanding Installment Fees
            </div>
        </div>

        <div class="metric-card" style="border-left:4px solid #DC2626;">
            <div class="metric-header">
                <span class="metric-title">Overdue Installments</span>
                <span style="font-size:20px;">⚠️</span>
            </div>
            <div class="metric-value" style="color:#DC2626;" id="kpiOverdueStudents">
                <?= (int)($summary['overdue_students_count'] ?? 0) ?> Students
            </div>
            <div class="metric-sub">
                Amount: ₹<?= number_format($summary['total_overdue_amount'] ?? 0, 2) ?>
            </div>
        </div>

        <div class="metric-card" style="border-left:4px solid var(--success);">
            <div class="metric-header">
                <span class="metric-title">Total Fee Collections</span>
                <span style="font-size:20px;">💳</span>
            </div>
            <div class="metric-value" style="color:var(--success);" id="kpiTotalCollected">
                ₹<?= number_format($summary['total_collected_amount'] ?? 0, 2) ?>
            </div>
            <div class="metric-sub">
                Reconciled Ledger Collections
            </div>
        </div>
    </section>

    <!-- 2. FILTER & SEARCH TOOLBAR -->
    <div class="chart-card" style="margin-bottom:20px; padding:18px 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:18px;">🔍</span>
                <strong style="font-size:15px; color:var(--text-main);">Filter & Search Pending Dues</strong>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div class="tab-switcher" style="margin:0;">
                    <button type="button" class="tab-btn active" id="btnViewList" onclick="setViewMode('list')">📄 All Installments</button>
                    <button type="button" class="tab-btn" id="btnViewGroup" onclick="setViewMode('group')">👥 Group by Student</button>
                </div>
                <button type="button" onclick="resetFilters()" class="btn btn-secondary" style="font-size:12px; padding:6px 12px; font-weight:600;">
                    🔄 Reset Filters
                </button>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
            <!-- Live Search -->
            <div style="grid-column: span 2; min-width:260px;">
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Search Student / Admission / Roll
                </label>
                <div style="position:relative;">
                    <input type="text" id="filterSearch" placeholder="Type student name, admission ID (e.g. ADM-2026), or roll no..." 
                           style="width:100%; padding:9px 12px 9px 34px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; font-family:inherit; outline:none;"
                           oninput="debounceSearch()">
                    <span style="position:absolute; left:10px; top:9px; color:var(--text-light); font-size:14px;">🔍</span>
                </div>
            </div>

            <!-- Department Filter -->
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Department
                </label>
                <select id="filterDepartment" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFFFFF;" onchange="loadInstallments(1)">
                    <option value="0">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Degree Course Filter -->
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Degree Course
                </label>
                <select id="filterCourse" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFFFFF;" onchange="loadInstallments(1)">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Academic Year / Class Filter -->
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Academic Year / Class
                </label>
                <select id="filterClass" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFFFFF;" onchange="loadInstallments(1)">
                    <option value="0">All Classes (FY / SY / TY)</option>
                    <?php foreach ($classes as $cl): ?>
                        <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['name'], ENT_QUOTES, 'UTF-8') ?> (<?= $cl['code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Installment Status
                </label>
                <select id="filterStatus" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFFFFF;" onchange="loadInstallments(1)">
                    <option value="">All Statuses</option>
                    <option value="overdue">⚠️ Overdue Only (Due Date Elapsed)</option>
                    <option value="partial">⏳ Partially Paid</option>
                    <option value="pending">📅 Pending (Future Due)</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase;">
                    Sort Order
                </label>
                <select id="filterSort" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFFFFF;" onchange="loadInstallments(1)">
                    <option value="overdue_first">⚠️ Overdue First</option>
                    <option value="due_date_asc">📅 Due Date (Earliest First)</option>
                    <option value="due_date_desc">📅 Due Date (Latest First)</option>
                    <option value="amount_desc">💰 Outstanding Balance (High to Low)</option>
                    <option value="amount_asc">💰 Outstanding Balance (Low to High)</option>
                    <option value="student_name">🔤 Student Name (A to Z)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- 3. MAIN PENDING INSTALLMENT STUDENTS TABLE -->
    <div class="chart-card" style="padding:0; overflow:hidden;">
        <!-- Card Header -->
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#FAFCFF;">
            <div>
                <h3 style="font-size:16px; font-weight:800; color:var(--text-main); margin:0;">
                    Pending Installments Register
                </h3>
                <span style="font-size:12px; color:var(--text-muted);" id="tableCountBadge">
                    Fetching authoritative fee installment records...
                </span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:12px; color:var(--text-muted);">Rows per page:</span>
                <select id="filterPerPage" style="padding:5px 8px; border-radius:6px; border:1px solid var(--border-color); font-size:12px;" onchange="loadInstallments(1)">
                    <option value="10">10</option>
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <button type="button" onclick="loadInstallments(currentPage)" class="btn btn-secondary" style="padding:5px 10px; font-size:12px;" title="Refresh Data">
                    🔄
                </button>
            </div>
        </div>

        <!-- Table Viewport -->
        <div style="overflow-x:auto; width:100%; min-height:220px;" id="tableContainer">
            <div id="tableLoading" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 20px; color:var(--text-muted); gap:12px;">
                <div style="width:36px; height:36px; border:3px solid #E2E8F0; border-top-color:var(--primary); border-radius:50%; animation:spin 0.8s linear infinite;"></div>
                <div style="font-size:14px; font-weight:600;">Loading pending installment records...</div>
            </div>

            <table id="pendingInstallmentsTable" style="width:100%; border-collapse:collapse; font-size:13px; display:none;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color); color:var(--text-muted); font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">
                        <th style="padding:12px 14px; width:45px;">SR.</th>
                        <th style="padding:12px 14px;">STUDENT NAME</th>
                        <th style="padding:12px 14px;">ADMISSION ID / ROLL</th>
                        <th style="padding:12px 14px;">DEPARTMENT</th>
                        <th style="padding:12px 14px;">DEGREE COURSE</th>
                        <th style="padding:12px 14px;">ACADEMIC YEAR</th>
                        <th style="padding:12px 14px; text-align:right;">TOTAL FEES</th>
                        <th style="padding:12px 14px; text-align:right;">AMOUNT PAID</th>
                        <th style="padding:12px 14px; text-align:right;">OUTSTANDING</th>
                        <th style="padding:12px 14px;">INSTALLMENT</th>
                        <th style="padding:12px 14px;">DUE DATE</th>
                        <th style="padding:12px 14px; text-align:center;">STATUS</th>
                        <th style="padding:12px 14px; text-align:center;">AVAILABLE ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>

            <!-- Empty State -->
            <div id="tableEmpty" style="display:none; text-align:center; padding:56px 20px;">
                <div style="font-size:44px; margin-bottom:12px;">🎉</div>
                <h4 style="font-size:16px; font-weight:700; color:var(--text-main); margin-bottom:6px;">No Pending Installment Dues Found</h4>
                <p style="font-size:13px; color:var(--text-muted); max-width:440px; margin:0 auto 16px;">
                    All students matching current filter criteria are either fully paid or have no active dues recorded.
                </p>
                <button type="button" onclick="resetFilters()" class="btn btn-secondary" style="font-size:12px; font-weight:700;">
                    Reset All Filters
                </button>
            </div>

            <!-- Error State -->
            <div id="tableError" style="display:none; text-align:center; padding:48px 20px;">
                <div style="font-size:40px; margin-bottom:10px;">⚠️</div>
                <h4 style="font-size:16px; font-weight:700; color:var(--danger); margin-bottom:6px;">Failed to Load Installment Dues</h4>
                <p style="font-size:13px; color:var(--text-muted); max-width:440px; margin:0 auto 16px;" id="errorMessage">
                    A database or network error occurred while retrieving fee records.
                </p>
                <button type="button" onclick="loadInstallments(currentPage)" class="btn btn-primary" style="font-size:12px; font-weight:700;">
                    Retry Request
                </button>
            </div>
        </div>

        <!-- Pagination Bar -->
        <div style="padding:12px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#FAFCFF;" id="paginationContainer">
            <div style="font-size:12px; color:var(--text-muted);" id="paginationInfo">
                Showing 0 to 0 of 0 records
            </div>
            <div style="display:flex; gap:6px; align-items:center;" id="paginationButtons">
                <!-- Dynamically generated pagination buttons -->
            </div>
        </div>
    </div>
</main>

<!-- ========================================== -->
<!-- MODAL: STUDENT INSTALLMENT BREAKDOWN       -->
<!-- ========================================== -->
<div class="modal-overlay" id="modalStudentInstallments" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); z-index:2100; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:920px; width:100%; max-height:90vh; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); display:flex; flex-direction:column;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #1E293B, #0F172A); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:22px;">🎓</span>
                    <h3 style="font-size:17px; font-weight:800; color:#FFFFFF; margin:0;" id="breakdownStudentName">
                        Student Installment Schedule
                    </h3>
                </div>
                <div style="font-size:12px; color:#94A3B8; margin-top:4px;" id="breakdownStudentMeta">
                    Admission ID &bull; Department &bull; Course
                </div>
            </div>
            <button type="button" onclick="closeBreakdownModal()" style="background:none; border:none; color:#CBD5E1; font-size:28px; cursor:pointer; line-height:1;" onmouseover="this.style.color='#FFF'" onmouseout="this.style.color='#CBD5E1'">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding:20px 24px; overflow-y:auto; flex:1;">
            <!-- Student Financial Summary Cards -->
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:20px;">
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:12px;">
                    <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Total Course Fees</div>
                    <div style="font-size:18px; font-weight:800; color:var(--text-main); margin-top:4px;" id="modalTotalFees">₹0.00</div>
                </div>
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:12px;">
                    <div style="font-size:11px; text-transform:uppercase; color:#065F46; font-weight:700;">Total Amount Paid</div>
                    <div style="font-size:18px; font-weight:800; color:var(--success); margin-top:4px;" id="modalTotalPaid">₹0.00</div>
                </div>
                <div style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:12px;">
                    <div style="font-size:11px; text-transform:uppercase; color:#991B1B; font-weight:700;">Outstanding Balance</div>
                    <div style="font-size:18px; font-weight:800; color:var(--danger); margin-top:4px;" id="modalTotalOutstanding">₹0.00</div>
                </div>
            </div>

            <!-- Installments List Table -->
            <h4 style="font-size:14px; font-weight:800; color:var(--text-main); margin-bottom:10px;">
                Fee Installment Breakdown
            </h4>
            <div style="border:1px solid var(--border-color); border-radius:10px; overflow:hidden; margin-bottom:24px;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#F1F5F9; text-align:left; border-bottom:1px solid var(--border-color); color:var(--text-muted);">
                            <th style="padding:10px 12px;">INSTALLMENT TITLE</th>
                            <th style="padding:10px 12px; text-align:right;">AMOUNT (₹)</th>
                            <th style="padding:10px 12px; text-align:right;">PAID (₹)</th>
                            <th style="padding:10px 12px; text-align:right;">BALANCE (₹)</th>
                            <th style="padding:10px 12px;">DUE DATE</th>
                            <th style="padding:10px 12px; text-align:center;">STATUS</th>
                            <th style="padding:10px 12px; text-align:center;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="modalInstallmentsBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Payment Transaction History -->
            <h4 style="font-size:14px; font-weight:800; color:var(--text-main); margin-bottom:10px;">
                Recorded Payment Transactions & Receipts
            </h4>
            <div style="border:1px solid var(--border-color); border-radius:10px; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#F1F5F9; text-align:left; border-bottom:1px solid var(--border-color); color:var(--text-muted);">
                            <th style="padding:10px 12px;">TXN ID / REF</th>
                            <th style="padding:10px 12px;">ALLOCATED INSTALLMENT</th>
                            <th style="padding:10px 12px;">MODE</th>
                            <th style="padding:10px 12px; text-align:right;">AMOUNT PAID (₹)</th>
                            <th style="padding:10px 12px;">PAID AT</th>
                            <th style="padding:10px 12px; text-align:center;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="modalPaymentsBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:14px 24px; background:#F8FAFC; border-top:1px solid var(--border-color); text-align:right;">
            <button type="button" onclick="closeBreakdownModal()" class="btn btn-secondary" style="font-weight:700;">
                Close Breakdown
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: QUICK RECORD PAYMENT                -->
<!-- ========================================== -->
<div class="modal-overlay" id="modalRecordPayment" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); z-index:2200; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:540px; width:100%; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #059669, #047857); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:24px;">💵</span>
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#FFFFFF; margin:0;">Record Fee Payment</h3>
                    <div style="font-size:12px; opacity:0.9;">Authoritative Ledger & Cash Collection</div>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" style="background:none; border:none; color:#D1FAE5; font-size:26px; cursor:pointer; line-height:1;" onmouseover="this.style.color='#FFF'" onmouseout="this.style.color='#D1FAE5'">&times;</button>
        </div>

        <!-- Form Body -->
        <form id="recordPaymentForm" onsubmit="submitPayment(event)" style="padding:20px 24px;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" id="payStudentId" name="student_id" value="">
            <input type="hidden" id="payInstallmentId" name="installment_id" value="">
            <input type="hidden" id="payMaxOutstanding" value="0">

            <!-- Student & Installment Banner -->
            <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; padding:12px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <strong style="color:#065F46; font-size:14px;" id="payStudentNameText">Student Name</strong>
                        <div style="font-size:12px; color:#047857;" id="payAdmissionNoText">ADM-XXXX</div>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:2px;" id="payInstallmentTitleText">Term 1 Installment</div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:11px; text-transform:uppercase; color:#065F46; font-weight:700;">Outstanding</span>
                        <div style="font-size:16px; font-weight:800; color:var(--danger);" id="payOutstandingText">₹0.00</div>
                    </div>
                </div>
            </div>

            <!-- Amount to Pay -->
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label style="font-size:12px; font-weight:700; color:var(--text-main);">Amount Paid (₹) *</label>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="fillQuickAmount(1)" style="background:#E2E8F0; border:none; border-radius:4px; font-size:11px; padding:2px 8px; cursor:pointer; font-weight:600;">Full Amount</button>
                        <button type="button" onclick="fillQuickAmount(0.5)" style="background:#E2E8F0; border:none; border-radius:4px; font-size:11px; padding:2px 8px; cursor:pointer; font-weight:600;">50%</button>
                    </div>
                </div>
                <input type="number" step="0.01" min="1" id="payAmountInput" name="amount_paid" required
                       style="width:100%; padding:10px 12px; border:2px solid #059669; border-radius:8px; font-size:16px; font-weight:800; color:var(--text-main); font-family:inherit; outline:none;"
                       placeholder="e.g. 5000.00">
            </div>

            <!-- Payment Mode -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:4px;">Payment Mode *</label>
                    <select id="payModeSelect" name="payment_mode" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; font-family:inherit; background:#FFF;" onchange="onPaymentModeChange()">
                        <option value="cash" selected>💵 Cash Collection</option>
                        <option value="card">💳 Card / POS Terminal</option>
                        <option value="online">🌐 Online Transfer / UPI</option>
                        <option value="cheque">📑 Cheque / Demand Draft</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:4px;">Transaction / Receipt Ref</label>
                    <input type="text" id="payTransactionId" name="transaction_id" placeholder="Auto-generated if blank"
                           style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; font-family:inherit;">
                </div>
            </div>

            <!-- Remarks -->
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:4px;">Accountant Remarks (Optional)</label>
                <input type="text" id="payRemarks" name="remarks" placeholder="e.g. Fee counter receipt, counter 2"
                       style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; font-family:inherit;">
            </div>

            <div id="paymentAlert" style="display:none; padding:10px 14px; border-radius:8px; font-size:12px; margin-bottom:14px;"></div>

            <!-- Form Actions -->
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closePaymentModal()" class="btn btn-secondary" style="font-weight:700;">
                    Cancel
                </button>
                <button type="submit" id="btnSubmitPayment" class="btn btn-primary" style="background:#059669; border:none; font-weight:800; padding:10px 20px;">
                    ✓ Record & Issue Receipt
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: RECEIPT SUCCESS DIALOG              -->
<!-- ========================================== -->
<div class="modal-overlay" id="modalReceiptSuccess" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); z-index:2300; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:480px; width:100%; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); text-align:center; padding:28px 24px;">
        <div style="width:60px; height:60px; border-radius:50%; background:#DCFCE7; color:#16A34A; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 16px;">
            ✓
        </div>
        <h3 style="font-size:18px; font-weight:800; color:var(--text-main); margin-bottom:4px;">Payment Recorded Successfully!</h3>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">
            Official transaction has been recorded in fee collections and the financial general ledger.
        </p>

        <!-- Receipt Summary Box -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:16px; text-align:left; font-size:12px; margin-bottom:20px;" id="receiptDetailsBox">
            <!-- Dynamically populated -->
        </div>

        <div style="display:flex; justify-content:center; gap:10px;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-weight:700; font-size:12px;">
                🖨️ Print Receipt
            </button>
            <button type="button" onclick="closeReceiptModal()" class="btn btn-primary" style="font-weight:700; font-size:12px;">
                Done
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: SYNC COURSE INSTALLMENTS            -->
<!-- ========================================== -->
<div class="modal-overlay" id="modalSyncInstallments" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); z-index:2100; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="max-width:460px; width:100%; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); padding:24px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <span style="font-size:26px;">⚡</span>
            <div>
                <h3 style="font-size:16px; font-weight:800; margin:0;">Sync Course Installments</h3>
                <div style="font-size:12px; color:var(--text-muted);">Generate installments for enrolled students</div>
            </div>
        </div>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
            This tool generates official fee installment schedules for all active students enrolled in published course fee structures without creating duplicate records.
        </p>
        <div style="margin-bottom:18px;">
            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Target Course</label>
            <select id="syncCourseSelect" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#FFF;">
                <option value="0">All Courses with Fee Structures</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="syncResultAlert" style="display:none; padding:10px; border-radius:8px; font-size:12px; margin-bottom:14px;"></div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeSyncModal()" class="btn btn-secondary" style="font-weight:700;">Cancel</button>
            <button type="button" id="btnExecuteSync" onclick="executeInstallmentSync()" class="btn btn-primary" style="font-weight:700; background:#4F46E5;">
                Start Sync
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.badge-status-overdue {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
    padding: 3px 8px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-status-partial {
    background: #EFF6FF;
    color: #2563EB;
    border: 1px solid #BFDBFE;
    padding: 3px 8px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-status-pending {
    background: #FFFBEB;
    color: #D97706;
    border: 1px solid #FDE68A;
    padding: 3px 8px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-status-paid {
    background: #ECFDF5;
    color: #059669;
    border: 1px solid #A7F3D0;
    padding: 3px 8px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.table-hover-row:hover {
    background: #F8FAFC !important;
}
.accordion-toggle {
    cursor: pointer;
    transition: background 0.15s;
}
.accordion-toggle:hover {
    background: #F1F5F9 !important;
}
</style>

<script>
let currentPage = 1;
let currentViewMode = 'list'; // 'list' or 'group'
let searchTimeout = null;
let currentRecords = [];

document.addEventListener('DOMContentLoaded', function() {
    loadInstallments(1);
});

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        loadInstallments(1);
    }, 300);
}

function setViewMode(mode) {
    currentViewMode = mode;
    document.getElementById('btnViewList').classList.toggle('active', mode === 'list');
    document.getElementById('btnViewGroup').classList.toggle('active', mode === 'group');
    loadInstallments(1);
}

function resetFilters() {
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterDepartment').value = '0';
    document.getElementById('filterCourse').value = '0';
    document.getElementById('filterClass').value = '0';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSort').value = 'overdue_first';
    loadInstallments(1);
}

function loadInstallments(page) {
    currentPage = page;
    const search = document.getElementById('filterSearch').value.trim();
    const departmentId = document.getElementById('filterDepartment').value;
    const courseId = document.getElementById('filterCourse').value;
    const classId = document.getElementById('filterClass').value;
    const status = document.getElementById('filterStatus').value;
    const sortBy = document.getElementById('filterSort').value;
    const perPage = document.getElementById('filterPerPage').value;
    const groupByStudent = (currentViewMode === 'group');

    // UI Loading state
    document.getElementById('tableLoading').style.display = 'flex';
    document.getElementById('pendingInstallmentsTable').style.display = 'none';
    document.getElementById('tableEmpty').style.display = 'none';
    document.getElementById('tableError').style.display = 'none';

    // Update export link with active filters
    const exportParams = new URLSearchParams({
        search: search,
        department_id: departmentId,
        course_id: courseId,
        class_id: classId,
        status: status
    });
    document.getElementById('btnExportCsv').href = 'index.php?page=export_pending_installments_csv&' + exportParams.toString();

    const fetchParams = new URLSearchParams({
        search: search,
        department_id: departmentId,
        course_id: courseId,
        class_id: classId,
        status: status,
        sort_by: sortBy,
        page_num: page,
        per_page: perPage,
        group_by_student: groupByStudent ? 'true' : 'false'
    });

    fetch('index.php?page=api_pending_installments&' + fetchParams.toString(), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP error ' + r.status);
        return r.json();
    })
    .then(data => {
        document.getElementById('tableLoading').style.display = 'none';
        if (data.status !== 'success') {
            throw new Error(data.message || 'Error fetching records');
        }

        // Update KPI Summary Badges
        if (data.summary) {
            updateKpiWidgets(data.summary);
        }

        currentRecords = data.records || [];
        renderTable(currentRecords, data.pagination, groupByStudent);
    })
    .catch(err => {
        document.getElementById('tableLoading').style.display = 'none';
        document.getElementById('tableError').style.display = 'block';
        document.getElementById('errorMessage').textContent = err.message || 'Failed to load records.';
    });
}

function updateKpiWidgets(s) {
    if (document.getElementById('kpiPendingStudents')) {
        document.getElementById('kpiPendingStudents').textContent = s.pending_students_count || 0;
    }
    if (document.getElementById('kpiTotalOutstanding')) {
        document.getElementById('kpiTotalOutstanding').textContent = '₹' + Number(s.total_outstanding_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    if (document.getElementById('bannerOutstandingAmount')) {
        document.getElementById('bannerOutstandingAmount').textContent = '₹' + Number(s.total_outstanding_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    if (document.getElementById('kpiOverdueStudents')) {
        document.getElementById('kpiOverdueStudents').textContent = (s.overdue_students_count || 0) + ' Students';
    }
    if (document.getElementById('kpiTotalCollected')) {
        document.getElementById('kpiTotalCollected').textContent = '₹' + Number(s.total_collected_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function renderTable(records, pagination, isGrouped) {
    const tbody = document.getElementById('pendingTableBody');
    tbody.innerHTML = '';

    if (!records || records.length === 0) {
        document.getElementById('tableEmpty').style.display = 'block';
        document.getElementById('pendingInstallmentsTable').style.display = 'none';
        document.getElementById('tableCountBadge').textContent = '0 records found';
        renderPagination(pagination);
        return;
    }

    document.getElementById('tableEmpty').style.display = 'none';
    document.getElementById('pendingInstallmentsTable').style.display = 'table';
    document.getElementById('tableCountBadge').textContent = `Showing ${records.length} of ${pagination.total_records} records (${pagination.unique_students} unique students)`;

    if (isGrouped) {
        renderGroupedRows(tbody, records, pagination);
    } else {
        renderFlatRows(tbody, records, pagination);
    }

    renderPagination(pagination);
}

function renderFlatRows(tbody, records, pagination) {
    let sr = (pagination.current_page - 1) * pagination.per_page + 1;

    records.forEach(r => {
        const tr = document.createElement('tr');
        tr.className = 'table-hover-row';
        tr.style.borderBottom = '1px solid var(--border-color)';

        const studentName = escapeHtml(trimStr(r.first_name + ' ' + (r.middle_name || '') + ' ' + r.last_name));
        const admissionNo = escapeHtml(r.admission_no || '-');
        const rollNo = escapeHtml(r.roll_no || '-');
        const dept = escapeHtml(r.department_name || 'General');
        const course = escapeHtml(r.course_name || 'General Course');
        const className = escapeHtml(r.class_name || r.class_code || 'FY');
        const title = escapeHtml(r.installment_title || 'Fee Installment');
        const dueDate = r.installment_due_date || '-';

        const totalFees = '₹' + Number(r.student_total_fees || r.installment_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const amountPaid = '₹' + Number(r.installment_amount_paid).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const outstanding = '₹' + Number(r.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const statusBadge = renderStatusBadge(r.computed_status, r.is_overdue);

        tr.innerHTML = `
            <td style="padding:12px 14px; font-weight:600; color:var(--text-muted);">${sr++}</td>
            <td style="padding:12px 14px;">
                <div style="font-weight:700; color:var(--text-main);">${studentName}</div>
                <div style="font-size:11px; color:var(--text-muted);">${escapeHtml(r.student_email || '')}</div>
            </td>
            <td style="padding:12px 14px;">
                <code style="background:#F1F5F9; padding:2px 6px; border-radius:4px; font-weight:700; color:#334155;">${admissionNo}</code>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Roll: ${rollNo}</div>
            </td>
            <td style="padding:12px 14px; font-size:12px; color:var(--text-main);">${dept}</td>
            <td style="padding:12px 14px;">
                <div style="font-weight:600; color:var(--text-main);">${course}</div>
            </td>
            <td style="padding:12px 14px;">
                <span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:6px; font-weight:700; font-size:11px;">
                    ${className}
                </span>
            </td>
            <td style="padding:12px 14px; text-align:right; font-weight:600; color:var(--text-muted);">${totalFees}</td>
            <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--success);">${amountPaid}</td>
            <td style="padding:12px 14px; text-align:right; font-weight:800; color:var(--danger);">${outstanding}</td>
            <td style="padding:12px 14px; font-weight:600; color:var(--text-main);">${title}</td>
            <td style="padding:12px 14px; font-size:12px; white-space:nowrap;">
                ${dueDate}
            </td>
            <td style="padding:12px 14px; text-align:center;">${statusBadge}</td>
            <td style="padding:12px 14px; text-align:center;">
                <div style="display:flex; gap:6px; justify-content:center;">
                    <button type="button" class="btn btn-secondary" style="padding:4px 8px; font-size:11px; font-weight:700;" onclick="openStudentBreakdown(${r.student_id})">
                        👁️ View
                    </button>
                    <button type="button" class="btn btn-primary" style="padding:4px 10px; font-size:11px; font-weight:700; background:#059669; border:none;" 
                            onclick="openRecordPaymentModal(${r.student_id}, '${escapeQuote(studentName)}', '${escapeQuote(admissionNo)}', ${r.installment_id}, '${escapeQuote(title)}', ${r.outstanding_balance})">
                        💳 Collect
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderGroupedRows(tbody, records, pagination) {
    let sr = (pagination.current_page - 1) * pagination.per_page + 1;

    records.forEach((s, idx) => {
        const studentRow = document.createElement('tr');
        studentRow.className = 'accordion-toggle';
        studentRow.style.borderBottom = '1px solid var(--border-color)';
        studentRow.style.background = '#FFFFFF';

        const sName = escapeHtml(s.student_name);
        const adm = escapeHtml(s.admission_no || '-');
        const roll = escapeHtml(s.roll_no || '-');
        const dept = escapeHtml(s.department_name || '-');
        const crs = escapeHtml(s.course_name || '-');
        const cls = escapeHtml(s.class_name || 'FY');

        const totFees = '₹' + Number(s.total_fees).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const totPaid = '₹' + Number(s.total_paid).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const totOut = '₹' + Number(s.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const countInst = (s.installments || []).length;

        studentRow.innerHTML = `
            <td style="padding:12px 14px; font-weight:700; color:var(--text-muted);">${sr++}</td>
            <td style="padding:12px 14px;">
                <div style="font-weight:800; color:var(--text-main); font-size:14px;">${sName}</div>
                <div style="font-size:11px; color:var(--primary); font-weight:600; cursor:pointer;" onclick="toggleStudentAccordion(${s.student_id})">
                    ▼ Show ${countInst} Installment${countInst > 1 ? 's' : ''}
                </div>
            </td>
            <td style="padding:12px 14px;">
                <code style="background:#F1F5F9; padding:2px 6px; border-radius:4px; font-weight:700;">${adm}</code>
                <div style="font-size:11px; color:var(--text-muted);">Roll: ${roll}</div>
            </td>
            <td style="padding:12px 14px; font-size:12px;">${dept}</td>
            <td style="padding:12px 14px; font-weight:600;">${crs}</td>
            <td style="padding:12px 14px;">
                <span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:6px; font-weight:700; font-size:11px;">
                    ${cls}
                </span>
            </td>
            <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--text-muted);">${totFees}</td>
            <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--success);">${totPaid}</td>
            <td style="padding:12px 14px; text-align:right; font-weight:800; color:var(--danger); font-size:14px;">${totOut}</td>
            <td style="padding:12px 14px; font-size:12px; color:var(--text-muted);" colspan="2">
                <span style="background:#FEF3C7; color:#92400E; padding:2px 8px; border-radius:10px; font-weight:700; font-size:11px;">
                    ${countInst} Unsettled Installment${countInst > 1 ? 's' : ''}
                </span>
            </td>
            <td style="padding:12px 14px; text-align:center;">
                <span class="badge-status-overdue">Action Req</span>
            </td>
            <td style="padding:12px 14px; text-align:center;">
                <div style="display:flex; gap:6px; justify-content:center;">
                    <button type="button" class="btn btn-secondary" style="padding:4px 8px; font-size:11px; font-weight:700;" onclick="openStudentBreakdown(${s.student_id})">
                        👁️ Breakdown
                    </button>
                    <button type="button" class="btn btn-primary" style="padding:4px 10px; font-size:11px; font-weight:700; background:#059669; border:none;" onclick="toggleStudentAccordion(${s.student_id})">
                        ▼ Installments
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(studentRow);

        // Accordion Details Row
        const detailsRow = document.createElement('tr');
        detailsRow.id = `accordion_student_${s.student_id}`;
        detailsRow.style.display = 'none';
        detailsRow.style.background = '#F8FAFC';

        let instRowsHtml = '';
        (s.installments || []).forEach(inst => {
            const iTitle = escapeHtml(inst.installment_title);
            const iAmt = '₹' + Number(inst.installment_amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
            const iPaid = '₹' + Number(inst.installment_amount_paid).toLocaleString('en-IN', {minimumFractionDigits: 2});
            const iOut = '₹' + Number(inst.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
            const badge = renderStatusBadge(inst.computed_status, inst.is_overdue);

            instRowsHtml += `
                <tr style="border-bottom:1px solid #E2E8F0; background:#FFFFFF;">
                    <td style="padding:8px 12px; font-weight:700;">${iTitle}</td>
                    <td style="padding:8px 12px; text-align:right;">${iAmt}</td>
                    <td style="padding:8px 12px; text-align:right; color:var(--success); font-weight:700;">${iPaid}</td>
                    <td style="padding:8px 12px; text-align:right; color:var(--danger); font-weight:800;">${iOut}</td>
                    <td style="padding:8px 12px;">${inst.installment_due_date}</td>
                    <td style="padding:8px 12px; text-align:center;">${badge}</td>
                    <td style="padding:8px 12px; text-align:center;">
                        <button type="button" class="btn btn-sm btn-primary" style="padding:3px 8px; font-size:11px; background:#059669; border:none;"
                                onclick="openRecordPaymentModal(${s.student_id}, '${escapeQuote(s.student_name)}', '${escapeQuote(s.admission_no)}', ${inst.installment_id}, '${escapeQuote(inst.installment_title)}', ${inst.outstanding_balance})">
                            💳 Pay Dues
                        </button>
                    </td>
                </tr>
            `;
        });

        detailsRow.innerHTML = `
            <td colspan="13" style="padding:14px 20px;">
                <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; padding:12px; box-shadow:var(--shadow-sm);">
                    <div style="font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:8px;">
                        📌 Individual Unsettled Installments for ${sName} (${adm}):
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="background:#F1F5F9; text-align:left; color:var(--text-muted); font-size:11px;">
                                <th style="padding:8px 12px;">INSTALLMENT</th>
                                <th style="padding:8px 12px; text-align:right;">AMOUNT</th>
                                <th style="padding:8px 12px; text-align:right;">AMOUNT PAID</th>
                                <th style="padding:8px 12px; text-align:right;">OUTSTANDING</th>
                                <th style="padding:8px 12px;">DUE DATE</th>
                                <th style="padding:8px 12px; text-align:center;">STATUS</th>
                                <th style="padding:8px 12px; text-align:center;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${instRowsHtml}
                        </tbody>
                    </table>
                </div>
            </td>
        `;
        tbody.appendChild(detailsRow);
    });
}

function toggleStudentAccordion(studentId) {
    const el = document.getElementById(`accordion_student_${studentId}`);
    if (el) {
        el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
    }
}

function renderStatusBadge(status, isOverdue) {
    if (status === 'overdue' || (isOverdue && status !== 'paid')) {
        return `<span class="badge-status-overdue">⚠️ Overdue</span>`;
    } else if (status === 'overdue_partial') {
        return `<span class="badge-status-overdue">⚠️ Partial (Overdue)</span>`;
    } else if (status === 'partial') {
        return `<span class="badge-status-partial">⏳ Partially Paid</span>`;
    } else if (status === 'paid') {
        return `<span class="badge-status-paid">✓ Settled</span>`;
    }
    return `<span class="badge-status-pending">📅 Pending</span>`;
}

function renderPagination(p) {
    const container = document.getElementById('paginationButtons');
    const info = document.getElementById('paginationInfo');
    container.innerHTML = '';

    if (!p || p.total_records === 0) {
        info.textContent = 'Showing 0 to 0 of 0 records';
        return;
    }

    const start = (p.current_page - 1) * p.per_page + 1;
    const end = Math.min(p.current_page * p.per_page, p.total_records);
    info.textContent = `Showing ${start} to ${end} of ${p.total_records} records (${p.unique_students} unique students)`;

    if (p.total_pages <= 1) return;

    // Previous Button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'btn btn-secondary';
    prevBtn.style.padding = '4px 10px';
    prevBtn.style.fontSize = '12px';
    prevBtn.disabled = (p.current_page <= 1);
    prevBtn.innerHTML = '&larr; Prev';
    prevBtn.onclick = () => loadInstallments(p.current_page - 1);
    container.appendChild(prevBtn);

    // Page Numbers
    for (let i = 1; i <= p.total_pages; i++) {
        if (i === 1 || i === p.total_pages || (i >= p.current_page - 2 && i <= p.current_page + 2)) {
            const pageBtn = document.createElement('button');
            pageBtn.className = (i === p.current_page) ? 'btn btn-primary' : 'btn btn-secondary';
            pageBtn.style.padding = '4px 10px';
            pageBtn.style.fontSize = '12px';
            pageBtn.textContent = i;
            pageBtn.onclick = () => loadInstallments(i);
            container.appendChild(pageBtn);
        } else if (i === p.current_page - 3 || i === p.current_page + 3) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '4px 6px';
            container.appendChild(dots);
        }
    }

    // Next Button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn btn-secondary';
    nextBtn.style.padding = '4px 10px';
    nextBtn.style.fontSize = '12px';
    nextBtn.disabled = (p.current_page >= p.total_pages);
    nextBtn.innerHTML = 'Next &rarr;';
    nextBtn.onclick = () => loadInstallments(p.current_page + 1);
    container.appendChild(nextBtn);
}

// -------------------------------------------------------------
// MODAL: STUDENT BREAKDOWN LOGIC
// -------------------------------------------------------------
function openStudentBreakdown(studentId) {
    const modal = document.getElementById('modalStudentInstallments');
    modal.style.display = 'flex';

    document.getElementById('modalInstallmentsBody').innerHTML = '<tr><td colspan="7" style="padding:20px; text-align:center;">Loading installment details...</td></tr>';
    document.getElementById('modalPaymentsBody').innerHTML = '<tr><td colspan="6" style="padding:20px; text-align:center;">Loading payment history...</td></tr>';

    fetch(`index.php?page=api_student_installments&student_id=${studentId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') {
            alert(res.message || 'Error fetching student details');
            closeBreakdownModal();
            return;
        }

        const s = res.student;
        document.getElementById('breakdownStudentName').textContent = s.name;
        document.getElementById('breakdownStudentMeta').textContent = `Admission: ${s.admission_no} | Roll: ${s.roll_no} | ${s.department_name} • ${s.course_name} (${s.class_name})`;

        document.getElementById('modalTotalFees').textContent = '₹' + Number(s.total_fees).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('modalTotalPaid').textContent = '₹' + Number(s.total_paid).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('modalTotalOutstanding').textContent = '₹' + Number(s.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2});

        // Populate Installments
        const iBody = document.getElementById('modalInstallmentsBody');
        iBody.innerHTML = '';
        if (!res.installments || res.installments.length === 0) {
            iBody.innerHTML = '<tr><td colspan="7" style="padding:16px; text-align:center; color:var(--text-muted);">No installments found for this student.</td></tr>';
        } else {
            res.installments.forEach(inst => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #E2E8F0';
                const iOut = Math.max(0, Number(inst.outstanding_balance));
                const badge = renderStatusBadge(inst.computed_status, inst.is_overdue);

                let actionHtml = '<span style="color:var(--text-muted); font-size:11px;">Settled</span>';
                if (iOut > 0) {
                    actionHtml = `<button type="button" class="btn btn-sm btn-primary" style="padding:3px 8px; font-size:11px; background:#059669; border:none;" 
                        onclick="closeBreakdownModal(); openRecordPaymentModal(${s.id}, '${escapeQuote(s.name)}', '${escapeQuote(s.admission_no)}', ${inst.id}, '${escapeQuote(inst.title)}', ${iOut})">
                        💳 Collect
                    </button>`;
                }

                tr.innerHTML = `
                    <td style="padding:10px 12px; font-weight:700;">${escapeHtml(inst.title)}</td>
                    <td style="padding:10px 12px; text-align:right;">₹${Number(inst.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td style="padding:10px 12px; text-align:right; color:var(--success); font-weight:700;">₹${Number(inst.amount_paid).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td style="padding:10px 12px; text-align:right; color:var(--danger); font-weight:800;">₹${Number(iOut).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td style="padding:10px 12px;">${inst.due_date}</td>
                    <td style="padding:10px 12px; text-align:center;">${badge}</td>
                    <td style="padding:10px 12px; text-align:center;">${actionHtml}</td>
                `;
                iBody.appendChild(tr);
            });
        }

        // Populate Payments
        const pBody = document.getElementById('modalPaymentsBody');
        pBody.innerHTML = '';
        if (!res.payments || res.payments.length === 0) {
            pBody.innerHTML = '<tr><td colspan="6" style="padding:16px; text-align:center; color:var(--text-muted);">No payment transactions recorded yet.</td></tr>';
        } else {
            res.payments.forEach(pay => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #E2E8F0';
                tr.innerHTML = `
                    <td style="padding:10px 12px;"><code style="font-weight:700;">${escapeHtml(pay.transaction_id)}</code></td>
                    <td style="padding:10px 12px;">${escapeHtml(pay.installment_title || 'General Fee')}</td>
                    <td style="padding:10px 12px;"><span style="text-transform:uppercase; background:#F1F5F9; padding:2px 6px; border-radius:4px; font-weight:700; font-size:11px;">${pay.payment_mode}</span></td>
                    <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--success);">₹${Number(pay.amount_paid).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td style="padding:10px 12px; font-size:11px; color:var(--text-muted);">${pay.paid_at}</td>
                    <td style="padding:10px 12px; text-align:center;"><span class="badge-status-paid">✓ Success</span></td>
                `;
                pBody.appendChild(tr);
            });
        }
    })
    .catch(err => {
        alert('Network error loading details');
        closeBreakdownModal();
    });
}

function closeBreakdownModal() {
    document.getElementById('modalStudentInstallments').style.display = 'none';
}

// -------------------------------------------------------------
// MODAL: RECORD PAYMENT WORKFLOW
// -------------------------------------------------------------
function openRecordPaymentModal(studentId, studentName, admissionNo, installmentId, installmentTitle, outstandingBalance) {
    document.getElementById('payStudentId').value = studentId;
    document.getElementById('payInstallmentId').value = installmentId;
    document.getElementById('payMaxOutstanding').value = outstandingBalance;

    document.getElementById('payStudentNameText').textContent = studentName;
    document.getElementById('payAdmissionNoText').textContent = admissionNo;
    document.getElementById('payInstallmentTitleText').textContent = installmentTitle;
    document.getElementById('payOutstandingText').textContent = '₹' + Number(outstandingBalance).toLocaleString('en-IN', {minimumFractionDigits: 2});

    document.getElementById('payAmountInput').value = Number(outstandingBalance).toFixed(2);
    document.getElementById('payAmountInput').max = outstandingBalance;
    document.getElementById('payModeSelect').value = 'cash';
    document.getElementById('payTransactionId').value = '';
    document.getElementById('payRemarks').value = '';
    document.getElementById('paymentAlert').style.display = 'none';

    document.getElementById('modalRecordPayment').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('modalRecordPayment').style.display = 'none';
}

function fillQuickAmount(ratio) {
    const max = parseFloat(document.getElementById('payMaxOutstanding').value) || 0;
    const amt = (max * ratio).toFixed(2);
    document.getElementById('payAmountInput').value = amt;
}

function onPaymentModeChange() {
    const mode = document.getElementById('payModeSelect').value;
    const txInput = document.getElementById('payTransactionId');
    if (mode === 'cash') {
        txInput.placeholder = 'Auto-generated Cash Ref (e.g. CSH-20261028-XXXX)';
    } else if (mode === 'online') {
        txInput.placeholder = 'e.g. UPI / Gateway Reference ID';
    } else if (mode === 'cheque') {
        txInput.placeholder = 'e.g. Cheque No / Bank Name';
    } else {
        txInput.placeholder = 'e.g. POS Transaction Reference';
    }
}

function submitPayment(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitPayment');
    const alertBox = document.getElementById('paymentAlert');
    alertBox.style.display = 'none';

    const amt = parseFloat(document.getElementById('payAmountInput').value) || 0;
    const max = parseFloat(document.getElementById('payMaxOutstanding').value) || 0;

    if (amt <= 0) {
        alertBox.style.display = 'block';
        alertBox.style.background = '#FEF2F2';
        alertBox.style.color = '#DC2626';
        alertBox.textContent = 'Please enter a valid payment amount greater than zero.';
        return;
    }

    if (amt > max + 0.01) {
        alertBox.style.display = 'block';
        alertBox.style.background = '#FEF2F2';
        alertBox.style.color = '#DC2626';
        alertBox.textContent = `Amount cannot exceed current outstanding balance (₹${max.toFixed(2)}).`;
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Processing Settlement...';

    const form = document.getElementById('recordPaymentForm');
    const formData = new FormData(form);

    fetch('index.php?page=record_installment_payment', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.textContent = '✓ Record & Issue Receipt';

        if (res.status === 'success') {
            closePaymentModal();
            showReceiptSuccess(res.receipt);
            loadInstallments(currentPage);
        } else {
            alertBox.style.display = 'block';
            alertBox.style.background = '#FEF2F2';
            alertBox.style.color = '#DC2626';
            alertBox.textContent = res.message || 'Payment recording failed.';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = '✓ Record & Issue Receipt';
        alertBox.style.display = 'block';
        alertBox.style.background = '#FEF2F2';
        alertBox.style.color = '#DC2626';
        alertBox.textContent = 'Network error while recording payment.';
    });
}

function showReceiptSuccess(rcpt) {
    const box = document.getElementById('receiptDetailsBox');
    box.innerHTML = `
        <div style="display:flex; justify-content:space-between; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px;">
            <span style="color:var(--text-muted);">Receipt No / Transaction ID:</span>
            <strong style="color:var(--text-main);">${escapeHtml(rcpt.transaction_id)}</strong>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <span style="color:var(--text-muted);">Student:</span>
            <strong style="color:var(--text-main);">${escapeHtml(rcpt.student_name)} (${escapeHtml(rcpt.admission_no)})</strong>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <span style="color:var(--text-muted);">Installment:</span>
            <span>${escapeHtml(rcpt.installment_title)}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <span style="color:var(--text-muted);">Payment Mode:</span>
            <strong>${escapeHtml(rcpt.payment_mode)}</strong>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:14px; font-weight:800; color:var(--success);">
            <span>Amount Paid:</span>
            <span>₹${Number(rcpt.amount_paid).toLocaleString('en-IN', {minimumFractionDigits:2})}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:8px; border-top:1px dashed #CBD5E1; padding-top:6px;">
            <span style="color:var(--text-muted);">Remaining Installment Balance:</span>
            <strong style="color:${rcpt.remaining_balance <= 0 ? 'var(--success)' : 'var(--danger)'};">
                ${rcpt.remaining_balance <= 0 ? 'Fully Settled (₹0.00)' : '₹' + Number(rcpt.remaining_balance).toLocaleString('en-IN', {minimumFractionDigits:2})}
            </strong>
        </div>
        <div style="font-size:11px; color:var(--text-muted); text-align:right; margin-top:6px;">
            Recorded on: ${escapeHtml(rcpt.paid_at)}
        </div>
    `;
    document.getElementById('modalReceiptSuccess').style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('modalReceiptSuccess').style.display = 'none';
}

// -------------------------------------------------------------
// MODAL: SYNC COURSE INSTALLMENTS
// -------------------------------------------------------------
function openSyncInstallmentsModal() {
    document.getElementById('syncResultAlert').style.display = 'none';
    document.getElementById('modalSyncInstallments').style.display = 'flex';
}

function closeSyncModal() {
    document.getElementById('modalSyncInstallments').style.display = 'none';
}

function executeInstallmentSync() {
    const btn = document.getElementById('btnExecuteSync');
    const alertBox = document.getElementById('syncResultAlert');
    const courseId = document.getElementById('syncCourseSelect').value;

    btn.disabled = true;
    btn.textContent = 'Syncing...';
    alertBox.style.display = 'none';

    const formData = new FormData();
    formData.append('csrf_token', '<?= \App\Core\CSRF::getToken() ?>');
    formData.append('course_id', courseId);

    fetch('index.php?page=generate_student_installments', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.textContent = 'Start Sync';
        alertBox.style.display = 'block';

        if (res.status === 'success') {
            alertBox.style.background = '#DCFCE7';
            alertBox.style.color = '#16A34A';
            alertBox.textContent = res.message;
            loadInstallments(currentPage);
            setTimeout(closeSyncModal, 1500);
        } else {
            alertBox.style.background = '#FEF2F2';
            alertBox.style.color = '#DC2626';
            alertBox.textContent = res.message || 'Sync failed.';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Start Sync';
        alertBox.style.display = 'block';
        alertBox.style.background = '#FEF2F2';
        alertBox.style.color = '#DC2626';
        alertBox.textContent = 'Network error executing installment sync.';
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

function trimStr(str) {
    return String(str).replace(/\s+/g, ' ').trim();
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
