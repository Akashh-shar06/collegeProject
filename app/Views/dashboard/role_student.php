<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:linear-gradient(135deg, #8B5CF6, #6D28D9); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h2 style="font-size:22px; font-weight:800;">Student Personal Academic Portal</h2>
            <p style="font-size:13px; opacity:0.9;">Welcome back, <?= htmlspecialchars($student['first_name'] ?? 'John', ENT_QUOTES, 'UTF-8') ?>! Admission No: <strong><?= htmlspecialchars($student['admission_no'] ?? 'ADM-2024-001', ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <a href="index.php?page=transfer_certificate&student_id=<?= $student['id'] ?? 1 ?>" class="btn btn-secondary" style="background:#FFFFFF; color:#6D28D9; font-weight:700; text-decoration:none;">📄 Download Official Report Card PDF</a>
    </div>

    <!-- Personal Metrics -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="index.php?page=module&name=student_attendance" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Running Attendance %</span></div>
            <div class="metric-value" style="color:<?= ($attendancePct ?? 100) >= 75 ? 'var(--success)' : 'var(--danger)' ?>;"><?= $attendancePct ?? 100 ?>%</div>
            <div class="metric-sub"><?= $presentAtt ?? 0 ?> Present / <?= $totalAtt ?? 0 ?> Total Days</div>
        </a>
        <a href="index.php?page=module&name=exam_gradebook" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Current GPA / Rank</span></div>
            <div class="metric-value"><?= number_format($gpa ?? 3.8, 1) ?> / Rank <?= $classRank ?? 1 ?></div>
            <div class="metric-sub">Term 1 Assessment</div>
        </a>
        <a href="index.php?page=module&name=fees" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Pending Fee Dues</span></div>
            <div class="metric-value" style="color:var(--warning);">₹<?= number_format($pendingFeeSum ?? 6250, 2) ?></div>
            <div class="metric-sub">Active Term Balance</div>
        </a>
        <a href="index.php?page=module&name=library" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Library Issues</span></div>
            <div class="metric-value"><?= $activeBooksCount ?? 1 ?> Book(s) Active</div>
            <div class="metric-sub">Clean Circulation Log</div>
        </a>
    </section>

    <!-- Student Attendance Log Section (Section 6.3) -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>My Daily Attendance Record & History</h3>
            <span style="font-size:12px; color:var(--text-muted);">Real-time roster updates logged by faculty</span>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">DATE</th>
                        <th style="padding:12px;">ATTENDANCE STATUS</th>
                        <th style="padding:12px;">RECORDED AT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentAttendance)): ?>
                        <?php foreach ($recentAttendance as $att): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px; font-weight:700;"><?= date('d M Y', strtotime($att['date'])) ?></td>
                            <td style="padding:12px;">
                                <?php if ($att['status'] === 'present'): ?>
                                    <span style="background:var(--success-light); color:var(--success); padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;">Present ✅</span>
                                <?php elseif ($att['status'] === 'absent'): ?>
                                    <span style="background:#FEE2E2; color:#DC2626; padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;">Absent ❌</span>
                                <?php elseif ($att['status'] === 'late'): ?>
                                    <span style="background:var(--warning-light); color:var(--warning); padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;">Late ⏱️</span>
                                <?php else: ?>
                                    <span style="background:#F1F5F9; color:#475569; padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= htmlspecialchars($att['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px; color:var(--text-muted);"><?= htmlspecialchars($att['created_at'] ?? $att['date'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding:20px; text-align:center; color:var(--text-muted);">No attendance records found for your account yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Grades & Class Rank -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Academic Grades & Examination Summary</h3>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">EXAMINATION</th>
                        <th style="padding:12px;">MARKS OBTAINED</th>
                        <th style="padding:12px;">LETTER GRADE</th>
                        <th style="padding:12px;">REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grades)): ?>
                        <?php foreach ($grades as $g): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($g['exam_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px;"><?= $g['marks_obtained'] ?> / 100</td>
                            <td style="padding:12px;"><span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:var(--radius-pill); font-weight:700;"><?= $g['grade'] ?></span></td>
                            <td style="padding:12px; color:var(--text-muted);"><?= htmlspecialchars($g['remarks'] ?? 'Satisfactory', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding:20px; text-align:center; color:var(--text-muted);">No exam results published for your student profile yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Fee Installments & Official Payment Receipts Section -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3>💳 Fee Installments & Payment Receipts</h3>
                <span style="font-size:12px; color:var(--text-muted);">View fee breakdown, pay dues in installments, and download official payment receipts</span>
            </div>
            <a href="index.php?page=module&name=fees" class="btn btn-secondary" style="font-size:12px; font-weight:700; text-decoration:none;">📄 View All Fee Records</a>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">FEE HEAD / INSTALLMENT</th>
                        <th style="padding:12px;">AMOUNT (₹)</th>
                        <th style="padding:12px;">DUE DATE</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($installments)): ?>
                        <?php foreach ($installments as $inst): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($inst['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px; font-weight:700; color:var(--primary);">₹<?= number_format($inst['amount'], 2) ?></td>
                            <td style="padding:12px; color:var(--text-muted);"><?= date('d M Y', strtotime($inst['due_date'])) ?></td>
                            <td style="padding:12px;">
                                <?php if (strtolower($inst['status']) === 'paid'): ?>
                                    <span style="background:var(--success-light); color:var(--success); padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;">Paid ✅</span>
                                <?php else: ?>
                                    <span style="background:var(--warning-light); color:var(--warning); padding:3px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;">Unpaid ⏳</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px; text-align:right;">
                                <?php if (strtolower($inst['status']) === 'paid'): ?>
                                    <button type="button" class="btn btn-secondary" style="padding:6px 14px; font-size:12px; font-weight:700; border-color:#0066FF; color:#0066FF;" onclick="showStudentReceiptModal(<?= $inst['id'] ?>, '<?= htmlspecialchars($inst['title'], ENT_QUOTES, 'UTF-8') ?>', <?= $inst['amount'] ?>, '<?= htmlspecialchars(($student['first_name'] ?? 'John') . ' ' . ($student['last_name'] ?? 'Doe'), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($student['admission_no'] ?? 'ADM-2024-001', ENT_QUOTES, 'UTF-8') ?>')">📄 Download Receipt</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary" style="padding:6px 14px; font-size:12px; font-weight:700;" onclick="payStudentInstallment(<?= $inst['id'] ?>, <?= $inst['amount'] ?>)">💳 Pay Now via Gateway</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding:20px; text-align:center; color:var(--text-muted);">No fee installments found for your account.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STUDENT OFFICIAL FEE RECEIPT MODAL -->
    <div class="modal-overlay" id="studentReceiptModal">
        <div class="modal-card" style="max-width:580px;">
            <div class="modal-header">
                <h3 style="font-size:16px; font-weight:700;">Official Fee Payment Receipt</h3>
                <button type="button" onclick="document.getElementById('studentReceiptModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body" id="studentReceiptModalBody">
            </div>
            <div class="modal-footer" style="display:flex; justify-content:space-between;">
                <button type="button" class="btn btn-secondary" onclick="printStudentFeeReceipt()">🖨️ Print / Save PDF Receipt</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('studentReceiptModal').classList.remove('active')">Close</button>
            </div>
        </div>
    </div>

    <!-- All 12 Student Modules Grid Section -->
    <?php
    $studentModules = [
        'student_attendance' => ['title' => 'Daily Attendance', 'desc' => 'View daily presence & attendance logs', 'icon' => '📅', 'class' => 'icon-purple'],
        'academic_calendar'  => ['title' => 'Academic Calendar', 'desc' => 'Terms, holidays & exam schedules', 'icon' => '📆', 'class' => 'icon-blue'],
        'time_table'         => ['title' => 'Class Time Table', 'desc' => 'Daily lecture schedules & room nos', 'icon' => '🗓️', 'class' => 'icon-red'],
        'online_exam'        => ['title' => 'Online Exam Portal', 'desc' => 'Take online MCQ quizzes & exams', 'icon' => '📝', 'class' => 'icon-purple'],
        'exam_management'   => ['title' => 'Exam & Gradebook', 'desc' => 'View hall tickets, marks & transcripts', 'icon' => '📖', 'class' => 'icon-red'],
        'fees'               => ['title' => 'Fees & Receipts', 'desc' => 'Check fee structure & payment history', 'icon' => '💵', 'class' => 'icon-amber'],
        'library'            => ['title' => 'Library Management', 'desc' => 'Search books & view issued books', 'icon' => '📚', 'class' => 'icon-amber'],
        'student_mis'        => ['title' => 'Student MIS Profile', 'desc' => 'Personal academic record & bio', 'icon' => '☁️', 'class' => 'icon-green'],
        'news_events'        => ['title' => 'News & Events', 'desc' => 'Notices, circulars & campus bulletins', 'icon' => '📑', 'class' => 'icon-red'],
        'communication'      => ['title' => 'Communication', 'desc' => 'Broadcasts & campus messages', 'icon' => '✈️', 'class' => 'icon-blue'],
        'tutorial'           => ['title' => 'Study Tutorials', 'desc' => 'Study notes, lectures & resources', 'icon' => '💬', 'class' => 'icon-dark'],
        'support'            => ['title' => 'Help Desk & Support', 'desc' => 'Raise support tickets & query help', 'icon' => '🤝', 'class' => 'icon-amber'],
    ];
    ?>

    <section class="launcher-section" style="margin-top:24px;">
        <div class="launcher-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:var(--text-main); margin-bottom:4px;">Student Academic & Portal Modules</h2>
                <p style="font-size:12px; color:var(--text-muted); margin:0;">Access all 12 modules for attendance, exams, timetables, library, study notes, and support</p>
            </div>
            <span style="font-size:12px; font-weight:700; color:#6D28D9; background:#F3E8FF; padding:6px 14px; border-radius:var(--radius-pill); border:1px solid #DDD6FE;">12 Student Modules Active</span>
        </div>

        <div class="launcher-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:16px;">
            <?php foreach ($studentModules as $key => $mod): ?>
                <a href="index.php?page=module&name=<?= $key ?>" class="module-card" data-module="<?= $key ?>" style="text-decoration:none; display:flex; align-items:center; gap:14px; padding:16px; background:#FFFFFF; border:1px solid var(--border-color); border-radius:var(--radius-md); transition:all 0.2s ease-in-out; box-shadow:var(--shadow-sm);">
                    <div class="module-icon <?= $mod['class'] ?>" style="font-size:24px; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#F8FAFC;">
                        <?= $mod['icon'] ?>
                    </div>
                    <div class="module-info" style="display:flex; flex-direction:column; gap:3px; overflow:hidden;">
                        <span class="module-title" style="font-weight:700; font-size:14px; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="module-desc" style="font-size:11px; color:var(--text-muted); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?= htmlspecialchars($mod['desc'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script>
function payStudentInstallment(id, amount) {
    if (confirm(`Proceed to payment gateway checkout for ₹${amount}?`)) {
        const formData = new FormData();
        formData.append('installment_id', id);
        formData.append('amount', amount);

        fetch('index.php?page=payment_webhook', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert('💳 Payment Gateway Callback Confirmed!\n' + data.message);
            window.location.reload();
        })
        .catch(err => {
            alert('Payment processing error. Please try again.');
        });
    }
}

function showStudentReceiptModal(id, title, amount, studentName, admissionNo) {
    const body = document.getElementById('studentReceiptModalBody');
    const todayStr = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    body.innerHTML = `
        <div id="printableStudentReceipt" style="border:1px solid #CBD5E1; padding:24px; border-radius:12px; background:#FFFFFF; font-family:inherit;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; border-bottom:3px solid #6D28D9; padding-bottom:12px;">
                <div>
                    <h2 style="font-size:20px; font-weight:800; color:#0F172A; margin:0;">Campus360 Enterprise ERP</h2>
                    <p style="font-size:12px; color:#64748B; margin:4px 0 0 0;">Official Student Fee Payment Receipt</p>
                </div>
                <div style="text-align:right;">
                    <span style="display:inline-block; background:#F3E8FF; color:#6D28D9; font-weight:800; padding:4px 10px; border-radius:6px; font-size:12px;">RECEIPT #REC-${id}</span>
                    <div style="font-size:11px; color:#64748B; margin-top:4px;">Date: ${todayStr}</div>
                </div>
            </div>

            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px; margin-bottom:16px; display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:13px;">
                <div><span style="color:#64748B; font-weight:600;">Student Name:</span> <br><strong style="color:#0F172A;">${studentName}</strong></div>
                <div><span style="color:#64748B; font-weight:600;">Admission No:</span> <br><strong style="color:#6D28D9; font-family:monospace;">${admissionNo}</strong></div>
                <div><span style="color:#64748B; font-weight:600;">Payment Gateway:</span> <br><strong>Online Verified Settlement</strong></div>
                <div><span style="color:#64748B; font-weight:600;">Payment Status:</span> <br><span style="color:#10B981; font-weight:800;">SETTLED & PAID ✅</span></div>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:16px;">
                <thead>
                    <tr style="background:#F1F5F9; text-align:left; border-bottom:1px solid #CBD5E1;">
                        <th style="padding:8px 10px;">ITEM / DESCRIPTION</th>
                        <th style="padding:8px 10px; text-align:right;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:10px; font-weight:700; border-bottom:1px solid #E2E8F0;">${title}</td>
                        <td style="padding:10px; text-align:right; font-weight:700; border-bottom:1px solid #E2E8F0;">₹${parseFloat(amount).toFixed(2)}</td>
                    </tr>
                </tbody>
            </table>

            <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:14px; font-weight:700; color:#166534;">TOTAL PAID AMOUNT:</span>
                <span style="font-size:22px; font-weight:800; color:#15803D;">₹${parseFloat(amount).toFixed(2)}</span>
            </div>

            <div style="margin-top:20px; text-align:center; font-size:11px; color:#94A3B8; border-top:1px dashed #E2E8F0; padding-top:10px;">
                This is a computer-generated official receipt issued by Campus360 ERP System. No signature required.
            </div>
        </div>
    `;
    document.getElementById('studentReceiptModal').classList.add('active');
}

function printStudentFeeReceipt() {
    const printable = document.getElementById('printableStudentReceipt');
    if (!printable) return;
    const printWin = window.open('', '_blank', 'width=650,height=700');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fee Payment Official Receipt - Campus360</title>
            <style>
                body { margin:0; padding:40px; background:#F8FAFC; font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display:flex; justify-content:center; }
                @media print {
                    body { background:none; padding:0; }
                }
            </style>
        </head>
        <body>
            \${printable.outerHTML}
            <script>
                window.onload = function() { window.print(); };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

// Live Auto-Poll for Automatic Data Updates when Teacher submits new data
let lastStateHash = null;
function checkRealtimeUpdates() {
    fetch('index.php?page=api_dashboard_updates')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (lastStateHash && lastStateHash !== data.hash) {
                    window.location.reload();
                }
                lastStateHash = data.hash;
            }
        })
        .catch(err => console.debug('Sync check error:', err));
}
setInterval(checkRealtimeUpdates, 5000);
checkRealtimeUpdates();
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
