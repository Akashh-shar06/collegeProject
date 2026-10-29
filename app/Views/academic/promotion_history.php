<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=student_promotion" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Student Promotion Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">📜</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Student Promotion Audit History Register</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Audit Trail</span>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
        <form method="GET" action="index.php" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="page" value="promotion_history">
            
            <div style="flex:1; min-width:200px;">
                <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search student name or unique ID..." style="width:100%; padding:9px 14px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
            </div>

            <div style="width:180px;">
                <select name="session" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="all">All Sessions</option>
                    <?php foreach ($sessions as $s): ?>
                        <option value="<?= $s['name'] ?>" <?= $sessionFilter === $s['name'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding:9px 18px; font-size:13px; font-weight:700;">Filter</button>
            <?php if (!empty($search) || $sessionFilter !== 'all'): ?>
                <a href="index.php?page=promotion_history" class="btn btn-secondary" style="padding:9px 14px; font-size:13px; text-decoration:none; background:#E2E8F0; color:#334155;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- HISTORY TABLE -->
    <div class="chart-card">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569;">
                        <th style="padding:12px;">#ID</th>
                        <th style="padding:12px;">STUDENT ID</th>
                        <th style="padding:12px;">STUDENT NAME</th>
                        <th style="padding:12px;">SOURCE LEVEL & SESSION</th>
                        <th style="padding:12px;">TARGET LEVEL & SESSION</th>
                        <th style="padding:12px;">OPERATOR</th>
                        <th style="padding:12px;">TIMESTAMP</th>
                        <th style="padding:12px;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $h): ?>
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:12px; font-weight:700;">#<?= $h['id'] ?></td>
                                <td style="padding:12px; font-weight:800; color:#1E293B;">
                                    <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-family:monospace;"><?= htmlspecialchars($h['unique_id'] ?: $h['admission_no'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="padding:12px;">
                                    <strong style="color:#0F172A; font-size:14px;"><?= htmlspecialchars(trim("{$h['first_name']} {$h['last_name']}"), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <div style="font-size:11px; color:#64748B;"><?= htmlspecialchars($h['course_name'] ?? 'B.Sc. Computer Science', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <div style="font-weight:700; color:#475569;"><?= htmlspecialchars($h['from_academic_level'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:#64748B;"><?= htmlspecialchars($h['from_academic_session'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <div style="font-weight:800; color:#059669;"><?= htmlspecialchars($h['to_academic_level'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:#16A34A;"><?= htmlspecialchars($h['to_academic_session'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px; font-weight:600; color:#0F172A;">
                                    <?= htmlspecialchars($h['operator_name'] ?? 'System Operator', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px; font-size:12px; color:#64748B;">
                                    <?= date('M d, Y h:i A', strtotime($h['promoted_at'])) ?>
                                </td>
                                <td style="padding:12px;">
                                    <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px;">SUCCESS</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="padding:40px; text-align:center; color:#64748B;">No promotion history records logged yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
