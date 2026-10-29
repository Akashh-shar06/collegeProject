    </div><!-- End main-wrapper -->
</div><!-- End app-container -->

<!-- Modal Drawer Container -->
<div class="modal-overlay" id="moduleModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="modalTitle" style="font-size:16px; font-weight:700; color:var(--text-main);">Module Details</h3>
            <button type="button" id="closeModalBtn" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Dynamic JSON/HTML table populated via app.js -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('moduleModal').classList.remove('active')">Close</button>
            <button type="button" class="btn btn-primary" onclick="alert('Module action executed successfully!')">Execute Action</button>
        </div>
    </div>
</div>

<!-- Quick Add Modal Container -->
<div class="modal-overlay" id="quickAddModal">
    <div class="modal-card" style="max-width: 540px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #0066FF, #00C8FF); color: #FFF;">
            <h3 style="font-size:16px; font-weight:800; color:#FFF; display:flex; align-items:center; gap:8px;">
                <span>✨</span> Quick Action / Create New Entry
            </h3>
            <button type="button" onclick="document.getElementById('quickAddModal').classList.remove('active')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#FFF;">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
                <button type="button" class="btn btn-secondary quick-tab-btn" onclick="switchQuickTab(event, 'student')" style="padding:10px; font-weight:700; text-align:center; cursor:pointer; border:1px solid #0066FF; color:#0066FF; background:#EBF3FF;">+ Admit Student</button>
                <button type="button" class="btn btn-secondary quick-tab-btn" onclick="switchQuickTab(event, 'enquiry')" style="padding:10px; font-weight:700; text-align:center; cursor:pointer;">+ Submit Enquiry</button>
                <button type="button" class="btn btn-secondary quick-tab-btn" onclick="switchQuickTab(event, 'tenant')" style="padding:10px; font-weight:700; text-align:center; cursor:pointer;">+ Onboard Tenant</button>
                <button type="button" class="btn btn-secondary quick-tab-btn" onclick="switchQuickTab(event, 'class')" style="padding:10px; font-weight:700; text-align:center; cursor:pointer;">+ Add Class</button>
            </div>

            <!-- Tab 1: Admit Student -->
            <form id="quickTabStudent" action="index.php?page=admit_student" method="POST" class="quick-tab-form">
                <?= \App\Core\CSRF::inputField() ?>
                <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:var(--primary);">🎓 Direct Student Admission</div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">First Name</label>
                    <input type="text" name="first_name" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="e.g. John">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Last Name</label>
                    <input type="text" name="last_name" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="e.g. Doe">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Email</label>
                    <input type="email" name="email" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="student@example.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:700;">Submit Student Admission</button>
            </form>

            <!-- Tab 2: Submit Enquiry -->
            <form id="quickTabEnquiry" action="index.php?page=submit_enquiry" method="POST" class="quick-tab-form" style="display:none;">
                <?= \App\Core\CSRF::inputField() ?>
                <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:var(--primary);">🎧 Direct Enquiry Entry</div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Student / Applicant Name</label>
                    <input type="text" name="student_name" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="Full Name">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Contact Phone</label>
                    <input type="text" name="phone" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="+1 555-0192">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Course Interested</label>
                    <input type="text" name="course_interested" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="Computer Science">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:700;">Submit Enquiry</button>
            </form>

            <!-- Tab 3: Onboard Tenant -->
            <div id="quickTabTenant" class="quick-tab-form" style="display:none; text-align:center; padding:16px;">
                <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:var(--primary);">🏛️ Onboard New College/School Tenant</div>
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">Launch multi-step workflow wizard to onboard a brand new institution tenant.</p>
                <a href="index.php?page=register_tenant" class="btn btn-primary" style="text-decoration:none; display:inline-block;">Launch Tenant Onboarding Wizard</a>
            </div>

            <!-- Tab 4: Add Class -->
            <form id="quickTabClass" action="index.php?page=store_class" method="POST" class="quick-tab-form" style="display:none;">
                <?= \App\Core\CSRF::inputField() ?>
                <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:var(--primary);">🏫 Add Class / Grade Level</div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Class Name</label>
                    <input type="text" name="class_name" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="Grade 10">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Class Code</label>
                    <input type="text" name="class_code" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #CBD5E1;" required placeholder="G10">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:700;">Create Class</button>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('quickAddModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/dashboard/partials/payslip_modal.php'; ?>
<script src="assets/js/app.js"></script>
</body>
</html>

