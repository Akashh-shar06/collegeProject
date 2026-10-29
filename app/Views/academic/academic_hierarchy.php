<?php 
require BASE_PATH . '/app/Views/layouts/header.php'; 
$canManage = !empty($canManage);
?>

<main class="dashboard-viewport">
    <!-- Header Banner -->
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFFFFF; padding:24px 28px; border-radius:14px; box-shadow:0 10px 25px -5px rgba(15,23,42,0.15);">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span style="font-size:26px;">🏛️</span>
                <h1 style="font-size:22px; font-weight:800; color:#FFFFFF; margin:0; line-height:1.2;">Academic Structure & Student Hierarchy</h1>
                <span style="background:rgba(99,102,241,0.25); color:#A5B4FC; border:1px solid rgba(165,180,252,0.3); font-size:11px; font-weight:700; padding:3px 10px; border-radius:12px; text-transform:uppercase;"><?= htmlspecialchars($user['role_display'] ?? ucwords(str_replace('_', ' ', $user['role_name'] ?? 'Administrator')), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!$canManage): ?>
                    <span style="background:rgba(16,185,129,0.2); color:#6EE7B7; border:1px solid rgba(110,231,183,0.3); font-size:11px; font-weight:700; padding:3px 10px; border-radius:12px;">VIEW ONLY</span>
                <?php endif; ?>
            </div>
            <p style="font-size:13px; color:#94A3B8; margin:0;">
                Department ➔ Degree Courses ➔ 3 Academic Years (FY, SY, TY) ➔ Student Cohort Roster
            </p>
        </div>
        <?php if ($canManage): ?>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="button" class="btn" onclick="openAddDeptModal()" style="background:#3B82F6; color:#FFF; font-weight:700; font-size:12px; padding:9px 16px; border-radius:8px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    🏛️ + Add Department
                </button>
                <button type="button" class="btn" onclick="openAddCourseModal()" style="background:#10B981; color:#FFF; font-weight:700; font-size:12px; padding:9px 16px; border-radius:8px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    🎓 + Add Course to Dept
                </button>
                <button type="button" class="btn" onclick="openEnrollStudentModal()" style="background:#6366F1; color:#FFF; font-weight:700; font-size:12px; padding:9px 16px; border-radius:8px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    👤 + Enroll Student (FY/SY/TY)
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div style="background:#DCFCE7; color:#166534; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #86EFAC; display:flex; align-items:center; gap:8px;">
            <span>✅</span> <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div style="background:#FEE2E2; color:#991B1B; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; margin-bottom:20px; border:1px solid #FCA5A5; display:flex; align-items:center; gap:8px;">
            <span>⚠️</span> <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- HIGH-LEVEL INSTITUTIONAL METRICS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:24px;">
        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">🏛️ Departments</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:4px;"><?= (int)($stats['total_departments'] ?? 0) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Active Academic Divisions</div>
        </div>

        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">🎓 Degree Courses</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:4px;"><?= (int)($stats['total_courses'] ?? 0) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Undergraduate Programs</div>
        </div>

        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;">🥇 FY (First Year)</div>
            <div style="font-size:24px; font-weight:800; color:#16A34A; margin-top:4px;"><?= (int)($stats['total_fy_students'] ?? 0) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Enrolled Students</div>
        </div>

        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#1E40AF; text-transform:uppercase;">🥈 SY (Second Year)</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:4px;"><?= (int)($stats['total_sy_students'] ?? 0) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Enrolled Students</div>
        </div>

        <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#7C3AED; text-transform:uppercase;">🥉 TY (Third Year)</div>
            <div style="font-size:24px; font-weight:800; color:#9333EA; margin-top:4px;"><?= (int)($stats['total_ty_students'] ?? 0) ?></div>
            <div style="font-size:11px; color:#475569; margin-top:2px;">Enrolled Students</div>
        </div>

        <div style="background:linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); border:1px solid #C7D2FE; border-radius:12px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:11px; font-weight:700; color:#4338CA; text-transform:uppercase;">👥 Total Students</div>
            <div style="font-size:24px; font-weight:800; color:#3730A3; margin-top:4px;"><?= (int)($stats['total_students'] ?? 0) ?></div>
            <div style="font-size:11px; color:#4338CA; margin-top:2px;">Across All Departments</div>
        </div>
    </div>

    <!-- DEPARTMENT FILTER SELECTOR TABS -->
    <div style="display:flex; gap:10px; margin-bottom:20px; overflow-x:auto; padding-bottom:4px;">
        <a href="index.php?page=academic_hierarchy&dept_id=all" 
           style="padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; white-space:nowrap; transition:all 0.2s; <?= (empty($selectedDeptId) || $selectedDeptId === 'all') ? 'background:#0F172A; color:#FFF;' : 'background:#FFF; color:#475569; border:1px solid #CBD5E1;' ?>">
            🏢 All Departments (<?= count($departments) ?>)
        </a>
        <?php foreach ($allDepartments as $ad): ?>
            <a href="index.php?page=academic_hierarchy&dept_id=<?= (int)$ad['id'] ?>" 
               style="padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; white-space:nowrap; transition:all 0.2s; <?= ((int)($selectedDeptId ?? 0) === (int)$ad['id']) ? 'background:#2563EB; color:#FFF;' : 'background:#FFF; color:#475569; border:1px solid #CBD5E1;' ?>">
                🏛️ <?= htmlspecialchars($ad['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($ad['code'] ?? 'DEPT', ENT_QUOTES, 'UTF-8') ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <!-- HIERARCHY ACCORDION & CARDS CONTAINER -->
    <?php if (empty($departments)): ?>
        <div style="background:#FFF; border:2px dashed #CBD5E1; border-radius:14px; padding:40px; text-align:center;">
            <div style="font-size:48px; margin-bottom:12px;">🏛️</div>
            <h3 style="font-size:18px; font-weight:800; color:#1E293B; margin-bottom:6px;">No Departments Found</h3>
            <p style="font-size:13px; color:#64748B; max-width:480px; margin:0 auto 18px auto;">
                As <?= htmlspecialchars($user['role_display'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?>, you can configure departments and multiple courses with 3 years (FY, SY, TY).
            </p>
            <button type="button" onclick="openAddDeptModal()" class="btn btn-primary" style="padding:10px 24px; font-weight:700; font-size:13px;">+ Add First Department</button>
        </div>
    <?php else: ?>
        <?php foreach ($departments as $deptIndex => $dept): ?>
            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; margin-bottom:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden;">
                <!-- Department Header -->
                <div style="background:linear-gradient(135deg, #1E293B 0%, #334155 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:20px;">🏛️</span>
                            <h2 style="font-size:18px; font-weight:800; color:#FFFFFF; margin:0;">
                                <?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <span style="background:rgba(255,255,255,0.2); color:#FFFFFF; padding:2px 8px; border-radius:6px; font-family:monospace; font-size:11px; font-weight:700;">
                                <?= htmlspecialchars($dept['code'] ?? 'DEPT', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div style="font-size:12px; color:#CBD5E1; margin-top:4px;">
                            <?= count($dept['courses']) ?> Course(s) Configured &bull; Total Enrolled Students: <?= (int)$dept['student_count'] ?>
                        </div>
                    </div>
                    <?php if ($canManage): ?>
                        <div style="display:flex; gap:8px;">
                            <button type="button" onclick="openAddCourseForDept(<?= (int)$dept['id'] ?>)" class="btn btn-sm" style="background:#10B981; color:#FFF; font-weight:700; font-size:11px; padding:6px 12px; border-radius:6px; border:none; cursor:pointer;">
                                🎓 + Add Course
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="padding:20px;">
                    <?php if (empty($dept['courses'])): ?>
                        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:24px; text-align:center;">
                            <p style="font-size:13px; color:#64748B; margin-bottom:12px;">No courses added yet in <strong><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
                            <?php if ($canManage): ?>
                                <button type="button" onclick="openAddCourseForDept(<?= (int)$dept['id'] ?>)" class="btn btn-primary" style="padding:8px 16px; font-size:12px; font-weight:700;">
                                    + Add First Course (with FY, SY, TY)
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:20px;">
                            <?php foreach ($dept['courses'] as $courseIndex => $course): ?>
                                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                                    <!-- Course Title Bar -->
                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; border-bottom:1px solid #E2E8F0; padding-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:18px;">🎓</span>
                                            <div>
                                                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">
                                                    <?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </h3>
                                                <div style="display:flex; gap:6px; align-items:center; margin-top:2px;">
                                                    <span style="background:#EFF6FF; color:#1D4ED8; font-family:monospace; font-size:11px; font-weight:700; padding:1px 6px; border-radius:4px;">
                                                        <?= htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    <span style="font-size:11px; color:#64748B;">
                                                        <?= (int)$course['duration_years'] ?>-Year Degree &bull; 3 Academic Levels: FY, SY, TY
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:8px; align-items:center;">
                                            <span style="background:#EEF2FF; color:#4F46E5; font-size:12px; font-weight:800; padding:4px 10px; border-radius:16px; border:1px solid #C7D2FE;">
                                                👥 <?= (int)$course['total_students'] ?> Total Students
                                            </span>
                                            <?php if ($canManage): ?>
                                                <button type="button" onclick="openEnrollForCourse(<?= (int)$dept['id'] ?>, <?= (int)$course['id'] ?>)" class="btn btn-sm" style="background:#4F46E5; color:#FFF; font-weight:700; font-size:11px; padding:6px 12px; border-radius:6px; border:none; cursor:pointer;">
                                                    + Enroll Student
                                                </button>
                                                <button type="button" onclick='openEditHierarchyCourse(<?= json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn btn-sm" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; font-weight:700; font-size:11px; padding:6px 10px; border-radius:6px; cursor:pointer;" title="Edit Course">
                                                    ✏️ Edit
                                                </button>
                                                <form method="POST" action="index.php?page=course_delete" onsubmit="return confirm('Are you sure you want to delete course: <?= htmlspecialchars($course['name'], ENT_QUOTES) ?> (<?= htmlspecialchars($course['code'], ENT_QUOTES) ?>)?');" style="margin:0; display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$course['id'] ?>">
                                                    <input type="hidden" name="redirect_page" value="academic_hierarchy&dept_id=<?= (int)$dept['id'] ?>">
                                                    <button type="submit" class="btn btn-sm" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; font-weight:700; font-size:11px; padding:6px 10px; border-radius:6px; cursor:pointer;" title="Delete Course">
                                                        🗑️ Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- 3 Years (FY, SY, TY) Tab Strip -->
                                    <?php 
                                        $cUniqueId = "c_{$dept['id']}_{$course['id']}";
                                    ?>
                                    <div style="display:flex; gap:8px; margin-bottom:12px; border-bottom:2px solid #E2E8F0; padding-bottom:6px;">
                                        <?php foreach ($course['years'] as $yIdx => $year): ?>
                                            <?php 
                                                $studentCount = count($year['students']);
                                                $pillColor = $year['code'] === 'FY' ? '#16A34A' : ($year['code'] === 'SY' ? '#2563EB' : '#9333EA');
                                                $bgColor = $year['code'] === 'FY' ? '#DCFCE7' : ($year['code'] === 'SY' ? '#DBEAFE' : '#F3E8FF');
                                            ?>
                                            <button type="button" 
                                                    id="tab_btn_<?= $cUniqueId ?>_<?= $year['order'] ?>"
                                                    onclick="switchYearTab('<?= $cUniqueId ?>', <?= $year['order'] ?>)"
                                                    style="padding:8px 16px; border-radius:8px 8px 0 0; font-size:13px; font-weight:800; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.15s; <?= $yIdx === 0 ? 'background:#0F172A; color:#FFF;' : 'background:#FFF; color:#475569; border:1px solid #CBD5E1; border-bottom:none;' ?>">
                                                <span><?= htmlspecialchars($year['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <span style="background:<?= $bgColor ?>; color:<?= $pillColor ?>; font-size:11px; padding:2px 7px; border-radius:12px; font-weight:800;">
                                                    <?= $studentCount ?>
                                                </span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Year Content Containers -->
                                    <?php foreach ($course['years'] as $yIdx => $year): ?>
                                        <div id="tab_pane_<?= $cUniqueId ?>_<?= $year['order'] ?>" style="<?= $yIdx === 0 ? 'display:block;' : 'display:none;' ?>">
                                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                                <div style="font-size:12px; font-weight:700; color:#475569;">
                                                    Enrolled in <strong><?= htmlspecialchars($year['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($year['semesters'], ENT_QUOTES, 'UTF-8') ?>) &bull; <?= count($year['students']) ?> Student(s)
                                                </div>
                                                <button type="button" onclick="openEnrollForCourseYear(<?= (int)$dept['id'] ?>, <?= (int)$course['id'] ?>, <?= (int)$year['order'] ?>)" style="background:none; border:none; color:#2563EB; font-weight:700; font-size:12px; cursor:pointer; text-decoration:underline;">
                                                    + Add Student to <?= htmlspecialchars($year['code'], ENT_QUOTES, 'UTF-8') ?>
                                                </button>
                                            </div>

                                            <?php if (empty($year['students'])): ?>
                                                <div style="background:#FFF; border:1px dashed #CBD5E1; border-radius:8px; padding:20px; text-align:center;">
                                                    <span style="font-size:12px; color:#64748B;">No students currently enrolled in <?= htmlspecialchars($year['name'], ENT_QUOTES, 'UTF-8') ?>.</span>
                                                    <div style="margin-top:8px;">
                                                        <button type="button" onclick="openEnrollForCourseYear(<?= (int)$dept['id'] ?>, <?= (int)$course['id'] ?>, <?= (int)$year['order'] ?>)" class="btn btn-sm btn-primary" style="font-size:11px; padding:5px 12px;">+ Enroll Student to <?= htmlspecialchars($year['code'], ENT_QUOTES, 'UTF-8') ?></button>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:8px; overflow-x:auto;">
                                                    <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                                                        <thead>
                                                            <tr style="background:#F1F5F9; color:#475569; border-bottom:1px solid #E2E8F0;">
                                                                <th style="padding:10px 12px;">ROLL NO</th>
                                                                <th style="padding:10px 12px;">STUDENT NAME</th>
                                                                <th style="padding:10px 12px;">ADMISSION NO</th>
                                                                <th style="padding:10px 12px;">CONTACT / EMAIL</th>
                                                                <th style="padding:10px 12px;">GENDER</th>
                                                                <th style="padding:10px 12px; text-align:center;">STATUS</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($year['students'] as $st): ?>
                                                                <tr style="border-bottom:1px solid #F1F5F9;">
                                                                    <td style="padding:10px 12px; font-weight:800; font-family:monospace; color:#1E293B;">
                                                                        <span style="background:#EEF2FF; color:#4338CA; padding:2px 8px; border-radius:4px;">
                                                                            <?= htmlspecialchars($st['roll_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                                                        </span>
                                                                    </td>
                                                                    <td style="padding:10px 12px;">
                                                                        <div style="display:flex; align-items:center; gap:8px;">
                                                                            <div style="width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg, #3B82F6, #1D4ED8); color:#FFF; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px;">
                                                                                <?= strtoupper(substr($st['first_name'], 0, 1)) ?>
                                                                            </div>
                                                                            <div>
                                                                                <strong style="color:#0F172A; font-size:13px; display:block;">
                                                                                    <?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'], ENT_QUOTES, 'UTF-8') ?>
                                                                                </strong>
                                                                                <span style="font-size:11px; color:#64748B;">@<?= htmlspecialchars($st['username'] ?? 'student', ENT_QUOTES, 'UTF-8') ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="padding:10px 12px; color:#334155; font-family:monospace;">
                                                                        <?= htmlspecialchars($st['admission_no'] ?? $st['unique_id'] ?? 'ADM', ENT_QUOTES, 'UTF-8') ?>
                                                                    </td>
                                                                    <td style="padding:10px 12px; color:#475569;">
                                                                        <div><?= htmlspecialchars($st['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                                                        <div style="font-size:10px; color:#94A3B8;"><?= htmlspecialchars($st['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                                                    </td>
                                                                    <td style="padding:10px 12px; text-transform:capitalize; color:#334155;">
                                                                        <?= htmlspecialchars($st['gender'] ?? 'male', ENT_QUOTES, 'UTF-8') ?>
                                                                    </td>
                                                                    <td style="padding:10px 12px; text-align:center;">
                                                                        <span style="background:#DCFCE7; color:#166534; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px; text-transform:uppercase;">
                                                                            <?= htmlspecialchars($st['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- MODAL 1: ADD DEPARTMENT -->
<div class="modal-overlay" id="modalAddDept" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:2000; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:520px; width:92%; background:#FFF; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px;">
                🏛️ Add New Department
            </h3>
            <button type="button" onclick="closeAddDeptModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=department_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="redirect_page" value="academic_hierarchy">
            <input type="hidden" name="id" value="0">
            <div style="padding:22px; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Science Department, Commerce Department" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Code / Abbreviation *</label>
                    <input type="text" name="code" required placeholder="e.g. SCI-01, COM-01" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Description</label>
                    <textarea name="description" rows="2" placeholder="Academic department description..." style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;"></textarea>
                </div>
            </div>
            <div style="background:#F8FAFC; padding:14px 22px; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeAddDeptModal()" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#2563EB; border:none; padding:8px 20px; font-weight:700;">+ Save Department & Auto-Seed FY, SY, TY</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: ADD COURSE TO DEPARTMENT -->
<div class="modal-overlay" id="modalAddCourse" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:2000; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:550px; width:92%; background:#FFF; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px;" id="modalAddCourseTitle">
                🎓 Add Course to Department
            </h3>
            <button type="button" onclick="closeAddCourseModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=course_save">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="redirect_page" value="academic_hierarchy">
            <input type="hidden" name="id" id="modal_course_id" value="0">
            <input type="hidden" name="status" id="modal_course_status" value="active">
            <div style="padding:22px; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Parent Department *</label>
                    <select name="department_id" id="modal_course_dept_id" required style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600; background:#FFF;">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($allDepartments as $d): ?>
                            <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course / Degree Name *</label>
                    <input type="text" name="name" id="modal_course_name" required placeholder="e.g. B.Sc. Information Technology, Bachelor of Commerce" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course Code *</label>
                        <input type="text" name="code" id="modal_course_code" required placeholder="e.g. BSCIT, BCOM" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Duration (Academic Years) *</label>
                        <select name="duration_years" id="modal_course_duration" required style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                            <option value="3" selected>3 Years (FY, SY, TY)</option>
                            <option value="2">2 Years (Master's / PG)</option>
                            <option value="4">4 Years (Honors / Engineering)</option>
                        </select>
                    </div>
                </div>
                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 14px; font-size:12px; color:#166534;">
                    ✨ <strong>Academic Provisioning:</strong> Saving this course configures the Academic Years (FY, SY, TY) and semesters dynamically.
                </div>
            </div>
            <div style="background:#F8FAFC; padding:14px 22px; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeAddCourseModal()" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
                <button type="submit" id="modal_course_submit_btn" class="btn btn-primary" style="background:#10B981; border:none; padding:8px 20px; font-weight:700;">+ Save Course</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: QUICK ENROLL STUDENT TO FY/SY/TY -->
<div class="modal-overlay" id="modalEnrollStudent" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:2000; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width:580px; width:92%; background:#FFF; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:#FFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px;">
                👤 Enroll Student into Course Year (FY, SY, TY)
            </h3>
            <button type="button" onclick="closeEnrollStudentModal()" style="background:none; border:none; color:#FFF; font-size:24px; cursor:pointer;">&times;</button>
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
                            <?php foreach ($allDepartments as $d): ?>
                                <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Course *</label>
                        <select name="course_id" id="enroll_course_id" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600; background:#FFF;">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($departments as $d): ?>
                                <?php foreach ($d['courses'] as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" data-dept-id="<?= (int)$d['id'] ?>">
                                        <?= htmlspecialchars($c['name'] . ' (' . $c['code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Step 2: Year Selection (FY, SY, TY) -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Academic Year / Level *</label>
                    <select name="year_order" id="enroll_year_order" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:700; background:#F8FAFC; color:#0F172A;">
                        <option value="1">🥇 First Year (FY) - Semester 1 & 2</option>
                        <option value="2">🥈 Second Year (SY) - Semester 3 & 4</option>
                        <option value="3">🥉 Third Year (TY) - Semester 5 & 6</option>
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
                        <input type="text" name="roll_no" placeholder="e.g. FYIT-04 (leave blank for auto)" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
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
                <button type="button" onclick="closeEnrollStudentModal()" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#6366F1; border:none; padding:8px 20px; font-weight:700;">✨ Complete Enrollment</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchYearTab(cKey, yrOrder) {
    // Hide all year panes for this course
    for (let i = 1; i <= 3; i++) {
        const pane = document.getElementById('tab_pane_' + cKey + '_' + i);
        const btn = document.getElementById('tab_btn_' + cKey + '_' + i);
        if (pane) {
            pane.style.display = (i === yrOrder) ? 'block' : 'none';
        }
        if (btn) {
            if (i === yrOrder) {
                btn.style.background = '#0F172A';
                btn.style.color = '#FFFFFF';
                btn.style.border = 'none';
            } else {
                btn.style.background = '#FFFFFF';
                btn.style.color = '#475569';
                btn.style.border = '1px solid #CBD5E1';
                btn.style.borderBottom = 'none';
            }
        }
    }
}

function openAddDeptModal() {
    document.getElementById('modalAddDept').style.display = 'flex';
}
function closeAddDeptModal() {
    document.getElementById('modalAddDept').style.display = 'none';
}

function openAddCourseModal() {
    document.getElementById('modal_course_id').value = '0';
    document.getElementById('modal_course_name').value = '';
    document.getElementById('modal_course_code').value = '';
    document.getElementById('modal_course_duration').value = '3';
    document.getElementById('modalAddCourseTitle').innerHTML = '🎓 Add Course to Department';
    document.getElementById('modal_course_submit_btn').innerText = '+ Save Course & Seed 3 Years';
    document.getElementById('modalAddCourse').style.display = 'flex';
}
function openAddCourseForDept(deptId) {
    openAddCourseModal();
    const sel = document.getElementById('modal_course_dept_id');
    if (sel) sel.value = deptId;
}
function openEditHierarchyCourse(course) {
    document.getElementById('modal_course_id').value = course.id;
    document.getElementById('modal_course_name').value = course.name;
    document.getElementById('modal_course_code').value = course.code;
    document.getElementById('modal_course_duration').value = course.duration_years || 3;
    document.getElementById('modal_course_dept_id').value = course.department_id || '';
    document.getElementById('modal_course_status').value = course.status || 'active';
    document.getElementById('modalAddCourseTitle').innerHTML = '✏️ Edit Course: ' + course.name;
    document.getElementById('modal_course_submit_btn').innerText = 'Update Course';
    document.getElementById('modalAddCourse').style.display = 'flex';
}
function closeAddCourseModal() {
    document.getElementById('modalAddCourse').style.display = 'none';
}

function openEnrollStudentModal() {
    document.getElementById('modalEnrollStudent').style.display = 'flex';
}
function openEnrollForCourse(deptId, courseId) {
    const dSel = document.getElementById('enroll_dept_id');
    const cSel = document.getElementById('enroll_course_id');
    if (dSel) dSel.value = deptId;
    syncEnrollCourses(deptId);
    if (cSel) cSel.value = courseId;
    document.getElementById('modalEnrollStudent').style.display = 'flex';
}
function openEnrollForCourseYear(deptId, courseId, yearOrder) {
    openEnrollForCourse(deptId, courseId);
    const ySel = document.getElementById('enroll_year_order');
    if (ySel) ySel.value = yearOrder;
}
function closeEnrollStudentModal() {
    document.getElementById('modalEnrollStudent').style.display = 'none';
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
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
