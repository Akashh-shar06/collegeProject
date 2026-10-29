<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">🚀</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Student Academic Promotion & Progression Management</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Bulk Year Progression</span>
            </div>
        </div>
        <div>
            <a href="index.php?page=promotion_history" class="btn btn-secondary" style="font-size:13px; font-weight:700; padding:10px 18px; text-decoration:none;">📜 View Promotion Audit History</a>
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

    <!-- STEP 1 & 2: SOURCE ACADEMIC SELECTION FILTER -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:20px; margin-bottom:24px;">
        <h3 style="font-size:14px; font-weight:800; color:#334155; margin-bottom:14px; text-transform:uppercase; letter-spacing:0.5px;">
            1️⃣ Select Current Academic Cohort to Promote
        </h3>
        <form method="GET" action="index.php" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; align-items:end;">
            <input type="hidden" name="page" value="student_promotion">
            
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:4px;">Current Academic Session *</label>
                <select name="source_session" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                    <?php foreach ($sessions as $s): ?>
                        <option value="<?= $s['name'] ?>" <?= $filters['source_session'] === $s['name'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:4px;">Course Program</label>
                <select name="course_id" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['course_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:4px;">Department</label>
                <select name="department_id" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $filters['department_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:4px;">Current Academic Level / Year</label>
                <select name="academic_level_id" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Levels</option>
                    <?php foreach ($levels as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $filters['academic_level_id'] === (int)$l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:9px 16px; font-size:13px; font-weight:700; background:#2563EB;">
                    🔍 Load Eligible Students
                </button>
            </div>
        </form>
    </div>

    <!-- PROMOTION FORM & STUDENTS TABLE -->
    <form method="POST" action="index.php?page=promote_students_process" id="promotionForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
        <input type="hidden" name="source_session" value="<?= htmlspecialchars($filters['source_session'], ENT_QUOTES, 'UTF-8') ?>">

        <!-- TARGET SELECTION HEADER -->
        <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; padding:20px; margin-bottom:24px;">
            <h3 style="font-size:14px; font-weight:800; color:#166534; margin-bottom:14px; text-transform:uppercase; letter-spacing:0.5px;">
                2️⃣ Select Target Next Academic Level & Session
            </h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; align-items:end;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#14532D; margin-bottom:4px;">Target Academic Session *</label>
                    <select name="target_session" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #86EFAC; font-size:13px; font-weight:700; background:#FFF;">
                        <?php foreach ($sessions as $s): ?>
                            <option value="<?= $s['name'] ?>" <?= $filters['target_session'] === $s['name'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#14532D; margin-bottom:4px;">Target Next Academic Level / Year *</label>
                    <select name="target_academic_level_id" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #86EFAC; font-size:13px; font-weight:700; background:#FFF;">
                        <option value="">-- Select Target Next Level --</option>
                        <?php foreach ($levels as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#14532D; margin-bottom:4px;">Operator Remarks / Reason</label>
                    <input type="text" name="remarks" value="Bulk Annual Academic Promotion" style="width:100%; padding:9px; border-radius:6px; border:1px solid #86EFAC; font-size:13px; background:#FFF;">
                </div>

                <div>
                    <button type="button" onclick="confirmPromotion()" class="btn btn-primary" style="width:100%; padding:10px 20px; font-size:13px; font-weight:800; background:linear-gradient(135deg, #16A34A 0%, #059669 100%); border:none; color:#FFF; border-radius:8px; cursor:pointer;">
                        🚀 Execute Bulk Promotion
                    </button>
                </div>
            </div>
        </div>

        <!-- STUDENTS TABLE -->
        <div class="chart-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">
                    👥 Eligible Students Roster (<?= count($students) ?> Found)
                </h3>
                <div>
                    <button type="button" onclick="toggleSelectAll(this)" class="btn btn-secondary" style="font-size:12px; padding:6px 12px;">Select / Deselect All</button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569;">
                            <th style="padding:12px; text-align:center; width:40px;">
                                <input type="checkbox" id="selectAllCheck" onclick="toggleSelectAllCheckboxes(this)" style="width:16px; height:16px;">
                            </th>
                            <th style="padding:12px;">STUDENT ID</th>
                            <th style="padding:12px;">STUDENT NAME</th>
                            <th style="padding:12px;">COURSE & DEPT</th>
                            <th style="padding:12px;">CURRENT LEVEL</th>
                            <th style="padding:12px;">SESSION</th>
                            <th style="padding:12px;">PROMOTION ELIGIBILITY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $st): ?>
                                <tr style="border-bottom:1px solid #F1F5F9;">
                                    <td style="padding:12px; text-align:center;">
                                        <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>" class="st-check" style="width:16px; height:16px;">
                                    </td>
                                    <td style="padding:12px; font-weight:800; color:#1E293B;">
                                        <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-family:monospace;"><?= htmlspecialchars($st['unique_id'] ?: $st['admission_no'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td style="padding:12px;">
                                        <strong style="color:#0F172A; font-size:14px;"><?= htmlspecialchars(trim("{$st['first_name']} {$st['middle_name']} {$st['last_name']}"), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <div style="font-size:11px; color:#64748B;">Roll No: <?= htmlspecialchars($st['roll_no'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td style="padding:12px; font-size:12px;">
                                        <div style="font-weight:700; color:#2563EB;"><?= htmlspecialchars($st['course_name'] ?? 'B.Sc. Computer Science', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div style="color:#64748B;"><?= htmlspecialchars($st['department_name'] ?? 'General Dept', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td style="padding:12px; font-weight:700; color:#4F46E5;">
                                        <?= htmlspecialchars($st['level_name'] ?? 'First Year (FY)', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="padding:12px; font-size:12px; color:#475569;">
                                        <?= htmlspecialchars($st['current_session'] ?: $filters['source_session'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px;">ELIGIBLE FOR PROMOTION</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding:40px; text-align:center; color:#64748B;">
                                    🔍 Select current academic session, course or level above and click "Load Eligible Students".
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</main>

<script>
function toggleSelectAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('.st-check');
    checkboxes.forEach(cb => cb.checked = master.checked);
}

function toggleSelectAll(btn) {
    const master = document.getElementById('selectAllCheck');
    master.checked = !master.checked;
    toggleSelectAllCheckboxes(master);
}

function confirmPromotion() {
    const checked = document.querySelectorAll('.st-check:checked');
    if (checked.length === 0) {
        alert('Please select at least one student from the table using checkboxes.');
        return;
    }
    const targetLvl = document.querySelector('select[name="target_academic_level_id"]');
    if (!targetLvl || !targetLvl.value) {
        alert('Please select a Target Next Academic Level.');
        return;
    }
    
    const count = checked.length;
    if (confirm(`Execute promotion for ${count} selected student(s) in a secure database transaction?`)) {
        document.getElementById('promotionForm').submit();
    }
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
