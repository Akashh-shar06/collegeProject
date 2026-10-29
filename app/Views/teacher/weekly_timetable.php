<?php
/**
 * Campus360 Enterprise ERP - Weekly Timetable Matrix
 * Dark navy dashboard layout matching UI/UX specification with Edit/Delete timings,
 * live duration calculation, interval-overlap conflict detection, and role-based access.
 */
$pageTitle = "College Weekly Timetable Schedule";
require BASE_PATH . '/app/Views/layouts/header.php';

$currentUrl = "index.php?page=teacher_timetable&date=" . urlencode($weekStartDate);
if (!empty($viewMode)) {
    $currentUrl .= "&view_mode=" . urlencode($viewMode);
}
if (!empty($selectedCourseKey) && $selectedCourseKey !== 'all') {
    $currentUrl .= "&course=" . urlencode($selectedCourseKey);
}
if (!empty($selectedYear) && $selectedYear !== 'ALL') {
    $currentUrl .= "&year=" . urlencode($selectedYear);
}
if (!empty($selectedTeacherId)) {
    $currentUrl .= "&teacher_id=" . (int)$selectedTeacherId;
}
if (!empty($selectedClassId)) {
    $currentUrl .= "&section_id=" . (int)$selectedClassId;
}
if (!empty($selectedSubjectId)) {
    $currentUrl .= "&subject_id=" . (int)$selectedSubjectId;
}
?>

