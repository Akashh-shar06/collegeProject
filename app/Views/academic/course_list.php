<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">🎓</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Course & Degree Program Management</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Institutional Curriculum</span>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openCourseModal()" style="font-size:13px; font-weight:700; padding:10px 20px; background:linear-gradient(135deg, #4F46E5 0%, #2563EB 100%); border:none; color:#FFF; border-radius:8px; cursor:pointer;">
                + Add New Course
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
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Total Courses Offered</div>
            <div style="font-size:26px; font-weight:800; color:#2563EB; margin-top:4px;"><?= count($courses) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Configured Programs</div>
        </div>
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Active Degree Programs</div>
            <div style="font-size:26px; font-weight:800; color:#059669; margin-top:4px;"><?= count(array_filter($courses, fn($c) => strtolower($c['status']) === 'active')) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Available for Admissions</div>
        </div>
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Archived / Inactive</div>
            <div style="font-size:26px; font-weight:800; color:#D97706; margin-top:4px;"><?= count(array_filter($courses, fn($c) => strtolower($c['status']) !== 'active')) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Historical Records</div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
        <form method="GET" action="index.php" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="page" value="courses">
            
            <div style="flex:1; min-width:220px;">
                <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by course name or code..." style="width:100%; padding:9px 14px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
            </div>
            <div style="width:160px;">
                <select name="status" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="all">All Statuses</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:9px 18px; font-size:13px; font-weight:700;">Filter</button>
            <?php if (!empty($search) || $statusFilter !== 'all'): ?>
                <a href="index.php?page=courses" class="btn btn-secondary" style="padding:9px 14px; font-size:13px; text-decoration:none; background:#E2E8F0; color:#334155;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- COURSES TABLE -->
    <div class="chart-card">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569;">
                        <th style="padding:12px;">CODE</th>
                        <th style="padding:12px;">COURSE NAME</th>
                        <th style="padding:12px;">DURATION</th>
                        <th style="padding:12px;">LINKED DEPT</th>
                        <th style="padding:12px;">ASSOCIATED STATS</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $c): ?>
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:12px; font-weight:800; color:#1E293B;">
                                    <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-family:monospace;"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="padding:12px;">
                                    <strong style="color:#0F172A; font-size:14px; display:block;"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($c['description'])): ?>
                                        <span style="font-size:11px; color:#64748B;"><?= htmlspecialchars($c['description'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; font-weight:700; color:#4F46E5;">
                                    <?= (int)$c['duration_years'] ?> Academic Year(s)
                                </td>
                                <td style="padding:12px; color:#334155;">
                                    <?= htmlspecialchars($c['department_name'] ?? 'General / All Depts', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <span style="background:#F1F5F9; padding:2px 8px; border-radius:4px; margin-right:4px;">👥 <?= (int)$c['student_count'] ?> Students</span>
                                    <span style="background:#F1F5F9; padding:2px 8px; border-radius:4px;">📖 <?= (int)$c['subject_count'] ?> Subjects</span>
                                </td>
                                <td style="padding:12px;">
                                    <?php if (strtolower($c['status']) === 'active'): ?>
                                        <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px; text-transform:uppercase;">ACTIVE</span>
                                    <?php else: ?>
                                        <span style="background:#F3F4F6; color:#4B5563; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px; text-transform:uppercase;">INACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:8px;">
                                        <button type="button" class="btn btn-secondary" onclick='editCourse(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="font-size:11px; padding:4px 10px; background:#EEF2FF; color:#4338CA; border:1px solid #C7D2FE;">✏️ Edit</button>
                                        
                                        <form method="POST" action="index.php?page=course_delete" onsubmit="return confirm('Are you sure you want to delete/archive this course? Dependent records will trigger safety deactivation.');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:4px 10px; background:#FEF2F2; color:#991B1B; border:1px solid #FCA5A5;">🗑️ Delete / Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding:40px; text-align:center; color:#64748B;">
                                📭 No courses found matching the search criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- COURSE MODAL -->
<div class="modal-overlay" id="courseModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:560px; width:92%; background:#FFF; border-radius:12px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); padding:16px 24px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0;" id="modalCourseTitle">➕ Add New Course / Degree Program</h3>
            <button type="button" onclick="closeCourseModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=course_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="id" id="course_id" value="0">
            
            <div style="padding:20px; max-height:70vh; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course Name *</label>
                    <input type="text" name="name" id="course_name" required placeholder="e.g. B.Sc. Computer Science" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course Code *</label>
                        <input type="text" name="code" id="course_code" required placeholder="e.g. CS01, IT01" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Duration (Academic Years) *</label>
                        <select name="duration_years" id="course_duration" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                            <option value="1">1 Year (Diploma / Certificate)</option>
                            <option value="2">2 Years (Master's / PG)</option>
                            <option value="3" selected>3 Years (Undergraduate / Bachelor)</option>
                            <option value="4">4 Years (Engineering / Honors)</option>
                            <option value="5">5 Years (Integrated Master's / Law)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Associated Primary Department</label>
                    <select name="department_id" id="course_department_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                        <option value="0">-- General / Shared Across Departments --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . $d['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status</label>
                    <select name="status" id="course_status" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Description & Curriculum Notes</label>
                    <textarea name="description" id="course_description" rows="3" placeholder="Brief outline of course requirements..." style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;"></textarea>
                </div>
            </div>

            <div style="background:#F8FAFC; padding:14px 20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeCourseModal()" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px; background:#4F46E5; border:none; font-weight:700;">Save Course</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCourseModal() {
    document.getElementById('course_id').value = '0';
    document.getElementById('course_name').value = '';
    document.getElementById('course_code').value = '';
    document.getElementById('course_duration').value = '3';
    document.getElementById('course_department_id').value = '0';
    document.getElementById('course_status').value = 'active';
    document.getElementById('course_description').value = '';
    document.getElementById('modalCourseTitle').innerText = '➕ Add New Course / Degree Program';
    
    const m = document.getElementById('courseModal');
    m.style.display = 'flex';
}

function editCourse(c) {
    document.getElementById('course_id').value = c.id;
    document.getElementById('course_name').value = c.name || '';
    document.getElementById('course_code').value = c.code || '';
    document.getElementById('course_duration').value = c.duration_years || '3';
    document.getElementById('course_department_id').value = c.department_id || '0';
    document.getElementById('course_status').value = c.status || 'active';
    document.getElementById('course_description').value = c.description || '';
    document.getElementById('modalCourseTitle').innerText = '✏️ Edit Course Record: ' + c.code;
    
    const m = document.getElementById('courseModal');
    m.style.display = 'flex';
}

function closeCourseModal() {
    document.getElementById('courseModal').style.display = 'none';
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
