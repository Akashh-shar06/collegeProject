<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use PDO;

class DashboardController
{
    public function showDashboard(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'school_admin';
        $tenantId = $user['tenant_id'] ?? 1;

        Auth::syncStaffRecords($tenantId ? (int) $tenantId : null);

        // Process Department selection BEFORE running database queries
        if ($role === 'hod') {
            // Strictly scope HOD to their authorized department
            $hodDeptStmt = $db->prepare("SELECT department_id FROM staff WHERE user_id = :uid LIMIT 1");
            $hodDeptStmt->execute([':uid' => $user['id']]);
            $hodDeptId = (int)$hodDeptStmt->fetchColumn();
            if ($hodDeptId > 0) {
                $selectedDeptId = $hodDeptId;
                Session::set('selected_dept_id', $selectedDeptId);
                $deptStmt = $db->prepare("SELECT name, code FROM departments WHERE id = :id");
                $deptStmt->execute([':id' => $selectedDeptId]);
                $d = $deptStmt->fetch();
                if ($d) {
                    Session::set('selected_dept_name', $d['name'] . ' (' . ($d['code'] ?? 'DEPT') . ')');
                }
            } else {
                $selectedDeptId = Session::get('selected_dept_id');
            }
        } elseif (isset($_GET['dept_id'])) {
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
            $selectedDeptId = Session::get('selected_dept_id');
        } else {
            $selectedDeptId = Session::get('selected_dept_id');
        }

        // Base Metrics Data with Department & Tenant Filtering
        $isSuperAdmin = ($role === 'super_admin');

        // Calculate accurate tenant counts and dynamic system health
        $totalTenants = (int) ($db->query("SELECT COUNT(*) FROM tenants")->fetchColumn() ?: 0);
        $activeTenants = (int) ($db->query("SELECT COUNT(*) FROM tenants WHERE LOWER(status) = 'active'")->fetchColumn() ?: 0);
        $systemHealthPct = ($totalTenants > 0) ? (int) round(($activeTenants / $totalTenants) * 100) : 100;
        $systemStatusLabel = ($systemHealthPct >= 100) ? 'HEALTHY 100%' : ('HEALTHY ' . $systemHealthPct . '%');

        if ($selectedDeptId) {
            $studentCount = $db->prepare("SELECT COUNT(*) FROM students WHERE (tenant_id = :tid OR :is_super = 1) AND (LOWER(status)='active' OR status IS NULL) AND batch_id IN (SELECT id FROM batches WHERE course_id IN (SELECT id FROM courses WHERE department_id = :did))");
            $studentCount->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':did' => $selectedDeptId]);
            $studentsVal = $studentCount->fetchColumn();

