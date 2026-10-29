<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Validator;
use App\Core\CSRF;
use App\Core\Permission;
use App\Core\Session;
use PDO;

class ModuleController
{
    private array $modulesConfig = [
        'enquiry' => ['title' => 'Enquiry Module', 'table' => 'enquiries', 'icon' => '🎧', 'category' => 'Academic & Admissions'],
        'admission' => ['title' => 'Admission Process', 'table' => 'admissions', 'icon' => '💻', 'category' => 'Academic & Admissions'],
        'fees' => ['title' => 'Fees Collection', 'table' => 'fee_collections', 'icon' => '💵', 'category' => 'Financial'],
        'accounts' => ['title' => 'Accounts Module', 'table' => 'accounts_transactions', 'icon' => '₹', 'category' => 'Financial'],
        'student_attendance' => ['title' => 'Student Attendance', 'table' => 'student_attendance', 'icon' => '📅', 'category' => 'Academic'],
        'academic_calendar' => ['title' => 'Academic Calendar', 'table' => 'notices', 'icon' => '📆', 'category' => 'Academic'],
        'id_cards' => ['title' => 'ID Cards Module', 'table' => 'students', 'icon' => '🪪', 'category' => 'Academic'],
        'time_table' => ['title' => 'Class Time Table', 'table' => 'time_tables', 'icon' => '🗓️', 'category' => 'Academic'],
        'certificate' => ['title' => 'Certificate Module', 'table' => 'certificates', 'icon' => '🏅', 'category' => 'Academic'],
        'online_exam' => ['title' => 'Online Exam', 'table' => 'online_exams', 'icon' => 'Q', 'category' => 'Examination'],
        'exam_management' => ['title' => 'Exam Management', 'table' => 'exams', 'icon' => '📖', 'category' => 'Examination'],
        'exam_management_module' => ['title' => 'Exam Gradebook & Transcripts', 'table' => 'exam_results', 'icon' => '📕', 'category' => 'Examination'],
        'transport' => ['title' => 'Transport Module', 'table' => 'transport_routes', 'icon' => '🚌', 'category' => 'Operations'],
        'stock' => ['title' => 'Stock Management', 'table' => 'inventory_assets', 'icon' => '📄', 'category' => 'Operations'],
        'library' => ['title' => 'Library Management', 'table' => 'library_books', 'icon' => '📚', 'category' => 'Operations'],
        'visitor' => ['title' => 'Visitor Module', 'table' => 'visitors', 'icon' => '👤', 'category' => 'Operations'],
        'student_mis' => ['title' => 'Student MIS', 'table' => 'students', 'icon' => '☁️', 'category' => 'Academic'],
        'hr_management' => ['title' => 'HR Management', 'table' => 'staff', 'icon' => '👥', 'category' => 'Human Resources'],
        'staff_data' => ['title' => 'Staff Data', 'table' => 'staff', 'icon' => '👨‍🏫', 'category' => 'Human Resources'],
        'staff_management' => ['title' => 'Staff Management & Executive Portal', 'table' => 'staff', 'icon' => '👨‍💼', 'category' => 'Human Resources'],
        'staff_attendance' => ['title' => 'Staff Attendance', 'table' => 'staff_attendance', 'icon' => '🗓️', 'category' => 'Human Resources'],
        'leave_management' => ['title' => 'Leave Management', 'table' => 'leave_requests', 'icon' => '📅', 'category' => 'Human Resources'],
        'payroll' => ['title' => 'Payroll Module', 'table' => 'staff', 'icon' => '💰', 'category' => 'Financial'],
        'ticket_management' => ['title' => 'Ticket Management', 'table' => 'tickets', 'icon' => '📦', 'category' => 'Support & IT'],
        'asset_management' => ['title' => 'Asset Management', 'table' => 'inventory_assets', 'icon' => '🏛️', 'category' => 'Operations'],
        'task_management' => ['title' => 'Task Management', 'table' => 'tickets', 'icon' => '🛒', 'category' => 'Operations'],
        'hostel_management' => ['title' => 'Hostel Management', 'table' => 'hostel_rooms', 'icon' => '🛏️', 'category' => 'Operations'],
        'day_boarding' => ['title' => 'Day Boarding', 'table' => 'students', 'icon' => '💳', 'category' => 'Operations'],
        'infirmary' => ['title' => 'Infirmary Module', 'table' => 'students', 'icon' => '🧰', 'category' => 'Operations'],
        'app_analysis' => ['title' => 'App Analysis & Audit Logs', 'table' => 'security_audit_logs', 'icon' => '📊', 'category' => 'System'],
        'recruitment' => ['title' => 'Recruitment Module', 'table' => 'staff', 'icon' => '👤+', 'category' => 'Human Resources'],
        'role_permission' => ['title' => 'Role & Permission Matrix', 'table' => 'roles', 'icon' => '♿', 'category' => 'System Administration'],
        'news_events' => ['title' => 'News & Events', 'table' => 'notices', 'icon' => '📑', 'category' => 'Communication'],
        'communication' => ['title' => 'Communication Module', 'table' => 'notices', 'icon' => '✈️', 'category' => 'Communication'],
        'tutorial' => ['title' => 'Tutorial & Study Notes', 'table' => 'courses', 'icon' => '💬', 'category' => 'Academic'],
        'support' => ['title' => 'Support & Help Desk', 'table' => 'tickets', 'icon' => '🤝', 'category' => 'Support & IT'],
        'courses' => ['title' => 'Course Management', 'table' => 'courses', 'icon' => '🎓', 'category' => 'Academic & Admissions'],
        'departments' => ['title' => 'Department Management', 'table' => 'departments', 'icon' => '🏛️', 'category' => 'Academic & Admissions'],
        'subjects' => ['title' => 'Subject Management', 'table' => 'subjects', 'icon' => '📘', 'category' => 'Academic & Admissions'],
        'academic_structure' => ['title' => 'Academic Structure', 'table' => 'academic_sessions', 'icon' => '📆', 'category' => 'Academic & Admissions'],
        'student_promotion' => ['title' => 'Student Promotion', 'table' => 'student_promotions', 'icon' => '🚀', 'category' => 'Academic & Admissions'],
        'promotion_history' => ['title' => 'Promotion History', 'table' => 'student_promotions', 'icon' => '📜', 'category' => 'Academic & Admissions'],
    ];


