<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Header Banner -->
    <div style="background:linear-gradient(135deg, #1E293B, #0F172A); color:#FFFFFF; padding:24px; border-radius:var(--radius-lg); margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:28px;">👨‍💼</span>
                <div>
                    <h2 style="font-size:22px; font-weight:800; margin:0;">Staff Management & Executive Portal</h2>
                    <p style="font-size:13px; opacity:0.85; margin:4px 0 0 0;">Overall management, salary, contact details, performance monitoring & task assignments for Teaching and Non-Teaching Staff</p>
                </div>
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('addStaffModal').classList.add('active')" style="background:#059669; font-weight:700; border:none; cursor:pointer;">+ Add New Staff Member</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('assignTaskModal').classList.add('active')" style="background:#2563EB; font-weight:700;">+ Assign Staff Task</button>
            <a href="index.php?page=module&name=leave_management" class="btn btn-secondary" style="background:rgba(255,255,255,0.15); color:#FFFFFF; border:1px solid rgba(255,255,255,0.3); font-weight:700; text-decoration:none;">📅 Leave Approvals</a>
        </div>
    </div>

    <?php
    $allStaff = $records ?? $staffList ?? [];
    $teachingStaff = [];
    $nonTeachingStaff = [];
    $totalPayrollSum = 0.00;

    foreach ($allStaff as $st) {
        $desig = strtolower($st['designation'] ?? '');
        $role = strtolower($st['role_name'] ?? '');
        $sal = (float)($st['salary'] ?? 0);
        $totalPayrollSum += $sal;

        if (str_contains($desig, 'teacher') || str_contains($desig, 'faculty') || str_contains($desig, 'professor') || str_contains($desig, 'lecturer') || str_contains($desig, 'instructor') || str_contains($desig, 'tutor') || $role === 'teacher') {
            $teachingStaff[] = $st;
        } else {
            $nonTeachingStaff[] = $st;
        }
    }
    ?>

    <!-- Quick Metrics Summary Bar -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:16px; border-radius:10px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:10px; background:#EFF6FF; color:#2563EB; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800;">👨‍🏫</div>
            <div>
                <div style="font-size:12px; color:#64748B; font-weight:600; text-transform:uppercase;">Teaching Faculty</div>
                <div style="font-size:20px; font-weight:800; color:#0F172A;"><?= count($teachingStaff) ?> Active Staff</div>
            </div>
        </div>
        <div style="background:#FFFFFF; padding:16px; border-radius:10px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:10px; background:#ECFDF5; color:#059669; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800;">🛠️</div>
            <div>
                <div style="font-size:12px; color:#64748B; font-weight:600; text-transform:uppercase;">Non-Teaching Staff</div>
                <div style="font-size:20px; font-weight:800; color:#0F172A;"><?= count($nonTeachingStaff) ?> Active Roles</div>
            </div>
        </div>
        <div style="background:#FFFFFF; padding:16px; border-radius:10px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:10px; background:#F0FDF4; color:#166534; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800;">💰</div>
            <div>
                <div style="font-size:12px; color:#64748B; font-weight:600; text-transform:uppercase;">Total Monthly Payroll</div>
                <div style="font-size:18px; font-weight:800; color:#15803D;">₹<?= number_format($totalPayrollSum, 2) ?></div>
            </div>
        </div>
        <div style="background:#FFFFFF; padding:16px; border-radius:10px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:10px; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800;">📋</div>
            <div>
                <div style="font-size:12px; color:#64748B; font-weight:600; text-transform:uppercase;">Assigned Staff Tasks</div>
                <div style="font-size:20px; font-weight:800; color:#0F172A;"><?= count($staffTasks ?? []) ?> Active Tasks</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display:flex; gap:8px; border-bottom:2px solid #E2E8F0; margin-bottom:20px;">
        <button type="button" class="btn" id="btnTabTeaching" onclick="switchStaffTab('teaching')" style="background:#2563EB; color:#FFFFFF; font-weight:700; border-radius:6px 6px 0 0; padding:8px 18px; cursor:pointer;">👨‍🏫 Teaching Staff (<?= count($teachingStaff) ?>)</button>
        <button type="button" class="btn" id="btnTabNonTeaching" onclick="switchStaffTab('nonteaching')" style="background:#E2E8F0; color:#475569; font-weight:700; border-radius:6px 6px 0 0; padding:8px 18px; cursor:pointer;">🛠️ Non-Teaching Staff (<?= count($nonTeachingStaff) ?>)</button>
        <button type="button" class="btn" id="btnTabTasks" onclick="switchStaffTab('tasks')" style="background:#E2E8F0; color:#475569; font-weight:700; border-radius:6px 6px 0 0; padding:8px 18px; cursor:pointer;">📋 Staff Task Engine (<?= count($staffTasks ?? []) ?>)</button>
    </div>

    <!-- TAB 1: TEACHING STAFF FACULTY REGISTER -->
    <div id="tabTeaching" class="chart-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
            <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0;">Academic Teaching Staff Roster</h3>
            <input type="text" onkeyup="filterTable('teachingTable', this.value)" placeholder="Search teaching staff..." style="padding:6px 14px; border-radius:20px; border:1px solid #CBD5E1; font-size:12px; outline:none; width:220px;" />
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;" id="teachingTable">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #CBD5E1; color:#475569;">
                        <th style="padding:10px 12px;">EMP ID</th>
                        <th style="padding:10px 12px;">FACULTY NAME</th>
                        <th style="padding:10px 12px;">DESIGNATION</th>
                        <th style="padding:10px 12px;">DEPARTMENT</th>
                        <th style="padding:10px 12px;">PHONE NUMBER</th>
                        <th style="padding:10px 12px;">SALARY (₹)</th>
                        <th style="padding:10px 12px;">STATUS</th>
                        <th style="padding:10px 12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachingStaff)): ?>
                        <?php foreach ($teachingStaff as $st): ?>
                            <tr style="border-bottom:1px solid #E2E8F0; transition:background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:10px 12px; font-weight:700; color:#2563EB;"><code><?= htmlspecialchars($st['emp_id'] ?? 'EMP', ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                    <?= htmlspecialchars(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($st['staff_username'])): ?>
                                        <div style="font-size:11px; color:#64748B; font-weight:400;">@<?= htmlspecialchars($st['staff_username'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:10px 12px;"><span style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; background:#DBEAFE; color:#1E40AF;"><?= htmlspecialchars($st['designation'] ?? 'Teacher', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td style="padding:10px 12px; color:#334155;"><?= htmlspecialchars($st['department_name'] ?? 'Academic Dept', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                    <?= !empty($st['phone']) ? htmlspecialchars($st['phone'], ENT_QUOTES, 'UTF-8') : '<span style="color:#94A3B8;">—</span>' ?>
                                </td>
                                <td style="padding:10px 12px; font-weight:800; color:#15803D;">
                                    ₹<?= number_format((float)($st['salary'] ?? 0), 2) ?>
                                </td>
                                <td style="padding:10px 12px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534; text-transform:uppercase;">
                                        <?= htmlspecialchars($st['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:10px 12px; text-align:right;">
                                    <div style="display:flex; gap:6px; justify-content:flex-end;">
                                        <button type="button" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModalFromRecord(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>)">📄 Payslip PDF</button> <button type="button" class="btn btn-primary" style="font-size:11px; padding:3px 10px;" data-record="<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>" onclick="openStaffEditModal(this)">Edit</button>
                                        <form action="index.php?page=module_delete" method="POST" onsubmit="return confirm('Delete staff record #<?= $st['id'] ?>?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="module_key" value="staff_data">
                                            <input type="hidden" name="record_id" value="<?= $st['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; color:#DC2626; border-color:#FCA5A5;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding:30px; text-align:center; color:#64748B;">No teaching faculty staff records found. Click "+ Add New Staff Member" to add faculty.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: NON-TEACHING STAFF ROLES -->
    <div id="tabNonTeaching" class="chart-card" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
            <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0;">Non-Teaching Administrative & Operations Staff</h3>
            <input type="text" onkeyup="filterTable('nonTeachingTable', this.value)" placeholder="Search non-teaching staff..." style="padding:6px 14px; border-radius:20px; border:1px solid #CBD5E1; font-size:12px; outline:none; width:220px;" />
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;" id="nonTeachingTable">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid #CBD5E1; color:#475569;">
                        <th style="padding:10px 12px;">EMP ID</th>
                        <th style="padding:10px 12px;">EMPLOYEE NAME</th>
                        <th style="padding:10px 12px;">JOB ROLE / DESIGNATION</th>
                        <th style="padding:10px 12px;">DEPARTMENT</th>
                        <th style="padding:10px 12px;">PHONE NUMBER</th>
                        <th style="padding:10px 12px;">SALARY (₹)</th>
                        <th style="padding:10px 12px;">STATUS</th>
                        <th style="padding:10px 12px; text-align:right;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($nonTeachingStaff)): ?>
                        <?php foreach ($nonTeachingStaff as $st): ?>
                            <tr style="border-bottom:1px solid #E2E8F0; transition:background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:10px 12px; font-weight:700; color:#2563EB;"><code><?= htmlspecialchars($st['emp_id'] ?? 'EMP', ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                    <?= htmlspecialchars(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($st['staff_username'])): ?>
                                        <div style="font-size:11px; color:#64748B; font-weight:400;">@<?= htmlspecialchars($st['staff_username'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:10px 12px; font-weight:600; color:#0F172A;"><?= htmlspecialchars($st['designation'] ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding:10px 12px; color:#334155;"><?= htmlspecialchars($st['department_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                    <?= !empty($st['phone']) ? htmlspecialchars($st['phone'], ENT_QUOTES, 'UTF-8') : '<span style="color:#94A3B8;">—</span>' ?>
                                </td>
                                <td style="padding:10px 12px; font-weight:800; color:#15803D;">
                                    ₹<?= number_format((float)($st['salary'] ?? 0), 2) ?>
                                </td>
                                <td style="padding:10px 12px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534; text-transform:uppercase;">
                                        <?= htmlspecialchars($st['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding:10px 12px; text-align:right;">
                                    <div style="display:flex; gap:6px; justify-content:flex-end;">
                                        <button type="button" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModalFromRecord(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>)">📄 Payslip PDF</button> <button type="button" class="btn btn-primary" style="font-size:11px; padding:3px 10px;" data-record="<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>" onclick="openStaffEditModal(this)">Edit</button>
                                        <form action="index.php?page=module_delete" method="POST" onsubmit="return confirm('Delete staff record #<?= $st['id'] ?>?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="module_key" value="staff_data">
                                            <input type="hidden" name="record_id" value="<?= $st['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; color:#DC2626; border-color:#FCA5A5;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding:30px; text-align:center; color:#64748B;">No non-teaching staff records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: STAFF TASK ASSIGNMENT ENGINE -->
    <div id="tabTasks" class="chart-card" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0;">Staff Task Assignment & Tracking Engine</h3>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('assignTaskModal').classList.add('active')">+ Assign New Staff Task</button>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#0F172A; color:#FFFFFF;">
                        <th style="padding:10px 12px;">#TASK ID</th>
                        <th style="padding:10px 12px;">TASK TITLE & DESCRIPTION</th>
                        <th style="padding:10px 12px;">ASSIGNED TO</th>
                        <th style="padding:10px 12px;">PRIORITY</th>
                        <th style="padding:10px 12px;">START / DUE DATE</th>
                        <th style="padding:10px 12px;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($staffTasks)): ?>
                        <?php foreach ($staffTasks as $tsk): ?>
                            <tr style="border-bottom:1px solid #E2E8F0;">
                                <td style="padding:10px 12px; font-weight:700;">#TSK-<?= $tsk['id'] ?></td>
                                <td style="padding:10px 12px;">
                                    <div style="font-weight:700; color:#0F172A;"><?= htmlspecialchars($tsk['title'] ?? 'Task Title', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="font-size:12px; color:#64748B;"><?= htmlspecialchars($tsk['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding:10px 12px; font-weight:600;"><?= htmlspecialchars($tsk['assigned_to_name'] ?? 'Staff Member', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding:10px 12px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#FEE2E2; color:#991B1B; text-transform:uppercase;"><?= htmlspecialchars($tsk['priority'] ?? 'MEDIUM', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="padding:10px 12px; font-size:12px; color:#475569;"><?= $tsk['start_date'] ?? 'N/A' ?> to <?= $tsk['due_date'] ?? 'N/A' ?></td>
                                <td style="padding:10px 12px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534; text-transform:uppercase;"><?= htmlspecialchars($tsk['status'] ?? 'IN PROGRESS', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:10px 12px; font-weight:700;">#TSK-101</td>
                            <td style="padding:10px 12px;">
                                <div style="font-weight:700; color:#0F172A;">Submit Mid-Semester Marks Entry</div>
                                <div style="font-size:12px; color:#64748B;">Complete mid-semester evaluation marks entry for CS Section A</div>
                            </td>
                            <td style="padding:10px 12px; font-weight:600;">Faculty Member</td>
                            <td style="padding:10px 12px;"><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#FEE2E2; color:#991B1B;">HIGH</span></td>
                            <td style="padding:10px 12px; font-size:12px; color:#475569;">Active Cycle</td>
                            <td style="padding:10px 12px;"><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#FEF3C7; color:#92400E;">IN PROGRESS</span></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL 1: ADD NEW STAFF MEMBER -->
<div class="modal-overlay" id="addStaffModal">
    <div class="modal-card" style="max-width:620px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">+ Add New Employee / Faculty Member</h3>
            <button type="button" onclick="document.getElementById('addStaffModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=module_store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="module_key" value="staff_data">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">First Name *</label>
                        <input type="text" name="first_name" required placeholder="e.g. Rahul" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Last Name *</label>
                        <input type="text" name="last_name" required placeholder="e.g. Sharma" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Designation / Job Role *</label>
                        <select name="designation" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="HOD / Head of Department">HOD / Head of Department</option>
                            <option value="Teaching Staff / Teacher">Teaching Staff / Teacher</option>
                            <option value="Senior Lecturer / Professor">Senior Lecturer / Professor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Administrative Officer">Administrative Officer</option>
                            <option value="Accountant / Accounts Executive">Accountant / Accounts Executive</option>
                            <option value="HR Executive / HR Administrator">HR Executive / HR Administrator</option>
                            <option value="Librarian / Library Assistant">Librarian / Library Assistant</option>
                            <option value="Transport & Hostel Administrator">Transport & Hostel Administrator</option>
                            <option value="Inventory / Asset Manager">Inventory / Asset Manager</option>
                            <option value="Receptionist / Front Office Executive">Receptionist / Front Office Executive</option>
                            <option value="ERP Administrator">ERP Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Department *</label>
                        <select name="department_id" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">General Administration</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#2563EB;">📱 Phone Number *</label>
                        <input type="text" name="phone" required placeholder="e.g. 9876543210" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#15803D;">💵 Monthly Salary (₹) *</label>
                        <input type="number" step="0.01" name="salary" required placeholder="e.g. 45000.00" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Email Address</label>
                        <input type="email" name="email" placeholder="e.g. rahul@campus360.edu" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Joining Date</label>
                        <input type="date" name="joining_date" value="<?= date('Y-m-d') ?>" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:8px; padding-top:10px; border-top:1px dashed #CBD5E1;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔐 Staff User ID / Username</label>
                        <input type="text" name="username" placeholder="e.g. rahul_sharma" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔑 Password</label>
                        <input type="password" name="password" placeholder="e.g. StaffPass@123" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addStaffModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#059669;">Save Employee to Database</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EDIT STAFF MEMBER -->
<div class="modal-overlay" id="editStaffModal">
    <div class="modal-card" style="max-width:620px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">✏️ Edit Staff Member Record</h3>
            <button type="button" onclick="document.getElementById('editStaffModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=module_update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
            <input type="hidden" name="module_key" value="staff_data">
            <input type="hidden" name="record_id" id="editStaffRecordId" value="0">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">First Name *</label>
                        <input type="text" name="first_name" id="editFirstName" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Last Name *</label>
                        <input type="text" name="last_name" id="editLastName" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Designation / Job Role *</label>
                        <input type="text" name="designation" id="editDesignation" required class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Department</label>
                        <select name="department_id" id="editDepartmentId" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">General Administration</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#2563EB;">📱 Phone Number *</label>
                        <input type="text" name="phone" id="editPhone" required placeholder="e.g. 9876543210" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#15803D;">💵 Monthly Salary (₹) *</label>
                        <input type="number" step="0.01" name="salary" id="editSalary" required placeholder="e.g. 45000.00" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Email Address</label>
                        <input type="email" name="email" id="editEmail" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Status</label>
                        <select name="status" id="editStatus" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                            <option value="active">Active</option>
                            <option value="on_leave">On Leave</option>
                            <option value="resigned">Resigned / Inactive</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:8px; padding-top:10px; border-top:1px dashed #CBD5E1;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔐 Staff User ID / Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔑 Reset Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current" class="form-control" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #CBD5E1;">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editStaffModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Database Record</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: ASSIGN STAFF TASK -->
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
                        <?php foreach ($allStaff as $st): ?>
                            <option value="<?= $st['id'] ?>"><?= htmlspecialchars(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? '') . ' (' . ($st['designation'] ?? 'Staff') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
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

<script>
function switchStaffTab(tab) {
    document.getElementById('tabTeaching').style.display = tab === 'teaching' ? 'block' : 'none';
    document.getElementById('tabNonTeaching').style.display = tab === 'nonteaching' ? 'block' : 'none';
    document.getElementById('tabTasks').style.display = tab === 'tasks' ? 'block' : 'none';

    document.getElementById('btnTabTeaching').style.background = tab === 'teaching' ? '#2563EB' : '#E2E8F0';
    document.getElementById('btnTabTeaching').style.color = tab === 'teaching' ? '#FFFFFF' : '#475569';
    document.getElementById('btnTabNonTeaching').style.background = tab === 'nonteaching' ? '#2563EB' : '#E2E8F0';
    document.getElementById('btnTabNonTeaching').style.color = tab === 'nonteaching' ? '#FFFFFF' : '#475569';
    document.getElementById('btnTabTasks').style.background = tab === 'tasks' ? '#2563EB' : '#E2E8F0';
    document.getElementById('btnTabTasks').style.color = tab === 'tasks' ? '#FFFFFF' : '#475569';
}

function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const rows = table.getElementsByTagName('tr');
    const q = query.toLowerCase();

    for (let i = 1; i < rows.length; i++) {
        const text = rows[i].textContent.toLowerCase();
        rows[i].style.display = text.includes(q) ? '' : 'none';
    }
}

function openStaffEditModal(btn) {
    try {
        const data = JSON.parse(btn.getAttribute('data-record'));
        document.getElementById('editStaffRecordId').value = data.id;
        document.getElementById('editFirstName').value = data.first_name || '';
        document.getElementById('editLastName').value = data.last_name || '';
        document.getElementById('editDesignation').value = data.designation || '';
        document.getElementById('editPhone').value = data.phone || '';
        document.getElementById('editSalary').value = data.salary !== undefined ? data.salary : '0.00';
        document.getElementById('editEmail').value = data.email || '';
        document.getElementById('editStatus').value = data.status || 'active';
        document.getElementById('editUsername').value = data.staff_username || data.username || '';
        if (data.department_id) {
            document.getElementById('editDepartmentId').value = data.department_id;
        }

        document.getElementById('editStaffModal').classList.add('active');
    } catch(e) {
        console.error("Open Staff Edit Modal Error:", e);
    }
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
