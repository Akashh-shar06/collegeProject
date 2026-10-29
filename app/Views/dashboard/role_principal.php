<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner -->
    <div style="background:linear-gradient(135deg, #1E293B, #0F172A); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);">
        <div>
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:32px;">🛡️</span>
                <div>
                    <h2 style="font-size:22px; font-weight:800; margin:0;">Principal Executive Staff Manager Portal</h2>
                    <p style="font-size:13px; opacity:0.85; margin:4px 0 0 0;">Overall management authority for Teaching & Non-Teaching Staff, task assignments, leave approvals & academic monitoring</p>
                </div>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-primary" onclick="openAddDepartmentModal()" style="background:#4F46E5; font-weight:700; border:none; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px; color:#FFF;">🏛️ + Add / Manage Depts</button>
            <a href="index.php?page=module&name=staff_management" class="btn btn-primary" style="background:#2563EB; font-weight:700; text-decoration:none;">👨‍💼 Staff Management</a>
            <a href="index.php?page=module&name=leave_management" class="btn btn-secondary" style="background:rgba(255,255,255,0.15); color:#FFFFFF; border:1px solid rgba(255,255,255,0.3); font-weight:700; text-decoration:none;">📅 Leave Approvals Queue</a>
        </div>
    </div>

    <!-- 1. STAFF OVERVIEW METRICS SECTION -->
    <section class="metrics-row" style="margin-bottom:24px;">
        <a href="index.php?page=module&name=staff_management" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Teaching Staff</span></div>
            <div class="metric-value" style="color:#2563EB;"><?= count(array_filter($staffList ?? [], fn($st) => str_contains(strtolower($st['role_name'] ?? ''), 'teacher') || str_contains(strtolower($st['designation'] ?? ''), 'professor'))) ?> Faculty</div>
            <div class="metric-sub">Academic Professors & Lecturers</div>
        </a>
        <a href="index.php?page=module&name=staff_management" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Non-Teaching Staff</span></div>
            <div class="metric-value" style="color:#059669;"><?= count(array_filter($staffList ?? [], fn($st) => !str_contains(strtolower($st['role_name'] ?? ''), 'teacher') && !str_contains(strtolower($st['designation'] ?? ''), 'professor'))) ?> Roles</div>
            <div class="metric-sub">Admin, Finance, HR, Library, Transport, IT</div>
        </a>
        <a href="index.php?page=module&name=staff_attendance" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Total Staff Roster</span></div>
            <div class="metric-value" style="color:#059669;"><?= count($staffList ?? []) ?> Active</div>
            <div class="metric-sub">Institutional Employee Roster</div>
        </a>
        <a href="index.php?page=module&name=leave_management" class="metric-card" style="text-decoration:none; color:inherit;">
            <div class="metric-header"><span class="metric-title">Staff On Leave / Pending</span></div>
            <div class="metric-value" style="color:#D97706;"><?= count($pendingLeaves ?? []) ?> Requests</div>
            <div class="metric-sub">Pending Executive Approvals</div>
        </a>
    </section>

    <!-- 2. REAL-TIME INSTITUTIONAL ALERTS WIDGET -->
    <div style="background:#FEF2F2; border:1px solid #FCA5A5; border-radius:10px; padding:16px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:24px;">⚠️</span>
            <div>
                <strong style="color:#991B1B; font-size:14px;">Institutional Executive Alerts</strong>
                <div style="font-size:12px; color:#7F1D1D; margin-top:2px;">
                    • 1 Pending Staff Leave Request awaiting Principal Final Approval &nbsp;|&nbsp; 
                    • 2 Staff Tasks In Progress &nbsp;|&nbsp; 
                    • Mid-Semester Gradebook evaluation 92% completed
                </div>
            </div>
        </div>
        <a href="index.php?page=module&name=leave_management" class="btn btn-secondary" style="font-size:12px; background:#DC2626; color:#FFFFFF; text-decoration:none; border:none; padding:6px 14px; font-weight:700;">Review Approvals</a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">
        <!-- 3. STAFF TASK ASSIGNMENT ENGINE -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#0F172A;">Staff Task Assignments & Progress</h3>
                    <span style="font-size:12px; color:#64748B;">Tasks assigned by Principal to Teaching & Non-Teaching Staff</span>
                </div>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('assignTaskModal').classList.add('active')" style="font-size:12px; padding:6px 12px;">+ Assign Task</button>
            </div>
            <div style="overflow-x:auto; margin-top:14px;">
                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1px solid #CBD5E1;">
                            <th style="padding:8px;">TASK</th>
                            <th style="padding:8px;">ASSIGNED TO</th>
                            <th style="padding:8px;">DUE DATE</th>
                            <th style="padding:8px;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:8px; font-weight:700; color:#0F172A;">Mid-Semester Marks Entry</td>
                            <td style="padding:8px;">Dr. Sahil Sharma (Teacher)</td>
                            <td style="padding:8px; color:#475569;">25 Aug 2026</td>
                            <td style="padding:8px;"><span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#FEF3C7; color:#92400E;">IN PROGRESS</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:8px; font-weight:700; color:#0F172A;">Fee Collection Reconciliation</td>
                            <td style="padding:8px;">Frank Woods (Accountant)</td>
                            <td style="padding:8px; color:#475569;">28 Aug 2026</td>
                            <td style="padding:8px;"><span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#DCFCE7; color:#166534;">COMPLETED</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. PENDING LEAVE APPROVALS QUEUE -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#0F172A;">Staff Leave Approvals Queue</h3>
                    <span style="font-size:12px; color:#64748B;">Staff & HR Leave requests requiring Principal final approval</span>
                </div>
                <a href="index.php?page=module&name=leave_management" class="btn btn-secondary" style="font-size:12px; padding:6px 12px; text-decoration:none;">View All</a>
            </div>
            <div style="overflow-x:auto; margin-top:14px;">
                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1px solid #CBD5E1;">
                            <th style="padding:8px;">EMPLOYEE</th>
                            <th style="padding:8px;">LEAVE TYPE</th>
                            <th style="padding:8px;">DAYS</th>
                            <th style="padding:8px;">HR STATUS</th>
                            <th style="padding:8px; text-align:center;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:8px; font-weight:700; color:#0F172A;">Dr. Sahil Sharma</td>
                            <td style="padding:8px; color:#2563EB; font-weight:600;">Casual Leave</td>
                            <td style="padding:8px; font-weight:700;">3 Days</td>
                            <td style="padding:8px;"><span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#DCFCE7; color:#166534;">RECOMMENDED</span></td>
                            <td style="padding:8px; text-align:center;"><a href="index.php?page=module&name=leave_management" class="btn btn-primary" style="font-size:10px; padding:2px 8px; background:#059669; text-decoration:none;">Review</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. ACADEMIC & EXAMINATION MONITORING SECTION -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Academic Operations & Examination Gradebook Monitor</h3>
            <a href="index.php?page=module&name=exam_management_module" class="btn btn-secondary" style="text-decoration:none;">View Institutional Gradebook</a>
        </div>
        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #CBD5E1;">
                        <th style="padding:10px;">CLASS / BATCH</th>
                        <th style="padding:10px;">COURSE / SUBJECT</th>
                        <th style="padding:10px;">TEACHER IN-CHARGE</th>
                        <th style="padding:10px;">ATTENDANCE RATE</th>
                        <th style="padding:10px;">MARKS SUBMISSION STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid #E2E8F0;">
                        <td style="padding:10px; font-weight:700; color:#0F172A;">B.Tech CS - Sec A</td>
                        <td style="padding:10px;">Data Structures & Algorithms</td>
                        <td style="padding:10px; font-weight:600;">Dr. Sahil Sharma</td>
                        <td style="padding:10px;"><span style="color:#059669; font-weight:700;">96.8%</span></td>
                        <td style="padding:10px;"><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534;">SUBMITTED (100%)</span></td>
                    </tr>
                    <tr style="border-bottom:1px solid #E2E8F0;">
                        <td style="padding:10px; font-weight:700; color:#0F172A;">B.Tech EEE - Sec B</td>
                        <td style="padding:10px;">Circuit Theory & Power Systems</td>
                        <td style="padding:10px; font-weight:600;">Prof. Soap MacTavish</td>
                        <td style="padding:10px;"><span style="color:#059669; font-weight:700;">94.2%</span></td>
                        <td style="padding:10px;"><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#FEF3C7; color:#92400E;">IN PROGRESS (85%)</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL: ASSIGN STAFF TASK -->
<div class="modal-overlay" id="assignTaskModal">
    <div class="modal-card" style="max-width:540px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">📋 Assign Work Task to Staff Member</h3>
            <button type="button" onclick="document.getElementById('assignTaskModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=task_assign" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Task Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Complete Mid-Semester Marks Entry" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Assign To Employee *</label>
                    <select name="assigned_to_user_id" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                        <?php if (!empty($allStaffUsers)): ?>
                            <?php foreach ($allStaffUsers as $su): ?>
                                <option value="<?= $su['id'] ?>"><?= htmlspecialchars($su['username'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($su['role_display'], ENT_QUOTES, 'UTF-8') ?>)</option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="3">Dr. Sahil Sharma (Teacher)</option>
                            <option value="4">Alex Mason (Administrative Officer)</option>
                            <option value="5">Frank Woods (Accountant)</option>
                            <option value="6">David HR (HR Executive)</option>
                            <option value="7">Librarian User (Librarian)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Priority</label>
                        <select name="priority" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Start Date</label>
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Due Date</label>
                        <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Task Description & Instructions *</label>
                    <textarea name="description" rows="3" required class="form-control" placeholder="Detailed work instructions for the employee..." style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('assignTaskModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Task</button>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
