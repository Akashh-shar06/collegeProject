<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <div style="background:linear-gradient(135deg, #0066FF, #00C8FF); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:22px; font-weight:800;">School Administrator Portal</h2>
            <p style="font-size:13px; opacity:0.9;">Institution configuration, academic structure, staff creation & student admissions</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.php?page=academic_hierarchy" class="btn btn-secondary" style="background:#FFFFFF; color:#2563EB; font-weight:800; font-size:13px; padding:8px 14px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">🏛️ Academic Hierarchy (FY, SY, TY)</a>
            <button type="button" class="btn btn-secondary" onclick="openAddDepartmentModal()" style="background:#FFFFFF; color:var(--primary); font-weight:700; border:none; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px;">🏛️ + Add / Manage Departments</button>
            <a href="index.php?page=module&name=admission" class="btn btn-secondary" style="background:#FFFFFF; color:var(--primary); text-decoration:none; font-weight:700; font-size:13px; padding:8px 14px; border-radius:8px;">+ Admit New Student</a>
            <a href="index.php?page=module&name=staff_data" class="btn btn-secondary" style="background:#FFFFFF; color:var(--text-main); text-decoration:none; font-weight:700; font-size:13px; padding:8px 14px; border-radius:8px;">+ Create Staff Account</a>
        </div>
    </div>

    <!-- Quick Action Launchers -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="index.php?page=module&name=student_mis" class="metric-card" style="text-decoration:none;">
            <div class="metric-header"><span class="metric-title">Enrolled Students</span></div>
            <div class="metric-value"><?= number_format($metrics['students'] ?? 1240) ?></div>
            <div class="metric-sub">Active Student Profiles</div>
        </a>

        <a href="index.php?page=module&name=staff_data" class="metric-card" style="text-decoration:none;">
            <div class="metric-header"><span class="metric-title">Faculty & Staff</span></div>
            <div class="metric-value"><?= number_format($metrics['staff'] ?? 84) ?></div>
            <div class="metric-sub">Teachers & Administrative Staff</div>
        </a>

        <a href="index.php?page=module&name=enquiry" class="metric-card" style="text-decoration:none;">
            <div class="metric-header"><span class="metric-title">Admission Enquiries</span></div>
            <div class="metric-value"><?= number_format($metrics['enquiries'] ?? 324) ?></div>
            <div class="metric-sub">Pending Admission Leads</div>
        </a>

        <a href="index.php?page=module&name=news_events" class="metric-card" style="text-decoration:none;">
            <div class="metric-header"><span class="metric-title">Published Circulars</span></div>
            <div class="metric-value"><?= number_format($metrics['notices'] ?? 102) ?></div>
            <div class="metric-sub">Active Institution Notices</div>
        </a>
    </section>

    <style>
    /* Academic Structure & Cohort Interactive Badges */
    .cohort-pill-btn {
        flex: 1;
        border-radius: 6px;
        padding: 6px 4px;
        text-align: center;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        border: 1px solid transparent;
        text-decoration: none;
        user-select: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        outline: none;
    }
    .cohort-pill-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        filter: brightness(0.96);
    }
    .cohort-pill-btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .cohort-pill-btn.fy-btn {
        background: #DCFCE7;
        color: #166534;
        border-color: #86EFAC;
    }
    .cohort-pill-btn.fy-btn:hover {
        background: #BBF7D0;
        color: #14532D;
        border-color: #4ADE80;
    }
    .cohort-pill-btn.sy-btn {
        background: #DBEAFE;
        color: #1E40AF;
        border-color: #93C5FD;
    }
    .cohort-pill-btn.sy-btn:hover {
        background: #BFDBFE;
        color: #1E3A8A;
        border-color: #60A5FA;
    }
    .cohort-pill-btn.ty-btn {
        background: #F3E8FF;
        color: #6B21A8;
        border-color: #D8B4FE;
    }
    .cohort-pill-btn.ty-btn:hover {
        background: #E9D5FF;
        color: #581C87;
        border-color: #C084FC;
    }
    .course-total-badge-btn {
        background: #EEF2FF;
        color: #4338CA;
        border: 1px solid #C7D2FE;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1.2;
    }
    .course-total-badge-btn:hover {
        background: #4338CA;
        color: #FFFFFF;
        border-color: #4338CA;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(67, 56, 202, 0.25);
    }
    .metric-cohort-card {
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    .metric-cohort-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
    }
    .roster-tab-btn {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .roster-tab-btn:hover {
        background: #F1F5F9;
        color: #0F172A;
    }
    .roster-tab-btn.active {
        background: #0F172A;
        color: #FFFFFF;
        border-color: #0F172A;
    }
    .roster-tab-btn.active.tab-fy {
        background: #16A34A;
        color: #FFFFFF;
        border-color: #16A34A;
    }
    .roster-tab-btn.active.tab-sy {
        background: #2563EB;
        color: #FFFFFF;
        border-color: #2563EB;
    }
    .roster-tab-btn.active.tab-ty {
        background: #9333EA;
        color: #FFFFFF;
        border-color: #9333EA;
    }
    .gender-pill-btn {
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid #E2E8F0;
        background: #FFF;
        color: #64748B;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .gender-pill-btn.active {
        background: #475569;
        color: #FFF;
        border-color: #475569;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        #modalStudentRoster, #modalStudentRoster * {
            visibility: visible !important;
        }
        #modalStudentRoster {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            background: transparent !important;
            display: block !important;
        }
        #modalStudentRoster .modal-card {
            box-shadow: none !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .no-print {
            display: none !important;
        }
    }
    </style>

    <!-- ACADEMIC HIERARCHY MANAGER (Department -> Courses -> FY, SY, TY -> Students) -->
    <div class="chart-card" style="margin-bottom:24px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:14px; padding:22px; box-shadow:0 4px 12px rgba(15,23,42,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px; border-bottom:2px solid #F1F5F9; padding-bottom:14px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:22px;">🏛️</span>
                    <h3 style="font-size:18px; font-weight:800; color:#0F172A; margin:0;">
                        Institutional Academic Structure & Student Hierarchy
                    </h3>
                    <span style="background:#EFF6FF; color:#1D4ED8; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px;">LIVE COHORT DATA</span>
                </div>
                <p style="font-size:12px; color:#64748B; margin:4px 0 0 0;">
                    Department ➔ Multiple Courses ➔ 3 Academic Years (FY, SY, TY) ➔ Student Enrolment Breakdown &bull; <strong style="color:#2563EB;">Click any FY, SY, TY button to view student list</strong>
                </p>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <button type="button" class="btn" onclick="openEnrollStudentModal()" style="background:#10B981; color:#FFF; border:none; padding:8px 14px; font-size:12px; font-weight:700; border-radius:8px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                    👤 + Enroll Student
                </button>
                <a href="index.php?page=academic_hierarchy" class="btn btn-primary" style="background:#2563EB; border:none; padding:8px 16px; font-size:12px; font-weight:700; text-decoration:none; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                    🔍 Full Hierarchy Explorer & Roster ➔
                </a>
            </div>
        </div>

        <!-- Metric Badges Row (Clickable Cohort Filters) -->
        <?php if (!empty($hierarchyStats)): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin-bottom:20px;">
                <div class="metric-cohort-card" onclick="openInstitutionCohortRoster('ALL')" title="Click to view all enrolled students across all departments" style="background:#F8FAFC; border:1px solid #E2E8F0; padding:10px 14px; border-radius:8px; text-align:center;">
                    <div style="font-size:11px; color:#64748B; font-weight:700;">DEPARTMENTS</div>
                    <div style="font-size:18px; font-weight:800; color:#0F172A; margin-top:2px;"><?= (int)$hierarchyStats['total_departments'] ?></div>
                </div>
                <div class="metric-cohort-card" onclick="openInstitutionCohortRoster('ALL')" title="Click to view all students across all courses" style="background:#F8FAFC; border:1px solid #E2E8F0; padding:10px 14px; border-radius:8px; text-align:center;">
                    <div style="font-size:11px; color:#64748B; font-weight:700;">COURSES (ALL)</div>
                    <div style="font-size:18px; font-weight:800; color:#2563EB; margin-top:2px;"><?= (int)$hierarchyStats['total_courses'] ?> (<?= (int)$hierarchyStats['total_students'] ?> stds)</div>
                </div>
                <div class="metric-cohort-card" onclick="openInstitutionCohortRoster('FY')" title="Click to view all FY (1st Year) students across the institution" style="background:#F0FDF4; border:1px solid #BBF7D0; padding:10px 14px; border-radius:8px; text-align:center;">
                    <div style="font-size:11px; color:#166534; font-weight:800; display:flex; align-items:center; justify-content:center; gap:4px;">
                        <span>FY (1ST YEAR)</span> <span style="font-size:10px;">👁️</span>
                    </div>
                    <div style="font-size:18px; font-weight:800; color:#16A34A; margin-top:2px;"><?= (int)$hierarchyStats['total_fy_students'] ?> Students</div>
                </div>
                <div class="metric-cohort-card" onclick="openInstitutionCohortRoster('SY')" title="Click to view all SY (2nd Year) students across the institution" style="background:#EFF6FF; border:1px solid #BFDBFE; padding:10px 14px; border-radius:8px; text-align:center;">
                    <div style="font-size:11px; color:#1E40AF; font-weight:800; display:flex; align-items:center; justify-content:center; gap:4px;">
                        <span>SY (2ND YEAR)</span> <span style="font-size:10px;">👁️</span>
                    </div>
                    <div style="font-size:18px; font-weight:800; color:#2563EB; margin-top:2px;"><?= (int)$hierarchyStats['total_sy_students'] ?> Students</div>
                </div>
                <div class="metric-cohort-card" onclick="openInstitutionCohortRoster('TY')" title="Click to view all TY (3rd Year) students across the institution" style="background:#FAF5FF; border:1px solid #E9D5FF; padding:10px 14px; border-radius:8px; text-align:center;">
                    <div style="font-size:11px; color:#6B21A8; font-weight:800; display:flex; align-items:center; justify-content:center; gap:4px;">
                        <span>TY (3RD YEAR)</span> <span style="font-size:10px;">👁️</span>
                    </div>
                    <div style="font-size:18px; font-weight:800; color:#9333EA; margin-top:2px;"><?= (int)$hierarchyStats['total_ty_students'] ?> Students</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Departments & Courses Hierarchy Cards -->
        <?php if (!empty($academicHierarchy)): ?>
            <div style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($academicHierarchy as $dept): ?>
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="font-size:15px; color:#0F172A;">🏛️ <?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span style="background:#E2E8F0; color:#334155; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px; font-family:monospace;"><?= htmlspecialchars($dept['code'] ?? 'DEPT', ENT_QUOTES, 'UTF-8') ?></span>
                                <span style="font-size:11px; color:#64748B;">(<?= count($dept['courses']) ?> Courses &bull; <?= (int)$dept['student_count'] ?> Students)</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" onclick="openDepartmentRoster(<?= (int)$dept['id'] ?>)" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; font-size:11px; font-weight:700; padding:3px 9px; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="View all students in <?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?>">
                                    👥 Department Roster
                                </button>
                                <a href="index.php?page=academic_hierarchy&dept_id=<?= (int)$dept['id'] ?>" style="font-size:11px; font-weight:700; color:#2563EB; text-decoration:none;">View in Explorer ➔</a>
                            </div>
                        </div>

                        <?php if (empty($dept['courses'])): ?>
                            <div style="font-size:12px; color:#64748B; font-style:italic;">No courses currently configured in this department. <a href="index.php?page=courses" style="color:#2563EB;">Add a course</a></div>
                        <?php else: ?>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px;">
                                <?php foreach ($dept['courses'] as $crs): ?>
                                    <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; padding:12px; display:flex; flex-direction:column; justify-content:space-between;">
                                        <div>
                                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:8px;">
                                                <div style="flex:1; min-width:0;">
                                                    <div style="font-size:13px; font-weight:800; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer;" 
                                                         title="Click to view student roster for <?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                         onclick="openCourseRoster(<?= (int)$crs['id'] ?>, 'ALL')">
                                                        <?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                    <span style="font-size:10px; color:#64748B; font-family:monospace;"><?= htmlspecialchars($crs['code'], ENT_QUOTES, 'UTF-8') ?> &bull; <?= (int)$crs['duration_years'] ?> Years</span>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                                    <button type="button" 
                                                            class="course-total-badge-btn" 
                                                            onclick="openCourseRoster(<?= (int)$crs['id'] ?>, 'ALL')" 
                                                            title="Click to view all <?= (int)$crs['total_students'] ?> students in <?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= (int)$crs['total_students'] ?> stds
                                                    </button>
                                                    <button type="button" onclick='openEditCourseModal(<?= json_encode($crs, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Edit Course" style="background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE; border-radius:6px; padding:3px 6px; font-size:11px; font-weight:700; cursor:pointer; line-height:1; display:flex; align-items:center;" onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#EFF6FF'">
                                                        ✏️
                                                    </button>
                                                    <form method="POST" action="index.php?page=course_delete" onsubmit="return confirm('Are you sure you want to delete course: <?= htmlspecialchars($crs['name'], ENT_QUOTES) ?> (<?= htmlspecialchars($crs['code'], ENT_QUOTES) ?>)?');" style="margin:0; display:inline;">
                                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                                        <input type="hidden" name="id" value="<?= (int)$crs['id'] ?>">
                                                        <input type="hidden" name="redirect_page" value="dashboard">
                                                        <button type="submit" title="Delete Course" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:6px; padding:3px 6px; font-size:11px; font-weight:700; cursor:pointer; line-height:1; display:flex; align-items:center;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='#FEF2F2'">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FY, SY, TY Interactive Buttons -->
                                        <div style="display:flex; gap:6px; margin-top:8px;">
                                            <?php foreach ($crs['years'] as $yr): ?>
                                                <?php 
                                                    $pillClass = $yr['code'] === 'FY' ? 'fy-btn' : ($yr['code'] === 'SY' ? 'sy-btn' : 'ty-btn');
                                                ?>
                                                <button type="button" 
                                                        class="cohort-pill-btn <?= $pillClass ?>" 
                                                        onclick="openCourseRoster(<?= (int)$crs['id'] ?>, '<?= $yr['code'] ?>')" 
                                                        title="Click to view all <?= $yr['code'] ?> students of <?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?> (<?= count($yr['students']) ?> enrolled)">
                                                    <span><?= $yr['code'] ?>:</span> <span><?= count($yr['students']) ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Academic Structure Manager -->
    <div class="chart-card" style="margin-bottom:24px;">
        <div class="chart-header">
            <h3>Academic Structure (Classes & Sections)</h3>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('addClassModal').classList.add('active')">+ Add New Class/Subject</button>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color);">
                        <th style="padding:12px;">CLASS NAME</th>
                        <th style="padding:12px;">CODE</th>
                        <th style="padding:12px;">SECTIONS COUNT</th>
                        <th style="padding:12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $c): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:12px;"><code><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td style="padding:12px;"><?= $c['section_count'] ?> Active Sections</td>
                        <td style="padding:12px; text-align:right; display:flex; gap:6px; justify-content:flex-end; align-items:center;">
                            <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px;" data-record='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>' onclick="handleDashViewClass(this)">View</button>

                            <button type="button" class="btn btn-primary" style="padding:4px 10px; font-size:11px;" data-record='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>' onclick="handleDashEditClass(this)">Edit</button>

                            <a href="index.php?page=module&name=time_table" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; text-decoration:none;">Timetable</a>
                            
                            <form action="index.php?page=delete_class" method="POST" onsubmit="return confirm('Permanently delete class &quot;<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>&quot;?');" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; color:var(--danger); border-color:#FCA5A5;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include 36-Module Grid Launcher Partial -->
    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<!-- ADD CLASS MODAL -->
