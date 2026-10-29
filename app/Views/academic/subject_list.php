<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner Card -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:20px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:24px; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; background:#EFF6FF; border-radius:8px; flex-shrink:0;">📘</span>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; line-height:1.2;">Subject & Curriculum Offering Management</h1>
                <span style="background:#DBEAFE; color:#1E40AF; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">Academic Subjects</span>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('offeringModal').style.display='flex'" style="font-size:13px; font-weight:700;">🔗 + Assign Subject Offering</button>
            <button type="button" class="btn btn-primary" onclick="openSubjectModal()" style="font-size:13px; font-weight:700; padding:10px 20px; background:linear-gradient(135deg, #4F46E5 0%, #2563EB 100%); border:none; color:#FFF; border-radius:8px; cursor:pointer;">
                + Add New Subject
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

    <!-- DEPENDENT FILTER BAR -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
        <form method="GET" action="index.php" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="page" value="subjects">
            
            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:3px;">Search Subject</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Subject name or code..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
            </div>

            <div style="width:180px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:3px;">Course</label>
                <select name="course_id" id="filter_course_id" onchange="filterDependentDepts(this.value)" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['course_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width:180px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:3px;">Department</label>
                <select name="department_id" id="filter_department_id" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" data-course-id="<?= $d['course_id'] ?>" <?= $filters['department_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width:160px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#64748B; margin-bottom:3px;">Academic Level</label>
                <select name="academic_level_id" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;">
                    <option value="0">All Levels</option>
                    <?php foreach ($levels as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $filters['academic_level_id'] === (int)$l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; align-items:flex-end; gap:8px; margin-top:18px;">
                <button type="submit" class="btn btn-primary" style="padding:8px 16px; font-size:13px; font-weight:700;">Filter</button>
                <a href="index.php?page=subjects" class="btn btn-secondary" style="padding:8px 12px; font-size:13px; text-decoration:none; background:#E2E8F0; color:#334155;">Reset</a>
            </div>
        </form>
    </div>

    <!-- SUBJECTS TABLE -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569;">
                        <th style="padding:12px;">CODE</th>
                        <th style="padding:12px;">SUBJECT NAME</th>
                        <th style="padding:12px;">COURSE & DEPT</th>
                        <th style="padding:12px;">LEVEL & SEMESTER</th>
                        <th style="padding:12px;">TYPE & CREDITS</th>
                        <th style="padding:12px;">MARKS (MAX / PASS)</th>
                        <th style="padding:12px;">STATUS</th>
                        <th style="padding:12px; text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $s): ?>
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:12px; font-weight:800; color:#1E293B;">
                                    <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-family:monospace;"><?= htmlspecialchars($s['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="padding:12px;">
                                    <strong style="color:#0F172A; font-size:14px;"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <div style="font-weight:700; color:#2563EB;"><?= htmlspecialchars($s['course_name'] ?? 'General Course', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:#64748B;"><?= htmlspecialchars($s['department_name'] ?? 'General Dept', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px; font-size:12px;">
                                    <div style="font-weight:700; color:#4F46E5;"><?= htmlspecialchars($s['level_name'] ?? 'All Levels', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:#64748B;"><?= htmlspecialchars($s['semester_name'] ?? 'Semester 1', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px;">
                                    <span style="background:#F1F5F9; color:#334155; font-weight:700; padding:2px 8px; border-radius:4px; font-size:11px; text-transform:uppercase;"><?= htmlspecialchars($s['type'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span style="font-weight:700; color:#2563EB; margin-left:4px;"><?= (int)$s['credits'] ?> Credits</span>
                                </td>
                                <td style="padding:12px; font-size:12px; font-weight:700;">
                                    <?= (int)$s['max_marks'] ?> / <span style="color:#059669;"><?= (int)$s['passing_marks'] ?> Pass</span>
                                </td>
                                <td style="padding:12px;">
                                    <?php if (strtolower($s['status']) === 'active'): ?>
                                        <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px;">ACTIVE</span>
                                    <?php else: ?>
                                        <span style="background:#F3F4F6; color:#4B5563; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px;">INACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:6px;">
                                        <button type="button" class="btn btn-secondary" onclick='editSubject(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="font-size:11px; padding:4px 8px;">✏️ Edit</button>
                                        <form method="POST" action="index.php?page=subject_delete" onsubmit="return confirm('Delete or archive this subject?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:4px 8px; color:#DC2626;">🗑️ Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="padding:40px; text-align:center; color:#64748B;">No subjects configured yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- SUBJECT MODAL -->
<div class="modal-overlay" id="subjectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:620px; width:92%; background:#FFF; border-radius:12px; overflow:hidden;">
        <div style="background:#0F172A; padding:16px 24px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0;" id="modalSubTitle">📘 Add New Academic Subject</h3>
            <button type="button" onclick="closeSubjectModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=subject_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="id" id="sub_id" value="0">
            
            <div style="padding:20px; max-height:70vh; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Subject Name *</label>
                        <input type="text" name="name" id="sub_name" required placeholder="e.g. Python Programming, Database Management" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-weight:600;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Subject Code *</label>
                        <input type="text" name="code" id="sub_code" required placeholder="e.g. CS101, IT202" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; text-transform:uppercase;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Course Program</label>
                        <select name="course_id" id="sub_course_id" onchange="syncModalDepts(this.value)" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="0">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Department</label>
                        <select name="department_id" id="sub_department_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="0">-- Select Department --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" data-course-id="<?= $d['course_id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Academic Level / Year</label>
                        <select name="academic_level_id" id="sub_level_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="0">-- Select Academic Level --</option>
                            <?php foreach ($levels as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Semester</label>
                        <select name="semester_id" id="sub_semester_id" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="0">-- Select Semester --</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>"><?= htmlspecialchars($sem['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Type</label>
                        <select name="type" id="sub_type" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="core">Core</option>
                            <option value="elective">Elective</option>
                            <option value="theory">Theory</option>
                            <option value="practical">Practical</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Credits</label>
                        <input type="number" name="credits" id="sub_credits" value="3" min="1" max="10" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Max Marks</label>
                        <input type="number" name="max_marks" id="sub_max" value="100" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Pass Marks</label>
                        <input type="number" name="passing_marks" id="sub_pass" value="40" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>
            </div>

            <div style="background:#F8FAFC; padding:14px 20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeSubjectModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#4F46E5;">Save Subject</button>
            </div>
        </form>
    </div>
</div>

<!-- OFFERING MODAL -->
<div class="modal-overlay" id="offeringModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:500px; width:92%; background:#FFF; border-radius:12px; overflow:hidden;">
        <div style="background:#0F172A; padding:16px 20px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h4 style="margin:0; font-size:16px; font-weight:800;">🔗 Assign Subject Offering</h4>
            <button type="button" onclick="document.getElementById('offeringModal').style.display='none'" style="background:none; border:none; color:#FFF; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=subject_offering_save" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Select Subject *</label>
                <select name="subject_id" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1;">
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name'] . ' (' . $sub['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Target Course Program</label>
                <select name="course_id" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1;">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Target Academic Level / Year</label>
                <select name="academic_level_id" style="width:100%; padding:9px; border-radius:6px; border:1px solid #CBD5E1;">
                    <option value="0">All Levels</option>
                    <?php foreach ($levels as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                <button type="button" onclick="document.getElementById('offeringModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#4F46E5;">Save Offering</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubjectModal() {
    document.getElementById('sub_id').value = '0';
    document.getElementById('sub_name').value = '';
    document.getElementById('sub_code').value = '';
    document.getElementById('sub_course_id').value = '0';
    document.getElementById('sub_department_id').value = '0';
    document.getElementById('sub_level_id').value = '0';
    document.getElementById('sub_semester_id').value = '0';
    document.getElementById('sub_type').value = 'core';
    document.getElementById('sub_credits').value = '3';
    document.getElementById('sub_max').value = '100';
    document.getElementById('sub_pass').value = '40';
    document.getElementById('modalSubTitle').innerText = '📘 Add New Academic Subject';
    document.getElementById('subjectModal').style.display = 'flex';
}

function editSubject(s) {
    document.getElementById('sub_id').value = s.id;
    document.getElementById('sub_name').value = s.name || '';
    document.getElementById('sub_code').value = s.code || '';
    document.getElementById('sub_course_id').value = s.course_id || '0';
    document.getElementById('sub_department_id').value = s.department_id || '0';
    document.getElementById('sub_level_id').value = s.academic_level_id || '0';
    document.getElementById('sub_semester_id').value = s.semester_id || '0';
    document.getElementById('sub_type').value = s.type || 'core';
    document.getElementById('sub_credits').value = s.credits || '3';
    document.getElementById('sub_max').value = s.max_marks || '100';
    document.getElementById('sub_pass').value = s.passing_marks || '40';
    document.getElementById('modalSubTitle').innerText = '✏️ Edit Subject: ' + s.code;
    document.getElementById('subjectModal').style.display = 'flex';
}

function closeSubjectModal() {
    document.getElementById('subjectModal').style.display = 'none';
}

function filterDependentDepts(courseId) {
    const deptSelect = document.getElementById('filter_department_id');
    const options = deptSelect.querySelectorAll('option');
    options.forEach(opt => {
        if (opt.value === '0') return;
        const cId = opt.getAttribute('data-course-id');
        if (courseId === '0' || cId === courseId || !cId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
