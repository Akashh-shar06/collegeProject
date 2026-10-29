<?php
/**
 * Campus360 Enterprise ERP - Lecture Classroom Session Hub
 * Integrated Attendance, Daily Lesson Record, Homework, and Classroom Remarks
 */
$pageTitle = "Classroom Lecture Session — " . ($lecture['resolved_subject_name'] ?? 'Class');
require BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container-fluid" style="padding: 24px; background: #F8FAFC; min-height: 100vh;">
    <!-- Top Breadcrumb -->
    <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
        <a href="index.php?page=dashboard" style="color: #64748B; text-decoration: none;">Dashboard</a>
        <span>/</span>
        <a href="index.php?page=teacher_timetable" style="color: #64748B; text-decoration: none;">Weekly Timetable</a>
        <span>/</span>
        <span style="color: #2563EB;">Classroom Lecture Session</span>
    </div>

    <!-- Alert Notifications Box -->
    <div id="ajaxAlertContainer"></div>

    <!-- Master Lecture Context Banner -->
    <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 300px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                <span style="background: #EEF2FF; color: #4338CA; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 6px; text-transform: uppercase;">
                    <?= htmlspecialchars($lecture['subject_code'] ?? 'SUB-101', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
                    <?= htmlspecialchars($lecture['resolved_subject_name'] ?? $lecture['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <span style="background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                    <?= ucfirst($lecture['subject_type'] ?? 'theory') ?>
                </span>
            </div>

            <!-- Class & Department Metadata -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px; font-size: 13px; color: #475569; margin-top: 8px;">
                <span>👥 Class / Division: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['section_name'] ?? 'Division A', ENT_QUOTES, 'UTF-8') ?></strong></span>
                <span>•</span>
                <span>🎓 Course: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['course_name'] ?? 'Degree Program', ENT_QUOTES, 'UTF-8') ?></strong></span>
                <?php if (!empty($lecture['semester_name'])): ?>
                    <span>•</span>
                    <span>Semester: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['semester_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                <?php endif; ?>
                <span>•</span>
                <span>🏢 Dept: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['department_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></strong></span>
            </div>

            <!-- Time & Room Details -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px; font-size: 13px; color: #475569; margin-top: 6px;">
                <span>📅 Lecture Date: <strong style="color: #2563EB;"><?= date('l, d F Y', strtotime($lectureDate)) ?></strong></span>
                <span>•</span>
                <span>⏰ Time Slot: <strong style="color: #0F172A;"><?= date('g:i A', strtotime($lecture['start_time'])) ?> &ndash; <?= date('g:i A', strtotime($lecture['end_time'])) ?></strong></span>
                <span>•</span>
                <span>📍 Classroom: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['classroom_number'] ?? $lecture['room_no'] ?? 'Block 101', ENT_QUOTES, 'UTF-8') ?></strong></span>
                <span>•</span>
                <span>👨‍🏫 Faculty: <strong style="color: #0F172A;"><?= htmlspecialchars($lecture['teacher_first_name'] . ' ' . $lecture['teacher_last_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
            </div>
        </div>

        <!-- Quick Status Card -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; min-width: 240px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 8px;">
                SESSION COMPLETION
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                <span style="font-size: 12px; color: #475569;">Attendance:</span>
                <?php if ($attendanceSession && $attendanceSession['status'] === 'completed'): ?>
                    <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 8px; border-radius: 10px; border: 1px solid #A7F3D0;">
                        ✓ Saved (<?= (int)$attendanceStats['percentage'] ?>%)
                    </span>
                <?php else: ?>
                    <span style="font-size: 11px; font-weight: 700; color: #D97706; background: #FFFBEB; padding: 2px 8px; border-radius: 10px; border: 1px solid #FDE68A;">
                        ⚠️ Pending
                    </span>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                <span style="font-size: 12px; color: #475569;">Lesson Topic:</span>
                <?php if ($currentLesson): ?>
                    <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 8px; border-radius: 10px; border: 1px solid #A7F3D0;">
                        ✓ Recorded
                    </span>
                <?php else: ?>
                    <span style="font-size: 11px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 2px 8px; border-radius: 10px;">
                        Not Added
                    </span>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 12px; color: #475569;">Homework:</span>
                <span style="font-size: 11px; font-weight: 700; color: #7C3AED; background: #FAF5FF; padding: 2px 8px; border-radius: 10px;">
                    <?= count($homeworkList) ?> Active
                </span>
            </div>
        </div>
    </div>

    <!-- Tabbed Navigation Bar -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid #E2E8F0; margin-bottom: 24px; overflow-x: auto;">
        <button type="button" class="lecture-tab-btn active" onclick="switchLectureTab('attendanceTab', this)">
            📋 1. Student Attendance (<?= count($students) ?>)
        </button>
        <button type="button" class="lecture-tab-btn" onclick="switchLectureTab('lessonTab', this)">
            📖 2. Daily Lesson Record
        </button>
        <button type="button" class="lecture-tab-btn" onclick="switchLectureTab('homeworkTab', this)">
            📚 3. Homework & Assignments (<?= count($homeworkList) ?>)
        </button>
        <button type="button" class="lecture-tab-btn" onclick="switchLectureTab('remarksTab', this)">
            💬 4. Classroom Remarks (<?= count($classroomRemarks) ?>)
        </button>
        <button type="button" class="lecture-tab-btn" onclick="switchLectureTab('historyTab', this)">
            📜 5. Previous Sessions History
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: STUDENT ATTENDANCE MANAGEMENT                     -->
    <!-- ======================================================== -->
    <div id="attendanceTab" class="lecture-tab-content active">
        <form id="attendanceForm" onsubmit="submitLectureAttendance(event)">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="time_table_id" value="<?= (int)$lecture['id'] ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($lectureDate) ?>">

            <!-- Attendance Control Bar with Live Counters -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 18px 24px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: var(--shadow-sm);">
                <!-- Live Badges -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="padding: 6px 14px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px;">
                        Total: <strong id="cntTotal"><?= (int)$attendanceStats['total'] ?></strong>
                    </div>
                    <div style="padding: 6px 14px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; font-size: 13px;">
                        Present: <strong id="cntPresent"><?= (int)$attendanceStats['present'] ?></strong>
                    </div>
                    <div style="padding: 6px 14px; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; font-size: 13px;">
                        Absent: <strong id="cntAbsent"><?= (int)$attendanceStats['absent'] ?></strong>
                    </div>
                    <div style="padding: 6px 14px; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; border-radius: 8px; font-size: 13px;">
                        Late: <strong id="cntLate"><?= (int)$attendanceStats['late'] ?></strong>
                    </div>
                    <div style="padding: 6px 14px; background: #F5F3FF; border: 1px solid #DDD6FE; color: #5B21B6; border-radius: 8px; font-size: 13px;">
                        Excused: <strong id="cntExcused"><?= (int)$attendanceStats['excused'] ?></strong>
                    </div>
                    <div style="padding: 6px 14px; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; border-radius: 8px; font-size: 13px;">
                        Completion: <strong id="cntPct"><?= (int)$attendanceStats['percentage'] ?>%</strong>
                    </div>
                </div>

                <!-- Bulk Quick Action Buttons -->
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn" onclick="bulkSetAttendance('present')" style="background: #10B981; color: #FFFFFF; font-weight: 700; font-size: 12px; padding: 8px 14px; border-radius: 8px; border:none; cursor: pointer;">
                        ✓ Mark All Present
                    </button>
                    <button type="button" class="btn" onclick="confirmMarkAllAbsent()" style="background: #EF4444; color: #FFFFFF; font-weight: 700; font-size: 12px; padding: 8px 14px; border-radius: 8px; border:none; cursor: pointer;">
                        ✕ Mark All Absent
                    </button>
                    <button type="button" class="btn" onclick="clearAllAttendance()" style="background: #F1F5F9; color: #475569; font-weight: 700; font-size: 12px; padding: 8px 14px; border-radius: 8px; border:1px solid #CBD5E1; cursor: pointer;">
                        Reset
                    </button>
                    <button type="submit" id="saveAttendanceBtn" class="btn btn-primary" style="font-weight: 800; font-size: 13px; padding: 9px 20px; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(37,99,235,0.3);">
                        💾 Save Attendance Roster
                    </button>
                </div>
            </div>

            <!-- Attendance Student Roster Table -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: var(--shadow-sm);">
                <?php if (empty($students)): ?>
                    <div style="text-align: center; padding: 48px 20px; color: #64748B;">
                        <div style="font-size: 40px; margin-bottom: 10px;">👥</div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #334155; margin-bottom: 4px;">No Active Students Found in Roster</h4>
                        <p style="font-size: 13px; margin: 0;">No active student enrollments match this class / division.</p>
                    </div>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #0F172A; color: #FFFFFF;">
                                <th style="padding: 14px 16px; width: 60px; text-align: center;">#</th>
                                <th style="padding: 14px 16px; width: 130px;">ROLL NUMBER</th>
                                <th style="padding: 14px 16px; width: 140px;">STUDENT ID</th>
                                <th style="padding: 14px 16px;">STUDENT FULL NAME</th>
                                <th style="padding: 14px 16px; width: 340px; text-align: center;">ATTENDANCE STATUS</th>
                                <th style="padding: 14px 16px; width: 220px;">REMARKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $serial = 1;
                            foreach ($students as $s): 
                                $sId = $s['id'];
                                $currentStatus = $savedAttendanceMap[$sId]['status'] ?? '';
                                $currentRemark = $savedAttendanceMap[$sId]['remarks'] ?? '';
                            ?>
                                <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                    <td style="padding: 12px 16px; text-align: center; color: #64748B; font-weight: 600;">
                                        <?= $serial++ ?>
                                    </td>
                                    <!-- Official Academic Roll Number -->
                                    <td style="padding: 12px 16px; font-weight: 800; color: #0F172A;">
                                        <span style="background: #EEF2FF; color: #4338CA; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 13px;">
                                            <?= htmlspecialchars($s['roll_no'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <!-- Permanent Student ID -->
                                    <td style="padding: 12px 16px; color: #475569; font-weight: 600;">
                                        <?= htmlspecialchars($s['unique_id'] ?: $s['admission_no'] ?: ('STU-' . $s['id']), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <!-- Student Name -->
                                    <td style="padding: 12px 16px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #E2E8F0; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                                <?= strtoupper(substr($s['first_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: #0F172A;">
                                                    <?= htmlspecialchars($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'] . ' ' : '') . $s['last_name'], ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <div style="font-size: 11px; color: #94A3B8;">
                                                    Internal ID: #<?= $s['id'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Status Selector Controls -->
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <div style="display: inline-flex; background: #F1F5F9; border-radius: 8px; padding: 3px; gap: 4px;">
                                            <label class="status-radio-label <?= ($currentStatus === 'present') ? 'active-present' : '' ?>">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="present" <?= ($currentStatus === 'present') ? 'checked' : '' ?> onchange="updateRowHighlight(this)" style="display:none;">
                                                <span>✓ Present</span>
                                            </label>
                                            <label class="status-radio-label <?= ($currentStatus === 'absent') ? 'active-absent' : '' ?>">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="absent" <?= ($currentStatus === 'absent') ? 'checked' : '' ?> onchange="updateRowHighlight(this)" style="display:none;">
                                                <span>✕ Absent</span>
                                            </label>
                                            <label class="status-radio-label <?= ($currentStatus === 'late') ? 'active-late' : '' ?>">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="late" <?= ($currentStatus === 'late') ? 'checked' : '' ?> onchange="updateRowHighlight(this)" style="display:none;">
                                                <span>⏳ Late</span>
                                            </label>
                                            <label class="status-radio-label <?= ($currentStatus === 'excused') ? 'active-excused' : '' ?>">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="excused" <?= ($currentStatus === 'excused') ? 'checked' : '' ?> onchange="updateRowHighlight(this)" style="display:none;">
                                                <span>🛡️ Excused</span>
                                            </label>
                                        </div>
                                    </td>
                                    <!-- Remarks -->
                                    <td style="padding: 12px 16px;">
                                        <input type="text" name="remarks[<?= $s['id'] ?>]" value="<?= htmlspecialchars($currentRemark, ENT_QUOTES, 'UTF-8') ?>" placeholder="Optional remark..." onchange="markDirty()" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid #E2E8F0; font-size: 12px;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: DAILY LESSON RECORD                               -->
    <!-- ======================================================== -->
    <div id="lessonTab" class="lecture-tab-content">
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px;">
            <!-- Today's Lesson Form -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> Record Topic Taught Today
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 0 0 20px 0;">
                    Document syllabus coverage, concepts taught, and classroom activities for <?= date('d M Y', strtotime($lectureDate)) ?>
                </p>

                <form id="lessonForm" onsubmit="submitLessonRecord(event)">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <input type="hidden" name="time_table_id" value="<?= (int)$lecture['id'] ?>">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($lectureDate) ?>">

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            Actual Topic Taught *
                        </label>
                        <input type="text" name="topic_title" id="lesson_topic_title" required value="<?= htmlspecialchars($currentLesson['topic_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Relational Algebra, SQL Joins & Query Optimization" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px; font-family: inherit;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            Lesson Description / Notes
                        </label>
                        <textarea name="description" id="lesson_description" rows="4" placeholder="Detail the concepts explained, code demonstrations, practical exercises or questions raised..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-family: inherit; line-height: 1.5;"><?= htmlspecialchars($currentLesson['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            Lesson Completion Status
                        </label>
                        <select name="status" id="lesson_status" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; background: #FFFFFF;">
                            <option value="completed" <?= (($currentLesson['status'] ?? 'completed') === 'completed') ? 'selected' : '' ?>>Completed — Full topic covered</option>
                            <option value="partially_completed" <?= (($currentLesson['status'] ?? '') === 'partially_completed') ? 'selected' : '' ?>>Partially Completed — Carried over to next lecture</option>
                            <option value="planned" <?= (($currentLesson['status'] ?? '') === 'planned') ? 'selected' : '' ?>>Planned / Preparation stage</option>
                        </select>
                    </div>

                    <button type="submit" id="saveLessonBtn" class="btn btn-primary" style="padding: 10px 24px; font-weight: 800; font-size: 14px; border-radius: 8px; cursor: pointer;">
                        💾 Save Lesson Record
                    </button>
                </form>
            </div>

            <!-- Last Lesson Taught & Syllabus Timeline -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Last Lesson Callout -->
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 14px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 18px;">💡</span>
                        <h4 style="font-size: 15px; font-weight: 800; color: #166534; margin: 0;">
                            Previous Lesson for this Subject & Class
                        </h4>
                    </div>
                    <?php if (!empty($lastLessonTaught)): ?>
                        <div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-bottom: 4px;">
                            <?= htmlspecialchars($lastLessonTaught['topic_title'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div style="font-size: 12px; color: #166534; margin-bottom: 6px;">
                            Taught on: <strong><?= date('l, d M Y', strtotime($lastLessonTaught['lecture_date'])) ?></strong>
                            (Status: <?= ucfirst(str_replace('_', ' ', $lastLessonTaught['status'])) ?>)
                        </div>
                        <?php if (!empty($lastLessonTaught['description'])): ?>
                            <p style="font-size: 12px; color: #4B5563; margin: 0; line-height: 1.4;">
                                <?= htmlspecialchars($lastLessonTaught['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="font-size: 12px; color: #166534; margin: 0;">
                            No previous lesson recorded yet for this class & subject. This is the first recorded lecture session.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Previous Topics History -->
                <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: var(--shadow-sm); max-height: 380px; overflow-y: auto;">
                    <h4 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0 0 12px 0; display: flex; align-items: center; justify-content: space-between;">
                        <span>📚 Topics Covered Timeline</span>
                        <span style="font-size: 11px; color: #64748B; font-weight: normal;"><?= count($previousLessons) ?> Records</span>
                    </h4>
                    <?php if (empty($previousLessons)): ?>
                        <p style="font-size: 12px; color: #64748B; margin: 0;">No history available yet.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($previousLessons as $prev): ?>
                                <div style="border-left: 3px solid #2563EB; padding-left: 12px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748B;">
                                        <span><?= date('d M Y', strtotime($prev['lecture_date'])) ?></span>
                                        <span style="font-weight: 700; color: #059669;"><?= ucfirst(str_replace('_', ' ', $prev['status'])) ?></span>
                                    </div>
                                    <div style="font-size: 13px; font-weight: 700; color: #0F172A;">
                                        <?= htmlspecialchars($prev['topic_title'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: HOMEWORK / ASSIGNMENT MANAGEMENT                   -->
    <!-- ======================================================== -->
    <div id="homeworkTab" class="lecture-tab-content">
        <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px;">
            <!-- Create Homework Form -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                    <span>📚</span> Assign New Homework
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 0 0 18px 0;">
                    Assign homework task for <strong><?= htmlspecialchars($lecture['section_name'] ?? 'Class', ENT_QUOTES, 'UTF-8') ?></strong>
                </p>

                <form action="index.php?page=save_homework" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <input type="hidden" name="time_table_id" value="<?= (int)$lecture['id'] ?>">
                    <input type="hidden" name="subject_id" value="<?= (int)$lecture['subject_id'] ?>">
                    <input type="hidden" name="department_id" value="<?= (int)$lecture['department_id'] ?>">
                    <input type="hidden" name="course_id" value="<?= (int)$lecture['course_id'] ?>">
                    <input type="hidden" name="section_id" value="<?= (int)$lecture['section_id'] ?>">
                    <input type="hidden" name="assigned_date" value="<?= htmlspecialchars($lectureDate) ?>">

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Homework Title *
                        </label>
                        <input type="text" name="title" required placeholder="e.g. Assignment 2: ER Diagrams & SQL Schema" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Submission Due Date *
                        </label>
                        <input type="date" name="due_date" required min="<?= $lectureDate ?>" value="<?= date('Y-m-d', strtotime('+3 days', strtotime($lectureDate))) ?>" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Instructions / Task Details
                        </label>
                        <textarea name="description" rows="3" placeholder="Provide instructions, required sections, deliverables or problems to solve..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; line-height: 1.4;"></textarea>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Supporting Document / Attachment (Optional)
                        </label>
                        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg" style="width: 100%; font-size: 12px; color: #475569;">
                        <span style="font-size: 11px; color: #64748B;">Supported: PDF, DOC, DOCX, ZIP, PNG, JPG (Max 10MB)</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 800; font-size: 13px; border-radius: 8px;">
                        🚀 Publish Homework
                    </button>
                </form>
            </div>

            <!-- List of Homework for this Class & Subject -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; display: flex; align-items: center; justify-content: space-between;">
                    <span>📋 Assigned Homework for this Class</span>
                    <span style="font-size: 12px; color: #7C3AED; font-weight: 700;"><?= count($homeworkList) ?> Active</span>
                </h3>

                <?php if (empty($homeworkList)): ?>
                    <p style="font-size: 13px; color: #64748B; margin: 0;">No homework has been assigned for this class and subject yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($homeworkList as $hw): ?>
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">
                                        <?= htmlspecialchars($hw['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </h4>
                                    <span style="font-size: 11px; font-weight: 700; color: <?= ($hw['due_date'] < date('Y-m-d')) ? '#EF4444' : '#059669' ?>; background: <?= ($hw['due_date'] < date('Y-m-d')) ? '#FEF2F2' : '#ECFDF5' ?>; padding: 3px 8px; border-radius: 6px;">
                                        Due: <?= date('d M Y', strtotime($hw['due_date'])) ?>
                                    </span>
                                </div>
                                <?php if (!empty($hw['description'])): ?>
                                    <p style="font-size: 12px; color: #475569; margin: 0 0 8px 0; line-height: 1.4;">
                                        <?= htmlspecialchars($hw['description'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                <?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                                    <span style="color: #64748B;">Assigned on: <?= date('d M Y', strtotime($hw['assigned_date'])) ?></span>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if (!empty($hw['attachment_path'])): ?>
                                            <a href="<?= htmlspecialchars($hw['attachment_path']) ?>" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: none;">
                                                📎 Download File
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" onclick="deleteHomework(<?= (int)$hw['id'] ?>)" style="background:none; border:none; color:#EF4444; font-size:12px; font-weight:700; cursor:pointer;">
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: CLASSROOM REMARKS                                 -->
    <!-- ======================================================== -->
    <div id="remarksTab" class="lecture-tab-content">
        <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px;">
            <!-- Add Remark Form -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                    <span>💬</span> Add Classroom Remark
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 0 0 18px 0;">
                    Record private faculty notes on class participation, student academic feedback or discipline.
                </p>

                <form id="remarkForm" onsubmit="submitClassroomRemark(event)">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <input type="hidden" name="time_table_id" value="<?= (int)$lecture['id'] ?>">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($lectureDate) ?>">

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Remark Target
                        </label>
                        <select name="student_id" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; background: #FFFFFF;">
                            <option value="">🏢 General Class Remark (Whole Division)</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    👤 <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . ($s['roll_no'] ?: 'Roll ' . $s['id']) . ')', ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Remark Category
                        </label>
                        <select name="remark_type" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; background: #FFFFFF;">
                            <option value="general_class">General Class Feedback</option>
                            <option value="academic_performance">Academic Performance</option>
                            <option value="participation">Classroom Participation & Interaction</option>
                            <option value="behavior">Discipline & Conduct</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Remark Text *
                        </label>
                        <textarea name="remark_text" id="remark_text" required rows="3" placeholder="Write private faculty observation..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; line-height: 1.4;"></textarea>
                    </div>

                    <button type="submit" id="saveRemarkBtn" class="btn btn-primary" style="padding: 10px 20px; font-weight: 800; font-size: 13px; border-radius: 8px;">
                        💾 Save Remark
                    </button>
                </form>
            </div>

            <!-- Existing Remarks List -->
            <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">
                    Classroom Remarks Log
                </h3>

                <div id="remarksListContainer" style="display: flex; flex-direction: column; gap: 12px;">
                    <?php if (empty($classroomRemarks)): ?>
                        <p id="noRemarksPlaceholder" style="font-size: 13px; color: #64748B; margin: 0;">No remarks recorded for this lecture yet.</p>
                    <?php else: ?>
                        <?php foreach ($classroomRemarks as $rem): ?>
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
                                    <strong>
                                        <?= !empty($rem['student_id']) ? ('👤 ' . htmlspecialchars($rem['first_name'] . ' ' . $rem['last_name'] . ' (' . $rem['roll_no'] . ')', ENT_QUOTES, 'UTF-8')) : '🏢 Whole Class' ?>
                                    </strong>
                                    <span style="color: #64748B; font-size: 11px;">
                                        <?= date('d M Y H:i', strtotime($rem['created_at'])) ?>
                                    </span>
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #4338CA; margin-bottom: 4px;">
                                    Category: <?= ucfirst(str_replace('_', ' ', $rem['remark_type'])) ?>
                                </div>
                                <p style="font-size: 13px; color: #334155; margin: 0;">
                                    <?= htmlspecialchars($rem['remark_text'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: PREVIOUS SESSIONS HISTORY                         -->
    <!-- ======================================================== -->
    <div id="historyTab" class="lecture-tab-content">
        <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">
                All Recorded Sessions for this Timetable Slot
            </h3>
            <?php if (empty($previousLessons)): ?>
                <p style="font-size: 13px; color: #64748B;">No prior session history found.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #F1F5F9; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 10px 14px; text-align: left;">DATE</th>
                            <th style="padding: 10px 14px; text-align: left;">TOPIC TAUGHT</th>
                            <th style="padding: 10px 14px; text-align: left;">STATUS</th>
                            <th style="padding: 10px 14px; text-align: left;">RECORDED BY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previousLessons as $pl): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 10px 14px; font-weight: 700; color: #2563EB;">
                                    <?= date('d M Y', strtotime($pl['lecture_date'])) ?>
                                </td>
                                <td style="padding: 10px 14px; font-weight: 600; color: #0F172A;">
                                    <?= htmlspecialchars($pl['topic_title'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 10px 14px;">
                                    <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 8px; border-radius: 6px;">
                                        <?= ucfirst(str_replace('_', ' ', $pl['status'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 14px; color: #64748B;">
                                    <?= htmlspecialchars($pl['recorded_by_username'] ?? 'Faculty', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.lecture-tab-btn {
    padding: 10px 18px;
    background: none;
    border: none;
    font-size: 14px;
    font-weight: 700;
    color: #64748B;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}
.lecture-tab-btn:hover {
    color: #0F172A;
}
.lecture-tab-btn.active {
    color: #2563EB;
    border-bottom-color: #2563EB;
}
.lecture-tab-content {
    display: none;
}
.lecture-tab-content.active {
    display: block;
}
.status-radio-label {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: #64748B;
    user-select: none;
    transition: all 0.15s;
}
.status-radio-label:hover {
    background: #E2E8F0;
}
.active-present {
    background: #10B981 !important;
    color: #FFFFFF !important;
}
.active-absent {
    background: #EF4444 !important;
    color: #FFFFFF !important;
}
.active-late {
    background: #F59E0B !important;
    color: #FFFFFF !important;
}
.active-excused {
    background: #8B5CF6 !important;
    color: #FFFFFF !important;
}
</style>

<script>
let isDirty = false;

function markDirty() {
    isDirty = true;
}

window.addEventListener('beforeunload', function (e) {
    if (isDirty) {
        e.preventDefault();
        e.returnValue = 'You have unsaved attendance changes. Are you sure you want to leave?';
    }
});

function switchLectureTab(tabId, btn) {
    document.querySelectorAll('.lecture-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.lecture-tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function updateRowHighlight(radio) {
    markDirty();
    const parentContainer = radio.closest('td');
    parentContainer.querySelectorAll('.status-radio-label').forEach(lbl => {
        lbl.className = 'status-radio-label';
    });

    const activeLabel = radio.closest('label');
    if (radio.value === 'present') activeLabel.classList.add('active-present');
    else if (radio.value === 'absent') activeLabel.classList.add('active-absent');
    else if (radio.value === 'late') activeLabel.classList.add('active-late');
    else if (radio.value === 'excused') activeLabel.classList.add('active-excused');

    recalcAttendanceCounters();
}

function bulkSetAttendance(status) {
    markDirty();
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        if (radio.value === status) {
            radio.checked = true;
            updateRowHighlight(radio);
        }
    });
}

function confirmMarkAllAbsent() {
    if (confirm('Are you sure you want to mark ALL students ABSENT for this session?')) {
        bulkSetAttendance('absent');
    }
}

function clearAllAttendance() {
    markDirty();
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.checked = false;
        radio.closest('label').className = 'status-radio-label';
    });
    recalcAttendanceCounters();
}

function recalcAttendanceCounters() {
    let pres = 0, abs = 0, late = 0, exc = 0;
    const allRadios = document.querySelectorAll('input[type="radio"]:checked');
    allRadios.forEach(r => {
        if (r.value === 'present') pres++;
        else if (r.value === 'absent') abs++;
        else if (r.value === 'late') late++;
        else if (r.value === 'excused') exc++;
    });

    const total = document.querySelectorAll('tbody tr').length;
    document.getElementById('cntPresent').innerText = pres;
    document.getElementById('cntAbsent').innerText = abs;
    document.getElementById('cntLate').innerText = late;
    document.getElementById('cntExcused').innerText = exc;

    const pct = total > 0 ? Math.round((pres / total) * 100) : 0;
    document.getElementById('cntPct').innerText = pct + '%';
}

function showAlert(type, msg) {
    const box = document.getElementById('ajaxAlertContainer');
    const color = type === 'success' ? '#065F46' : '#991B1B';
    const bg = type === 'success' ? '#ECFDF5' : '#FEF2F2';
    const border = type === 'success' ? '#A7F3D0' : '#FECACA';
    box.innerHTML = `
        <div style="background:${bg}; border:1px solid ${border}; color:${color}; padding:14px 18px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:14px;">
            <span>${type === 'success' ? '✅' : '⚠️'} ${msg}</span>
            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; font-size:18px; cursor:pointer; color:${color};">&times;</button>
        </div>
    `;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function submitLectureAttendance(e) {
    e.preventDefault();
    const form = document.getElementById('attendanceForm');
    const btn = document.getElementById('saveAttendanceBtn');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const formData = new FormData(form);

    fetch('index.php?page=save_lecture_attendance', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = '💾 Save Attendance Roster';
        if (data.success) {
            isDirty = false;
            showAlert('success', data.message);
        } else {
            showAlert('error', data.error || 'Failed to save attendance.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = '💾 Save Attendance Roster';
        showAlert('error', err.message);
    });
}

function submitLessonRecord(e) {
    e.preventDefault();
    const form = document.getElementById('lessonForm');
    const btn = document.getElementById('saveLessonBtn');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const formData = new FormData(form);

    fetch('index.php?page=save_lesson_record', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = '💾 Save Lesson Record';
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('error', data.error || 'Failed to save lesson record.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = '💾 Save Lesson Record';
        showAlert('error', err.message);
    });
}

function submitClassroomRemark(e) {
    e.preventDefault();
    const form = document.getElementById('remarkForm');
    const btn = document.getElementById('saveRemarkBtn');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const formData = new FormData(form);

    fetch('index.php?page=save_classroom_remark', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = '💾 Save Remark';
        if (data.success) {
            showAlert('success', data.message);
            document.getElementById('remark_text').value = '';
            // Append remark to list
            const container = document.getElementById('remarksListContainer');
            const placeholder = document.getElementById('noRemarksPlaceholder');
            if (placeholder) placeholder.remove();

            const rDiv = document.createElement('div');
            rDiv.style.cssText = 'background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:14px;';
            rDiv.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px;">
                    <strong>💬 New Remark</strong>
                    <span style="color:#64748B; font-size:11px;">Just now</span>
                </div>
                <div style="font-size:11px; font-weight:700; color:#4338CA; margin-bottom:4px;">
                    Category: ${data.remark.type}
                </div>
                <p style="font-size:13px; color:#334155; margin:0;">${data.remark.text}</p>
            `;
            container.prepend(rDiv);
        } else {
            showAlert('error', data.error || 'Failed to save remark.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = '💾 Save Remark';
        showAlert('error', err.message);
    });
}

function deleteHomework(hwId) {
    if (!confirm('Are you sure you want to delete this homework assignment?')) return;
    const token = '<?= \App\Core\CSRF::getToken() ?>';

    const formData = new FormData();
    formData.append('id', hwId);
    formData.append('csrf_token', token);

    fetch('index.php?page=delete_homework', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            window.location.reload();
        } else {
            showAlert('error', data.error || 'Failed to delete homework.');
        }
    })
    .catch(err => showAlert('error', err.message));
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
