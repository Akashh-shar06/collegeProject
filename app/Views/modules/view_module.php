<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<?php
$canManage = $canManage ?? \App\Core\Permission::canManage($moduleKey);
$accessLevel = $accessLevel ?? \App\Core\Permission::getAccessLevel($moduleKey);
?>

<main class="dashboard-viewport">
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; background:#FFFFFF; padding:18px 24px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(15,23,42,0.06);">
        <div style="display:flex; flex-direction:column; gap:6px; min-width:0;">
            <a href="index.php?page=dashboard" style="text-decoration:none; color:var(--primary); font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">← Back to Dashboard</a>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:2px;">
                <span style="font-size:22px; display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; background:#F1F5F9; border-radius:8px; flex-shrink:0;"><?= $config['icon'] ?></span>
                <h1 style="font-size:22px; font-weight:800; color:var(--text-main); margin:0; line-height:1.2; white-space:nowrap;"><?= htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <span class="dept-badge" style="font-size:11px; font-weight:700; text-transform:uppercase; background:#E2E8F0; color:#334155; padding:4px 10px; border-radius:6px; white-space:nowrap;"><?= htmlspecialchars($config['category'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!$canManage): ?>
                    <span style="background:#FEF3C7; color:#92400E; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; white-space:nowrap;">👁️ Read-Only View</span>
                <?php else: ?>
                    <span style="background:#DCFCE7; color:#166534; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; white-space:nowrap;">✓ Full Manage Access</span>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($canManage): ?>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('createRecordModal').classList.add('active')" style="white-space:nowrap; flex-shrink:0; padding:10px 20px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer;">+ Add New Record</button>
        <?php endif; ?>
    </div>

    <?php if ($moduleKey === 'admission'): ?>
        <!-- Administrative Officer — New Student Admission Portal Form -->
        <div class="chart-card" style="margin-bottom:24px; background:linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%); border:1px solid #CBD5E1; border-radius:16px; padding:24px; box-shadow:0 4px 20px rgba(15,23,42,0.06);">
            <div class="chart-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #E2E8F0; padding-bottom:14px; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="font-size:18px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        🎓 Administrative Officer — New Student Admission Portal
                    </h3>
                    <span style="font-size:12px; color:#64748B;">Complete student enrollment, profile details, parent contacts, photo upload, and account provisioning</span>
                </div>
                <div>
                    <span style="font-size:12px; font-weight:700; background:#EEF2FF; color:#4F46E5; padding:6px 14px; border-radius:20px; border:1px solid #C7D2FE;">
                        📋 Official Admission Form
                    </span>
                </div>
            </div>

            <form action="index.php?page=admit_student" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                
                <div style="display:grid; grid-template-columns: 240px 1fr; gap:24px; align-items:start;">
                    
                    <!-- PHOTO UPLOAD CONTAINER WITH LIVE PREVIEW -->
                    <div style="background:#FFFFFF; border:1px dashed #6366F1; border-radius:14px; padding:20px; text-align:center; box-shadow:0 2px 8px rgba(99,102,241,0.05);">
                        <div style="font-size:12px; font-weight:800; color:#1E293B; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px;">📸 Student Photo *</div>
                        <div style="width:120px; height:120px; margin:0 auto 12px auto; border-radius:50%; background:#F1F5F9; border:3px solid #6366F1; overflow:hidden; display:flex; align-items:center; justify-content:center; position:relative; box-shadow:0 4px 10px rgba(0,0,0,0.08);">
                            <img id="admPhotoPreview" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' fill='%2364748B' viewBox='0 0 16 16'><path d='M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z'/></svg>" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <input type="file" name="photo" id="admPhotoInput" accept="image/*" style="display:none;" onchange="previewStudentPhoto(this)">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('admPhotoInput').click()" style="font-size:12px; font-weight:700; border-color:#6366F1; color:#4338CA; width:100%; border-radius:8px; padding:8px 12px; background:#EEF2FF; cursor:pointer;">
                            📁 Choose Photo File
                        </button>
                        <span style="display:block; font-size:10px; color:#94A3B8; margin-top:8px;">Supports JPG, PNG, WEBP (Max 5MB)</span>
                    </div>

                    <!-- FORM FIELDS GRID -->
                    <div>
                        <!-- SECTION 1: PERSONAL & LOGIN DETAILS -->
                        <div style="font-size:13px; font-weight:800; color:#4F46E5; margin-bottom:10px; padding-bottom:4px; border-bottom:1px solid #E0E7FF; display:flex; align-items:center; gap:6px;">
                            <span>👤 1. Personal & Login Information</span>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">First Name *</label>
                                <input type="text" name="first_name" required placeholder="e.g. Rahul" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Last Name *</label>
                                <input type="text" name="last_name" required placeholder="e.g. Sharma" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student DOB *</label>
                                <input type="date" name="dob" required value="2005-01-15" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:18px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student ID / Admission No *</label>
                                <input type="text" name="student_id" required placeholder="e.g. STU2024001" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:700; color:#0F172A;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Password (Login Access) *</label>
                                <input type="password" name="password" required placeholder="Set Password" value="Student@123" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Gender *</label>
                                <select name="gender" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- SECTION 2: CONTACT & PARENT MOBILES -->
                        <div style="font-size:13px; font-weight:800; color:#059669; margin-bottom:10px; padding-bottom:4px; border-bottom:1px solid #D1FAE5; display:flex; align-items:center; gap:6px;">
                            <span>📞 2. Contact & Family Phone Numbers</span>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px; margin-bottom:18px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Email Address *</label>
                                <input type="email" name="email" required placeholder="rahul@example.com" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student Phone Number *</label>
                                <input type="tel" name="phone" required placeholder="+91 9876543210" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Father Mobile no. *</label>
                                <input type="tel" name="guardian_phone" required placeholder="+91 9876500001" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Mother Mobile no. *</label>
                                <input type="tel" name="mother_phone" required placeholder="+91 9876500002" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                            </div>
                        </div>

                        <!-- SECTION 3: ACADEMIC DEPARTMENT, YEAR & CLASS -->
                        <div style="font-size:13px; font-weight:800; color:#D97706; margin-bottom:10px; padding-bottom:4px; border-bottom:1px solid #FEF3C7; display:flex; align-items:center; gap:6px;">
                            <span>🏢 3. Student Department, Year & Class Allocation</span>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student Department *</label>
                                <select name="department_id" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                                    <option value="">-- Select Department --</option>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name'] . ' (' . ($dept['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="1">B.Sc. Computer Science</option>
                                        <option value="2">B.Sc. IT</option>
                                        <option value="3">B.Com</option>
                                        <option value="4">BCA</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student Year *</label>
                                <select name="standard_id" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                                    <option value="1">1st Year (FY)</option>
                                    <option value="2">2nd Year (SY)</option>
                                    <option value="3">3rd Year (TY)</option>
                                    <option value="4">4th Year (Final Year)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#1E293B; margin-bottom:4px;">Student Class *</label>
                                <select name="class_id" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; background:#FFF;">
                                    <option value="">-- Select Class --</option>
                                    <?php if (!empty($classesList)): ?>
                                        <?php foreach ($classesList as $cls): ?>
                                            <option value="<?= $cls['id'] ?>"><?= htmlspecialchars($cls['name'] . ($cls['code'] ? ' (' . $cls['code'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="1">Class A</option>
                                        <option value="2">Class B</option>
                                        <option value="3">Class C</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div style="display:flex; justify-content:flex-end; gap:12px; align-items:center;">
                            <button type="reset" class="btn btn-secondary" style="padding:10px 20px; font-weight:700; font-size:13px; border-radius:8px;">Reset Form</button>
                            <button type="submit" class="btn btn-primary" style="padding:10px 28px; font-weight:800; font-size:13px; border-radius:8px; background:linear-gradient(135deg, #4F46E5 0%, #3730A3 100%); border:none; box-shadow:0 4px 12px rgba(79,70,229,0.25); cursor:pointer;">
                                ✨ Complete Admission & Provision Account
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
        function previewStudentPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('admPhotoPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>
    <?php endif; ?>

       <?php if ($moduleKey === 'role_permission'): ?>
        <!-- Interactive Section 13 RBAC Role Management Table -->
        <div class="chart-card" style="margin-bottom:24px;">
            <div class="chart-header">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#1E293B;">🛡️ Enterprise Role-Based Access Control (RBAC) Role Management</h3>
                    <span style="font-size:12px; color:var(--text-muted);">Manage 12 job-based system roles, module assignments, permission levels, and access restrictions</span>
                </div>
                <button type="button" class="btn btn-primary" style="font-size:12px; padding:6px 14px;" onclick="alert('Role permissions configured & synchronized with database matrix.')">Synchronize Permissions</button>
            </div>
            <div style="overflow-x:auto; margin-top:14px;">
                <?php
                $roleManagementTable = [
                    ['role' => 'Principal', 'type' => 'Administration', 'modules' => 'All (36)', 'status' => 'Active'],
                    ['role' => 'Teaching Staff', 'type' => 'Academic', 'modules' => '8 Modules', 'status' => 'Active'],
                    ['role' => 'Administrative Officer', 'type' => 'Non-Teaching', 'modules' => 'Assigned (11)', 'status' => 'Active'],
                    ['role' => 'Accountant', 'type' => 'Non-Teaching', 'modules' => 'Assigned (6)', 'status' => 'Active'],
                    ['role' => 'HR Executive', 'type' => 'Non-Teaching', 'modules' => 'Assigned (10)', 'status' => 'Active'],
                    ['role' => 'Librarian', 'type' => 'Non-Teaching', 'modules' => 'Assigned (7)', 'status' => 'Active'],
                    ['role' => 'Transport & Hostel Admin', 'type' => 'Non-Teaching', 'modules' => 'Assigned (10)', 'status' => 'Active'],
                    ['role' => 'Inventory Manager', 'type' => 'Non-Teaching', 'modules' => 'Assigned (8)', 'status' => 'Active'],
                    ['role' => 'Receptionist', 'type' => 'Non-Teaching', 'modules' => 'Assigned (8)', 'status' => 'Active'],
                    ['role' => 'ERP Administrator', 'type' => 'Non-Teaching', 'modules' => 'Assigned (7)', 'status' => 'Active'],
                    ['role' => 'Student', 'type' => 'Student', 'modules' => 'Assigned (23)', 'status' => 'Active'],
                    ['role' => 'Parent', 'type' => 'Parent', 'modules' => 'Assigned (22)', 'status' => 'Active'],
                ];
                ?>
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#1E293B; color:#FFFFFF;">
                            <th style="padding:10px 14px;">Role</th>
                            <th style="padding:10px 14px;">User Type</th>
                            <th style="padding:10px 14px;">Modules</th>
                            <th style="padding:10px 14px;">Status</th>
                            <th style="padding:10px 14px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roleManagementTable as $idx => $r): ?>
                            <tr style="border-bottom:1px solid #E2E8F0; background: <?= $idx % 2 === 0 ? '#F8FAFC' : '#FFFFFF' ?>;">
                                <td style="padding:10px 14px; font-weight:700; color:#0F172A;"><?= htmlspecialchars($r['role']) ?></td>
                                <td style="padding:10px 14px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#E2E8F0; color:#334155;"><?= htmlspecialchars($r['type']) ?></span>
                                </td>
                                <td style="padding:10px 14px; font-weight:600; color:#2563EB;"><?= htmlspecialchars($r['modules']) ?></td>
                                <td style="padding:10px 14px;">
                                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:#DCFCE7; color:#166534;"><?= htmlspecialchars($r['status']) ?></span>
                                </td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <button type="button" class="btn btn-secondary" style="font-size:11px; padding:3px 10px;" onclick="alert('Editing permissions for role: <?= htmlspecialchars($r['role'], ENT_QUOTES) ?>')">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($moduleKey === 'student_mis' || $moduleKey === 'id_cards'): ?>
        <?php 
        $defaultStudentCard = !empty($records) ? $records[0] : [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'admission_no' => 'ADM-2024-001',
            'roll_no' => '24CSE001',
            'gender' => 'male',
            'dob' => '2004-05-14',
            'phone' => '+1 555-0101',
            'email' => 'john@campus360.edu',
            'guardian_name' => 'Robert Doe',
            'department_name' => 'Computer Science & Engineering',
            'course_name' => 'B.Tech CSE',
            'status' => 'active'
        ];
        ?>

        <!-- Embedded Modern Styles for ID Card Filter Row & Slider -->
        <style>
            .id-card-filter-row {
                display: grid;
                grid-template-columns: 1fr 1fr 1.35fr;
                gap: 16px;
                background: #FFFFFF;
                border: 1px solid #E2E8F0;
                border-radius: 14px;
                padding: 16px 20px;
                margin-bottom: 22px;
                box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            }
            @media (max-width: 820px) {
                .id-card-filter-row {
                    grid-template-columns: 1fr;
                    gap: 14px;
                }
            }
            .filter-box-col {
                display: flex;
                flex-direction: column;
                position: relative;
            }
            .filter-input-control {
                width: 100%;
                padding: 10px 14px;
                border-radius: 10px;
                border: 1px solid #CBD5E1;
                background: #FFFFFF;
                font-size: 13px;
                font-weight: 600;
                color: #0F172A;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
                box-sizing: border-box;
            }
            .filter-input-control:focus {
                border-color: #6366F1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            }
            .filter-input-control:disabled {
                background: #F8FAFC;
                color: #94A3B8;
                cursor: not-allowed;
                border-color: #E2E8F0;
            }
            .dept-option-item {
                padding: 9px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                color: #334155;
                cursor: pointer;
                transition: background 0.15s, color 0.15s;
                user-select: none;
            }
            .dept-option-item:hover {
                background: #EEF2FF;
                color: #4338CA;
            }
            .dept-option-item.selected {
                background: #4338CA;
                color: #FFFFFF;
            }
            .slider-arrow-btn {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: #FFFFFF;
                border: 2px solid #CBD5E1;
                color: #1E1B4B;
                font-size: 18px;
                font-weight: 800;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1);
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                flex-shrink: 0;
                user-select: none;
            }
            .slider-arrow-btn:hover:not(:disabled) {
                background: #4F46E5;
                color: #FFFFFF;
                border-color: #4F46E5;
                box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
                transform: scale(1.06);
            }
            .slider-arrow-btn:active:not(:disabled) {
                transform: scale(0.96);
            }
            .slider-arrow-btn:disabled {
                opacity: 0.35;
                cursor: not-allowed;
                background: #F1F5F9;
                color: #94A3B8;
                border-color: #E2E8F0;
                box-shadow: none;
                transform: none;
            }
            .slider-pagination-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #CBD5E1;
                cursor: pointer;
                transition: all 0.25s ease;
                border: none;
                padding: 0;
            }
            .slider-pagination-dot.active {
                width: 26px;
                border-radius: 12px;
                background: #4F46E5;
                box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4);
            }
            @media (max-width: 600px) {
                .slider-arrow-btn {
                    width: 38px;
                    height: 38px;
                    font-size: 16px;
                }
            }
        </style>

        <!-- Official Student Digital ID Card Component -->
        <div class="chart-card" style="margin-bottom:24px; background:linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%); border:1px solid #E2E8F0; border-radius:16px; padding:24px; overflow:hidden;">
            
            <!-- Header Section (Preserved Title, Subtitle, Existing logged-in profile/selector, Print Button) -->
            <div class="chart-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E2E8F0; padding-bottom:16px; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="font-size:18px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        🪪 Official Student Digital Identification Card (MIS Profile)
                    </h3>
                    <span style="font-size:12px; color:var(--text-muted);">Verified multi-tenant student credential record & digital security barcode</span>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <?php if (count($records) > 1): ?>
                        <select id="idCardStudentSelector" onchange="onHeaderStudentSelectorChange(this.value)" style="padding:7px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:12px; font-weight:600; outline:none; background:#FFF; color:#0F172A; cursor:pointer;">
                            <?php foreach ($records as $idx => $stRec): ?>
                                <?php 
                                $stDisplayName = trim(($stRec['first_name'] ?? '') . ' ' . ($stRec['last_name'] ?? ''));
                                if (empty($stDisplayName) && !empty($stRec['full_name'])) {
                                    $stDisplayName = $stRec['full_name'];
                                }
                                if (empty($stDisplayName)) {
                                    $stDisplayName = 'Student #' . ($stRec['id'] ?? $idx + 1);
                                }
                                $stCode = !empty($stRec['admission_no']) ? $stRec['admission_no'] : (!empty($stRec['roll_no']) ? $stRec['roll_no'] : (!empty($stRec['student_id']) ? $stRec['student_id'] : 'N/A'));
                                ?>
                                <option value="<?= $idx ?>"><?= htmlspecialchars($stDisplayName . ' (' . $stCode . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary" onclick="printStudentIdCard()" style="font-size:12px; font-weight:700; padding:8px 18px; display:inline-flex; align-items:center; gap:6px; cursor:pointer; border-radius:8px; box-shadow:0 2px 6px rgba(79, 70, 229, 0.25);">
                        🖨️ Print Digital ID Card
                    </button>
                </div>
            </div>

            <!-- THREE SELECTION BOXES (Department, Academic Year, Student) IN ONE RESPONSIVE ROW -->
            <div class="id-card-filter-row">
                
                <!-- Box 1: Department Selection (Searchable Dropdown) -->
                <div class="filter-box-col">
                    <label for="deptSearchTrigger" style="display:block; font-size:12px; font-weight:800; color:#1E293B; margin-bottom:6px;">
                        🏢 Select Department <span style="color:#DC2626;">*</span>
                    </label>
                    <div id="deptDropdownContainer" style="position:relative;">
                        <button type="button" id="deptSearchTrigger" onclick="toggleDeptDropdown()" class="filter-input-control" style="display:flex; justify-content:space-between; align-items:center; cursor:pointer; text-align:left;">
                            <span id="deptSelectedText" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">-- Choose Department --</span>
                            <span style="font-size:10px; color:#64748B; margin-left:8px;">▼</span>
                        </button>
                        <input type="hidden" id="filterDepartmentSelect" value="">

                        <!-- Searchable Dropdown Popup -->
                        <div id="deptOptionsPanel" style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:100; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 12px 28px -4px rgba(15,23,42,0.18); padding:8px; max-height:260px; overflow-y:auto;">
                            <div style="position:relative; margin-bottom:8px;">
                                <input type="text" id="deptSearchInput" oninput="filterDeptOptions(this.value)" placeholder="Search department..." style="width:100%; padding:7px 10px 7px 28px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px; outline:none; box-sizing:border-box;">
                                <span style="position:absolute; left:8px; top:7px; font-size:12px; color:#94A3B8; pointer-events:none;">🔍</span>
                            </div>
                            <div id="deptOptionsList" style="display:flex; flex-direction:column; gap:2px;">
                                <?php foreach ($departments as $dept): ?>
                                    <?php 
                                    $deptLabel = $dept['name'] . (!empty($dept['code']) && $dept['code'] !== $dept['name'] ? ' (' . $dept['code'] . ')' : '');
                                    ?>
                                    <div class="dept-option-item" onclick="selectDepartment('<?= $dept['id'] ?>', '<?= htmlspecialchars(addslashes($deptLabel)) ?>')" data-name="<?= htmlspecialchars(strtolower($dept['name'] . ' ' . ($dept['code'] ?? ''))) ?>">
                                        <?= htmlspecialchars($deptLabel) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="deptNoResultsMsg" style="display:none; padding:10px; font-size:12px; color:#94A3B8; text-align:center;">
                                No departments matching search
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box 2: Academic Year Selection -->
                <div class="filter-box-col">
                    <label for="filterAcademicYearSelect" style="display:block; font-size:12px; font-weight:800; color:#1E293B; margin-bottom:6px;">
                        📅 Select Academic Year <span style="color:#DC2626;">*</span>
                    </label>
                    <select id="filterAcademicYearSelect" onchange="onAcademicYearChange(this.value)" class="filter-input-control" style="cursor:pointer;">
                        <option value="">-- Choose Academic Year --</option>
                        <option value="FY">FY — First Year</option>
                        <option value="SY">SY — Second Year</option>
                        <option value="TY">TY — Third Year</option>
                    </select>
                </div>

                <!-- Box 3: Student Selection (Dynamically Populated) -->
                <div class="filter-box-col">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label for="filterStudentSelect" style="font-size:12px; font-weight:800; color:#1E293B; margin:0;">
                            🎓 Select Student <span style="color:#DC2626;">*</span>
                        </label>
                        <span id="studentLoadingSpinner" style="display:none; font-size:11px; font-weight:700; color:#4F46E5;">
                            ⏳ Loading students...
                        </span>
                    </div>
                    <select id="filterStudentSelect" onchange="onStudentDropdownChange(this.value)" class="filter-input-control" disabled style="cursor:not-allowed;">
                        <option value="">-- Select Department & Year First --</option>
                    </select>
                </div>

            </div>

            <!-- DEDICATED STUDENT DIGITAL ID CARD DISPLAY AREA (HORIZONTAL SLIDER) -->
            <div id="idCardDisplayContainer" style="position:relative; width:100%;">
                
                <!-- Slider Status Bar: Counter & Filter Breadcrumb -->
                <div id="sliderTopBar" style="display:none; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px; width:100%; max-width:560px; margin-left:auto; margin-right:auto;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="sliderStudentCountBadge" style="background:#EEF2FF; color:#4338CA; border:1px solid #C7D2FE; font-size:12px; font-weight:800; padding:4px 12px; border-radius:20px;">
                            Student 1 of 1
                        </span>
                        <span id="sliderFilterBreadcrumb" style="font-size:12px; font-weight:600; color:#64748B;"></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:11px; color:#94A3B8; font-weight:600;">Swipe or use ◀ ▶ arrows</span>
                    </div>
                </div>

                <!-- Slider Stage: Left Arrow + Viewport + Right Arrow -->
                <div id="sliderStageWrapper" style="display:none; justify-content:center; align-items:center; gap:14px; position:relative; width:100%;">
                    
                    <!-- Left Arrow Button -->
                    <button type="button" id="sliderPrevBtn" onclick="slidePrevStudent()" aria-label="Previous Student ID Card" class="slider-arrow-btn">
                        ❮
                    </button>

                    <!-- Viewport: Clips horizontal track -->
                    <div id="idCardSliderViewport" style="width:100%; max-width:480px; overflow:hidden; border-radius:20px; user-select:none; cursor:grab; position:relative; min-height:460px;">
                        <div id="idCardSliderTrack" style="display:flex; transition:transform 0.38s cubic-bezier(0.22, 1, 0.36, 1); will-change:transform; width:100%;">
                            <!-- Dynamically loaded student ID cards slide here -->
                        </div>
                    </div>

                    <!-- Right Arrow Button -->
                    <button type="button" id="sliderNextBtn" onclick="slideNextStudent()" aria-label="Next Student ID Card" class="slider-arrow-btn">
                        ❯
                    </button>
                </div>

                <!-- Pagination Dots Container -->
                <div id="sliderDotsWrapper" style="display:none; justify-content:center; align-items:center; gap:6px; margin-top:16px; flex-wrap:wrap; max-width:480px; margin-left:auto; margin-right:auto;">
                </div>

                <!-- Initial Prompt State (before filters are selected) -->
                <div id="sliderInitialPrompt" style="text-align:center; padding:46px 20px; background:#F8FAFC; border:2px dashed #CBD5E1; border-radius:18px; max-width:480px; margin:10px auto;">
                    <div style="font-size:44px; margin-bottom:12px;">🪪</div>
                    <h4 style="font-size:16px; font-weight:800; color:#1E293B; margin:0 0 6px 0;">Select Department & Academic Year</h4>
                    <p style="font-size:13px; color:#64748B; margin:0; line-height:1.5;">
                        Choose a department and an academic year (FY, SY, TY) from the selection boxes above to browse and slide through official verified student digital ID cards.
                    </p>
                </div>

                <!-- Empty State (when 0 matching students exist) -->
                <div id="sliderEmptyState" style="display:none; text-align:center; padding:46px 20px; background:#FFFBEB; border:2px dashed #FCD34D; border-radius:18px; max-width:480px; margin:10px auto;">
                    <div style="font-size:44px; margin-bottom:12px;">🔍</div>
                    <h4 style="font-size:16px; font-weight:800; color:#92400E; margin:0 0 6px 0;">No Students Found</h4>
                    <p id="sliderEmptyMessage" style="font-size:13px; color:#B45309; margin:0; line-height:1.5;">
                        No students found for the selected department and academic year.
                    </p>
                </div>

            </div>
        </div>

        <script>
        // State variables
        let currentFilteredStudents = [];
        let currentSliderIndex = 0;
        let selectedDeptId = '';
        let selectedDeptName = '';
        let selectedAcademicYear = '';

        // Touch & Drag Tracking
        let touchStartX = 0;
        let touchStartY = 0;
        let mouseStartX = 0;
        let isMouseDown = false;

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatCardDate(dobStr) {
            if (!dobStr) return '14 May 2004';
            try {
                const d = new Date(dobStr);
                if (isNaN(d.getTime())) return dobStr;
                return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch (e) {
                return dobStr;
            }
        }

        function renderCardPhotoHtml(student) {
            const photo = student.photo || '';
            const gender = (student.gender || 'male').toLowerCase();
            const avatarEmoji = (gender === 'female') ? '👩‍🎓' : '👨‍🎓';

            if (photo) {
                if (photo.toLowerCase().endsWith('.pdf')) {
                    return `
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:6px; text-align:center;">
                            <span style="font-size:32px;">📄</span>
                            <span style="font-size:9px; font-weight:800; color:#DC2626; margin-top:2px;">PDF PHOTO</span>
                            <a href="${escapeHtml(photo)}" target="_blank" style="margin-top:4px; font-size:9px; font-weight:700; color:#FFF; background:#DC2626; padding:3px 8px; border-radius:4px; text-decoration:none;">Open PDF</a>
                        </div>
                    `;
                } else {
                    return `<img src="${escapeHtml(photo)}" alt="Student Photo" style="width:100%; height:100%; object-fit:cover;">`;
                }
            } else {
                return `<span style="font-size:48px;">${avatarEmoji}</span>`;
            }
        }

        function renderStudentCardMarkup(student, idx) {
            const fullName = (student.first_name || '') + ' ' + (student.last_name || '');
            const displayName = fullName.trim() || student.full_name || 'Student Record';
            const admNo = student.admission_no || student.student_id || 'N/A';
            const rollNo = student.roll_no || 'N/A';
            let deptStr = student.department_name || student.department || 'General';
            if (student.course_name) {
                deptStr += ' (' + student.course_name + ')';
            }
            const gender = (student.gender || 'male').toLowerCase();
            const genderText = gender.charAt(0).toUpperCase() + gender.slice(1);
            const dobText = formatCardDate(student.dob);
            const studentYear = student.student_year || 'First Year (FY)';
            const phoneText = student.phone || student.student_phone || 'N/A';
            const statusText = (student.status || 'ACTIVE').toUpperCase();
            const institutionName = (student.institution_name || 'CAMPUS360 ENTERPRISE ERP').toUpperCase();

            return `
                <div class="student-id-slide" data-index="${idx}" style="min-width:100%; width:100%; flex-shrink:0; display:flex; justify-content:center; align-items:center; box-sizing:border-box; padding:4px;">
                    <div id="printableStudentIdCard_${idx}" class="printable-student-card" style="width:100%; max-width:480px; background:#FFFFFF; border-radius:18px; box-shadow:0 20px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08); border:2px solid #E2E8F0; overflow:hidden; position:relative; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                        
                        <!-- Banner Header -->
                        <div style="background:linear-gradient(135deg, #1E1B4B 0%, #4338CA 100%); color:#FFFFFF; padding:18px 20px; text-align:center; position:relative;">
                            <div style="position:absolute; top:12px; right:14px; background:linear-gradient(135deg, #F59E0B, #D97706); color:#FFF; font-size:9px; font-weight:800; padding:3px 8px; border-radius:12px; letter-spacing:0.5px; box-shadow:0 2px 4px rgba(0,0,0,0.2);">
                                VERIFIED
                            </div>
                            <div style="font-size:16px; font-weight:900; letter-spacing:0.8px; text-transform:uppercase;">${escapeHtml(institutionName)}</div>
                            <div style="font-size:10px; opacity:0.85; font-weight:600; letter-spacing:1px; margin-top:2px; text-transform:uppercase;">Official Student Identification Card</div>
                        </div>

                        <!-- Card Body -->
                        <div style="padding:20px; position:relative; background:#FFFFFF;">
                            <!-- Watermark -->
                            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); opacity:0.03; font-size:110px; font-weight:900; pointer-events:none; user-select:none; color:#1E1B4B;">
                                C360
                            </div>

                            <div style="display:flex; gap:18px; align-items:flex-start; position:relative; z-index:2;">
                                <!-- Photo Avatar Container -->
                                <div style="display:flex; flex-direction:column; align-items:center; gap:8px; flex-shrink:0;">
                                    <div style="width:100px; height:120px; border-radius:12px; background:linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); border:2px solid #6366F1; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); overflow:hidden; position:relative;">
                                        ${renderCardPhotoHtml(student)}
                                    </div>
                                    <span style="background:#DCFCE7; color:#15803D; font-size:10px; font-weight:800; padding:2px 8px; border-radius:10px; text-transform:uppercase; letter-spacing:0.5px;">
                                        ${escapeHtml(statusText)}
                                    </span>
                                </div>

                                <!-- Details Column -->
                                <div style="flex:1; display:flex; flex-direction:column; gap:6px; font-size:12px;">
                                    <div>
                                        <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Student Full Name</div>
                                        <div style="font-size:17px; font-weight:800; color:#0F172A; line-height:1.2;">
                                            ${escapeHtml(displayName)}
                                        </div>
                                    </div>

                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:2px;">
                                        <div>
                                            <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Admission No.</div>
                                            <div style="font-weight:800; color:#4338CA; font-family:monospace; font-size:12px;">
                                                ${escapeHtml(admNo)}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Roll Number</div>
                                            <div style="font-weight:800; color:#0F172A; font-family:monospace; font-size:12px;">
                                                ${escapeHtml(rollNo)}
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top:2px;">
                                        <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Department & Course</div>
                                        <div style="font-weight:700; color:#334155; font-size:11px; line-height:1.2;">
                                            ${escapeHtml(deptStr)}
                                        </div>
                                    </div>

                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:2px;">
                                        <div>
                                            <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Gender / DOB</div>
                                            <div style="font-weight:600; color:#475569; font-size:11px;">
                                                ${escapeHtml(genderText)} | ${escapeHtml(dobText)}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Academic Year</div>
                                            <div style="font-weight:700; color:#4338CA; font-size:11px;">
                                                ${escapeHtml(studentYear)}
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top:2px;">
                                        <div style="font-size:9px; font-weight:700; color:#64748B; text-transform:uppercase;">Contact Phone</div>
                                        <div style="font-weight:600; color:#475569; font-size:11px;">
                                            ${escapeHtml(phoneText)}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Barcode & Signature Line -->
                            <div style="margin-top:16px; padding-top:12px; border-top:1px dashed #E2E8F0; display:flex; justify-content:space-between; align-items:center; position:relative; z-index:2;">
                                <div style="display:flex; flex-direction:column; align-items:flex-start;">
                                    <svg width="160" height="32" viewBox="0 0 160 32" fill="none">
                                        <rect width="160" height="32" fill="#F8FAFC" rx="4"/>
                                        <path d="M10 4v24M14 4v24M16 4v24M22 4v24M26 4v24M32 4v24M36 4v24M40 4v24M46 4v24M52 4v24M56 4v24M60 4v24M66 4v24M72 4v24M78 4v24M82 4v24M88 4v24M94 4v24M98 4v24M104 4v24M110 4v24M114 4v24M120 4v24M126 4v24M130 4v24M136 4v24M142 4v24M146 4v24M150 4v24" stroke="#0F172A" stroke-width="2"/>
                                    </svg>
                                    <span style="font-size:9px; font-family:monospace; color:#64748B; margin-top:2px;">
                                        *${escapeHtml(admNo)}*
                                    </span>
                                </div>

                                <div style="text-align:right;">
                                    <div style="font-size:10px; font-family:cursive; color:#1E1B4B; font-weight:700; transform:rotate(-3deg); margin-bottom:2px;">
                                        Prof. Arthur Pendelton
                                    </div>
                                    <div style="font-size:8px; font-weight:800; color:#64748B; text-transform:uppercase; border-top:1px solid #CBD5E1; padding-top:2px; display:inline-block;">
                                        Registrar Signature
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Toggle Department Dropdown Panel
        function toggleDeptDropdown() {
            const panel = document.getElementById('deptOptionsPanel');
            if (!panel) return;
            const isOpen = panel.style.display === 'block';
            panel.style.display = isOpen ? 'none' : 'block';
            if (!isOpen) {
                const searchInput = document.getElementById('deptSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                    filterDeptOptions('');
                    setTimeout(() => searchInput.focus(), 50);
                }
            }
        }

        // Filter Department Dropdown Options
        function filterDeptOptions(term) {
            term = (term || '').toLowerCase().trim();
            const items = document.querySelectorAll('.dept-option-item');
            let visibleCount = 0;
            items.forEach(item => {
                const name = (item.getAttribute('data-name') || '').toLowerCase();
                if (!term || name.includes(term)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            const noResults = document.getElementById('deptNoResultsMsg');
            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        // Select Department Option
        function selectDepartment(deptId, deptName) {
            selectedDeptId = deptId;
            selectedDeptName = deptName;
            document.getElementById('filterDepartmentSelect').value = deptId;
            document.getElementById('deptSelectedText').textContent = deptName;
            document.getElementById('deptSelectedText').title = deptName;

            // Highlight selected in list
            document.querySelectorAll('.dept-option-item').forEach(item => {
                item.classList.remove('selected');
            });
            event?.currentTarget?.classList?.add('selected');

            // Close panel
            document.getElementById('deptOptionsPanel').style.display = 'none';

            // Trigger workflow update
            onFilterChange();
        }

        // Close Dept Dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('deptDropdownContainer');
            if (container && !container.contains(e.target)) {
                const panel = document.getElementById('deptOptionsPanel');
                if (panel) panel.style.display = 'none';
            }
        });

        // Academic Year Change
        function onAcademicYearChange(yearCode) {
            selectedAcademicYear = yearCode;
            onFilterChange();
        }

        // Handle Department or Academic Year Filter Change
        function onFilterChange() {
            const deptId = document.getElementById('filterDepartmentSelect').value;
            const yearCode = document.getElementById('filterAcademicYearSelect').value;
            const studentSelect = document.getElementById('filterStudentSelect');
            const spinner = document.getElementById('studentLoadingSpinner');

            // Reset current state
            currentFilteredStudents = [];
            currentSliderIndex = 0;

            // Initially disable student dropdown and clear options
            studentSelect.innerHTML = '';
            studentSelect.disabled = true;
            studentSelect.style.cursor = 'not-allowed';

            // If either Department or Academic Year is missing:
            if (!deptId || !yearCode) {
                let hint = '-- Select Department & Year First --';
                if (deptId && !yearCode) hint = '-- Choose Academic Year Next --';
                if (!deptId && yearCode) hint = '-- Choose Department Next --';
                
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = hint;
                studentSelect.appendChild(opt);

                // Show initial guidance prompt
                document.getElementById('sliderStageWrapper').style.display = 'none';
                document.getElementById('sliderDotsWrapper').style.display = 'none';
                document.getElementById('sliderTopBar').style.display = 'none';
                document.getElementById('sliderEmptyState').style.display = 'none';
                document.getElementById('sliderInitialPrompt').style.display = 'block';

                // Sync top header selector if present
                const headerSel = document.getElementById('idCardStudentSelector');
                if (headerSel) {
                    headerSel.innerHTML = '<option value="">-- No student selected --</option>';
                    headerSel.disabled = true;
                }
                return;
            }

            // Both selected: Show loading indicator
            spinner.style.display = 'inline-block';
            const loadingOpt = document.createElement('option');
            loadingOpt.value = '';
            loadingOpt.textContent = '⏳ Loading matching students...';
            studentSelect.appendChild(loadingOpt);

            // Fetch real students from API endpoint
            const url = `index.php?page=api_students_filter&department_id=${encodeURIComponent(deptId)}&academic_year=${encodeURIComponent(yearCode)}`;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                spinner.style.display = 'none';
                studentSelect.innerHTML = '';

                if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                    currentFilteredStudents = data.data;

                    // Populate Box 3 Student Select
                    currentFilteredStudents.forEach((st, idx) => {
                        const fullName = (st.first_name || '') + ' ' + (st.last_name || '');
                        const name = fullName.trim() || st.full_name || 'Student #' + (st.id || idx + 1);
                        const code = st.admission_no || st.student_id || st.roll_no || 'N/A';

                        const opt = document.createElement('option');
                        opt.value = idx;
                        opt.textContent = `${name} (${code})`;
                        studentSelect.appendChild(opt);
                    });

                    // Enable Student Select
                    studentSelect.disabled = false;
                    studentSelect.style.cursor = 'pointer';
                    studentSelect.value = 0;

                    // Sync top header selector
                    const headerSel = document.getElementById('idCardStudentSelector');
                    if (headerSel) {
                        headerSel.innerHTML = studentSelect.innerHTML;
                        headerSel.disabled = false;
                        headerSel.value = 0;
                    }

                    // Render Slider
                    renderSliderCards();
                    updateSliderView(0);

                    // Show Slider Stage and hide states
                    document.getElementById('sliderInitialPrompt').style.display = 'none';
                    document.getElementById('sliderEmptyState').style.display = 'none';
                    document.getElementById('sliderStageWrapper').style.display = 'flex';
                    document.getElementById('sliderDotsWrapper').style.display = 'flex';
                    document.getElementById('sliderTopBar').style.display = 'flex';

                } else {
                    // Empty Result
                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.textContent = 'No matching students found';
                    studentSelect.appendChild(emptyOpt);
                    studentSelect.disabled = true;

                    // Sync top header selector
                    const headerSel = document.getElementById('idCardStudentSelector');
                    if (headerSel) {
                        headerSel.innerHTML = '<option value="">No matching students found</option>';
                        headerSel.disabled = true;
                    }

                    // Hide slider controls and show friendly message
                    document.getElementById('sliderStageWrapper').style.display = 'none';
                    document.getElementById('sliderDotsWrapper').style.display = 'none';
                    document.getElementById('sliderTopBar').style.display = 'none';
                    document.getElementById('sliderInitialPrompt').style.display = 'none';
                    document.getElementById('sliderEmptyState').style.display = 'block';
                    document.getElementById('sliderEmptyMessage').textContent = 'No students found for the selected department and academic year.';
                }
            })
            .catch(err => {
                console.error('Filter students API error:', err);
                spinner.style.display = 'none';
                studentSelect.innerHTML = '<option value="">Error fetching students</option>';
                studentSelect.disabled = true;

                document.getElementById('sliderStageWrapper').style.display = 'none';
                document.getElementById('sliderDotsWrapper').style.display = 'none';
                document.getElementById('sliderTopBar').style.display = 'none';
                document.getElementById('sliderInitialPrompt').style.display = 'none';
                document.getElementById('sliderEmptyState').style.display = 'block';
                document.getElementById('sliderEmptyMessage').textContent = 'An error occurred while loading student records. Please try again.';
            });
        }

        // Render Cards in Slider Track
        function renderSliderCards() {
            const track = document.getElementById('idCardSliderTrack');
            const dotsWrapper = document.getElementById('sliderDotsWrapper');
            track.innerHTML = '';
            dotsWrapper.innerHTML = '';

            currentFilteredStudents.forEach((st, idx) => {
                // Card Slide
                const cardHtml = renderStudentCardMarkup(st, idx);
                track.insertAdjacentHTML('beforeend', cardHtml);

                // Pagination Dot
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'slider-pagination-dot' + (idx === 0 ? ' active' : '');
                dot.setAttribute('aria-label', `Navigate to student ${idx + 1}`);
                dot.onclick = () => navigateToStudent(idx);
                dotsWrapper.appendChild(dot);
            });
        }

        // Navigate Slider to specific index
        function navigateToStudent(index) {
            if (index < 0 || index >= currentFilteredStudents.length) return;
            updateSliderView(index);
        }

        // Update Slider View, Boundaries, Dots, and Synchronized Dropdowns
        function updateSliderView(newIndex) {
            currentSliderIndex = newIndex;
            const track = document.getElementById('idCardSliderTrack');
            const prevBtn = document.getElementById('sliderPrevBtn');
            const nextBtn = document.getElementById('sliderNextBtn');
            const countBadge = document.getElementById('sliderStudentCountBadge');
            const breadcrumb = document.getElementById('sliderFilterBreadcrumb');
            const studentSelect = document.getElementById('filterStudentSelect');
            const headerSel = document.getElementById('idCardStudentSelector');

            // Slide Track horizontally
            track.style.transform = `translateX(-${currentSliderIndex * 100}%)`;

            // Boundaries: Left disabled on 1st, Right disabled on last
            const total = currentFilteredStudents.length;
            prevBtn.disabled = (currentSliderIndex <= 0);
            nextBtn.disabled = (currentSliderIndex >= total - 1);

            // Single student available
            if (total <= 1) {
                prevBtn.style.visibility = 'hidden';
                nextBtn.style.visibility = 'hidden';
            } else {
                prevBtn.style.visibility = 'visible';
                nextBtn.style.visibility = 'visible';
            }

            // Update Counter Badge & Breadcrumb
            if (countBadge) {
                countBadge.textContent = `Student ${currentSliderIndex + 1} of ${total}`;
            }
            if (breadcrumb) {
                const yearLabel = document.getElementById('filterAcademicYearSelect').value;
                breadcrumb.textContent = `${selectedDeptName} • ${yearLabel}`;
            }

            // Update Box 3 Student Dropdown
            if (studentSelect && studentSelect.value != currentSliderIndex) {
                studentSelect.value = currentSliderIndex;
            }

            // Update Header Selector
            if (headerSel && headerSel.value != currentSliderIndex) {
                headerSel.value = currentSliderIndex;
            }

            // Update Pagination Dots
            const dots = document.querySelectorAll('.slider-pagination-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentSliderIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });

            // Set #printableStudentIdCard ID on the active card for compatibility
            document.querySelectorAll('.printable-student-card').forEach((el, idx) => {
                if (idx === currentSliderIndex) {
                    el.id = 'printableStudentIdCard';
                } else {
                    el.id = `printableStudentIdCard_${idx}`;
                }
            });
        }

        // Arrow Buttons Handlers
        function slidePrevStudent() {
            if (currentSliderIndex > 0) {
                navigateToStudent(currentSliderIndex - 1);
            }
        }

        function slideNextStudent() {
            if (currentSliderIndex < currentFilteredStudents.length - 1) {
                navigateToStudent(currentSliderIndex + 1);
            }
        }

        // Box 3 Student Dropdown Selection Handler
        function onStudentDropdownChange(val) {
            const index = parseInt(val, 10);
            if (!isNaN(index) && index >= 0 && index < currentFilteredStudents.length) {
                navigateToStudent(index);
            }
        }

        // Top Header Student Selector Handler
        function onHeaderStudentSelectorChange(val) {
            const index = parseInt(val, 10);
            if (!isNaN(index) && index >= 0 && index < currentFilteredStudents.length) {
                navigateToStudent(index);
            }
        }

        // Backward compatibility switchIdCardStudent function
        function switchIdCardStudent(index) {
            onStudentDropdownChange(index);
        }

        // Touch Swipe & Mouse Drag Handling
        const sliderViewport = document.getElementById('idCardSliderViewport');
        if (sliderViewport) {
            // Touch Swipe
            sliderViewport.addEventListener('touchstart', (e) => {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });

            sliderViewport.addEventListener('touchend', (e) => {
                const touchEndX = e.changedTouches[0].clientX;
                const touchEndY = e.changedTouches[0].clientY;
                const diffX = touchEndX - touchStartX;
                const diffY = touchEndY - touchStartY;

                // Horizontal swipe threshold
                if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
                    if (diffX < 0) {
                        slideNextStudent();
                    } else {
                        slidePrevStudent();
                    }
                }
            }, { passive: true });

            // Desktop Mouse Drag
            sliderViewport.addEventListener('mousedown', (e) => {
                isMouseDown = true;
                mouseStartX = e.clientX;
                sliderViewport.style.cursor = 'grabbing';
            });

            window.addEventListener('mouseup', (e) => {
                if (!isMouseDown) return;
                isMouseDown = false;
                sliderViewport.style.cursor = 'grab';
                const diffX = e.clientX - mouseStartX;
                if (Math.abs(diffX) > 40) {
                    if (diffX < 0) {
                        slideNextStudent();
                    } else {
                        slidePrevStudent();
                    }
                }
            });
        }

        // PRINT FUNCTIONALITY (Requirement 6)
        function printStudentIdCard() {
            if (!currentFilteredStudents || currentFilteredStudents.length === 0 || currentSliderIndex < 0) {
                alert("Please select a department and academic year with available student ID cards to print.");
                return;
            }

            const activeCard = document.getElementById('printableStudentIdCard') || document.querySelector(`.student-id-slide[data-index="${currentSliderIndex}"] .printable-student-card`);
            if (!activeCard) {
                alert("No student ID card is currently displayed for printing.");
                return;
            }

            const currentStudent = currentFilteredStudents[currentSliderIndex];
            const studentName = currentStudent ? (currentStudent.full_name || (currentStudent.first_name + ' ' + currentStudent.last_name)) : 'Student';
            const cardHtml = activeCard.outerHTML;

            const printWindow = window.open('', '_blank', 'width=680,height=780');
            if (!printWindow) {
                alert("Please allow popups to print the Digital ID Card.");
                return;
            }

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Student Official Digital ID Card - ${escapeHtml(studentName)}</title>
                    <style>
                        @page {
                            size: auto;
                            margin: 12mm;
                        }
                        * {
                            box-sizing: border-box;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        body {
                            margin: 0;
                            padding: 30px;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            background: #F1F5F9;
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        }
                        @media print {
                            body {
                                background: none !important;
                                padding: 0 !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${cardHtml}
                    <script>
                        window.onload = function() {
                            const images = document.images;
                            let loaded = 0;
                            const total = images.length;
                            if (total === 0) {
                                setTimeout(() => { window.print(); }, 250);
                            } else {
                                let done = false;
                                function checkDone() {
                                    loaded++;
                                    if (loaded >= total && !done) {
                                        done = true;
                                        setTimeout(() => { window.print(); }, 300);
                                    }
                                }
                                for (let i = 0; i < total; i++) {
                                    if (images[i].complete) {
                                        checkDone();
                                    } else {
                                        images[i].onload = checkDone;
                                        images[i].onerror = checkDone;
                                    }
                                }
                                setTimeout(() => {
                                    if (!done) {
                                        done = true;
                                        window.print();
                                    }
                                }, 1500);
                            }
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        // On Page Load: Handle initial URL parameters or pre-populate if requested
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const urlDept = urlParams.get('department_id') || urlParams.get('dept');
            const urlYear = urlParams.get('academic_year') || urlParams.get('year');

            if (urlDept) {
                const deptItems = document.querySelectorAll('.dept-option-item');
                let foundDept = null;
                deptItems.forEach(item => {
                    const nameAttr = item.getAttribute('data-name');
                    const onclickAttr = item.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes(`'${urlDept}'`)) {
                        foundDept = item;
                    }
                });
                if (foundDept) {
                    foundDept.click();
                }
            }

            if (urlYear) {
                const yearSelect = document.getElementById('filterAcademicYearSelect');
                if (yearSelect) {
                    yearSelect.value = urlYear.toUpperCase();
                    onAcademicYearChange(yearSelect.value);
                }
            }
        });
        </script>
    <?php endif; ?>

    <?php if ($moduleKey === 'time_table'): ?>
        <?php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $sampleMatrix = [
            '7:10–8:00' => [
                'Monday'    => ['subject' => 'Mobile Application', 'teacher' => 'Prof. Sharma', 'room' => 'Lab No. 1'],
                'Tuesday'   => ['subject' => 'Web Technology', 'teacher' => 'Prof. Patil', 'room' => 'Block 101'],
                'Wednesday' => ['subject' => 'DBMS', 'teacher' => 'Prof. Khan', 'room' => 'Lab No. 2'],
                'Thursday'  => ['subject' => 'Java', 'teacher' => 'Prof. Singh', 'room' => 'Block 101'],
                'Friday'    => ['subject' => 'AI', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
                'Saturday'  => ['subject' => 'CN', 'teacher' => 'Prof. Joshi', 'room' => 'Lab No. 1'],
            ],
            '8:00–8:50' => [
                'Monday'    => ['subject' => 'AI Application', 'teacher' => 'Prof. Sharma', 'room' => 'Block 101'],
                'Tuesday'   => ['subject' => 'DBMS', 'teacher' => 'Prof. Khan', 'room' => 'Block 101'],
                'Wednesday' => ['subject' => 'Java', 'teacher' => 'Prof. Singh', 'room' => 'Lab No. 2'],
                'Thursday'  => ['subject' => 'Web Technology', 'teacher' => 'Prof. Patil', 'room' => 'Lab No. 1'],
                'Friday'    => ['subject' => 'CN', 'teacher' => 'Prof. Joshi', 'room' => 'Block 101'],
                'Saturday'  => ['subject' => 'AI', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
            ],
            '8:50–9:40' => [
                'Monday'    => ['subject' => 'DBMS', 'teacher' => 'Prof. Khan', 'room' => 'Block 101'],
                'Tuesday'   => ['subject' => 'Java', 'teacher' => 'Prof. Singh', 'room' => 'Lab No. 2'],
                'Wednesday' => ['subject' => 'Web Technology', 'teacher' => 'Prof. Patil', 'room' => 'Block 101'],
                'Thursday'  => ['subject' => 'AI', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
                'Friday'    => ['subject' => 'Mobile Application', 'teacher' => 'Prof. Sharma', 'room' => 'Lab No. 1'],
                'Saturday'  => ['subject' => 'DBMS', 'teacher' => 'Prof. Khan', 'room' => 'Block 101'],
            ],
            '9:40–10:30' => [
                'Monday'    => ['subject' => 'Web Technology', 'teacher' => 'Prof. Patil', 'room' => 'Lab No. 1'],
                'Tuesday'   => ['subject' => 'AI', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
                'Wednesday' => ['subject' => 'CN', 'teacher' => 'Prof. Joshi', 'room' => 'Block 101'],
                'Thursday'  => ['subject' => 'DBMS', 'teacher' => 'Prof. Khan', 'room' => 'Lab No. 2'],
                'Friday'    => ['subject' => 'Java', 'teacher' => 'Prof. Singh', 'room' => 'Block 101'],
                'Saturday'  => ['subject' => 'Mobile Application', 'teacher' => 'Prof. Sharma', 'room' => 'Lab No. 1'],
            ],
            '10:30–11:20' => [
                'Monday'    => ['subject' => 'Java Lab', 'teacher' => 'Prof. Singh', 'room' => 'Lab No. 2'],
                'Tuesday'   => ['subject' => 'Mobile Application', 'teacher' => 'Prof. Sharma', 'room' => 'Lab No. 1'],
                'Wednesday' => ['subject' => 'AI', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
                'Thursday'  => ['subject' => 'CN', 'teacher' => 'Prof. Joshi', 'room' => 'Block 101'],
                'Friday'    => ['subject' => 'DBMS Lab', 'teacher' => 'Prof. Khan', 'room' => 'Lab No. 2'],
                'Saturday'  => ['subject' => 'Web Technology', 'teacher' => 'Prof. Patil', 'room' => 'Block 101'],
            ],
            '11:20–12:10' => [
                'Monday'    => ['subject' => 'Software Eng', 'teacher' => 'Dr. Soap', 'room' => 'Block 101'],
                'Tuesday'   => ['subject' => 'CN Lab', 'teacher' => 'Prof. Joshi', 'room' => 'Lab No. 1'],
                'Wednesday' => ['subject' => 'Mobile Application', 'teacher' => 'Prof. Sharma', 'room' => 'Block 101'],
                'Thursday'  => ['subject' => 'Java', 'teacher' => 'Prof. Singh', 'room' => 'Block 101'],
                'Friday'    => ['subject' => 'Web Tech Lab', 'teacher' => 'Prof. Patil', 'room' => 'Lab No. 1'],
                'Saturday'  => ['subject' => 'AI Application', 'teacher' => 'Prof. Mehta', 'room' => 'Block 101'],
            ]
        ];

        // Standard 6 Time Slots strictly
        $matrix = $sampleMatrix;

        if (!empty($records)) {
            $slotKeys = array_keys($sampleMatrix);
            foreach ($records as $r) {
                $day = ucfirst(strtolower(trim($r['day_of_week'] ?? '')));
                $slot = trim($r['time_slot'] ?? '');
                $subj = $r['subject_name'] ?? '';
                $staffName = (!empty($r['first_name']) ? ($r['first_name'] . ' ' . ($r['last_name'] ?? '')) : 'Faculty');
                $room = $r['room_no'] ?? 'Block 101';

                if (in_array($day, $days, true) && !empty($subj)) {
                    // Match to standard slot
                    $targetSlot = null;
                    foreach ($slotKeys as $sKey) {
                        if ($sKey === $slot || str_contains($slot, substr($sKey, 0, 4))) {
                            $targetSlot = $sKey;
                            break;
                        }
                    }
                    if (!$targetSlot) {
                        if (str_contains($slot, '8:00') || str_contains($slot, '08:00')) {
                            $targetSlot = '8:00–8:50';
                        } elseif (str_contains($slot, '10:') || str_contains($slot, '10:10')) {
                            $targetSlot = '9:40–10:30';
                        } else {
                            $targetSlot = '7:10–8:00';
                        }
                    }

                    $matrix[$targetSlot][$day] = [
                        'id' => $r['id'] ?? null,
                        'subject' => $subj,
                        'teacher' => $staffName,
                        'room' => $room
                    ];
                }
            }
        }
        ?>

        <!-- Dark Theme Timetable View -->
        <div class="chart-card" style="margin-bottom:24px; background:#1C1C1E; border:1px solid #2C2C2E; border-radius:16px; padding:24px; color:#F2F2F7; box-shadow:0 20px 25px -5px rgba(0,0,0,0.6);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #2C2C2E; padding-bottom:16px; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="font-size:18px; font-weight:800; color:#FFFFFF; margin:0; display:flex; align-items:center; gap:8px;">
                        🗓️ Class Timetable
                    </h3>
                    <span style="font-size:12px; color:#8E8E93;">Weekly period schedule across days (Monday – Saturday)</span>
                </div>
                
                <div style="display:flex; align-items:center; gap:12px;">
                    <?php if ($canManage): ?>
                        <button type="button" class="btn btn-primary" onclick="openTimetableAssignModal('', '')" style="background:#0066FF; color:#FFFFFF; border:none; padding:8px 16px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer;">
                            + Add Period Slot
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid #3A3A3C;">
                            <th style="padding:14px; width:100px; color:#8E8E93; font-size:12px; font-weight:700; text-transform:uppercase;">Time</th>
                            <?php foreach ($days as $day): ?>
                                <th style="padding:14px; color:#FFFFFF; font-size:13px; font-weight:700; width:15%;"><?= $day ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matrix as $slot => $dayMap): ?>
                            <tr style="border-bottom:1px solid #2C2C2E;">
                                <td style="padding:16px 14px; color:#8E8E93; font-size:12px; font-weight:700; vertical-align:top; white-space:nowrap; font-family:monospace;">
                                    <?= htmlspecialchars($slot) ?>
                                </td>

                                <?php foreach ($days as $day): ?>
                                    <?php $cell = $dayMap[$day] ?? null; ?>
                                    <td style="padding:10px; vertical-align:top; position:relative; min-width:130px;">
                                        <?php if (!empty($cell)): ?>
                                            <div style="background:#2C2C2E; border:1px solid #3A3A3C; border-radius:8px; padding:10px 12px; position:relative; transition:all 0.2s;">
                                                <div style="font-weight:700; font-size:13px; color:#FFFFFF; margin-bottom:2px; line-height:1.2;">
                                                    <?= htmlspecialchars($cell['subject']) ?>
                                                </div>
                                                <div style="font-size:11px; color:#AEAEC0; font-weight:500;">
                                                    <?= htmlspecialchars($cell['teacher']) ?>
                                                </div>
                                                <div style="font-size:10px; font-weight:700; margin-top:6px; display:inline-flex; align-items:center; gap:4px; <?= str_contains(strtolower($cell['room']), 'lab') ? 'color:#30D158;' : 'color:#FF453A;' ?>">
                                                    <?= str_contains(strtolower($cell['room']), 'lab') ? '🧪' : '📍' ?> <?= htmlspecialchars($cell['room']) ?>
                                                </div>

                                                <?php if ($canManage): ?>
                                                    <button type="button" onclick="openTimetableAssignModal('<?= htmlspecialchars($day, ENT_QUOTES) ?>', '<?= htmlspecialchars($slot, ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($cell['subject']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($cell['teacher']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($cell['room']), ENT_QUOTES) ?>', <?= (int)($cell['id'] ?? 0) ?>)" style="position:absolute; top:6px; right:6px; background:rgba(255,255,255,0.15); border:none; color:#FFFFFF; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; cursor:pointer;" title="Edit Period Slot">
                                                        ✏️ Edit
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php if ($canManage): ?>
                                                <div onclick="openTimetableAssignModal('<?= htmlspecialchars($day, ENT_QUOTES) ?>', '<?= htmlspecialchars($slot, ENT_QUOTES) ?>', '', '', '', 0)" style="border:1px dashed #3A3A3C; border-radius:8px; padding:14px; text-align:center; color:#636366; font-size:11px; font-weight:600; cursor:pointer;" onmouseover="this.style.background='rgba(0,102,255,0.15)'; this.style.borderColor='#0066FF'; this.style.color='#64D2FF';" onmouseout="this.style.background='transparent'; this.style.borderColor='#3A3A3C'; this.style.color='#636366';">
                                                    + Assign
                                                </div>
                                            <?php else: ?>
                                                <div style="font-size:11px; color:#48484A; text-align:center; padding:14px;">—</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($canManage): ?>
            <!-- Edit / Assign Period Modal -->
            <div id="timetableAssignModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#1C1C1E; border:1px solid #3A3A3C; border-radius:16px; padding:24px; width:100%; max-width:440px; color:#FFFFFF; box-shadow:0 25px 50px -12px rgba(0,0,0,0.9);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #2C2C2E; padding-bottom:12px;">
                        <h3 style="font-size:16px; font-weight:800; margin:0;" id="ttAssignModalTitle">Assign Class Period</h3>
                        <button type="button" onclick="closeTimetableAssignModal()" style="background:none; border:none; color:#8E8E93; font-size:18px; font-weight:700; cursor:pointer;">✕</button>
                    </div>

                    <form action="index.php?page=module_store" method="POST">
                        <?= \App\Core\CSRF::inputField() ?>
                        <input type="hidden" name="module_key" value="time_table">
                        <input type="hidden" name="record_id" id="ttAssignRecordId" value="0">
                        <input type="hidden" name="batch_id" value="1">

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#8E8E93; margin-bottom:6px;">Day of Week</label>
                            <select name="day_of_week" id="ttAssignDayOfWeek" style="width:100%; background:#2C2C2E; color:#FFFFFF; border:1px solid #3A3A3C; padding:10px; border-radius:8px; font-size:13px; font-weight:600; outline:none;" required>
                                <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#8E8E93; margin-bottom:6px;">Time Period Slot</label>
                            <input type="text" name="time_slot" id="ttAssignTimeSlot" placeholder="e.g. 7:10–8:00 or 8:00–8:50" style="width:100%; background:#2C2C2E; color:#FFFFFF; border:1px solid #3A3A3C; padding:10px; border-radius:8px; font-size:13px; font-weight:600; outline:none;" required>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#8E8E93; margin-bottom:6px;">Subject Name</label>
                            <input type="text" name="subject_name" id="ttAssignSubjectName" placeholder="e.g. Mobile Application" style="width:100%; background:#2C2C2E; color:#FFFFFF; border:1px solid #3A3A3C; padding:10px; border-radius:8px; font-size:13px; font-weight:600; outline:none;" required>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#8E8E93; margin-bottom:6px;">Assigned Teacher / Faculty</label>
                            <select name="staff_id" id="ttAssignStaffId" style="width:100%; background:#2C2C2E; color:#FFFFFF; border:1px solid #3A3A3C; padding:10px; border-radius:8px; font-size:13px; font-weight:600; outline:none;" required>
                                <?php if (!empty($staffList)): ?>
                                    <?php foreach ($staffList as $stf): ?>
                                        <option value="<?= $stf['id'] ?>"><?= htmlspecialchars(($stf['first_name'] ?? '') . ' ' . ($stf['last_name'] ?? '') . ' (' . ($stf['emp_id'] ?? 'EMP') . ')') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Prof. Sharma (EMP-001)</option>
                                    <option value="2">Prof. Patil (EMP-002)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#8E8E93; margin-bottom:6px;">Room / Location</label>
                            <input type="text" name="room_no" id="ttAssignRoomNo" placeholder="e.g. Lab No. 1 or Block 101" style="width:100%; background:#2C2C2E; color:#FFFFFF; border:1px solid #3A3A3C; padding:10px; border-radius:8px; font-size:13px; font-weight:600; outline:none;" required>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button type="button" onclick="closeTimetableAssignModal()" style="background:#2C2C2E; color:#8E8E93; border:1px solid #3A3A3C; padding:10px 16px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer;">Cancel</button>
                            <button type="submit" style="background:#0066FF; color:#FFFFFF; border:none; padding:10px 20px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer;">Save Period Slot</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openTimetableAssignModal(day, slot, subject, teacher, room, recordId) {
                    if (day) document.getElementById('ttAssignDayOfWeek').value = day;
                    if (slot) document.getElementById('ttAssignTimeSlot').value = slot;
                    if (subject) document.getElementById('ttAssignSubjectName').value = subject;
                    if (room) document.getElementById('ttAssignRoomNo').value = room;
                    if (recordId) document.getElementById('ttAssignRecordId').value = recordId;
                    
                    var title = (subject ? 'Edit Period Slot' : 'Assign Period Slot');
                    if (day && slot) title += ' — ' + day + ' (' + slot + ')';
                    document.getElementById('ttAssignModalTitle').innerText = title;
                    document.getElementById('timetableAssignModal').style.display = 'flex';
                }
                function closeTimetableAssignModal() {
                    document.getElementById('timetableAssignModal').style.display = 'none';
                }
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (($moduleKey === 'staff_data' || $moduleKey === 'hr_management') && in_array($user['role_name'] ?? '', ['super_admin', 'school_admin', 'principal', 'administrator'], true)): ?>
        <!-- Section 3 & 12 — Department & Teacher Assignment Management Cards -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:24px;">
            <!-- Department & Academic Standard Creation Card -->
            <div class="chart-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="border-bottom:1px solid #E2E8F0; padding-bottom:12px; margin-bottom:16px;">
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        🏛️ Add New Department & Standard
                    </h3>
                    <span style="font-size:12px; color:var(--text-muted);">Dynamic department storage in database (e.g. B.Sc. IT, AI & Data Science)</span>
                </div>
                <form action="index.php?page=store_department" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Name *</label>
                        <input type="text" name="name" placeholder="e.g. Artificial Intelligence & Data Science" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Code</label>
                        <input type="text" name="code" placeholder="e.g. BSC-AIDS" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:700; border-radius:8px; background:#4F46E5; border:none; cursor:pointer;">
                        + Create Department & Auto-Seed Standards (FY, SY, TY)
                    </button>
                </form>
            </div>

            <!-- Teacher Academic Assignment Card -->
            <div class="chart-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="border-bottom:1px solid #E2E8F0; padding-bottom:12px; margin-bottom:16px;">
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        👨‍🏫 Teacher Academic Assignment
                    </h3>
                    <span style="font-size:12px; color:var(--text-muted);">Assign Teacher to Department, Standard, Division/Class & Subject</span>
                </div>
                <form action="index.php?page=assign_teacher" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <div style="margin-bottom:10px;">
                        <label style="display:block; font-size:11px; font-weight:700; color:#334155; margin-bottom:2px;">Select Teacher / Faculty *</label>
                        <select name="staff_id" class="form-control" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px;">
                            <option value="">-- Select Faculty Member --</option>
                            <?php foreach ($staffList as $stf): ?>
                                <option value="<?= $stf['id'] ?>">Prof. <?= htmlspecialchars(($stf['first_name'] ?? '') . ' ' . ($stf['last_name'] ?? '') . ' (' . ($stf['emp_id'] ?? 'EMP') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:#334155; margin-bottom:2px;">Department *</label>
                            <select name="department_id" id="assign_dept_id" class="form-control" onchange="loadAssignStandards(this.value)" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px;">
                                <option value="">-- Select Dept --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . $d['code'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:#334155; margin-bottom:2px;">Standard / Year *</label>
                            <select name="standard_id" id="assign_standard_id" class="form-control" onchange="loadAssignClasses(this.value)" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px;">
                                <option value="">-- Select Year --</option>
                                <option value="1">FY</option>
                                <option value="2">SY</option>
                                <option value="3" selected>TY</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:14px;">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:#334155; margin-bottom:2px;">Class / Division *</label>
                            <select name="section_id" id="assign_section_id" class="form-control" onchange="loadAssignSubjects(this.value)" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px;">
                                <option value="1">Class A</option>
                                <option value="2">Class B</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:#334155; margin-bottom:2px;">Course / Subject *</label>
                            <select name="subject_id" id="assign_subject_id" class="form-control" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px;">
                                <option value="1">Database Management System (DBMS)</option>
                                <option value="2">Java Programming</option>
                                <option value="3">Web Technology</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:700; border-radius:8px; background:#059669; border:none; cursor:pointer;">
                        + Save Teacher Academic Assignment
                    </button>
                </form>
            </div>
        </div>

        <script>
        function loadAssignStandards(deptId) {
            const stdSel = document.getElementById('assign_standard_id');
            if (!stdSel) return;
            stdSel.innerHTML = '<option value="">Loading...</option>';
            fetch('index.php?page=api_standards&department_id=' + deptId)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        let html = '<option value="">-- Select Year --</option>';
                        data.data.forEach(item => { html += `<option value="${item.id}">${item.name}</option>`; });
                        stdSel.innerHTML = html;
                    } else { stdSel.innerHTML = '<option value="3">TY</option><option value="2">SY</option><option value="1">FY</option>'; }
                });
        }
        function loadAssignClasses(standardId) {
            const deptId = document.getElementById('assign_dept_id')?.value || 0;
            const secSel = document.getElementById('assign_section_id');
            if (!secSel) return;
            secSel.innerHTML = '<option value="">Loading...</option>';
            fetch(`index.php?page=api_classes&standard_id=${standardId}&department_id=${deptId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        let html = '<option value="">-- Select Class --</option>';
                        data.data.forEach(item => { html += `<option value="${item.id}">${item.name}</option>`; });
                        secSel.innerHTML = html;
                    } else { secSel.innerHTML = '<option value="1">Class A</option><option value="2">Class B</option>'; }
                });
        }
        function loadAssignSubjects(sectionId) {
            const deptId = document.getElementById('assign_dept_id')?.value || 0;
            const stdId = document.getElementById('assign_standard_id')?.value || 0;
            const subSel = document.getElementById('assign_subject_id');
            if (!subSel) return;
            subSel.innerHTML = '<option value="">Loading...</option>';
            fetch(`index.php?page=api_subjects&section_id=${sectionId}&standard_id=${stdId}&department_id=${deptId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        let html = '<option value="">-- Select Subject --</option>';
                        data.data.forEach(item => { html += `<option value="${item.id}">${item.name} (${item.code || 'SUB'})</option>`; });
                        subSel.innerHTML = html;
                    } else { subSel.innerHTML = '<option value="1">DBMS</option><option value="2">Java</option>'; }
                });
        }
        </script>
    <?php endif; ?>

    <!-- Data Table Card -->

    <?php if ($moduleKey !== 'time_table'): ?>
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <h3>Database Records Inspector</h3>
                <span style="font-size:12px; color:var(--text-muted);">Total Stored Entries: <?= count($records) ?> | Multi-Tenant Scoped</span>
            </div>
            <div style="display:flex; gap:10px;">
                <input type="text" id="tableSearch" placeholder="Search table..." style="padding:6px 12px; border-radius:var(--radius-pill); border:1px solid var(--border-color); font-size:12px; outline:none;" />
                <button type="button" class="btn btn-secondary" onclick="exportTableToCSV('moduleDataTable', '<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>_export.csv')">Export CSV</button>
                <button type="button" class="btn btn-secondary" onclick="window.print()">Print</button>
            </div>
        </div>

        <div style="overflow-x:auto; margin-top:16px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;" id="moduleDataTable">
                <thead>
                    <tr style="background:#F8FAFC; text-align:left; border-bottom:2px solid var(--border-color); color:var(--text-muted); font-size:12px;">
                        <?php if (!empty($records)): ?>
                            <?php foreach (array_keys($records[0]) as $col): ?>
                                <th style="padding:12px 14px; text-transform:uppercase;"><?= str_replace('_', ' ', $col) ?></th>
                            <?php endforeach; ?>
                            <th style="padding:12px 14px; text-align:right;">ACTIONS</th>
                        <?php else: ?>
                            <th style="padding:12px 14px;">STATUS</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $row): ?>
                            <tr style="border-bottom:1px solid var(--border-color); transition:background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <?php foreach ($row as $key => $val): ?>
                                    <td style="padding:12px 14px;">
                                        <?php if ($key === 'status'): ?>
                                            <span style="background:<?= $val === 'active' || $val === 'approved' || $val === 'paid' || $val === 'published' || $val === 'admitted' ? 'var(--success-light)' : 'var(--warning-light)' ?>; color:<?= $val === 'active' || $val === 'approved' || $val === 'paid' || $val === 'published' || $val === 'admitted' ? 'var(--success)' : 'var(--warning)' ?>; padding:2px 8px; border-radius:var(--radius-pill); font-size:11px; font-weight:700; text-transform:uppercase;"><?= htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php elseif ($key === 'photo'): ?>
                                            <?php if (!empty($val)): ?>
                                                <?php if (strtolower(pathinfo((string)$val, PATHINFO_EXTENSION)) === 'pdf' || str_contains(strtolower((string)$val), '.pdf')): ?>
                                                    <a href="<?= htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; color:#DC2626; border-color:#FCA5A5; background:#FEF2F2; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                        📄 View PDF Photo
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="display:inline-block;">
                                                        <img src="<?= htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #6366F1;" alt="Student Photo">
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span style="color:#94A3B8; font-size:11px;">No Photo</span>
                                            <?php endif; ?>
                                        <?php elseif ($key === 'salary'): ?>
                                            <strong style="color:#15803D;">₹<?= number_format((float)$val, 2) ?></strong>
                                        <?php elseif ($key === 'phone'): ?>
                                            <strong style="color:#0F172A;"><?= !empty($val) ? htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') : '-' ?></strong>
                                        <?php else: ?>
                                            <?= htmlspecialchars($val !== null ? (string)$val : '-', ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td style="padding:12px 14px; text-align:right; display:flex; gap:6px; justify-content:flex-end;">
                                    <?php if ($moduleKey === 'payroll' || $moduleKey === 'staff_data' || $moduleKey === 'hr_management'): ?>
                                        <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModalFromRecord(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">📄 Payslip PDF</button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px;" data-record="<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>" onclick="handleViewClick(this)"><?= $moduleKey === 'fees' ? '📄 Download / View Receipt' : 'View' ?></button>
                                    
                                    <?php if ($canManage): ?>
                                        <button type="button" class="btn btn-primary" style="padding:4px 10px; font-size:11px;" data-record="<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>" onclick="handleEditClick(this)">Edit</button>

                                        <form action="index.php?page=module_delete" method="POST" onsubmit="return confirm('Permanently delete this record #<?= $row['id'] ?>?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                            <input type="hidden" name="module_key" value="<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="record_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; color:var(--danger); border-color:#FCA5A5;">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" style="padding:40px; text-align:center; color:var(--text-muted);">
                                No records currently stored in <strong><?= htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8') ?></strong>.
                                <?php if ($canManage): ?>
                                    <br><br>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('createRecordModal').classList.add('active')">+ Add New Record</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</main>


<!-- CREATE RECORD MODAL FORM -->
<div class="modal-overlay" id="createRecordModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">+ Create New Record for <?= htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <button type="button" onclick="document.getElementById('createRecordModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= ($moduleKey === 'admission' || $moduleKey === 'student_mis' || $moduleKey === 'id_cards') ? 'index.php?page=admit_student' : 'index.php?page=module_store' ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                <input type="hidden" name="module_key" value="<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect_module" value="<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>">

                <?php if ($moduleKey === 'communication' || $moduleKey === 'news_events' || $moduleKey === 'academic_calendar'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Notice Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Annual Campus Announcement" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Target Audience</label>
                        <select name="target_role" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <option value="all">All Roles (Public Notice)</option>
                            <option value="student">Students Only</option>
                            <option value="parent">Parents Only</option>
                            <option value="teacher">Teachers / Faculty Only</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Notice Content / Details</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Enter complete announcement content..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;"></textarea>
                    </div>
                <?php elseif ($moduleKey === 'enquiry'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Student Name</label>
                        <input type="text" name="student_name" class="form-control" placeholder="e.g. Alex Morgan" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. alex@gmail.com" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. +1 555-0199" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Course Interested</label>
                        <input type="text" name="course_interested" class="form-control" placeholder="e.g. BTECH-CSE" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'admission' || $moduleKey === 'student_mis' || $moduleKey === 'id_cards' || $moduleKey === 'day_boarding' || $moduleKey === 'infirmary'): ?>
                    <!-- ADMISSION MODE SELECTOR -->
                    <div style="background: linear-gradient(135deg, #F8FAFC, #EFF6FF); border: 1px solid #BFDBFE; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div style="font-size: 13px; font-weight: 800; color: #1E3A8A; display: flex; align-items: center; gap: 6px;">
                            <span>🎓</span> Select Admission Mode:
                        </div>
                        <div style="display: flex; background: #FFFFFF; padding: 4px; border-radius: 10px; border: 1px solid #CBD5E1; gap: 4px;">
                            <button type="button" id="btnModeNew" onclick="switchAdmissionMode('new')" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; background: #0066FF; color: #FFF;">
                                ✨ New Admission
                            </button>
                            <button type="button" id="btnModePromote" onclick="switchAdmissionMode('promote')" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; background: transparent; color: #475569;">
                                🔄 Existing Student – Promote to Next Year
                            </button>
                        </div>
                    </div>

                    <!-- MODE A: NEW ADMISSION FORM -->
                    <div id="sectionNewAdmission">
                        <!-- SECTION 1: PERSONAL & LOGIN INFORMATION -->
                        <div style="margin-bottom: 20px;">
                            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-bottom: 12px;">
                                1. Personal & Login Information
                            </div>

                            <style>
                                .personal-info-grid {
                                    display: grid;
                                    grid-template-columns: repeat(4, minmax(0, 1fr));
                                    gap: 16px 12px;
                                }
                                @media (max-width: 992px) {
                                    .personal-info-grid {
                                        grid-template-columns: repeat(2, minmax(0, 1fr));
                                    }
                                }
                                @media (max-width: 576px) {
                                    .personal-info-grid {
                                        grid-template-columns: minmax(0, 1fr);
                                    }
                                }
                            </style>

                            <div class="personal-info-grid">
                                <!-- Row 1 -->
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Unique ID *</label>
                                    <input type="text" name="unique_id" value="<?= htmlspecialchars($nextUniqueId ?? 'MU2026-01', ENT_QUOTES, 'UTF-8') ?>" readonly class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; font-weight:800; color:#0066FF;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">First Name *</label>
                                    <input type="text" name="first_name" placeholder="e.g. Rahul" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Middle Name</label>
                                    <input type="text" name="middle_name" placeholder="e.g. Kumar" class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Last Name *</label>
                                    <input type="text" name="last_name" placeholder="e.g. Sharma" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                </div>

                                <!-- Row 2 -->
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">DOB *</label>
                                    <input type="date" name="dob" value="2005-01-15" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Student ID / Admission No. *</label>
                                    <input type="text" name="student_id" value="<?= htmlspecialchars('STU' . date('Y') . str_replace(['MU', date('Y'), '-'], '', $nextUniqueId ?? 'MU2026-01'), ENT_QUOTES, 'UTF-8') ?>" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-weight:700;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Password (Login Access) *</label>
                                    <input type="password" name="password" placeholder="Set Login Password" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#1E293B;">Gender *</label>
                                    <select name="gender" required class="form-control" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1;">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: CONTACT & GUARDIAN DETAILS -->
                        <div style="margin-bottom: 20px; border-top: 1px dashed #CBD5E1; padding-top: 16px;">
                            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-bottom: 12px;">
                                2. Contact & Guardian Details
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Email Address *</label>
                                    <input type="email" name="email" class="form-control" placeholder="student@example.com" required style="width:100%; padding:9px; border-radius:8px; border:1px solid #E2E8F0;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Student Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+91 9876543210" required style="width:100%; padding:9px; border-radius:8px; border:1px solid #E2E8F0;">
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Father Mobile no. *</label>
                                    <input type="tel" name="guardian_phone" class="form-control" placeholder="+91 9876500001" required style="width:100%; padding:9px; border-radius:8px; border:1px solid #E2E8F0;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Mother Mobile no. *</label>
                                    <input type="tel" name="mother_phone" class="form-control" placeholder="+91 9876500002" required style="width:100%; padding:9px; border-radius:8px; border:1px solid #E2E8F0;">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: ACADEMIC & DEPARTMENT ENROLLMENT -->
                        <div style="margin-bottom: 20px; border-top: 1px dashed #CBD5E1; padding-top: 16px;">
                            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-bottom: 12px;">
                                3. Academic & Department Enrollment
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:12px;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px;">Department *</label>
                                    <select name="department_id" class="form-control" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #E2E8F0; font-size:12px;">
                                        <option value="">-- Select --</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px;">Student Year *</label>
                                    <select name="standard_id" class="form-control" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #E2E8F0; font-size:12px;">
                                        <option value="1">1st Year (FY)</option>
                                        <option value="2">2nd Year (SY)</option>
                                        <option value="3">3rd Year (TY)</option>
                                        <option value="4">4th Year (Final)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px;">Student Class *</label>
                                    <select name="class_id" class="form-control" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #E2E8F0; font-size:12px;">
                                        <option value="">-- Select --</option>
                                        <?php foreach ($classesList as $cls): ?>
                                            <option value="<?= $cls['id'] ?>"><?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Student Photo (PDF or Image)</label>
                                <input type="file" name="photo" accept="image/*,.pdf" class="form-control" style="width:100%; padding:6px; border-radius:8px; border:1px solid #E2E8F0; font-size:12px;">
                            </div>
                        </div>
                    </div>

                    <!-- MODE B: EXISTING STUDENT PROMOTION SECTION -->
                    <div id="sectionStudentPromotion" style="display:none;">
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:18px; margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:800; color:#0F172A; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                                <span>🔍</span> Search Existing Student by Unique ID
                            </div>
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="searchUniqueIdInput" placeholder="Enter Unique ID e.g. MU2026-01" class="form-control" style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-weight:700;">
                                <button type="button" class="btn btn-primary" onclick="searchStudentPromotion()" style="padding:9px 18px; font-weight:700; background:#2563EB; border:none; border-radius:8px; cursor:pointer;">
                                    Search
                                </button>
                            </div>
                            <div id="searchLoadingIndicator" style="display:none; margin-top:10px; font-size:12px; color:#2563EB; font-weight:700;">
                                ⌛ Searching student records...
                            </div>
                            <div id="searchErrorMessage" style="display:none; margin-top:10px; background:#FEF2F2; color:#991B1B; border:1px solid #FCA5A5; padding:8px 12px; border-radius:8px; font-size:12px;"></div>
                        </div>

                        <!-- FOUND STUDENT DETAILS CARD & PROMOTION SUBMISSION -->
                        <div id="promotionFormCard" style="display:none; background:#FFF; border:1px solid #93C5FD; border-radius:12px; padding:18px; box-shadow:0 4px 12px rgba(37,99,235,0.08);">
                            <div style="border-bottom:1px solid #E2E8F0; padding-bottom:10px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <h4 style="margin:0; font-size:15px; font-weight:800; color:#1E3A8A;" id="resStudentFullName">Rahul Sharma</h4>
                                    <div style="font-size:12px; color:#64748B;">Permanent Unique ID: <strong id="resUniqueIdTag" style="color:#0066FF;">MU2026-01</strong></div>
                                </div>
                                <span style="background:#DCFCE7; color:#166534; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase;" id="resStatusTag">ACTIVE</span>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px; background:#F8FAFC; padding:12px; border-radius:8px; margin-bottom:16px; font-size:12px;">
                                <div><strong>Admission No:</strong> <span id="resAdmissionNo">STU2024001</span></div>
                                <div><strong>Department:</strong> <span id="resDeptName">Computer Science</span></div>
                                <div><strong>Current Level:</strong> <span id="resCurrentStandard" style="color:#2563EB; font-weight:700;">1st Year (FY)</span></div>
                                <div><strong>Current Class:</strong> <span id="resClassName">Class A</span></div>
                            </div>

                            <div style="background:#EFF6FF; border:1px solid #BFDBFE; padding:14px; border-radius:10px; margin-bottom:16px;">
                                <div style="font-size:13px; font-weight:800; color:#1E40AF; margin-bottom:10px;">
                                    🎓 Academic Progression Target
                                </div>
                                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px; color:#1E293B;">Target Academic Year / Level *</label>
                                        <select id="selectTargetStandard" onchange="updateFormPromoteTargetStandard(this.value)" class="form-control" style="width:100%; padding:8px; border-radius:8px; border:1px solid #CBD5E1; font-weight:700;">
                                            <option value="2">2nd Year (SY)</option>
                                            <option value="3">3rd Year (TY)</option>
                                            <option value="4">4th Year (Final)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px; color:#1E293B;">Target Class *</label>
                                        <select id="selectTargetClass" onchange="updateFormPromoteTargetClass(this.value)" class="form-control" style="width:100%; padding:8px; border-radius:8px; border:1px solid #CBD5E1;">
                                            <?php foreach ($classesList as $cls): ?>
                                                <option value="<?= $cls['id'] ?>"><?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; margin-bottom:4px; color:#1E293B;">Target Academic Year *</label>
                                        <input type="text" id="inputTargetAcademicYear" oninput="updateFormPromoteTargetYear(this.value)" value="<?= ((int)date('Y') + 1) . '-' . ((int)date('Y') + 2) ?>" class="form-control" style="width:100%; padding:8px; border-radius:8px; border:1px solid #CBD5E1; font-weight:700;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function switchAdmissionMode(mode) {
                        const btnNew = document.getElementById('btnModeNew');
                        const btnPromote = document.getElementById('btnModePromote');
                        const secNew = document.getElementById('sectionNewAdmission');
                        const secPromote = document.getElementById('sectionStudentPromotion');
                        const modalForm = document.querySelector('#createRecordModal form');

                        if (mode === 'new') {
                            btnNew.style.background = '#0066FF';
                            btnNew.style.color = '#FFF';
                            btnPromote.style.background = 'transparent';
                            btnPromote.style.color = '#475569';
                            secNew.style.display = 'block';
                            secPromote.style.display = 'none';
                            if (modalForm) modalForm.action = 'index.php?page=admit_student';
                        } else {
                            btnPromote.style.background = '#0066FF';
                            btnPromote.style.color = '#FFF';
                            btnNew.style.background = 'transparent';
                            btnNew.style.color = '#475569';
                            secNew.style.display = 'none';
                            secPromote.style.display = 'block';
                            if (modalForm) modalForm.action = 'index.php?page=promote_student';
                        }
                    }

                    function searchStudentPromotion() {
                        const query = document.getElementById('searchUniqueIdInput').value.trim();
                        const loading = document.getElementById('searchLoadingIndicator');
                        const errorBox = document.getElementById('searchErrorMessage');
                        const card = document.getElementById('promotionFormCard');

                        if (!query) {
                            errorBox.innerText = 'Please enter a Student Unique ID or Admission Number to search.';
                            errorBox.style.display = 'block';
                            card.style.display = 'none';
                            return;
                        }

                        loading.style.display = 'block';
                        errorBox.style.display = 'none';
                        card.style.display = 'none';

                        fetch(`index.php?page=api_search_student_promotion&unique_id=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                loading.style.display = 'none';
                                if (data.error) {
                                    errorBox.innerText = data.error;
                                    errorBox.style.display = 'block';
                                } else if (data.success && data.student) {
                                    const st = data.student;
                                    document.getElementById('resStudentFullName').innerText = `${st.first_name} ${st.middle_name ? st.middle_name + ' ' : ''}${st.last_name}`;
                                    document.getElementById('resUniqueIdTag').innerText = st.unique_id || 'MU2026-01';
                                    document.getElementById('resAdmissionNo').innerText = st.admission_no || '-';
                                    document.getElementById('resDeptName').innerText = st.department_name || 'General';
                                    document.getElementById('resCurrentStandard').innerText = st.standard_name || '1st Year (FY)';
                                    document.getElementById('resClassName').innerText = st.class_name || 'Class A';
                                    document.getElementById('resStatusTag').innerText = (st.status || 'active').toUpperCase();
                                    
                                    let hiddenUid = document.getElementById('hiddenPromoteUniqueId');
                                    if (!hiddenUid) {
                                        hiddenUid = document.createElement('input');
                                        hiddenUid.type = 'hidden';
                                        hiddenUid.id = 'hiddenPromoteUniqueId';
                                        hiddenUid.name = 'unique_id';
                                        const form = document.querySelector('#createRecordModal form');
                                        if (form) form.appendChild(hiddenUid);
                                    }
                                    hiddenUid.value = st.unique_id || st.admission_no;

                                    let hiddenStd = document.getElementById('hiddenPromoteStd');
                                    if (!hiddenStd) {
                                        hiddenStd = document.createElement('input');
                                        hiddenStd.type = 'hidden';
                                        hiddenStd.id = 'hiddenPromoteStd';
                                        hiddenStd.name = 'target_standard_id';
                                        const form = document.querySelector('#createRecordModal form');
                                        if (form) form.appendChild(hiddenStd);
                                    }

                                    let hiddenCls = document.getElementById('hiddenPromoteCls');
                                    if (!hiddenCls) {
                                        hiddenCls = document.createElement('input');
                                        hiddenCls.type = 'hidden';
                                        hiddenCls.id = 'hiddenPromoteCls';
                                        hiddenCls.name = 'target_class_id';
                                        const form = document.querySelector('#createRecordModal form');
                                        if (form) form.appendChild(hiddenCls);
                                    }

                                    let hiddenYr = document.getElementById('hiddenPromoteYr');
                                    if (!hiddenYr) {
                                        hiddenYr = document.createElement('input');
                                        hiddenYr.type = 'hidden';
                                        hiddenYr.id = 'hiddenPromoteYr';
                                        hiddenYr.name = 'target_academic_year';
                                        const form = document.querySelector('#createRecordModal form');
                                        if (form) form.appendChild(hiddenYr);
                                    }

                                    if (data.next_standard_id) {
                                        document.getElementById('selectTargetStandard').value = data.next_standard_id;
                                        hiddenStd.value = data.next_standard_id;
                                    }
                                    if (data.default_target_academic_year) {
                                        document.getElementById('inputTargetAcademicYear').value = data.default_target_academic_year;
                                        hiddenYr.value = data.default_target_academic_year;
                                    }
                                    hiddenCls.value = document.getElementById('selectTargetClass').value;

                                    card.style.display = 'block';
                                }
                            })
                            .catch(err => {
                                loading.style.display = 'none';
                                errorBox.innerText = 'Network error while retrieving student records. Please try again.';
                                errorBox.style.display = 'block';
                            });
                    }

                    function updateFormPromoteTargetStandard(val) {
                        const h = document.getElementById('hiddenPromoteStd');
                        if (h) h.value = val;
                    }
                    function updateFormPromoteTargetClass(val) {
                        const h = document.getElementById('hiddenPromoteCls');
                        if (h) h.value = val;
                    }
                    function updateFormPromoteTargetYear(val) {
                        const h = document.getElementById('hiddenPromoteYr');
                        if (h) h.value = val;
                    }
                    </script><?php elseif ($moduleKey === 'fees'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Select Student *</label>
                        <select name="student_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <?php if (!empty($studentsList)): ?>
                                <?php foreach ($studentsList as $st): ?>
                                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['roll_no'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">John Doe (24CSE001)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Fee Structure</label>
                            <select name="fee_structure_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                                <?php if (!empty($feeStructuresList)): ?>
                                    <?php foreach ($feeStructuresList as $fs): ?>
                                        <option value="<?= $fs['id'] ?>"><?= htmlspecialchars($fs['fee_type'] . ' - ₹' . number_format($fs['amount'], 2), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Tuition Fee - ₹10,500.00</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Fee Installment *</label>
                            <select name="installment_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                                <?php if (!empty($feeInstallmentsList)): ?>
                                    <?php foreach ($feeInstallmentsList as $fi): ?>
                                        <option value="<?= $fi['id'] ?>"><?= htmlspecialchars($fi['title'] . ' (₹' . number_format($fi['amount'], 2) . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Installment #1 (Term 1) - ₹625.00</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Amount Paid (₹) *</label>
                            <input type="number" step="0.01" name="amount_paid" class="form-control" placeholder="e.g. 625.00" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Payment Mode</label>
                            <select name="payment_mode" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                                <option value="online">Online Payment</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                <?php elseif ($moduleKey === 'accounts'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Transaction Type</label>
                        <select name="type" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Tuition Fee Collection / Equipment Expense" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 500.00" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'library'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Book Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Advanced Data Structures" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Author</label>
                        <input type="text" name="author" class="form-control" placeholder="e.g. Robert Sedgewick" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'time_table'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Subject Name *</label>
                        <input type="text" name="subject_name" class="form-control" placeholder="e.g. Data Structures & Algorithms" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Day of Week *</label>
                            <select name="day_of_week" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Time Slot *</label>
                            <input type="text" name="time_slot" class="form-control" placeholder="e.g. 09:00 AM - 10:00 AM" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Room / Lab No. *</label>
                            <input type="text" name="room_no" class="form-control" placeholder="e.g. Room 204 / Lab 101" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Faculty / Staff</label>
                            <select name="staff_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                                <?php if (!empty($staffList)): ?>
                                    <?php foreach ($staffList as $stf): ?>
                                        <option value="<?= $stf['id'] ?>"><?= htmlspecialchars(($stf['first_name'] ?? '') . ' ' . ($stf['last_name'] ?? '') . ' (' . ($stf['emp_id'] ?? 'EMP') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Dr. Soap (EMP-001)</option>
                                    <option value="2">Dr. Cipeng (EMP-002)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Target Class Batch</label>
                        <select name="batch_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <?php if (!empty($batchesList)): ?>
                                <?php foreach ($batchesList as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['batch_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">CSE Batch 2024-2028</option>
                                <option value="2">IT Batch 2025-2028</option>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php elseif ($moduleKey === 'exam_management'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Exam / Test Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Mid-Term Examination 2026" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Exam Date *</label>
                            <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Total Marks *</label>
                            <input type="number" name="total_marks" class="form-control" value="100" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                    </div>
                <?php elseif ($moduleKey === 'online_exam'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Online Exam Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Algorithms Quiz 1" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Duration (Minutes) *</label>
                            <input type="number" name="duration_minutes" class="form-control" value="60" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Total Questions *</label>
                            <input type="number" name="total_questions" class="form-control" value="20" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                    </div>
                <?php elseif ($moduleKey === 'exam_management_module'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Select Student *</label>
                        <select name="student_id" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <?php if (!empty($studentsList)): ?>
                                <?php foreach ($studentsList as $st): ?>
                                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['roll_no'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">John Doe (24CSE001)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Marks Obtained (out of 100) *</label>
                            <input type="number" step="0.5" name="marks_obtained" class="form-control" placeholder="e.g. 85.5" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Excellent Performance" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        </div>
                    </div>
                <?php elseif ($moduleKey === 'transport'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Route Name</label>
                        <input type="text" name="route_name" class="form-control" placeholder="e.g. Downtown Express" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Vehicle Number</label>
                        <input type="text" name="vehicle_no" class="form-control" placeholder="e.g. BUS-102" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Driver Phone</label>
                        <input type="text" name="driver_phone" class="form-control" placeholder="e.g. +1 555-0188" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'ticket_management' || $moduleKey === 'support' || $moduleKey === 'task_management'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Ticket Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Wi-Fi Access Request in Library" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Priority</label>
                        <select name="priority" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                <?php elseif ($moduleKey === 'visitor'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Visitor Name</label>
                        <input type="text" name="visitor_name" class="form-control" placeholder="e.g. Sarah Connor" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. +1 555-0199" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Purpose</label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Parent Inquiry / Vendor Visit" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'stock' || $moduleKey === 'asset_management'): ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Item Name</label>
                        <input type="text" name="item_name" class="form-control" placeholder="e.g. Projector / Classroom Desk" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                <?php elseif ($moduleKey === 'hr_management' || $moduleKey === 'staff_data' || $moduleKey === 'recruitment' || $moduleKey === 'payroll'): ?>
                    <div style="margin-bottom:15px; background: #F8FAFC; padding: 12px 14px; border-radius: 8px; border: 1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong style="font-size:13px; color:#1E293B;">👨‍🏫 Dynamic Staff Data Entry</strong>
                            <div style="font-size:11px; color:#64748B;">Add single or multiple staff members in one request</div>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addStaffRow()" style="font-size:12px; padding:6px 12px; font-weight:700; background:#EFF6FF; color:#1D4ED8; border-color:#BFDBFE;">+ Add Another Staff</button>
                    </div>

                    <div id="staffRowsContainer" style="display:flex; flex-direction:column; gap:16px;">
                        <div class="staff-row-card" style="background:#FFF; border:1px solid #CBD5E1; border-radius:10px; padding:14px; position:relative;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:6px; border-bottom:1px solid #F1F5F9;">
                                <span class="staff-row-title" style="font-size:12px; font-weight:700; color:var(--primary);">Staff Entry #1</span>
                                <button type="button" onclick="removeStaffRow(this)" class="btn-remove-staff-row" style="background:none; border:none; color:var(--danger); font-size:12px; font-weight:700; cursor:pointer; display:none;">✕ Remove</button>
                            </div>
                            
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">First Name *</label>
                                    <input type="text" name="first_name[]" class="form-control" placeholder="e.g. Jayshree" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Last Name *</label>
                                    <input type="text" name="last_name[]" class="form-control" placeholder="e.g. Patade" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Designation / Assigned Job Role *</label>
                                    <select name="designation[]" class="form-control" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                        <?php if (!empty($rolesList)): ?>
                                            <?php foreach ($rolesList as $roleItem): ?>
                                                <option value="<?= htmlspecialchars($roleItem['display_name'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($roleItem['display_name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="HOD / Head of Department">HOD / Head of Department</option>
                                             <option value="Teaching Staff / Teacher">Teaching Staff / Teacher</option>
                                            <option value="Administrative Officer / Office Administrator">Administrative Officer / Office Administrator</option>
                                            <option value="Accountant / Accounts Executive">Accountant / Accounts Executive</option>
                                            <option value="HR Executive / HR Administrator">HR Executive / HR Administrator</option>
                                            <option value="Librarian / Library Assistant">Librarian / Library Assistant</option>
                                            <option value="Transport & Hostel Administrator">Transport & Hostel Administrator</option>
                                            <option value="Inventory / Store & Asset Manager">Inventory / Store & Asset Manager</option>
                                            <option value="Receptionist / Front Office Executive">Receptionist / Front Office Executive</option>
                                            <option value="ERP Administrator / System Administrator">ERP Administrator / System Administrator</option>
                                            <option value="Principal / Super Administrator">Principal / Super Administrator</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                    <input type="hidden" name="department_id[]" value="1">
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Phone Number</label>
                                    <input type="text" name="phone[]" class="form-control" placeholder="e.g. 9876543210" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Email Address</label>
                                    <input type="email" name="email[]" class="form-control" placeholder="e.g. staff@campus360.edu" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Joining Date</label>
                                    <input type="date" name="joining_date[]" class="form-control" value="<?= date('Y-m-d') ?>" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Salary (₹)</label>
                                    <input type="number" step="0.01" name="salary[]" class="form-control" placeholder="e.g. 25000.00" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px; padding-top:10px; border-top:1px dashed #CBD5E1;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px; color:var(--primary);">🔐 Staff User ID / Username</label>
                                    <input type="text" name="username[]" class="form-control" placeholder="e.g. jayshree_patade" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px; color:var(--primary);">🔑 Staff Password</label>
                                    <input type="password" name="password[]" class="form-control" placeholder="e.g. StaffPass@123" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #E2E8F0; font-size:12px;">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Title / Name</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter record title/name" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Details / Content</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Enter record details..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;"></textarea>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createRecordModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Record(s) to Database</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT RECORD MODAL FORM -->
<div class="modal-overlay" id="editRecordModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">Edit Record for <?= htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <button type="button" onclick="document.getElementById('editRecordModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=module_update" method="POST" enctype="multipart/form-data">
            <div class="modal-body" id="editRecordModalBody">
                <!-- Dynamically injected fields -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editRecordModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Database Record</button>
            </div>
        </form>
    </div>
</div>

<!-- RECORD DETAIL INSPECTOR MODAL -->
<div class="modal-overlay" id="recordDetailModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">Record Details Inspector</h3>
            <button type="button" onclick="document.getElementById('recordDetailModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body" id="recordDetailModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('recordDetailModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<script>
function addStaffRow() {
    const container = document.getElementById('staffRowsContainer');
    if (!container) return;
    
    const rows = container.querySelectorAll('.staff-row-card');
    const firstRow = rows[0];
    if (!firstRow) return;
    
    const newRow = firstRow.cloneNode(true);
    
    // Clear input values in new row
    newRow.querySelectorAll('input').forEach(input => {
        if (input.type === 'date') {
            input.value = new Date().toISOString().split('T')[0];
        } else {
            input.value = '';
        }
    });
    
    container.appendChild(newRow);
    updateStaffRowTitles();
}

function removeStaffRow(btn) {
    const container = document.getElementById('staffRowsContainer');
    if (!container) return;
    const card = btn.closest('.staff-row-card');
    if (card && container.querySelectorAll('.staff-row-card').length > 1) {
        card.remove();
        updateStaffRowTitles();
    }
}

function updateStaffRowTitles() {
    const container = document.getElementById('staffRowsContainer');
    if (!container) return;
    const rows = container.querySelectorAll('.staff-row-card');
    rows.forEach((row, idx) => {
        const titleSpan = row.querySelector('.staff-row-title');
        if (titleSpan) {
            titleSpan.textContent = `Staff Entry #${idx + 1}`;
        }
        const removeBtn = row.querySelector('.btn-remove-staff-row');
        if (removeBtn) {
            removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        }
    });
}

function showRecordDetailModal(data) {
    const body = document.getElementById('recordDetailModalBody');
    const moduleKey = "<?= $moduleKey ?>";
    
    if (moduleKey === 'fees' || data.amount_paid !== undefined || data.installment_id !== undefined || data.fee_structure_id !== undefined) {
        const id = data.id || '101';
        const title = data.title || data.fee_type || 'Student Fee Installment / Collection';
        const amt = parseFloat(data.amount_paid || data.amount || 0).toFixed(2);
        const mode = (data.payment_mode || 'online').toUpperCase();
        const txn = data.transaction_id || `REC-${id}`;
        const dateStr = data.paid_at || data.created_at || data.due_date || new Date().toLocaleDateString();
        const status = (data.status || 'paid').toUpperCase();
        
        body.innerHTML = `
            <div id="printableModuleReceipt" style="border:1px solid #CBD5E1; padding:24px; border-radius:12px; background:#FFFFFF; font-family:inherit;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; border-bottom:3px solid #0066FF; padding-bottom:12px;">
                    <div>
                        <h2 style="font-size:20px; font-weight:800; color:#0F172A; margin:0;">Campus360 Enterprise ERP</h2>
                        <p style="font-size:12px; color:#64748B; margin:4px 0 0 0;">Official Student Fee Payment Receipt</p>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:inline-block; background:#EFF6FF; color:#0066FF; font-weight:800; padding:4px 10px; border-radius:6px; font-size:12px;">RECEIPT #${txn}</span>
                        <div style="font-size:11px; color:#64748B; margin-top:4px;">Date: ${dateStr}</div>
                    </div>
                </div>

                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px; margin-bottom:16px; display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:13px;">
                    <div><span style="color:#64748B; font-weight:600;">Fee Record ID:</span> <br><strong style="color:#0F172A;">#${id}</strong></div>
                    <div><span style="color:#64748B; font-weight:600;">Payment Mode:</span> <br><strong style="color:#0066FF;">${mode}</strong></div>
                    <div><span style="color:#64748B; font-weight:600;">Transaction Ref:</span> <br><strong style="font-family:monospace;">${txn}</strong></div>
                    <div><span style="color:#64748B; font-weight:600;">Status:</span> <br><span style="color:#10B981; font-weight:800;">${status} ✅</span></div>
                </div>

                <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:16px;">
                    <thead>
                        <tr style="background:#F1F5F9; text-align:left; border-bottom:1px solid #CBD5E1;">
                            <th style="padding:8px 10px;">ITEM / DESCRIPTION</th>
                            <th style="padding:8px 10px; text-align:right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:10px; font-weight:700; border-bottom:1px solid #E2E8F0;">${title}</td>
                            <td style="padding:10px; text-align:right; font-weight:700; border-bottom:1px solid #E2E8F0;">₹${amt}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:14px; font-weight:700; color:#166534;">TOTAL SETTLED AMOUNT:</span>
                    <span style="font-size:22px; font-weight:800; color:#15803D;">₹${amt}</span>
                </div>

                <div style="margin-top:20px; text-align:center; font-size:11px; color:#94A3B8; border-top:1px dashed #E2E8F0; padding-top:10px;">
                    Official Receipt issued by Campus360 ERP System. No signature required.
                </div>
            </div>
            <div style="margin-top:16px; text-align:right;">
                <button type="button" class="btn btn-secondary" onclick="printModuleFeeReceipt()" style="font-weight:700;">🖨️ Print / Save PDF Receipt</button>
            </div>
        `;
    } else {
        let html = '<div style="display:flex; flex-direction:column; gap:10px;">';
        for (const [key, val] of Object.entries(data)) {
            let valDisplay = val !== null ? String(val) : '-';
            if (key === 'photo' && val) {
                if (String(val).toLowerCase().includes('.pdf')) {
                    valDisplay = `<a href="${String(val).replace(/"/g, '&quot;')}" target="_blank" class="btn btn-secondary" style="color:#DC2626; border-color:#FCA5A5; background:#FEF2F2; font-size:11px; font-weight:700; text-decoration:none; padding:4px 12px; display:inline-flex; align-items:center; gap:4px;">📄 Open Student Photo PDF Document</a>`;
                } else {
                    valDisplay = `<a href="${String(val).replace(/"/g, '&quot;')}" target="_blank"><img src="${String(val).replace(/"/g, '&quot;')}" style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid #6366F1;"></a>`;
                }
            }
            html += `<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #E2E8F0;">
                <strong style="color:#64748B; text-transform:capitalize;">${key.replace(/_/g, ' ')}:</strong>
                <span style="color:#0F172A; font-weight:600;">${valDisplay}</span>
            </div>`;
        }
        html += '</div>';
        body.innerHTML = html;
    }
    document.getElementById('recordDetailModal').classList.add('active');
}

function printModuleFeeReceipt() {
    const printable = document.getElementById('printableModuleReceipt');
    if (!printable) return;
    const printWin = window.open('', '_blank', 'width=650,height=700');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fee Payment Official Receipt - Campus360</title>
            <style>
                body { margin:0; padding:40px; background:#F8FAFC; font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display:flex; justify-content:center; }
                @media print {
                    body { background:none; padding:0; }
                }
            </style>
        </head>
        <body>
            \${printable.outerHTML}
            <script>
                window.onload = function() { window.print(); };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

function showEditRecordModal(data) {
    const body = document.getElementById('editRecordModalBody');
    let html = `<input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                <input type="hidden" name="module_key" value="<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="record_id" value="${data.id}">
                <div style="display:flex; flex-direction:column; gap:12px;">`;

    const currentTable = "<?= $config['table'] ?>";

    for (const [key, val] of Object.entries(data)) {
        if (key === 'id' || key === 'tenant_id' || key === 'created_at' || key === 'updated_at' || key === 'student_user_id' || key === 'staff_username' || key === 'user_id') continue;

        if (key === 'status') {
            if (currentTable === 'enquiries') {
                html += `<div style="margin-bottom:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">Status</label>
                    <select name="status" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        <option value="pending" ${val === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="contacted" ${val === 'contacted' ? 'selected' : ''}>Contacted</option>
                        <option value="admitted" ${val === 'admitted' || val === 'approved' || val === 'active' ? 'selected' : ''}>Admitted / Approved</option>
                        <option value="closed" ${val === 'closed' ? 'selected' : ''}>Closed</option>
                    </select>
                </div>`;
            } else if (currentTable === 'admissions' || currentTable === 'leave_requests') {
                html += `<div style="margin-bottom:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">Status</label>
                    <select name="status" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        <option value="approved" ${val === 'approved' || val === 'active' ? 'selected' : ''}>Approved</option>
                        <option value="pending" ${val === 'pending' || val === 'pending_verification' ? 'selected' : ''}>Pending Verification</option>
                        <option value="rejected" ${val === 'rejected' ? 'selected' : ''}>Rejected</option>
                    </select>
                </div>`;
            } else if (currentTable === 'notices') {
                html += `<div style="margin-bottom:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">Status</label>
                    <select name="status" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        <option value="published" ${val === 'published' ? 'selected' : ''}>Published</option>
                        <option value="archived" ${val === 'archived' ? 'selected' : ''}>Archived</option>
                    </select>
                </div>`;
            } else if (currentTable === 'tickets') {
                html += `<div style="margin-bottom:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">Status</label>
                    <select name="status" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        <option value="open" ${val === 'open' ? 'selected' : ''}>Open</option>
                        <option value="in_progress" ${val === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="resolved" ${val === 'resolved' ? 'selected' : ''}>Resolved</option>
                        <option value="closed" ${val === 'closed' ? 'selected' : ''}>Closed</option>
                    </select>
                </div>`;
            } else {
                html += `<div style="margin-bottom:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">Status</label>
                    <select name="status" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
                        <option value="active" ${val === 'active' || val === 'approved' || val === 'paid' ? 'selected' : ''}>Active / Approved / Paid</option>
                        <option value="pending" ${val === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="suspended" ${val === 'suspended' || val === 'failed' || val === 'closed' ? 'selected' : ''}>Suspended / Inactive / Closed</option>
                    </select>
                </div>`;
            }
        } else if (key === 'salary') {
            html += `<div style="margin-bottom:8px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Salary (₹)</label>
                <input type="number" step="0.01" name="salary" value="${val !== null ? String(val).replace(/"/g, '&quot;') : '0.00'}" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
            </div>`;
        } else if (key === 'photo') {
            const isPdf = val && String(val).toLowerCase().includes('.pdf');
            const hasVal = val && String(val).trim() !== '';
            let previewHtml = '';
            if (hasVal) {
                if (isPdf) {
                    previewHtml = `<div style="margin-top:6px; background:#FEF2F2; border:1px solid #FCA5A5; padding:8px 12px; border-radius:6px; font-size:12px; display:flex; justify-content:space-between; align-items:center;"><span style="color:#B91C1C; font-weight:800;">📄 Current Photo (PDF Document):</span> <a href="${String(val).replace(/"/g, '&quot;')}" target="_blank" class="btn btn-secondary" style="color:#DC2626; font-weight:700; text-decoration:none; background:#FFF; border:1px solid #EF4444; padding:3px 10px; border-radius:4px; font-size:11px;">📄 Open PDF File</a></div>`;
                } else {
                    previewHtml = `<div style="margin-top:6px; background:#F8FAFC; border:1px solid #E2E8F0; padding:8px 12px; border-radius:6px; font-size:12px; display:flex; align-items:center; gap:10px;"><span style="font-weight:700; color:#334155;">Current Photo Image:</span> <img src="${String(val).replace(/"/g, '&quot;')}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #6366F1;"> <a href="${String(val).replace(/"/g, '&quot;')}" target="_blank" style="color:#2563EB; font-weight:700; text-decoration:underline; font-size:11px;">View Full Image</a></div>`;
                }
            }
            html += `<div style="margin-bottom:12px; background:#F8FAFC; border:1px solid #E2E8F0; padding:12px; border-radius:10px;">
                <label style="display:block; font-size:12px; font-weight:800; color:#1E293B; margin-bottom:4px;">📸 Student Photo (Upload PDF format or Images)</label>
                <input type="file" name="photo" accept="image/*,.pdf" class="form-control" style="width:100%; padding:8px; border-radius:8px; border:1px solid #CBD5E1; font-size:12px; background:#FFF;">
                <input type="hidden" name="existing_photo" value="${hasVal ? String(val).replace(/"/g, '&quot;') : ''}">
                ${previewHtml}
                <span style="display:block; font-size:10px; color:#64748B; margin-top:4px;">Supports JPG, PNG, WEBP, and <strong>PDF</strong> document format (Max 10MB)</span>
            </div>`;
        } else {
            html += `<div style="margin-bottom:8px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; text-transform:capitalize;">${key.replace(/_/g, ' ')}</label>
                <input type="text" name="${key}" value="${val !== null ? String(val).replace(/"/g, '&quot;') : ''}" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
            </div>`;
        }
    }

    if (currentTable === 'staff') {
        html += `<div style="margin-bottom:8px; margin-top:12px; padding-top:10px; border-top:1px dashed #CBD5E1;">
            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔐 Staff User ID / Username</label>
            <input type="text" name="username" value="${data.staff_username ? String(data.staff_username).replace(/"/g, '&quot;') : (data.username ? String(data.username).replace(/"/g, '&quot;') : '')}" class="form-control" placeholder="e.g. staff_username" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
        </div>
        <div style="margin-bottom:8px;">
            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔑 Reset Password (Leave blank to keep current password)</label>
            <input type="password" name="password" placeholder="Enter new password" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
        </div>`;
    } else if (currentTable === 'students') {
        html += `<div style="margin-bottom:8px; margin-top:12px; padding-top:10px; border-top:1px dashed #CBD5E1;">
            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🎓 Student User ID / Username</label>
            <input type="text" name="username" value="${data.student_user_id ? String(data.student_user_id).replace(/"/g, '&quot;') : (data.username ? String(data.username).replace(/"/g, '&quot;') : '')}" class="form-control" placeholder="e.g. student_username" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
        </div>
        <div style="margin-bottom:8px;">
            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--primary);">🔑 Reset Password (Leave blank to keep current password)</label>
            <input type="password" name="password" placeholder="Enter new password" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #E2E8F0;">
        </div>`;
    }

    html += '</div>';
    body.innerHTML = html;
    document.getElementById('editRecordModal').classList.add('active');
}

function handleViewClick(btn) {
    try {
        const raw = btn.getAttribute('data-record');
        const data = JSON.parse(raw);
        showRecordDetailModal(data);
    } catch (e) {
        console.error("View Modal Error:", e);
    }
}

function handleEditClick(btn) {
    try {
        const raw = btn.getAttribute('data-record');
        const data = JSON.parse(raw);
        showEditRecordModal(data);
    } catch (e) {
        console.error("Edit Modal Error:", e);
    }
}

// Live Auto-Poll for Automatic Data Updates when Teacher submits new data
let lastStateHash = null;
function checkRealtimeUpdates() {
    fetch('index.php?page=api_dashboard_updates')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (lastStateHash && lastStateHash !== data.hash) {
                    window.location.reload();
                }
                lastStateHash = data.hash;
            }
        })
        .catch(err => console.debug('Sync check error:', err));
}
setInterval(checkRealtimeUpdates, 5000);
checkRealtimeUpdates();
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
