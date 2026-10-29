<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:#0F172A; color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h2 style="font-size:22px; font-weight:800;">Global Super Admin Platform Oversight</h2>
            <p style="font-size:13px; opacity:0.8;">Multi-tenant institution provisioning, status monitoring & platform health</p>
        </div>
        <a href="index.php?page=register_tenant" class="btn btn-primary" style="background:#0066FF; text-decoration:none;">+ Onboard New College/School Tenant</a>
    </div>

    <!-- Global Platform Metrics -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="#registered-tenants" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Total Institutions</span></div>
            <div class="metric-value"><?= number_format($metrics['tenants'] ?? 0) ?></div>
            <div class="metric-sub">Active Provisioned Tenants</div>
        </a>
        <a href="index.php?page=module&name=student_mis" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Global Students</span></div>
            <div class="metric-value"><?= number_format($metrics['students'] ?? 0) ?></div>
            <div class="metric-sub">Enrolled Across Institutions</div>
        </a>
        <a href="index.php?page=module&name=staff_data" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Global Faculty & Staff</span></div>
            <div class="metric-value"><?= number_format($metrics['staff'] ?? 0) ?></div>
            <div class="metric-sub">Active Employee Accounts</div>
        </a>
        <a href="index.php?page=module&name=audit_logs" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">System Status</span></div>
            <div class="metric-value" style="color:<?= ($metrics['system_health_pct'] ?? 100) >= 100 ? 'var(--success)' : '#D97706' ?>; font-size:20px;">
                <?= htmlspecialchars($metrics['system_status_label'] ?? 'HEALTHY 100%', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="metric-sub">PDO Connection & Security Active</div>
        </a>
    </section>

    <!-- Provisioned Tenants Directory -->
    <div class="chart-card" id="registered-tenants">
        <div class="chart-header">
            <h3>Registered Institutions & Tenants</h3>
            <span style="font-size:12px; color:var(--text-muted);">Manage Tenant Approvals & Access Controls</span>
        </div>

        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">INSTITUTION NAME</th>
                        <th style="padding:12px;">CODE</th>
                        <th style="padding:12px;">DOMAIN</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $t): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px;"><?= $t['id'] ?></td>
                        <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><code><?= htmlspecialchars($t['code'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td style="padding:12px;"><?= htmlspecialchars($t['domain'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;">
                            <span style="background:<?= $t['status'] === 'active' ? 'var(--success-light)' : 'var(--danger-light)' ?>; color:<?= $t['status'] === 'active' ? 'var(--success)' : 'var(--danger)' ?>; padding:4px 10px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= $t['status'] ?></span>
                        </td>
                        <td style="padding:12px; text-align:right;">
                            <form action="index.php?page=toggle_tenant_status" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="status" value="<?= $t['status'] === 'active' ? 'suspended' : 'active' ?>">
                                <button type="submit" class="btn btn-secondary" style="padding:4px 12px; font-size:11px; font-weight:700; color:<?= $t['status'] === 'active' ? 'var(--danger)' : 'var(--success)' ?>;">
                                    <?= $t['status'] === 'active' ? 'Suspend Access' : 'Activate Access' ?>
                                </button>
                            </form>
                            <form action="index.php?page=delete_tenant" method="POST" style="display:inline; margin-left:6px;" onsubmit="return confirm('Are you sure you want to delete this institution (<?= htmlspecialchars(addslashes($t['name']), ENT_QUOTES, 'UTF-8') ?>)? This action will remove all associated tenant data.');">
                                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="padding:4px 12px; font-size:11px; font-weight:700; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
