<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<div class="main-content" style="padding: 24px; max-width: 900px; margin: 0 auto;">

    <!-- Flash Messages -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div style="background: #D1FAE5; color: #065F46; padding: 14px 18px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; border-left: 4px solid #10B981;">
            <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::getFlash('warning')): ?>
        <div style="background: #FEF3C7; color: #92400E; padding: 14px 18px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; border-left: 4px solid #F59E0B;">
            <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
        <div style="background: #FEE2E2; color: #991B1B; padding: 14px 18px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; border-left: 4px solid #EF4444;">
            <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($existingSubmission): ?>
        <!-- ========================================== -->
        <!-- VIEW MODE: EXAM ALREADY SUBMITTED & RESULTS -->
        <!-- ========================================== -->
        <?php 
        $pct = ($existingSubmission['total_score'] > 0) ? round(($existingSubmission['score'] / $existingSubmission['total_score']) * 100, 1) : 0;
        ?>
        <div style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); margin-bottom: 24px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 8px;">
                <?= $pct >= 50 ? '🏆' : '📝' ?>
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;"><?= htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') ?> - Result Summary</h1>
            <p style="color: #64748B; font-size: 14px; margin: 0 0 20px 0;">Course: <strong><?= htmlspecialchars($exam['course_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></strong></p>

            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 24px;">
                <div style="background: #F8FAFC; padding: 16px 24px; border-radius: 12px; border: 1px solid #E2E8F0; min-width: 150px;">
                    <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Marks Obtained</div>
                    <div style="font-size: 32px; font-weight: 800; color: #4338CA; margin-top: 4px;"><?= $existingSubmission['score'] ?> / <?= $existingSubmission['total_score'] ?></div>
                </div>
                <div style="background: #F8FAFC; padding: 16px 24px; border-radius: 12px; border: 1px solid #E2E8F0; min-width: 150px;">
                    <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Score Percentage</div>
                    <div style="font-size: 32px; font-weight: 800; color: <?= $pct >= 50 ? '#059669' : '#DC2626' ?>; margin-top: 4px;"><?= $pct ?>%</div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <?php if ($existingSubmission['submission_status'] === 'auto_submitted_tab_switch'): ?>
                    <div style="display: inline-block; background: #FEE2E2; color: #991B1B; font-weight: 800; padding: 8px 16px; border-radius: 20px; font-size: 13px;">
                        ⚠️ Auto-Submitted: Tab Switch / Focus Loss Violation Detected
                    </div>
                <?php elseif ($existingSubmission['submission_status'] === 'auto_submitted_back_button'): ?>
                    <div style="display: inline-block; background: #FEF3C7; color: #92400E; font-weight: 800; padding: 8px 16px; border-radius: 20px; font-size: 13px;">
                        ⚠️ Auto-Submitted: Back Button / Navigation Violation Detected
                    </div>
                <?php elseif ($existingSubmission['submission_status'] === 'auto_submitted_timeout'): ?>
                    <div style="display: inline-block; background: #E0E7FF; color: #3730A3; font-weight: 800; padding: 8px 16px; border-radius: 20px; font-size: 13px;">
                        ⏱️ Auto-Submitted: Exam Time Limit Expired
                    </div>
                <?php else: ?>
                    <div style="display: inline-block; background: #D1FAE5; color: #065F46; font-weight: 800; padding: 8px 16px; border-radius: 20px; font-size: 13px;">
                        ✅ Exam Submitted Successfully
                    </div>
                <?php endif; ?>
            </div>

            <div style="font-size: 12px; color: #64748B;">Submitted on: <?= $existingSubmission['submitted_at'] ?></div>
        </div>

        <!-- Detailed Breakdown -->
        <div style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Detailed Question Breakdown</h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($questions as $idx => $q): ?>
                    <?php 
                    $userAns = $submittedAnswers[$q['id']] ?? null;
                    $selOpt = $userAns ? $userAns['selected_option'] : null;
                    $isCorrect = $userAns ? (bool)$userAns['is_correct'] : false;
                    ?>
                    <div style="border: 2px solid <?= $isCorrect ? '#10B981' : ($selOpt ? '#EF4444' : '#CBD5E1') ?>; background: <?= $isCorrect ? '#ECFDF5' : ($selOpt ? '#FEF2F2' : '#F8FAFC') ?>; border-radius: 12px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div style="font-size: 14px; font-weight: 700; color: #1E293B;">
                                Q<?= $idx + 1 ?>: <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div>
                                <?php if ($isCorrect): ?>
                                    <span style="background: #10B981; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 12px;">+<?= (int)$q['marks'] ?> Mark (Correct)</span>
                                <?php elseif ($selOpt): ?>
                                    <span style="background: #EF4444; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 12px;">0 Marks (Incorrect)</span>
                                <?php else: ?>
                                    <span style="background: #94A3B8; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 12px;">0 Marks (Unanswered)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; margin-top: 10px;">
                            <?php foreach (['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']] as $optKey => $optVal): ?>
                                <?php 
                                $isCorrectOpt = ($q['correct_option'] === $optKey);
                                $isUserChoice = ($selOpt === $optKey);
                                $bgColor = '#FFFFFF';
                                $borderColor = '#E2E8F0';
                                if ($isCorrectOpt) {
                                    $bgColor = '#D1FAE5';
                                    $borderColor = '#10B981';
                                } elseif ($isUserChoice && !$isCorrectOpt) {
                                    $bgColor = '#FEE2E2';
                                    $borderColor = '#EF4444';
                                }
                                ?>
                                <div style="padding: 8px 12px; border-radius: 6px; border: 1px solid <?= $borderColor ?>; background: <?= $bgColor ?>; display: flex; align-items: center; justify-content: space-between;">
                                    <span><strong><?= $optKey ?>:</strong> <?= htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($isCorrectOpt): ?>
                                        <span style="font-weight: 800; color: #065F46; font-size: 11px;">✓ Correct</span>
                                    <?php elseif ($isUserChoice): ?>
                                        <span style="font-weight: 800; color: #991B1B; font-size: 11px;">✗ Your Choice</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 24px; text-align: center;">
                <a href="index.php?page=module&name=online_exam" style="background: #4F46E5; color: #FFFFFF; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; display: inline-block;">
                    Back to Online Exam Directory
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- EXECUTION MODE: ACTIVE EXAM & ANTI-CHEATING -->
        <!-- ========================================== -->
        <?php $durationSeconds = max(60, (int)$exam['duration_minutes'] * 60); ?>

        <!-- Sticky Header: Timer & Security Badge -->
        <div style="position: sticky; top: 10px; z-index: 900; background: #1E1B4B; color: #FFFFFF; border-radius: 14px; padding: 16px 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800; margin: 0;"><?= htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <div style="font-size: 12px; color: #C7D2FE; margin-top: 2px;">
                    Questions: <strong><?= count($questions) ?> MCQ</strong> | Course: <strong><?= htmlspecialchars($exam['course_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <!-- Anti-Cheating Monitor Indicator -->
                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #FCA5A5; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 20px; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; background: #EF4444; border-radius: 50%; animation: pulse 1.5s infinite;"></span>
                    <span>Anti-Cheating Monitored</span>
                </div>

                <!-- Timer -->
                <div style="background: #312E81; padding: 8px 16px; border-radius: 10px; border: 1px solid #4338CA; text-align: center;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #A5B4FC; font-weight: 700;">Remaining Time</div>
                    <div id="examTimerClock" style="font-size: 22px; font-weight: 900; color: #10B981; font-family: monospace;">
                        --:--
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Alert Banner -->
        <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; color: #92400E; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; line-height: 1.5;">
            <strong>⚠️ EXAM SECURITY NOTICE:</strong> Do NOT switch tabs, minimize window, lose window focus, or press browser back button. Any tab-switching or back button press will <strong>IMMEDIATELY AUTO-SUBMIT</strong> your exam!
        </div>

        <!-- Exam Form -->
        <form id="onlineExamForm" action="index.php?page=submit_online_exam" method="POST">
            <?= \App\Core\CSRF::inputField() ?>
            <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
            <input type="hidden" name="submission_reason" id="submissionReasonInput" value="manual">

            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
                <?php foreach ($questions as $idx => $q): ?>
                    <div style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="font-size: 15px; font-weight: 700; color: #0F172A; margin-bottom: 14px; display: flex; justify-content: space-between;">
                            <span>Q<?= $idx + 1 ?>: <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span style="font-size: 12px; color: #6366F1; font-weight: 700;">[<?= (int)$q['marks'] ?> Mark]</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach (['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']] as $optKey => $optVal): ?>
                                <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; border: 1px solid #E2E8F0; background: #F8FAFC; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='#EEF2FF'; this.style.borderColor='#6366F1';" onmouseout="if(!this.querySelector('input').checked){this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';}">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $optKey ?>" style="width: 18px; height: 18px; accent-color: #4F46E5;" onchange="this.parentElement.parentElement.querySelectorAll('label').forEach(l => {l.style.background='#F8FAFC'; l.style.borderColor='#E2E8F0';}); this.parentElement.style.background='#EEF2FF'; this.parentElement.style.borderColor='#4F46E5';">
                                    <span style="font-size: 13px; font-weight: 700; color: #4338CA; width: 24px; height: 24px; border-radius: 50%; background: #E0E7FF; display: flex; align-items: center; justify-content: center;"><?= $optKey ?></span>
                                    <span style="font-size: 13px; color: #1E293B; font-weight: 500;"><?= htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Button -->
            <div style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; text-align: center;">
                <button type="submit" onclick="return confirm('Are you sure you want to submit your exam?');" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; border: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);">
                    🚀 Submit Exam Now
                </button>
            </div>
        </form>

        <!-- Anti-Cheating & Countdown Timer JavaScript -->
        <script>
            (function() {
                var totalSeconds = <?= (int)$durationSeconds ?>;
                var formSubmitted = false;
                var form = document.getElementById('onlineExamForm');
                var reasonInput = document.getElementById('submissionReasonInput');
                var clockDisplay = document.getElementById('examTimerClock');

                // 1. Countdown Timer
                function updateClock() {
                    if (totalSeconds <= 0) {
                        if (!formSubmitted) {
                            formSubmitted = true;
                            reasonInput.value = 'auto_submitted_timeout';
                            alert('⏱️ Time limit expired! Your exam is submitting automatically.');
                            form.submit();
                        }
                        return;
                    }
                    totalSeconds--;

                    var mins = Math.floor(totalSeconds / 60);
                    var secs = totalSeconds % 60;
                    var minsStr = mins < 10 ? '0' + mins : mins;
                    var secsStr = secs < 10 ? '0' + secs : secs;
                    
                    clockDisplay.textContent = minsStr + ':' + secsStr;

                    if (totalSeconds < 120) {
                        clockDisplay.style.color = '#EF4444';
                    }
                }
                updateClock();
                var timerInterval = setInterval(updateClock, 1000);

                // 2. Anti-Cheating: Tab Switch & Window Blur Detection
                function triggerTabSwitchAutoSubmit() {
                    if (!formSubmitted) {
                        formSubmitted = true;
                        reasonInput.value = 'auto_submitted_tab_switch';
                        alert('⚠️ TAB SWITCH DETECTED! Anti-cheating policy triggered. Your exam will be submitted automatically NOW.');
                        form.submit();
                    }
                }

                document.addEventListener('visibilitychange', function() {
                    if (document.hidden || document.visibilityState === 'hidden') {
                        triggerTabSwitchAutoSubmit();
                    }
                });

                window.addEventListener('blur', function() {
                    triggerTabSwitchAutoSubmit();
                });

                // 3. Anti-Cheating: Back Button & Page Navigation Detection
                function triggerBackButtonAutoSubmit() {
                    if (!formSubmitted) {
                        formSubmitted = true;
                        reasonInput.value = 'auto_submitted_back_button';
                        alert('⚠️ BACK BUTTON NAVIGATION DETECTED! Anti-cheating policy triggered. Your exam will be submitted automatically NOW.');
                        form.submit();
                    }
                }

                // Push fake history state to trap back button
                history.pushState(null, null, location.href);
                window.addEventListener('popstate', function(event) {
                    history.pushState(null, null, location.href);
                    triggerBackButtonAutoSubmit();
                });

                // Form submit handler to prevent multi-submit
                form.addEventListener('submit', function() {
                    formSubmitted = true;
                });
            })();
        </script>
    <?php endif; ?>

</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
