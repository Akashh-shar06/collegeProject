<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport" style="padding: 24px; max-width: 1440px; margin: 0 auto;">
    <!-- HOD Header Banner -->
    <div
        style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); color: #FFFFFF; padding: 26px 30px; border-radius: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: 0 12px 28px -6px rgba(49, 46, 129, 0.35);">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                <span style="font-size: 32px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🎓</span>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h2
                            style="font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; letter-spacing: -0.02em;">
                            HOD / Head of Department Portal
                        </h2>
                        <span
                            style="background: rgba(129, 140, 248, 0.25); color: #C7D2FE; border: 1px solid rgba(165, 180, 252, 0.35); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 12px; text-transform: uppercase;">
                            <?= htmlspecialchars($user['role_display'] ?? 'HOD / Head of Department', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <p style="font-size: 13px; color: #E0E7FF; margin: 4px 0 0 0; opacity: 0.95;">
                        <strong>Department:</strong>
                        <?= htmlspecialchars($deptInfo['name'] ?? $activeDeptName ?? 'Science Department', ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($deptInfo['code'])): ?>
                            <span
                                style="background: rgba(255,255,255,0.15); padding: 1px 7px; border-radius: 6px; font-size: 11px; font-weight: 700;"><?= htmlspecialchars($deptInfo['code'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        &bull; Logged in as:
                        <strong><?= htmlspecialchars($user['username'] ?? 'HOD', ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="index.php?page=timetable&open_modal=1" class="btn"
                style="background: #10B981; color: #FFFFFF; font-weight: 800; font-size: 13px; padding: 10px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); transition: transform 0.15s ease;"
                onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                <span>➕</span> Create Teacher Timetable
            </a>
            <a href="index.php?page=timetable" class="btn"
                style="background: rgba(255,255,255,0.15); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.3); font-weight: 700; font-size: 13px; padding: 10px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(4px);">
                <span>🗓️</span> Department Timetable
            </a>
            <a href="index.php?page=academic_hierarchy" class="btn"
                style="background: #FFFFFF; color: #312E81; font-weight: 800; font-size: 13px; padding: 10px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                <span>🏛️</span> Courses & Hierarchy
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div
            style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <span>✅</span> <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div
            style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- 1. KEY KPI METRICS SECTION WITH FUNCTIONAL VIEW DETAILS BUTTONS -->
    <section
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 26px;">
        <!-- Card 1: Department Teachers -->
        <div class="hod-kpi-card" onclick="openHodModal('teachers')"
            style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05); border-left: 5px solid #4F46E5; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; justify-content: space-between;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px -4px rgba(79, 70, 229, 0.15)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(15, 23, 42, 0.05)'">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 13px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Department
                        Teachers</span>
                    <span style="font-size: 24px; background: #EEF2FF; padding: 6px; border-radius: 10px;">👨‍🏫</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0F172A; line-height: 1;">
                    <?= (int) ($hodStats['teacher_count'] ?? count($deptTeachers)) ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 8px;">Teaching faculty under your department
                </div>
            </div>
            <div
                style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn" onclick="event.stopPropagation(); openHodModal('teachers');"
                    style="background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#E0E7FF'" onmouseout="this.style.background='#EEF2FF'">
                    <span>👁️</span> View Details &rarr;
                </button>
                <span style="font-size: 11px; font-weight: 600; color: #94A3B8;">Click to expand</span>
            </div>
        </div>

        <!-- Card 2: Timetable Lecture Slots -->
        <div class="hod-kpi-card" onclick="openHodModal('slots')"
            style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05); border-left: 5px solid #10B981; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; justify-content: space-between;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px -4px rgba(16, 185, 129, 0.15)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(15, 23, 42, 0.05)'">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 13px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Timetable
                        Lecture Slots</span>
                    <span style="font-size: 24px; background: #ECFDF5; padding: 6px; border-radius: 10px;">🗓️</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0F172A; line-height: 1;">
                    <?= (int) ($hodStats['slot_count'] ?? count($deptTimetables)) ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 8px;">Active scheduled slots across days</div>
            </div>
            <div
                style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn" onclick="event.stopPropagation(); openHodModal('slots');"
                    style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#D1FAE5'" onmouseout="this.style.background='#ECFDF5'">
                    <span>👁️</span> View Details &rarr;
                </button>
                <span style="font-size: 11px; font-weight: 600; color: #94A3B8;">Click to expand</span>
            </div>
        </div>

        <!-- Card 3: Degree Courses -->
        <div class="hod-kpi-card" onclick="openHodModal('courses')"
            style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05); border-left: 5px solid #3B82F6; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; justify-content: space-between;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px -4px rgba(59, 130, 246, 0.15)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(15, 23, 42, 0.05)'">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 13px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Degree
                        Courses</span>
                    <span style="font-size: 24px; background: #EFF6FF; padding: 6px; border-radius: 10px;">📚</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0F172A; line-height: 1;">
                    <?= (int) ($hodStats['course_count'] ?? count($deptCourses)) ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 8px;">Configured with FY, SY & TY years</div>
            </div>
            <div
                style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn" onclick="event.stopPropagation(); openHodModal('courses');"
                    style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#EFF6FF'">
                    <span>👁️</span> View Details &rarr;
                </button>
                <span style="font-size: 11px; font-weight: 600; color: #94A3B8;">Click to expand</span>
            </div>
        </div>

        <!-- Card 4: Total Department Students -->
        <div class="hod-kpi-card" onclick="openHodModal('students')"
            style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05); border-left: 5px solid #F59E0B; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; justify-content: space-between;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px -4px rgba(245, 158, 11, 0.15)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(15, 23, 42, 0.05)'">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 13px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total
                        Department Students</span>
                    <span style="font-size: 24px; background: #FFFBEB; padding: 6px; border-radius: 10px;">👥</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0F172A; line-height: 1;">
                    <?= number_format($hodStats['student_count'] ?? count($deptStudents ?? [])) ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 8px;">Enrolled across all department cohorts
                </div>
            </div>
            <div
                style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn" onclick="event.stopPropagation(); openHodModal('students');"
                    style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#FEF3C7'" onmouseout="this.style.background='#FFFBEB'">
                    <span>👁️</span> View Details &rarr;
                </button>
                <span style="font-size: 11px; font-weight: 600; color: #94A3B8;">Click to expand</span>
            </div>
        </div>
    </section>

    <!-- 2. TIMETABLE CREATION & MANAGEMENT SPOTLIGHT -->
    <section
        style="background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); border: 1px solid #C7D2FE; border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 680px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <span
                    style="background: #4F46E5; color: #FFF; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">HOD
                    Action Hub</span>
                <h3 style="font-size: 19px; font-weight: 800; color: #1E1B4B; margin: 0;">Teacher Timetable & Lecture
                    Schedule Engine</h3>
            </div>
            <p style="font-size: 13px; color: #3730A3; margin: 0; line-height: 1.5;">
                As <strong>Head of Department</strong>, you have complete authority to create, assign, and organize
                class timetables for all teachers under your department. Schedule weekly lectures, assign classrooms,
                prevent teacher slot conflicts, and manage student divisions smoothly.
            </p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="index.php?page=timetable&open_modal=1" class="btn"
                style="background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%); color: #FFFFFF; font-weight: 800; font-size: 13px; padding: 11px 22px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                <span>➕</span> + Create New Timetable Slot
            </a>
            <a href="index.php?page=timetable" class="btn"
                style="background: #FFFFFF; color: #4338CA; border: 1px solid #C7D2FE; font-weight: 700; font-size: 13px; padding: 11px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>📋</span> View Full Schedule Matrix
            </a>
        </div>
    </section>

    <!-- 3. DEPARTMENT TEACHERS ROSTER & TIMETABLE ASSIGNMENTS -->
    <section id="deptFacultySection"
        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; margin-bottom: 28px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.04);">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3
                    style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>👨‍🏫</span> Department Teaching Faculty & Schedule Workload
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">
                    Manage your teachers and create lecture schedules directly for each faculty member
                </p>
            </div>
            <a href="index.php?page=timetable&open_modal=1" class="btn btn-sm"
                style="background: #4F46E5; color: #FFF; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                ➕ Assign Timetable Slot
            </a>
        </div>

        <?php if (empty($deptTeachers)): ?>
            <div
                style="background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 32px; text-align: center;">
                <div style="font-size: 36px; margin-bottom: 8px;">👨‍🏫</div>
                <h4 style="font-size: 15px; font-weight: 700; color: #334155; margin-bottom: 4px;">No Teachers Found in
                    Department</h4>
                <p style="font-size: 12px; color: #64748B; margin-bottom: 14px;">Staff accounts can be assigned to this
                    department via Staff Management.</p>
                <a href="index.php?page=module&name=staff_data" class="btn btn-primary"
                    style="font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 8px; text-decoration: none;">View
                    Staff Directory</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr
                            style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; text-align: left; color: #475569; font-weight: 700;">
                            <th style="padding: 12px 16px;">Teacher Name & ID</th>
                            <th style="padding: 12px 16px;">Designation</th>
                            <th style="padding: 12px 16px;">Assigned Subjects</th>
                            <th style="padding: 12px 16px;">Username / Email</th>
                            <th style="padding: 12px 16px; text-align: center;">Assigned Slots</th>
                            <th style="padding: 12px 16px; text-align: right;">Timetable Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptTeachers as $teacher): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;"
                                onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                                <td style="padding: 14px 16px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                            style="width: 34px; height: 34px; border-radius: 50%; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                                            <?= strtoupper(substr($teacher['first_name'] ?? 'T', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong style="color: #0F172A; font-size: 13px;">
                                                Prof.
                                                <?= htmlspecialchars(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </strong>
                                            <?php if (!empty($teacher['emp_id'])): ?>
                                                <div style="font-size: 11px; color: #64748B;">EMP ID:
                                                    <?= htmlspecialchars($teacher['emp_id'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 14px 16px; color: #334155;">
                                    <span
                                        style="background: #F1F5F9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <?= htmlspecialchars($teacher['designation'] ?? 'Lecturer / Teacher', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if (!empty($teacher['assigned_subjects'])): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                            <?php foreach ($teacher['assigned_subjects'] as $sub): ?>
                                                <span
                                                    style="background: #EEF2FF; color: #4338CA; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                                    <?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94A3B8; font-size: 11px; font-style: italic;">None Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; color: #64748B; font-size: 12px;">
                                    <?= htmlspecialchars($teacher['user_email'] ?? $teacher['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    <span
                                        style="background: <?= ((int) ($teacher['slot_count'] ?? 0) > 0 ? '#ECFDF5' : '#F1F5F9') ?>; color: <?= ((int) ($teacher['slot_count'] ?? 0) > 0 ? '#059669' : '#64748B') ?>; font-weight: 800; font-size: 12px; padding: 3px 10px; border-radius: 12px; display: inline-block;">
                                        <?= (int) ($teacher['slot_count'] ?? 0) ?> slots
                                    </span>
                                </td>
                                <td style="padding: 14px 16px; text-align: right; white-space: nowrap;">
                                    <a href="index.php?page=timetable&open_modal=1&teacher_id=<?= (int) $teacher['id'] ?>"
                                        class="btn btn-sm"
                                        style="background: #10B981; color: #FFF; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px; text-decoration: none; margin-right: 6px;"
                                        title="Create timetable slot for this teacher">
                                        ➕ Add Slot
                                    </a>
                                    <a href="index.php?page=timetable&teacher_id=<?= (int) $teacher['id'] ?>" class="btn btn-sm"
                                        style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px; text-decoration: none;"
                                        title="View teacher schedule">
                                        🗓️ View Schedule
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 4. UPCOMING DEPARTMENT TIMETABLE SCHEDULE TABLE -->
    <section
        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; margin-bottom: 28px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.04);">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3
                    style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🗓️</span> Department Weekly Timetable Matrix
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">
                    Current scheduled lectures for courses and teachers in your department
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="index.php?page=timetable&open_modal=1" class="btn btn-sm"
                    style="background: #4F46E5; color: #FFF; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    ➕ + Add Timetable Entry
                </a>
                <a href="index.php?page=timetable" class="btn btn-sm"
                    style="background: #F1F5F9; color: #334155; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; text-decoration: none;">
                    Open Timetable Editor &rarr;
                </a>
            </div>
        </div>

        <?php if (empty($deptTimetables)): ?>
            <div
                style="background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 36px; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 8px;">🗓️</div>
                <h4 style="font-size: 16px; font-weight: 800; color: #1E293B; margin-bottom: 4px;">No Timetable Slots
                    Created Yet</h4>
                <p style="font-size: 13px; color: #64748B; max-width: 460px; margin: 0 auto 16px auto;">
                    As HOD, click below to schedule your teachers' lectures, subjects, time slots and classrooms.
                </p>
                <a href="index.php?page=timetable&open_modal=1" class="btn"
                    style="background: #4F46E5; color: #FFF; font-weight: 700; font-size: 13px; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    ➕ + Create First Teacher Timetable Slot
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr
                            style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; text-align: left; color: #475569; font-weight: 700;">
                            <th style="padding: 12px 14px;">Day</th>
                            <th style="padding: 12px 14px;">Time Slot</th>
                            <th style="padding: 12px 14px;">Subject</th>
                            <th style="padding: 12px 14px;">Assigned Teacher</th>
                            <th style="padding: 12px 14px;">Course / Division</th>
                            <th style="padding: 12px 14px;">Classroom</th>
                            <th style="padding: 12px 14px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptTimetables as $slot): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;"
                                onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                                <td style="padding: 12px 14px; font-weight: 700; color: #1E293B;">
                                    <span
                                        style="background: #EEF2FF; color: #4338CA; padding: 3px 8px; border-radius: 6px; font-size: 11px;">
                                        <?= htmlspecialchars($slot['day_of_week'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; color: #475569; font-weight: 600;">
                                    <?= htmlspecialchars($slot['time_slot'] ?? (date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time']))), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 12px 14px; font-weight: 700; color: #0F172A;">
                                    <?= htmlspecialchars($slot['subject_name_resolved'] ?: $slot['subject_name'] ?: 'Subject', ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($slot['subject_code'])): ?>
                                        <span
                                            style="font-size: 11px; color: #64748B; font-weight: 500;">(<?= htmlspecialchars($slot['subject_code'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px; color: #334155;">
                                    Prof.
                                    <?= htmlspecialchars(($slot['first_name'] ?? 'Staff') . ' ' . ($slot['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 12px 14px; color: #475569;">
                                    <?= htmlspecialchars($slot['course_name'] ?: $slot['course_code'] ?: 'Course', ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($slot['section_name'])): ?>
                                        &bull; Div <?= htmlspecialchars($slot['section_name'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <span
                                        style="background: #F1F5F9; color: #0F172A; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        🏛️
                                        <?= htmlspecialchars($slot['classroom_number'] ?: $slot['room_no'] ?: 'Room', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                                    <a href="index.php?page=timetable" class="btn btn-sm"
                                        style="background: #EEF2FF; color: #4F46E5; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; text-decoration: none;">
                                        ✏️ Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 5. DEPARTMENT DEGREE COURSES & COHORTS -->
    <section
        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.04);">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3
                    style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📚</span> Department Degree Programs & Academic Cohorts (FY, SY, TY)
                </h3>
                <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">
                    Courses and student cohorts under your department leadership
                </p>
            </div>
            <a href="index.php?page=academic_hierarchy" class="btn btn-sm"
                style="background: #3B82F6; color: #FFF; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; text-decoration: none;">
                Academic Hierarchy &rarr;
            </a>
        </div>

        <?php if (empty($deptCourses)): ?>
            <div
                style="background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 32px; text-align: center;">
                <div style="font-size: 36px; margin-bottom: 8px;">📚</div>
                <h4 style="font-size: 15px; font-weight: 700; color: #334155; margin-bottom: 4px;">No Courses Configured for
                    this Department</h4>
                <p style="font-size: 12px; color: #64748B; margin-bottom: 14px;">Courses can be added via the Academic
                    Hierarchy portal.</p>
                <a href="index.php?page=academic_hierarchy" class="btn btn-primary"
                    style="font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 8px; text-decoration: none;">Open
                    Academic Hierarchy</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach ($deptCourses as $crs): ?>
                    <div
                        style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; position: relative;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">
                                    <?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                                <span
                                    style="background: #EEF2FF; color: #4F46E5; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-top: 4px;">
                                    <?= htmlspecialchars($crs['code'] ?? 'DEGREE', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <span style="font-size: 20px;">🎓</span>
                        </div>
                        <div
                            style="display: flex; gap: 12px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #64748B;">
                            <div>
                                <strong><?= (int) ($crs['batch_count'] ?? 0) ?></strong> Batches
                            </div>
                            <div>&bull;</div>
                            <div>
                                <strong><?= (int) ($crs['student_count'] ?? 0) ?></strong> Students
                            </div>
                        </div>
                        <div style="margin-top: 12px;">
                            <a href="index.php?page=timetable&course_id=<?= (int) $crs['id'] ?>"
                                style="color: #4F46E5; font-size: 12px; font-weight: 700; text-decoration: none;">
                                View Timetable for Course &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<!-- HOD INTERACTIVE MODALS FOR KPI METRICS -->

<!-- 1. Teachers Modal -->
<div id="hodModal_teachers" class="hod-modal-overlay" onclick="if(event.target === this) closeHodModal('teachers')" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#FFFFFF; border-radius:16px; width:100%; max-width:960px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow:hidden; border:1px solid #E2E8F0;">
        <div style="background:linear-gradient(135deg, #1E1B4B 0%, #312E81 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px; background:rgba(255,255,255,0.15); padding:6px 10px; border-radius:10px;">👨‍🏫</span>
                <div>
                    <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF;">Department Teachers & Faculty</h3>
                    <div style="font-size:12px; color:#C7D2FE; margin-top:2px;">Showing <?= count($deptTeachers) ?> teaching faculty assigned to your department</div>
                </div>
            </div>
            <button type="button" onclick="closeHodModal('teachers')" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
        </div>
        
        <div style="padding:16px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:260px;">
                <input type="text" id="hodSearch_teachers" onkeyup="filterHodTable('teachers')" placeholder="🔍 Search by faculty name, employee ID, email..." style="width:100%; padding:9px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; background:#FFFFFF; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#4F46E5'" onblur="this.style.borderColor='#CBD5E1'">
            </div>
            <a href="index.php?page=teacher_timetable" class="btn" style="background:#4F46E5; color:#FFFFFF; font-size:12px; font-weight:700; padding:9px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>🗓️</span> View Master Timetable
            </a>
        </div>

        <div style="padding:0; overflow-y:auto; flex:1;">
            <table id="hodTable_teachers" style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F1F5F9; color:#475569; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #E2E8F0;">
                        <th style="padding:12px 20px;">Faculty Member</th>
                        <th style="padding:12px 16px;">Employee ID</th>
                        <th style="padding:12px 16px;">Contact</th>
                        <th style="padding:12px 16px;">Assigned Subjects</th>
                        <th style="padding:12px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody style="divide-y: 1px solid #F1F5F9;">
                    <?php if (empty($deptTeachers)): ?>
                        <tr><td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No teachers found for this department.</td></tr>
                    <?php else: ?>
                        <?php foreach ($deptTeachers as $t): ?>
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 20px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width:36px; height:36px; border-radius:50%; background:#EEF2FF; color:#4F46E5; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px;">
                                            <?= strtoupper(substr($t['first_name'] ?? 'P', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong style="color:#0F172A; font-size:14px;">Prof. <?= htmlspecialchars(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div style="font-size:11px; color:#64748B;"><?= htmlspecialchars($t['designation'] ?? 'Department Faculty', ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="background:#F1F5F9; color:#334155; padding:3px 8px; border-radius:6px; font-weight:700; font-size:12px; font-family:monospace;">
                                        <?= htmlspecialchars($t['emp_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="color:#334155; font-size:12px;"><?= htmlspecialchars($t['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="font-size:11px; color:#94A3B8;"><?= htmlspecialchars($t['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:14px 16px;">
                                    <?php if (!empty($t['assigned_subjects']) && is_array($t['assigned_subjects'])): ?>
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            <?php foreach ($t['assigned_subjects'] as $subName): ?>
                                                <span style="background:#EEF2FF; color:#4338CA; border:1px solid #C7D2FE; font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px;">
                                                    <?= htmlspecialchars($subName, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#94A3B8; font-size:12px; font-style:italic;">No active timetable slots</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;">
                                        <?= htmlspecialchars(ucfirst($t['status'] ?? 'Active'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="hodEmpty_teachers" style="display:none;"><td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No faculty matching search query.</td></tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding:14px 24px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:12px; color:#64748B;">Total Records: <strong><?= count($deptTeachers) ?></strong></span>
            <button type="button" class="btn" onclick="closeHodModal('teachers')" style="background:#E2E8F0; color:#334155; font-size:12px; font-weight:700; padding:7px 16px; border-radius:8px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<!-- 2. Timetable Slots Modal -->
<div id="hodModal_slots" class="hod-modal-overlay" onclick="if(event.target === this) closeHodModal('slots')" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#FFFFFF; border-radius:16px; width:100%; max-width:1050px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow:hidden; border:1px solid #E2E8F0;">
        <div style="background:linear-gradient(135deg, #064E3B 0%, #065F46 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px; background:rgba(255,255,255,0.15); padding:6px 10px; border-radius:10px;">🗓️</span>
                <div>
                    <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF;">Department Timetable Lecture Slots</h3>
                    <div style="font-size:12px; color:#A7F3D0; margin-top:2px;">Showing <?= count($deptTimetables) ?> active lecture schedules across the week</div>
                </div>
            </div>
            <button type="button" onclick="closeHodModal('slots')" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
        </div>
        
        <div style="padding:16px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:260px;">
                <input type="text" id="hodSearch_slots" onkeyup="filterHodTable('slots')" placeholder="🔍 Search by day, subject, course, teacher, room..." style="width:100%; padding:9px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; background:#FFFFFF; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#CBD5E1'">
            </div>
            <a href="index.php?page=teacher_timetable" class="btn" style="background:#10B981; color:#FFFFFF; font-size:12px; font-weight:700; padding:9px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>📅</span> Open Weekly Matrix View
            </a>
        </div>

        <div style="padding:0; overflow-y:auto; flex:1;">
            <table id="hodTable_slots" style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F1F5F9; color:#475569; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #E2E8F0;">
                        <th style="padding:12px 16px;">Day</th>
                        <th style="padding:12px 16px;">Time Slot</th>
                        <th style="padding:12px 16px;">Subject Name & Code</th>
                        <th style="padding:12px 16px;">Course & Cohort</th>
                        <th style="padding:12px 16px;">Assigned Teacher</th>
                        <th style="padding:12px 16px;">Room / Lab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deptTimetables)): ?>
                        <tr><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No timetable slots scheduled yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($deptTimetables as $slot): ?>
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 16px;">
                                    <span style="background:#EEF2FF; color:#4338CA; font-weight:700; font-size:12px; padding:3px 8px; border-radius:6px;">
                                        <?= htmlspecialchars($slot['day_of_week'] ?? 'Monday', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:12px 16px; font-weight:700; color:#0F172A;">
                                    <?= htmlspecialchars($slot['time_slot'] ?? (substr($slot['start_time'] ?? '09:00', 0, 5) . ' - ' . substr($slot['end_time'] ?? '10:00', 0, 5)), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px 16px;">
                                    <strong style="color:#0F172A;"><?= htmlspecialchars($slot['subject_name_resolved'] ?? $slot['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($slot['subject_code'])): ?>
                                        <span style="font-size:11px; color:#64748B;">(<?= htmlspecialchars($slot['subject_code'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px 16px;">
                                    <span style="background:#F1F5F9; color:#334155; font-size:12px; font-weight:600; padding:2px 8px; border-radius:4px;">
                                        <?= htmlspecialchars($slot['course_name'] ?? 'Course', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:12px 16px; color:#2563EB; font-weight:600;">
                                    Prof. <?= htmlspecialchars(($slot['first_name'] ?? '') . ' ' . ($slot['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px 16px;">
                                    <span style="background:#FEF3C7; color:#92400E; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">
                                        📍 <?= htmlspecialchars($slot['classroom_number'] ?? $slot['room_no'] ?? 'Room 101', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="hodEmpty_slots" style="display:none;"><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No lecture slots matching search query.</td></tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding:14px 24px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:12px; color:#64748B;">Total Scheduled Slots: <strong><?= count($deptTimetables) ?></strong></span>
            <button type="button" class="btn" onclick="closeHodModal('slots')" style="background:#E2E8F0; color:#334155; font-size:12px; font-weight:700; padding:7px 16px; border-radius:8px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<!-- 3. Degree Courses Modal -->
<div id="hodModal_courses" class="hod-modal-overlay" onclick="if(event.target === this) closeHodModal('courses')" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#FFFFFF; border-radius:16px; width:100%; max-width:900px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow:hidden; border:1px solid #E2E8F0;">
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px; background:rgba(255,255,255,0.15); padding:6px 10px; border-radius:10px;">📚</span>
                <div>
                    <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF;">Department Degree Courses</h3>
                    <div style="font-size:12px; color:#BFDBFE; margin-top:2px;">Showing <?= count($deptCourses) ?> academic degree programs offered</div>
                </div>
            </div>
            <button type="button" onclick="closeHodModal('courses')" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
        </div>
        
        <div style="padding:16px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:260px;">
                <input type="text" id="hodSearch_courses" onkeyup="filterHodTable('courses')" placeholder="🔍 Search by course name or code..." style="width:100%; padding:9px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; background:#FFFFFF; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#3B82F6'" onblur="this.style.borderColor='#CBD5E1'">
            </div>
        </div>

        <div style="padding:0; overflow-y:auto; flex:1;">
            <table id="hodTable_courses" style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F1F5F9; color:#475569; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #E2E8F0;">
                        <th style="padding:12px 20px;">Degree Course</th>
                        <th style="padding:12px 16px;">Program Code</th>
                        <th style="padding:12px 16px;">Batches</th>
                        <th style="padding:12px 16px;">Enrolled Students</th>
                        <th style="padding:12px 16px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deptCourses)): ?>
                        <tr><td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No courses configured for this department.</td></tr>
                    <?php else: ?>
                        <?php foreach ($deptCourses as $crs): ?>
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 20px;">
                                    <strong style="color:#0F172A; font-size:14px;"><?= htmlspecialchars($crs['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">
                                        <?= htmlspecialchars($crs['code'] ?? 'DEGREE', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-weight:600; color:#334155;">
                                    <?= (int) ($crs['batch_count'] ?? 0) ?> Batches
                                </td>
                                <td style="padding:14px 16px; font-weight:700; color:#0F172A;">
                                    <?= (int) ($crs['student_count'] ?? 0) ?> Students
                                </td>
                                <td style="padding:14px 16px;">
                                    <a href="index.php?page=timetable&course_id=<?= (int)$crs['id'] ?>" style="color:#4F46E5; font-size:12px; font-weight:700; text-decoration:none;">
                                        View Schedule &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="hodEmpty_courses" style="display:none;"><td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No courses matching search query.</td></tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding:14px 24px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:12px; color:#64748B;">Configured Courses: <strong><?= count($deptCourses) ?></strong></span>
            <button type="button" class="btn" onclick="closeHodModal('courses')" style="background:#E2E8F0; color:#334155; font-size:12px; font-weight:700; padding:7px 16px; border-radius:8px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<!-- 4. Department Students Modal -->
<div id="hodModal_students" class="hod-modal-overlay" onclick="if(event.target === this) closeHodModal('students')" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#FFFFFF; border-radius:16px; width:100%; max-width:1050px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow:hidden; border:1px solid #E2E8F0;">
        <div style="background:linear-gradient(135deg, #78350F 0%, #B45309 100%); color:#FFFFFF; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px; background:rgba(255,255,255,0.15); padding:6px 10px; border-radius:10px;">👥</span>
                <div>
                    <h3 style="font-size:18px; font-weight:800; margin:0; color:#FFFFFF;">Total Department Students</h3>
                    <div style="font-size:12px; color:#FDE68A; margin-top:2px;">Showing <?= count($deptStudents ?? []) ?> students enrolled under your department cohorts</div>
                </div>
            </div>
            <button type="button" onclick="closeHodModal('students')" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:22px; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
        </div>
        
        <div style="padding:16px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:260px;">
                <input type="text" id="hodSearch_students" onkeyup="filterHodTable('students')" placeholder="🔍 Search by roll no, admission no, student name, course..." style="width:100%; padding:9px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; background:#FFFFFF; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#F59E0B'" onblur="this.style.borderColor='#CBD5E1'">
            </div>
        </div>

        <div style="padding:0; overflow-y:auto; flex:1;">
            <table id="hodTable_students" style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F1F5F9; color:#475569; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #E2E8F0;">
                        <th style="padding:12px 16px;">Roll No / Adm No</th>
                        <th style="padding:12px 20px;">Student Name</th>
                        <th style="padding:12px 16px;">Degree Course</th>
                        <th style="padding:12px 16px;">Gender</th>
                        <th style="padding:12px 16px;">Contact</th>
                        <th style="padding:12px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deptStudents)): ?>
                        <tr><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No students enrolled under this department.</td></tr>
                    <?php else: ?>
                        <?php foreach ($deptStudents as $st): ?>
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 16px;">
                                    <span style="font-weight:700; color:#0F172A; font-family:monospace;">
                                        <?= htmlspecialchars($st['roll_no'] ?: ($st['admission_no'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:12px 20px;">
                                    <strong style="color:#0F172A;"><?= htmlspecialchars(trim(($st['first_name'] ?? '') . ' ' . ($st['middle_name'] ?? '') . ' ' . ($st['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td style="padding:12px 16px;">
                                    <span style="background:#FEF3C7; color:#92400E; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">
                                        <?= htmlspecialchars($st['course_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:12px 16px; color:#64748B;">
                                    <?= htmlspecialchars(ucfirst($st['gender'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding:12px 16px;">
                                    <div style="font-size:12px; color:#334155;"><?= htmlspecialchars($st['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="font-size:11px; color:#94A3B8;"><?= htmlspecialchars($st['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:12px 16px;">
                                    <span style="background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                        <?= htmlspecialchars(ucfirst($st['status'] ?? 'Active'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="hodEmpty_students" style="display:none;"><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No students matching search query.</td></tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding:14px 24px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:12px; color:#64748B;">Total Enrolled: <strong><?= count($deptStudents ?? []) ?></strong></span>
            <button type="button" class="btn" onclick="closeHodModal('students')" style="background:#E2E8F0; color:#334155; font-size:12px; font-weight:700; padding:7px 16px; border-radius:8px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<script>
function openHodModal(type) {
    const modal = document.getElementById('hodModal_' + type);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        const searchInput = document.getElementById('hodSearch_' + type);
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            filterHodTable(type);
        }
    }
}

function closeHodModal(type) {
    const modal = document.getElementById('hodModal_' + type);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function filterHodTable(type) {
    const input = document.getElementById('hodSearch_' + type);
    if (!input) return;
    const filter = input.value.toLowerCase().trim();
    const table = document.getElementById('hodTable_' + type);
    if (!table) return;
    const rows = table.getElementsByTagName('tr');
    let visibleCount = 0;
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].id === 'hodEmpty_' + type) continue;
        const text = rows[i].textContent.toLowerCase();
        if (text.indexOf(filter) > -1) {
            rows[i].style.display = '';
            visibleCount++;
        } else {
            rows[i].style.display = 'none';
        }
    }
    const emptyRow = document.getElementById('hodEmpty_' + type);
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

// Close on ESC key
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['teachers', 'slots', 'courses', 'students'].forEach(closeHodModal);
    }
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>