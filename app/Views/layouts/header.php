<?php
/**
 * Campus360 Enterprise ERP - Core Header & Top Navigation Layout
 */

use App\Core\Session;
use App\Core\Database;
use App\Core\Auth;

Session::start();

// Fetch Active Departments for Global Department Selection Filter
$headerDepartments = [];
$activeDeptId = Session::get('selected_dept_id');
$activeDeptName = Session::get('selected_dept_name', 'All Departments');

try {
    $db = Database::getInstance();
    $user = Auth::user();
    $tenantId = $user['tenant_id'] ?? null;
    $isSuperAdmin = ($user['role_name'] ?? '') === 'super_admin';

    if ($isSuperAdmin || $tenantId === null) {
        $headerDepartments = $db->query("SELECT id, name, code FROM departments ORDER BY id ASC")->fetchAll();
    } else {
        $deptStmt = $db->prepare("SELECT id, name, code FROM departments WHERE tenant_id = :tid ORDER BY id ASC");
        $deptStmt->execute([':tid' => $tenantId]);
        $headerDepartments = $deptStmt->fetchAll();
    }

    // Auto-sync label if department is selected
    if ($activeDeptId) {
        foreach ($headerDepartments as $d) {
            if ((int)$d['id'] === (int)$activeDeptId) {
                $activeDeptName = $d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')';
                break;
            }
        }
    }
    // Current Route detection for Sidebar Active state
    $currentPage = $_GET['page'] ?? 'dashboard';
    $currentModule = $_GET['name'] ?? '';
} catch (\Throwable $e) {
    $headerDepartments = [];
    $currentPage = 'dashboard';
    $currentModule = '';
}

