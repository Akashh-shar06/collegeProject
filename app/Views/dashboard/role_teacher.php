<?php
/**
 * Campus360 Enterprise ERP - Master Teacher Dashboard & Faculty Workspace
 */
require BASE_PATH . '/app/Views/layouts/header.php';
?>

<main class="dashboard-viewport" style="padding: 24px; background: #F8FAFC; min-height: 100vh;">
    <!-- Flash Notifications -->
    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; font-weight: 600; font-size: 14px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">✅</span>
                <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#065F46; font-size:18px; cursor:pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; font-weight: 600; font-size: 14px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">⚠️</span>
                <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#991B1B; font-size:18px; cursor:pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Master Teacher Hero Banner -->
    <div style="background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); border-radius: 18px; padding: 28px 32px; color: #FFFFFF; margin-bottom: 28px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -20px; top: -20px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; pointer-events: none;"></div>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; color: #FFFFFF; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4); border: 3px solid rgba(255,255,255,0.2);">
                <?= strtoupper(substr($staff['first_name'] ?? $user['username'] ?? 'T', 0, 1) . substr($staff['last_name'] ?? 'P', 0, 1)) ?>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <h2 style="font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px; color: #FFFFFF;">
                        <?= htmlspecialchars(($staff['first_name'] ?? 'Faculty') . ' ' . ($staff['last_name'] ?? $user['username']), ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <span style="background: rgba(59, 130, 246, 0.25); color: #93C5FD; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(147, 197, 253, 0.3); text-transform: uppercase;">
                        <?= htmlspecialchars($staff['designation'] ?? 'Senior Faculty', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span style="background: rgba(16, 185, 129, 0.2); color: #6EE7B7; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(110, 231, 183, 0.3);">
                        ID: <?= htmlspecialchars($staff['emp_id'] ?? 'EMP-001', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px; font-size: 13px; color: #94A3B8;">
                    <span>🏢 <?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?></span>
                    <span>•</span>
                    <span>📆 Session: <strong style="color: #F8FAFC;"><?= htmlspecialchars($currentSessionName, ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <span>•</span>
                    <span>📅 Today: <strong style="color: #F8FAFC;"><?= date('l, d F Y') ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Top Action Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; z-index: 2;">
            <a href="#studentRosterSection" class="btn" style="background: #10B981; color: #FFFFFF; font-weight: 700; font-size: 13px; padding: 10px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); transition: all 0.2s;">
                <span>👥</span> Student Roster
            </a>
            <a href="index.php?page=teacher_timetable" class="btn" style="background: #3B82F6; color: #FFFFFF; font-weight: 700; font-size: 13px; padding: 10px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35); transition: all 0.2s;">
                <span>🗓️</span> Weekly Timetable
            </a>
            <a href="index.php?page=module&name=leave_management" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #FFFFFF; font-weight: 600; font-size: 13px; padding: 10px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
                <span>📅</span> Apply Leave
            </a>
        </div>
    </div>

    <!-- Real Statistical KPI Cards (Click to Filter) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <!-- Card 1: Authorized Department Students -->
        <a href="#studentRosterSection" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: var(--shadow-sm); transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Authorized Students</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">👥</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #059669; line-height: 1;"><?= (int)($stats['total_dept_students'] ?? 0) ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">In your department cohort</div>
            </div>
        </a>

        <!-- Card 2: Today's Lectures -->
        <a href="index.php?page=dashboard&filter=all" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid <?= ($currentFilter === 'all') ? '#3B82F6' : '#E2E8F0' ?>; box-shadow: <?= ($currentFilter === 'all') ? '0 4px 14px rgba(59,130,246,0.15)' : 'var(--shadow-sm)' ?>; transition: all 0.2s; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Today's Lectures</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 18px;">📚</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0F172A; line-height: 1;"><?= (int)$stats['today_total'] ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Total scheduled for <?= $todayDay ?></div>
            </div>
        </a>

        <!-- Card 3: Completed Lectures -->
        <a href="index.php?page=dashboard&filter=completed" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid <?= ($currentFilter === 'completed') ? '#10B981' : '#E2E8F0' ?>; box-shadow: <?= ($currentFilter === 'completed') ? '0 4px 14px rgba(16,185,129,0.15)' : 'var(--shadow-sm)' ?>; transition: all 0.2s; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Completed</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">✅</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #10B981; line-height: 1;"><?= (int)$stats['completed'] ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Attendance marked today</div>
            </div>
        </a>

        <!-- Card 4: Upcoming Lectures -->
        <a href="index.php?page=dashboard&filter=upcoming" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid <?= ($currentFilter === 'upcoming') ? '#3B82F6' : '#E2E8F0' ?>; box-shadow: <?= ($currentFilter === 'upcoming') ? '0 4px 14px rgba(59,130,246,0.15)' : 'var(--shadow-sm)' ?>; transition: all 0.2s; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Upcoming Today</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #F0F9FF; color: #0284C7; display: flex; align-items: center; justify-content: center; font-size: 18px;">⏳</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #0284C7; line-height: 1;"><?= (int)$stats['upcoming'] ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Yet to commence today</div>
            </div>
        </a>

        <!-- Card 5: Pending Attendance -->
        <a href="index.php?page=dashboard&filter=pending_attendance" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid <?= ($currentFilter === 'pending_attendance') ? '#F59E0B' : '#E2E8F0' ?>; box-shadow: <?= ($currentFilter === 'pending_attendance') ? '0 4px 14px rgba(245,158,11,0.15)' : 'var(--shadow-sm)' ?>; transition: all 0.2s; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Pending Attendance</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">📝</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #D97706; line-height: 1;"><?= (int)$stats['pending_attendance'] ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Action required now</div>
            </div>
        </a>

        <!-- Card 6: Active Homework -->
        <a href="#homeworkSection" style="text-decoration: none; color: inherit;">
            <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: var(--shadow-sm); transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Active Homework</span>
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: #F5F3FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 18px;">📑</span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #7C3AED; line-height: 1;"><?= (int)$stats['active_homework'] ?></div>
                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Assignments with open deadlines</div>
            </div>
        </a>
    </div>

    <!-- Main Workspace 2-Column Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1.1fr; gap: 24px; margin-bottom: 30px;">
        <!-- Left Column: Today's Scheduled Lectures Matrix -->
        <div>
            <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>🗓️</span> Today's Lecture Schedule
                            <?php if ($currentFilter !== 'all'): ?>
                                <span style="font-size: 12px; font-weight: 600; background: #EEF2FF; color: #4F46E5; padding: 2px 10px; border-radius: 20px;">
                                    Filter: <?= ucfirst(str_replace('_', ' ', $currentFilter)) ?>
                                </span>
                            <?php endif; ?>
                        </h3>
                        <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">
                            Live timetable for <?= date('l, d M Y') ?> — Click any card to record attendance and lesson notes
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="index.php?page=teacher_timetable" style="font-size: 12px; font-weight: 700; color: #2563EB; text-decoration: none; padding: 6px 12px; border-radius: 8px; background: #EFF6FF;">
                            View Full Week &rarr;
                        </a>
                    </div>
                </div>

                <div style="padding: 20px 24px;">
                    <?php if (empty($todayLectures)): ?>
                        <div style="text-align: center; padding: 48px 20px; color: #64748B;">
                            <div style="font-size: 48px; margin-bottom: 12px;">☕</div>
                            <h4 style="font-size: 16px; font-weight: 700; color: #334155; margin-bottom: 6px;">No lectures scheduled today</h4>
                            <p style="font-size: 13px; max-width: 420px; margin: 0 auto 16px auto;">
                                <?php if ($currentFilter !== 'all'): ?>
                                    No lectures match the '<?= htmlspecialchars($currentFilter) ?>' filter.
                                    <br><a href="index.php?page=dashboard" style="color:#2563EB; font-weight:600;">Reset Filters</a>
                                <?php else: ?>
                                    You have no teaching lectures assigned for <?= $todayDay ?> in the active timetable. You can view your full weekly calendar or prepare lesson records.
                                <?php endif; ?>
                            </p>
                            <a href="index.php?page=teacher_timetable" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px; border-radius: 8px;">View Full Weekly Timetable</a>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <?php foreach ($todayLectures as $lec): ?>
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; transition: all 0.2s;" onmouseover="this.style.borderColor='#93C5FD'; this.style.backgroundColor='#F0F9FF';" onmouseout="this.style.borderColor='#E2E8F0'; this.style.backgroundColor='#F8FAFC';">
                                    <div style="display: flex; align-items: flex-start; gap: 16px;">
                                        <!-- Time Badge -->
                                        <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 10px 14px; text-align: center; min-width: 95px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                            <span style="display: block; font-size: 14px; font-weight: 800; color: #0F172A; line-height: 1.2;">
                                                <?= date('g:i A', strtotime($lec['start_time'])) ?>
                                            </span>
                                            <span style="display: block; font-size: 11px; font-weight: 600; color: #64748B;">
                                                to <?= date('g:i A', strtotime($lec['end_time'])) ?>
                                            </span>
                                        </div>

                                        <!-- Lecture Meta -->
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                <h4 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">
                                                    <?= htmlspecialchars($lec['resolved_subject_name'] ?? $lec['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?>
                                                </h4>
                                                <?php if (!empty($lec['subject_code'])): ?>
                                                    <span style="font-size: 11px; font-weight: 700; background: #E2E8F0; color: #475569; padding: 2px 6px; border-radius: 4px;">
                                                        <?= htmlspecialchars($lec['subject_code'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-size: 12px; color: #475569;">
                                                <span>👥 <strong><?= htmlspecialchars($lec['section_name'] ?? 'Class A', ENT_QUOTES, 'UTF-8') ?></strong></span>
                                                <span>•</span>
                                                <span>🎓 <?= htmlspecialchars($lec['course_name'] ?? $lec['department_name'] ?? 'Dept', ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if (!empty($lec['semester_name'])): ?>
                                                    <span>(<?= htmlspecialchars($lec['semester_name'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                                <?php endif; ?>
                                                <span>•</span>
                                                <span>📍 Room: <strong><?= htmlspecialchars($lec['classroom_number'] ?? $lec['room_no'] ?? 'TBD', ENT_QUOTES, 'UTF-8') ?></strong></span>
                                            </div>

                                            <!-- Status indicators -->
                                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                                <?php if ($lec['computed_status'] === 'completed'): ?>
                                                    <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 3px 8px; border-radius: 6px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 4px;">
                                                        ✓ Attendance Marked (<?= (int)($lec['attendance_session']['present_count'] ?? 0) ?>/<?= (int)($lec['attendance_session']['total_students'] ?? 0) ?> Present)
                                                    </span>
                                                <?php elseif ($lec['computed_status'] === 'pending_attendance'): ?>
                                                    <span style="font-size: 11px; font-weight: 700; color: #D97706; background: #FFFBEB; padding: 3px 8px; border-radius: 6px; border: 1px solid #FDE68A; display: inline-flex; align-items: center; gap: 4px;">
                                                        ⚠️ Attendance Pending
                                                    </span>
                                                <?php else: ?>
                                                    <span style="font-size: 11px; font-weight: 700; color: #0284C7; background: #F0F9FF; padding: 3px 8px; border-radius: 6px; border: 1px solid #BAE6FD; display: inline-flex; align-items: center; gap: 4px;">
                                                        ⏳ Upcoming Lecture
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($lec['lesson_record'])): ?>
                                                    <span style="font-size: 11px; font-weight: 600; color: #4338CA; background: #EEF2FF; padding: 3px 8px; border-radius: 6px;">
                                                        📖 Topic: <?= htmlspecialchars(substr($lec['lesson_record']['topic_title'], 0, 25) . (strlen($lec['lesson_record']['topic_title']) > 25 ? '...' : ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div>
                                        <a href="index.php?page=lecture_details&id=<?= (int)$lec['id'] ?>&date=<?= $todayDate ?>" class="btn" style="background: #2563EB; color: #FFFFFF; font-weight: 700; font-size: 13px; padding: 9px 16px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
                                            <span>⚡</span> Open Session
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Widgets (Last Lesson Taught, Homework, Assigned Subjects) -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Widget 1: Last Lesson Taught -->
            <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; padding: 22px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>📖</span> Last Lesson Taught
                    </h4>
                    <span style="font-size: 11px; color: #64748B;">Latest Record</span>
                </div>
                <?php if (!empty($lastLessonTaught)): ?>
                    <div style="background: #F8FAFC; border-radius: 10px; padding: 14px; border: 1px solid #E2E8F0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 12px; font-weight: 700; color: #2563EB;">
                                <?= htmlspecialchars($lastLessonTaught['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span style="font-size: 11px; color: #64748B;">
                                <?= date('d M Y', strtotime($lastLessonTaught['lecture_date'])) ?>
                            </span>
                        </div>
                        <h5 style="font-size: 14px; font-weight: 700; color: #0F172A; margin: 0 0 4px 0;">
                            <?= htmlspecialchars($lastLessonTaught['topic_title'], ENT_QUOTES, 'UTF-8') ?>
                        </h5>
                        <?php if (!empty($lastLessonTaught['description'])): ?>
                            <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.4;">
                                <?= htmlspecialchars(substr($lastLessonTaught['description'], 0, 100) . (strlen($lastLessonTaught['description']) > 100 ? '...' : ''), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 11px; font-weight: 600; color: #059669; background: #ECFDF5; padding: 2px 6px; border-radius: 4px;">
                                Status: <?= ucfirst(str_replace('_', ' ', $lastLessonTaught['status'])) ?>
                            </span>
                            <?php if (!empty($lastLessonTaught['time_table_id'])): ?>
                                <a href="index.php?page=lecture_details&id=<?= (int)$lastLessonTaught['time_table_id'] ?>&date=<?= $lastLessonTaught['lecture_date'] ?>" style="font-size: 11px; color: #2563EB; font-weight: 700; text-decoration: none;">
                                    View Session &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="font-size: 12px; color: #64748B; margin: 0;">No lesson record recorded yet. Open a lecture session to record today's topic.</p>
                <?php endif; ?>
            </div>

            <!-- Widget 2: Recent Homework Assigned -->
            <div id="homeworkSection" style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; padding: 22px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>📑</span> Recent Homework
                    </h4>
                    <span style="font-size: 12px; font-weight: 700; color: #7C3AED;"><?= count($recentHomework) ?> Listed</span>
                </div>
                <?php if (!empty($recentHomework)): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($recentHomework as $hw): ?>
                            <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 12px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-size: 13px; font-weight: 700; color: #0F172A;">
                                        <?= htmlspecialchars($hw['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span style="font-size: 11px; font-weight: 600; color: <?= ($hw['due_date'] < date('Y-m-d')) ? '#EF4444' : '#059669' ?>;">
                                        Due: <?= date('d M', strtotime($hw['due_date'])) ?>
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: #6B21A8; display: flex; justify-content: space-between; align-items: center;">
                                    <span><?= htmlspecialchars($hw['subject_name'] ?? 'Subject', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($hw['section_name'] ?? 'Class', ENT_QUOTES, 'UTF-8') ?>)</span>
                                    <?php if (!empty($hw['attachment_path'])): ?>
                                        <a href="<?= htmlspecialchars($hw['attachment_path']) ?>" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: none;">📎 Attachment</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 12px; color: #64748B; margin: 0;">No active homework published. You can assign homework directly from any lecture details page.</p>
                <?php endif; ?>
            </div>

            <!-- Widget 3: Assigned Subjects -->
            <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; padding: 22px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🎓</span> Assigned Teaching Subjects
                    </h4>
                    <span style="font-size: 11px; color: #64748B;"><?= count($assignedSubjects) ?> Subjects</span>
                </div>
                <?php if (!empty($assignedSubjects)): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($assignedSubjects as $sub): ?>
                            <span style="background: #F1F5F9; color: #1E293B; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                                <?= htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') ?> 
                                <?php if (!empty($sub['code'])): ?>
                                    <span style="color: #64748B; font-size: 10px;">(<?= htmlspecialchars($sub['code']) ?>)</span>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 12px; color: #64748B; margin: 0;">No specific teaching assignments found. Contact the administration to map subjects in the timetable.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Department & Assigned Students Roster Section -->
    <div id="studentRosterSection" style="margin-top: 28px; background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <span style="font-size: 24px;">👥</span>
                    <h3 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0;">
                        Department & Assigned Students Roster
                    </h3>
                    <span id="studentRosterCountBadge" style="background: #EFF6FF; color: #2563EB; font-size: 12px; font-weight: 800; padding: 3px 10px; border-radius: 12px; border: 1px solid #BFDBFE;">
                        0 Students
                    </span>
                </div>
                <p style="font-size: 13px; color: #64748B; margin: 0;">
                    Real-time roster of authorized students enrolled in <strong><?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?></strong> and your assigned lecture classes.
                </p>
            </div>

            <!-- Academic Year Filter Buttons -->
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" onclick="setStudentYearFilter('ALL', this)" class="year-filter-btn" style="background: #0F172A; color: #FFFFFF; border: none; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    All Cohorts
                </button>
                <button type="button" onclick="setStudentYearFilter('FY', this)" class="year-filter-btn" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    🥇 FY (1st Year)
                </button>
                <button type="button" onclick="setStudentYearFilter('SY', this)" class="year-filter-btn" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    🥈 SY (2nd Year)
                </button>
                <button type="button" onclick="setStudentYearFilter('TY', this)" class="year-filter-btn" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    🥉 TY (3rd Year)
                </button>
            </div>
        </div>

        <!-- Filter Controls Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; background: #F8FAFC; padding: 12px 16px; border-radius: 10px; border: 1px solid #E2E8F0;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 420px;">
                <span style="font-size: 16px; color: #94A3B8;">🔍</span>
                <input type="text" id="studentSearchInput" placeholder="Search by Student Name, Roll No, Admission ID..." 
                       style="width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; padding: 8px 12px; font-size: 13px; outline: none;" 
                       oninput="handleStudentSearch(this.value)">
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label for="studentSortSelect" style="font-size: 12px; font-weight: 700; color: #64748B;">Sort:</label>
                    <select id="studentSortSelect" onchange="handleStudentSort(this.value)" 
                            style="border: 1px solid #CBD5E1; border-radius: 6px; padding: 6px 10px; font-size: 12px; background: #FFF; font-weight: 600; cursor: pointer;">
                        <option value="name">Name (A &rarr; Z)</option>
                        <option value="roll_no">Roll No (Ascending)</option>
                        <option value="admission_no">Admission No</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <label for="studentLimitSelect" style="font-size: 12px; font-weight: 700; color: #64748B;">Show:</label>
                    <select id="studentLimitSelect" onchange="handleStudentLimit(this.value)" 
                            style="border: 1px solid #CBD5E1; border-radius: 6px; padding: 6px 8px; font-size: 12px; background: #FFF; font-weight: 600; cursor: pointer;">
                        <option value="15">15 per page</option>
                        <option value="30">30 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State Indicator -->
        <div id="studentLoadingSpinner" style="display: none; text-align: center; padding: 36px 20px;">
            <div style="display: inline-block; width: 36px; height: 36px; border: 3px solid #E2E8F0; border-top-color: #3B82F6; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <p style="font-size: 13px; color: #64748B; margin-top: 10px; font-weight: 600;">Fetching authorized student records...</p>
        </div>

        <!-- Error State Alert -->
        <div id="studentErrorAlert" style="display: none; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 10px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">⚠️</span>
                    <span id="studentErrorMessage" style="font-size: 13px; font-weight: 600;">Unable to fetch student records.</span>
                </div>
                <button type="button" onclick="loadTeacherStudents(currentStudentPage)" style="background: #991B1B; color: #FFF; border: none; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    Retry
                </button>
            </div>
        </div>

        <!-- Empty State Container -->
        <div id="studentEmptyState" style="display: none; text-align: center; padding: 40px 20px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 12px;">
            <span style="font-size: 40px; display: block; margin-bottom: 10px;">📭</span>
            <h4 style="font-size: 16px; font-weight: 800; color: #1E293B; margin-bottom: 4px;">No Students Found</h4>
            <p id="studentEmptyMessage" style="font-size: 13px; color: #64748B; max-width: 440px; margin: 0 auto;">
                No student records match your current search or year filter. Try resetting the filters or typing a different keyword.
            </p>
        </div>

        <!-- Student Roster Table -->
        <div id="studentTableContainer" style="overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 10px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 14px;">Photo</th>
                        <th style="padding: 12px 14px;">Student Name</th>
                        <th style="padding: 12px 14px;">Admission ID</th>
                        <th style="padding: 12px 14px;">Roll No</th>
                        <th style="padding: 12px 14px;">Department</th>
                        <th style="padding: 12px 14px;">Course / Program</th>
                        <th style="padding: 12px 14px;">Academic Year</th>
                        <th style="padding: 12px 14px;">Division</th>
                        <th style="padding: 12px 14px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <!-- Populated dynamically via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary Bar -->
        <div id="studentPaginationContainer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #E2E8F0;">
            <div id="studentPaginationSummary" style="font-size: 12px; color: #64748B; font-weight: 600;">
                Showing 0 to 0 of 0 students
            </div>
            <div id="studentPaginationButtons" style="display: flex; gap: 6px; align-items: center;">
                <!-- Populated dynamically via JavaScript -->
            </div>
        </div>
    </div>

    <!-- ERP Modular Launchpad Section -->
    <div style="margin-top: 28px;">
        <h3 style="font-size: 16px; font-weight: 800; color: #334155; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
            ERP Academic Modules & Utilities
        </h3>
        <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
    </div>
</main>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.student-row:hover {
    background: #F8FAFC;
}
</style>

<script>
let currentStudentPage = 1;
let currentStudentYear = 'ALL';
let currentStudentSearch = '';
let currentStudentSort = 'name';
let currentStudentLimit = 15;
let studentSearchDebounceTimer = null;

function setStudentYearFilter(year, btn) {
    currentStudentYear = year;
    currentStudentPage = 1;
    document.querySelectorAll('.year-filter-btn').forEach(b => {
        b.style.background = '#F1F5F9';
        b.style.color = '#475569';
        b.style.border = '1px solid #CBD5E1';
    });
    btn.style.background = '#0F172A';
    btn.style.color = '#FFFFFF';
    btn.style.border = 'none';
    loadTeacherStudents(1);
}

function handleStudentSearch(val) {
    clearTimeout(studentSearchDebounceTimer);
    studentSearchDebounceTimer = setTimeout(() => {
        currentStudentSearch = val.trim();
        currentStudentPage = 1;
        loadTeacherStudents(1);
    }, 280);
}

function handleStudentSort(val) {
    currentStudentSort = val;
    loadTeacherStudents(1);
}

function handleStudentLimit(val) {
    currentStudentLimit = parseInt(val, 10) || 15;
    loadTeacherStudents(1);
}

function loadTeacherStudents(page = 1) {
    currentStudentPage = page;
    const spinner = document.getElementById('studentLoadingSpinner');
    const tableContainer = document.getElementById('studentTableContainer');
    const tableBody = document.getElementById('studentTableBody');
    const emptyState = document.getElementById('studentEmptyState');
    const errorAlert = document.getElementById('studentErrorAlert');
    const paginationContainer = document.getElementById('studentPaginationContainer');

    spinner.style.display = 'block';
    tableContainer.style.opacity = '0.4';
    errorAlert.style.display = 'none';
    emptyState.style.display = 'none';

    const params = new URLSearchParams({
        page: 'api_module',
        module: 'students',
        page_num: currentStudentPage,
        limit: currentStudentLimit,
        sort_by: currentStudentSort,
        order: 'ASC'
    });

    if (currentStudentYear && currentStudentYear !== 'ALL') {
        params.append('academic_year', currentStudentYear);
    }
    if (currentStudentSearch) {
        params.append('search', currentStudentSearch);
    }

    fetch('index.php?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status + ' response');
        return res.json();
    })
    .then(res => {
        spinner.style.display = 'none';
        tableContainer.style.opacity = '1';

        const data = res.data || [];
        const total = res.total || 0;
        const totalPages = res.total_pages || 1;

        document.getElementById('studentRosterCountBadge').textContent = total + ' Students';

        if (data.length === 0) {
            tableContainer.style.display = 'none';
            paginationContainer.style.display = 'none';
            emptyState.style.display = 'block';
            document.getElementById('studentEmptyMessage').textContent = currentStudentSearch 
                ? `No students matching "${currentStudentSearch}" in ${currentStudentYear === 'ALL' ? 'any year' : currentStudentYear}.`
                : `No student records found in your authorized cohort.`;
            return;
        }

        emptyState.style.display = 'none';
        tableContainer.style.display = 'block';
        paginationContainer.style.display = 'flex';

        tableBody.innerHTML = '';
        data.forEach(st => {
            const tr = document.createElement('tr');
            tr.className = 'student-row';
            tr.style.borderBottom = '1px solid #E2E8F0';

            const initials = ((st.first_name ? st.first_name[0] : 'S') + (st.last_name ? st.last_name[0] : '')).toUpperCase();
            const avatarHtml = st.photo 
                ? `<img src="${escapeHtml(st.photo)}" alt="Photo" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid #CBD5E1;">`
                : `<div style="width: 36px; height: 36px; border-radius: 50%; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 1px solid #C7D2FE;">${initials}</div>`;

            const yearCode = (st.year_code || 'FY').toUpperCase();
            let yearBadge = `<span style="background: #DCFCE7; color: #166534; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px;">${escapeHtml(st.student_year || 'FY')}</span>`;
            if (yearCode === 'SY') {
                yearBadge = `<span style="background: #DBEAFE; color: #1E40AF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px;">${escapeHtml(st.student_year || 'SY')}</span>`;
            } else if (yearCode === 'TY') {
                yearBadge = `<span style="background: #F3E8FF; color: #6B21A8; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px;">${escapeHtml(st.student_year || 'TY')}</span>`;
            }

            tr.innerHTML = `
                <td style="padding: 10px 14px; vertical-align: middle;">${avatarHtml}</td>
                <td style="padding: 10px 14px; vertical-align: middle;">
                    <div style="font-weight: 700; color: #0F172A;">${escapeHtml(st.full_name || (st.first_name + ' ' + (st.last_name || '')))}</div>
                    ${st.email ? `<div style="font-size: 11px; color: #64748B;">${escapeHtml(st.email)}</div>` : ''}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; font-family: monospace; font-weight: 600; color: #334155;">
                    ${escapeHtml(st.admission_no || 'N/A')}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; font-weight: 700; color: #2563EB;">
                    ${escapeHtml(st.roll_no || '—')}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; color: #334155;">
                    ${escapeHtml(st.department_name || 'General')}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; color: #334155;">
                    ${escapeHtml(st.course_name || 'General')}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle;">
                    ${yearBadge}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; color: #475569; font-weight: 600;">
                    ${escapeHtml(st.class_name || 'Div A')}
                </td>
                <td style="padding: 10px 14px; vertical-align: middle; text-align: center;">
                    <span style="background: #ECFDF5; color: #059669; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                        ${escapeHtml(st.status ? st.status.toUpperCase() : 'ACTIVE')}
                    </span>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // Update Pagination controls
        const start = total === 0 ? 0 : (currentStudentPage - 1) * currentStudentLimit + 1;
        const end = Math.min(currentStudentPage * currentStudentLimit, total);
        document.getElementById('studentPaginationSummary').textContent = `Showing ${start} to ${end} of ${total} students`;

        const btnContainer = document.getElementById('studentPaginationButtons');
        btnContainer.innerHTML = '';

        if (totalPages > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '« Prev';
            prevBtn.disabled = currentStudentPage <= 1;
            prevBtn.style.cssText = `padding: 4px 10px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 12px; font-weight: 600; background: ${currentStudentPage <= 1 ? '#F1F5F9' : '#FFF'}; cursor: ${currentStudentPage <= 1 ? 'not-allowed' : 'pointer'};`;
            prevBtn.onclick = () => loadTeacherStudents(currentStudentPage - 1);
            btnContainer.appendChild(prevBtn);

            for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                const isCurr = (i === currentStudentPage);
                pageBtn.style.cssText = `padding: 4px 10px; border: 1px solid ${isCurr ? '#2563EB' : '#CBD5E1'}; border-radius: 6px; font-size: 12px; font-weight: 700; background: ${isCurr ? '#2563EB' : '#FFF'}; color: ${isCurr ? '#FFF' : '#334155'}; cursor: pointer;`;
                pageBtn.onclick = () => loadTeacherStudents(i);
                btnContainer.appendChild(pageBtn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next »';
            nextBtn.disabled = currentStudentPage >= totalPages;
            nextBtn.style.cssText = `padding: 4px 10px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 12px; font-weight: 600; background: ${currentStudentPage >= totalPages ? '#F1F5F9' : '#FFF'}; cursor: ${currentStudentPage >= totalPages ? 'not-allowed' : 'pointer'};`;
            nextBtn.onclick = () => loadTeacherStudents(currentStudentPage + 1);
            btnContainer.appendChild(nextBtn);
        }
    })
    .catch(err => {
        spinner.style.display = 'none';
        tableContainer.style.opacity = '1';
        tableContainer.style.display = 'none';
        paginationContainer.style.display = 'none';
        errorAlert.style.display = 'block';
        document.getElementById('studentErrorMessage').textContent = 'Unable to load student records: ' + err.message;
    });
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

document.addEventListener('DOMContentLoaded', () => {
    loadTeacherStudents(1);
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
