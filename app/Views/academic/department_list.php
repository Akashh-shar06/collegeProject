<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">🏛️</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Academic Department Management</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Institutional Structure</span>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openDeptModal()" style="font-size:13px; font-weight:700; padding:10px 20px; background:linear-gradient(135deg, #4F46E5 0%, #2563EB 100%); border:none; color:#FFF; border-radius:8px; cursor:pointer;">
                + Create New Department
            </button>
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
    <?php if ($flashWarn = \App\Core\Session::getFlash('warning')): ?>
        <div style="background:#FEF3C7; color:#92400E; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #FDE68A;">
            💡 <?= htmlspecialchars($flashWarn, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- SUMMARY METRICS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Total Departments</div>
            <div style="font-size:26px; font-weight:800; color:#2563EB; margin-top:4px;"><?= count($departments) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Institutional Academic Units</div>
        </div>
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Active Departments</div>
            <div style="font-size:26px; font-weight:800; color:#059669; margin-top:4px;"><?= count(array_filter($departments, fn($d) => strtolower($d['status']) === 'active')) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Operational Units</div>
        </div>
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Parent Courses</div>
            <div style="font-size:26px; font-weight:800; color:#7C3AED; margin-top:4px;"><?= count($courses) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Degree Programs</div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
        <form method="GET" action="index.php" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="page" value="departments">
            
            <div style="flex:1; min-width:200px;">
                <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search department name or code..." style="width:100%; padding:9px 14px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
            </div>
            
            <div style="width:200px;">
                <select name="course_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Parent Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseFilter === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name'] . ' (' . $c['code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width:140px;">
                <select name="status" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="all">All Statuses</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding:9px 18px; font-size:13px; font-weight:700;">Apply Filters</button>
            <?php if (!empty($search) || $courseFilter > 0 || $statusFilter !== 'all'): ?>
                <a href="index.php?page=departments" class="btn btn-secondary" style="padding:9px 14px; font-size:13px; text-decoration:none; background:#E2E8F0; color:#334155;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- DEPARTMENTS TABLE -->
    <div class="chart-card">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569;">
                        <th style="padding:12px;">CODE</th>
                        <th style="padding:12px;">DEPARTMENT NAME</th>
                        <th style="padding:12px;">PARENT COURSE</th>
                        <th style="padding:12px;">ASSOCIATED RECORDS</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $d): ?>
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:12px; font-weight:800; color:#1E293B;">
                                    <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-family:monospace;"><?= htmlspecialchars($d['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="padding:12px;">
                                    <strong style="color:#0F172A; font-size:14px; display:block;"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($d['description'])): ?>
                                        <span style="font-size:11px; color:#64748B;"><?= htmlspecialchars($d['description'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; font-weight:600; color:#4F46E5;">
                                    <?= htmlspecialchars($d['parent_course_name'] ? ($d['parent_course_name'] . ' (' . $d['parent_course_code'] . ')') : 'General / Multi-Course', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <span style="background:#F1F5F9; padding:2px 8px; border-radius:4px; margin-right:4px;">👥 <?= (int)$d['student_count'] ?> Students</span>
                                    <span style="background:#F1F5F9; padding:2px 8px; border-radius:4px; margin-right:4px;">📖 <?= (int)$d['subject_count'] ?> Subjects</span>
                                    <span style="background:#F1F5F9; padding:2px 8px; border-radius:4px;">👨‍🏫 <?= (int)$d['staff_count'] ?> Faculty</span>
                                </td>
                                <td style="padding:12px;">
                                    <?php if (strtolower($d['status']) === 'active'): ?>
                                        <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px; text-transform:uppercase;">ACTIVE</span>
                                    <?php else: ?>
                                        <span style="background:#F3F4F6; color:#4B5563; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px; text-transform:uppercase;">INACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:8px;">
                                        <button type="button" class="btn btn-secondary" onclick='editDept(<?= json_encode($d, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="font-size:11px; padding:4px 10px; background:#EEF2FF; color:#4338CA; border:1px solid #C7D2FE;">✏️ Edit</button>
                                        
                                        <form method="POST" action="index.php?page=department_delete" onsubmit="return confirm('Are you sure you want to delete/archive this department?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:4px 10px; background:#FEF2F2; color:#991B1B; border:1px solid #FCA5A5;">🗑️ Delete / Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding:40px; text-align:center; color:#64748B;">
                                📭 No departments found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- DEPARTMENT MODAL -->
<div class="modal-overlay" id="deptModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:560px; width:92%; background:#FFF; border-radius:12px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); padding:16px 24px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0;" id="modalDeptTitle">🏢 Add New Department</h3>
            <button type="button" onclick="closeDeptModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=department_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="id" id="dept_id" value="0">
            
            <div style="padding:20px; max-height:70vh; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Name *</label>
                    <input type="text" name="name" id="dept_name" required placeholder="e.g. Computer Science & Engineering" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Code *</label>
                        <input type="text" name="code" id="dept_code" required placeholder="e.g. CS, IT, COM" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Parent Course Program</label>
                        <select name="course_id" id="dept_course_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                            <option value="0">-- Multi-Course / Independent --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] . ' (' . $c['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status</label>
                    <select name="status" id="dept_status" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Description / Department Notes</label>
                    <textarea name="description" id="dept_description" rows="3" placeholder="Overview of faculty and academic scope..." style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;"></textarea>
                </div>
            </div>

            <div style="background:#F8FAFC; padding:14px 20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeDeptModal()" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px; background:#4F46E5; border:none; font-weight:700;">Save Department</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeptModal() {
    document.getElementById('dept_id').value = '0';
    document.getElementById('dept_name').value = '';
    document.getElementById('dept_code').value = '';
    document.getElementById('dept_course_id').value = '0';
    document.getElementById('dept_status').value = 'active';
    document.getElementById('dept_description').value = '';
    document.getElementById('modalDeptTitle').innerText = '🏢 Add New Department';
    
    document.getElementById('deptModal').style.display = 'flex';
}

function editDept(d) {
    document.getElementById('dept_id').value = d.id;
    document.getElementById('dept_name').value = d.name || '';
    document.getElementById('dept_code').value = d.code || '';
    document.getElementById('dept_course_id').value = d.course_id || '0';
    document.getElementById('dept_status').value = d.status || 'active';
    document.getElementById('dept_description').value = d.description || '';
    document.getElementById('modalDeptTitle').innerText = '✏️ Edit Department: ' + d.code;
    
    document.getElementById('deptModal').style.display = 'flex';
}

function closeDeptModal() {
    document.getElementById('deptModal').style.display = 'none';
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
