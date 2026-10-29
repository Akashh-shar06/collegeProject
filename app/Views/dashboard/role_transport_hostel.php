<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div class="dashboard-banner" style="background: linear-gradient(135deg, #0284C7, #0369A1); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #E0F2FE; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Transport & Hostel Operations</span>
                <h1 style="font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">Welcome, <?= htmlspecialchars($user['username'] ?? 'Transport Admin', ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="color: #BAE6FD; font-size: 13px; margin: 0;">Manage bus routes, drivers, vehicle fleet, hostel blocks, room capacity, student boarding allocations, and day boarding services.</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #BAE6FD;">Job Role</span>
                <div style="font-weight: 700; color: #F0F9FF; font-size: 14px;">Transport & Hostel Administrator</div>
            </div>
        </div>
    </div>

    <!-- Transport & Hostel Metrics Row -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=transport" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Active Routes</span>
                <span class="metric-menu">🚌</span>
            </div>
            <div class="metric-value"><?= count($routes ?? []) ?></div>
            <div class="metric-sub">Bus Fleet & Driver Routes</div>
        </a>
        <a href="index.php?page=module&name=hostel" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Total Hostel Rooms</span>
                <span class="metric-menu">🛏️</span>
            </div>
            <div class="metric-value"><?= count($rooms ?? []) ?></div>
            <div class="metric-sub">Hostel Blocks & Rooms</div>
        </a>
        <a href="index.php?page=module&name=hostel" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Active Allocations</span>
                <span class="metric-menu">🔑</span>
            </div>
            <div class="metric-value"><?= count($allocations ?? []) ?></div>
            <div class="metric-sub">Boarding Students</div>
        </a>
        <a href="index.php?page=module&name=transport" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Day Boarding</span>
                <span class="metric-menu">💳</span>
            </div>
            <div class="metric-value">48</div>
            <div class="metric-sub">Meal Pass Subscriptions</div>
        </a>
    </section>

    <!-- Transport Routes Summary -->
    <section class="schedule-card" style="margin-bottom: 24px;">
        <div class="schedule-header">
            <div>
                <h3 style="font-size: 16px; font-weight: 700;">Transport Routes & Drivers</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Institutional fleet tracking & fare schedules</span>
            </div>
            <a href="index.php?page=module&name=transport" class="btn btn-secondary" style="font-size: 12px;">Manage Transport</a>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; text-align: left; font-size: 12px; color: #64748B;">
                        <th style="padding: 10px;">Route Name</th>
                        <th style="padding: 10px;">Vehicle No</th>
                        <th style="padding: 10px;">Driver Name</th>
                        <th style="padding: 10px;">Contact Phone</th>
                        <th style="padding: 10px;">Term Fare</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routes)): ?>
                        <?php foreach (array_slice($routes, 0, 5) as $rt): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($rt['route_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($rt['vehicle_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($rt['driver_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($rt['driver_phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px; font-weight: 700; color: #0284C7;">$<?= number_format((float)($rt['fare'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 16px; text-align: center; color: var(--text-muted);">No transport routes configured.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