<div class="container-fluid" style="padding: 24px; background: #0B0F19; min-height: 100vh; color: #F8FAFC; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    
    <!-- Floating Toast Notification Container -->
    <div id="timetableToastContainer" style="position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 420px; pointer-events: none;"></div>

    <!-- Flash Notifications (Server-Side) -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #34D399; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <span>✅</span> <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #F87171; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Student Enrolled Class Meta Banner (Student Login Only) -->
    <?php if ($role === 'student' && !empty($studentScope)): ?>
        <div style="background: linear-gradient(135deg, #1E1B4B 0%, #1E293B 100%); border: 1px solid #312E81; border-radius: 14px; padding: 18px 24px; margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.4);">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #6366F1; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; box-shadow: 0 4px 10px rgba(99,102,241,0.4);">
                    🎓
                </div>
                <div>
                    <div style="font-size: 17px; font-weight: 800; color: #FFFFFF;">
                        <?= htmlspecialchars(trim(($studentScope['first_name'] ?? '') . ' ' . ($studentScope['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        <span style="font-size: 11px; background: rgba(56, 189, 248, 0.2); color: #38BDF8; padding: 3px 8px; border-radius: 6px; margin-left: 8px; font-weight: 700;">
                            Roll No: <?= htmlspecialchars($studentScope['roll_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <div style="font-size: 13px; color: #94A3B8; margin-top: 4px;">
                        <span>Program: <strong style="color: #38BDF8;"><?= htmlspecialchars($studentScope['course_name'] ?? 'Degree Course', ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <span style="margin: 0 8px;">&bull;</span>
                        <span>Academic Year: <strong style="color: #FFFFFF;"><?= htmlspecialchars($studentScope['academic_year_code'] ?? 'FY', ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <span style="margin: 0 8px;">&bull;</span>
                        <span>Division: <strong style="color: #FFFFFF;"><?= htmlspecialchars($studentScope['class_name'] ?? 'Class', ENT_QUOTES, 'UTF-8') ?></strong></span>
                    </div>
                </div>
            </div>
            <div>
                <span style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #34D399; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🔒</span> Verified Class Schedule (View-Only)
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb, Title & Global Actions -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid #1E293B;">
        <div>
            <div style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <a href="index.php?page=dashboard" style="color: #94A3B8; text-decoration: none;">Dashboard</a>
                <span>/</span>
                <span style="color: #38BDF8;">Timetable Management</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span>🗓️</span> College Timetable Matrix
            </h1>
            <p style="font-size: 13px; color: #94A3B8; margin: 4px 0 0 0;">
                Active Schedule: <strong style="color: #38BDF8;"><?= htmlspecialchars($viewingTeacherName, ENT_QUOTES, 'UTF-8') ?></strong>
                | Academic Year: <strong style="color: #FFFFFF;">2026-2027</strong>
            </p>
        </div>

        <!-- Weekly Navigation Controls & Add Slot Trigger -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <?php if (!empty($canManageTimetable)): ?>
                <button type="button" onclick="openAddSlotModal()" class="btn" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); color: #FFFFFF; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(37,99,235,0.35); transition: all 0.2s;">
                    <span>➕</span> Add Lecture
                </button>
            <?php endif; ?>

            <!-- Week Switchers -->
            <a href="index.php?page=teacher_timetable&date=<?= $prevWeekDate ?>&view_mode=<?= urlencode($viewMode) ?>&course=<?= urlencode($selectedCourseKey) ?>&year=<?= urlencode($selectedYear) ?><?= $selectedTeacherId ? '&teacher_id='.$selectedTeacherId : '' ?><?= $selectedClassId ? '&section_id='.$selectedClassId : '' ?>" class="btn" style="background: #1E293B; border: 1px solid #334155; color: #E2E8F0; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                &larr; Prev Week
            </a>
            
            <a href="index.php?page=teacher_timetable&date=<?= date('Y-m-d') ?>&view_mode=<?= urlencode($viewMode) ?>&course=<?= urlencode($selectedCourseKey) ?>&year=<?= urlencode($selectedYear) ?><?= $selectedTeacherId ? '&teacher_id='.$selectedTeacherId : '' ?>" class="btn" style="background: <?= ($weekStartDate <= date('Y-m-d') && date('Y-m-d') <= $weekEndDate) ? '#2563EB' : '#1E293B' ?>; border: 1px solid <?= ($weekStartDate <= date('Y-m-d') && date('Y-m-d') <= $weekEndDate) ? '#2563EB' : '#334155' ?>; color: #FFFFFF; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 8px; text-decoration: none;">
                Today
            </a>

            <a href="index.php?page=teacher_timetable&date=<?= $nextWeekDate ?>&view_mode=<?= urlencode($viewMode) ?>&course=<?= urlencode($selectedCourseKey) ?>&year=<?= urlencode($selectedYear) ?><?= $selectedTeacherId ? '&teacher_id='.$selectedTeacherId : '' ?><?= $selectedClassId ? '&section_id='.$selectedClassId : '' ?>" class="btn" style="background: #1E293B; border: 1px solid #334155; color: #E2E8F0; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                Next Week &rarr;
            </a>

            <!-- Date Picker Jump -->
            <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                <input type="hidden" name="page" value="teacher_timetable">
                <input type="hidden" name="view_mode" value="<?= htmlspecialchars($viewMode) ?>">
                <input type="hidden" name="course" value="<?= htmlspecialchars($selectedCourseKey) ?>">
                <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">
                <?php if ($selectedTeacherId): ?><input type="hidden" name="teacher_id" value="<?= $selectedTeacherId ?>"><?php endif; ?>
                <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>" onchange="this.form.submit()" style="padding: 7px 12px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1E293B; color: #FFFFFF; font-family: inherit;">
            </form>
        </div>
    </div>

    <!-- View Mode Switcher & Academic Filter Hub -->
    <div style="background: #111827; border-radius: 14px; border: 1px solid #1F2937; padding: 18px 22px; margin-bottom: 22px; display: flex; flex-direction: column; gap: 16px;">
        
        <!-- Top Row: View Mode Switcher + Week Range -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; border-bottom: 1px solid #1E293B; padding-bottom: 14px;">
            <!-- View Mode Switcher Tabs -->
            <?php if ($role !== 'student'): ?>
                <div style="display: inline-flex; background: #0B0F19; border: 1px solid #1E293B; border-radius: 10px; padding: 4px; gap: 4px;">
                    <a href="index.php?page=teacher_timetable&date=<?= urlencode($weekStartDate) ?>&view_mode=teacher&course=<?= urlencode($selectedCourseKey) ?>&year=<?= urlencode($selectedYear) ?><?= $selectedTeacherId ? '&teacher_id='.$selectedTeacherId : '' ?>"
                       style="padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; <?= $viewMode === 'teacher' ? 'background: #2563EB; color: #FFFFFF; box-shadow: 0 2px 8px rgba(37,99,235,0.4);' : 'color: #94A3B8;' ?>">
                        <span>👨‍🏫</span> Teacher-wise View
                    </a>
                    <a href="index.php?page=teacher_timetable&date=<?= urlencode($weekStartDate) ?>&view_mode=class&course=<?= urlencode($selectedCourseKey) ?>&year=<?= urlencode($selectedYear) ?>"
                       style="padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; <?= $viewMode === 'class' ? 'background: #2563EB; color: #FFFFFF; box-shadow: 0 2px 8px rgba(37,99,235,0.4);' : 'color: #94A3B8;' ?>">
                        <span>👥</span> Class-wise View
                    </a>
                </div>
            <?php else: ?>
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #38BDF8;">
                    <span>👥</span> Enrolled Class View (<?= htmlspecialchars($studentScope['course_name'] ?? 'Degree') ?> &bull; <?= htmlspecialchars($studentScope['academic_year_code'] ?? 'FY') ?>)
                </div>
            <?php endif; ?>

            <!-- Week Date Display -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #1E293B; color: #38BDF8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    📅
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase;">Week Period</div>
                    <div style="font-size: 14px; font-weight: 800; color: #FFFFFF;">
                        <?= date('D, d M', strtotime($weekStartDate)) ?> &mdash; <?= date('D, d M Y', strtotime($weekEndDate)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: Course Selection Pills & FY/SY/TY Filter Pills -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <!-- Course Filter Pills -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; min-width: 70px;">Courses:</span>
                
                <?php 
                $coursePills = [
                    'all' => 'All Courses',
                    'bsc_cs' => 'BSc CS',
                    'bsc_it' => 'BSc IT',
                    'data_science' => 'Data Science',
                    'bca' => 'BCA',
                    'bcom' => 'BCom',
                    'bms' => 'BMS',
                ];
                foreach ($coursePills as $cKey => $cTitle): 
                    $isActive = ($selectedCourseKey === $cKey);
                    $url = "index.php?page=teacher_timetable&date=" . urlencode($weekStartDate) . "&view_mode=" . urlencode($viewMode) . "&course=" . urlencode($cKey) . "&year=" . urlencode($selectedYear);
                    if ($selectedTeacherId) $url .= "&teacher_id=" . $selectedTeacherId;
                ?>
                    <a href="<?= $url ?>" 
                       style="padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; transition: all 0.15s; border: 1px solid <?= $isActive ? '#2563EB' : '#1E293B' ?>; <?= $isActive ? 'background: #2563EB; color: #FFFFFF; box-shadow: 0 2px 8px rgba(37,99,235,0.4);' : 'background: #111827; color: #94A3B8;' ?>"
                       onmouseover="if (!<?= $isActive ? 'true' : 'false' ?>) { this.style.color='#FFFFFF'; this.style.borderColor='#334155'; }"
                       onmouseout="if (!<?= $isActive ? 'true' : 'false' ?>) { this.style.color='#94A3B8'; this.style.borderColor='#1E293B'; }">
                        <?= htmlspecialchars($cTitle) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- FY / SY / TY Selector Pills -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; min-width: 70px;">Year:</span>
                
                <?php 
                $yearPills = [
                    'ALL' => 'All Years',
                    'FY' => 'FY (First Year)',
                    'SY' => 'SY (Second Year)',
                    'TY' => 'TY (Third Year)',
                ];
                foreach ($yearPills as $yKey => $yTitle): 
                    $isYearActive = ($selectedYear === $yKey);
                    $url = "index.php?page=teacher_timetable&date=" . urlencode($weekStartDate) . "&view_mode=" . urlencode($viewMode) . "&course=" . urlencode($selectedCourseKey) . "&year=" . urlencode($yKey);
                    if ($selectedTeacherId) $url .= "&teacher_id=" . $selectedTeacherId;
                ?>
                    <a href="<?= $url ?>" 
                       style="padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; transition: all 0.15s; border: 1px solid <?= $isYearActive ? '#0EA5E9' : '#1E293B' ?>; <?= $isYearActive ? 'background: rgba(14, 165, 233, 0.2); color: #38BDF8; border-color: #0EA5E9;' : 'background: #111827; color: #94A3B8;' ?>"
                       onmouseover="if (!<?= $isYearActive ? 'true' : 'false' ?>) { this.style.color='#FFFFFF'; this.style.borderColor='#334155'; }"
                       onmouseout="if (!<?= $isYearActive ? 'true' : 'false' ?>) { this.style.color='#94A3B8'; this.style.borderColor='#1E293B'; }">
                        <?= htmlspecialchars($yTitle) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bottom Row: Specific Dropdown Selectors & Reset -->
        <?php if ($role !== 'student'): ?>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding-top: 10px; border-top: 1px solid #1E293B;">
                <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0; width: 100%;">
                    <input type="hidden" name="page" value="teacher_timetable">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($weekStartDate) ?>">
                    <input type="hidden" name="view_mode" value="<?= htmlspecialchars($viewMode) ?>">
                    <input type="hidden" name="course" value="<?= htmlspecialchars($selectedCourseKey) ?>">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">

                    <?php if ($isAdminRole || !empty($isHod)): ?>
                        <div style="min-width: 220px;">
                            <select name="teacher_id" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #334155; font-size: 13px; background: #1E293B; color: #FFFFFF; outline: none;">
                                <option value="all">-- All Faculty Members --</option>
                                <?php foreach ($teachersList as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($selectedTeacherId == $t['id']) ? 'selected' : '' ?>>
                                        👨‍🏫 Prof. <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . (!empty($t['emp_id']) ? ' (' . $t['emp_id'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div style="min-width: 180px;">
                        <select name="section_id" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #334155; font-size: 13px; background: #1E293B; color: #FFFFFF; outline: none;">
                            <option value="all">All Divisions</option>
                            <?php foreach ($sectionsList as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ($selectedClassId == $sec['id']) ? 'selected' : '' ?>>
                                    👥 <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="min-width: 180px;">
                        <select name="subject_id" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #334155; font-size: 13px; background: #1E293B; color: #FFFFFF; outline: none;">
                            <option value="all">All Subjects</option>
                            <?php foreach ($subjectsList as $sub): ?>
                                <option value="<?= $sub['id'] ?>" <?= ($selectedSubjectId == $sub['id']) ? 'selected' : '' ?>>
                                    📘 <?= htmlspecialchars($sub['name'] . (!empty($sub['code']) ? ' (' . $sub['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($selectedClassId || $selectedSubjectId || ($selectedTeacherId && $isAdminRole) || $selectedCourseKey !== 'all' || $selectedYear !== 'ALL'): ?>
                        <a href="index.php?page=teacher_timetable&date=<?= $weekStartDate ?>&view_mode=<?= urlencode($viewMode) ?>" style="font-size: 12px; font-weight: 700; color: #F87171; text-decoration: none; padding: 7px 12px; display: inline-flex; align-items: center; gap: 4px;">
                            ✕ Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Status Legend Guide -->
    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; font-size: 12px; flex-wrap: wrap; background: #070B14; padding: 10px 16px; border-radius: 10px; border: 1px solid #1E293B;">
        <span style="font-weight: 700; color: #94A3B8;">Schedule Guide:</span>
        <span style="display: inline-flex; align-items: center; gap: 6px; color: #34D399; font-weight: 600;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span> Attendance Marked
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; color: #FBBF24; font-weight: 600;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #F59E0B;"></span> Pending Attendance
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; color: #60A5FA; font-weight: 600;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #3B82F6;"></span> Upcoming Lecture
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; color: #C084FC; font-weight: 600;">
            <span style="width: 8px; height: 8px; border-radius: 2px; background: #A855F7;"></span> Theory
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; color: #38BDF8; font-weight: 600;">
            <span style="width: 8px; height: 8px; border-radius: 2px; background: #0284C7;"></span> Practical
        </span>
        <?php if (!empty($canManageTimetable)): ?>
            <span style="color: #38BDF8; margin-left: auto; font-weight: 600;">💡 Click ✏️ to edit timings/teacher or 🗑️ to delete lecture. Click any empty slot to add.</span>
        <?php else: ?>
            <span style="color: #94A3B8; margin-left: auto;">💡 Click any card to view classroom session and attendance details.</span>
        <?php endif; ?>
    </div>

    <!-- WEEKLY TIMETABLE GRID TABLE (DARK NAVY #030712) -->
    <div style="background: #030712; border-radius: 16px; border: 1px solid #1E293B; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.6); overflow-x: auto; margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1050px; text-align: left;">
            <thead>
                <tr style="background: #0B132B;">
                    <th style="padding: 14px 10px; width: 155px; font-size: 13px; font-weight: 800; color: #FFFFFF; text-transform: uppercase; letter-spacing: 0.5px; border-right: 1px solid #1E293B; border-bottom: 2px solid #1E293B; text-align: center;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                            <span>TIME SLOT</span>
                            <?php if (!empty($canManageTimetable)): ?>
                                <button type="button" onclick="openAddTimeSlotModal()" title="Add a new Time Slot Row" style="background: rgba(37, 99, 235, 0.25); border: 1px solid #3B82F6; color: #60A5FA; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 4px; cursor: pointer; text-transform: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='rgba(37, 99, 235, 0.45)';" onmouseout="this.style.background='rgba(37, 99, 235, 0.25)';">
                                    <span>➕ Add Slot</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </th>
                    <?php foreach ($weekdays as $dayName => $dateStr): 
                        $isToday = ($dateStr === date('Y-m-d'));
                    ?>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 800; color: #FFFFFF; border-right: 1px solid #1E293B; border-bottom: 2px solid #1E293B; <?= $isToday ? 'background: #13274F;' : '' ?>">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span><?= strtoupper($dayName) ?></span>
                                <?php if ($isToday): ?>
                                    <span style="background: #2563EB; color: #FFFFFF; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">TODAY</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 11px; font-weight: 500; color: #94A3B8; margin-top: 2px;">
                                <?= date('d M Y', strtotime($dateStr)) ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($periods)): ?>
                    <tr>
                        <td colspan="7" style="padding: 60px 20px; text-align: center; color: #64748B;">
                            <div style="font-size: 40px; margin-bottom: 10px;">📋</div>
                            <h4 style="font-size: 16px; font-weight: 700; color: #E2E8F0; margin-bottom: 4px;">No Lecture Periods Configured</h4>
                            <p style="font-size: 13px; margin: 0; color: #94A3B8;">No lecture periods exist for this schedule.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $periodCounter = 0;
                    foreach ($periods as $pKey => $pInfo): 
                        $periodCounter++;
                        $periodLabel = $pInfo['label'] ?? ('Period ' . $periodCounter);
                        $pStart = date('g:i A', strtotime($pInfo['start_time']));
                        $pEnd = date('g:i A', strtotime($pInfo['end_time']));
                        $pDuration = (int)round((strtotime($pInfo['end_time']) - strtotime($pInfo['start_time'])) / 60);

                        $rowLecCount = 0;
                        foreach ($weekdays as $dName => $dStr) {
                            $rowLecCount += count($matrix[$dName][$pKey] ?? []);
                        }
                    ?>
                        <tr id="row-period-<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>">
                            <!-- Left Period Header Band -->
                            <td style="padding: 16px 10px; background: #070B14; border-right: 1px solid #1E293B; border-bottom: 1px solid #1E293B; text-align: center; vertical-align: middle;">
                                <div style="font-size: 11px; font-weight: 800; color: #38BDF8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">
                                    <?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div style="font-size: 13px; font-weight: 800; color: #FFFFFF; line-height: 1.2;">
                                    <?= $pStart ?> &ndash; <?= $pEnd ?>
                                </div>
                                <div style="font-size: 10px; color: #64748B; margin-top: 4px; font-weight: 600;">
                                    ⏱️ <?= $pDuration ?> min
                                </div>

                                <?php if (!empty($canManageTimetable)): ?>
                                    <div style="margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <!-- Edit Row Button -->
                                        <button type="button" 
                                                onclick="openEditTimeSlotModal(<?= htmlspecialchars(json_encode([
                                                    'key' => $pKey,
                                                    'id' => $pInfo['id'] ?? 0,
                                                    'label' => $periodLabel,
                                                    'start_time' => substr($pInfo['start_time'], 0, 5),
                                                    'end_time' => substr($pInfo['end_time'], 0, 5),
                                                    'duration' => $pDuration,
                                                    'lecture_count' => $rowLecCount
                                                ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                title="Edit this Time Slot Row (Timings & Label)"
                                                style="background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.4); color: #38BDF8; padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;"
                                                onmouseover="this.style.background='rgba(56, 189, 248, 0.28)'; this.style.transform='scale(1.05)';"
                                                onmouseout="this.style.background='rgba(56, 189, 248, 0.12)'; this.style.transform='none';">
                                            <span>✏️</span> <span>Edit</span>
                                        </button>

                                        <!-- Delete Row Button -->
                                        <button type="button" 
                                                onclick="openDeleteTimeSlotModal(<?= htmlspecialchars(json_encode([
                                                    'key' => $pKey,
                                                    'id' => $pInfo['id'] ?? 0,
                                                    'label' => $periodLabel,
                                                    'start_time' => $pInfo['start_time'],
                                                    'end_time' => $pInfo['end_time'],
                                                    'time_display' => $pStart . ' – ' . $pEnd,
                                                    'duration' => $pDuration,
                                                    'lecture_count' => $rowLecCount
                                                ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                title="Delete entire row and all scheduled lectures"
                                                style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #F87171; padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;"
                                                onmouseover="this.style.background='rgba(239, 68, 68, 0.28)'; this.style.transform='scale(1.05)';"
                                                onmouseout="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.transform='none';">
                                            <span>🗑️</span> <span>Delete</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 6 Weekday Cells (Monday to Saturday) -->
                            <?php foreach ($weekdays as $dayName => $dateStr): 
                                $isToday = ($dateStr === date('Y-m-d'));
                                $cellLectures = $matrix[$dayName][$pKey] ?? [];
                            ?>
                                <td style="padding: 10px; border-right: 1px solid #1E293B; border-bottom: 1px solid #1E293B; vertical-align: top; min-width: 170px; <?= $isToday ? 'background: rgba(30, 58, 138, 0.08);' : '#070C16' ?>">
                                    <?php if (!empty($cellLectures)): ?>
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <?php foreach ($cellLectures as $card): 
                                                $cohortDisplay = $card['course_cohort_display'] ?? 'Degree · FY';
                                                $teacherName = trim(($card['teacher_first_name'] ?? '') . ' ' . ($card['teacher_last_name'] ?? ''));
                                                if (empty($teacherName)) $teacherName = 'Faculty';
                                                $lecType = strtolower($card['lecture_type'] ?? 'theory');
                                                $durationMins = $card['duration_minutes'] ?? 60;
                                                $cardStartTime = date('g:i A', strtotime($card['start_time']));
                                                $cardEndTime = date('g:i A', strtotime($card['end_time']));
                                            ?>
                                                <!-- Dark Navy Subject Card (#141E30) -->
                                                <div class="timetable-lecture-card" 
                                                     id="lecture-card-<?= (int)$card['id'] ?>"
                                                     style="background: #141E30; border: 1.5px solid #233148; border-radius: 12px; padding: 12px 14px; transition: all 0.2s ease; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.35);"
                                                     onmouseover="this.style.borderColor='#38BDF8'; this.style.background='#1A263D'; this.style.transform='translateY(-2px)';"
                                                     onmouseout="this.style.borderColor='#233148'; this.style.background='#141E30'; this.style.transform='none';">
                                                    
                                                    <!-- Line 1: Subject Name + Lecture Type Badge -->
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; margin-bottom: 4px;">
                                                        <div style="font-size: 13.5px; font-weight: 700; color: #FFFFFF; line-height: 1.3;">
                                                            <?= htmlspecialchars($card['resolved_subject_name'] ?? $card['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?>
                                                        </div>
                                                        <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; flex-shrink: 0; <?= $lecType === 'practical' ? 'background: rgba(14, 165, 233, 0.2); color: #38BDF8;' : ($lecType === 'tutorial' ? 'background: rgba(245, 158, 11, 0.2); color: #FBBF24;' : 'background: rgba(168, 85, 247, 0.2); color: #C084FC;') ?>">
                                                            <?= htmlspecialchars(ucfirst($lecType)) ?>
                                                        </span>
                                                    </div>

                                                    <!-- Line 2: Degree Course and Year Cohort in Muted Slate -->
                                                    <div style="font-size: 11.5px; font-weight: 600; color: #94A3B8; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                                        <span>🎓 <?= htmlspecialchars($cohortDisplay, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <span style="font-size: 10px; color: #64748B;">⏱️ <?= $durationMins ?>m</span>
                                                    </div>

                                                    <!-- Line 3: Teacher Name in Sky Blue (#38BDF8) + Action Icons -->
                                                    <div style="font-size: 12px; font-weight: 600; color: #38BDF8; display: flex; align-items: center; justify-content: space-between;">
                                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 130px;">
                                                            👨‍🏫 Prof. <?= htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                        
                                                        <?php if (!empty($canManageTimetable)): ?>
                                                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                                <!-- Edit Action Trigger -->
                                                                <button type="button" 
                                                                        onclick="event.stopPropagation(); openEditLectureModal(<?= htmlspecialchars(json_encode($card), ENT_QUOTES, 'UTF-8') ?>);" 
                                                                        title="Edit Lecture Details & Timings"
                                                                        style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38BDF8; width: 26px; height: 26px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.15s;"
                                                                        onmouseover="this.style.background='rgba(56, 189, 248, 0.35)'; this.style.transform='scale(1.1)';" 
                                                                        onmouseout="this.style.background='rgba(56, 189, 248, 0.15)'; this.style.transform='none';">
                                                                    ✏️
                                                                </button>
                                                                <!-- Delete Action Trigger -->
                                                                <button type="button" 
                                                                        onclick="event.stopPropagation(); openDeleteLectureConfirmModal(<?= htmlspecialchars(json_encode($card), ENT_QUOTES, 'UTF-8') ?>);" 
                                                                        title="Delete Lecture"
                                                                        style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #F87171; width: 26px; height: 26px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.15s;"
                                                                        onmouseover="this.style.background='rgba(239, 68, 68, 0.35)'; this.style.transform='scale(1.1)';" 
                                                                        onmouseout="this.style.background='rgba(239, 68, 68, 0.15)'; this.style.transform='none';">
                                                                    🗑️
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Line 4: Timing & Duration -->
                                                    <div style="font-size: 11px; color: #CBD5E1; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                                        <span>⏰ <?= $cardStartTime ?> &ndash; <?= $cardEndTime ?></span>
                                                    </div>

                                                    <!-- Bottom Micro-Bar: Room & Session Link -->
                                                    <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #64748B;">
                                                        <span>📍 <?= htmlspecialchars($card['classroom_number'] ?? $card['room_no'] ?? 'Room 101', ENT_QUOTES, 'UTF-8') ?></span>
                                                        <a href="index.php?page=lecture_details&id=<?= (int)$card['id'] ?>&date=<?= $dateStr ?>" style="color: #60A5FA; text-decoration: none; font-weight: 600;">
                                                            Session &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Empty Cell -->
                                        <?php if (!empty($canManageTimetable)): ?>
                                            <div onclick="openAddSlotModal('<?= $dayName ?>', '<?= $pInfo['start_time'] ?>', '<?= $pInfo['end_time'] ?>', '<?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') ?>', '<?= $dateStr ?>')"
                                                 title="Click to schedule a lecture here"
                                                 style="min-height: 85px; border: 1px dashed #1E293B; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; color: #475569; transition: all 0.2s; font-size: 11px; gap: 4px;"
                                                 onmouseover="this.style.borderColor='#38BDF8'; this.style.color='#38BDF8'; this.style.background='rgba(56,189,248,0.05)';"
                                                 onmouseout="this.style.borderColor='#1E293B'; this.style.color='#475569'; this.style.background='transparent';">
                                                <span style="font-size: 18px; line-height: 1;">+</span>
                                                <span style="font-weight: 700;">Add Slot</span>
                                            </div>
                                        <?php else: ?>
                                            <div style="height: 100%; min-height: 75px; display: flex; align-items: center; justify-content: center; color: #334155; font-size: 16px;">
                                                &mdash;
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT LECTURE MODAL (Interactive with live duration calculation & conflict validation) -->
<!-- ========================================================================= -->
<div id="editLectureModal" onclick="if(event.target === this) closeEditLectureModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.75); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #1F2937; border-radius:16px; width:100%; max-width:660px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.6); overflow:hidden; color:#F8FAFC;">
        
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #1E293B 100%); padding:18px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #334155;">
            <div>
                <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF; display:flex; align-items:center; gap:8px;">
                    <span>⚙️</span> Edit Lecture Timetable Slot
                </h3>
                <div style="font-size:12px; color:#93C5FD; margin-top:2px;">Modify lecture timings, assigned faculty, classroom, or delete this slot</div>
            </div>
            <button type="button" onclick="closeEditLectureModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <!-- Form for Updating Slot -->
        <form id="editLectureForm" onsubmit="submitEditLectureAjax(event)" style="padding: 20px 24px; overflow-y: auto; flex: 1; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            <input type="hidden" name="id" id="edit_lecture_id" value="">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="academic_year" value="2026-2027">
            <input type="hidden" name="subject_name" id="edit_lecture_subject_name" value="">
            <input type="hidden" name="ajax" value="1">

            <!-- Conflict & Error Alert Box -->
            <div id="editModalAlert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #FCA5A5; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600; line-height: 1.4;">
                <span id="editModalAlertText"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <!-- 1. Assigned Teacher -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        👨‍🏫 Assigned Faculty *
                    </label>
                    <select name="staff_id" id="edit_lecture_staff_id" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="">-- Select Faculty Member --</option>
                        <?php foreach ($teachersList as $t): ?>
                            <option value="<?= $t['id'] ?>">
                                Prof. <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . (!empty($t['emp_id']) ? ' (' . $t['emp_id'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Subject -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📘 Subject / Course Name *
                    </label>
                    <select name="subject_id" id="edit_lecture_subject_id" onchange="syncEditSubjectName(this)" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjectsList as $sub): ?>
                            <option value="<?= $sub['id'] ?>" data-name="<?= htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($sub['name'] . (!empty($sub['code']) ? ' (' . $sub['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Course Program & Academic Year -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🎓 Degree Course *
                    </label>
                    <select name="course_id" id="edit_lecture_course_id" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] . (!empty($c['code']) ? ' (' . $c['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📅 Academic Year *
                    </label>
                    <select name="standard_id" id="edit_lecture_standard_id" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="1">FY (First Year)</option>
                        <option value="2">SY (Second Year)</option>
                        <option value="3">TY (Third Year)</option>
                    </select>
                </div>

                <!-- 4. Day of Week & Classroom -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📅 Day of Week *
                    </label>
                    <select name="day_of_week" id="edit_lecture_day" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📍 Classroom / Room *
                    </label>
                    <input type="text" name="room_no" id="edit_lecture_room_no" required placeholder="e.g. Block 101" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- 5. Start Time & End Time -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        ⏰ Start Time *
                    </label>
                    <input type="time" name="start_time" id="edit_lecture_start_time" onchange="calculateDuration('edit')" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        ⏰ End Time *
                    </label>
                    <input type="time" name="end_time" id="edit_lecture_end_time" onchange="calculateDuration('edit')" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- 6. Live Duration Calculator Pill -->
                <div style="grid-column: span 2; background: #0B0F19; border: 1px solid #1E293B; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span>⏱️</span>
                        <span style="color: #94A3B8;">Calculated Duration:</span>
                        <strong id="edit_lecture_duration_display" style="color: #38BDF8;">60 minutes</strong>
                    </div>
                    <div style="font-size: 11px; color: #64748B;">College Hours: 06:00 AM &ndash; 09:00 PM</div>
                </div>

                <!-- 7. Lecture Type & Period Band Label -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🏷️ Lecture Type *
                    </label>
                    <select name="lecture_type" id="edit_lecture_type" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="theory">Theory Lecture</option>
                        <option value="practical">Practical / Lab Session</option>
                        <option value="tutorial">Tutorial</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📌 Period Band Label
                    </label>
                    <input type="text" name="time_slot" id="edit_lecture_time_slot" placeholder="e.g. 08:00 AM - 09:00 AM" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- 8. Notes -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📝 Notes / Instructions (Optional)
                    </label>
                    <input type="text" name="notes" id="edit_lecture_notes" placeholder="e.g. Lab batch A, Bring reference materials" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>
            </div>

            <!-- Footer Buttons -->
            <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid #1F2937; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <!-- Delete Trigger Button -->
                <button type="button" onclick="triggerDeleteFromEditModal()" class="btn" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #F87171; font-size: 12px; font-weight: 700; padding: 9px 16px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🗑️</span> Delete Lecture
                </button>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeEditLectureModal()" class="btn" style="background: #334155; color: #E2E8F0; border: none; font-size: 12px; font-weight: 700; padding: 9px 16px; border-radius: 8px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" id="editLectureSubmitBtn" class="btn" style="background: #2563EB; color: #FFFFFF; border: none; font-size: 12px; font-weight: 700; padding: 9px 22px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DELETE CONFIRMATION DIALOG (With Subject, Class, Teacher, Date & Time) -->
<!-- ========================================================================= -->
<div id="deleteLectureConfirmModal" onclick="if(event.target === this) closeDeleteLectureConfirmModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.75); backdrop-filter:blur(4px); z-index:1060; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #EF4444; border-radius:16px; width:100%; max-width:480px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(239, 68, 68, 0.25); overflow:hidden; color:#F8FAFC;">
        
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #7F1D1D 0%, #1F2937 100%); padding:18px 22px; display:flex; align-items:center; gap:12px; border-bottom: 1px solid #374151;">
            <div style="font-size: 24px;">⚠️</div>
            <div>
                <h3 style="font-size:16px; font-weight:800; margin:0; color:#FFFFFF;">Confirm Lecture Deletion</h3>
                <div style="font-size:12px; color:#FCA5A5; margin-top:2px;">This timetable slot will be permanently removed</div>
            </div>
        </div>

        <!-- Body Details -->
        <div style="padding: 20px 22px;">
            <p style="font-size: 13px; color: #CBD5E1; margin: 0 0 16px 0; line-height: 1.5;">
                Are you sure you want to delete this lecture? Review the lecture details below:
            </p>

            <div style="background: #0B0F19; border: 1px solid #1E293B; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #94A3B8;">Subject:</span>
                    <strong id="del_confirm_subject" style="color: #FFFFFF;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #94A3B8;">Class / Cohort:</span>
                    <strong id="del_confirm_cohort" style="color: #38BDF8;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #94A3B8;">Faculty:</span>
                    <strong id="del_confirm_teacher" style="color: #FFFFFF;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #94A3B8;">Day & Time:</span>
                    <strong id="del_confirm_time" style="color: #FBBF24;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #94A3B8;">Classroom:</span>
                    <strong id="del_confirm_room" style="color: #FFFFFF;">-</strong>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div style="padding: 14px 22px; background: #0B0F19; border-top: 1px solid #1F2937; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="closeDeleteLectureConfirmModal()" class="btn" style="background: #334155; color: #E2E8F0; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer;">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn" onclick="executeDeleteLectureAjax()" class="btn" style="background: #DC2626; color: #FFFFFF; border: none; font-size: 13px; font-weight: 700; padding: 9px 20px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>Confirm Delete</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ADD LECTURE MODAL (Interactive with conflict validation) -->
<!-- ========================================================================= -->
<div id="addLectureModal" onclick="if(event.target === this) closeAddLectureModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.75); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #1F2937; border-radius:16px; width:100%; max-width:660px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.6); overflow:hidden; color:#F8FAFC;">
        
        <div style="background:linear-gradient(135deg, #065F46 0%, #1E293B 100%); padding:18px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #334155;">
            <div>
                <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF; display:flex; align-items:center; gap:8px;">
                    <span>➕</span> Schedule New Lecture Slot
                </h3>
                <div style="font-size:12px; color:#A7F3D0; margin-top:2px;">Create a new lecture entry for your department schedule</div>
            </div>
            <button type="button" onclick="closeAddLectureModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <form id="addLectureForm" onsubmit="submitAddLectureAjax(event)" style="padding: 20px 24px; overflow-y: auto; flex: 1; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="academic_year" value="2026-2027">
            <input type="hidden" name="subject_name" id="add_lecture_subject_name" value="">
            <input type="hidden" name="ajax" value="1">

            <!-- Conflict Alert Box -->
            <div id="addModalAlert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #FCA5A5; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600; line-height: 1.4;">
                <span id="addModalAlertText"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <!-- Teacher -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        👨‍🏫 Assigned Teacher *
                    </label>
                    <select name="staff_id" id="add_lecture_staff_id" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="">-- Select Faculty Member --</option>
                        <?php foreach ($teachersList as $t): ?>
                            <option value="<?= $t['id'] ?>">
                                Prof. <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . (!empty($t['emp_id']) ? ' (' . $t['emp_id'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subject -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📘 Subject Name *
                    </label>
                    <select name="subject_id" id="add_lecture_subject_id" onchange="syncAddSubjectName(this)" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjectsList as $sub): ?>
                            <option value="<?= $sub['id'] ?>" data-name="<?= htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($sub['name'] . (!empty($sub['code']) ? ' (' . $sub['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Course & Academic Year -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🎓 Degree Course *
                    </label>
                    <select name="course_id" id="add_lecture_course_id" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (!empty($selectedCourseId) && $selectedCourseId == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'] . (!empty($c['code']) ? ' (' . $c['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📅 Academic Year *
                    </label>
                    <select name="standard_id" id="add_lecture_standard_id" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="1" <?= ($selectedYear === 'FY') ? 'selected' : '' ?>>FY (First Year)</option>
                        <option value="2" <?= ($selectedYear === 'SY') ? 'selected' : '' ?>>SY (Second Year)</option>
                        <option value="3" <?= ($selectedYear === 'TY') ? 'selected' : '' ?>>TY (Third Year)</option>
                    </select>
                </div>

                <!-- Day of Week & Classroom -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📅 Day of Week *
                    </label>
                    <select name="day_of_week" id="add_lecture_day" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📍 Classroom / Room *
                    </label>
                    <input type="text" name="room_no" id="add_lecture_room_no" value="Block 101" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- Start & End Time -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        ⏰ Start Time *
                    </label>
                    <input type="time" name="start_time" id="add_lecture_start_time" value="08:00" onchange="calculateDuration('add')" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        ⏰ End Time *
                    </label>
                    <input type="time" name="end_time" id="add_lecture_end_time" value="09:00" onchange="calculateDuration('add')" required style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- Live Duration Calculator -->
                <div style="grid-column: span 2; background: #0B0F19; border: 1px solid #1E293B; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span>⏱️</span>
                        <span style="color: #94A3B8;">Calculated Duration:</span>
                        <strong id="add_lecture_duration_display" style="color: #34D399;">60 minutes</strong>
                    </div>
                    <div style="font-size: 11px; color: #64748B;">College Hours: 06:00 AM &ndash; 09:00 PM</div>
                </div>

                <!-- Lecture Type & Time Slot Label -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🏷️ Lecture Type *
                    </label>
                    <select name="lecture_type" id="add_lecture_type" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                        <option value="theory">Theory Lecture</option>
                        <option value="practical">Practical / Lab Session</option>
                        <option value="tutorial">Tutorial</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🏷️ Slot Band Label
                    </label>
                    <input type="text" name="time_slot" id="add_lecture_time_slot" value="08:00 AM - 09:00 AM" placeholder="e.g. 08:00 AM - 09:00 AM" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <!-- Notes -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        📝 Notes / Instructions (Optional)
                    </label>
                    <input type="text" name="notes" id="add_lecture_notes" placeholder="e.g. Regular theory session" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>
            </div>

            <!-- Footer Buttons -->
            <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid #1F2937; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAddLectureModal()" class="btn" style="background: #334155; color: #E2E8F0; border: none; font-size: 12px; font-weight: 700; padding: 9px 16px; border-radius: 8px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="addLectureSubmitBtn" class="btn" style="background: #10B981; color: #FFFFFF; border: none; font-size: 12px; font-weight: 700; padding: 9px 22px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>Create Slot</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT TIME SLOT ROW MODAL -->
<!-- ========================================================================= -->
<div id="editTimeSlotModal" onclick="if(event.target === this) closeEditTimeSlotModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.78); backdrop-filter:blur(4px); z-index:1060; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #1E293B; border-radius:16px; width:100%; max-width:520px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.7); overflow:hidden; color:#F8FAFC;">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #0F172A 100%); padding:18px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #334155;">
            <div>
                <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF; display:flex; align-items:center; gap:8px;">
                    <span>✏️</span> Edit Time Slot Row
                </h3>
                <div style="font-size:12px; color:#93C5FD; margin-top:3px;">Update timing and label for this entire timetable row</div>
            </div>
            <button type="button" onclick="closeEditTimeSlotModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <!-- Form Body -->
        <form id="editTimeSlotForm" onsubmit="submitEditTimeSlotAjax(event)" style="padding: 22px 24px; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            <input type="hidden" name="period_key" id="edit_timeslot_key" value="">
            <input type="hidden" name="old_start_time" id="edit_timeslot_old_st" value="">
            <input type="hidden" name="old_end_time" id="edit_timeslot_old_et" value="">

            <div id="editTimeSlotAlert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #FCA5A5; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
                <span id="editTimeSlotAlertText"></span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🏷️ Period / Time Slot Label *
                    </label>
                    <input type="text" name="period_label" id="edit_timeslot_label" required placeholder="e.g. Period 1, Zero Period, Morning Slot" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                            ⏰ Start Time *
                        </label>
                        <input type="time" name="start_time" id="edit_timeslot_start_time" required onchange="calculateTimeSlotDuration('edit')" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                            ⏰ End Time *
                        </label>
                        <input type="time" name="end_time" id="edit_timeslot_end_time" required onchange="calculateTimeSlotDuration('edit')" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                    </div>
                </div>

                <div style="background: #0B0F19; border: 1px solid #1E293B; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span>⏱️</span>
                        <span style="color: #94A3B8;">Calculated Duration:</span>
                        <strong id="edit_timeslot_duration_display" style="color: #38BDF8;">60 minutes</strong>
                    </div>
                </div>

                <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #93C5FD; line-height: 1.4;">
                    💡 <strong>Sync Note:</strong> Saving will update this time slot row and automatically synchronize start & end timings for all lectures currently scheduled in this row across all weekdays.
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditTimeSlotModal()" style="background: #334155; color: #E2E8F0; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="editTimeSlotSubmitBtn" style="background: #2563EB; color: #FFFFFF; border: none; font-size: 13px; font-weight: 700; padding: 9px 20px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DELETE TIME SLOT ROW CONFIRMATION MODAL -->
<!-- ========================================================================= -->
<div id="deleteTimeSlotModal" onclick="if(event.target === this) closeDeleteTimeSlotModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.8); backdrop-filter:blur(4px); z-index:1060; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #7F1D1D; border-radius:16px; width:100%; max-width:520px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.8); overflow:hidden; color:#F8FAFC;">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #7F1D1D 0%, #111827 100%); padding:18px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #991B1B;">
            <div>
                <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF; display:flex; align-items:center; gap:8px;">
                    <span>🗑️</span> Delete Entire Time Slot Row
                </h3>
                <div style="font-size:12px; color:#FCA5A5; margin-top:3px;">Permanently remove this time slot row from the timetable</div>
            </div>
            <button type="button" onclick="closeDeleteTimeSlotModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 22px 24px;">
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid #EF4444; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; color: #FCA5A5; font-size: 13px; line-height: 1.5;">
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span> Permanent Row Deletion
                </div>
                <div>Are you sure you want to delete this row? This action will delete the entire row and all lectures scheduled in this time slot across Monday to Saturday.</div>
            </div>

            <div style="background: #0B0F19; border: 1px solid #1E293B; border-radius: 10px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: #94A3B8;">Period Label:</span>
                    <strong id="del_timeslot_label_display" style="color: #38BDF8;">Period 6</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: #94A3B8;">Timing:</span>
                    <strong id="del_timeslot_time_display" style="color: #FFFFFF;">7:10 AM &ndash; 8:00 AM</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: #94A3B8;">Scheduled Lectures to Delete:</span>
                    <strong id="del_timeslot_count_display" style="color: #F87171;">3 lecture(s)</strong>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 14px 24px; background: #0B0F19; border-top: 1px solid #1F2937; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="closeDeleteTimeSlotModal()" style="background: #334155; color: #E2E8F0; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer;">
                Cancel
            </button>
            <button type="button" id="confirmDeleteTimeSlotBtn" onclick="executeDeleteTimeSlotAjax()" style="background: #DC2626; color: #FFFFFF; border: none; font-size: 13px; font-weight: 700; padding: 9px 20px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>🗑️ Yes, Delete Entire Row</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ADD TIME SLOT ROW MODAL -->
<!-- ========================================================================= -->
<div id="addTimeSlotModal" onclick="if(event.target === this) closeAddTimeSlotModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 0, 0, 0.78); backdrop-filter:blur(4px); z-index:1060; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#111827; border: 1px solid #1E293B; border-radius:16px; width:100%; max-width:520px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.7); overflow:hidden; color:#F8FAFC;">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #065F46 0%, #0F172A 100%); padding:18px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #334155;">
            <div>
                <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF; display:flex; align-items:center; gap:8px;">
                    <span>➕</span> Add New Time Slot Row
                </h3>
                <div style="font-size:12px; color:#A7F3D0; margin-top:3px;">Add a new lecture period row to your weekly timetable</div>
            </div>
            <button type="button" onclick="closeAddTimeSlotModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <form id="addTimeSlotForm" onsubmit="submitAddTimeSlotAjax(event)" style="padding: 22px 24px; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">

            <div id="addTimeSlotAlert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #FCA5A5; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
                <span id="addTimeSlotAlertText"></span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                        🏷️ Period / Time Slot Label *
                    </label>
                    <input type="text" name="period_label" id="new_timeslot_label" required placeholder="e.g. Period 7, Remedial Slot, Evening Session" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                            ⏰ Start Time *
                        </label>
                        <input type="time" name="start_time" id="new_timeslot_start_time" value="13:00" required onchange="calculateTimeSlotDuration('add')" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px; text-transform: uppercase;">
                            ⏰ End Time *
                        </label>
                        <input type="time" name="end_time" id="new_timeslot_end_time" value="14:00" required onchange="calculateTimeSlotDuration('add')" style="width: 100%; padding: 10px 14px; border: 1px solid #334155; border-radius: 8px; font-size: 13px; background: #1F2937; color: #FFFFFF; outline: none;">
                    </div>
                </div>

                <div style="background: #0B0F19; border: 1px solid #1E293B; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span>⏱️</span>
                        <span style="color: #94A3B8;">Calculated Duration:</span>
                        <strong id="new_timeslot_duration_display" style="color: #34D399;">60 minutes</strong>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAddTimeSlotModal()" style="background: #334155; color: #E2E8F0; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="addTimeSlotSubmitBtn" style="background: #059669; color: #FFFFFF; border: none; font-size: 13px; font-weight: 700; padding: 9px 20px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>Add Time Slot</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLERS FOR TIMETABLE ACTIONS & LIVE CALCULATIONS -->
<!-- ========================================================================= -->
<script>
let currentDeleteCandidate = null;

// Toast helper
function showToast(message, type = 'success') {
    const container = document.getElementById('timetableToastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.style.pointerEvents = 'auto';
    toast.style.padding = '12px 18px';
    toast.style.borderRadius = '10px';
    toast.style.fontSize = '13px';
    toast.style.fontWeight = '700';
    toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.5)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '8px';
    toast.style.transition = 'all 0.3s ease';

    if (type === 'success') {
        toast.style.background = '#065F46';
        toast.style.border = '1px solid #10B981';
        toast.style.color = '#ECFDF5';
        toast.innerHTML = '<span>✅</span> ' + message;
    } else {
        toast.style.background = '#7F1D1D';
        toast.style.border = '1px solid #EF4444';
        toast.style.color = '#FEF2F2';
        toast.innerHTML = '<span>⚠️</span> ' + message;
    }

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}

// Calculate duration live
function calculateDuration(mode = 'edit') {
    const prefix = mode === 'edit' ? 'edit_lecture_' : 'add_lecture_';
    const startInput = document.getElementById(prefix + 'start_time');
    const endInput = document.getElementById(prefix + 'end_time');
    const display = document.getElementById(prefix + 'duration_display');
    const slotLabelInput = document.getElementById(prefix + 'time_slot');

    if (!startInput || !endInput || !display) return;

    const st = startInput.value;
    const et = endInput.value;

    if (!st || !et) {
        display.textContent = '--';
        return;
    }

    const stParts = st.split(':');
    const etParts = et.split(':');
    const stMinutes = parseInt(stParts[0], 10) * 60 + parseInt(stParts[1], 10);
    const etMinutes = parseInt(etParts[0], 10) * 60 + parseInt(etParts[1], 10);
    const diff = etMinutes - stMinutes;

    if (diff <= 0) {
        display.innerHTML = '<span style="color:#EF4444;">Invalid (End time must be after start time)</span>';
    } else {
        const hours = Math.floor(diff / 60);
        const mins = diff % 60;
        let text = '';
        if (hours > 0) text += hours + ' hr ';
        if (mins > 0 || hours === 0) text += mins + ' min';
        text += ' (' + diff + ' minutes total)';
        display.textContent = text;

        // Auto update slot band label if user hasn't typed custom
        if (slotLabelInput) {
            const formatTime = (timeStr) => {
                const parts = timeStr.split(':');
                let h = parseInt(parts[0], 10);
                const m = parts[1];
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                if (h === 0) h = 12;
                return (h < 10 ? '0' + h : h) + ':' + m + ' ' + ampm;
            };
            slotLabelInput.value = formatTime(st) + ' - ' + formatTime(et);
        }
    }
}

// Open Edit Lecture Modal
function openEditLectureModal(card) {
    if (!card) return;
    currentDeleteCandidate = card;

    // Reset alert
    const alertBox = document.getElementById('editModalAlert');
    if (alertBox) alertBox.style.display = 'none';

    document.getElementById('edit_lecture_id').value = card.id || '';
    document.getElementById('edit_lecture_staff_id').value = card.staff_id || '';
    document.getElementById('edit_lecture_subject_id').value = card.subject_id || '';
    document.getElementById('edit_lecture_subject_name').value = card.resolved_subject_name || card.subject_name || '';
    document.getElementById('edit_lecture_day').value = card.day_of_week || 'Monday';
    document.getElementById('edit_lecture_time_slot').value = card.time_slot || '';
    document.getElementById('edit_lecture_start_time').value = (card.start_time || '08:00:00').substring(0, 5);
    document.getElementById('edit_lecture_end_time').value = (card.end_time || '09:00:00').substring(0, 5);
    
    if (document.getElementById('edit_lecture_course_id') && card.course_id) {
        document.getElementById('edit_lecture_course_id').value = card.course_id;
    }
    if (document.getElementById('edit_lecture_standard_id') && card.standard_id) {
        document.getElementById('edit_lecture_standard_id').value = card.standard_id;
    }
    if (document.getElementById('edit_lecture_type')) {
        document.getElementById('edit_lecture_type').value = card.lecture_type || 'theory';
    }
    if (document.getElementById('edit_lecture_notes')) {
        document.getElementById('edit_lecture_notes').value = card.notes || '';
    }

    document.getElementById('edit_lecture_room_no').value = card.classroom_number || card.room_no || 'Block 101';

    calculateDuration('edit');

    const modal = document.getElementById('editLectureModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditLectureModal() {
    const modal = document.getElementById('editLectureModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function syncEditSubjectName(selectElem) {
    const opt = selectElem.options[selectElem.selectedIndex];
    if (opt && opt.dataset.name) {
        document.getElementById('edit_lecture_subject_name').value = opt.dataset.name;
    }
}

// Open Delete Confirmation Modal
function openDeleteLectureConfirmModal(card) {
    if (!card) return;
    currentDeleteCandidate = card;

    const teacherName = (card.teacher_first_name || '') + ' ' + (card.teacher_last_name || 'Faculty');
    document.getElementById('del_confirm_subject').textContent = card.resolved_subject_name || card.subject_name || 'Subject';
    document.getElementById('del_confirm_cohort').textContent = card.course_cohort_display || 'Class Cohort';
    document.getElementById('del_confirm_teacher').textContent = 'Prof. ' + teacherName.trim();
    document.getElementById('del_confirm_time').textContent = (card.day_of_week || 'Day') + ', ' + (card.start_time || '').substring(0, 5) + ' - ' + (card.end_time || '').substring(0, 5);
    document.getElementById('del_confirm_room').textContent = card.classroom_number || card.room_no || 'Block 101';

    const modal = document.getElementById('deleteLectureConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteLectureConfirmModal() {
    const modal = document.getElementById('deleteLectureConfirmModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function triggerDeleteFromEditModal() {
    closeEditLectureModal();
    if (currentDeleteCandidate) {
        openDeleteLectureConfirmModal(currentDeleteCandidate);
    }
}

// Open Add Lecture Modal
function openAddSlotModal(day, startTime, endTime, timeSlot, dateStr) {
    // Reset alert
    const alertBox = document.getElementById('addModalAlert');
    if (alertBox) alertBox.style.display = 'none';

    if (day) document.getElementById('add_lecture_day').value = day;
    if (startTime) document.getElementById('add_lecture_start_time').value = startTime.substring(0, 5);
    if (endTime) document.getElementById('add_lecture_end_time').value = endTime.substring(0, 5);
    if (timeSlot) document.getElementById('add_lecture_time_slot').value = timeSlot;

    calculateDuration('add');

    const modal = document.getElementById('addLectureModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAddLectureModal() {
    const modal = document.getElementById('addLectureModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function syncAddSubjectName(selectElem) {
    const opt = selectElem.options[selectElem.selectedIndex];
    if (opt && opt.dataset.name) {
        document.getElementById('add_lecture_subject_name').value = opt.dataset.name;
    }
}

// AJAX Submission: Edit Lecture
async function submitEditLectureAjax(e) {
    e.preventDefault();
    const form = document.getElementById('editLectureForm');
    const submitBtn = document.getElementById('editLectureSubmitBtn');
    const alertBox = document.getElementById('editModalAlert');
    const alertText = document.getElementById('editModalAlertText');

    if (alertBox) alertBox.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳ Saving...</span>';

    try {
        const formData = new FormData(form);
        const res = await fetch('index.php?page=update_timetable', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'Lecture updated successfully!', 'success');
            closeEditLectureModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            const errorMsg = (data && data.error) ? data.error : ('Error ' + res.status + ': Failed to update timetable slot.');
            if (alertBox && alertText) {
                alertText.textContent = errorMsg;
                alertBox.style.display = 'block';
            } else {
                showToast(errorMsg, 'error');
            }
        }
    } catch (err) {
        if (alertBox && alertText) {
            alertText.textContent = 'Network error: ' + err.message;
            alertBox.style.display = 'block';
        } else {
            showToast('Network error: ' + err.message, 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Save Changes</span>';
    }
}

// AJAX Submission: Create Lecture
async function submitAddLectureAjax(e) {
    e.preventDefault();
    const form = document.getElementById('addLectureForm');
    const submitBtn = document.getElementById('addLectureSubmitBtn');
    const alertBox = document.getElementById('addModalAlert');
    const alertText = document.getElementById('addModalAlertText');

    if (alertBox) alertBox.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳ Scheduling...</span>';

    try {
        const formData = new FormData(form);
        const res = await fetch('index.php?page=create_timetable', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'Lecture slot created successfully!', 'success');
            closeAddLectureModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            const errorMsg = (data && data.error) ? data.error : ('Error ' + res.status + ': Failed to create lecture slot.');
            if (alertBox && alertText) {
                alertText.textContent = errorMsg;
                alertBox.style.display = 'block';
            } else {
                showToast(errorMsg, 'error');
            }
        }
    } catch (err) {
        if (alertBox && alertText) {
            alertText.textContent = 'Network error: ' + err.message;
            alertBox.style.display = 'block';
        } else {
            showToast('Network error: ' + err.message, 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Create Slot</span>';
    }
}

// AJAX Submission: Delete Lecture
async function executeDeleteLectureAjax() {
    if (!currentDeleteCandidate || !currentDeleteCandidate.id) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳ Deleting...</span>';

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= \App\Core\CSRF::generate() ?>');
        formData.append('id', currentDeleteCandidate.id);
        formData.append('ajax', '1');

        const res = await fetch('index.php?page=delete_timetable', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'Lecture deleted successfully.', 'success');
            closeDeleteLectureConfirmModal();
            
            // Remove card from UI smoothly
            const cardEl = document.getElementById('lecture-card-' + currentDeleteCandidate.id);
            if (cardEl) {
                cardEl.style.transition = 'all 0.3s ease';
                cardEl.style.opacity = '0';
                cardEl.style.transform = 'scale(0.9)';
                setTimeout(() => window.location.reload(), 400);
            } else {
                setTimeout(() => window.location.reload(), 400);
            }
        } else {
            showToast((data && data.error) ? data.error : 'Failed to delete lecture.', 'error');
        }
    } catch (err) {
        showToast('Network error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>Confirm Delete</span>';
    }
}

// =========================================================================
// TIME SLOT ROW MANAGEMENT JAVASCRIPT CONTROLLERS
// =========================================================================
let currentSlotToDelete = null;

function openEditTimeSlotModal(slotData) {
    if (!slotData) return;
    const alertBox = document.getElementById('editTimeSlotAlert');
    if (alertBox) alertBox.style.display = 'none';

    document.getElementById('edit_timeslot_key').value = slotData.key || '';
    document.getElementById('edit_timeslot_old_st').value = slotData.start_time || '';
    document.getElementById('edit_timeslot_old_et').value = slotData.end_time || '';
    document.getElementById('edit_timeslot_label').value = slotData.label || '';
    document.getElementById('edit_timeslot_start_time').value = (slotData.start_time || '08:00').substring(0, 5);
    document.getElementById('edit_timeslot_end_time').value = (slotData.end_time || '09:00').substring(0, 5);

    calculateTimeSlotDuration('edit');

    const modal = document.getElementById('editTimeSlotModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditTimeSlotModal() {
    const modal = document.getElementById('editTimeSlotModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function openDeleteTimeSlotModal(slotData) {
    if (!slotData) return;
    currentSlotToDelete = slotData;

    document.getElementById('del_timeslot_label_display').textContent = slotData.label || 'Time Slot';
    document.getElementById('del_timeslot_time_display').textContent = slotData.time_display || (slotData.start_time + ' - ' + slotData.end_time);
    
    const count = parseInt(slotData.lecture_count || 0, 10);
    const countEl = document.getElementById('del_timeslot_count_display');
    if (countEl) {
        countEl.textContent = count + ' lecture' + (count === 1 ? '' : 's') + ' across all weekdays';
        countEl.style.color = count > 0 ? '#F87171' : '#94A3B8';
    }

    const modal = document.getElementById('deleteTimeSlotModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteTimeSlotModal() {
    const modal = document.getElementById('deleteTimeSlotModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function openAddTimeSlotModal() {
    const alertBox = document.getElementById('addTimeSlotAlert');
    if (alertBox) alertBox.style.display = 'none';

    calculateTimeSlotDuration('add');

    const modal = document.getElementById('addTimeSlotModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAddTimeSlotModal() {
    const modal = document.getElementById('addTimeSlotModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function calculateTimeSlotDuration(mode) {
    const stInput = mode === 'edit' ? document.getElementById('edit_timeslot_start_time') : document.getElementById('new_timeslot_start_time');
    const etInput = mode === 'edit' ? document.getElementById('edit_timeslot_end_time') : document.getElementById('new_timeslot_end_time');
    const disp = mode === 'edit' ? document.getElementById('edit_timeslot_duration_display') : document.getElementById('new_timeslot_duration_display');

    if (!stInput || !etInput || !disp) return;

    const st = stInput.value;
    const et = etInput.value;
    if (st && et) {
        const [sh, sm] = st.split(':').map(Number);
        const [eh, em] = et.split(':').map(Number);
        let diff = (eh * 60 + em) - (sh * 60 + sm);
        if (diff > 0) {
            disp.textContent = diff + ' minutes';
            disp.style.color = mode === 'edit' ? '#38BDF8' : '#34D399';
        } else {
            disp.textContent = 'Invalid (End must be after Start)';
            disp.style.color = '#EF4444';
        }
    }
}

// AJAX: Submit Edit Time Slot Row
async function submitEditTimeSlotAjax(e) {
    e.preventDefault();
    const form = document.getElementById('editTimeSlotForm');
    const submitBtn = document.getElementById('editTimeSlotSubmitBtn');
    const alertBox = document.getElementById('editTimeSlotAlert');
    const alertText = document.getElementById('editTimeSlotAlertText');

    if (alertBox) alertBox.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳ Saving...</span>';

    try {
        const formData = new FormData(form);
        const res = await fetch('index.php?page=update_timeslot_row', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'Time slot row updated successfully!', 'success');
            closeEditTimeSlotModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            const errorMsg = (data && data.error) ? data.error : ('Error ' + res.status + ': Failed to update time slot row.');
            if (alertBox && alertText) {
                alertText.textContent = errorMsg;
                alertBox.style.display = 'block';
            } else {
                showToast(errorMsg, 'error');
            }
        }
    } catch (err) {
        if (alertBox && alertText) {
            alertText.textContent = 'Network error: ' + err.message;
            alertBox.style.display = 'block';
        } else {
            showToast('Network error: ' + err.message, 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Save Changes</span>';
    }
}

// AJAX: Submit Delete Time Slot Row
async function executeDeleteTimeSlotAjax() {
    if (!currentSlotToDelete) return;
    const btn = document.getElementById('confirmDeleteTimeSlotBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳ Deleting Row...</span>';

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= \App\Core\CSRF::generate() ?>');
        formData.append('period_key', currentSlotToDelete.key || '');
        formData.append('start_time', currentSlotToDelete.start_time || '');
        formData.append('end_time', currentSlotToDelete.end_time || '');
        formData.append('ajax', '1');

        const res = await fetch('index.php?page=delete_timeslot_row', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'Time slot row deleted successfully.', 'success');
            closeDeleteTimeSlotModal();

            // Smoothly remove row from UI
            const rowEl = document.getElementById('row-period-' + currentSlotToDelete.key);
            if (rowEl) {
                rowEl.style.transition = 'all 0.35s ease';
                rowEl.style.opacity = '0';
                rowEl.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    rowEl.remove();
                    window.location.reload();
                }, 350);
            } else {
                setTimeout(() => window.location.reload(), 400);
            }
        } else {
            showToast((data && data.error) ? data.error : 'Failed to delete time slot row.', 'error');
        }
    } catch (err) {
        showToast('Network error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>🗑️ Yes, Delete Entire Row</span>';
    }
}

// AJAX: Submit Add Time Slot Row
async function submitAddTimeSlotAjax(e) {
    e.preventDefault();
    const form = document.getElementById('addTimeSlotForm');
    const submitBtn = document.getElementById('addTimeSlotSubmitBtn');
    const alertBox = document.getElementById('addTimeSlotAlert');
    const alertText = document.getElementById('addTimeSlotAlertText');

    if (alertBox) alertBox.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳ Adding Slot...</span>';

    try {
        const formData = new FormData(form);
        const res = await fetch('index.php?page=add_timeslot_row', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.success) {
            showToast(data.message || 'New time slot row added successfully!', 'success');
            closeAddTimeSlotModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            const errorMsg = (data && data.error) ? data.error : ('Error ' + res.status + ': Failed to add time slot row.');
            if (alertBox && alertText) {
                alertText.textContent = errorMsg;
                alertBox.style.display = 'block';
            } else {
                showToast(errorMsg, 'error');
            }
        }
    } catch (err) {
        if (alertBox && alertText) {
            alertText.textContent = 'Network error: ' + err.message;
            alertBox.style.display = 'block';
        } else {
            showToast('Network error: ' + err.message, 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Add Time Slot</span>';
    }
}

// Close modals on ESC
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditLectureModal();
        closeDeleteLectureConfirmModal();
        closeAddLectureModal();
        closeEditTimeSlotModal();
        closeDeleteTimeSlotModal();
        closeAddTimeSlotModal();
    }
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