<div class="modal-overlay" id="addClassModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">+ Provision New Academic Class</h3>
            <button type="button" onclick="document.getElementById('addClassModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=store_class" method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Class Name</label>
                    <input type="text" name="class_name" class="form-control" placeholder="e.g. Class 11 - Computer Science" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Class Unique Code</label>
                    <input type="text" name="class_code" class="form-control" placeholder="e.g. C11-CS" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addClassModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Class to Institution</button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW CLASS MODAL -->
<div class="modal-overlay" id="viewClassModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">👁️ Academic Class Details</h3>
            <button type="button" onclick="document.getElementById('viewClassModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body" id="viewClassModalBody" style="padding:16px;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('viewClassModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<!-- EDIT CLASS MODAL -->
<div class="modal-overlay" id="editClassModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">✏️ Edit Academic Class Details</h3>
            <button type="button" onclick="document.getElementById('editClassModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=update_class" method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                <input type="hidden" name="class_id" id="editClassId" value="">
                
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Class Name</label>
                    <input type="text" name="class_name" id="editClassName" class="form-control" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Class Unique Code</label>
                    <input type="text" name="class_code" id="editClassCode" class="form-control" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editClassModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Class</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT COURSE MODAL -->
<div class="modal-overlay" id="editCourseModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:2050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:540px; width:92%; background:#FFF; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); padding:16px 22px; color:#FFF; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:800; margin:0;" id="editModalCourseTitle">✏️ Edit Course</h3>
            <button type="button" onclick="closeEditCourseModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=course_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="id" id="edit_course_id" value="0">
            <input type="hidden" name="redirect_page" value="dashboard">
            
            <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Parent Department *</label>
                    <select name="department_id" id="edit_course_dept_id" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600; background:#FFF;">
                        <?php if (!empty($academicHierarchy)): ?>
                            <?php foreach ($academicHierarchy as $d): ?>
                                <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course / Degree Name *</label>
                    <input type="text" name="name" id="edit_course_name" required placeholder="e.g. B.Sc. Information Technology" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course Code *</label>
                        <input type="text" name="code" id="edit_course_code" required placeholder="e.g. BSCIT, BCOM" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase; font-weight:700;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Duration (Years) *</label>
                        <select name="duration_years" id="edit_course_duration" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                            <option value="1">1 Year</option>
                            <option value="2">2 Years (Master's)</option>
                            <option value="3">3 Years (FY, SY, TY)</option>
                            <option value="4">4 Years (Engineering/Honors)</option>
                            <option value="5">5 Years (Integrated)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status</label>
                    <select name="status" id="edit_course_status" style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Description / Curriculum Notes</label>
                    <textarea name="description" id="edit_course_description" rows="2" placeholder="Course notes..." style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px;"></textarea>
                </div>
            </div>

            <div style="background:#F8FAFC; padding:12px 20px; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeEditCourseModal()" class="btn btn-secondary" style="padding:8px 16px; font-weight:700; border-radius:6px; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px; background:#2563EB; border:none; font-weight:700; border-radius:6px; cursor:pointer;">Update Course</button>
            </div>
        </form>
    </div>
</div>

<!-- LIVE ACADEMIC HIERARCHY DATASET -->
<script id="academicHierarchyJson" type="application/json">
<?= json_encode($academicHierarchy, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>

<!-- MODAL: STUDENT COHORT ROSTER -->
<div class="modal-overlay" id="modalStudentRoster" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.68); backdrop-filter:blur(4px); z-index:2100; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:980px; width:95%; max-height:88vh; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px -15px rgba(15,23,42,0.4); margin:auto; display:flex; flex-direction:column;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:22px;">🎓</span>
                    <h3 style="font-size:17px; font-weight:800; color:#FFFFFF; margin:0;" id="rosterModalTitle">
                        Student Cohort Roster
                    </h3>
                    <span id="rosterModalYearBadge" style="background:#16A34A; color:#FFF; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px;">FY</span>
                    <span id="rosterModalCountBadge" style="background:rgba(59,130,246,0.3); color:#93C5FD; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px;">0 Students</span>
                </div>
                <div style="font-size:12px; color:#94A3B8; margin-top:4px;" id="rosterModalSubtitle">
                    Department &bull; Course Enrolment Roster
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" onclick="openEnrollFromRoster()" class="btn btn-sm" style="background:#10B981; color:#FFF; font-weight:700; font-size:11px; padding:7px 12px; border-radius:6px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                    👤 + Enroll Student
                </button>
                <a id="rosterModalExplorerLink" href="index.php?page=academic_hierarchy" class="btn btn-sm" style="background:rgba(255,255,255,0.15); color:#FFF; font-weight:700; font-size:11px; padding:7px 12px; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                    🔍 Explorer ➔
                </a>
                <button type="button" onclick="closeStudentRosterModal()" style="background:none; border:none; color:#CBD5E1; font-size:26px; cursor:pointer; line-height:1; padding:0 4px; transition:color 0.15s;" onmouseover="this.style.color='#FFF'" onmouseout="this.style.color='#CBD5E1'">&times;</button>
            </div>
        </div>

        <!-- Filter Controls Bar -->
        <div style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:14px 22px; display:flex; flex-direction:column; gap:12px;">
            <!-- Row 1: Year Tabs & Course Dropdown -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; gap:6px; flex-wrap:wrap;" id="rosterYearTabs">
                    <button type="button" class="roster-tab-btn active" id="tab_all" onclick="switchRosterTab('ALL')">
                        <span>All Students</span> <span style="background:rgba(0,0,0,0.08); padding:2px 6px; border-radius:10px; font-size:10px;" id="badge_count_all">0</span>
                    </button>
                    <button type="button" class="roster-tab-btn tab-fy" id="tab_FY" onclick="switchRosterTab('FY')">
                        <span>🥇 FY (1st Year)</span> <span style="background:rgba(0,0,0,0.08); padding:2px 6px; border-radius:10px; font-size:10px;" id="badge_count_FY">0</span>
                    </button>
                    <button type="button" class="roster-tab-btn tab-sy" id="tab_SY" onclick="switchRosterTab('SY')">
                        <span>🥈 SY (2nd Year)</span> <span style="background:rgba(0,0,0,0.08); padding:2px 6px; border-radius:10px; font-size:10px;" id="badge_count_SY">0</span>
                    </button>
                    <button type="button" class="roster-tab-btn tab-ty" id="tab_TY" onclick="switchRosterTab('TY')">
                        <span>🥉 TY (3rd Year)</span> <span style="background:rgba(0,0,0,0.08); padding:2px 6px; border-radius:10px; font-size:10px;" id="badge_count_TY">0</span>
                    </button>
                </div>

                <div id="rosterCourseFilterWrapper" style="display:none; align-items:center; gap:6px;">
                    <label style="font-size:11px; font-weight:700; color:#475569;">Course:</label>
                    <select id="rosterCourseFilter" onchange="applyRosterFilters()" style="padding:6px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px; font-weight:600; background:#FFF; max-width:240px;">
                        <option value="ALL">All Courses</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Search Input & Gender Filter -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="position:relative; flex:1; min-width:240px;">
                    <input type="text" id="rosterSearchInput" oninput="applyRosterFilters()" placeholder="🔍 Quick search student by name, roll no, admission no, email..." style="width:100%; padding:8px 30px 8px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px; font-weight:600; outline:none; transition:border-color 0.15s;" onfocus="this.style.borderColor='#2563EB'" onblur="this.style.borderColor='#CBD5E1'">
                    <button type="button" id="rosterClearSearchBtn" onclick="clearRosterSearch()" style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94A3B8; font-size:16px; cursor:pointer;">&times;</button>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="display:flex; align-items:center; gap:4px;">
                        <span style="font-size:11px; font-weight:700; color:#64748B;">Gender:</span>
                        <button type="button" class="gender-pill-btn active" id="gender_all" onclick="setGenderFilter('ALL')">All</button>
                        <button type="button" class="gender-pill-btn" id="gender_male" onclick="setGenderFilter('male')">Male</button>
                        <button type="button" class="gender-pill-btn" id="gender_female" onclick="setGenderFilter('female')">Female</button>
                    </div>
                    <span style="font-size:12px; font-weight:800; color:#1D4ED8; white-space:nowrap; background:#EFF6FF; padding:4px 8px; border-radius:6px;" id="rosterVisibleCountDisplay">
                        Showing 0 Students
                    </span>
                </div>
            </div>
        </div>

        <!-- Student Table Body -->
        <div style="flex:1; overflow-y:auto; overflow-x:auto; max-height:54vh; padding:0; background:#FFF;" id="rosterTableContainer">
            <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                <thead>
                    <tr style="background:#F1F5F9; color:#475569; position:sticky; top:0; z-index:2; border-bottom:1px solid #CBD5E1;">
                        <th style="padding:10px 12px; width:40px; text-align:center;">#</th>
                        <th style="padding:10px 12px;">ROLL NO</th>
                        <th style="padding:10px 12px;">STUDENT NAME</th>
                        <th style="padding:10px 12px; text-align:center;">COHORT / YEAR</th>
                        <th style="padding:10px 12px;" id="rosterThCourse">COURSE &amp; DEPT</th>
                        <th style="padding:10px 12px;">ADMISSION NO</th>
                        <th style="padding:10px 12px;">CONTACT / EMAIL</th>
                        <th style="padding:10px 12px; text-align:center;">GENDER</th>
                        <th style="padding:10px 12px; text-align:center;">STATUS</th>
                    </tr>
                </thead>
                <tbody id="rosterTableTbody">
                </tbody>
            </table>

            <!-- Empty State -->
            <div id="rosterEmptyState" style="display:none; padding:40px 20px; text-align:center;">
                <div style="font-size:42px; margin-bottom:8px;">🎓</div>
                <h4 style="font-size:16px; font-weight:800; color:#1E293B; margin:0 0 6px 0;" id="rosterEmptyTitle">No Students Found</h4>
                <p style="font-size:12px; color:#64748B; max-width:440px; margin:0 auto 16px auto;" id="rosterEmptySubtitle">
                    There are no students matching your filter criteria.
                </p>
                <div style="display:flex; justify-content:center; gap:8px;">
                    <button type="button" onclick="clearRosterFilters()" class="btn btn-sm btn-secondary" style="font-size:11px; padding:6px 14px; cursor:pointer;">Reset Filters</button>
                    <button type="button" onclick="openEnrollFromRoster()" class="btn btn-sm btn-primary" style="font-size:11px; padding:6px 14px; background:#10B981; border:none; cursor:pointer;">+ Enroll Student</button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="background:#F8FAFC; border-top:1px solid #E2E8F0; padding:12px 22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="font-size:11px; color:#64748B; font-weight:600;" id="rosterFooterMeta">
                🏛️ Institutional Academic Structure &bull; Live Cohort Synchronized
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button type="button" onclick="printRosterTable()" class="btn btn-secondary no-print" style="padding:6px 14px; font-size:11px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#FFF; border:1px solid #CBD5E1;">
                    🖨️ Print Roster
                </button>
                <button type="button" onclick="exportRosterToCSV()" class="btn btn-secondary no-print" style="padding:6px 14px; font-size:11px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#FFF; border:1px solid #CBD5E1;">
                    📥 Export CSV
                </button>
                <button type="button" onclick="closeStudentRosterModal()" class="btn btn-secondary no-print" style="padding:6px 16px; font-size:11px; font-weight:700; border-radius:6px; cursor:pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: QUICK ENROLL STUDENT -->
<div class="modal-overlay" id="modalEnrollStudent" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:2200; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:580px; width:92%; background:#FFF; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px;">
                👤 Quick Enroll Student into Cohort Year (FY, SY, TY)
            </h3>
            <button type="button" onclick="closeEnrollStudentModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=quick_enroll_student">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <div style="padding:22px; display:flex; flex-direction:column; gap:14px; max-height:75vh; overflow-y:auto;">
                <!-- Step 1: Department & Course -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department *</label>
                        <select name="department_id" id="enroll_dept_id" onchange="syncEnrollCourses(this.value)" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600; background:#FFF;">
                            <option value="">-- Select Dept --</option>
                            <?php if (!empty($academicHierarchy)): ?>
                                <?php foreach ($academicHierarchy as $d): ?>
                                    <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course *</label>
                        <select name="course_id" id="enroll_course_id" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600; background:#FFF;">
                            <option value="">-- Select Course --</option>
                            <?php if (!empty($academicHierarchy)): ?>
                                <?php foreach ($academicHierarchy as $d): ?>
                                    <?php foreach ($d['courses'] as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>" data-dept-id="<?= (int)$d['id'] ?>">
                                            <?= htmlspecialchars($c['name'] . ' (' . $c['code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Step 2: Year Selection (FY, SY, TY) -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Academic Year / Level *</label>
                    <select name="year_order" id="enroll_year_order" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:700; background:#F8FAFC; color:#0F172A;">
                        <option value="1">🥇 First Year (FY) - Semester 1 &amp; 2</option>
                        <option value="2">🥈 Second Year (SY) - Semester 3 &amp; 4</option>
                        <option value="3">🥉 Third Year (TY) - Semester 5 &amp; 6</option>
                    </select>
                </div>

                <!-- Step 3: Student Details -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">First Name *</label>
                        <input type="text" name="first_name" required placeholder="e.g. Rahul" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Last Name *</label>
                        <input type="text" name="last_name" required placeholder="e.g. Sharma" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Email Address</label>
                        <input type="email" name="email" placeholder="student@college.edu" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Mobile Phone</label>
                        <input type="text" name="phone" placeholder="+91 9876543210" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Roll Number (Optional - Auto generated)</label>
                        <input type="text" name="roll_no" placeholder="e.g. FYIT-05 (leave blank for auto)" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Gender</label>
                        <select name="gender" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="background:#F8FAFC; padding:14px 22px; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeEnrollStudentModal()" class="btn btn-secondary" style="padding:8px 16px; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#6366F1; border:none; padding:8px 20px; font-weight:700; cursor:pointer;">✨ Complete Enrollment</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- Academic Hierarchy Live Data ---
let academicHierarchyData = [];
try {
    const rawData = document.getElementById('academicHierarchyJson').textContent;
    academicHierarchyData = JSON.parse(rawData);
} catch(e) {
    console.error("Could not parse academicHierarchyJson:", e);
}

// Roster state
let rosterCurrentMode = 'course'; // 'course' | 'department' | 'institution'
let rosterActiveTargetId = null;  // courseId or deptId
let rosterActiveYear = 'FY';      // 'ALL' | 'FY' | 'SY' | 'TY'
let rosterActiveGender = 'ALL';   // 'ALL' | 'male' | 'female'
let rosterLoadedStudents = [];

function openCourseRoster(courseId, yearCode = 'ALL') {
    rosterCurrentMode = 'course';
    rosterActiveTargetId = parseInt(courseId);
    rosterActiveYear = yearCode;
    rosterActiveGender = 'ALL';
    const sInput = document.getElementById('rosterSearchInput');
    if (sInput) sInput.value = '';
    
    let foundCourse = null;
    let foundDept = null;
    for (const d of academicHierarchyData) {
        for (const c of (d.courses || [])) {
            if (parseInt(c.id) === parseInt(courseId)) {
                foundCourse = c;
                foundDept = d;
                break;
            }
        }
        if (foundCourse) break;
    }

    if (!foundCourse) {
        console.error("Course not found:", courseId);
        return;
    }

    document.getElementById('rosterModalTitle').innerText = '🎓 ' + foundCourse.name;
    document.getElementById('rosterModalSubtitle').innerText = (foundDept ? foundDept.name + ' (' + (foundDept.code || 'DEPT') + ') • ' : '') + (foundCourse.code || '') + ' • ' + (foundCourse.duration_years || 3) + ' Years';
    document.getElementById('rosterModalExplorerLink').href = 'index.php?page=academic_hierarchy' + (foundDept ? '&dept_id=' + foundDept.id : '');
    
    const fyCount = countStudents(foundCourse, 'FY');
    const syCount = countStudents(foundCourse, 'SY');
    const tyCount = countStudents(foundCourse, 'TY');
    const allCount = fyCount + syCount + tyCount;
    
    document.getElementById('badge_count_all').innerText = allCount;
    document.getElementById('badge_count_FY').innerText = fyCount;
    document.getElementById('badge_count_SY').innerText = syCount;
    document.getElementById('badge_count_TY').innerText = tyCount;

    document.getElementById('rosterCourseFilterWrapper').style.display = 'none';
    const thCourse = document.getElementById('rosterThCourse');
    if (thCourse) thCourse.style.display = 'none';

    updateYearTabsUI();
    updateGenderTabsUI();
    loadAndRenderRoster();

    const modal = document.getElementById('modalStudentRoster');
    if (modal) modal.style.display = 'flex';
}

function openInstitutionCohortRoster(yearCode = 'FY') {
    rosterCurrentMode = 'institution';
    rosterActiveTargetId = null;
    rosterActiveYear = yearCode;
    rosterActiveGender = 'ALL';
    const sInput = document.getElementById('rosterSearchInput');
    if (sInput) sInput.value = '';

    document.getElementById('rosterModalTitle').innerText = '🏛️ Institutional Student Cohort Roster';
    document.getElementById('rosterModalSubtitle').innerText = 'All Departments • All Degree Courses • ' + (yearCode === 'ALL' ? 'All Enrolled Students' : yearCode + ' Cohort');
    document.getElementById('rosterModalExplorerLink').href = 'index.php?page=academic_hierarchy';

    let fyCount = 0, syCount = 0, tyCount = 0;
    for (const d of academicHierarchyData) {
        for (const c of (d.courses || [])) {
            fyCount += countStudents(c, 'FY');
            syCount += countStudents(c, 'SY');
            tyCount += countStudents(c, 'TY');
        }
    }
    document.getElementById('badge_count_all').innerText = (fyCount + syCount + tyCount);
    document.getElementById('badge_count_FY').innerText = fyCount;
    document.getElementById('badge_count_SY').innerText = syCount;
    document.getElementById('badge_count_TY').innerText = tyCount;

    const courseSel = document.getElementById('rosterCourseFilter');
    if (courseSel) {
        courseSel.innerHTML = '<option value="ALL">All Courses (' + (fyCount + syCount + tyCount) + ')</option>';
        for (const d of academicHierarchyData) {
            for (const c of (d.courses || [])) {
                const cTotal = (c.years || []).reduce((acc, y) => acc + (y.students ? y.students.length : 0), 0);
                courseSel.innerHTML += `<option value="${c.id}">${c.name} (${cTotal})</option>`;
            }
        }
    }
    document.getElementById('rosterCourseFilterWrapper').style.display = 'flex';
    const thCourse = document.getElementById('rosterThCourse');
    if (thCourse) thCourse.style.display = '';

    updateYearTabsUI();
    updateGenderTabsUI();
    loadAndRenderRoster();

    const modal = document.getElementById('modalStudentRoster');
    if (modal) modal.style.display = 'flex';
}

function openDepartmentRoster(deptId) {
    rosterCurrentMode = 'department';
    rosterActiveTargetId = parseInt(deptId);
    rosterActiveYear = 'ALL';
    rosterActiveGender = 'ALL';
    const sInput = document.getElementById('rosterSearchInput');
    if (sInput) sInput.value = '';

    const dept = academicHierarchyData.find(d => parseInt(d.id) === parseInt(deptId));
    if (!dept) return;

    document.getElementById('rosterModalTitle').innerText = '🏛️ ' + dept.name + ' — Student Roster';
    document.getElementById('rosterModalSubtitle').innerText = (dept.code || 'DEPT') + ' • ' + (dept.courses ? dept.courses.length : 0) + ' Degree Courses • ' + (dept.student_count || 0) + ' Students';
    document.getElementById('rosterModalExplorerLink').href = 'index.php?page=academic_hierarchy&dept_id=' + dept.id;

    let fyCount = 0, syCount = 0, tyCount = 0;
    for (const c of (dept.courses || [])) {
        fyCount += countStudents(c, 'FY');
        syCount += countStudents(c, 'SY');
        tyCount += countStudents(c, 'TY');
    }
    document.getElementById('badge_count_all').innerText = (fyCount + syCount + tyCount);
    document.getElementById('badge_count_FY').innerText = fyCount;
    document.getElementById('badge_count_SY').innerText = syCount;
    document.getElementById('badge_count_TY').innerText = tyCount;

    const courseSel = document.getElementById('rosterCourseFilter');
    if (courseSel) {
        courseSel.innerHTML = '<option value="ALL">All Department Courses</option>';
        for (const c of (dept.courses || [])) {
            const cTotal = (c.years || []).reduce((acc, y) => acc + (y.students ? y.students.length : 0), 0);
            courseSel.innerHTML += `<option value="${c.id}">${c.name} (${cTotal})</option>`;
        }
    }
    document.getElementById('rosterCourseFilterWrapper').style.display = 'flex';
    const thCourse = document.getElementById('rosterThCourse');
    if (thCourse) thCourse.style.display = '';

    updateYearTabsUI();
    updateGenderTabsUI();
    loadAndRenderRoster();

    const modal = document.getElementById('modalStudentRoster');
    if (modal) modal.style.display = 'flex';
}

function countStudents(course, yearCode) {
    if (!course || !course.years) return 0;
    const yr = course.years.find(y => y.code === yearCode);
    return yr && yr.students ? yr.students.length : 0;
}

function switchRosterTab(yearCode) {
    rosterActiveYear = yearCode;
    updateYearTabsUI();
    applyRosterFilters();
}

function updateYearTabsUI() {
    ['all', 'FY', 'SY', 'TY'].forEach(code => {
        const btn = document.getElementById('tab_' + code);
        if (!btn) return;
        if ((code === 'all' && rosterActiveYear === 'ALL') || code === rosterActiveYear) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const yrBadge = document.getElementById('rosterModalYearBadge');
    if (!yrBadge) return;
    if (rosterActiveYear === 'FY') {
        yrBadge.innerText = 'FY (1st Year)';
        yrBadge.style.background = '#16A34A';
    } else if (rosterActiveYear === 'SY') {
        yrBadge.innerText = 'SY (2nd Year)';
        yrBadge.style.background = '#2563EB';
    } else if (rosterActiveYear === 'TY') {
        yrBadge.innerText = 'TY (3rd Year)';
        yrBadge.style.background = '#9333EA';
    } else {
        yrBadge.innerText = 'All Years (FY, SY, TY)';
        yrBadge.style.background = '#475569';
    }
}

function setGenderFilter(gender) {
    rosterActiveGender = gender;
    updateGenderTabsUI();
    applyRosterFilters();
}

function updateGenderTabsUI() {
    ['all', 'male', 'female'].forEach(g => {
        const btn = document.getElementById('gender_' + g);
        if (!btn) return;
        if (g.toUpperCase() === rosterActiveGender.toUpperCase()) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

function loadAndRenderRoster() {
    let list = [];
    if (rosterCurrentMode === 'course') {
        for (const d of academicHierarchyData) {
            for (const c of (d.courses || [])) {
                if (parseInt(c.id) === rosterActiveTargetId) {
                    for (const yr of (c.years || [])) {
                        for (const st of (yr.students || [])) {
                            list.push({ ...st, courseName: c.name, courseCode: c.code, courseId: c.id, deptName: d.name, deptCode: d.code, deptId: d.id, yearCode: yr.code });
                        }
                    }
                }
            }
        }
    } else if (rosterCurrentMode === 'department') {
        const dept = academicHierarchyData.find(d => parseInt(d.id) === rosterActiveTargetId);
        if (dept) {
            for (const c of (dept.courses || [])) {
                for (const yr of (c.years || [])) {
                    for (const st of (yr.students || [])) {
                        list.push({ ...st, courseName: c.name, courseCode: c.code, courseId: c.id, deptName: dept.name, deptCode: dept.code, deptId: dept.id, yearCode: yr.code });
                    }
                }
            }
        }
    } else {
        for (const d of academicHierarchyData) {
            for (const c of (d.courses || [])) {
                for (const yr of (c.years || [])) {
                    for (const st of (yr.students || [])) {
                        list.push({ ...st, courseName: c.name, courseCode: c.code, courseId: c.id, deptName: d.name, deptCode: d.code, deptId: d.id, yearCode: yr.code });
                    }
                }
            }
        }
    }
    rosterLoadedStudents = list;
    applyRosterFilters();
}

function applyRosterFilters() {
    const sInput = document.getElementById('rosterSearchInput');
    const q = (sInput ? sInput.value : '').trim().toLowerCase();
    const clearBtn = document.getElementById('rosterClearSearchBtn');
    if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

    const courseFilterVal = document.getElementById('rosterCourseFilter') ? document.getElementById('rosterCourseFilter').value : 'ALL';

    const filtered = rosterLoadedStudents.filter(st => {
        if (rosterActiveYear !== 'ALL' && st.yearCode !== rosterActiveYear) {
            return false;
        }
        if (courseFilterVal !== 'ALL' && parseInt(st.courseId) !== parseInt(courseFilterVal)) {
            return false;
        }
        if (rosterActiveGender !== 'ALL') {
            const gen = (st.gender || 'male').toLowerCase();
            if (gen !== rosterActiveGender.toLowerCase()) return false;
        }
        if (q) {
            const fullName = `${st.first_name || ''} ${st.middle_name || ''} ${st.last_name || ''}`.toLowerCase();
            const roll = (st.roll_no || '').toLowerCase();
            const adm = (st.admission_no || st.unique_id || '').toLowerCase();
            const email = (st.email || '').toLowerCase();
            const phone = (st.phone || '').toLowerCase();
            const username = (st.username || '').toLowerCase();
            if (!fullName.includes(q) && !roll.includes(q) && !adm.includes(q) && !email.includes(q) && !phone.includes(q) && !username.includes(q)) {
                return false;
            }
        }
        return true;
    });

    renderRosterTable(filtered);
}

function clearRosterSearch() {
    const sInput = document.getElementById('rosterSearchInput');
    if (sInput) sInput.value = '';
    applyRosterFilters();
}

function clearRosterFilters() {
    const sInput = document.getElementById('rosterSearchInput');
    if (sInput) sInput.value = '';
    rosterActiveYear = 'ALL';
    rosterActiveGender = 'ALL';
    const cSel = document.getElementById('rosterCourseFilter');
    if (cSel) cSel.value = 'ALL';
    updateYearTabsUI();
    updateGenderTabsUI();
    applyRosterFilters();
}

function renderRosterTable(students) {
    const tbody = document.getElementById('rosterTableTbody');
    const emptyState = document.getElementById('rosterEmptyState');
    const visibleCount = document.getElementById('rosterVisibleCountDisplay');
    const modalCountBadge = document.getElementById('rosterModalCountBadge');

    if (visibleCount) visibleCount.innerText = `Showing ${students.length} Student${students.length === 1 ? '' : 's'}`;
    if (modalCountBadge) modalCountBadge.innerText = `${students.length} Students`;

    if (!tbody) return;

    if (students.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) {
            emptyState.style.display = 'block';
            document.getElementById('rosterEmptyTitle').innerText = 'No Students Found';
            const yearTxt = rosterActiveYear === 'ALL' ? 'any year' : rosterActiveYear + ' (Year ' + (rosterActiveYear === 'FY' ? 1 : (rosterActiveYear === 'SY' ? 2 : 3)) + ')';
            document.getElementById('rosterEmptySubtitle').innerText = `No enrolled students match current filters for ${yearTxt}.`;
        }
        return;
    }

    if (emptyState) emptyState.style.display = 'none';
    const thCourse = document.getElementById('rosterThCourse');
    const showCourseCol = thCourse && thCourse.style.display !== 'none';

    let html = '';
    students.forEach((st, idx) => {
        const initial = (st.first_name ? st.first_name.charAt(0) : 'S').toUpperCase();
        const fullName = `${st.first_name || ''} ${st.last_name || ''}`.trim() || 'Student';
        const rollBadge = st.roll_no ? `<span style="background:#EEF2FF; color:#4338CA; padding:3px 8px; border-radius:4px; font-weight:800; font-family:monospace; font-size:11px;">${escapeHtml(st.roll_no)}</span>` : '<span style="color:#94A3B8; font-style:italic;">N/A</span>';
        
        let yearPill = '';
        if (st.yearCode === 'FY') {
            yearPill = '<span style="background:#DCFCE7; color:#166534; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px;">FY (Year 1)</span>';
        } else if (st.yearCode === 'SY') {
            yearPill = '<span style="background:#DBEAFE; color:#1E40AF; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px;">SY (Year 2)</span>';
        } else {
            yearPill = '<span style="background:#F3E8FF; color:#6B21A8; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px;">TY (Year 3)</span>';
        }

        const avatarGradients = [
            'linear-gradient(135deg, #3B82F6, #1D4ED8)',
            'linear-gradient(135deg, #10B981, #047857)',
            'linear-gradient(135deg, #8B5CF6, #6D28D9)',
            'linear-gradient(135deg, #F59E0B, #B45309)',
            'linear-gradient(135deg, #EC4899, #BE185D)'
        ];
        const grad = avatarGradients[Math.abs(hashString(fullName)) % avatarGradients.length];

        html += `
            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.12s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <td style="padding:10px 12px; text-align:center; color:#94A3B8; font-size:11px; font-weight:700;">${idx + 1}</td>
                <td style="padding:10px 12px;">${rollBadge}</td>
                <td style="padding:10px 12px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:28px; height:28px; border-radius:50%; background:${grad}; color:#FFF; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; flex-shrink:0;">
                            ${initial}
                        </div>
                        <div>
                            <strong style="color:#0F172A; font-size:13px; display:block;">${escapeHtml(fullName)}</strong>
                            <span style="font-size:10px; color:#64748B;">@${escapeHtml(st.username || 'student')}</span>
                        </div>
                    </div>
                </td>
                <td style="padding:10px 12px; text-align:center;">${yearPill}</td>
                ${showCourseCol ? `<td style="padding:10px 12px;"><span style="font-size:11px; font-weight:700; color:#334155;">${escapeHtml(st.courseName || '')}</span><div style="font-size:10px; color:#64748B;">${escapeHtml(st.deptName || '')}</div></td>` : ''}
                <td style="padding:10px 12px; font-family:monospace; color:#334155; font-size:11px;">
                    ${escapeHtml(st.admission_no || st.unique_id || 'ADM')}
                </td>
                <td style="padding:10px 12px; color:#475569;">
                    <div style="font-size:11px;">${escapeHtml(st.email || 'No email')}</div>
                    <div style="font-size:10px; color:#94A3B8;">${escapeHtml(st.phone || '')}</div>
                </td>
                <td style="padding:10px 12px; text-align:center; text-transform:capitalize; color:#475569; font-size:11px;">
                    ${escapeHtml(st.gender || 'male')}
                </td>
                <td style="padding:10px 12px; text-align:center;">
                    <span style="background:#DCFCE7; color:#166534; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;">
                        <span style="width:5px; height:5px; border-radius:50%; background:#16A34A;"></span> Active
                    </span>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function closeStudentRosterModal() {
    const modal = document.getElementById('modalStudentRoster');
    if (modal) modal.style.display = 'none';
}

function openEnrollFromRoster() {
    let deptId = null;
    let courseId = null;
    let yearOrder = rosterActiveYear === 'SY' ? 2 : (rosterActiveYear === 'TY' ? 3 : 1);

    if (rosterCurrentMode === 'course' && rosterActiveTargetId) {
        courseId = rosterActiveTargetId;
        const found = academicHierarchyData.find(d => (d.courses || []).some(c => parseInt(c.id) === courseId));
        if (found) deptId = found.id;
    } else if (rosterCurrentMode === 'department' && rosterActiveTargetId) {
        deptId = rosterActiveTargetId;
    }

    openEnrollStudentModal(deptId, courseId, yearOrder);
}

function openEnrollStudentModal(deptId = null, courseId = null, yearOrder = 1) {
    const dSel = document.getElementById('enroll_dept_id');
    const ySel = document.getElementById('enroll_year_order');
    if (deptId && dSel) {
        dSel.value = deptId;
        syncEnrollCourses(deptId);
    }
    const cSel = document.getElementById('enroll_course_id');
    if (courseId && cSel) {
        cSel.value = courseId;
    }
    if (yearOrder && ySel) {
        ySel.value = yearOrder;
    }
    const modal = document.getElementById('modalEnrollStudent');
    if (modal) modal.style.display = 'flex';
}

function closeEnrollStudentModal() {
    const modal = document.getElementById('modalEnrollStudent');
    if (modal) modal.style.display = 'none';
}

function syncEnrollCourses(deptId) {
    const cSel = document.getElementById('enroll_course_id');
    if (!cSel) return;
    const opts = cSel.options;
    let firstMatch = '';
    for (let i = 0; i < opts.length; i++) {
        const opt = opts[i];
        if (!opt.value) continue;
        const dId = opt.getAttribute('data-dept-id');
        if (!deptId || dId == deptId) {
            opt.style.display = '';
            if (!firstMatch) firstMatch = opt.value;
        } else {
            opt.style.display = 'none';
        }
    }
    if (firstMatch) {
        cSel.value = firstMatch;
    }
}

function exportRosterToCSV() {
    if (!rosterLoadedStudents || rosterLoadedStudents.length === 0) {
        alert("No students to export.");
        return;
    }
    const sInput = document.getElementById('rosterSearchInput');
    const q = (sInput ? sInput.value : '').trim().toLowerCase();
    const courseFilterVal = document.getElementById('rosterCourseFilter') ? document.getElementById('rosterCourseFilter').value : 'ALL';
    
    const exportList = rosterLoadedStudents.filter(st => {
        if (rosterActiveYear !== 'ALL' && st.yearCode !== rosterActiveYear) return false;
        if (courseFilterVal !== 'ALL' && parseInt(st.courseId) !== parseInt(courseFilterVal)) return false;
        if (rosterActiveGender !== 'ALL' && (st.gender || 'male').toLowerCase() !== rosterActiveGender.toLowerCase()) return false;
        if (q) {
            const fullName = `${st.first_name || ''} ${st.last_name || ''}`.toLowerCase();
            if (!fullName.includes(q) && !(st.roll_no || '').toLowerCase().includes(q)) return false;
        }
        return true;
    });

    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Index,Roll No,Full Name,Cohort,Course,Department,Admission No,Email,Phone,Gender,Status\r\n";
    exportList.forEach((st, idx) => {
        const name = `"${(st.first_name || '')} ${(st.last_name || '')}"`;
        const row = [
            idx + 1,
            st.roll_no || '',
            name,
            st.yearCode || '',
            `"${st.courseName || ''}"`,
            `"${st.deptName || ''}"`,
            st.admission_no || st.unique_id || '',
            st.email || '',
            st.phone || '',
            st.gender || 'male',
            st.status || 'active'
        ].join(",");
        csvContent += row + "\r\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Student_Cohort_${rosterActiveYear}_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printRosterTable() {
    window.print();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function hashString(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
    }
    return hash;
}

// Global modal overlay click & ESC key listener
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStudentRosterModal();
        closeEnrollStudentModal();
        closeEditCourseModal();
    }
});
window.addEventListener('click', function(e) {
    const rosterModal = document.getElementById('modalStudentRoster');
    if (e.target === rosterModal) {
        closeStudentRosterModal();
    }
    const enrollModal = document.getElementById('modalEnrollStudent');
    if (e.target === enrollModal) {
        closeEnrollStudentModal();
    }
});

function handleDashViewClass(btn) {
    try {
        const data = JSON.parse(btn.getAttribute('data-record'));
        let html = `
            <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                <div style="padding:8px 12px; background:#F8FAFC; border-radius:6px;"><strong>Class ID:</strong> #${data.id}</div>
                <div style="padding:8px 12px; background:#F8FAFC; border-radius:6px;"><strong>Class Name:</strong> ${data.name}</div>
                <div style="padding:8px 12px; background:#F8FAFC; border-radius:6px;"><strong>Class Code:</strong> <code>${data.code}</code></div>
                <div style="padding:8px 12px; background:#F8FAFC; border-radius:6px;"><strong>Active Sections:</strong> ${data.section_count} Active Sections</div>
            </div>
        `;
        document.getElementById('viewClassModalBody').innerHTML = html;
        document.getElementById('viewClassModal').classList.add('active');
    } catch(e) {
        console.error("View Class Error:", e);
    }
}

function handleDashEditClass(btn) {
    try {
        const data = JSON.parse(btn.getAttribute('data-record'));
        document.getElementById('editClassId').value = data.id;
        document.getElementById('editClassName').value = data.name;
        document.getElementById('editClassCode').value = data.code;
        document.getElementById('editClassModal').classList.add('active');
    } catch(e) {
        console.error("Edit Class Error:", e);
    }
}

function openEditCourseModal(course) {
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_course_name').value = course.name;
    document.getElementById('edit_course_code').value = course.code;
    document.getElementById('edit_course_duration').value = course.duration_years || 3;
    document.getElementById('edit_course_dept_id').value = course.department_id || 0;
    document.getElementById('edit_course_status').value = course.status || 'active';
    document.getElementById('edit_course_description').value = course.description || '';
    document.getElementById('editModalCourseTitle').innerText = '✏️ Edit Course: ' + course.name;
    const modal = document.getElementById('editCourseModal');
    if (modal) modal.style.display = 'flex';
}

function closeEditCourseModal() {
    const modal = document.getElementById('editCourseModal');
    if (modal) modal.style.display = 'none';
}

// Live Auto-Poll for Automatic Data Updates (Pauses when a modal is active)
let lastStateHash = null;
function checkRealtimeUpdates() {
    fetch('index.php?page=api_dashboard_updates')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const isModalOpen = document.querySelector('.modal-overlay[style*="display: flex"], .modal-overlay.active');
                if (isModalOpen) return;
                if (lastStateHash && lastStateHash !== data.hash) {
                    window.location.reload();
                }
                lastStateHash = data.hash;
            }
        })
        .catch(err => console.debug('Sync check error:', err));
}
setInterval(checkRealtimeUpdates, 5000);
checkRealtimeUpdates();
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
