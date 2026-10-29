<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div class="dashboard-banner" style="background: linear-gradient(135deg, #7C3AED, #5B21B6); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #DDD6FE; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Inventory & Asset Control</span>
                <h1 style="font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">Welcome, <?= htmlspecialchars($user['username'] ?? 'Inventory Manager', ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="color: #DDD6FE; font-size: 13px; margin: 0;">Track office stationery, lab equipment, IT assets, damaged items, stock movements, and asset allocations.</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #DDD6FE;">Job Role</span>
                <div style="font-weight: 700; color: #F5F3FF; font-size: 14px;">Store & Asset Manager</div>
            </div>
        </div>
    </div>

    <!-- Inventory & Asset Metrics Row -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=inventory" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Total Registered Assets</span>
                <span class="metric-menu">🏛️</span>
            </div>
            <div class="metric-value"><?= count($assets ?? []) ?></div>
            <div class="metric-sub">Campus Property Inventory</div>
        </a>
        <a href="index.php?page=module&name=inventory" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Stock Items</span>
                <span class="metric-menu">📄</span>
            </div>
            <div class="metric-value"><?= count($stockItems ?? []) ?></div>
            <div class="metric-sub">Stationery & Consumables</div>
        </a>
        <a href="index.php?page=module&name=inventory" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Low Stock Items</span>
                <span class="metric-menu">⚠️</span>
            </div>
            <div class="metric-value">4</div>
            <div class="metric-sub">Reorder Threshold</div>
        </a>
        <a href="index.php?page=module&name=inventory" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header">
                <span class="metric-title">Damaged / Disposed</span>
                <span class="metric-menu">🧰</span>
            </div>
            <div class="metric-value">2</div>
            <div class="metric-sub">Reported Issues</div>
        </a>
    </section>

    <!-- Asset Inventory Table Summary -->
    <section class="schedule-card" style="margin-bottom: 24px;">
        <div class="schedule-header">
            <div>
                <h3 style="font-size: 16px; font-weight: 700;">Campus Assets & Equipment Summary</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Stock valuation & unit tracking</span>
            </div>
            <a href="index.php?page=module&name=asset_management" class="btn btn-secondary" style="font-size: 12px;">Asset Management</a>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; text-align: left; font-size: 12px; color: #64748B;">
                        <th style="padding: 10px;">Item Name</th>
                        <th style="padding: 10px;">Category</th>
                        <th style="padding: 10px;">Quantity</th>
                        <th style="padding: 10px;">Unit Price</th>
                        <th style="padding: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assets)): ?>
                        <?php foreach (array_slice($assets, 0, 5) as $ast): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($ast['item_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= strtoupper(htmlspecialchars($ast['category'] ?? 'Asset', ENT_QUOTES, 'UTF-8')) ?></td>
                                <td style="padding: 10px; font-weight: 700;"><?= number_format((int)($ast['quantity'] ?? 1)) ?></td>
                                <td style="padding: 10px; color: #7C3AED; font-weight: 700;">$<?= number_format((float)($ast['unit_price'] ?? 0), 2) ?></td>
                                <td style="padding: 10px;">
                                    <span class="badge" style="background: #DCFCE7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;"><?= strtoupper($ast['status'] ?? 'IN_STOCK') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 16px; text-align: center; color: var(--text-muted);">No inventory assets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