    // READ Operation
    public function showModule(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $moduleKey = Validator::sanitize($_GET['name'] ?? 'enquiry');
        if (!isset($this->modulesConfig[$moduleKey])) {
            $moduleKey = 'enquiry';
        }

        // Permission check
        Permission::enforce($moduleKey);

        if ($moduleKey === 'leave_management') {
            (new LeaveController())->showLeaveModule();
            return;
        }

        if ($moduleKey === 'online_exam') {
            (new OnlineExamController())->showOnlineExamModule();
            return;
        }

        if ($moduleKey === 'time_table') {
            (new TimetableController())->showTimetable();
            return;
        }

        if ($moduleKey === 'courses') {
            (new AcademicController())->showCourses();
            return;
        }

        if ($moduleKey === 'departments') {
            (new AcademicController())->showDepartments();
            return;
        }

        if ($moduleKey === 'subjects') {
            (new AcademicController())->showSubjects();
            return;
        }

        if ($moduleKey === 'student_promotion') {
            (new AcademicController())->showStudentPromotion();
            return;
        }

        if ($moduleKey === 'academic_structure') {
            (new AcademicController())->showAcademicStructure();
            return;
        }

        if ($moduleKey === 'promotion_history') {
            (new AcademicController())->showPromotionHistory();
            return;
        }

        $config = $this->modulesConfig[$moduleKey];
        $user = Auth::user();
        $tenantId = $user['tenant_id'];
        $db = Database::getInstance();

        Auth::syncStaffRecords($tenantId ? (int) $tenantId : null);


        // Process Department selection BEFORE running database queries
        if (isset($_GET['dept_id'])) {
            if ($_GET['dept_id'] === 'all' || (int) $_GET['dept_id'] === 0) {
                Session::remove('selected_dept_id');
                Session::remove('selected_dept_name');
            } else {
                $selectedId = (int) $_GET['dept_id'];
                Session::set('selected_dept_id', $selectedId);
                $deptStmt = $db->prepare("SELECT name, code FROM departments WHERE id = :id");
                $deptStmt->execute([':id' => $selectedId]);
                $d = $deptStmt->fetch();
                if ($d) {
                    Session::set('selected_dept_name', $d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')');
                }
            }
        }

        $selectedDeptId = Session::get('selected_dept_id');

        $records = [];
        try {
            $tableName = $config['table'];

            // Check columns of table
            $colStmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            $hasDeptCol = in_array('department_id', $columns);

            $conditions = [];
            $params = [];

            if ($tenantId !== null && $user['role_name'] !== 'super_admin' && $tableName !== 'roles') {
                $conditions[] = "tenant_id = :tid";
                $params[':tid'] = $tenantId;
            }

            // Role-based data scoping (Student, Parent, Teacher)
            if ($user['role_name'] === 'student') {
                $stRow = $db->prepare("SELECT id FROM students WHERE user_id = :uid LIMIT 1");
                $stRow->execute([':uid' => $user['id']]);
                $stId = $stRow->fetchColumn();
                if (!$stId) {
                    $stFallback = $db->prepare("SELECT id FROM students WHERE tenant_id = :tid ORDER BY id ASC LIMIT 1");
                    $stFallback->execute([':tid' => $tenantId ?? 1]);
                    $stId = $stFallback->fetchColumn();
                }
                if ($stId) {
                    if ($tableName === 'students') {
                        $conditions[] = "s.id = :st_scope_id";
                        $params[':st_scope_id'] = (int) $stId;
                    } elseif (in_array('student_id', $columns)) {
                        $conditions[] = "student_id = :st_scope_id";
                        $params[':st_scope_id'] = (int) $stId;
                    }
                }
            } elseif ($user['role_name'] === 'parent') {
                $stRow = $db->query("SELECT id FROM students WHERE tenant_id = " . (int) ($tenantId ?? 1) . " ORDER BY id ASC LIMIT 1")->fetchColumn();
                if ($stRow && in_array('student_id', $columns)) {
                    $conditions[] = "student_id = :pr_scope_id";
                    $params[':pr_scope_id'] = (int) $stRow;
                }
            } elseif ($user['role_name'] === 'teacher') {
                $stfRow = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = :uid LIMIT 1");
                $stfRow->execute([':uid' => $user['id']]);
                $stfData = $stfRow->fetch();
                $stfId = $stfData['id'] ?? null;
                $stfDeptId = $stfData['department_id'] ?? null;
                if ($stfId && in_array('staff_id', $columns)) {
                    $conditions[] = "staff_id = :stf_scope_id";
                    $params[':stf_scope_id'] = (int) $stfId;
                }
                if ($tableName === 'students' && $stfId) {
                    $conditions[] = "((s.department_id = :t_stf_dept OR c.department_id = :t_stf_dept_c) 
                                     OR s.department_id IN (SELECT department_id FROM teacher_assignments WHERE staff_id = :t_stf_id AND department_id IS NOT NULL) 
                                     OR s.section_id IN (SELECT section_id FROM teacher_assignments WHERE staff_id = :t_stf_id2 AND section_id IS NOT NULL) 
                                     OR s.course_id IN (SELECT course_id FROM time_tables WHERE staff_id = :t_stf_tt AND course_id IS NOT NULL) 
                                     OR s.id IN (SELECT student_id FROM student_attendance WHERE marked_by = :t_stf_id3))";
                    $params[':t_stf_id'] = (int) $stfId;
                    $params[':t_stf_id2'] = (int) $stfId;
                    $params[':t_stf_dept'] = (int) ($stfDeptId ?: 0);
                    $params[':t_stf_dept_c'] = (int) ($stfDeptId ?: 0);
                    $params[':t_stf_tt'] = (int) $stfId;
                    $params[':t_stf_id3'] = (int) $stfId;
                }
            }

            if (!empty($selectedDeptId)) {
                if ($hasDeptCol) {
                    $conditions[] = "department_id = :did";
                    $params[':did'] = (int) $selectedDeptId;
                } elseif ($tableName === 'students') {
                    $conditions[] = "batch_id IN (SELECT id FROM batches WHERE course_id IN (SELECT id FROM courses WHERE department_id = :did))";
                    $params[':did'] = (int) $selectedDeptId;
                } elseif ($tableName === 'batches' || $tableName === 'fee_structures') {
                    $conditions[] = "course_id IN (SELECT id FROM courses WHERE department_id = :did)";
                    $params[':did'] = (int) $selectedDeptId;
                } elseif ($tableName === 'time_tables') {
                    $conditions[] = "(staff_id IN (SELECT id FROM staff WHERE department_id = :did1) OR batch_id IN (SELECT id FROM batches WHERE course_id IN (SELECT id FROM courses WHERE department_id = :did2)))";
                    $params[':did1'] = (int) $selectedDeptId;
                    $params[':did2'] = (int) $selectedDeptId;
                }
            }

            $aliasedConditions = array_map(function ($cond) {

                if (strpos($cond, 'tenant_id') === 0 || strpos($cond, 'department_id') === 0 || strpos($cond, 'batch_id') === 0) {
                    return 's.' . $cond;
                }
                return $cond;
            }, $conditions);

            $aliasedWhere = !empty($aliasedConditions) ? "WHERE " . implode(' AND ', $aliasedConditions) : "";
            $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

            if ($tableName === 'staff') {
                $sql = "SELECT s.id, s.emp_id, s.first_name, s.last_name, s.designation, s.phone, s.email, COALESCE(d.name, 'General') as department_name, u.username as staff_username, s.joining_date, s.salary, s.status FROM staff s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN departments d ON s.department_id = d.id {$aliasedWhere} ORDER BY s.id DESC LIMIT 100";
            } elseif ($tableName === 'students' || $tableName === 'admissions' || $moduleKey === 'admission' || $moduleKey === 'student_mis' || $moduleKey === 'id_cards') {
                $sql = "SELECT s.id, s.admission_no, s.admission_no as student_id, s.roll_no, s.first_name, s.last_name, CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name, s.photo, s.gender, s.dob, s.email, s.phone, s.phone as student_phone, s.guardian_name, s.guardian_phone, s.guardian_phone as father_mobile, s.mother_phone, s.mother_phone as mother_mobile, COALESCE(s.department_id, c.department_id) as department_id, COALESCE(d.name, 'General') as department, COALESCE(d.name, 'General') as department_name, COALESCE(c.name, '') as course_name, s.standard_id, s.course_id, s.class_id, s.batch_id, COALESCE(b.batch_name, 'Academic Session 2026-2027') as batch_name, COALESCE(t.name, 'CAMPUS360 ENTERPRISE ERP') as institution_name, CASE WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'First Year (FY)' WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'Second Year (SY)' WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'Third Year (TY)' WHEN s.standard_id = 4 OR cl.code IN ('4th', 'YR4') OR cl.name LIKE '%4th%' OR cl.name LIKE '%Final%' THEN 'Final Year (4th)' ELSE 'First Year (FY)' END as student_year, CASE WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY' WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY' WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY' ELSE 'FY' END as year_code, COALESCE(cl.name, 'Class A') as class_name, s.status, u.username as student_username FROM students s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN tenants t ON s.tenant_id = t.id LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id LEFT JOIN classes cl ON s.class_id = cl.id {$aliasedWhere} GROUP BY s.id ORDER BY s.id DESC LIMIT 50";
            } else {
                $sql = "SELECT * FROM `{$tableName}` {$whereClause} ORDER BY id DESC LIMIT 50";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Module Query Error: " . $e->getMessage());
        }

        $departments = [];
        $staffList = [];
        $batchesList = [];
        $classesList = [];
        $feeInstallmentsList = [];
        $feeStructuresList = [];
        $studentsList = [];
        try {
            $deptQuery = "SELECT id, name, code FROM departments";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $deptQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $departments = $db->query($deptQuery)->fetchAll();

            $classQuery = "SELECT id, name, code FROM classes";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $classQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $classesList = $db->query($classQuery)->fetchAll();

            if ($moduleKey === 'time_table') {
                $stfQuery = "SELECT s.id, s.first_name, s.last_name, s.emp_id, s.designation 
                             FROM staff s 
                             LEFT JOIN users u ON s.user_id = u.id 
                             LEFT JOIN roles r ON u.role_id = r.id 
                             WHERE (r.name IN ('teacher', 'principal', 'school_admin') 
                                OR s.designation LIKE '%Teacher%' 
                                OR s.designation LIKE '%Faculty%' 
                                OR s.designation LIKE '%Professor%' 
                                OR s.designation LIKE '%Lecturer%' 
                                OR s.designation LIKE '%Instructor%' 
                                OR s.designation LIKE '%Tutor%' 
                                OR s.designation LIKE '%HOD%')";
                if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                    $stfQuery .= " AND s.tenant_id = " . (int) $tenantId;
                }
            } else {
                $stfQuery = "SELECT id, first_name, last_name, emp_id FROM staff";
                if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                    $stfQuery .= " WHERE tenant_id = " . (int) $tenantId;
                }
            }
            $staffList = $db->query($stfQuery)->fetchAll();

