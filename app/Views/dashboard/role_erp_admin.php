<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div class="dashboard-banner" style="background: linear-gradient(135deg, #4C1D95, #6D28D9); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(76, 29, 149, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #DDD6FE; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">System Administration & Security</span>
                <h1 style="font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">Welcome, <?= htmlspecialchars($user['username'] ?? 'ERP Admin', ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="color: #DDD6FE; font-size: 13px; margin: 0;">Configure Role-Based Access Control (RBAC), monitor security audit logs, audit application usage, manage user accounts, and resolve technical tickets.</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #DDD6FE;">Job Role</span>
                <div style="font-weight: 700; color: #F5F3FF; font-size: 14px;">ERP System Administrator</div>
            </div>
        </div>
    </div>

    <!-- ERP Admin Security & Technical KPI Metrics -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=roles_permissions" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Active Job Roles</span>
                <span class="metric-menu">♿</span>
            </div>
            <div class="metric-value"><?= count($rolesList ?? []) ?></div>
            <div class="metric-sub">Configured RBAC Roles</div>
        </a>
        <a href="index.php?page=module&name=user_management" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Total Users</span>
                <span class="metric-menu">👤</span>
            </div>
            <div class="metric-value"><?= count($usersList ?? []) ?></div>
            <div class="metric-sub">Active ERP User Accounts</div>
        </a>
        <a href="index.php?page=module&name=audit_logs" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Security Audit Logs</span>
                <span class="metric-menu">📊</span>
            </div>
            <div class="metric-value"><?= count($auditLogs ?? []) ?></div>
            <div class="metric-sub">Recorded System Events</div>
        </a>
        <a href="index.php?page=module&name=audit_logs" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Failed Logins</span>
                <span class="metric-menu">🔒</span>
            </div>
            <div class="metric-value">0</div>
            <div class="metric-sub">Security Lockouts</div>
        </a>
    </section>

    <!-- User & Role Management Table Overview -->
    <section class="schedule-card" style="margin-bottom: 24px;">
        <div class="schedule-header">
            <div>
                <h3 style="font-size: 16px; font-weight: 700;">ERP User Accounts & Job Role Distribution</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Manage accounts, password resets & permission scoping</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="index.php?page=module&name=role_permission" class="btn btn-primary" style="font-size: 12px; padding: 6px 14px;">Role & Permission Matrix</a>
                <a href="index.php?page=module&name=app_analysis" class="btn btn-secondary" style="font-size: 12px; padding: 6px 14px;">Audit Logs</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; text-align: left; font-size: 12px; color: #64748B;">
                        <th style="padding: 10px;">User ID</th>
                        <th style="padding: 10px;">Username</th>
                        <th style="padding: 10px;">Email</th>
                        <th style="padding: 10px;">Assigned Job Role</th>
                        <th style="padding: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usersList)): ?>
                        <?php foreach (array_slice($usersList, 0, 8) as $usr): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                <td style="padding: 10px; font-weight: 700; color: #6D28D9;">#<?= (int)$usr['id'] ?></td>
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($usr['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($usr['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px; font-weight: 700; color: #4C1D95;"><?= htmlspecialchars($usr['role_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;">
                                    <span class="badge" style="background: #DCFCE7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;"><?= strtoupper($usr['status'] ?? 'ACTIVE') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 16px; text-align: center; color: var(--text-muted);">No user accounts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