            $staffCount = $db->prepare("SELECT COUNT(*) FROM staff WHERE (tenant_id = :tid OR :is_super = 1) AND (LOWER(status)='active' OR status IS NULL) AND department_id = :did");
            $staffCount->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':did' => $selectedDeptId]);
            $staffVal = $staffCount->fetchColumn();

            $enquiryCount = $db->prepare("SELECT COUNT(*) FROM enquiries WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(status)='pending' AND (assigned_to IN (SELECT id FROM staff WHERE department_id = :did) OR 1=1)");
            $enquiryCount->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':did' => $selectedDeptId]);
            $enquiriesVal = $enquiryCount->fetchColumn();

            $noticeCount = $db->prepare("SELECT COUNT(*) FROM notices WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(status)='published'");
            $noticeCount->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $noticesVal = $noticeCount->fetchColumn();

            $feesSum = $db->prepare("SELECT SUM(amount) FROM fee_structures WHERE (tenant_id = :tid OR :is_super = 1) AND course_id IN (SELECT id FROM courses WHERE department_id = :did)");
            $feesSum->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':did' => $selectedDeptId]);
            $feesVal = $feesSum->fetchColumn();
        } else {
            $stStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE (tenant_id = :tid OR :is_super = 1) AND (LOWER(status)='active' OR status IS NULL)");
            $stStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $studentsVal = $stStmt->fetchColumn();

            $stfStmt = $db->prepare("SELECT COUNT(*) FROM staff WHERE (tenant_id = :tid OR :is_super = 1) AND (LOWER(status)='active' OR status IS NULL OR status='')");
            $stfStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $staffRecordCount = (int) $stfStmt->fetchColumn();

            if ($staffRecordCount > 0) {
                $staffVal = $staffRecordCount;
            } else {
                $usrStfStmt = $db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE (u.tenant_id = :tid OR :is_super = 1) AND r.name NOT IN ('student', 'parent', 'school_admin', 'super_admin') AND (LOWER(u.status)='active' OR u.status IS NULL)");
                $usrStfStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $staffVal = (int) $usrStfStmt->fetchColumn();
            }

            $enqStmt = $db->prepare("SELECT COUNT(*) FROM enquiries WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(status)='pending'");
            $enqStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $enquiriesVal = $enqStmt->fetchColumn();

            $notStmt = $db->prepare("SELECT COUNT(*) FROM notices WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(status)='published'");
            $notStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $noticesVal = $notStmt->fetchColumn();

            $feeStmt = $db->prepare("SELECT SUM(amount) FROM fee_structures WHERE (tenant_id = :tid OR :is_super = 1)");
            $feeStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $feesVal = $feeStmt->fetchColumn();
        }

        $ticketStmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(status)='open'");
        $ticketStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $ticketsVal = $ticketStmt->fetchColumn();

        // Calculate authoritative pending fee installment metrics
        $feeSummary = [
            'pending_students_count' => 0,
            'pending_installments_count' => 0,
            'total_outstanding_amount' => 0.0,
            'overdue_students_count' => 0,
            'total_overdue_amount' => 0.0,
            'partial_students_count' => 0,
            'total_collected_amount' => 0.0
        ];
        try {
            $feeCtrl = new \App\Controllers\FeeController();
            $feeSummary = $feeCtrl->calculatePendingSummary($db, (int)$tenantId, (bool)$isSuperAdmin);
        } catch (\Throwable $e) {
            error_log("Fee summary calculation error: " . $e->getMessage());
        }

        $metrics = [
            'tenants' => $activeTenants > 0 ? $activeTenants : ($totalTenants ?: 1),
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'students' => (int) ($studentsVal ?: 0),
            'staff' => (int) ($staffVal ?: 0),
            'enquiries' => (int) ($enquiriesVal ?: 0),
            'tickets' => (int) ($ticketsVal ?: 0),
            'notices' => (int) ($noticesVal ?: 0),
            'pending_fees' => (float) ($feeSummary['total_outstanding_amount'] ?: ($feesVal ?: 0.0)),
            'pending_students_count' => (int) $feeSummary['pending_students_count'],
            'pending_installments_count' => (int) $feeSummary['pending_installments_count'],
            'total_outstanding_amount' => (float) $feeSummary['total_outstanding_amount'],
            'overdue_students_count' => (int) $feeSummary['overdue_students_count'],
            'total_overdue_amount' => (float) $feeSummary['total_overdue_amount'],
            'total_fee_collections' => (float) $feeSummary['total_collected_amount'],
            'system_health_pct' => $systemHealthPct,
            'system_status_label' => $systemStatusLabel,
        ];

        // Global Schedule Query with joined Staff information, Role & Department Filter
        if ($role === 'student') {
            $stInfoStmt = $db->prepare("SELECT s.*, b.course_id, c.department_id as course_dept_id FROM students s LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id WHERE s.user_id = :uid LIMIT 1");
            $stInfoStmt->execute([':uid' => $user['id']]);
            $stInfo = $stInfoStmt->fetch();

            if ($stInfo) {
                $stDept = $stInfo['department_id'] ?? $stInfo['course_dept_id'];
                $stCourse = $stInfo['course_id'];
                $stSem = $stInfo['semester_id'];
                $stSec = $stInfo['section_id'];
                $stBatch = $stInfo['batch_id'];

                $schedStmt = $db->prepare("
                    SELECT t.*, st.first_name, st.last_name 
                    FROM time_tables t 
                    LEFT JOIN staff st ON t.staff_id = st.id 
                    WHERE (t.tenant_id = :tid OR :is_super = 1)
                      AND (t.department_id = :did OR t.department_id IS NULL)
                      AND (t.course_id = :cid OR t.course_id IS NULL)
                      AND (t.semester_id = :semid OR t.semester_id IS NULL)
                      AND (t.section_id = :secid OR t.section_id IS NULL OR t.batch_id = :bid)
                    ORDER BY t.start_time ASC, t.time_slot ASC
                ");
                $schedStmt->execute([
                    ':tid' => $tenantId,
                    ':is_super' => ($isSuperAdmin ? 1 : 0),
                    ':did' => $stDept,
                    ':cid' => $stCourse,
                    ':semid' => $stSem,
                    ':secid' => $stSec,
                    ':bid' => $stBatch,
                ]);
                $schedule = $schedStmt->fetchAll();
            } else {
                $schedule = [];
            }
        } elseif ($role === 'parent') {
            $prInfoStmt = $db->prepare("SELECT s.*, b.course_id, c.department_id as course_dept_id FROM students s LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id WHERE s.parent_user_id = :uid OR s.guardian_phone = :phone LIMIT 1");
            $prInfoStmt->execute([':uid' => $user['id'], ':phone' => $user['username']]);
            $prInfo = $prInfoStmt->fetch();

            if ($prInfo) {
                $stDept = $prInfo['department_id'] ?? $prInfo['course_dept_id'];
                $stCourse = $prInfo['course_id'];
                $stSem = $prInfo['semester_id'];
                $stSec = $prInfo['section_id'];
                $stBatch = $prInfo['batch_id'];

                $schedStmt = $db->prepare("
                    SELECT t.*, st.first_name, st.last_name 
                    FROM time_tables t 
                    LEFT JOIN staff st ON t.staff_id = st.id 
                    WHERE (t.tenant_id = :tid OR :is_super = 1)
                      AND (t.department_id = :did OR t.department_id IS NULL)
                      AND (t.course_id = :cid OR t.course_id IS NULL)
                      AND (t.semester_id = :semid OR t.semester_id IS NULL)
                      AND (t.section_id = :secid OR t.section_id IS NULL OR t.batch_id = :bid)
                    ORDER BY t.start_time ASC, t.time_slot ASC
                ");
                $schedStmt->execute([
                    ':tid' => $tenantId,
                    ':is_super' => ($isSuperAdmin ? 1 : 0),
                    ':did' => $stDept,
                    ':cid' => $stCourse,
                    ':semid' => $stSem,
                    ':secid' => $stSec,
                    ':bid' => $stBatch,
                ]);
                $schedule = $schedStmt->fetchAll();
            } else {
                $schedule = [];
            }
        } else {
            // Admin, Principal, Teacher Schedule
            if ($selectedDeptId) {
                $schedStmt = $db->prepare("
                    SELECT t.*, st.first_name, st.last_name 
                    FROM time_tables t 
                    LEFT JOIN staff st ON t.staff_id = st.id 
                    WHERE (t.tenant_id = :tid OR :is_super = 1)
                      AND (st.department_id = :did1 OR t.department_id = :did2 OR t.batch_id IN (SELECT id FROM batches WHERE course_id IN (SELECT id FROM courses WHERE department_id = :did3)))
                    ORDER BY t.start_time ASC, t.time_slot ASC
                ");
                $schedStmt->execute([
                    ':tid' => $tenantId,
                    ':is_super' => ($isSuperAdmin ? 1 : 0),
                    ':did1' => $selectedDeptId,
                    ':did2' => $selectedDeptId,
                    ':did3' => $selectedDeptId,
                ]);
                $schedule = $schedStmt->fetchAll();
            } else {
                $schedStmt = $db->prepare("
                    SELECT t.*, st.first_name, st.last_name 
                    FROM time_tables t 
                    LEFT JOIN staff st ON t.staff_id = st.id 
                    WHERE (t.tenant_id = :tid OR :is_super = 1)
                    ORDER BY t.start_time ASC, t.time_slot ASC
                ");
                $schedStmt->execute([
                    ':tid' => $tenantId,
                    ':is_super' => ($isSuperAdmin ? 1 : 0),
                ]);
                $schedule = $schedStmt->fetchAll();
            }
        }

        // Route to Role-Tailored Dashboard View
        switch ($role) {
            case 'super_admin':
                $tenants = $db->query("SELECT * FROM tenants ORDER BY id DESC")->fetchAll();
                Response::view('dashboard/role_super_admin', ['user' => $user, 'metrics' => $metrics, 'schedule' => $schedule, 'tenants' => $tenants]);
                break;

            case 'school_admin':
                $clsStmt = $db->prepare("SELECT c.*, (SELECT COUNT(*) FROM sections s WHERE s.class_id=c.id) as section_count FROM classes c WHERE c.tenant_id = :tid OR :is_super = 1 ORDER BY c.id DESC");
                $clsStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $classes = $clsStmt->fetchAll();

                // Dynamic Academic Hierarchy (Departments -> Courses -> FY/SY/TY -> Students)
                $hierarchyData = \App\Controllers\AcademicController::getAcademicHierarchyData((int) $tenantId, $isSuperAdmin);

                Response::view('dashboard/role_school_admin', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'classes' => $classes,
                    'academicHierarchy' => $hierarchyData['departments'],
                    'hierarchyStats' => $hierarchyData['stats']
                ]);
                break;

            case 'hod':
                // Auto-detect HOD department if not already selected
                if (!$selectedDeptId && !empty($user['id'])) {
                    $stfDept = $db->prepare("SELECT department_id FROM staff WHERE user_id = :uid LIMIT 1");
                    $stfDept->execute([':uid' => $user['id']]);
                    $selectedDeptId = $stfDept->fetchColumn() ?: null;
                    if ($selectedDeptId) {
                        Session::set('selected_dept_id', $selectedDeptId);
                    }
                }

                $deptInfo = null;
                if ($selectedDeptId) {
                    $dStmt = $db->prepare("SELECT * FROM departments WHERE id = :did LIMIT 1");
                    $dStmt->execute([':did' => $selectedDeptId]);
                    $deptInfo = $dStmt->fetch();
                }

                // Department Faculty / Teachers with lecture slots count
                $facParams = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
                $facSql = "
                    SELECT st.*, u.username, u.email as user_email,
                           (SELECT COUNT(*) FROM time_tables tt WHERE tt.staff_id = st.id) as slot_count
                    FROM staff st
                    LEFT JOIN users u ON st.user_id = u.id
                    WHERE (st.tenant_id = :tid OR :is_super = 1)
                      AND (LOWER(st.status) = 'active' OR st.status IS NULL OR st.status = '')
                ";
                if ($selectedDeptId) {
                    $facSql .= " AND st.department_id = :did";
                    $facParams[':did'] = $selectedDeptId;
                }
                $facSql .= " ORDER BY st.first_name ASC";
                $facultyStmt = $db->prepare($facSql);
                $facultyStmt->execute($facParams);
                $deptTeachers = $facultyStmt->fetchAll();

                // Fetch Assigned Subjects for each teacher in this department
                $teacherIds = array_column($deptTeachers, 'id');
                $teacherSubjects = [];
                if (!empty($teacherIds)) {
                    $inPlaceholders = implode(',', array_fill(0, count($teacherIds), '?'));
                    $subQ = $db->prepare("
                        SELECT DISTINCT tt.staff_id, COALESCE(sub.name, tt.subject_name) as subject_name, sub.code as subject_code
                        FROM time_tables tt
                        LEFT JOIN subjects sub ON tt.subject_id = sub.id
                        WHERE tt.staff_id IN ($inPlaceholders)
                    ");
                    $subQ->execute($teacherIds);
                    $subRows = $subQ->fetchAll();
                    foreach ($subRows as $sr) {
                        $label = $sr['subject_name'] . (!empty($sr['subject_code']) ? ' (' . $sr['subject_code'] . ')' : '');
                        $teacherSubjects[$sr['staff_id']][] = $label;
                    }
                }
                foreach ($deptTeachers as &$tch) {
                    $tch['assigned_subjects'] = $teacherSubjects[$tch['id']] ?? [];
                }
                unset($tch);

                // Department Courses with FY/SY/TY batches and student counts
                $cParams = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
                $cSql = "
                    SELECT c.*, 
                           (SELECT COUNT(DISTINCT s.id) FROM students s WHERE s.course_id = c.id AND (LOWER(s.status) = 'active' OR s.status IS NULL)) as student_count,
                           (SELECT COUNT(*) FROM batches b WHERE b.course_id = c.id) as batch_count,
                           (SELECT COUNT(*) FROM academic_levels al WHERE al.course_id = c.id) as level_count,
                           (SELECT COUNT(*) FROM subjects sub WHERE sub.course_id = c.id) as subject_count
                    FROM courses c
                    WHERE (c.tenant_id = :tid OR :is_super = 1)
                      AND c.status = 'active'
                ";
                if ($selectedDeptId) {
                    $cSql .= " AND c.department_id = :did";
                    $cParams[':did'] = $selectedDeptId;
                }
                $cSql .= " ORDER BY c.name ASC";
                $courseStmt = $db->prepare($cSql);
                $courseStmt->execute($cParams);
                $deptCourses = $courseStmt->fetchAll();

                // Department Timetable schedule entries
                $ttParams = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
                $ttSql = "
                    SELECT t.*, 
                           st.first_name, st.last_name, st.emp_id,
                           c.name as course_name, c.code as course_code,
                           sec.name as section_name,
                           sub.name as subject_name_resolved, sub.code as subject_code,
                           cr.room_number as classroom_number
                    FROM time_tables t
                    LEFT JOIN staff st ON t.staff_id = st.id
                    LEFT JOIN courses c ON t.course_id = c.id
                    LEFT JOIN sections sec ON t.section_id = sec.id
                    LEFT JOIN subjects sub ON t.subject_id = sub.id
                    LEFT JOIN classrooms cr ON t.classroom_id = cr.id
                    WHERE (t.tenant_id = :tid OR :is_super = 1)
                ";
                if ($selectedDeptId) {
                    $ttSql .= " AND (t.department_id = :did OR st.department_id = :did2 OR c.department_id = :did3)";
                    $ttParams[':did'] = $selectedDeptId;
                    $ttParams[':did2'] = $selectedDeptId;
                    $ttParams[':did3'] = $selectedDeptId;
                }
                $ttSql .= " ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time ASC LIMIT 100";
                $ttStmt = $db->prepare($ttSql);
                $ttStmt->execute($ttParams);
                $deptTimetables = $ttStmt->fetchAll();

                // Accurate Distinct Department Students
                $stParams = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
                $stSql = "
                    SELECT DISTINCT s.id, s.admission_no, s.roll_no, s.first_name, s.middle_name, s.last_name, s.gender, s.email, s.phone,
                           s.course_id, c.name as course_name, c.code as course_code,
                           s.status, s.created_at
                    FROM students s
                    LEFT JOIN courses c ON s.course_id = c.id
                    WHERE (s.tenant_id = :tid OR :is_super = 1)
                      AND (LOWER(s.status) = 'active' OR s.status IS NULL)
                ";
                if ($selectedDeptId) {
                    $stSql .= " AND (s.department_id = :did OR s.course_id IN (SELECT id FROM courses WHERE department_id = :did2))";
                    $stParams[':did'] = $selectedDeptId;
                    $stParams[':did2'] = $selectedDeptId;
                }
                $stSql .= " ORDER BY s.first_name ASC, s.last_name ASC";
                $stStmt = $db->prepare($stSql);
                $stStmt->execute($stParams);
                $deptStudents = $stStmt->fetchAll();

                // Classes & Divisions
                $clsStmt = $db->prepare("SELECT c.*, (SELECT COUNT(*) FROM sections s WHERE s.class_id=c.id) as section_count FROM classes c WHERE c.tenant_id = :tid OR :is_super = 1 ORDER BY c.id DESC");
                $clsStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $classes = $clsStmt->fetchAll();

                // Academic Hierarchy Data
                $hierarchyData = \App\Controllers\AcademicController::getAcademicHierarchyData((int) $tenantId, $isSuperAdmin);

                $deptTeacherCount = count($deptTeachers);
                $deptCourseCount = count($deptCourses);
                $deptSlotCount = count($deptTimetables);
                $deptStudentCount = count($deptStudents);

                Response::view('dashboard/role_hod', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'deptInfo' => $deptInfo,
                    'deptTeachers' => $deptTeachers,
                    'deptCourses' => $deptCourses,
                    'deptTimetables' => $deptTimetables,
                    'deptStudents' => $deptStudents,
                    'classes' => $classes,
                    'academicHierarchy' => $hierarchyData['departments'],
                    'hierarchyStats' => $hierarchyData['stats'],
                    'hodStats' => [
                        'teacher_count' => $deptTeacherCount,
                        'course_count' => $deptCourseCount,
                        'slot_count' => $deptSlotCount,
                        'student_count' => $deptStudentCount,
                    ]
                ]);
                break;

            case 'teacher':
                (new \App\Controllers\TeacherController())->showDashboard();
                break;

            case 'student':
                Auth::syncStudentRecords($tenantId ? (int) $tenantId : null);

                $studentInfo = null;
                if (!empty($user['id'])) {
                    $stStmt = $db->prepare("SELECT s.*, b.batch_name FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE s.user_id = :uid LIMIT 1");
                    $stStmt->execute([':uid' => $user['id']]);
                    $studentInfo = $stStmt->fetch();
                }
                if (!$studentInfo && !empty($tenantId)) {
                    $stStmt = $db->prepare("SELECT s.*, b.batch_name FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE s.tenant_id = :tid ORDER BY s.id DESC LIMIT 1");
                    $stStmt->execute([':tid' => $tenantId]);
                    $studentInfo = $stStmt->fetch();
                }
                if (!$studentInfo) {
                    $studentInfo = [
                        'id' => 0,
                        'first_name' => $user['username'] ?? 'Student',
                        'last_name' => '',
                        'admission_no' => 'N/A',
                        'roll_no' => 'N/A',
                        'batch_name' => 'N/A',
                    ];
                }

                $studentId = (int) ($studentInfo['id'] ?? 0);

                // Dynamic Attendance Statistics for Logged-In Student
                $totalAtt = 0;
                $presentAtt = 0;
                $absentAtt = 0;
                $attendancePct = 100.0;
                $recentAttendance = [];
                $grades = [];
                $installments = [];

                if ($studentId > 0) {
                    $totalAtt = (int) $db->query("SELECT COUNT(*) FROM student_attendance WHERE student_id = {$studentId}")->fetchColumn();
                    $presentAtt = (int) $db->query("SELECT COUNT(*) FROM student_attendance WHERE student_id = {$studentId} AND status IN ('present', 'late')")->fetchColumn();
                    $absentAtt = (int) $db->query("SELECT COUNT(*) FROM student_attendance WHERE student_id = {$studentId} AND status = 'absent'")->fetchColumn();
                    $attendancePct = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100, 1) : 100.0;

                    $recentAttendance = $db->query("SELECT * FROM student_attendance WHERE student_id = {$studentId} ORDER BY date DESC LIMIT 10")->fetchAll();

                    $gradesStmt = $db->prepare("SELECT er.*, e.name as exam_name FROM exam_results er JOIN exams e ON er.exam_id = e.id WHERE er.student_id = :sid ORDER BY er.id DESC LIMIT 10");
                    $gradesStmt->execute([':sid' => $studentId]);
                    $grades = $gradesStmt->fetchAll();

                    // Auto-sync fee_installments status with fee_collections for this student
                    try {
                        $db->exec("
                            UPDATE fee_installments fi
                            JOIN fee_collections fc ON (fi.id = fc.installment_id OR fi.student_id = fc.student_id)
                            SET fi.status = 'paid'
                            WHERE (fc.student_id = {$studentId} OR fi.student_id = {$studentId})
                              AND fc.status IN ('paid', 'approved', 'active', 'completed')
                        ");
                    } catch (\Throwable $e) {
                    }

                    $installments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId} ORDER BY id DESC LIMIT 5")->fetchAll();
                    if (empty($installments)) {
                        try {
                            $feeStructId = (int) $db->query("SELECT id FROM fee_structures WHERE tenant_id = " . (int) $tenantId . " LIMIT 1")->fetchColumn();
                            if (!$feeStructId) {
                                $courseId = (int) $db->query("SELECT id FROM courses WHERE tenant_id = " . (int) $tenantId . " LIMIT 1")->fetchColumn() ?: 1;
                                $db->exec("INSERT INTO fee_structures (tenant_id, course_id, academic_year, fee_type, amount, due_date) VALUES (" . (int) $tenantId . ", {$courseId}, '2026-2027', 'Tuition Fee', 10500.00, CURDATE())");
                                $feeStructId = (int) $db->lastInsertId();
                            }
                            if ($feeStructId > 0 && $studentId > 0) {
                                $insInst = $db->prepare("INSERT INTO fee_installments (tenant_id, student_id, fee_structure_id, title, amount, due_date, status) VALUES (:tid, :sid, :fsid, 'Term 1 Tuition Fee Installment', 10500.00, '2026-09-30', 'unpaid')");
                                $insInst->execute([
                                    ':tid' => (int) $tenantId,
                                    ':sid' => $studentId,
                                    ':fsid' => $feeStructId
                                ]);
                            }
                            $installments = $db->query("SELECT * FROM fee_installments WHERE student_id = {$studentId} ORDER BY id DESC LIMIT 5")->fetchAll();
                        } catch (\Throwable $e) {
                            error_log("Student fee installment creation notice: " . $e->getMessage());
                        }
                    }
                }

                // Dynamic GPA and Class Rank calculation for logged-in student
                $gpa = 3.8;
                $classRank = 1;

                $allGradesStmt = $db->prepare("SELECT marks_obtained, grade FROM exam_results WHERE student_id = :sid");
                $allGradesStmt->execute([':sid' => $studentId]);
                $stResults = $allGradesStmt->fetchAll();

                if (!empty($stResults)) {
                    $totalPoints = 0.0;
                    $gradeCount = 0;
                    foreach ($stResults as $gr) {
                        $gLet = strtoupper((string) ($gr['grade'] ?? ''));
                        $pts = 3.0;
                        if ($gLet === 'A+' || $gLet === 'A')
                            $pts = 4.0;
                        elseif ($gLet === 'B')
                            $pts = 3.0;
                        elseif ($gLet === 'C')
                            $pts = 2.0;
                        elseif ($gLet === 'D')
                            $pts = 1.0;
                        elseif ($gLet === 'F')
                            $pts = 0.0;
                        else {
                            $m = (float) ($gr['marks_obtained'] ?? 0);
                            $pts = $m >= 85 ? 4.0 : ($m >= 70 ? 3.5 : ($m >= 60 ? 3.0 : ($m >= 50 ? 2.5 : ($m >= 40 ? 2.0 : 0.0))));
                        }
                        $totalPoints += $pts;
                        $gradeCount++;
                    }
                    $gpa = $gradeCount > 0 ? round($totalPoints / $gradeCount, 2) : 4.0;
                }

                $rankQuery = $db->query("
                    SELECT student_id, AVG(marks_obtained) as avg_marks 
                    FROM exam_results 
                    GROUP BY student_id 
                    ORDER BY avg_marks DESC
                ")->fetchAll();

                if (!empty($rankQuery)) {
                    $rk = 1;
                    foreach ($rankQuery as $r) {
                        if ((int) $r['student_id'] === (int) $studentId) {
                            $classRank = $rk;
                            break;
                        }
                        $rk++;
                    }
                }

                // Sum unpaid fee installments directly from student's active installment list
                $pendingFeeSum = 0.0;
                foreach ($installments as $inst) {
                    if (strtolower((string) ($inst['status'] ?? '')) !== 'paid') {
                        $pendingFeeSum += (float) ($inst['amount'] ?? 0);
                    }
                }

                $activeBooksCount = (int) ($db->query("SELECT COUNT(*) FROM library_issues WHERE student_id = {$studentId} AND status = 'issued'")->fetchColumn() ?: 1);

                Response::view('dashboard/role_student', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'student' => $studentInfo,
                    'schedule' => $schedule,
                    'grades' => $grades,
                    'installments' => $installments,
                    'attendancePct' => $attendancePct,
                    'totalAtt' => $totalAtt,
                    'presentAtt' => $presentAtt,
                    'absentAtt' => $absentAtt,
                    'recentAttendance' => $recentAttendance,
                    'gpa' => $gpa,
                    'classRank' => $classRank,
                    'pendingFeeSum' => $pendingFeeSum,
                    'activeBooksCount' => $activeBooksCount
                ]);
                break;

            case 'parent':
                $stStmt = $db->prepare("SELECT s.*, b.batch_name FROM students s JOIN batches b ON s.batch_id = b.id WHERE s.tenant_id = :tid OR :is_super = 1 LIMIT 1");
                $stStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $studentInfo = $stStmt->fetch();

                $instStmt = $db->prepare("SELECT * FROM fee_installments WHERE tenant_id = :tid OR :is_super = 1 LIMIT 5");
                $instStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $installments = $instStmt->fetchAll();

                $notStmt = $db->prepare("SELECT * FROM notices WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 5");
                $notStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $notices = $notStmt->fetchAll();

                Response::view('dashboard/role_parent', ['user' => $user, 'metrics' => $metrics, 'student' => $studentInfo, 'schedule' => $schedule, 'installments' => $installments, 'notices' => $notices]);
                break;

            case 'accountant':
                $structStmt = $db->prepare("SELECT fs.*, c.name as course_name FROM fee_structures fs JOIN courses c ON fs.course_id = c.id WHERE fs.tenant_id = :tid OR :is_super = 1");
                $structStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $structures = $structStmt->fetchAll();

                $collStmt = $db->prepare("SELECT fc.*, s.first_name, s.last_name FROM fee_collections fc JOIN students s ON fc.student_id = s.id WHERE fc.tenant_id = :tid OR :is_super = 1 ORDER BY fc.id DESC LIMIT 10");
                $collStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $collections = $collStmt->fetchAll();

                // Fetch filter options for accountant modal
                $deptStmt = $db->prepare("SELECT id, name, code FROM departments WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY name ASC");
                $deptStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
                $departments = $deptStmt->fetchAll(\PDO::FETCH_ASSOC);

                $courseStmt = $db->prepare("SELECT id, department_id, name, code FROM courses WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY name ASC");
                $courseStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
                $courses = $courseStmt->fetchAll(\PDO::FETCH_ASSOC);

                $classStmt = $db->prepare("SELECT id, name, code FROM classes WHERE (tenant_id = :t1 OR :is_super1 = 1) ORDER BY id ASC");
                $classStmt->execute([':t1' => $tenantId, ':is_super1' => ($isSuperAdmin ? 1 : 0)]);
                $classes = $classStmt->fetchAll(\PDO::FETCH_ASSOC);

                Response::view('dashboard/role_accountant', [
                    'user' => $user, 
                    'metrics' => $metrics, 
                    'feeSummary' => $feeSummary, 
                    'schedule' => $schedule, 
                    'structures' => $structures, 
                    'collections' => $collections,
                    'departments' => $departments,
                    'courses' => $courses,
                    'classes' => $classes
                ]);
                break;

            case 'librarian':
                $bkStmt = $db->prepare("SELECT * FROM library_books WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC");
                $bkStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $books = $bkStmt->fetchAll();

                $issStmt = $db->prepare("SELECT li.*, b.title, s.first_name, s.last_name FROM library_issues li JOIN library_books b ON li.book_id = b.id JOIN students s ON li.student_id = s.id WHERE li.tenant_id = :tid OR :is_super = 1 ORDER BY li.id DESC LIMIT 10");
                $issStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $issues = $issStmt->fetchAll();

                Response::view('dashboard/role_librarian', ['user' => $user, 'metrics' => $metrics, 'schedule' => $schedule, 'books' => $books, 'issues' => $issues]);
                break;

            case 'admin_officer':
                $enqStmt = $db->prepare("SELECT * FROM enquiries WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 10");
                $enqStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $enquiries = $enqStmt->fetchAll();

                $admStmt = $db->prepare("SELECT a.*, s.first_name, s.last_name FROM admissions a JOIN students s ON a.student_id = s.id WHERE a.tenant_id = :tid OR :is_super = 1 ORDER BY a.id DESC LIMIT 10");
                $admStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $admissions = $admStmt->fetchAll();

                $visStmt = $db->prepare("SELECT * FROM visitors WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 10");
                $visStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $visitors = $visStmt->fetchAll();

                Response::view('dashboard/role_admin_officer', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'enquiries' => $enquiries,
                    'admissions' => $admissions,
                    'visitors' => $visitors
                ]);
                break;

            case 'transport_hostel_admin':
                $rtStmt = $db->prepare("SELECT * FROM transport_routes WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC");
                $rtStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $routes = $rtStmt->fetchAll();

                $rmStmt = $db->prepare("SELECT * FROM hostel_rooms WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC");
                $rmStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $rooms = $rmStmt->fetchAll();

                $alcStmt = $db->prepare("SELECT ha.*, hr.block, hr.room_no, st.first_name, st.last_name FROM hostel_allocations ha JOIN hostel_rooms hr ON ha.room_id = hr.id JOIN students st ON ha.student_id = st.id WHERE ha.tenant_id = :tid OR :is_super = 1 ORDER BY ha.id DESC LIMIT 10");
                $alcStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $allocations = $alcStmt->fetchAll();

                Response::view('dashboard/role_transport_hostel', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'routes' => $routes,
                    'rooms' => $rooms,
                    'allocations' => $allocations
                ]);
                break;

            case 'inventory_manager':
                $astStmt = $db->prepare("SELECT * FROM inventory_assets WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC");
                $astStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $assets = $astStmt->fetchAll();

                $stkStmt = $db->prepare("SELECT * FROM inventory_assets WHERE (tenant_id = :tid OR :is_super = 1) AND category = 'stock' ORDER BY id DESC");
                $stkStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $stockItems = $stkStmt->fetchAll();

                Response::view('dashboard/role_inventory', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'assets' => $assets,
                    'stockItems' => $stockItems
                ]);
                break;

            case 'receptionist':
                $enqStmt = $db->prepare("SELECT * FROM enquiries WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 10");
                $enqStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $enquiries = $enqStmt->fetchAll();

                $visStmt = $db->prepare("SELECT * FROM visitors WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 10");
                $visStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $visitors = $visStmt->fetchAll();

                Response::view('dashboard/role_receptionist', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'enquiries' => $enquiries,
                    'visitors' => $visitors
                ]);
                break;

            case 'erp_admin':
                $uStmt = $db->prepare("SELECT u.*, r.display_name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.tenant_id = :tid OR :is_super = 1 ORDER BY u.id DESC LIMIT 20");
                $uStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $usersList = $uStmt->fetchAll();

                $logStmt = $db->prepare("SELECT * FROM security_audit_logs WHERE tenant_id = :tid OR :is_super = 1 ORDER BY id DESC LIMIT 20");
                $logStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $auditLogs = $logStmt->fetchAll();

                $rolesList = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
                Response::view('dashboard/role_erp_admin', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'usersList' => $usersList,
                    'auditLogs' => $auditLogs,
                    'rolesList' => $rolesList
                ]);
                break;

            case 'principal':
                $stfStmt = $db->prepare("SELECT st.*, COALESCE(d.name, 'General') as department_name, u.username, COALESCE(r.display_name, st.designation) as role_name FROM staff st LEFT JOIN departments d ON st.department_id = d.id LEFT JOIN users u ON st.user_id = u.id LEFT JOIN roles r ON u.role_id = r.id WHERE st.tenant_id = :tid OR :is_super = 1 ORDER BY st.id DESC");
                $stfStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $staffList = $stfStmt->fetchAll();

                $tskStmt = $db->prepare("SELECT t.*, u1.username as assigned_by_name, u2.username as assigned_to_name FROM staff_tasks t JOIN users u1 ON t.assigned_by_user_id = u1.id JOIN users u2 ON t.assigned_to_user_id = u2.id WHERE t.tenant_id = :tid OR :is_super = 1 ORDER BY t.id DESC");
                $tskStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $staffTasks = $tskStmt->fetchAll();

                $pendingLeavesStmt = $db->prepare("SELECT * FROM leave_requests WHERE (tenant_id = :tid OR :is_super = 1) AND status IN ('submitted', 'under_hr_review', 'hr_recommended', 'pending_principal_approval') ORDER BY id DESC");
                $pendingLeavesStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $pendingLeaves = $pendingLeavesStmt->fetchAll();

                $allStaffStmt = $db->prepare("SELECT u.id, u.username, r.display_name as role_display FROM users u JOIN roles r ON u.role_id = r.id WHERE (u.tenant_id = :tid OR :is_super = 1) AND r.name NOT IN ('student', 'parent') ORDER BY u.id ASC");
                $allStaffStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $allStaffUsers = $allStaffStmt->fetchAll();

                $resStmt = $db->prepare("SELECT er.*, s.first_name, s.last_name, e.name as exam_name FROM exam_results er JOIN students s ON er.student_id = s.id JOIN exams e ON er.exam_id = e.id WHERE s.tenant_id = :tid OR :is_super = 1 LIMIT 10");
                $resStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                $results = $resStmt->fetchAll();

                Response::view('dashboard/role_principal', [
                    'user' => $user,
                    'metrics' => $metrics,
                    'schedule' => $schedule,
                    'staffList' => $staffList,
                    'staffTasks' => $staffTasks,
                    'pendingLeaves' => $pendingLeaves,
                    'allStaffUsers' => $allStaffUsers,
                    'results' => $results
                ]);
                break;

            case 'hr_executive':
            case 'hr':

                if ($selectedDeptId) {
                    $staffStmt = $db->prepare("SELECT st.*, COALESCE(d.name, 'General') as department_name FROM staff st LEFT JOIN departments d ON st.department_id = d.id WHERE st.department_id = :did AND (st.tenant_id = :tid OR :is_super = 1) ORDER BY st.id DESC");
                    $staffStmt->execute([':did' => $selectedDeptId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                    $staffList = $staffStmt->fetchAll();
                } else {
                    $staffStmt = $db->prepare("SELECT st.*, COALESCE(d.name, 'General') as department_name FROM staff st LEFT JOIN departments d ON st.department_id = d.id WHERE st.tenant_id = :tid OR :is_super = 1 ORDER BY st.id DESC");
                    $staffStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
                    $staffList = $staffStmt->fetchAll();
                }

                if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
                    $leaveStmt = $db->prepare("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.applicant_user_id = u.id WHERE lr.tenant_id = :tid OR u.tenant_id = :tid2 ORDER BY lr.id DESC");
                    $leaveStmt->execute([':tid' => $tenantId, ':tid2' => $tenantId]);
                    $leaves = $leaveStmt->fetchAll();
                    if (empty($leaves)) {
                        $leaves = $db->query("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.applicant_user_id = u.id ORDER BY lr.id DESC")->fetchAll();
                    }
                } else {
                    $leaves = $db->query("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.applicant_user_id = u.id ORDER BY lr.id DESC")->fetchAll();
                }

                Response::view('dashboard/role_hr', ['user' => $user, 'metrics' => $metrics, 'schedule' => $schedule, 'staffList' => $staffList, 'leaves' => $leaves]);
                break;

            default:
                Response::view('dashboard/launcher', ['user' => $user, 'metrics' => $metrics, 'schedule' => $schedule]);
                break;
        }
    }


    public function toggleTenantStatus(): void
    {
        if (!Auth::check() || Auth::user()['role_name'] !== 'super_admin') {
            Response::redirect('index.php?page=dashboard');
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $status = Validator::sanitize($_POST['status'] ?? 'active');

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE tenants SET status = :st WHERE id = :tid");
        $stmt->execute([':st' => $status, ':tid' => $tenantId]);

        Response::redirect('index.php?page=dashboard');
    }

    public function deleteTenant(): void
    {
        if (!Auth::check() || Auth::user()['role_name'] !== 'super_admin') {
            Response::redirect('index.php?page=dashboard');
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        if ($tenantId > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM tenants WHERE id = :tid");
            $stmt->execute([':tid' => $tenantId]);
        }

        Response::redirect('index.php?page=dashboard');
    }
}
