<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div class="dashboard-banner" style="background: linear-gradient(135deg, #1E293B, #0F172A); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60A5FA; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Administrative Officer Portal</span>
                <h1 style="font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">Welcome, <?= htmlspecialchars($user['username'] ?? 'Admin Officer', ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="color: #94A3B8; font-size: 13px; margin: 0;">Manage admission enquiries, student records, certificates, ID cards, visitor desk, and office communications.</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #94A3B8;">Job Role</span>
                <div style="font-weight: 700; color: #F8FAFC; font-size: 14px;">Office Administrator</div>
            </div>
        </div>
    </div>

    <!-- Administrative KPI Metrics Row -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=enquiry" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Active Enquiries</span>
                <span class="metric-menu">🎧</span>
            </div>
            <div class="metric-value"><?= count($enquiries ?? []) ?></div>
            <div class="metric-sub">Pending Student Leads</div>
        </a>
        <a href="index.php?page=module&name=admission" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Admissions Processed</span>
                <span class="metric-menu">💻</span>
            </div>
            <div class="metric-value"><?= count($admissions ?? []) ?></div>
            <div class="metric-sub">Active Term Applicants</div>
        </a>
        <a href="index.php?page=module&name=visitor_mgmt" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Visitors Registered</span>
                <span class="metric-menu">👤</span>
            </div>
            <div class="metric-value"><?= count($visitors ?? []) ?></div>
            <div class="metric-sub">Today Gate Passes</div>
        </a>
        <a href="index.php?page=module&name=tickets" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Support Tickets</span>
                <span class="metric-menu">📦</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['tickets'] ?? 12) ?></div>
            <div class="metric-sub">Pending Office Tickets</div>
        </a>
    </section>

    <!-- Administrative Modules Quick Actions -->
    <section class="schedule-card" style="margin-bottom: 24px;">
        <div class="schedule-header">
            <div>
                <h3 style="font-size: 16px; font-weight: 700;">Recent Admission Enquiries</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Manage candidate inquiries and conversion status</span>
            </div>
            <a href="index.php?page=module&name=enquiry" class="btn btn-secondary" style="font-size: 12px;">View All Enquiries</a>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; text-align: left; font-size: 12px; color: #64748B;">
                        <th style="padding: 10px;">Student Name</th>
                        <th style="padding: 10px;">Phone</th>
                        <th style="padding: 10px;">Course Interested</th>
                        <th style="padding: 10px;">Source</th>
                        <th style="padding: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enquiries)): ?>
                        <?php foreach (array_slice($enquiries, 0, 5) as $enq): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($enq['student_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($enq['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($enq['course_interested'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($enq['source'] ?? 'Website', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;">
                                    <span class="badge" style="background: #FEF3C7; color: #D97706; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;"><?= strtoupper($enq['status'] ?? 'PENDING') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 16px; text-align: center; color: var(--text-muted);">No recent enquiries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
