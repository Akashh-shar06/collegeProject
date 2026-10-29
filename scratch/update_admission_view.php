<?php
$file = __DIR__ . '/../app/Views/modules/view_module.php';
$content = file_get_contents($file);

$startMarker = "<?php elseif (\$moduleKey === 'admission'";
$endMarker = "<?php elseif (\$moduleKey === 'fees'): ?>";

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker);

if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
    $newBlock = <<<'EOD'
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
                    </script>
EOD;

    $content = substr_replace($content, $newBlock, $startPos, $endPos - $startPos);
    file_put_contents($file, $content);
    echo "SUCCESS: Replaced admission form block in view_module.php\n";
} else {
    echo "ERROR: Markers not found.\n";
}
