<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<?php
$isStudent = ($user['role_name'] === 'student');
?>

<div class="main-content" style="padding: 24px;">
    <!-- Flash Messages -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div
            style="background: #D1FAE5; color: #065F46; padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; border-left: 4px solid #10B981; display: flex; align-items: center; justify-content: space-between;">
            <span>✅ <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.parentElement.remove()"
                style="background:none; border:none; color:#065F46; font-size:16px; cursor:pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::getFlash('warning')): ?>
        <div
            style="background: #FEF3C7; color: #92400E; padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; border-left: 4px solid #F59E0B; display: flex; align-items: center; justify-content: space-between;">
            <span>⚠️ <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.parentElement.remove()"
                style="background:none; border:none; color:#92400E; font-size:16px; cursor:pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
        <div
            style="background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; border-left: 4px solid #EF4444; display: flex; align-items: center; justify-content: space-between;">
            <span>❌ <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.parentElement.remove()"
                style="background:none; border:none; color:#991B1B; font-size:16px; cursor:pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Module Header -->
    <div
        style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%); color: #FFFFFF; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 32px;">Q</span>
                    <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Online Exam & MCQ Portal</h1>
                </div>
                <p style="margin: 6px 0 0 0; color: #C7D2FE; font-size: 14px;">
                    <?php if ($isStudent): ?>
                        View and give time-limited MCQ exams created by your faculty. Anti-cheating protection active during
                        test taking.
                    <?php else: ?>
                        Create time-limited MCQ exams, manage questions, and monitor student anti-cheating auto-submissions.
                    <?php endif; ?>
                </p>
            </div>
            <?php if (!$isStudent): ?>
                <div>
                    <button type="button" onclick="document.getElementById('createExamModal').style.display='flex'"
                        style="background: #6366F1; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                        + Create New MCQ Exam
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div
            style="background: #FFFFFF; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase;">
                <?= $isStudent ? 'Available Exams' : (!empty($viewArchived) ? 'Archived Online Exams' : 'Total Active Exams') ?></div>
            <div id="totalExamsCounter" style="font-size: 28px; font-weight: 800; color: #1E1B4B; margin-top: 4px;"><?= count($exams) ?></div>
        </div>
        <div
            style="background: #FFFFFF; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase;">Anti-Cheating
                Security</div>
            <div
                style="font-size: 15px; font-weight: 700; color: #059669; margin-top: 8px; display: flex; align-items: center; gap: 6px;">
                <span>🛡️ Tab-Switch Auto-Submit</span>
            </div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">+ Back Button Auto-Submit Enabled</div>
        </div>
        <div
            style="background: #FFFFFF; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase;">
                <?= $isStudent ? 'My Completed Exams' : 'Student Submissions Logged' ?></div>
            <div style="font-size: 28px; font-weight: 800; color: #059669; margin-top: 4px;"><?= count($submissions) ?>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div style="display: grid; grid-template-columns: <?= $isStudent ? '1fr' : '1fr 1fr' ?>; gap: 24px;">

        <!-- Left Column: Exams Directory -->
        <div>
            <div
                style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); hei                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">📚
                            <?= $isStudent ? 'Assigned MCQ Exams' : (!empty($viewArchived) ? 'Archived Exams Directory' : 'Online Exam Directory') ?></h3>
                        <?php if (!$isStudent): ?>
                            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Filter by Academic Year:</div>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isStudent && (($archivedCount ?? 0) > 0 || !empty($viewArchived))): ?>
                        <a href="index.php?page=module&name=online_exam<?= !empty($viewArchived) ? '' : '&view_archived=1' ?>"
                            style="font-size: 12px; font-weight: 700; color: <?= !empty($viewArchived) ? '#4F46E5' : '#64748B' ?>; text-decoration: none; padding: 4px 10px; border-radius: 6px; background: #F1F5F9; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 4px;">
                            <?= !empty($viewArchived) ? '← View Active Exams' : '📦 View Archived (' . (int)($archivedCount ?? 0) . ')' ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!$isStudent): ?>
                    <!-- Academic Year Filter Tabs (FY / SY / TY / All) -->
                    <div style="display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;">
                        <a href="index.php?page=module&name=online_exam<?= !empty($viewArchived) ? '&view_archived=1' : '' ?>&academic_year=ALL"
                           style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid #CBD5E1; background: <?= ($selectedYearFilter === 'ALL' || empty($selectedYearFilter)) ? '#0F172A' : '#FFFFFF' ?>; color: <?= ($selectedYearFilter === 'ALL' || empty($selectedYearFilter)) ? '#FFFFFF' : '#475569' ?>;">
                            All Years
                        </a>
                        <a href="index.php?page=module&name=online_exam<?= !empty($viewArchived) ? '&view_archived=1' : '' ?>&academic_year=FY"
                           style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid #93C5FD; background: <?= ($selectedYearFilter === 'FY') ? '#2563EB' : '#EFF6FF' ?>; color: <?= ($selectedYearFilter === 'FY') ? '#FFFFFF' : '#1D4ED8' ?>;">
                            🎓 FY — First Year
                        </a>
                        <a href="index.php?page=module&name=online_exam<?= !empty($viewArchived) ? '&view_archived=1' : '' ?>&academic_year=SY"
                           style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid #FCD34D; background: <?= ($selectedYearFilter === 'SY') ? '#D97706' : '#FFFBEB' ?>; color: <?= ($selectedYearFilter === 'SY') ? '#FFFFFF' : '#B45309' ?>;">
                            🎓 SY — Second Year
                        </a>
                        <a href="index.php?page=module&name=online_exam<?= !empty($viewArchived) ? '&view_archived=1' : '' ?>&academic_year=TY"
                           style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid #D8B4FE; background: <?= ($selectedYearFilter === 'TY') ? '#7E22CE' : '#FAF5FF' ?>; color: <?= ($selectedYearFilter === 'TY') ? '#FFFFFF' : '#6B21A8' ?>;">
                            🎓 TY — Third Year
                        </a>
                    </div>
                <?php endif; ?>

                <div id="examCardsContainer" style="display: flex; flex-direction: column; gap: 14px;">
                    <?php if (empty($exams)): ?>
                        <p id="emptyExamsMessage" style="text-align: center; color: #64748B; padding: 30px;">
                            <?= !empty($viewArchived) ? 'No archived exams found in repository.' : 'No online exams available matching the selected criteria.' ?>
                        </p>
                    <?php else: ?>
                        <?php foreach ($exams as $ex): ?>
                            <?php
                            $isSelected = ($selectedExam && $selectedExam['id'] == $ex['id']);
                            $studentSub = $studentSubmissionsMap[$ex['id']] ?? null;
                            $subCount = (int)($ex['submission_count'] ?? 0);
                            $exYear = strtoupper($ex['academic_year'] ?? 'FY');
                            if (!in_array($exYear, ['FY', 'SY', 'TY'], true)) {
                                $exYear = ($ex['standard_id'] == 2) ? 'SY' : (($ex['standard_id'] == 3) ? 'TY' : 'FY');
                            }
                            ?>
                            <div id="examCard_<?= $ex['id'] ?>" class="exam-item-card"
                                style="border: 2px solid <?= $isSelected ? '#6366F1' : '#E2E8F0' ?>; background: <?= $isSelected ? '#EEF2FF' : '#F8FAFC' ?>; border-radius: 12px; padding: 16px; transition: all 0.2s;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                                            <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #1E1B4B;">
                                                <?= htmlspecialchars($ex['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <!-- Academic Year Badge (FY / SY / TY) -->
                                            <?php if ($exYear === 'FY'): ?>
                                                <span style="background: #DBEAFE; color: #1E40AF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px; border: 1px solid #BFDBFE;">
                                                    🎓 FY (First Year)
                                                </span>
                                            <?php elseif ($exYear === 'SY'): ?>
                                                <span style="background: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px; border: 1px solid #FDE68A;">
                                                    🎓 SY (Second Year)
                                                </span>
                                            <?php elseif ($exYear === 'TY'): ?>
                                                <span style="background: #F3E8FF; color: #6B21A8; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px; border: 1px solid #E9D5FF;">
                                                    🎓 TY (Third Year)
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div
                                            style="font-size: 12px; color: #475569; display: flex; flex-wrap: wrap; gap: 14px; margin-top: 6px;">
                                            <?php if (!empty($ex['department_name'])): ?>
                                                <span>🏢 Dept: <strong><?= htmlspecialchars($ex['department_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                            <?php endif; ?>
                                            <span>📖 Course:
                                                <strong><?= htmlspecialchars($ex['course_name'] ?? 'All Courses', ENT_QUOTES, 'UTF-8') ?></strong></span>
                                            <?php if (!empty($ex['subject_name'])): ?>
                                                <span>📚 Subject: <strong><?= htmlspecialchars($ex['subject_name'] . (!empty($ex['subject_code']) ? ' (' . $ex['subject_code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?></strong></span>
                                            <?php endif; ?>
                                            <span>👤 Target:
                                                <strong>
                                                    <?php if (!empty($ex['student_id'])): ?>
                                                        <?= (!$isStudent) ? htmlspecialchars($ex['student_fname'] . ' ' . $ex['student_lname'] . ' (' . $ex['student_roll'] . ')', ENT_QUOTES, 'UTF-8') : 'Assigned Specifically to You' ?>
                                                    <?php else: ?>
                                                        All Eligible <?= $exYear ?> Students
                                                    <?php endif; ?>
                                                </strong>
                                            </span>
                                            <span>⏱️ Duration: <strong><?= (int) $ex['duration_minutes'] ?> Minutes</strong></span>
                                            <span>❓ Questions: <strong><?= (int) $ex['question_count'] ?> MCQ</strong></span>
                                            <?php if (!$isStudent): ?>
                                                <span>📊 Student Submissions: <strong><?= $subCount ?></strong></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span
                                        style="background: <?= $ex['status'] === 'published' ? '#D1FAE5' : ($ex['status'] === 'archived' ? '#FEF3C7' : '#F3F4F6') ?>; color: <?= $ex['status'] === 'published' ? '#065F46' : ($ex['status'] === 'archived' ? '#92400E' : '#374151') ?>; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                                        <?= htmlspecialchars($ex['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>

                                <div style="margin-top: 14px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                    <?php if ($isStudent): ?>
                                        <?php if ($studentSub): ?>
                                            <span
                                                style="background: #E0E7FF; color: #3730A3; font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 6px;">
                                                ✅ Completed (Score: <?= $studentSub['score'] ?> / <?= $studentSub['total_score'] ?>)
                                            </span>
                                            <a href="index.php?page=take_online_exam&id=<?= $ex['id'] ?>"
                                                style="background: #4F46E5; color: #FFFFFF; text-decoration: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                                📊 View My Score & Results
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?page=take_online_exam&id=<?= $ex['id'] ?>"
                                                style="background: #059669; color: #FFFFFF; text-decoration: none; padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(5,150,105,0.2);">
                                                🚀 Start / Give MCQ Exam
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Teacher / Principal Management Controls -->
                                        <a href="index.php?page=module&name=online_exam&selected_exam_id=<?= $ex['id'] ?>#mcqBuilderSection"
                                            onclick="openManageMcqModal(<?= $ex['id'] ?>); return false;"
                                            style="background: #4F46E5; color: #FFFFFF; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                            ✏️ Manage MCQ Questions (<?= (int) $ex['question_count'] ?>)
                                        </a>

                                        <!-- Edit Exam Configuration Button -->
                                        <button type="button" class="btn-edit-exam"
                                            data-id="<?= (int)$ex['id'] ?>"
                                            data-title="<?= htmlspecialchars($ex['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-dept="<?= (int)($ex['department_id'] ?? 0) ?>"
                                            data-course="<?= (int)($ex['course_id'] ?? 0) ?>"
                                            data-year="<?= htmlspecialchars($exYear, ENT_QUOTES, 'UTF-8') ?>"
                                            data-subject="<?= (int)($ex['subject_id'] ?? 0) ?>"
                                            data-student="<?= (int)($ex['student_id'] ?? 0) ?>"
                                            data-duration="<?= (int)$ex['duration_minutes'] ?>"
                                            data-status="<?= htmlspecialchars($ex['status'] ?? 'published', ENT_QUOTES, 'UTF-8') ?>"
                                            onclick="openEditExamModal(this)"
                                            style="background: #FFFFFF; color: #334155; border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            ⚙️ Edit Exam
                                        </button>

                                        <a href="index.php?page=take_online_exam&id=<?= $ex['id'] ?>" target="_blank"
                                            style="background: #059669; color: #FFFFFF; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                            🚀 Take / Preview Exam
                                        </a>

                                        <!-- Functional Delete Exam Button with Data Attributes (Special Characters & Single Quotes Safe) -->
                                        <button type="button" class="btn-delete-exam"
                                            data-id="<?= (int)$ex['id'] ?>"
                                            data-title="<?= htmlspecialchars($ex['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-course="<?= htmlspecialchars($ex['course_name'] ?? 'All Courses', ENT_QUOTES, 'UTF-8') ?>"
                                            data-year="<?= htmlspecialchars($exYear, ENT_QUOTES, 'UTF-8') ?>"
                                            data-duration="<?= (int)$ex['duration_minutes'] ?>"
                                            data-questions="<?= (int)$ex['question_count'] ?>"
                                            data-submissions="<?= $subCount ?>"
                                            onclick="handleDeleteExamClick(this)"
                                            style="background: #EF4444; color: #FFFFFF; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background 0.15s;"
                                            onmouseover="this.style.background='#DC2626'" onmouseout="this.style.background='#EF4444'">
                                            🗑️ Delete Exam
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: MCQ Question Builder (Teacher/Admin ONLY) -->
        <?php if (!$isStudent): ?>
            <div>
                <?php if ($selectedExam): ?>
                    <div id="mcqBuilderSection"
                        style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 2px solid #EEF2FF; padding-bottom: 10px;">
                            <div>
                                <span
                                    style="font-size: 11px; font-weight: 700; color: #6366F1; text-transform: uppercase;">Selected
                                    Exam MCQ Builder</span>
                                <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 2px 0 0 0;">
                                    <?= htmlspecialchars($selectedExam['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            </div>
                            <span
                                style="background: #EEF2FF; color: #4338CA; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 20px;">
                                ⏱️ <?= (int) $selectedExam['duration_minutes'] ?> Mins Limit
                            </span>
                        </div>

                        <!-- Form: Add MCQ Questions -->
                        <form action="index.php?page=add_mcq_question" method="POST" id="batchMcqForm"
                            style="margin-bottom: 20px;">
                            <?= \App\Core\CSRF::inputField() ?>
                            <input type="hidden" name="exam_id" value="<?= $selectedExam['id'] ?>">

                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1E293B;">+ Add MCQ Questions
                                </h4>
                                <button type="button" onclick="addQuestionFieldBlock()"
                                    style="background: #EEF2FF; color: #4338CA; border: 1px dashed #6366F1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                                    ➕ Add Another MCQ Question
                                </button>
                            </div>

                            <div id="dynamicQuestionsContainer"
                                style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px;">
                                <!-- Question Block 0 -->
                                <div class="q-block"
                                    style="background: #F8FAFC; padding: 14px; border-radius: 10px; border: 1px solid #E2E8F0; position: relative;">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <span class="q-label"
                                            style="font-size: 12px; font-weight: 800; color: #4338CA;">Question #1</span>
                                        <button type="button" onclick="removeQuestionFieldBlock(this)" class="remove-btn"
                                            style="display: none; background: none; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer;">✖
                                            Remove</button>
                                    </div>

                                    <div style="margin-bottom: 8px;">
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Question
                                            Text *</label>
                                        <textarea name="questions[0][question_text]" rows="2"
                                            placeholder="e.g. Which layer of OSI model handles IP routing?" required
                                            style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option
                                                A *</label>
                                            <input type="text" name="questions[0][option_a]" placeholder="Option A text"
                                                required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option
                                                B *</label>
                                            <input type="text" name="questions[0][option_b]" placeholder="Option B text"
                                                required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option
                                                C *</label>
                                            <input type="text" name="questions[0][option_c]" placeholder="Option C text"
                                                required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option
                                                D *</label>
                                            <input type="text" name="questions[0][option_d]" placeholder="Option D text"
                                                required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #059669; margin-bottom: 2px;">Correct
                                                Option *</label>
                                            <select name="questions[0][correct_option]" required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #10B981; font-size: 12px; font-weight: 700; background: #ECFDF5; color: #065F46;">
                                                <option value="A">Option A</option>
                                                <option value="B">Option B</option>
                                                <option value="C">Option C</option>
                                                <option value="D">Option D</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Marks
                                                *</label>
                                            <input type="number" name="questions[0][marks]" value="1" min="1" required
                                                style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                style="width: 100%; background: #4F46E5; color: #FFFFFF; border: none; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                                🚀 Save MCQ Questions to Exam
                            </button>
                        </form>

                        <script>
                            var questionCounter = 1;

                            function addQuestionFieldBlock() {
                                var container = document.getElementById('dynamicQuestionsContainer');
                                var qIdx = questionCounter++;
                                var qNum = container.children.length + 1;

                                var html = `
                                <div class="q-block" style="background: #F8FAFC; padding: 14px; border-radius: 10px; border: 1px solid #E2E8F0; position: relative;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <span class="q-label" style="font-size: 12px; font-weight: 800; color: #4338CA;">Question #${qNum}</span>
                                        <button type="button" onclick="removeQuestionFieldBlock(this)" class="remove-btn" style="background: none; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer;">✖ Remove</button>
                                    </div>

                                    <div style="margin-bottom: 8px;">
                                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Question Text *</label>
                                        <textarea name="questions[\${qIdx}][question_text]" rows="2" placeholder="e.g. Enter question text..." required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option A *</label>
                                            <input type="text" name="questions[\${qIdx}][option_a]" placeholder="Option A text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option B *</label>
                                            <input type="text" name="questions[\${qIdx}][option_b]" placeholder="Option B text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option C *</label>
                                            <input type="text" name="questions[\${qIdx}][option_c]" placeholder="Option C text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option D *</label>
                                            <input type="text" name="questions[\${qIdx}][option_d]" placeholder="Option D text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #059669; margin-bottom: 2px;">Correct Option *</label>
                                            <select name="questions[\${qIdx}][correct_option]" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #10B981; font-size: 12px; font-weight: 700; background: #ECFDF5; color: #065F46;">
                                                <option value="A">Option A</option>
                                                <option value="B">Option B</option>
                                                <option value="C">Option C</option>
                                                <option value="D">Option D</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Marks *</label>
                                            <input type="number" name="questions[\${qIdx}][marks]" value="1" min="1" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                        </div>
                                    </div>
                                </div>`;

                                var div = document.createElement('div');
                                div.innerHTML = html;
                                container.appendChild(div.firstElementChild);
                                updateQuestionLabels();
                            }

                            function removeQuestionFieldBlock(btn) {
                                var block = btn.closest('.q-block');
                                block.remove();
                                updateQuestionLabels();
                            }

                            function updateQuestionLabels() {
                                var container = document.getElementById('dynamicQuestionsContainer');
                                var blocks = container.querySelectorAll('.q-block');
                                blocks.forEach(function (block, idx) {
                                    var label = block.querySelector('.q-label');
                                    if (label) label.textContent = 'Question #' + (idx + 1);
                                    var rmBtn = block.querySelector('.remove-btn');
                                    if (rmBtn) {
                                        rmBtn.style.display = (blocks.length > 1) ? 'inline-block' : 'none';
                                    }
                                });
                            }
                        </script>

                        <!-- Questions List -->
                        <h4 style="font-size: 14px; font-weight: 700; color: #0F172A; margin: 0 0 10px 0;">Existing MCQ
                            Questions (<?= count($questions) ?>)</h4>
                        <?php if (empty($questions)): ?>
                            <p style="font-size: 13px; color: #64748B; font-style: italic;">No MCQ questions added yet. Use the form
                                above to add questions.</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto;">
                                <?php foreach ($questions as $idx => $q): ?>
                                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <div style="font-size: 13px; font-weight: 700; color: #1E293B;">
                                                Q<?= $idx + 1 ?>: <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?>
                                                <span
                                                    style="font-size: 11px; color: #6366F1; margin-left: 6px;">(<?= (int) $q['marks'] ?>
                                                    Mark)</span>
                                            </div>
                                            <form action="index.php?page=delete_mcq_question" method="POST"
                                                onsubmit="return confirm('Delete this question?');">
                                                <?= \App\Core\CSRF::inputField() ?>
                                                <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                                <input type="hidden" name="exam_id" value="<?= $selectedExam['id'] ?>">
                                                <button type="submit"
                                                    style="background: none; border: none; color: #EF4444; cursor: pointer; font-size: 12px; font-weight: 700;">🗑️
                                                    Delete</button>
                                            </form>
                                        </div>
                                        <div
                                            style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 8px; font-size: 11px;">
                                            <div
                                                style="padding: 4px 8px; border-radius: 4px; background: <?= $q['correct_option'] === 'A' ? '#D1FAE5' : '#F1F5F9' ?>; color: <?= $q['correct_option'] === 'A' ? '#065F46' : '#334155' ?>;">
                                                A: <?= htmlspecialchars($q['option_a'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div
                                                style="padding: 4px 8px; border-radius: 4px; background: <?= $q['correct_option'] === 'B' ? '#D1FAE5' : '#F1F5F9' ?>; color: <?= $q['correct_option'] === 'B' ? '#065F46' : '#334155' ?>;">
                                                B: <?= htmlspecialchars($q['option_b'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div
                                                style="padding: 4px 8px; border-radius: 4px; background: <?= $q['correct_option'] === 'C' ? '#D1FAE5' : '#F1F5F9' ?>; color: <?= $q['correct_option'] === 'C' ? '#065F46' : '#334155' ?>;">
                                                C: <?= htmlspecialchars($q['option_c'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div
                                                style="padding: 4px 8px; border-radius: 4px; background: <?= $q['correct_option'] === 'D' ? '#D1FAE5' : '#F1F5F9' ?>; color: <?= $q['correct_option'] === 'D' ? '#065F46' : '#334155' ?>;">
                                                D: <?= htmlspecialchars($q['option_d'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Student Submissions & Anti-Cheating Violation Logs -->
    <div
        style="margin-top: 30px; background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">📊
            <?= $isStudent ? 'My Exam Submission History & Scores' : 'Student Submissions & Anti-Cheating Violation Report' ?>
        </h3>

        <?php if (empty($submissions)): ?>
            <p style="color: #64748B; font-size: 13px;">No exam submissions recorded yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; color: #475569;">
                            <th style="padding: 10px;">ID</th>
                            <?php if (!$isStudent): ?>
                                <th style="padding: 10px;">Student Name</th>
                            <?php endif; ?>
                            <th style="padding: 10px;">Exam Title</th>
                            <th style="padding: 10px;">Score</th>
                            <th style="padding: 10px;">Percentage</th>
                            <th style="padding: 10px;">Submission Status & Anti-Cheating Flags</th>
                            <th style="padding: 10px;">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <?php
                            $pct = ($sub['total_score'] > 0) ? round(($sub['score'] / $sub['total_score']) * 100, 1) : 0;
                            ?>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 10px; font-weight: 700; color: #64748B;">#<?= $sub['id'] ?></td>
                                <?php if (!$isStudent): ?>
                                    <td style="padding: 10px; font-weight: 700; color: #0F172A;">
                                        <?= htmlspecialchars(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? '') . ' (' . ($sub['roll_no'] ?? '') . ')', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                <?php endif; ?>
                                <td style="padding: 10px; color: #334155;">
                                    <?= htmlspecialchars($sub['exam_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px; font-weight: 700; color: #4338CA;"><?= $sub['score'] ?> /
                                    <?= $sub['total_score'] ?></td>
                                <td style="padding: 10px; font-weight: 700; color: <?= $pct >= 50 ? '#059669' : '#DC2626' ?>;">
                                    <?= $pct ?>%</td>
                                <td style="padding: 10px;">
                                    <?php if ($sub['submission_status'] === 'auto_submitted_tab_switch'): ?>
                                        <span
                                            style="background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                            ⚠️ Auto-Submitted (Tab Switch Detected)
                                        </span>
                                    <?php elseif ($sub['submission_status'] === 'auto_submitted_back_button'): ?>
                                        <span
                                            style="background: #FEF3C7; color: #92400E; font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                            ⚠️ Auto-Submitted (Back Button Pressed)
                                        </span>
                                    <?php elseif ($sub['submission_status'] === 'auto_submitted_timeout'): ?>
                                        <span
                                            style="background: #E0E7FF; color: #3730A3; font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                            ⏱️ Auto-Submitted (Time Limit Expired)
                                        </span>
                                    <?php else: ?>
                                        <span
                                            style="background: #D1FAE5; color: #065F46; font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                            ✅ Submitted Manually
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; color: #64748B; font-size: 12px;"><?= $sub['submitted_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Create New Online Exam (Teachers/Admins ONLY) -->
<?php if (!$isStudent): ?>
    <div id="createExamModal"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div
            style="background: #FFFFFF; width: 92%; max-width: 580px; max-height: 90vh; overflow-y: auto; border-radius: 14px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #E2E8F0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #EEF2FF; padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; color: #6366F1; text-transform: uppercase;">Examination Management</span>
                    <h3 style="margin: 2px 0 0 0; font-size: 18px; font-weight: 800; color: #0F172A;">+ Create New Online MCQ Exam</h3>
                </div>
                <button type="button" onclick="document.getElementById('createExamModal').style.display='none'"
                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748B; line-height: 1;">&times;</button>
            </div>

            <form action="index.php?page=create_online_exam" method="POST" id="createExamForm">
                <?= \App\Core\CSRF::inputField() ?>
                <input type="hidden" name="action" value="create_exam">

                <!-- Step 1: Department Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        1. Department *
                    </label>
                    <select name="department_id" id="create_exam_dept" onchange="cascadeExamForm('create')" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Step 2: Course / Degree Program Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        2. Course / Degree Program *
                    </label>
                    <select name="course_id" id="create_exam_course" onchange="cascadeExamForm('create')" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" data-dept="<?= (int)($c['department_id'] ?? 0) ?>">
                                <?= htmlspecialchars($c['name'] . ' (' . ($c['code'] ?? 'CRS') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Step 3: Academic Year Dropdown (FY / SY / TY) -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #1E40AF; margin-bottom: 4px;">
                        3. Academic Year *
                    </label>
                    <select name="academic_year" id="create_exam_year" onchange="cascadeExamForm('create')" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #93C5FD; font-size: 13px; font-weight: 700; background: #EFF6FF; color: #1E40AF;">
                        <option value="FY">FY — First Year</option>
                        <option value="SY">SY — Second Year</option>
                        <option value="TY">TY — Third Year</option>
                    </select>
                    <small style="color: #64748B; font-size: 11px;">Exams created for FY will only be visible to and accessible by enrolled First Year students.</small>
                </div>

                <!-- Step 4: Subject Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        4. Subject (Filtered by Dept, Course & Year)
                    </label>
                    <select name="subject_id" id="create_exam_subject"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="">-- General / All Subjects --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" 
                                data-dept="<?= (int)($s['department_id'] ?? 0) ?>" 
                                data-course="<?= (int)($s['course_id'] ?? 0) ?>"
                                data-std="<?= (int)($s['standard_id'] ?? 0) ?>">
                                <?= htmlspecialchars($s['name'] . ' (' . ($s['code'] ?? 'SUB') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Step 5: Target Student (Optional) -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        5. Target Student (Optional)
                    </label>
                    <select name="student_id" id="create_exam_student"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="all">-- All Eligible Cohort Students --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                data-dept="<?= (int)($st['department_id'] ?? 0) ?>"
                                data-course="<?= (int)($st['course_id'] ?? 0) ?>"
                                data-year="<?= $st['academic_year_code'] ?>">
                                <?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['roll_no'] . ' - ' . $st['academic_year_code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Step 6: Exam Title -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        6. Exam Title *
                    </label>
                    <input type="text" name="title" id="create_exam_title" placeholder="e.g. FY B.Sc. CS Internal Assessment 1" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                </div>

                <!-- Step 7: Duration & Status -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Duration (Minutes) *
                        </label>
                        <input type="number" name="duration_minutes" value="15" min="1" max="180" required
                            style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Exam Status *
                        </label>
                        <select name="status"
                            style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                            <option value="published">Published (Active)</option>
                            <option value="draft">Draft (Unpublished)</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('createExamModal').style.display='none'"
                        style="background: #F1F5F9; color: #475569; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">Cancel</button>
                    <button type="submit"
                        style="background: #6366F1; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 10px rgba(99,102,241,0.25);">
                        Save & Create Exam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Online Exam Configuration -->
    <div id="editExamModal"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div
            style="background: #FFFFFF; width: 92%; max-width: 580px; max-height: 90vh; overflow-y: auto; border-radius: 14px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #E2E8F0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #EEF2FF; padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; color: #6366F1; text-transform: uppercase;">Update Examination</span>
                    <h3 style="margin: 2px 0 0 0; font-size: 18px; font-weight: 800; color: #0F172A;">⚙️ Edit Exam Configuration</h3>
                </div>
                <button type="button" onclick="closeEditExamModal()"
                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748B; line-height: 1;">&times;</button>
            </div>

            <form action="index.php?page=update_online_exam" method="POST" id="editExamForm">
                <?= \App\Core\CSRF::inputField() ?>
                <input type="hidden" name="exam_id" id="edit_exam_id" value="">

                <!-- Department Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Department
                    </label>
                    <select name="department_id" id="edit_exam_dept" onchange="cascadeExamForm('edit')"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="">-- All Departments --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Course Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Course / Degree Program
                    </label>
                    <select name="course_id" id="edit_exam_course" onchange="cascadeExamForm('edit')"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="">-- All Courses --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" data-dept="<?= (int)($c['department_id'] ?? 0) ?>">
                                <?= htmlspecialchars($c['name'] . ' (' . ($c['code'] ?? 'CRS') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Academic Year Selection (FY / SY / TY) -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #1E40AF; margin-bottom: 4px;">
                        Academic Year *
                    </label>
                    <select name="academic_year" id="edit_exam_year" onchange="cascadeExamForm('edit')" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #93C5FD; font-size: 13px; font-weight: 700; background: #EFF6FF; color: #1E40AF;">
                        <option value="FY">FY — First Year</option>
                        <option value="SY">SY — Second Year</option>
                        <option value="TY">TY — Third Year</option>
                    </select>
                </div>

                <!-- Subject Selection -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Subject
                    </label>
                    <select name="subject_id" id="edit_exam_subject"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="">-- General / All Subjects --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" 
                                data-dept="<?= (int)($s['department_id'] ?? 0) ?>" 
                                data-course="<?= (int)($s['course_id'] ?? 0) ?>"
                                data-std="<?= (int)($s['standard_id'] ?? 0) ?>">
                                <?= htmlspecialchars($s['name'] . ' (' . ($s['code'] ?? 'SUB') . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Target Student (Optional) -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Target Student (Optional)
                    </label>
                    <select name="student_id" id="edit_exam_student"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                        <option value="all">-- All Eligible Cohort Students --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                data-dept="<?= (int)($st['department_id'] ?? 0) ?>"
                                data-course="<?= (int)($st['course_id'] ?? 0) ?>"
                                data-year="<?= $st['academic_year_code'] ?>">
                                <?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['roll_no'] . ' - ' . $st['academic_year_code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Exam Title -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Exam Title *
                    </label>
                    <input type="text" name="title" id="edit_exam_title" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                </div>

                <!-- Duration & Status -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Duration (Minutes) *
                        </label>
                        <input type="number" name="duration_minutes" id="edit_exam_duration" value="15" min="1" max="180" required
                            style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Exam Status *
                        </label>
                        <select name="status" id="edit_exam_status"
                            style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
                            <option value="published">Published (Active)</option>
                            <option value="draft">Draft (Unpublished)</option>
                            <option value="closed">Closed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeEditExamModal()"
                        style="background: #F1F5F9; color: #475569; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">Cancel</button>
                    <button type="submit"
                        style="background: #2563EB; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 10px rgba(37,99,235,0.25);">
                        Update Exam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modals: Manage MCQ Questions for Each Exam -->
    <?php foreach ($exams as $ex): ?>
        <?php $exQuestions = $examQuestionsMap[$ex['id']] ?? []; ?>
        <div id="manageMcqModal_<?= $ex['id'] ?>" class="manage-mcq-modal"
            style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div
                style="background: #FFFFFF; width: 95%; max-width: 800px; max-height: 90vh; border-radius: 16px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden;">

                <!-- Modal Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #EEF2FF;">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; color: #6366F1; text-transform: uppercase;">MCQ Question Management Portal</span>
                        <h3 style="margin: 2px 0 0 0; font-size: 18px; font-weight: 800; color: #0F172A;">
                            ✏️ <?= htmlspecialchars($ex['title'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="background: #EEF2FF; color: #4338CA; font-weight: 700; font-size: 12px; padding: 4px 12px; border-radius: 20px;">
                            ⏱️ <?= (int) $ex['duration_minutes'] ?> Mins | ❓ <?= count($exQuestions) ?> Question(s)
                        </span>
                        <button type="button" onclick="closeManageMcqModal(<?= $ex['id'] ?>)"
                            style="background: #F1F5F9; border: none; font-size: 20px; font-weight: 700; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: #64748B; display: flex; align-items: center; justify-content: center;">&times;</button>
                    </div>
                </div>

                <!-- Modal Body: Scrollable -->
                <div style="overflow-y: auto; padding-top: 16px; flex: 1; display: flex; flex-direction: column; gap: 20px;">

                    <!-- Section 1: Add New MCQ Question Form -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px;">
                        <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; color: #1E1B4B;">
                            ➕ Add New MCQ Question
                        </h4>
                        <form action="index.php?page=add_mcq_question" method="POST">
                            <?= \App\Core\CSRF::inputField() ?>
                            <input type="hidden" name="exam_id" value="<?= $ex['id'] ?>">

                            <div style="margin-bottom: 10px;">
                                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Question Text *</label>
                                <textarea name="question_text" rows="2" placeholder="e.g. Which layer of OSI model handles IP routing?" required style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option A *</label>
                                    <input type="text" name="option_a" placeholder="Option A text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option B *</label>
                                    <input type="text" name="option_b" placeholder="Option B text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option C *</label>
                                    <input type="text" name="option_c" placeholder="Option C text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Option D *</label>
                                    <input type="text" name="option_d" placeholder="Option D text" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #059669; margin-bottom: 2px;">Correct Answer *</label>
                                    <select name="correct_option" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #10B981; font-size: 12px; font-weight: 700; background: #ECFDF5; color: #065F46;">
                                        <option value="A">Option A</option>
                                        <option value="B">Option B</option>
                                        <option value="C">Option C</option>
                                        <option value="D">Option D</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 2px;">Marks *</label>
                                    <input type="number" name="marks" value="1" min="1" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px;">
                                </div>
                            </div>

                            <button type="submit" style="width: 100%; background: #4F46E5; color: #FFFFFF; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">
                                🚀 Save Question to Exam
                            </button>
                        </form>
                    </div>

                    <!-- Section 2: List of Existing Questions -->
                    <div>
                        <h4 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0 0 10px 0;">
                            Existing MCQ Questions (<?= count($exQuestions) ?>)
                        </h4>
                        <?php if (empty($exQuestions)): ?>
                            <div style="text-align: center; padding: 20px; background: #F8FAFC; border-radius: 8px; border: 1px dashed #CBD5E1; color: #64748B; font-size: 13px;">
                                No MCQ questions added yet. Use the form above to add questions.
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($exQuestions as $qIdx => $q): ?>
                                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                                            <div style="font-size: 13px; font-weight: 700; color: #1E293B;">
                                                Q<?= $qIdx + 1 ?>: <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?>
                                                <span style="font-size: 11px; color: #6366F1; margin-left: 6px;">(<?= (int) $q['marks'] ?> Mark)</span>
                                            </div>
                                            <form action="index.php?page=delete_mcq_question" method="POST" onsubmit="return confirm('Delete this question?');" style="margin: 0;">
                                                <?= \App\Core\CSRF::inputField() ?>
                                                <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                                <input type="hidden" name="exam_id" value="<?= $ex['id'] ?>">
                                                <button type="submit" style="background: #FEE2E2; color: #991B1B; border: none; padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700;">
                                                    🗑️ Delete Question
                                                </button>
                                            </form>
                                        </div>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 10px; font-size: 12px;">
                                            <div style="padding: 6px 10px; border-radius: 6px; background: <?= $q['correct_option'] === 'A' ? '#D1FAE5' : '#F8FAFC' ?>; color: <?= $q['correct_option'] === 'A' ? '#065F46' : '#334155' ?>; border: 1px solid <?= $q['correct_option'] === 'A' ? '#6EE7B7' : '#E2E8F0' ?>;">
                                                <strong>A:</strong> <?= htmlspecialchars($q['option_a'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="padding: 6px 10px; border-radius: 6px; background: <?= $q['correct_option'] === 'B' ? '#D1FAE5' : '#F8FAFC' ?>; color: <?= $q['correct_option'] === 'B' ? '#065F46' : '#334155' ?>; border: 1px solid <?= $q['correct_option'] === 'B' ? '#6EE7B7' : '#E2E8F0' ?>;">
                                                <strong>B:</strong> <?= htmlspecialchars($q['option_b'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="padding: 6px 10px; border-radius: 6px; background: <?= $q['correct_option'] === 'C' ? '#D1FAE5' : '#F8FAFC' ?>; color: <?= $q['correct_option'] === 'C' ? '#065F46' : '#334155' ?>; border: 1px solid <?= $q['correct_option'] === 'C' ? '#6EE7B7' : '#E2E8F0' ?>;">
                                                <strong>C:</strong> <?= htmlspecialchars($q['option_c'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="padding: 6px 10px; border-radius: 6px; background: <?= $q['correct_option'] === 'D' ? '#D1FAE5' : '#F8FAFC' ?>; color: <?= $q['correct_option'] === 'D' ? '#065F46' : '#334155' ?>; border: 1px solid <?= $q['correct_option'] === 'D' ? '#6EE7B7' : '#E2E8F0' ?>;">
                                                <strong>D:</strong> <?= htmlspecialchars($q['option_d'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="padding-top: 16px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end;">
                    <button type="button" onclick="closeManageMcqModal(<?= $ex['id'] ?>)"
                        style="background: #F1F5F9; color: #475569; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                        Close Manager
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal: Confirmation Dialog for Deleting Online Exam (Principal & Staff) -->
<div id="deleteExamModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.7); z-index:1050; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#FFFFFF; width:92%; max-width:500px; border-radius:16px; padding:24px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #E2E8F0;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div style="width:44px; height:44px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
                🗑️
            </div>
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#0F172A;">Confirm Exam Deletion</h3>
                <span id="deleteExamIdBadge" style="font-size:12px; color:#64748B; font-weight:600;">Exam #--</span>
            </div>
        </div>

        <div id="deleteExamWarningBox" style="background:#FFFBEB; border-left:4px solid #F59E0B; padding:12px 14px; border-radius:8px; margin-bottom:16px; font-size:13px; color:#92400E; line-height:1.5;">
            Are you sure you want to delete the exam <strong id="deleteExamTitleDisplay"></strong>? 
            This action may affect associated questions, attempts, and results. Please review the impact before continuing.
        </div>

        <div id="deleteExamMetaInfo" style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:20px; font-size:12px; color:#475569; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <div>📖 Course: <strong id="deleteExamCourseDisplay">--</strong></div>
            <div>⏱️ Duration: <strong id="deleteExamDurationDisplay">--</strong></div>
            <div>❓ Questions: <strong id="deleteExamQuestionsDisplay">--</strong></div>
            <div>📊 Student Attempts: <strong id="deleteExamAttemptsDisplay">0</strong></div>
        </div>

        <form id="deleteExamForm" action="index.php?page=delete_online_exam" method="POST" onsubmit="submitExamDelete(event)">
            <?= \App\Core\CSRF::inputField() ?>
            <input type="hidden" name="exam_id" id="deleteExamIdInput" value="">
            <input type="hidden" name="archive_if_has_submissions" id="deleteExamArchiveInput" value="1">

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeDeleteExamModal()" id="deleteExamCancelBtn"
                    style="background:#F1F5F9; color:#475569; border:none; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" id="deleteExamConfirmBtn"
                    style="background:#DC2626; color:#FFFFFF; border:none; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 4px 10px rgba(220,38,38,0.25);">
                    <span id="deleteBtnSpinner" style="display:none;">⏳</span>
                    <span id="deleteBtnText">Confirm Delete</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Cascading Dropdown Logic: Department -> Course -> Academic Year (FY/SY/TY) -> Subject -> Student
function cascadeExamForm(mode) {
    var deptSelect = document.getElementById(mode + '_exam_dept');
    var courseSelect = document.getElementById(mode + '_exam_course');
    var yearSelect = document.getElementById(mode + '_exam_year');
    var subjectSelect = document.getElementById(mode + '_exam_subject');
    var studentSelect = document.getElementById(mode + '_exam_student');

    if (!deptSelect || !courseSelect) return;
    var selectedDept = deptSelect.value;
    var selectedCourse = courseSelect.value;
    var selectedYear = yearSelect ? yearSelect.value : 'FY';
    var yearStdMap = { 'FY': 1, 'SY': 2, 'TY': 3 };
    var selectedStd = yearStdMap[selectedYear] || 1;

    // Filter Courses by selected Department
    Array.from(courseSelect.options).forEach(function(opt) {
        if (!opt.value) { opt.style.display = 'block'; return; }
        var optDept = opt.getAttribute('data-dept');
        var matchDept = (!selectedDept || !optDept || optDept === '0' || optDept === selectedDept);
        opt.style.display = matchDept ? 'block' : 'none';
    });
    var currentCourseOpt = courseSelect.options[courseSelect.selectedIndex];
    if (currentCourseOpt && currentCourseOpt.style.display === 'none') {
        courseSelect.value = '';
        selectedCourse = '';
    }

    // Filter Subjects by Department, Course and Academic Year standard
    if (subjectSelect) {
        Array.from(subjectSelect.options).forEach(function(opt) {
            if (!opt.value) { opt.style.display = 'block'; return; }
            var optDept = opt.getAttribute('data-dept');
            var optCourse = opt.getAttribute('data-course');
            var optStd = opt.getAttribute('data-std');
            var matchDept = (!selectedDept || !optDept || optDept === '0' || optDept === selectedDept);
            var matchCourse = (!selectedCourse || !optCourse || optCourse === '0' || optCourse === selectedCourse);
            var matchStd = (!optStd || optStd === '0' || optStd == selectedStd);
            opt.style.display = (matchDept && matchCourse && matchStd) ? 'block' : 'none';
        });
        var currentSubjectOpt = subjectSelect.options[subjectSelect.selectedIndex];
        if (currentSubjectOpt && currentSubjectOpt.style.display === 'none') {
            subjectSelect.value = '';
        }
    }

    // Filter Students by Department, Course and Academic Year
    if (studentSelect) {
        Array.from(studentSelect.options).forEach(function(opt) {
            if (!opt.value || opt.value === 'all') { opt.style.display = 'block'; return; }
            var optDept = opt.getAttribute('data-dept');
            var optCourse = opt.getAttribute('data-course');
            var optYear = opt.getAttribute('data-year');
            var matchDept = (!selectedDept || !optDept || optDept === '0' || optDept === selectedDept);
            var matchCourse = (!selectedCourse || !optCourse || optCourse === '0' || optCourse === selectedCourse);
            var matchYear = (!optYear || optYear === selectedYear);
            opt.style.display = (matchDept && matchCourse && matchYear) ? 'block' : 'none';
        });
        var currentStudentOpt = studentSelect.options[studentSelect.selectedIndex];
        if (currentStudentOpt && currentStudentOpt.style.display === 'none') {
            studentSelect.value = 'all';
        }
    }
}

// Edit Exam Modal Controller
function openEditExamModal(btn) {
    if (!btn) return;
    var examId = btn.getAttribute('data-id');
    var title = btn.getAttribute('data-title');
    var deptId = btn.getAttribute('data-dept');
    var courseId = btn.getAttribute('data-course');
    var year = btn.getAttribute('data-year') || 'FY';
    var subjectId = btn.getAttribute('data-subject');
    var studentId = btn.getAttribute('data-student') || 'all';
    var duration = btn.getAttribute('data-duration') || '15';
    var status = btn.getAttribute('data-status') || 'published';

    document.getElementById('edit_exam_id').value = examId;
    document.getElementById('edit_exam_title').value = title;
    document.getElementById('edit_exam_dept').value = deptId || '';
    document.getElementById('edit_exam_year').value = year;
    document.getElementById('edit_exam_duration').value = duration;
    document.getElementById('edit_exam_status').value = status;

    // Run cascade to update visible courses, subjects, and students
    cascadeExamForm('edit');

    // Restore course, subject, and student selection after cascade
    if (courseId) document.getElementById('edit_exam_course').value = courseId;
    if (subjectId) document.getElementById('edit_exam_subject').value = subjectId;
    if (studentId) document.getElementById('edit_exam_student').value = studentId;

    document.getElementById('editExamModal').style.display = 'flex';
}

function closeEditExamModal() {
    var modal = document.getElementById('editExamModal');
    if (modal) modal.style.display = 'none';
}

// Safe Handler for Delete Exam Button
function handleDeleteExamClick(btn) {
    if (!btn) return;
    var exam = {
        id: btn.getAttribute('data-id'),
        title: btn.getAttribute('data-title'),
        course: btn.getAttribute('data-course'),
        year: btn.getAttribute('data-year'),
        duration: btn.getAttribute('data-duration'),
        questions: btn.getAttribute('data-questions'),
        submissions: parseInt(btn.getAttribute('data-submissions') || '0', 10)
    };
    openDeleteExamModal(exam);
}

document.addEventListener('DOMContentLoaded', function() {
    cascadeExamForm('create');
});

function openManageMcqModal(examId) {
    var modals = document.querySelectorAll('.manage-mcq-modal');
    modals.forEach(function(m) { m.style.display = 'none'; });
    var modal = document.getElementById('manageMcqModal_' + examId);
    if (modal) {
        modal.style.display = 'flex';
    }
    var section = document.getElementById('mcqBuilderSection');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

function closeManageMcqModal(examId) {
    var modal = document.getElementById('manageMcqModal_' + examId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function openDeleteExamModal(exam) {
    document.getElementById('deleteExamIdInput').value = exam.id;
    document.getElementById('deleteExamIdBadge').textContent = 'Exam ID #' + exam.id;
    document.getElementById('deleteExamTitleDisplay').textContent = exam.title;
    document.getElementById('deleteExamCourseDisplay').textContent = exam.course || 'All Courses';
    document.getElementById('deleteExamDurationDisplay').textContent = exam.duration + ' Mins';
    document.getElementById('deleteExamQuestionsDisplay').textContent = exam.questions + ' Questions';
    document.getElementById('deleteExamAttemptsDisplay').textContent = (exam.submissions || 0) + ' Attempt(s)';

    var warningBox = document.getElementById('deleteExamWarningBox');
    var confirmBtn = document.getElementById('deleteExamConfirmBtn');
    var btnText = document.getElementById('deleteBtnText');
    var archiveInput = document.getElementById('deleteExamArchiveInput');

    if (exam.submissions > 0) {
        warningBox.style.background = '#EFF6FF';
        warningBox.style.borderLeftColor = '#3B82F6';
        warningBox.style.color = '#1E40AF';
        warningBox.innerHTML = '<strong>Data Retention Policy Active:</strong> Are you sure you want to delete the exam "<strong>' + escapeHtml(exam.title) + '</strong>"? This exam has <strong>' + exam.submissions + ' student submission(s)</strong>. To protect student academic records and institutional compliance, confirming will <strong>safely archive</strong> this exam and remove it from active examinations without deleting student scores.';
        confirmBtn.style.background = '#2563EB';
        btnText.textContent = 'Confirm Archive';
        archiveInput.value = '1';
    } else {
        warningBox.style.background = '#FFFBEB';
        warningBox.style.borderLeftColor = '#F59E0B';
        warningBox.style.color = '#92400E';
        warningBox.innerHTML = 'Are you sure you want to delete the exam "<strong>' + escapeHtml(exam.title) + '</strong>"? This action may affect associated questions, attempts, and results. Please review the impact before continuing.';
        confirmBtn.style.background = '#DC2626';
        btnText.textContent = 'Confirm Delete';
        archiveInput.value = '1';
    }

    confirmBtn.disabled = false;
    document.getElementById('deleteExamCancelBtn').disabled = false;
    document.getElementById('deleteBtnSpinner').style.display = 'none';
    document.getElementById('deleteExamModal').style.display = 'flex';
}

function closeDeleteExamModal() {
    var modal = document.getElementById('deleteExamModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function submitExamDelete(e) {
    e.preventDefault();
    var form = document.getElementById('deleteExamForm');
    var examId = document.getElementById('deleteExamIdInput').value;
    var confirmBtn = document.getElementById('deleteExamConfirmBtn');
    var cancelBtn = document.getElementById('deleteExamCancelBtn');
    var spinner = document.getElementById('deleteBtnSpinner');
    var btnText = document.getElementById('deleteBtnText');

    // Prevent duplicate requests while operation is in progress
    confirmBtn.disabled = true;
    cancelBtn.disabled = true;
    spinner.style.display = 'inline-block';
    btnText.textContent = 'Processing...';

    var formData = new FormData(form);

    fetch('index.php?page=delete_online_exam', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(function(res) {
        return res.json().then(function(data) {
            return { ok: res.ok, status: res.status, data: data };
        });
    })
    .then(function(result) {
        if (result.ok && result.data && result.data.success) {
            closeDeleteExamModal();
            // Remove exam card from DOM
            var card = document.getElementById('examCard_' + examId);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    card.remove();
                    var container = document.getElementById('examCardsContainer');
                    if (container && container.querySelectorAll('.exam-item-card').length === 0) {
                        container.innerHTML = '<p id="emptyExamsMessage" style="text-align: center; color: #64748B; padding: 30px;">No online exams available at this time.</p>';
                    }
                }, 300);
            }
            // Update total exams counter
            var totalCountEl = document.getElementById('totalExamsCounter');
            if (totalCountEl) {
                var cur = parseInt(totalCountEl.textContent, 10);
                if (!isNaN(cur) && cur > 0) {
                    totalCountEl.textContent = (cur - 1);
                }
            }
            showToastAlert(result.data.message || 'Exam deleted successfully.', 'success');
        } else {
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.textContent = 'Confirm Delete';
            var errorMsg = (result.data && result.data.error) ? result.data.error : 'Unable to delete the exam. Please try again.';
            alert(errorMsg);
        }
    })
    .catch(function(err) {
        // Fallback: submit standard form if network/JSON error
        form.submit();
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function showToastAlert(msg, type) {
    var existing = document.getElementById('floatingToastAlert');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.id = 'floatingToastAlert';
    var bg = (type === 'success') ? '#D1FAE5' : '#FEE2E2';
    var color = (type === 'success') ? '#065F46' : '#991B1B';
    var border = (type === 'success') ? '#10B981' : '#EF4444';
    var icon = (type === 'success') ? '✅' : '❌';

    toast.style.cssText = 'position:fixed; top:24px; right:24px; z-index:9999; background:' + bg + '; color:' + color + '; padding:14px 20px; border-radius:10px; border-left:5px solid ' + border + '; font-weight:700; font-size:14px; box-shadow:0 10px 25px rgba(0,0,0,0.15); display:flex; align-items:center; gap:10px;';
    toast.innerHTML = '<span>' + icon + ' ' + escapeHtml(msg) + '</span><button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:' + color + '; font-size:16px; cursor:pointer; font-weight:800; margin-left:10px;">&times;</button>';
    document.body.appendChild(toast);
    setTimeout(function() { if (toast && toast.parentElement) toast.remove(); }, 5000);
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>