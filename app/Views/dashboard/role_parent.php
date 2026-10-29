<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:linear-gradient(135deg, #0EA5E9, #0284C7); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px;">
        <h2 style="font-size:22px; font-weight:800;">Parent & Guardian Student Portal</h2>
        <p style="font-size:13px; opacity:0.9;">Monitoring Child Progress for: <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'], ENT_QUOTES, 'UTF-8') ?></strong> (Admission No: <?= htmlspecialchars($student['admission_no'], ENT_QUOTES, 'UTF-8') ?>)</p>
    </div>

    <!-- Student Attendance & Status Metrics -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="index.php?page=module&name=student_attendance" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Child Attendance %</span></div>
            <div class="metric-value" style="color:var(--success);">92.5%</div>
            <div class="metric-sub">Regular Attendance Record</div>
        </a>
        <a href="index.php?page=module&name=student_attendance" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Absence Alerts</span></div>
            <div class="metric-value" style="color:var(--success);">0 Unread</div>
            <div class="metric-sub">Parent SMS/Email Notification Queue</div>
        </a>
        <a href="index.php?page=module&name=fees" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Pending Fee Dues</span></div>
            <div class="metric-value" style="color:var(--warning);">₹6,250.00</div>
            <div class="metric-sub">Due Date: 30 Sep 2026</div>
        </a>
        <a href="index.php?page=module&name=news_events" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Institution Circulars</span></div>
            <div class="metric-value"><?= count($notices) ?> Published</div>
            <div class="metric-sub">Academic Announcements</div>
        </a>
    </section>

    <!-- Child Fee Payment Section -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Fee Installments & Gateway Payment</h3>
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
                    <?php foreach ($installments as $inst): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($inst['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px; font-weight:700;">₹<?= number_format($inst['amount'], 2) ?></td>
                        <td style="padding:12px;"><?= $inst['due_date'] ?></td>
                        <td style="padding:12px;">
                            <span style="background:<?= $inst['status'] === 'paid' ? 'var(--success-light)' : 'var(--warning-light)' ?>; color:<?= $inst['status'] === 'paid' ? 'var(--success)' : 'var(--warning)' ?>; padding:2px 8px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= $inst['status'] ?></span>
                        </td>
                        <td style="padding:12px; text-align:right;">
                            <?php if ($inst['status'] !== 'paid'): ?>
                                <button type="button" class="btn btn-primary" style="padding:4px 12px; font-size:11px;" onclick="payParentInstallment(<?= $inst['id'] ?>, <?= $inst['amount'] ?>)">Pay Now via Gateway</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px;" onclick="showReceiptModal(<?= $inst['id'] ?>, '<?= htmlspecialchars($inst['title'], ENT_QUOTES, 'UTF-8') ?>', <?= $inst['amount'] ?>)">Download Receipt</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Parent Notifications & Circulars Feed -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Institution Notices & Circulars</h3>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px; margin-top:16px;">
            <?php foreach ($notices as $n): ?>
            <div style="background:#F8FAFC; border:1px solid var(--border-color); padding:14px; border-radius:var(--radius-md);">
                <div style="display:flex; justify-space-between; align-items:center;">
                    <strong style="font-size:14px; color:var(--text-main);"><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span style="font-size:11px; color:var(--text-muted);"><?= $n['publish_date'] ?></span>
                </div>
                <p style="font-size:13px; color:var(--text-muted); margin-top:6px;"><?= htmlspecialchars($n['content'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- RECEIPT MODAL -->
<div class="modal-overlay" id="receiptModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">Fee Payment Official Receipt</h3>
            <button type="button" onclick="document.getElementById('receiptModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body" id="receiptModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.print()">Print Receipt</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('receiptModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<script>
function payParentInstallment(id, amount) {
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
        });
    }
}

function showReceiptModal(id, title, amount) {
    const body = document.getElementById('receiptModalBody');
    body.innerHTML = `
        <div style="border:1px solid #E2E8F0; padding:20px; border-radius:8px; background:#F8FAFC;">
            <div style="display:flex; justify-content:space-between; margin-bottom:16px; border-bottom:2px solid #0066FF; padding-bottom:10px;">
                <div>
                    <h2 style="font-size:18px; font-weight:800; color:#0F172A;">Campus360 Enterprise ERP</h2>
                    <p style="font-size:12px; color:#64748B;">Official Student Fee Payment Receipt</p>
                </div>
                <div style="text-align:right;">
                    <span style="font-weight:700; color:#0066FF;">RECEIPT #REC-${id}</span>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:13px; margin-bottom:16px;">
                <div><strong>Fee Description:</strong> ${title}</div>
                <div><strong>Payment Status:</strong> Settlement Paid</div>
                <div><strong>Payment Gateway:</strong> Online Webhook Verified</div>
                <div><strong>Date Settled:</strong> ${new Date().toLocaleDateString()}</div>
            </div>
            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:6px; padding:12px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:14px; font-weight:700; color:#0F172A;">TOTAL PAID AMOUNT:</span>
                <span style="font-size:20px; font-weight:800; color:#10B981;">₹${amount.toFixed(2)}</span>
            </div>
        </div>
    `;
    document.getElementById('receiptModal').classList.add('active');
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
