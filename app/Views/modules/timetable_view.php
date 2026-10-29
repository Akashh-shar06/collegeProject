<?php
// app/Views/modules/timetable_view.php
$pageTitle = "Department & Class Timetable Management";
require BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container-fluid py-4" style="background: #f8fafc; min-height: 100vh;">
<?php if ($role === 'student'): ?>
    <?php
    $currentDayName = date('l');
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $studentName = trim(($studentScope['first_name'] ?? '') . ' ' . ($studentScope['last_name'] ?? ''));
    if (empty($studentName)) $studentName = $user['username'] ?? 'Student';
    ?>
    <!-- Student Timetable Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="color: #0f172a; font-weight: 800;">
                <span style="font-size:28px;">📚</span> Student Timetable
            </h1>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($user['tenant_name'] ?? 'College MIS', ENT_QUOTES, 'UTF-8') ?> &bull; 
                Official Academic Lecture Schedule &bull;
                <span class="badge badge-success px-2 py-1" style="background: #10B981; font-weight: 700;">👁️ VIEW ONLY</span>
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span class="badge badge-pill badge-light border px-3 py-2" style="font-size: 13px; color: #475569; background: #FFFFFF; font-weight: 600;">
                📅 Session: <strong><?= htmlspecialchars($filters['academic_year'] ?? '2026-2027', ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 13px; background: #EEF2FF; color: #4338CA; font-weight: 700;">
                Today is <?= htmlspecialchars($currentDayName, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>

    <!-- Student Academic Meta Information Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%); color: #FFFFFF;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #6366F1; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; margin-right: 18px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(99,102,241,0.4);">
                            <?= strtoupper(substr($studentName, 0, 1)) ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h3 class="h5 font-weight-bold mb-0 text-white" style="font-weight: 800;">
                                    <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <span class="badge px-2 py-1" style="background: rgba(255,255,255,0.2); color: #E0E7FF; font-size: 11px; font-weight: 700;">
                                    Roll No: <?= htmlspecialchars($studentScope['roll_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if (!empty($studentScope['admission_no'])): ?>
                                    <span class="badge px-2 py-1" style="background: rgba(255,255,255,0.15); color: #C7D2FE; font-size: 11px;">
                                        ID: <?= htmlspecialchars($studentScope['admission_no'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="small mb-0 mt-1" style="color: #C7D2FE;">
                                🏢 <?= htmlspecialchars($studentScope['department_name'] ?? 'Assigned Department', ENT_QUOTES, 'UTF-8') ?> &bull; 
                                🎓 <?= htmlspecialchars($studentScope['course_name'] ?? 'Degree Program', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12">
                    <div class="row text-center text-lg-right g-2">
                        <div class="col-4">
                            <div style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 8px;">
                                <div style="font-size: 11px; color: #A5B4FC; text-transform: uppercase; font-weight: 700;">Year</div>
                                <div style="font-size: 14px; font-weight: 800; color: #FFFFFF; margin-top: 2px;">
                                    <?= htmlspecialchars($studentScope['academic_year_code'] ?? 'FY', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 8px;">
                                <div style="font-size: 11px; color: #A5B4FC; text-transform: uppercase; font-weight: 700;">Semester</div>
                                <div style="font-size: 14px; font-weight: 800; color: #FFFFFF; margin-top: 2px;">
                                    Sem <?= htmlspecialchars($studentScope['semester_id'] ?? '1', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 8px;">
                                <div style="font-size: 11px; color: #A5B4FC; text-transform: uppercase; font-weight: 700;">Division</div>
                                <div style="font-size: 14px; font-weight: 800; color: #FFFFFF; margin-top: 2px;">
                                    <?= htmlspecialchars($studentScope['class_name'] ?? ('Div ' . ($studentScope['section_name'] ?? 'A')), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Day Selector Tabs -->
    <div class="mb-4">
        <div class="nav nav-pills flex-wrap" id="studentDayTabs" style="gap: 8px;">
            <button type="button" class="btn btn-sm font-weight-bold student-day-tab active" data-day="all" onclick="filterStudentDay('all', this)" style="border-radius: 20px; padding: 8px 18px; border: 1px solid #CBD5E1; background: #0F172A; color: #FFFFFF; cursor: pointer;">
                📋 Full Week View
            </button>
            <?php foreach ($daysOfWeek as $d): ?>
                <?php
                $isToday = (strtolower($d) === strtolower($currentDayName));
                $daySlotCount = count($scheduleGrid[$d] ?? []);
                ?>
                <button type="button" class="btn btn-sm font-weight-bold student-day-tab" data-day="<?= $d ?>" onclick="filterStudentDay('<?= $d ?>', this)" style="border-radius: 20px; padding: 8px 16px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span><?= $d ?></span>
                    <?php if ($daySlotCount > 0): ?>
                        <span class="badge badge-secondary" style="background: #E2E8F0; color: #1E293B; font-size: 11px;"><?= $daySlotCount ?></span>
                    <?php endif; ?>
                    <?php if ($isToday): ?>
                        <span class="badge badge-success" style="background: #10B981; color: #FFFFFF; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 10px;">TODAY</span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Student Day-wise Lecture Cards Grid -->
    <?php if (empty($timetableRecords)): ?>
        <div class="card shadow-sm border-0 p-5 text-center" style="border-radius: 14px; background: #FFFFFF;">
            <div style="font-size: 52px; margin-bottom: 14px;">📭</div>
            <h4 class="font-weight-bold text-dark mb-2" style="font-weight: 800;">No Timetable Scheduled Yet</h4>
            <p class="text-muted mx-auto" style="max-width: 520px; font-size: 14px;">
                There are currently no scheduled lectures published for your assigned cohort (<?= htmlspecialchars($studentScope['department_name'] ?? 'Department', ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars($studentScope['academic_year_display'] ?? 'FY', ENT_QUOTES, 'UTF-8') ?>) in academic session <?= htmlspecialchars($filters['academic_year'] ?? '2026-2027', ENT_QUOTES, 'UTF-8') ?>. Please check back later or contact your department HOD.
            </p>
        </div>
    <?php else: ?>
        <div id="studentScheduleContainer" style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach ($daysOfWeek as $dayName): ?>
                <?php
                $daySlots = $scheduleGrid[$dayName] ?? [];
                $isToday = (strtolower($dayName) === strtolower($currentDayName));
                ?>
                <div class="student-day-section" id="daySection_<?= $dayName ?>" style="display: block;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="font-weight-bold mb-0" style="color: #0F172A; font-weight: 800; font-size: 18px;">
                                <?= $dayName ?>
                            </h5>
                            <?php if ($isToday): ?>
                                <span class="badge badge-success" style="background: #10B981; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 12px;">
                                    ★ TODAY'S SCHEDULE
                                </span>
                            <?php endif; ?>
                            <span class="text-muted small ml-2">(<?= count($daySlots) ?> Lecture<?= count($daySlots) === 1 ? '' : 's' ?>)</span>
                        </div>
                    </div>

                    <?php if (empty($daySlots)): ?>
                        <div class="card border-0 shadow-sm p-4 text-center mb-3" style="border-radius: 12px; background: #F8FAFC; border: 1px dashed #CBD5E1 !important;">
                            <div class="text-muted small">
                                ☕ No lectures scheduled on <strong><?= $dayName ?></strong>. Enjoy your study time!
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 8px;">
                            <?php foreach ($daySlots as $slot): ?>
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #FFFFFF; border-left: 5px solid #4F46E5 !important; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.boxShadow='0 10px 25px -5px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between" style="min-height: 160px;">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge badge-primary px-2 py-1 font-weight-bold" style="background: #EEF2FF; color: #4338CA; font-size: 12px; border-radius: 6px;">
                                                    ⏱️ <?= htmlspecialchars($slot['time_slot'] ?: (date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time']))), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span class="badge badge-light border px-2 py-1" style="font-size: 11px; background: #F1F5F9; color: #334155; font-weight: 700;">
                                                    🏛️ <?= htmlspecialchars($slot['classroom_number'] ?: $slot['room_no'] ?: 'Room TBA', ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                            <h6 class="font-weight-bold mb-1" style="color: #0F172A; font-size: 15px; font-weight: 800; line-height: 1.3;">
                                                <?= htmlspecialchars($slot['subject_name_resolved'] ?: $slot['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </h6>
                                            <?php if (!empty($slot['subject_code'])): ?>
                                                <span class="badge badge-secondary mb-2" style="font-size: 11px; background: #E2E8F0; color: #334155;">
                                                    Code: <?= htmlspecialchars($slot['subject_code'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="pt-2 border-top mt-2 d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #4F46E5; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; margin-right: 8px;">
                                                    <?= strtoupper(substr($slot['teacher_first_name'] ?? 'T', 0, 1)) ?>
                                                </div>
                                                <div class="small font-weight-bold" style="color: #1E293B; font-size: 12px;">
                                                    Prof. <?= htmlspecialchars(($slot['teacher_first_name'] ?? 'Faculty') . ' ' . ($slot['teacher_last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </div>
                                            <span class="badge badge-success px-2 py-1" style="background: #D1FAE5; color: #065F46; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                                                <?= htmlspecialchars($slot['status'] ?? 'Active', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
    function filterStudentDay(day, btn) {
        var buttons = document.querySelectorAll('.student-day-tab');
        buttons.forEach(function(b) {
            b.style.background = '#FFFFFF';
            b.style.color = '#334155';
            b.classList.remove('active');
        });
        btn.style.background = '#0F172A';
        btn.style.color = '#FFFFFF';
        btn.classList.add('active');

        var sections = document.querySelectorAll('.student-day-section');
        sections.forEach(function(sec) {
            if (day === 'all') {
                sec.style.display = 'block';
            } else {
                if (sec.id === 'daySection_' + day) {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = 'none';
                }
            }
        });
    }
    </script>
</div>
<?php else: ?>
    <!-- Page Header (Admin / Teacher / HOD) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="color: #0f172a; font-weight: 800;">
                <span style="font-size:28px;">🗓️</span> Department & Class Timetable Matrix
            </h1>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($user['tenant_name'] ?? 'College ERP', ENT_QUOTES, 'UTF-8') ?> — 
                Role: <span class="badge badge-primary px-2 py-1" style="background: #4f46e5;"><?= htmlspecialchars(strtoupper($role), ENT_QUOTES, 'UTF-8') ?></span>
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if (in_array($role, ['super_admin', 'school_admin', 'principal', 'teacher', 'hod'], true)): ?>
                <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="openAddTimetableModal()" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border:none; padding: 10px 18px; border-radius: 8px; cursor: pointer; color: #fff; font-size: 13px;">
                    ➕ + Add Timetable Entry
                </button>
                <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" onclick="openCopyTimetableModal()" style="background: #FFFFFF; border: 1px solid #CBD5E1; padding: 10px 16px; border-radius: 8px; cursor: pointer; color: #334155; font-size: 13px;">
                    📋 Copy / Duplicate Schedule
                </button>
            <?php endif; ?>
            <a href="index.php?page=teacher_timetable" class="btn font-weight-bold shadow-sm" style="background: #0F172A; color: #FFFFFF; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                🗓️ Weekly Matrix View &rarr;
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <strong>Success!</strong> <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <strong>Error!</strong> <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filter Control Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #ffffff;">
        <div class="card-body p-4">
            <h6 class="font-weight-bold mb-3" style="color: #334155; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                🔍 Search & Filter Class Timetables
            </h6>
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="timetable">
                
                <div class="col-md-2">
                    <label class="small text-muted font-weight-bold mb-1">Academic Year</label>
                    <select name="academic_year" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="2026-2027" <?= ($filters['academic_year'] === '2026-2027') ? 'selected' : '' ?>>2026 - 2027</option>
                        <option value="2025-2026" <?= ($filters['academic_year'] === '2025-2026') ? 'selected' : '' ?>>2025 - 2026</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small text-muted font-weight-bold mb-1">Department</label>
                    <select name="department_id" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="all">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name'] . ' (' . $d['code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small text-muted font-weight-bold mb-1">Course</label>
                    <select name="course_id" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="all">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small text-muted font-weight-bold mb-1">Semester</label>
                    <select name="semester_id" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="all">All Semesters</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= ($filters['semester_id'] == $i) ? 'selected' : '' ?>>Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small text-muted font-weight-bold mb-1">Division / Section</label>
                    <select name="section_id" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="all">All Divisions</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= ($filters['section_id'] == $sec['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark btn-sm w-100 font-weight-bold" style="border-radius: 6px; padding: 7px; cursor: pointer;">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Timetable Grid Section -->
    <div class="card shadow-sm border-0" style="border-radius: 12px; background: #ffffff; overflow: hidden;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold mb-0" style="color: #0f172a; font-size: 16px;">
                📅 Timetable Schedule Matrix
            </h6>
            <span class="badge badge-light text-dark border px-3 py-2" style="font-size: 13px;">
                Total Slots Listed: <strong><?= count($timetableRecords) ?></strong>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle text-center" style="font-size: 14px;">
                    <thead style="background: #0f172a; color: #ffffff;">
                        <tr>
                            <th style="width: 15%; padding: 14px;">Day of Week</th>
                            <th style="width: 20%; padding: 14px;">Time Slot</th>
                            <th style="width: 25%; padding: 14px;">Subject & Code</th>
                            <th style="width: 20%; padding: 14px;">Assigned Teacher</th>
                            <th style="width: 12%; padding: 14px;">Classroom</th>
                            <?php if (in_array($role, ['super_admin', 'school_admin', 'principal', 'teacher', 'hod'], true)): ?>
                                <th style="width: 8%; padding: 14px;">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($timetableRecords)): ?>
                            <tr>
                                <td colspan="6" class="py-5 text-muted">
                                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                                    <h5 class="font-weight-bold mb-1" style="color: #64748b;">No Timetable Slots Found</h5>
                                    <p class="small mb-0">No schedule records match the current filters or department permissions.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($timetableRecords as $row): ?>
                                <tr>
                                    <td class="font-weight-bold" style="background: #f8fafc; color: #334155; vertical-align: middle;">
                                        <span class="badge badge-info px-2 py-1" style="background: #e2e8f0; color: #1e293b; font-size: 13px;">
                                            <?= htmlspecialchars($row['day_of_week'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;" class="font-weight-bold text-dark">
                                        ⏱️ <?= htmlspecialchars($row['time_slot'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align: middle; text-align: left;" class="px-3">
                                        <strong style="color: #1e293b; font-size: 15px;">
                                            <?= htmlspecialchars($row['subject_name_resolved'] ?: $row['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                        <?php if (!empty($row['subject_code'])): ?>
                                            <span class="badge badge-secondary ml-1"><?= htmlspecialchars($row['subject_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <div class="small text-muted mt-1">
                                            <?= htmlspecialchars($row['department_name'] ?? 'General Dept', ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($row['course_code'])): ?> &bull; <?= htmlspecialchars($row['course_code'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                            <?php if (!empty($row['semester_id'])): ?> &bull; Sem <?= htmlspecialchars($row['semester_id'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                            <?php if (!empty($row['section_name'])): ?> &bull; Div <?= htmlspecialchars($row['section_name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #4f46e5; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 8px;">
                                                <?= strtoupper(substr($row['teacher_first_name'] ?? 'T', 0, 1)) ?>
                                            </div>
                                            <div class="text-left">
                                                <div class="font-weight-bold" style="color: #0f172a;">
                                                    Prof. <?= htmlspecialchars(($row['teacher_first_name'] ?? 'Staff') . ' ' . ($row['teacher_last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span class="badge badge-pill badge-light border px-3 py-2" style="font-size: 13px; background: #f1f5f9; color: #0f172a;">
                                            🏛️ <?= htmlspecialchars($row['classroom_number'] ?: $row['room_no'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <?php if (in_array($role, ['super_admin', 'school_admin', 'principal', 'teacher', 'hod'], true)): ?>
                                        <td style="vertical-align: middle; white-space: nowrap;">
                                            <a href="index.php?page=lecture_details&id=<?= (int)$row['id'] ?>&date=<?= date('Y-m-d') ?>" class="btn btn-outline-primary btn-sm" style="border-radius: 6px; padding: 4px 8px; font-size: 12px; font-weight: 700; text-decoration: none;" title="Open Classroom Session">
                                                ⚡ Session
                                            </a>
                                            <button type="button" onclick="openEditTimetableModal(<?= (int)$row['id'] ?>)" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;" title="Edit Timetable Entry">
                                                ✏️ Edit
                                            </button>
                                            <form method="POST" action="index.php?page=delete_timetable" onsubmit="return confirm('Are you sure you want to delete this slot?');" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 6px; padding: 4px 8px; font-size: 12px; cursor: pointer;" title="Delete Slot">
                                                    🗑️
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: Add Timetable Entry -->
<?php if (in_array($role, ['super_admin', 'school_admin', 'principal', 'teacher', 'hod'], true)): ?>
<div class="modal-overlay" id="addTimetableModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width: 720px; width:92%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1); margin:auto;">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h5 class="modal-title font-weight-bold mb-0" style="color:#ffffff; font-size:18px;">
                ➕ Create New Class Timetable Entry
            </h5>
            <button type="button" onclick="closeAddTimetableModal()" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=create_timetable" onsubmit="return validateTimetableForm(this);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            <input type="hidden" name="section_id" id="tt_section_id" value="">
            
            <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Academic Year *</label>
                        <input type="text" name="academic_year" class="form-control" value="2026-2027" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Department *</label>
                        <select name="department_id" id="tt_department_id" class="form-control" onchange="loadTTStandards(this.value); loadTTSubjects();" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . $d['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Standard / Year *</label>
                        <select name="standard_id" id="tt_standard_id" class="form-control" onchange="loadTTSubjects();" required>
                            <option value="">-- Select Standard / Year --</option>
                            <option value="1">FY (First Year)</option>
                            <option value="2">SY (Second Year)</option>
                            <option value="3" selected>TY (Third Year)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Course / Program *</label>
                        <select name="course_id" id="tt_course_id" class="form-control" onchange="loadTTSubjects();" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Day of Week *</label>
                        <select name="day_of_week" class="form-control" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Start Time *</label>
                        <input type="time" name="start_time" class="form-control" value="07:10" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">End Time *</label>
                        <input type="time" name="end_time" class="form-control" value="08:00" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Subject / Course *</label>
                        <select name="subject_id" id="tt_subject_id" class="form-control" onchange="loadTTTeachers(this.value)" required>
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name'] . ' (' . $sub['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Assigned Teacher *</label>
                        <select name="staff_id" id="tt_staff_id" class="form-control" onchange="loadTTSubjectsForTeacher(this.value)" required>
                            <option value="">-- Select Teacher --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>">Prof. <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Classroom / Room *</label>
                        <input type="text" name="room_no" class="form-control" value="Block 101" placeholder="e.g. Block 101" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light p-3" style="display:flex; justify-content:flex-end; gap:10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-secondary font-weight-bold" onclick="closeAddTimetableModal()">Cancel</button>
                <button type="submit" class="btn btn-primary font-weight-bold" style="background: #4f46e5; border:none; cursor:pointer;">
                    Save Timetable Entry
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Timetable Entry -->
<div class="modal-overlay" id="editTimetableModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width: 720px; width:92%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); margin:auto;">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h5 class="modal-title font-weight-bold mb-0" style="color:#ffffff; font-size:18px;">
                ✏️ Edit Class Timetable Entry
            </h5>
            <button type="button" onclick="closeEditTimetableModal()" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=update_timetable" onsubmit="return validateTimetableForm(this);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            <input type="hidden" name="id" id="edit_tt_id" value="">
            <input type="hidden" name="section_id" id="edit_tt_section_id" value="">
            
            <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Academic Year *</label>
                        <input type="text" name="academic_year" id="edit_tt_academic_year" class="form-control" value="2026-2027" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Department *</label>
                        <select name="department_id" id="edit_tt_department_id" class="form-control" required>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . $d['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Course / Program</label>
                        <select name="course_id" id="edit_tt_course_id" class="form-control">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Day of Week *</label>
                        <select name="day_of_week" id="edit_tt_day_of_week" class="form-control" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Start Time *</label>
                        <input type="time" name="start_time" id="edit_tt_start_time" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">End Time *</label>
                        <input type="time" name="end_time" id="edit_tt_end_time" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Subject *</label>
                        <select name="subject_id" id="edit_tt_subject_id" class="form-control">
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name'] . ' (' . $sub['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Assigned Teacher *</label>
                        <select name="staff_id" id="edit_tt_staff_id" class="form-control" onchange="loadTTSubjectsForTeacher(this.value, 'edit_tt_subject_id')" required>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>">Prof. <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . ' (' . $t['emp_id'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Classroom / Room *</label>
                        <input type="text" name="room_no" id="edit_tt_room_no" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-muted">Slot Status</label>
                        <select name="status" id="edit_tt_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive / Suspended</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3" style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary font-weight-bold" onclick="closeEditTimetableModal()">Cancel</button>
                <button type="submit" class="btn btn-primary font-weight-bold" style="background:#2563EB; border:none; cursor:pointer;">
                    Update Timetable Entry
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Copy / Duplicate Timetable -->
<div class="modal-overlay" id="copyTimetableModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.65); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width: 580px; width:92%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); margin:auto;">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f172a 0%, #334155 100%); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h5 class="modal-title font-weight-bold mb-0" style="color:#ffffff; font-size:18px;">
                📋 Copy / Duplicate Schedule Across Days
            </h5>
            <button type="button" onclick="closeCopyTimetableModal()" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="index.php?page=copy_timetable">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
            
            <div class="modal-body p-4">
                <p style="font-size: 13px; color: #64748B; margin-bottom: 16px;">
                    Duplicate recurring timetable slots from a source day to a target day. Scheduling conflicts for teachers, rooms, and classes will be automatically detected and safely avoided.
                </p>

                <div class="mb-3">
                    <label class="font-weight-bold small text-muted">Source Day *</label>
                    <select name="source_day" class="form-control" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold small text-muted">Target Day to Copy Into *</label>
                    <select name="target_day" class="form-control" required>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Monday">Monday</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold small text-muted">Class / Division Filter (Optional)</label>
                    <select name="section_id" class="form-control">
                        <option value="0">All Classes in Source Day</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer bg-light p-3" style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary font-weight-bold" onclick="closeCopyTimetableModal()">Cancel</button>
                <button type="submit" class="btn btn-primary font-weight-bold" style="background:#0F172A; border:none; cursor:pointer;">
                    Run Duplication Engine
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddTimetableModal() {
    const modal = document.getElementById('addTimetableModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

function closeAddTimetableModal() {
    const modal = document.getElementById('addTimetableModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

function openEditTimetableModal(id) {
    const modal = document.getElementById('editTimetableModal');
    if (!modal) return;

    fetch('index.php?page=api_timetable_entry&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.entry) {
                const e = data.entry;
                document.getElementById('edit_tt_id').value = e.id;
                document.getElementById('edit_tt_academic_year').value = e.academic_year || '2026-2027';
                document.getElementById('edit_tt_department_id').value = e.department_id || '';
                document.getElementById('edit_tt_course_id').value = e.course_id || '';
                document.getElementById('edit_tt_section_id').value = e.section_id || '';
                document.getElementById('edit_tt_day_of_week').value = e.day_of_week || 'Monday';
                document.getElementById('edit_tt_start_time').value = e.start_time ? e.start_time.substring(0, 5) : '07:10';
                document.getElementById('edit_tt_end_time').value = e.end_time ? e.end_time.substring(0, 5) : '08:00';
                document.getElementById('edit_tt_subject_id').value = e.subject_id || '';
                document.getElementById('edit_tt_staff_id').value = e.staff_id || '';
                document.getElementById('edit_tt_room_no').value = e.room_no || '';
                document.getElementById('edit_tt_status').value = e.status || 'active';

                modal.style.display = 'flex';
                modal.classList.add('active');
            } else {
                alert('Could not load timetable details.');
            }
        })
        .catch(err => alert('Error loading timetable entry: ' + err.message));
}

function closeEditTimetableModal() {
    const modal = document.getElementById('editTimetableModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

function openCopyTimetableModal() {
    const modal = document.getElementById('copyTimetableModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

function closeCopyTimetableModal() {
    const modal = document.getElementById('copyTimetableModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

function loadTTStandards(deptId) {
    const stdSelect = document.getElementById('tt_standard_id');
    if (!stdSelect) return;
    stdSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch('index.php?page=api_standards&department_id=' + deptId)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.length > 0) {
                let html = '<option value="">-- Select Standard / Year --</option>';
                data.data.forEach(item => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                stdSelect.innerHTML = html;
            } else {
                stdSelect.innerHTML = '<option value="">No standards available</option>';
            }
        })
        .catch(err => {
            stdSelect.innerHTML = '<option value="">-- Select Standard / Year --</option>';
        });
}

function loadTTClasses(standardId) {
    const deptId = document.getElementById('tt_department_id')?.value || 0;
    const secSelect = document.getElementById('tt_section_id');
    if (!secSelect) return;
    secSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`index.php?page=api_classes&standard_id=${standardId}&department_id=${deptId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.length > 0) {
                let html = '<option value="">-- Select Class / Division --</option>';
                data.data.forEach(item => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                secSelect.innerHTML = html;
            } else {
                secSelect.innerHTML = '<option value="">No classes available</option>';
            }
        })
        .catch(err => {
            secSelect.innerHTML = '<option value="">-- Select Class / Division --</option>';
        });
}

function validateTimetableForm(form) {
    const startTime = form.start_time.value;
    const endTime = form.end_time.value;
    if (startTime && endTime && startTime >= endTime) {
        alert('Invalid Time Range: Lecture End Time must be later than Start Time.');
        return false;
    }
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Saving...';
    }
    return true;
}

function loadTTSubjects() {
    const deptId = document.getElementById('tt_department_id')?.value || 0;
    const stdId = document.getElementById('tt_standard_id')?.value || 0;
    const courseId = document.getElementById('tt_course_id')?.value || 0;
    const subSelect = document.getElementById('tt_subject_id');
    if (!subSelect) return;
    subSelect.innerHTML = '<option value="">Loading subjects...</option>';
    
    fetch(`index.php?page=api_subjects&course_id=${courseId}&standard_id=${stdId}&department_id=${deptId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.length > 0) {
                let html = '<option value="">-- Select Subject --</option>';
                data.data.forEach(item => {
                    html += `<option value="${item.id}">${item.name} (${item.code || 'SUB'})</option>`;
                });
                subSelect.innerHTML = html;
            } else {
                subSelect.innerHTML = '<option value="">No subjects found for selection</option>';
            }
        })
        .catch(err => {
            subSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        });
}

function loadTTSubjectsForTeacher(teacherId, targetSelectId = 'tt_subject_id') {
    const subSelect = document.getElementById(targetSelectId);
    if (!subSelect) return;
    if (!teacherId) {
        if (targetSelectId === 'tt_subject_id') loadTTSubjects();
        return;
    }
    const deptId = document.getElementById('tt_department_id')?.value || 0;
    subSelect.innerHTML = '<option value="">Loading teacher timetable subjects...</option>';

    fetch(`index.php?page=api_subjects&staff_id=${teacherId}&department_id=${deptId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.length > 0) {
                let html = '<option value="">-- Select Subject (Teacher Timetable) --</option>';
                data.data.forEach(item => {
                    html += `<option value="${item.id}">${item.name} (${item.code || 'SUB'})</option>`;
                });
                subSelect.innerHTML = html;
            } else {
                if (targetSelectId === 'tt_subject_id') loadTTSubjects();
            }
        })
        .catch(err => {
            if (targetSelectId === 'tt_subject_id') loadTTSubjects();
        });
}

function loadTTTeachers(subjectId) {
    const deptId = document.getElementById('tt_department_id')?.value || 0;
    const tSelect = document.getElementById('tt_staff_id');
    if (!tSelect) return;
    tSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`index.php?page=api_teachers&subject_id=${subjectId}&department_id=${deptId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.length > 0) {
                let html = '<option value="">-- Select Teacher --</option>';
                data.data.forEach(item => {
                    html += `<option value="${item.id}">Prof. ${item.first_name} ${item.last_name}</option>`;
                });
                tSelect.innerHTML = html;
            } else {
                tSelect.innerHTML = '<option value="">No teachers available</option>';
            }
        })
        .catch(err => {
            tSelect.innerHTML = '<option value="">-- Select Teacher --</option>';
        });
}

// Auto-open Add Timetable modal if requested via URL
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_modal') === '1') {
        openAddTimetableModal();
    }
});
</script>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>

