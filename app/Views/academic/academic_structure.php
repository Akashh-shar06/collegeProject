<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">📆</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Academic Year & Session Structure Management</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Institutional Progression</span>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div style="background:#DCFCE7; color:#166534; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #86EFAC;">
            ✅ <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div style="background:#FEE2E2; color:#991B1B; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #FCA5A5;">
            ⚠️ <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:24px;">
        <!-- 1. ACADEMIC SESSIONS -->
        <div class="chart-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">📅 Academic Sessions</h3>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('sessionModal').style.display='flex'" style="font-size:12px; font-weight:700; padding:6px 14px;">+ Add Session</button>
            </div>
            
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1px solid #CBD5E1; color:#475569;">
                            <th style="padding:10px;">SESSION</th>
                            <th style="padding:10px;">DATES</th>
                            <th style="padding:10px;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sessions)): ?>
                            <?php foreach ($sessions as $s): ?>
                                <tr style="border-bottom:1px solid #E2E8F0;">
                                    <td style="padding:10px; font-weight:800; color:#0F172A;"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="padding:10px; font-size:12px; color:#475569;">
                                        <?= $s['start_date'] ? date('M Y', strtotime($s['start_date'])) : 'N/A' ?> - 
                                        <?= $s['end_date'] ? date('M Y', strtotime($s['end_date'])) : 'N/A' ?>
                                    </td>
                                    <td style="padding:10px;">
                                        <?php if ((int)$s['is_current'] === 1): ?>
                                            <span style="background:#DCFCE7; color:#166534; font-size:10px; font-weight:800; padding:3px 8px; border-radius:12px;">CURRENT ACTIVE</span>
                                        <?php else: ?>
                                            <span style="background:#F1F5F9; color:#64748B; font-size:10px; font-weight:700; padding:3px 8px; border-radius:12px;">INACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#64748B;">No academic sessions created.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. ACADEMIC LEVELS (FY, SY, TY, 4Y) -->
        <div class="chart-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">🎓 Academic Levels / Progression Standards</h3>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('levelModal').style.display='flex'" style="font-size:12px; font-weight:700; padding:6px 14px;">+ Add Level</button>
            </div>
            
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1px solid #CBD5E1; color:#475569;">
                            <th style="padding:10px;">ORDER</th>
                            <th style="padding:10px;">LEVEL NAME</th>
                            <th style="padding:10px;">COURSE SCOPE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($levels)): ?>
                            <?php foreach ($levels as $lvl): ?>
                                <tr style="border-bottom:1px solid #E2E8F0;">
                                    <td style="padding:10px; font-weight:800; color:#4F46E5;">Year <?= (int)$lvl['level_order'] ?></td>
                                    <td style="padding:10px; font-weight:700; color:#0F172A;"><?= htmlspecialchars($lvl['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="padding:10px; font-size:12px; color:#475569;"><?= htmlspecialchars($lvl['course_name'] ?? 'Universal / All Courses', ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#64748B;">No academic levels configured.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- SESSION MODAL -->
<div class="modal-overlay" id="sessionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:500px; width:92%; background:#FFF; border-radius:12px; overflow:hidden;">
        <div style="background:#0F172A; padding:16px 20px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h4 style="margin:0; font-size:16px; font-weight:800;">📅 Add Academic Session</h4>
            <button type="button" onclick="document.getElementById('sessionModal').style.display='none'" style="background:none; border:none; color:#FFF; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=academic_session_save" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Session Name *</label>
                <input type="text" name="name" required placeholder="e.g. 2026-2027" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Start Date</label>
                    <input type="date" name="start_date" value="2026-06-01" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">End Date</label>
                    <input type="date" name="end_date" value="2027-04-30" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_current" id="is_current" value="1" checked style="width:16px; height:16px;">
                <label for="is_current" style="font-size:13px; font-weight:700; color:#0F172A;">Set as Active Academic Session</label>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                <button type="button" onclick="document.getElementById('sessionModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#4F46E5;">Save Session</button>
            </div>
        </form>
    </div>
</div>

<!-- LEVEL MODAL -->
<div class="modal-overlay" id="levelModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:500px; width:92%; background:#FFF; border-radius:12px; overflow:hidden;">
        <div style="background:#0F172A; padding:16px 20px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h4 style="margin:0; font-size:16px; font-weight:800;">🎓 Add Academic Level / Year</h4>
            <button type="button" onclick="document.getElementById('levelModal').style.display='none'" style="background:none; border:none; color:#FFF; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=academic_level_save" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Academic Level Name *</label>
                <input type="text" name="name" required placeholder="e.g. First Year (FY), Second Year (SY)..." style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Level Order *</label>
                    <input type="number" name="level_order" value="1" min="1" max="10" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Course Scope</label>
                    <select name="course_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                        <option value="0">Universal / All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                <button type="button" onclick="document.getElementById('levelModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#4F46E5;">Save Level</button>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
