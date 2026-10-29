<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div class="dashboard-banner" style="background: linear-gradient(135deg, #059669, #047857); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #A7F3D0; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Front Office & Visitor Desk</span>
                <h1 style="font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">Welcome, <?= htmlspecialchars($user['username'] ?? 'Receptionist', ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="color: #A7F3D0; font-size: 13px; margin: 0;">Register daily campus visitors, issue gate badges, log walk-in admission enquiries, and assist public callers.</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #A7F3D0;">Job Role</span>
                <div style="font-weight: 700; color: #ECFDF5; font-size: 14px;">Front Office Executive</div>
            </div>
        </div>
    </div>

    <!-- Receptionist Metrics Row -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=visitor_mgmt" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Today's Visitors</span>
                <span class="metric-menu">👤</span>
            </div>
            <div class="metric-value"><?= count($visitors ?? []) ?></div>
            <div class="metric-sub">Check-in Badges Issued</div>
        </a>
        <a href="index.php?page=module&name=enquiry" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Front Desk Enquiries</span>
                <span class="metric-menu">🎧</span>
            </div>
            <div class="metric-value"><?= count($enquiries ?? []) ?></div>
            <div class="metric-sub">Walk-in & Phone Enquiries</div>
        </a>
        <a href="index.php?page=module&name=tickets" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Public Tickets</span>
                <span class="metric-menu">📦</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['tickets'] ?? 8) ?></div>
            <div class="metric-sub">Pending Help Requests</div>
        </a>
        <a href="index.php?page=module&name=news_events" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Notices Broadcast</span>
                <span class="metric-menu">📑</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['notices'] ?? 102) ?></div>
            <div class="metric-sub">Internal Bulletins</div>
        </a>
    </section>

    <!-- Recent Visitors Table Summary -->
    <section class="schedule-card" style="margin-bottom: 24px;">
        <div class="schedule-header">
            <div>
                <h3 style="font-size: 16px; font-weight: 700;">Front Office Visitor Log</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Real-time visitor check-ins & badges</span>
            </div>
            <a href="index.php?page=module&name=visitor" class="btn btn-secondary" style="font-size: 12px;">Visitor Module</a>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; text-align: left; font-size: 12px; color: #64748B;">
                        <th style="padding: 10px;">Badge No</th>
                        <th style="padding: 10px;">Visitor Name</th>
                        <th style="padding: 10px;">Phone</th>
                        <th style="padding: 10px;">Purpose</th>
                        <th style="padding: 10px;">Person To Meet</th>
                        <th style="padding: 10px;">Check In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($visitors)): ?>
                        <?php foreach (array_slice($visitors, 0, 5) as $vis): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                <td style="padding: 10px; font-weight: 700; color: #059669;"><?= htmlspecialchars($vis['badge_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($vis['visitor_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($vis['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($vis['purpose'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($vis['person_to_meet'] ?? 'Office', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($vis['check_in'] ?? 'Now', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding: 16px; text-align: center; color: var(--text-muted);">No visitors logged today.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