            $batchQuery = "SELECT id, batch_name FROM batches";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $batchQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $batchesList = $db->query($batchQuery)->fetchAll();

            $instQuery = "SELECT id, title, amount FROM fee_installments";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $instQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $feeInstallmentsList = $db->query($instQuery)->fetchAll();

            $feeStructQuery = "SELECT id, fee_type, amount FROM fee_structures";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $feeStructQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $feeStructuresList = $db->query($feeStructQuery)->fetchAll();

            $stdQuery = "SELECT id, first_name, last_name, roll_no FROM students";
            if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                $stdQuery .= " WHERE tenant_id = " . (int) $tenantId;
            }
            $studentsList = $db->query($stdQuery)->fetchAll();

            $rolesList = $db->query("SELECT id, name, display_name FROM roles WHERE name NOT IN ('super_admin', 'school_admin', 'student', 'parent') ORDER BY id ASC")->fetchAll();
        } catch (\Throwable $e) {
            $rolesList = [];
        }

        if ($moduleKey === 'staff_management') {
            $staffTasks = [];
            try {
                $staffTasks = $db->query("SELECT t.*, u1.username as assigned_by_name, u2.username as assigned_to_name FROM staff_tasks t LEFT JOIN users u1 ON t.assigned_by_user_id = u1.id LEFT JOIN users u2 ON t.assigned_to_user_id = u2.id ORDER BY t.id DESC")->fetchAll();
            } catch (\Throwable $e) {
            }

            Response::view('modules/staff_management', [
                'user' => $user,
                'moduleKey' => $moduleKey,
                'config' => $config,
                'records' => $records,
                'staffList' => $records,
                'departments' => $departments,
                'classesList' => $classesList,
                'staffTasks' => $staffTasks,
                'rolesList' => $rolesList,
                'accessLevel' => Permission::getAccessLevel($moduleKey),
                'canManage' => Permission::canManage($moduleKey),
                'csrf_token' => CSRF::getToken()
            ]);
            return;
        }

        $nextUniqueId = \App\Controllers\WorkflowController::generateNextUniqueId((int) ($tenantId ?? 1));

        Response::view('modules/view_module', [
            'user' => $user,
            'moduleKey' => $moduleKey,
            'config' => $config,
            'records' => $records,
            'nextUniqueId' => $nextUniqueId,
            'departments' => $departments,
            'classesList' => $classesList,
            'staffList' => $staffList,
            'batchesList' => $batchesList,
            'feeInstallmentsList' => $feeInstallmentsList,
            'feeStructuresList' => $feeStructuresList,
            'studentsList' => $studentsList,
            'rolesList' => $rolesList,
            'accessLevel' => Permission::getAccessLevel($moduleKey),
            'canManage' => Permission::canManage($moduleKey),
            'csrf_token' => CSRF::getToken()
        ]);
    }


    // CREATE Operation - Dynamic Multi-Tenant Inserter (Supports Multi-Row Arrays)
    public function storeRecord(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $moduleKey = Validator::sanitize($_POST['module_key'] ?? 'enquiry');
        if (!isset($this->modulesConfig[$moduleKey])) {
            Response::redirect('index.php?page=dashboard');
        }

        Permission::enforceManage($moduleKey);

        $config = $this->modulesConfig[$moduleKey];
        $tableName = $config['table'];
        $db = Database::getInstance();
        $user = Auth::user();
        $tenantId = !empty($user['tenant_id']) ? (int) $user['tenant_id'] : 1;
        $selectedDeptId = Session::get('selected_dept_id');

        try {
            // Fetch table columns dynamically
            $colStmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);

            $isMultiRow = false;
            $rowCount = 1;

            foreach ($_POST as $key => $val) {
                if (in_array($key, $columns) && is_array($val)) {
                    $isMultiRow = true;
                    $rowCount = max($rowCount, count($val));
                }
            }

            $recordsToInsert = [];

            if ($isMultiRow) {
                for ($i = 0; $i < $rowCount; $i++) {
                    $row = [];
                    $hasContent = false;
                    foreach ($columns as $col) {
                        if ($col === 'id')
                            continue;
                        if (isset($_POST[$col])) {
                            if (is_array($_POST[$col])) {
                                $raw = $_POST[$col][$i] ?? '';
                            } else {
                                $raw = $_POST[$col];
                            }
                            $sanitized = Validator::sanitize((string) $raw);
                            if ($sanitized !== '') {
                                $row[$col] = $sanitized;
                                if (in_array($col, ['first_name', 'last_name', 'title', 'name', 'student_name', 'designation', 'email', 'phone'])) {
                                    $hasContent = true;
                                }
                            }
                        }
                    }
                    if ($hasContent || count($row) > 0) {
                        $recordsToInsert[] = $row;
                    }
                }
            } else {
                $insertData = [];
                foreach ($_POST as $key => $val) {
                    if (in_array($key, $columns) && $key !== 'id' && !is_array($val)) {
                        $sanitized = Validator::sanitize((string) $val);
                        if ($sanitized !== '') {
                            $insertData[$key] = $sanitized;
                        }
                    }
                }
                if (!empty($insertData)) {
                    $recordsToInsert[] = $insertData;
                }
            }

            foreach ($recordsToInsert as $idx => &$insertData) {
                // Fill tenant_id
                if (in_array('tenant_id', $columns) && empty($insertData['tenant_id'])) {
                    $insertData['tenant_id'] = $tenantId;
                }

                // Fill user/staff FK references & create staff user account if username provided
                if ($tableName === 'staff') {
                    $unameRaw = is_array($_POST['username'] ?? null) ? ($_POST['username'][$idx] ?? '') : ($_POST['username'] ?? '');
                    $passRaw = is_array($_POST['password'] ?? null) ? ($_POST['password'][$idx] ?? '') : ($_POST['password'] ?? '');

                    unset($insertData['username']);
                    unset($insertData['password']);

                    $uname = Validator::sanitize((string) $unameRaw);

                    if (!empty($uname)) {
                        $cleanUname = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $uname));
                        if (empty($cleanUname)) {
                            $cleanUname = 'staff_' . rand(100, 999);
                        }
                        $finalUname = $cleanUname;
                        $attempt = 0;
                        while ($attempt < 10) {
                            $chkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = :tid AND username = :uname");
                            $chkStmt->execute([':tid' => $tenantId, ':uname' => $finalUname]);
                            if ((int) $chkStmt->fetchColumn() === 0) {
                                break;
                            }
                            $attempt++;
                            $finalUname = $cleanUname . '_' . rand(10, 999);
                        }

                        $userEmail = !empty($insertData['email']) ? $insertData['email'] : ($finalUname . '@campus360.edu');
                        $rawPass = !empty($passRaw) ? $passRaw : 'StaffPass@123';
                        $passHash = password_hash($rawPass, PASSWORD_BCRYPT);

                        $selectedRoleId = is_array($_POST['role_id'] ?? null) ? ($_POST['role_id'][$idx] ?? 0) : ($_POST['role_id'] ?? 0);
                        $desig = strtolower($insertData['designation'] ?? '');

                        if ($selectedRoleId > 0) {
                            $roleId = (int) $selectedRoleId;
                        } elseif (strpos($desig, 'hod') !== false || strpos($desig, 'head of department') !== false) {
                            $roleId = 15; // HOD / Head of Department
                        } elseif (strpos($desig, 'principal') !== false || strpos($desig, 'director') !== false) {
                            $roleId = 3; // Principal
                        } elseif (strpos($desig, 'teacher') !== false || strpos($desig, 'faculty') !== false || strpos($desig, 'professor') !== false || strpos($desig, 'lecturer') !== false || strpos($desig, 'instructor') !== false || strpos($desig, 'tutor') !== false) {
                            $roleId = 4; // Teaching Staff / Teacher
                        } elseif (strpos($desig, 'account') !== false || strpos($desig, 'finance') !== false) {
                            $roleId = 7; // Accountant
                        } elseif (strpos($desig, 'librar') !== false) {
                            $roleId = 8; // Librarian
                        } elseif (strpos($desig, 'hr') !== false || strpos($desig, 'human resource') !== false) {
                            $roleId = 9; // HR Executive
                        } elseif (strpos($desig, 'officer') !== false || strpos($desig, 'administrative') !== false) {
                            $roleId = 10; // Administrative Officer
                        } elseif (strpos($desig, 'transport') !== false || strpos($desig, 'hostel') !== false) {
                            $roleId = 11; // Transport & Hostel Administrator
                        } elseif (strpos($desig, 'inventory') !== false || strpos($desig, 'store') !== false || strpos($desig, 'asset') !== false) {
                            $roleId = 12; // Inventory Manager
                        } elseif (strpos($desig, 'reception') !== false || strpos($desig, 'front office') !== false) {
                            $roleId = 13; // Receptionist
                        } elseif (strpos($desig, 'erp') !== false) {
                            $roleId = 14; // ERP Administrator
                        } elseif (strpos($desig, 'school_admin') !== false || strpos($desig, 'school admin') !== false) {
                            $roleId = 2; // School Administrator
                        } else {
                            $roleId = 4; // Default to Teacher role for staff
                        }


                        $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :uname, :email, :phash, :rid, 'active')");
                        $uStmt->execute([
                            ':tid' => $tenantId,
                            ':uname' => $finalUname,
                            ':email' => $userEmail,
                            ':phash' => $passHash,
                            ':rid' => $roleId
                        ]);

                        $createdUserId = (int) $db->lastInsertId();
                        $insertData['user_id'] = $createdUserId;
                    } else {
                        unset($insertData['user_id']);
                    }
                } elseif ($tableName === 'students' || $tableName === 'admissions') {
                    $unameRaw = is_array($_POST['username'] ?? null) ? ($_POST['username'][$idx] ?? '') : ($_POST['username'] ?? '');
                    $passRaw = is_array($_POST['password'] ?? null) ? ($_POST['password'][$idx] ?? '') : ($_POST['password'] ?? '');

                    unset($insertData['username']);
                    unset($insertData['password']);

                    $uname = Validator::sanitize((string) $unameRaw);
                    if (empty($uname) && !empty($insertData['admission_no'])) {
                        $uname = strtolower(str_replace('-', '_', $insertData['admission_no']));
                    }

                    if (!empty($uname)) {
                        $cleanUname = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $uname));
                        if (empty($cleanUname)) {
                            $cleanUname = 'student_' . rand(100, 999);
                        }
                        $finalUname = $cleanUname;
                        $attempt = 0;
                        while ($attempt < 10) {
                            $chkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = :tid AND username = :uname");
                            $chkStmt->execute([':tid' => $tenantId, ':uname' => $finalUname]);
                            if ((int) $chkStmt->fetchColumn() === 0) {
                                break;
                            }
                            $attempt++;
                            $finalUname = $cleanUname . '_' . rand(10, 999);
                        }

                        $userEmail = !empty($insertData['email']) ? $insertData['email'] : ($finalUname . '@campus360.edu');
                        $rawPass = !empty($passRaw) ? $passRaw : 'StudentPass@123';
                        $passHash = password_hash($rawPass, PASSWORD_BCRYPT);

                        $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :uname, :email, :phash, 5, 'active')");
                        $uStmt->execute([
                            ':tid' => $tenantId,
                            ':uname' => $finalUname,
                            ':email' => $userEmail,
                            ':phash' => $passHash
                        ]);

                        $createdUserId = (int) $db->lastInsertId();
                        $insertData['user_id'] = $createdUserId;
                    } else {
                        unset($insertData['user_id']);
                    }
                } else {
                    if (in_array('user_id', $columns) && empty($insertData['user_id'])) {
                        $insertData['user_id'] = $user['id'] ?? 1;
                    }
                }

                if (in_array('posted_by', $columns) && empty($insertData['posted_by'])) {
                    $insertData['posted_by'] = $user['id'] ?? 1;
                }
                if (in_array('marked_by', $columns) && empty($insertData['marked_by'])) {
                    $insertData['marked_by'] = $user['id'] ?? 1;
                }
                if (in_array('created_by', $columns) && empty($insertData['created_by'])) {
                    $insertData['created_by'] = $user['id'] ?? 1;
                }
                if (in_array('applicant_user_id', $columns) && empty($insertData['applicant_user_id'])) {
                    $insertData['applicant_user_id'] = $user['id'] ?? 1;
                }

                // Foreign Key default fallbacks if empty, missing, or referencing non-existent ID
                if (in_array('department_id', $columns)) {
                    $validDept = !empty($insertData['department_id']) ? (int) $db->query("SELECT id FROM departments WHERE id = " . (int) $insertData['department_id'])->fetchColumn() : 0;
                    if (!$validDept) {
                        $insertData['department_id'] = $this->getValidFkId($db, 'departments', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['department_id'] = $validDept;
                    }
                }
                if (in_array('batch_id', $columns)) {
                    $validBatch = !empty($insertData['batch_id']) ? (int) $db->query("SELECT id FROM batches WHERE id = " . (int) $insertData['batch_id'])->fetchColumn() : 0;
                    if (!$validBatch) {
                        $insertData['batch_id'] = $this->getValidFkId($db, 'batches', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['batch_id'] = $validBatch;
                    }
                }
                if (in_array('course_id', $columns)) {
                    $validCrs = !empty($insertData['course_id']) ? (int) $db->query("SELECT id FROM courses WHERE id = " . (int) $insertData['course_id'])->fetchColumn() : 0;
                    if (!$validCrs) {
                        $insertData['course_id'] = $this->getValidFkId($db, 'courses', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['course_id'] = $validCrs;
                    }
                }
                if (in_array('student_id', $columns)) {
                    $validSt = !empty($insertData['student_id']) ? (int) $db->query("SELECT id FROM students WHERE id = " . (int) $insertData['student_id'])->fetchColumn() : 0;
                    if (!$validSt) {
                        $insertData['student_id'] = $this->getValidFkId($db, 'students', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['student_id'] = $validSt;
                    }
                }
                if (in_array('staff_id', $columns)) {
                    $validStf = !empty($insertData['staff_id']) ? (int) $db->query("SELECT id FROM staff WHERE id = " . (int) $insertData['staff_id'])->fetchColumn() : 0;
                    if (!$validStf) {
                        $insertData['staff_id'] = $this->getValidFkId($db, 'staff', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['staff_id'] = $validStf;
                    }
                }
                if (in_array('assigned_to', $columns)) {
                    $validAss = !empty($insertData['assigned_to']) ? (int) $db->query("SELECT id FROM staff WHERE id = " . (int) $insertData['assigned_to'])->fetchColumn() : 0;
                    if (!$validAss) {
                        $insertData['assigned_to'] = $this->getValidFkId($db, 'staff', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['assigned_to'] = $validAss;
                    }
                }
                if (in_array('exam_id', $columns)) {
                    $validEx = !empty($insertData['exam_id']) ? (int) $db->query("SELECT id FROM exams WHERE id = " . (int) $insertData['exam_id'])->fetchColumn() : 0;
                    if (!$validEx) {
                        $insertData['exam_id'] = $this->getValidFkId($db, 'exams', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['exam_id'] = $validEx;
                    }
                }
                if (in_array('fee_structure_id', $columns)) {
                    $validFee = !empty($insertData['fee_structure_id']) ? (int) $db->query("SELECT id FROM fee_structures WHERE id = " . (int) $insertData['fee_structure_id'])->fetchColumn() : 0;
                    if (!$validFee) {
                        $insertData['fee_structure_id'] = $this->getValidFkId($db, 'fee_structures', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['fee_structure_id'] = $validFee;
                    }
                }
                if (in_array('installment_id', $columns)) {
                    $validInst = !empty($insertData['installment_id']) ? (int) $db->query("SELECT id FROM fee_installments WHERE id = " . (int) $insertData['installment_id'])->fetchColumn() : 0;
                    if (!$validInst) {
                        $insertData['installment_id'] = $this->getValidFkId($db, 'fee_installments', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['installment_id'] = $validInst;
                    }
                }
                if (in_array('book_id', $columns)) {
                    $validBk = !empty($insertData['book_id']) ? (int) $db->query("SELECT id FROM library_books WHERE id = " . (int) $insertData['book_id'])->fetchColumn() : 0;
                    if (!$validBk) {
                        $insertData['book_id'] = $this->getValidFkId($db, 'library_books', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['book_id'] = $validBk;
                    }
                }
                if (in_array('room_id', $columns)) {
                    $validRm = !empty($insertData['room_id']) ? (int) $db->query("SELECT id FROM hostel_rooms WHERE id = " . (int) $insertData['room_id'])->fetchColumn() : 0;
                    if (!$validRm) {
                        $insertData['room_id'] = $this->getValidFkId($db, 'hostel_rooms', $tenantId, $selectedDeptId);
                    } else {
                        $insertData['room_id'] = $validRm;
                    }
                }

                // Auto-generated codes & identifiers with unique check
                if (in_array('admission_no', $columns) && empty($insertData['admission_no'])) {
                    $insertData['admission_no'] = 'ADM-' . date('Y') . '-' . rand(1000, 9999);
                }
                if (in_array('roll_no', $columns) && empty($insertData['roll_no'])) {
                    $insertData['roll_no'] = 'R-' . rand(100, 999);
                }
                if (in_array('emp_id', $columns) && empty($insertData['emp_id'])) {
                    $attempt = 0;
                    do {
                        $candidate = 'EMP-' . rand(1000, 9999);
                        $exists = (int) $db->query("SELECT COUNT(*) FROM staff WHERE tenant_id = {$tenantId} AND emp_id = '{$candidate}'")->fetchColumn();
                        $attempt++;
                    } while ($exists > 0 && $attempt < 10);
                    $insertData['emp_id'] = $candidate;
                }
                if (in_array('isbn', $columns) && empty($insertData['isbn'])) {
                    $insertData['isbn'] = '978-' . rand(100000000, 999999999);
                }
                if (in_array('badge_no', $columns) && empty($insertData['badge_no'])) {
                    $insertData['badge_no'] = 'BDG-' . rand(100, 999);
                }
                if (in_array('reference_no', $columns) && empty($insertData['reference_no'])) {
                    $insertData['reference_no'] = 'REF-' . time() . '-' . $idx;
                }
                if (in_array('transaction_id', $columns) && empty($insertData['transaction_id'])) {
                    $insertData['transaction_id'] = 'TXN-' . time() . '-' . rand(100, 999) . '-' . $idx;
                }
                if (in_array('certificate_no', $columns) && empty($insertData['certificate_no'])) {
                    $insertData['certificate_no'] = 'CERT-' . time() . '-' . $idx;
                }

                // Dates & Times
                if (in_array('date', $columns) && empty($insertData['date'])) {
                    $insertData['date'] = date('Y-m-d');
                }
                if (in_array('admission_date', $columns) && empty($insertData['admission_date'])) {
                    $insertData['admission_date'] = date('Y-m-d');
                }
                if (in_array('start_date', $columns) && empty($insertData['start_date'])) {
                    $insertData['start_date'] = date('Y-m-d');
                }
                if (in_array('end_date', $columns) && empty($insertData['end_date'])) {
                    $insertData['end_date'] = date('Y-m-d', strtotime('+7 days'));
                }
                if (in_array('publish_date', $columns) && empty($insertData['publish_date'])) {
                    $insertData['publish_date'] = date('Y-m-d');
                }
                if (in_array('transaction_date', $columns) && empty($insertData['transaction_date'])) {
                    $insertData['transaction_date'] = date('Y-m-d');
                }
                if (in_array('issue_date', $columns) && empty($insertData['issue_date'])) {
                    $insertData['issue_date'] = date('Y-m-d');
                }
                if (in_array('return_due_date', $columns) && empty($insertData['return_due_date'])) {
                    $insertData['return_due_date'] = date('Y-m-d', strtotime('+14 days'));
                }
                if (in_array('allocated_date', $columns) && empty($insertData['allocated_date'])) {
                    $insertData['allocated_date'] = date('Y-m-d');
                }
                if (in_array('check_in', $columns) && empty($insertData['check_in'])) {
                    $insertData['check_in'] = date('Y-m-d H:i:s');
                }
                if (in_array('joining_date', $columns) && empty($insertData['joining_date'])) {
                    $insertData['joining_date'] = date('Y-m-d');
                }
                if (in_array('dob', $columns) && empty($insertData['dob'])) {
                    $insertData['dob'] = '2005-01-01';
                }
                if (in_array('due_date', $columns) && empty($insertData['due_date'])) {
                    $insertData['due_date'] = date('Y-m-d', strtotime('+30 days'));
                }
                if (in_array('exam_date', $columns) && empty($insertData['exam_date'])) {
                    $insertData['exam_date'] = date('Y-m-d');
                }

                // Defaults for specific columns
                if (in_array('target_role', $columns) && empty($insertData['target_role'])) {
                    $insertData['target_role'] = 'all';
                }
                if (in_array('status', $columns) && empty($insertData['status'])) {
                    if ($tableName === 'notices' || $tableName === 'online_exams') {
                        $insertData['status'] = 'published';
                    } elseif ($tableName === 'tickets') {
                        $insertData['status'] = 'open';
                    } elseif ($tableName === 'admissions' || $tableName === 'enquiries' || $tableName === 'leave_requests') {
                        $insertData['status'] = 'pending';
                    } elseif ($tableName === 'fee_collections' || $tableName === 'fee_installments') {
                        $insertData['status'] = 'paid';
                    } elseif ($tableName === 'inventory_assets') {
                        $insertData['status'] = 'in_stock';
                    } elseif ($tableName === 'certificates') {
                        $insertData['status'] = 'issued';
                    } else {
                        $insertData['status'] = 'active';
                    }
                }

                // String fallbacks if input fields title/content/name were passed
                if (in_array('title', $columns) && empty($insertData['title'])) {
                    $insertData['title'] = $insertData['name'] ?? $insertData['subject'] ?? $insertData['student_name'] ?? 'New Record Title';
                }
                if (in_array('content', $columns) && empty($insertData['content'])) {
                    $insertData['content'] = $insertData['details'] ?? $insertData['description'] ?? 'Record content details...';
                }
                if (in_array('name', $columns) && empty($insertData['name'])) {
                    $insertData['name'] = $insertData['title'] ?? $insertData['student_name'] ?? 'Record Entry Name';
                }
                if (in_array('first_name', $columns) && empty($insertData['first_name'])) {
                    $insertData['first_name'] = $insertData['title'] ?? $insertData['name'] ?? $insertData['student_name'] ?? 'John';
                }
                if (in_array('last_name', $columns) && empty($insertData['last_name'])) {
                    $insertData['last_name'] = $insertData['last_name'] ?? 'Doe';
                }
                if (in_array('subject_name', $columns) && empty($insertData['subject_name'])) {
                    $insertData['subject_name'] = $insertData['title'] ?? $insertData['name'] ?? $insertData['subject'] ?? 'General Lecture';
                }
                if (in_array('day_of_week', $columns) && empty($insertData['day_of_week'])) {
                    $insertData['day_of_week'] = 'Monday';
                }
                if (in_array('time_slot', $columns) && empty($insertData['time_slot'])) {
                    $insertData['time_slot'] = '09:00 AM - 10:00 AM';
                }
                if (in_array('room_no', $columns) && empty($insertData['room_no'])) {
                    $insertData['room_no'] = 'Room 101';
                }
                if (in_array('grade', $columns) && empty($insertData['grade'])) {
                    $m = (float) ($insertData['marks_obtained'] ?? 75);
                    $insertData['grade'] = $m >= 80 ? 'A' : ($m >= 60 ? 'B' : ($m >= 40 ? 'C' : 'F'));
                }

                if (!empty($insertData)) {
                    $cols = array_keys($insertData);
                    $placeholders = array_map(fn($c) => ":{$c}", $cols);

                    $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $placeholders) . ")";

                    if ($tableName === 'exam_results') {
                        $sql .= " ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), grade = VALUES(grade)";
                    } elseif ($tableName === 'student_attendance' || $tableName === 'staff_attendance') {
                        $sql .= " ON DUPLICATE KEY UPDATE status = VALUES(status)";
                    }

                    $stmt = $db->prepare($sql);
                    $stmt->execute($insertData);
                }
            }

            if ($tableName === 'fee_collections' && !empty($recordsToInsert)) {
                foreach ($recordsToInsert as $insData) {
                    $stId = (int) ($insData['student_id'] ?? 0);
                    $instId = (int) ($insData['installment_id'] ?? 0);
                    $st = strtolower((string) ($insData['status'] ?? 'paid'));
                    $targetSt = ($st === 'paid' || $st === 'approved' || $st === 'active' || $st === 'completed') ? 'paid' : 'unpaid';

                    if ($instId > 0) {
                        $db->prepare("UPDATE fee_installments SET status = :st WHERE id = :iid")->execute([':st' => $targetSt, ':iid' => $instId]);
                    }
                    if ($stId > 0) {
                        $db->prepare("UPDATE fee_installments SET status = :st WHERE student_id = :sid ORDER BY id ASC LIMIT 1")->execute([':st' => $targetSt, ':sid' => $stId]);
                    }
                }
            }

            if (!empty($recordsToInsert)) {
                $countInserted = count($recordsToInsert);
                // Audit log
                $audit = $db->prepare("INSERT INTO security_audit_logs (tenant_id, user_id, action, ip_address, details) VALUES (:tid, :uid, :act, :ip, :det)");
                $audit->execute([
                    ':tid' => $tenantId,
                    ':uid' => $user['id'] ?? 1,
                    ':act' => 'CRUD_CREATE_' . strtoupper($moduleKey),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':det' => "Inserted {$countInserted} record(s) into {$tableName}"
                ]);
            }

        } catch (\Throwable $e) {
            error_log("Module Store Error: " . $e->getMessage());
            @file_put_contents(BASE_PATH . '/scratch/last_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        Response::redirect("index.php?page=module&name={$moduleKey}");
    }

    // UPDATE Operation - Dynamic Column Updater
    public function updateRecord(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $moduleKey = Validator::sanitize($_POST['module_key'] ?? 'enquiry');
        $recordId = (int) ($_POST['record_id'] ?? 0);

        if (!isset($this->modulesConfig[$moduleKey]) || $recordId <= 0) {
            Response::redirect('index.php?page=dashboard');
        }

        Permission::enforceManage($moduleKey);

        $config = $this->modulesConfig[$moduleKey];
        $tableName = $config['table'];
        $db = Database::getInstance();
        $user = Auth::user();
        $tenantId = $user['tenant_id'];

        try {
            // Fetch table columns
            $colStmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);

            $updateData = [];
            $setClauses = [];

            // Process photo file upload if provided (supports images and PDF format)
            if (in_array('photo', $columns) && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/uploads/students/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION)) ?: 'jpg';
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];
                if (in_array($ext, $allowed)) {
                    $filename = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                        $updateData[':photo_uploaded'] = 'uploads/students/' . $filename;
                        $setClauses[] = "`photo` = :photo_uploaded";
                    }
                }
            }

            foreach ($_POST as $key => $val) {
                if ($key === 'photo' && isset($updateData[':photo_uploaded'])) {
                    continue; // Skip text value if a file was uploaded
                }
                if (in_array($key, $columns) && $key !== 'id' && $key !== 'tenant_id') {
                    $sanitized = Validator::sanitize((string) $val);

                    if (in_array($key, ['salary', 'amount', 'price', 'fee_amount', 'amount_paid', 'marks_obtained'])) {
                        $sanitized = is_numeric($val) ? (float) $val : 0.00;
                    }

                    if ($key === 'status') {
                        if ($tableName === 'enquiries') {
                            if ($sanitized === 'active' || $sanitized === 'approved' || $sanitized === 'admitted') {
                                $sanitized = 'admitted';
                            } elseif ($sanitized === 'contacted') {
                                $sanitized = 'contacted';
                            } elseif ($sanitized === 'closed') {
                                $sanitized = 'closed';
                            } else {
                                $sanitized = 'pending';
                            }
                        } elseif ($tableName === 'notices') {
                            $sanitized = in_array($sanitized, ['published', 'archived']) ? $sanitized : 'published';
                        } elseif ($tableName === 'tickets') {
                            $sanitized = in_array($sanitized, ['open', 'in_progress', 'resolved', 'closed']) ? $sanitized : 'open';
                        } elseif ($tableName === 'admissions' || $tableName === 'leave_requests') {
                            $sanitized = in_array($sanitized, ['pending', 'approved', 'rejected']) ? $sanitized : 'approved';
                        }
                    }

                    // Integer Foreign Keys Handling (assigned_to, staff_id, student_id, etc.)
                    if (in_array($key, ['assigned_to', 'staff_id', 'student_id', 'course_id', 'batch_id', 'department_id', 'fee_structure_id', 'book_id', 'room_id', 'user_id', 'marked_by', 'posted_by', 'created_by'])) {
                        if (!is_numeric($sanitized)) {
                            if (($key === 'assigned_to' || $key === 'staff_id') && $sanitized !== '') {
                                // Try lookup staff ID by name string (e.g. "akash") using distinct parameter names for PDO compatibility
                                $stfStmt = $db->prepare("SELECT id FROM staff WHERE first_name LIKE :n1 OR last_name LIKE :n2 LIMIT 1");
                                $stfStmt->execute([':n1' => "%{$sanitized}%", ':n2' => "%{$sanitized}%"]);
                                $stfId = $stfStmt->fetchColumn();
                                $sanitized = $stfId ? (int) $stfId : null;
                            } elseif ($key === 'department_id' && $sanitized !== '') {
                                $dStmt = $db->prepare("SELECT id FROM departments WHERE name LIKE :n1 OR code LIKE :n2 LIMIT 1");
                                $dStmt->execute([':n1' => "%{$sanitized}%", ':n2' => "%{$sanitized}%"]);
                                $dId = $dStmt->fetchColumn();
                                $sanitized = $dId ? (int) $dId : 1;
                            } else {
                                $sanitized = null;
                            }
                        } else {
                            $sanitized = (int) $sanitized;
                        }
                    }

                    if ($sanitized !== null) {
                        $updateData[":{$key}"] = $sanitized;
                        $setClauses[] = "`{$key}` = :{$key}";
                    } else {
                        $setClauses[] = "`{$key}` = NULL";
                    }
                }
            }

            // Staff User Account Update / Provisioning
            if ($tableName === 'staff') {
                $unameRaw = Validator::sanitize($_POST['username'] ?? '');
                $passRaw = $_POST['password'] ?? '';

                $stfObj = $db->prepare("SELECT user_id, tenant_id FROM staff WHERE id = :id");
                $stfObj->execute([':id' => $recordId]);
                $stfData = $stfObj->fetch();

                if ($stfData) {
                    $existingUserId = $stfData['user_id'] ? (int) $stfData['user_id'] : null;

                    if ($existingUserId) {
                        $uSet = [];
                        $uParams = [':uid' => $existingUserId];
                        if (!empty($unameRaw)) {
                            $uSet[] = "`username` = :uname";
                            $uParams[':uname'] = $unameRaw;
                        }
                        if (!empty($passRaw)) {
                            $uSet[] = "`password_hash` = :phash";
                            $uParams[':phash'] = password_hash($passRaw, PASSWORD_BCRYPT);
                        }
                        if (!empty($_POST['email'])) {
                            $uSet[] = "`email` = :email";
                            $uParams[':email'] = Validator::sanitize($_POST['email']);
                        }
                        if (!empty($_POST['designation']) || !empty($_POST['role_id'])) {
                            $desigCheck = strtolower($_POST['designation'] ?? '');
                            $rId = !empty($_POST['role_id']) ? (int) $_POST['role_id'] : null;
                            if (!$rId) {
                                if (strpos($desigCheck, 'hod') !== false || strpos($desigCheck, 'head of department') !== false) {
                                    $rId = 15;
                                } elseif (strpos($desigCheck, 'principal') !== false) {
                                    $rId = 3;
                                }
                            }
                            if ($rId) {
                                $uSet[] = "`role_id` = :rid";
                                $uParams[':rid'] = $rId;
                            }
                        }
                        if (!empty($uSet)) {
                            $db->prepare("UPDATE users SET " . implode(', ', $uSet) . " WHERE id = :uid")->execute($uParams);
                        }
                    } elseif (!empty($unameRaw)) {
                        $rawPass = !empty($passRaw) ? $passRaw : 'StaffPass@123';
                        $passHash = password_hash($rawPass, PASSWORD_BCRYPT);
                        $staffEmail = !empty($_POST['email']) ? Validator::sanitize($_POST['email']) : ($unameRaw . '@campus360.edu');

                        $desigCheck = strtolower($_POST['designation'] ?? '');
                        $rId = !empty($_POST['role_id']) ? (int) $_POST['role_id'] : 4;
                        if ($rId === 4) {
                            if (strpos($desigCheck, 'hod') !== false || strpos($desigCheck, 'head of department') !== false) {
                                $rId = 15;
                            } elseif (strpos($desigCheck, 'principal') !== false) {
                                $rId = 3;
                            }
                        }

                        $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :uname, :email, :phash, :rid, 'active')");
                        $uStmt->execute([
                            ':tid' => $stfData['tenant_id'] ?? $tenantId,
                            ':uname' => $unameRaw,
                            ':email' => $staffEmail,
                            ':phash' => $passHash,
                            ':rid' => $rId
                        ]);
                        $newUid = (int) $db->lastInsertId();
                        $setClauses[] = "`user_id` = :uid_val";
                        $updateData[':uid_val'] = $newUid;
                    }
                }
            }

            // Student User Account Update / Provisioning
            if ($tableName === 'students') {
                $unameRaw = Validator::sanitize($_POST['username'] ?? '');
                $passRaw = $_POST['password'] ?? '';

                $stObj = $db->prepare("SELECT user_id, tenant_id FROM students WHERE id = :id");
                $stObj->execute([':id' => $recordId]);
                $stData = $stObj->fetch();

                if ($stData) {
                    $existingUserId = $stData['user_id'] ? (int) $stData['user_id'] : null;

                    if ($existingUserId) {
                        $uSet = [];
                        $uParams = [':uid' => $existingUserId];
                        if (!empty($unameRaw)) {
                            $uSet[] = "`username` = :uname";
                            $uParams[':uname'] = $unameRaw;
                        }
                        if (!empty($passRaw)) {
                            $uSet[] = "`password_hash` = :phash";
                            $uParams[':phash'] = password_hash($passRaw, PASSWORD_BCRYPT);
                        }
                        if (!empty($_POST['email'])) {
                            $uSet[] = "`email` = :email";
                            $uParams[':email'] = Validator::sanitize($_POST['email']);
                        }
                        if (!empty($uSet)) {
                            $db->prepare("UPDATE users SET " . implode(', ', $uSet) . " WHERE id = :uid")->execute($uParams);
                        }
                    } elseif (!empty($unameRaw)) {
                        $rawPass = !empty($passRaw) ? $passRaw : 'StudentPass@123';
                        $passHash = password_hash($rawPass, PASSWORD_BCRYPT);
                        $studentEmail = !empty($_POST['email']) ? Validator::sanitize($_POST['email']) : ($unameRaw . '@campus360.edu');

                        $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :uname, :email, :phash, 5, 'active')");
                        $uStmt->execute([
                            ':tid' => $stData['tenant_id'] ?? $tenantId,
                            ':uname' => $unameRaw,
                            ':email' => $studentEmail,
                            ':phash' => $passHash
                        ]);
                        $newUid = (int) $db->lastInsertId();
                        $setClauses[] = "`user_id` = :uid_val";
                        $updateData[':uid_val'] = $newUid;
                    }
                }
            }

            if (!empty($setClauses)) {
                $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setClauses) . " WHERE id = :record_id";
                $updateData[':record_id'] = $recordId;

                if ($tenantId !== null && $user['role_name'] !== 'super_admin' && in_array('tenant_id', $columns)) {
                    $sql .= " AND tenant_id = :tenant_id";
                    $updateData[':tenant_id'] = $tenantId;
                }

                $stmt = $db->prepare($sql);
                $stmt->execute($updateData);

                if ($tableName === 'fee_collections' || $tableName === 'fee_installments') {
                    $rowObj = $db->prepare("SELECT student_id, installment_id, status FROM `{$tableName}` WHERE id = :id");
                    $rowObj->execute([':id' => $recordId]);
                    $rowF = $rowObj->fetch();
                    if ($rowF) {
                        $stId = (int) ($rowF['student_id'] ?? 0);
                        $instId = (int) ($rowF['installment_id'] ?? 0);
                        $st = strtolower((string) ($rowF['status'] ?? 'paid'));
                        $targetSt = ($st === 'paid' || $st === 'approved' || $st === 'active' || $st === 'completed') ? 'paid' : 'unpaid';

                        if ($tableName === 'fee_collections') {
                            if ($instId > 0) {
                                $db->prepare("UPDATE fee_installments SET status = :st WHERE id = :iid")->execute([':st' => $targetSt, ':iid' => $instId]);
                            }
                            if ($stId > 0) {
                                $db->prepare("UPDATE fee_installments SET status = :st WHERE student_id = :sid ORDER BY id ASC LIMIT 1")->execute([':st' => $targetSt, ':sid' => $stId]);
                            }
                        }
                    }
                }

                // Audit log
                $audit = $db->prepare("INSERT INTO security_audit_logs (tenant_id, user_id, action, ip_address, details) VALUES (:tid, :uid, :act, :ip, :det)");
                $audit->execute([
                    ':tid' => $tenantId ?? 1,
                    ':uid' => $user['id'] ?? 1,
                    ':act' => 'CRUD_UPDATE_' . strtoupper($moduleKey),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':det' => "Updated record ID {$recordId} in {$tableName}"
                ]);
            }
        } catch (\Throwable $e) {
            error_log("Module Update Error: " . $e->getMessage());
            @file_put_contents(BASE_PATH . '/scratch/last_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        Response::redirect("index.php?page=module&name={$moduleKey}");
    }

    // DELETE Operation
    public function deleteRecord(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $moduleKey = Validator::sanitize($_POST['module_key'] ?? 'enquiry');
        $recordId = (int) ($_POST['record_id'] ?? 0);

        if (!isset($this->modulesConfig[$moduleKey]) || $recordId <= 0) {
            Response::redirect('index.php?page=dashboard');
        }

        Permission::enforceManage($moduleKey);

        $config = $this->modulesConfig[$moduleKey];
        $tableName = $config['table'];
        $db = Database::getInstance();
        $user = Auth::user();
        $tenantId = $user['tenant_id'];

        try {
            $colStmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);

            if ($tableName === 'staff') {
                $stfUserStmt = $db->prepare("SELECT user_id FROM staff WHERE id = :id");
                $stfUserStmt->execute([':id' => $recordId]);
                $linkedUserId = (int) $stfUserStmt->fetchColumn();

                if ($tenantId !== null && $user['role_name'] !== 'super_admin' && in_array('tenant_id', $columns)) {
                    $stmt = $db->prepare("DELETE FROM `{$tableName}` WHERE id = :id AND tenant_id = :tid");
                    $stmt->execute([':id' => $recordId, ':tid' => $tenantId]);
                } else {
                    $stmt = $db->prepare("DELETE FROM `{$tableName}` WHERE id = :id");
                    $stmt->execute([':id' => $recordId]);
                }

                if ($linkedUserId > 0) {
                    $delUsr = $db->prepare("DELETE FROM users WHERE id = :uid");
                    $delUsr->execute([':uid' => $linkedUserId]);
                }
            } elseif ($tenantId !== null && $user['role_name'] !== 'super_admin' && in_array('tenant_id', $columns)) {
                $stmt = $db->prepare("DELETE FROM `{$tableName}` WHERE id = :id AND tenant_id = :tid");
                $stmt->execute([':id' => $recordId, ':tid' => $tenantId]);
            } else {
                $stmt = $db->prepare("DELETE FROM `{$tableName}` WHERE id = :id");
                $stmt->execute([':id' => $recordId]);
            }

            $audit = $db->prepare("INSERT INTO security_audit_logs (tenant_id, user_id, action, ip_address, details) VALUES (:tid, :uid, :act, :ip, :det)");
            $audit->execute([
                ':tid' => $tenantId ?? 1,
                ':uid' => $user['id'] ?? 1,
                ':act' => 'CRUD_DELETE_' . strtoupper($moduleKey),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':det' => "Deleted record ID {$recordId} from {$tableName}"
            ]);

        } catch (\Throwable $e) {
            error_log("Module Delete Error: " . $e->getMessage());
            @file_put_contents(BASE_PATH . '/scratch/last_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        Response::redirect("index.php?page=module&name={$moduleKey}");
    }

    private function getValidFkId(PDO $db, string $foreignTable, int $tenantId, ?int $deptId = null): ?int
    {
        try {
            if ($deptId && $foreignTable === 'batches') {
                $stmt = $db->prepare("SELECT b.id FROM batches b JOIN courses c ON b.course_id = c.id WHERE c.department_id = :did LIMIT 1");
                $stmt->execute([':did' => $deptId]);
                $id = $stmt->fetchColumn();
                if ($id)
                    return (int) $id;
            }

            // Try tenant-scoped query
            $stmt = $db->prepare("SELECT id FROM `{$foreignTable}` WHERE tenant_id = :tid ORDER BY id ASC LIMIT 1");
            $stmt->execute([':tid' => $tenantId]);
            $id = $stmt->fetchColumn();
            if ($id)
                return (int) $id;

            // Try global fallback query
            $stmt = $db->query("SELECT id FROM `{$foreignTable}` ORDER BY id ASC LIMIT 1");
            $id = $stmt->fetchColumn();
            if ($id)
                return (int) $id;

            // Auto-provision reference parent row if table is completely empty
            if ($foreignTable === 'departments') {
                $db->exec("INSERT INTO departments (tenant_id, name, code) VALUES ({$tenantId}, 'General Department', 'DEPT-GEN')");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'courses') {
                $deptIdVal = $this->getValidFkId($db, 'departments', $tenantId, $deptId) ?? 1;
                $db->exec("INSERT INTO courses (tenant_id, department_id, name, code) VALUES ({$tenantId}, {$deptIdVal}, 'General Course', 'CRS-GEN')");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'batches') {
                $crsIdVal = $this->getValidFkId($db, 'courses', $tenantId, $deptId) ?? 1;
                $db->exec("INSERT INTO batches (tenant_id, course_id, batch_name, start_year, end_year) VALUES ({$tenantId}, {$crsIdVal}, 'Batch 2026-2027', 2026, 2027)");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'staff') {
                $deptIdVal = $this->getValidFkId($db, 'departments', $tenantId, $deptId) ?? 1;
                $empNo = 'EMP-AUTO-' . rand(100, 999);
                $db->exec("INSERT INTO staff (tenant_id, emp_id, first_name, last_name, designation, department_id, joining_date) VALUES ({$tenantId}, '{$empNo}', 'Faculty', 'Member', 'Professor', {$deptIdVal}, CURDATE())");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'students') {
                $batchIdVal = $this->getValidFkId($db, 'batches', $tenantId, $deptId) ?? 1;
                $admNo = 'ADM-AUTO-' . rand(100, 999);
                $rollNo = 'R-AUTO-' . rand(100, 999);
                $db->exec("INSERT INTO students (tenant_id, admission_no, roll_no, first_name, last_name, gender, dob, batch_id) VALUES ({$tenantId}, '{$admNo}', '{$rollNo}', 'Student', 'Member', 'male', '2005-01-01', {$batchIdVal})");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'fee_structures') {
                $crsIdVal = $this->getValidFkId($db, 'courses', $tenantId, $deptId) ?? 1;
                $db->exec("INSERT INTO fee_structures (tenant_id, course_id, academic_year, fee_type, amount, due_date) VALUES ({$tenantId}, {$crsIdVal}, '2026-2027', 'Tuition Fee', 10500.00, CURDATE())");
                return (int) $db->lastInsertId();
            }
            if ($foreignTable === 'fee_installments') {
                $stVal = $this->getValidFkId($db, 'students', $tenantId, $deptId) ?? 1;
                $feeStructVal = $this->getValidFkId($db, 'fee_structures', $tenantId, $deptId) ?? 1;
                $db->exec("INSERT INTO fee_installments (tenant_id, student_id, fee_structure_id, title, amount, due_date, status) VALUES ({$tenantId}, {$stVal}, {$feeStructVal}, 'Term 1 Installment', 10500.00, CURDATE(), 'unpaid')");
                return (int) $db->lastInsertId();
            }
        } catch (\Throwable $e) {
            error_log("FK Provision Error for {$foreignTable}: " . $e->getMessage());
        }
        return 1;
    }

    public function storeDepartment(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $user = Auth::user();
        if (!in_array($user['role_name'], ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher'], true)) {
            Session::setFlash('error', 'Unauthorized: Only Authorized Staff and Teachers can create departments.');
            Response::redirect('index.php?page=dashboard');
            return;
        }

        $tenantId = $user['tenant_id'] ?? 1;
        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = Validator::sanitize($_POST['code'] ?? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 8)));
        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'dashboard');

        if (empty($name)) {
            Session::setFlash('error', 'Department name is required.');
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO departments (tenant_id, name, code) VALUES (:tid, :name, :code)");
        $stmt->execute([
            ':tid' => $tenantId,
            ':name' => $name,
            ':code' => $code,
        ]);
        $deptId = $db->lastInsertId();

        // Seed default academic standards (FY, SY, TY) for this newly created department
        $standards = [
            ['name' => 'FY', 'code' => 'FY', 'seq' => 1],
            ['name' => 'SY', 'code' => 'SY', 'seq' => 2],
            ['name' => 'TY', 'code' => 'TY', 'seq' => 3],
        ];
        $stStmt = $db->prepare("INSERT INTO academic_standards (tenant_id, department_id, name, code, sequence) VALUES (:tid, :did, :name, :code, :seq)");
        foreach ($standards as $std) {
            $stStmt->execute([
                ':tid' => $tenantId,
                ':did' => $deptId,
                ':name' => $std['name'],
                ':code' => $std['code'],
                ':seq' => $std['seq'],
            ]);
        }

        Session::setFlash('success', "Department '{$name}' created successfully with standards (FY, SY, TY)!");
        Response::redirect('index.php?page=' . $redirectPage);
    }

    public function deleteDepartment(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $user = Auth::user();
        if (!in_array($user['role_name'], ['super_admin', 'school_admin', 'principal', 'administrator'], true)) {
            Session::setFlash('error', 'Unauthorized Action.');
            Response::redirect('index.php?page=dashboard');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'dashboard');

        if ($id > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM departments WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
            $stmt->execute([':id' => $id, ':tid' => $user['tenant_id'] ?? 1, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            Session::setFlash('success', 'Department deleted successfully.');
        }

        Response::redirect('index.php?page=' . $redirectPage);
    }

    public function storeStandard(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $user = Auth::user();
        if (!in_array($user['role_name'], ['super_admin', 'school_admin', 'principal', 'administrator', 'teacher'], true)) {
            Session::setFlash('error', 'Unauthorized: Only Authorized Staff and Teachers can create academic divisions / standards.');
            Response::redirect('index.php?page=dashboard');
            return;
        }

        $tenantId = $user['tenant_id'] ?? 1;
        $deptId = (int) ($_POST['department_id'] ?? 0);
        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = Validator::sanitize($_POST['code'] ?? $name);
        $seq = (int) ($_POST['sequence'] ?? 1);
        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'dashboard');

        if (empty($name)) {
            Session::setFlash('error', 'Division / Standard name is required.');
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO academic_standards (tenant_id, department_id, name, code, sequence) VALUES (:tid, :did, :name, :code, :seq)");
        $stmt->execute([
            ':tid' => $tenantId,
            ':did' => $deptId > 0 ? $deptId : null,
            ':name' => $name,
            ':code' => $code,
            ':seq' => $seq,
        ]);

        // Also ensure a section / division entry exists if section name given
        $secStmt = $db->prepare("INSERT INTO sections (tenant_id, department_id, standard_id, class_id, name, code) VALUES (:tid, :did, :sid, 1, :name, :code)");
        $secStmt->execute([
            ':tid' => $tenantId,
            ':did' => $deptId > 0 ? $deptId : null,
            ':sid' => $db->lastInsertId(),
            ':name' => 'Division ' . $name,
            ':code' => $code,
        ]);

        Session::setFlash('success', "Academic Division / Standard '{$name}' (FY/SY/TY) created successfully!");
        Response::redirect('index.php?page=' . $redirectPage);
    }

    public function assignTeacher(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $user = Auth::user();
        if (!in_array($user['role_name'], ['super_admin', 'school_admin', 'principal', 'administrator'], true)) {
            Session::setFlash('error', 'Unauthorized: Only Administrators can assign teachers.');
            Response::redirect('index.php?page=module&name=staff_data');
            return;
        }

        $tenantId = $user['tenant_id'] ?? 1;
        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $deptId = (int) ($_POST['department_id'] ?? 0);
        $standardId = (int) ($_POST['standard_id'] ?? 0);
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);

        if ($staffId === 0) {
            Session::setFlash('error', 'Teacher / Faculty selection is required.');
            Response::redirect('index.php?page=module&name=staff_data');
            return;
        }

        $db = Database::getInstance();

        // Sync department to staff record if provided
        if ($deptId > 0) {
            $uDept = $db->prepare("UPDATE staff SET department_id = :did WHERE id = :stfid AND (tenant_id = :tid OR :is_super = 1)");
            $uDept->execute([':did' => $deptId, ':stfid' => $staffId, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
        }

        $stmt = $db->prepare("
            INSERT INTO teacher_assignments (tenant_id, staff_id, department_id, standard_id, section_id, subject_id)
            VALUES (:tid, :stfid, :did, :sid, :secid, :subid)
        ");
        $stmt->execute([
            ':tid' => $tenantId,
            ':stfid' => $staffId,
            ':did' => $deptId > 0 ? $deptId : null,
            ':sid' => $standardId > 0 ? $standardId : null,
            ':secid' => $sectionId > 0 ? $sectionId : null,
            ':subid' => $subjectId > 0 ? $subjectId : null,
        ]);

        Session::setFlash('success', 'Teacher academic assignment created successfully!');
        Response::redirect('index.php?page=module&name=staff_data');
    }

    public function deleteTeacherAssignment(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        $user = Auth::user();
        if (!in_array($user['role_name'], ['super_admin', 'school_admin', 'principal', 'administrator'], true)) {
            Session::setFlash('error', 'Unauthorized Action.');
            Response::redirect('index.php?page=module&name=staff_data');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $tenantId = $user['tenant_id'] ?? 1;

        if ($id > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM teacher_assignments WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
            $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            Session::setFlash('success', 'Teacher assignment removed.');
        }

        Response::redirect('index.php?page=module&name=staff_data');
    }
}

