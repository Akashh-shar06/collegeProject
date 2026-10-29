<?php
$allModules = [
    'enquiry' => ['title' => 'Enquiry Module', 'desc' => 'Admissions Leads & Follow-ups', 'icon' => '🎧', 'class' => 'icon-blue', 'category' => 'academic'],
    'admission' => ['title' => 'Admission Process', 'desc' => 'Enrollment & Documents', 'icon' => '💻', 'class' => 'icon-green', 'category' => 'academic'],
    'fees' => ['title' => 'Fees Collection', 'desc' => 'Fee Invoices & Dues', 'icon' => '💵', 'class' => 'icon-amber', 'category' => 'finance'],
    'accounts' => ['title' => 'Accounts Module', 'desc' => 'General Ledger & Expenses', 'icon' => '₹', 'class' => 'icon-purple', 'category' => 'finance'],
    'student_attendance' => ['title' => 'Daily Attendance', 'desc' => 'Daily Batch Marking', 'icon' => '📅', 'class' => 'icon-purple', 'category' => 'academic'],
    'academic_calendar' => ['title' => 'Academic Calendar', 'desc' => 'Terms & Milestones', 'icon' => '📆', 'class' => 'icon-blue', 'category' => 'academic'],
    'id_cards' => ['title' => 'ID Cards', 'desc' => 'Print & Barcode Gen', 'icon' => '🪪', 'class' => 'icon-green', 'category' => 'academic'],
    'time_table' => ['title' => 'Class Time Table', 'desc' => 'Schedules & Allocation', 'icon' => '🗓️', 'class' => 'icon-red', 'category' => 'academic'],
    'certificate' => ['title' => 'Certificate', 'desc' => 'TC, Bonafide & Degrees', 'icon' => '🏅', 'class' => 'icon-blue', 'category' => 'academic'],
    'online_exam' => ['title' => 'Online Exam', 'desc' => 'MCQ Portal & Quiz', 'icon' => 'Q', 'class' => 'icon-purple', 'category' => 'exam'],
    'exam_management' => ['title' => 'Exam Management', 'desc' => 'Hall Tickets & Schedules', 'icon' => '📖', 'class' => 'icon-red', 'category' => 'exam'],
    'exam_management_module' => ['title' => 'Student Marks Entry', 'desc' => 'Gradebook & Transcripts', 'icon' => '📕', 'class' => 'icon-red', 'category' => 'exam'],
    'transport' => ['title' => 'Transport Module', 'desc' => 'Routes, Drivers & Fleet', 'icon' => '🚌', 'class' => 'icon-blue', 'category' => 'operations'],
    'stock' => ['title' => 'Stock Management', 'desc' => 'Office & Lab Supplies', 'icon' => '📄', 'class' => 'icon-purple', 'category' => 'operations'],
    'library' => ['title' => 'Library Management', 'desc' => 'Books & Loan Issues', 'icon' => '📚', 'class' => 'icon-amber', 'category' => 'operations'],
    'visitor' => ['title' => 'Visitor Module', 'desc' => 'Gate Pass & Badges', 'icon' => '👤', 'class' => 'icon-blue', 'category' => 'operations'],
    'student_mis' => ['title' => 'Student MIS', 'desc' => 'Profiles & Records', 'icon' => '☁️', 'class' => 'icon-green', 'category' => 'academic'],
    'hr_management' => ['title' => 'HR Management', 'desc' => 'Staff Reviews & Roles', 'icon' => '👥', 'class' => 'icon-purple', 'category' => 'hr'],
    'staff_data' => ['title' => 'Staff Data', 'desc' => 'Faculty Profiles', 'icon' => '👨‍🏫', 'class' => 'icon-green', 'category' => 'hr'],
    'staff_attendance' => ['title' => 'Staff Attendance', 'desc' => 'Biometric & Daily Logs', 'icon' => '🗓️', 'class' => 'icon-purple', 'category' => 'hr'],
    'leave_management' => ['title' => 'Leave Management', 'desc' => 'Approvals & Balances', 'icon' => '📅', 'class' => 'icon-pink', 'category' => 'hr'],
    'payroll' => ['title' => 'Payroll', 'desc' => 'Salary & Payslips', 'icon' => '💰', 'class' => 'icon-amber', 'category' => 'finance'],
    'ticket_management' => ['title' => 'Ticket Management', 'desc' => 'IT Helpdesk Support', 'icon' => '📦', 'class' => 'icon-dark', 'category' => 'support'],
    'asset_management' => ['title' => 'Asset Management', 'desc' => 'Lab & Property Assets', 'icon' => '🏛️', 'class' => 'icon-blue', 'category' => 'operations'],
    'task_management' => ['title' => 'Task Management', 'desc' => 'Assignments & Deadlines', 'icon' => '🛒', 'class' => 'icon-blue', 'category' => 'support'],
    'hostel_management' => ['title' => 'Hostel Management', 'desc' => 'Room Allocations', 'icon' => '🛏️', 'class' => 'icon-red', 'category' => 'operations'],
    'day_boarding' => ['title' => 'Day Boarding', 'desc' => 'Meal Pass & Lockers', 'icon' => '💳', 'class' => 'icon-pink', 'category' => 'operations'],
    'infirmary' => ['title' => 'Infirmary', 'desc' => 'Medical & Health Logs', 'icon' => '🧰', 'class' => 'icon-amber', 'category' => 'operations'],
    'app_analysis' => ['title' => 'App Analysis', 'desc' => 'Usage Analytics & Logs', 'icon' => '📊', 'class' => 'icon-green', 'category' => 'support'],
    'recruitment' => ['title' => 'Recruitment Module', 'desc' => 'Hiring & Applicants', 'icon' => '👤+', 'class' => 'icon-blue', 'category' => 'hr'],
    'role_permission' => ['title' => 'Role & Permission', 'desc' => 'RBAC Matrix Control', 'icon' => '♿', 'class' => 'icon-purple', 'category' => 'support'],
    'news_events' => ['title' => 'News & Events', 'desc' => 'Notices & Bulletins', 'icon' => '📑', 'class' => 'icon-red', 'category' => 'support'],
    'communication' => ['title' => 'Communication', 'desc' => 'SMS & Email Broadcast', 'icon' => '✈️', 'class' => 'icon-blue', 'category' => 'support'],
    'tutorial' => ['title' => 'Tutorial', 'desc' => 'Study Notes & Uploads', 'icon' => '💬', 'class' => 'icon-dark', 'category' => 'academic'],
    'support' => ['title' => 'Support', 'desc' => 'Help Desk & Manuals', 'icon' => '🤝', 'class' => 'icon-amber', 'category' => 'support'],
];

$activeModules = [];
foreach ($allModules as $key => $mod) {
    if (\App\Core\Permission::can($key)) {
        $activeModules[$key] = $mod;
    }
}
?>

<!-- ERP Module Launcher Grid Partial -->
<section class="launcher-section">
    <div class="launcher-header">
        <div>
            <h2 style="font-size:18px; font-weight:800; color:var(--text-main);">Enterprise ERP Modules</h2>
            <p style="font-size:12px; color:var(--text-muted);">Select any module launcher to access management tools</p>
        </div>
        <span style="font-size:12px; font-weight:700; color:var(--primary); background:var(--primary-light); padding:4px 12px; border-radius:var(--radius-pill);"><?= count($activeModules) ?> Modules Active</span>
    </div>

    <div class="launcher-grid">
        <?php foreach ($activeModules as $key => $mod): ?>
            <a href="index.php?page=module&name=<?= $key ?>" class="module-card" data-module="<?= $key ?>" data-title="<?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?>" data-category="<?= $mod['category'] ?>" style="text-decoration:none;">
                <div class="module-icon <?= $mod['class'] ?>"><?= $mod['icon'] ?></div>
                <div class="module-info">
                    <span class="module-title"><?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="module-desc"><?= htmlspecialchars($mod['desc'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
