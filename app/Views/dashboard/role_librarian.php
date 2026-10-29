<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:linear-gradient(135deg, #8B5CF6, #7C3AED); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h2 style="font-size:22px; font-weight:800;">Librarian Catalogue & Circulation Portal</h2>
            <p style="font-size:13px; opacity:0.9;">Book catalogue management, student circulation issues, returns & overdue tracking</p>
        </div>
        <a href="index.php?page=module&name=library" class="btn btn-secondary" style="background:#FFFFFF; color:#7C3AED; font-weight:700; text-decoration:none;">+ Add Book to Catalogue</a>
    </div>

    <!-- Circulation & Catalogue Metrics -->
    <section class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="index.php?page=module&name=library" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Total Books in Catalogue</span></div>
            <div class="metric-value"><?= count($books ?? []) ?> Titles</div>
            <div class="metric-sub">Library Inventory</div>
        </a>
        <a href="index.php?page=module&name=library" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Active Book Loans</span></div>
            <div class="metric-value" style="color:var(--primary);"><?= count($issues ?? []) ?> Issued</div>
            <div class="metric-sub">Student Loan Register</div>
        </a>
        <a href="index.php?page=module&name=library" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Overdue Loans</span></div>
            <div class="metric-value" style="color:var(--success);">0 Overdue</div>
            <div class="metric-sub">Clean Overdue Status</div>
        </a>
    </section>

    <!-- Active Book Loans & Issues Table -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Active Book Circulation Loans</h3>
            <a href="index.php?page=module&name=library" class="btn btn-primary" style="text-decoration:none;">+ Issue Book to Student</a>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">BOOK TITLE</th>
                        <th style="padding:12px;">STUDENT</th>
                        <th style="padding:12px;">ISSUE DATE</th>
                        <th style="padding:12px;">DUE DATE</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($issues)): ?>
                        <?php foreach ($issues as $iss): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($iss['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars(($iss['first_name'] ?? '') . ' ' . ($iss['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:12px;"><?= $iss['issue_date'] ?></td>
                            <td style="padding:12px;"><?= $iss['return_due_date'] ?></td>
                            <td style="padding:12px;"><span style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:var(--radius-pill); font-weight:700; font-size:11px; text-transform:uppercase;"><?= $iss['status'] ?></span></td>
                            <td style="padding:12px; text-align:right;">
                                <?php if ($iss['status'] !== 'returned'): ?>
                                    <form action="index.php?page=return_book" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                        <input type="hidden" name="issue_id" value="<?= $iss['id'] ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:11px;">Process Return</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:11px; color:var(--success); font-weight:700;">Returned ✅</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:16px; text-align:center; color:var(--text-muted);">No active book circulation loans found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>

</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