// Preserve existing $departments if supplied by view
if (!isset($departments)) {
    $departments = $headerDepartments;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus360 Enterprise ERP</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .dropdown-menu {
            position: absolute;
            top: 50px;
            left: 160px;
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            display: none;
            z-index: 500;
            width: 260px;
            overflow: hidden;
        }
        .dropdown-item {
            padding: 10px 16px;
            font-size: 13px;
            color: var(--text-main);
            display: block;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }
    </style>
    <script>
        function selectDepartment(deptId) {
            const url = new URL(window.location.href);
            url.searchParams.set('dept_id', deptId);
            window.location.href = url.toString();
        }
    </script>
</head>
<body>
<div class="app-container">
    <!-- Collapsible Left Icon Rail Navigation -->
    <aside class="icon-rail">
        <a href="index.php?page=dashboard" class="rail-brand" style="text-decoration:none;" title="Campus360 Enterprise ERP">C</a>
        <nav class="rail-nav">
            <a href="index.php?page=dashboard" class="rail-item <?= ($currentPage === 'dashboard' && empty($currentModule)) ? 'active' : '' ?>" title="Dashboard Launcher">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </a>
            <?php if (in_array($user['role_name'] ?? '', ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher', 'hod'], true)): ?>
            <a href="index.php?page=academic_hierarchy" class="rail-item <?= ($currentPage === 'academic_hierarchy') ? 'active' : '' ?>" title="Academic Hierarchy (Dept ➔ Course ➔ FY, SY, TY)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('communication')): ?>
            <a href="index.php?page=module&name=communication" class="rail-item <?= ($currentModule === 'communication') ? 'active' : '' ?>" title="Communication & Messages">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="badge-dot"></span>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('time_table')): ?>
            <a href="index.php?page=module&name=time_table" class="rail-item <?= ($currentModule === 'time_table') ? 'active' : '' ?>" title="Academic Schedule & Timetable">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('teacher_panel')): ?>
            <a href="index.php?page=teacher_timetable" class="rail-item <?= (in_array($currentPage, ['teacher_timetable', 'lecture_details'], true)) ? 'active' : '' ?>" title="My Weekly Timetable & Classes">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('online_exam')): ?>
            <a href="index.php?page=module&name=online_exam" class="rail-item <?= ($currentModule === 'online_exam') ? 'active' : '' ?>" title="Online Exam Portal">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('exam_management')): ?>
            <a href="index.php?page=module&name=exam_management" class="rail-item <?= ($currentModule === 'exam_management') ? 'active' : '' ?>" title="Exam Management">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('exam_management_module')): ?>
            <a href="index.php?page=module&name=exam_management_module" class="rail-item <?= ($currentModule === 'exam_management_module') ? 'active' : '' ?>" title="Exam Gradebook & Transcripts">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('fees')): ?>
            <a href="index.php?page=module&name=fees" class="rail-item <?= ($currentModule === 'fees') ? 'active' : '' ?>" title="Finance & Fees Collection">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </a>
            <?php endif; ?>
            <?php if (\App\Core\Permission::can('role_permission')): ?>
            <a href="index.php?page=module&name=role_permission" class="rail-item <?= ($currentModule === 'role_permission') ? 'active' : '' ?>" title="Settings & Security RBAC">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Main Wrapper Area -->
    <div class="main-wrapper">
        <!-- Top Navigation Header Bar -->
        <header class="top-header" style="position:sticky; top:0; z-index:90; background:#FFFFFF;">
            <div class="header-left" style="display:flex; align-items:center; gap:16px;">
                <a href="index.php?page=dashboard" style="text-decoration:none; color:inherit;"><h1 class="page-title">Dashboard</h1></a>
                
                <!-- Dynamic Department Filter Dropdown -->
                <div style="position:relative; display:flex; align-items:center; gap:8px;">
                    <div class="dept-selector" id="deptSelectorBtn" style="cursor:pointer; user-select:none;">
                        Department <span class="dept-badge" id="currentDeptLabel"><?= htmlspecialchars($activeDeptName, ENT_QUOTES, 'UTF-8') ?> ˅</span>
                    </div>
                    
                    <div class="dropdown-menu" id="deptDropdownMenu" style="left:0; top:42px;">
                        <a href="javascript:void(0)" onclick="selectDepartment('all')" class="dropdown-item" style="font-weight:700; border-bottom:1px solid #E2E8F0;">
                            🏢 All Departments (Global View)
                        </a>
                        <?php foreach ($headerDepartments as $d): ?>
                            <a href="javascript:void(0)" onclick="selectDepartment(<?= (int)$d['id'] ?>)" class="dropdown-item">
                                💻 <?= htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($d['code'] ?? 'DEPT', ENT_QUOTES, 'UTF-8') ?>)
                            </a>
                        <?php endforeach; ?>
                        
                        <?php if (in_array($user['role_name'] ?? '', ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher', 'hod'], true)): ?>
                            <a href="javascript:void(0)" onclick="openAddDepartmentModal()" class="dropdown-item" style="font-weight:700; color:#4F46E5; background:#EEF2FF; border-top:1px solid #C7D2FE; display:flex; align-items:center; gap:6px; margin-top:4px;">
                                ➕ Add Dept / Division (FY, SY, TY)...
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (in_array($user['role_name'] ?? '', ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher', 'hod'], true)): ?>
                        <button type="button" onclick="openAddDepartmentModal()" class="btn btn-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color:#FFF; border:none; border-radius:18px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 6px rgba(79,70,229,0.25);">
                            ➕ Add Dept / Division
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Custom View Selector Dropdown -->
                <div style="position:relative;" class="tab-switcher">
                    <button type="button" class="tab-btn active" data-view="dashboard" onclick="switchCustomView('standard', 'View Dashboard')">View Dashboard</button>
                    
                    <button type="button" class="tab-btn" id="customViewBtn" onclick="toggleCustomViewDropdown(event)">
                        <span id="customViewLabel">View Custom 1</span> ˅
                    </button>

                    <div class="dropdown-menu" id="customViewDropdownMenu" style="top:42px; left:120px; width:250px;">
                        <a href="#" class="dropdown-item" onclick="switchCustomView('custom1', 'View Custom 1 (Standard Grid)'); return false;">
                            📊 View Custom 1 (Standard Grid)
                        </a>
                        <a href="#" class="dropdown-item" onclick="switchCustomView('custom2', 'View Custom 2 (Executive Matrix)'); return false;">
                            🏛️ View Custom 2 (Executive Matrix)
                        </a>
                        <a href="#" class="dropdown-item" onclick="switchCustomView('custom3', 'View Custom 3 (Real-time Inspector)'); return false;">
                            ⚡ View Custom 3 (Real-time Inspector)
                        </a>
                    </div>
                </div>
            </div>

            <?php
            $allModulesList = [
                'enquiry', 'admission', 'fees', 'accounts', 'student_attendance',
                'academic_calendar', 'id_cards', 'time_table', 'teacher_panel', 'certificate', 'online_exam',
                'exam_management', 'exam_management_module', 'transport', 'stock', 'library',
                'visitor', 'student_mis', 'hr_management', 'staff_data', 'staff_attendance',
                'leave_management', 'payroll', 'ticket_management', 'asset_management',
                'task_management', 'hostel_management', 'day_boarding', 'infirmary',
                'app_analysis', 'recruitment', 'role_permission', 'news_events',
                'communication', 'tutorial', 'support'
            ];
            $userAllowedModules = array_values(array_filter($allModulesList, fn($m) => \App\Core\Permission::can($m)));
            $userModuleCount = count($userAllowedModules);
            ?>
            <script>
                window.allowedModuleKeys = <?= json_encode($userAllowedModules) ?>;
            </script>
            <div class="header-right">
                <div class="search-box" style="position:relative;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="globalSearchInput" placeholder="Search <?= $userModuleCount ?> modules..." autocomplete="off" />
                </div>
                <div class="action-btn" title="Quick Add New Entry" id="quickAddBtn" style="cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:800; width:36px; height:36px; border-radius:50%; background:var(--primary-light); color:var(--primary); transition:all 0.2s;">+</div>
                <div class="user-profile" style="display:flex; align-items:center; gap:8px;">
                    <div class="avatar" style="background:var(--primary); color:#FFF; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">
                        <?= strtoupper(substr($user['username'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-size:13px; font-weight:700; line-height:1.1;"><?= htmlspecialchars($user['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?></span>
                        <span style="font-size:11px; color:var(--text-muted);"><?= htmlspecialchars($user['role_display'] ?? ucwords(str_replace('_', ' ', $user['role_name'] ?? 'school_admin')), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                </div>
                <a href="index.php?page=logout" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none; color:var(--danger); border-color:#FCA5A5;">Logout</a>
            </div>
        </header>

        <!-- Global Search Overlay -->
        <div id="searchResultsOverlay" style="display:none; position:absolute; top:60px; right:200px; background:#FFF; border:1px solid #E2E8F0; border-radius:12px; width:320px; max-height:400px; overflow-y:auto; box-shadow:0 12px 32px -4px rgba(15, 23, 42, 0.15); z-index:1000; padding:12px;">
            <div style="font-size:11px; font-weight:700; color:#64748B; padding:4px 8px 8px 8px; text-transform:uppercase; border-bottom:1px solid #F1F5F9; margin-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                <span>Module Search Results</span>
                <span style="font-size:10px; background:#E2E8F0; padding:2px 6px; border-radius:4px; font-weight:600;">ESC to close</span>
            </div>
            <div id="searchResultsList"></div>
        </div>

<!-- Modal: Add & Manage Departments & Divisions Global Modal -->
<?php if (in_array($user['role_name'] ?? '', ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher'], true)): ?>
<div class="modal-overlay" id="addDepartmentGlobalModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.65); z-index:2000; align-items:center; justify-content:center;">
    <div class="modal-card" style="max-width: 650px; width:92%; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); margin:auto;">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h5 class="modal-title font-weight-bold mb-0" style="color:#ffffff; font-size:18px; display:flex; align-items:center; gap:8px;">
                    🏛️ Department & Division Management
                </h5>
                <span style="font-size:12px; color:#94A3B8;">Teachers & Administrators: Add departments or add academic divisions (FY, SY, TY, etc.)</span>
            </div>
            <button type="button" onclick="closeAddDepartmentModal()" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        
        <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
            
            <!-- Direct Shortcut to Full Academic Hierarchy Explorer -->
            <a href="index.php?page=academic_hierarchy" style="display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:#FFF; padding:12px 18px; border-radius:10px; text-decoration:none; margin-bottom:20px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:22px;">🏛️</span>
                    <div>
                        <strong style="font-size:14px; display:block;">Academic Hierarchy Explorer & Roster</strong>
                        <span style="font-size:11px; color:#94A3B8;">Department ➔ Courses ➔ 3 Years (FY, SY, TY) ➔ Student Cohorts</span>
                    </div>
                </div>
                <span style="background:rgba(255,255,255,0.2); color:#FFF; font-size:11px; font-weight:800; padding:4px 10px; border-radius:6px;">Open Explorer ➔</span>
            </a>

            <!-- Section 1: Add Department -->
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:20px;">
                <h6 style="font-size:14px; font-weight:800; color:#0F172A; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                    🏢 1. Add New Department
                </h6>
                <form method="POST" action="index.php?page=store_department">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <input type="hidden" name="redirect_page" value="<?= htmlspecialchars($currentPage ?? 'dashboard') ?>">
                    
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Department Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Science Department, Commerce Department" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; font-weight:600;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Code / Abbreviation *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. SCI-01, COM-01" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #CBD5E1; font-size:13px; text-transform:uppercase;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width:100%; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border:none; padding:10px; font-weight:700; border-radius:8px; cursor:pointer; color:#FFF; font-size:13px;">
                        + Save Department & Auto-Seed FY, SY, TY Standards
                    </button>
                </form>
            </div>

            <!-- Section 2: Add Course to Department -->
            <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:16px; margin-bottom:20px;">
                <h6 style="font-size:14px; font-weight:800; color:#1E40AF; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                    🎓 2. Add Course to Department (3 Years: FY, SY, TY)
                </h6>
                <form method="POST" action="index.php?page=course_save">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                    <input type="hidden" name="redirect_page" value="<?= htmlspecialchars($currentPage ?? 'dashboard') ?>">
                    <input type="hidden" name="id" value="0">
                    <input type="hidden" name="status" value="active">
                    <input type="hidden" name="duration_years" value="3">

                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#1E3A8A; margin-bottom:4px;">Parent Department *</label>
                        <select name="department_id" class="form-control" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #93C5FD; font-size:13px; font-weight:600; background:#FFF;">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($headerDepartments as $dItem): ?>
                                <option value="<?= $dItem['id'] ?>"><?= htmlspecialchars($dItem['name'] . ' (' . ($dItem['code'] ?? 'DEPT') . ')', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:14px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#1E3A8A; margin-bottom:4px;">Course Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. B.Sc. IT, B.Com" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #93C5FD; font-size:13px; font-weight:600; background:#FFF;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#1E3A8A; margin-bottom:4px;">Course Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. BSCIT, BCOM" required style="width:100%; padding:9px 12px; border-radius:6px; border:1px solid #93C5FD; font-size:13px; text-transform:uppercase; background:#FFF;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width:100%; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border:none; padding:10px; font-weight:700; border-radius:8px; cursor:pointer; color:#FFF; font-size:13px;">
                        + Save Course & Provision 3 Years (FY, SY, TY)
                    </button>
                </form>
            </div>


            <!-- Section 3: List Active Departments -->
            <div>
                <h6 style="font-size:14px; font-weight:800; color:#0F172A; margin-bottom:10px;">📋 Existing Active Departments (<?= count($headerDepartments) ?>)</h6>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php if (empty($headerDepartments)): ?>
                        <div style="font-size:12px; color:#64748B; text-align:center; padding:12px;">No departments found in database.</div>
                    <?php else: ?>
                        <?php foreach ($headerDepartments as $deptItem): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; background:#FFF; border:1px solid #E2E8F0; padding:10px 14px; border-radius:8px;">
                                <div>
                                    <strong style="font-size:13px; color:#0F172A;"><?= htmlspecialchars($deptItem['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span style="font-size:11px; background:#E2E8F0; color:#475569; padding:2px 6px; border-radius:4px; font-weight:700; margin-left:6px;"><?= htmlspecialchars($deptItem['code'] ?? 'DEPT', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <button type="button" onclick="selectDepartment(<?= (int)$deptItem['id'] ?>); closeAddDepartmentModal();" style="padding:4px 10px; font-size:11px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; cursor:pointer;">Select</button>
                                    <form method="POST" action="index.php?page=delete_department" onsubmit="return confirm('Delete department <?= htmlspecialchars($deptItem['name'], ENT_QUOTES) ?>?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::getToken() ?>">
                                        <input type="hidden" name="id" value="<?= $deptItem['id'] ?>">
                                        <input type="hidden" name="redirect_page" value="<?= htmlspecialchars($currentPage ?? 'dashboard') ?>">
                                        <button type="submit" style="background:none; border:none; color:#EF4444; font-size:12px; font-weight:700; cursor:pointer; padding:4px 6px;">🗑️ Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="modal-footer bg-light p-3" style="display:flex; justify-content:flex-end; border-top:1px solid #E2E8F0;">
            <button type="button" class="btn btn-secondary" onclick="closeAddDepartmentModal()" style="padding:8px 16px; font-weight:700; border-radius:6px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<script>
function openAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentGlobalModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}
function closeAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentGlobalModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>
<?php endif; ?>

