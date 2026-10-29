<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Permission;
use App\Core\CSRF;
use PDO;

class TeacherController
{
    /**
     * Resolve the staff record for the authenticated user.
     */
    private function resolveStaffRecord(array $user, PDO $db): ?array
    {
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');

        // First find by user_id
        $stmt = $db->prepare("SELECT * FROM staff WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $user['id']]);
        $staff = $stmt->fetch();

        if (!$staff) {
            // Find by email or emp_id
            $stmt = $db->prepare("SELECT * FROM staff WHERE (email = :em OR emp_id = :emp) AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
            $stmt->execute([
                ':em' => $user['email'] ?? '',
                ':emp' => $user['username'] ?? '',
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0)
            ]);
            $staff = $stmt->fetch();

            if ($staff && empty($staff['user_id'])) {
                // Link staff record to user account
                $db->prepare("UPDATE staff SET user_id = :uid WHERE id = :sid")->execute([':uid' => $user['id'], ':sid' => $staff['id']]);
            }
        }

        // If still not found and role is teacher, ensure staff profile exists
        if (!$staff && $user['role_name'] === 'teacher') {
            $defaultDeptId = (int)($db->query("SELECT id FROM departments WHERE tenant_id = " . (int)$tenantId . " ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1);
            $genEmpId = 'EMP-' . str_pad((string)$user['id'], 3, '0', STR_PAD_LEFT);
            $db->prepare("
                INSERT INTO staff (tenant_id, user_id, emp_id, first_name, last_name, designation, department_id, email, phone, status, joining_date)
                VALUES (:tid, :uid, :emp, :fn, 'Faculty', 'Assistant Professor', :did, :em, '9876543210', 'active', CURDATE())
            ")->execute([
                ':tid' => $tenantId,
                ':uid' => $user['id'],
                ':emp' => $genEmpId,
                ':fn' => ucfirst($user['username']),
                ':did' => $defaultDeptId,
                ':em' => $user['email'] ?? ($user['username'] . '@campus360.edu')
            ]);
            $newStaffId = (int)$db->lastInsertId();
            $staff = $db->query("SELECT * FROM staff WHERE id = {$newStaffId}")->fetch();
        }

        return $staff ?: null;
    }

    /**
     * Teacher Dashboard / Intro Page
     */
    public function showDashboard(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');

        $staff = $this->resolveStaffRecord($user, $db);
        $staffId = $staff['id'] ?? 1;

        // Current academic context
        $todayDate = date('Y-m-d');
        $todayDay = date('l'); // Monday, Tuesday, ...
        $currentTime = date('H:i:s');

        // Department Info
        $departmentName = 'General Department';
        if (!empty($staff['department_id'])) {
            $dStmt = $db->prepare("SELECT name, code FROM departments WHERE id = :id");
            $dStmt->execute([':id' => $staff['department_id']]);
            $deptRow = $dStmt->fetch();
            if ($deptRow) {
                $departmentName = $deptRow['name'] . ' (' . ($deptRow['code'] ?? 'DEPT') . ')';
            }
        }

        // Assigned Subjects
        $subStmt = $db->prepare("
            SELECT DISTINCT 
                COALESCE(s.id, tt.subject_id, 0) as id,
                COALESCE(s.name, tt.subject_name, 'General Subject') as name,
                COALESCE(s.code, 'SUB') as code,
                COALESCE(s.type, 'theory') as type,
                d.name as department_name,
                c.name as course_name
            FROM time_tables tt
            LEFT JOIN subjects s ON tt.subject_id = s.id
            LEFT JOIN departments d ON tt.department_id = d.id
            LEFT JOIN courses c ON tt.course_id = c.id
            WHERE (tt.tenant_id = :tid OR :is_super = 1)
              AND tt.staff_id = :sid
              AND (tt.status = 'active' OR tt.status IS NULL)
        ");
        $subStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':sid' => $staffId]);
        $assignedSubjects = $subStmt->fetchAll();

        // Today's Scheduled Lectures for this teacher
        $schedStmt = $db->prepare("
            SELECT tt.*, 
                   d.name as department_name, d.code as department_code,
                   c.name as course_name, c.code as course_code,
                   sem.name as semester_name,
                   sec.name as section_name,
                   sub.name as resolved_subject_name, sub.code as subject_code, sub.type as subject_type,
                   cr.room_number as classroom_number
            FROM time_tables tt
            LEFT JOIN departments d ON tt.department_id = d.id
            LEFT JOIN courses c ON tt.course_id = c.id
            LEFT JOIN semesters sem ON tt.semester_id = sem.id
            LEFT JOIN sections sec ON tt.section_id = sec.id
            LEFT JOIN subjects sub ON tt.subject_id = sub.id
            LEFT JOIN classrooms cr ON tt.classroom_id = cr.id
            WHERE (tt.tenant_id = :tid OR :is_super = 1)
              AND tt.staff_id = :sid
              AND tt.day_of_week = :day
              AND (tt.status = 'active' OR tt.status IS NULL)
            ORDER BY tt.start_time ASC
        ");
        $schedStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':sid' => $staffId,
            ':day' => $todayDay
        ]);
        $todayLectures = $schedStmt->fetchAll();

        // Enrich today's lectures with attendance & lesson status
        $completedLecturesCount = 0;
        $upcomingLecturesCount = 0;
        $pendingAttendanceTodayCount = 0;

        foreach ($todayLectures as &$lec) {
            // Check attendance session
            $attStmt = $db->prepare("
                SELECT id, status, total_students, present_count, absent_count 
                FROM attendance_sessions 
                WHERE time_table_id = :ttid AND lecture_date = :ld AND (tenant_id = :tid OR :is_super = 1)
                LIMIT 1
            ");
            $attStmt->execute([':ttid' => $lec['id'], ':ld' => $todayDate, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $attSession = $attStmt->fetch();
            $lec['attendance_session'] = $attSession;

            // Check lesson record
            $lesStmt = $db->prepare("
                SELECT id, topic_title, status 
                FROM lesson_records 
                WHERE time_table_id = :ttid AND lecture_date = :ld AND (tenant_id = :tid OR :is_super = 1)
                LIMIT 1
            ");
            $lesStmt->execute([':ttid' => $lec['id'], ':ld' => $todayDate, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $lecRecord = $lesStmt->fetch();
            $lec['lesson_record'] = $lecRecord;

            // Status determination
            $startTime = $lec['start_time'] ?? '00:00:00';
            $endTime = $lec['end_time'] ?? '23:59:59';

            if (!empty($attSession) && $attSession['status'] === 'completed') {
                $lec['computed_status'] = 'completed';
                $lec['status_label'] = 'Attendance Marked';
                $lec['status_badge'] = 'success';
                $completedLecturesCount++;
            } elseif ($currentTime < $startTime) {
                $lec['computed_status'] = 'upcoming';
                $lec['status_label'] = 'Upcoming';
                $lec['status_badge'] = 'info';
                $upcomingLecturesCount++;
            } else {
                $lec['computed_status'] = 'pending_attendance';
                $lec['status_label'] = 'Pending Attendance';
                $lec['status_badge'] = 'warning';
                $pendingAttendanceTodayCount++;
            }
        }
        unset($lec);

        // All Pending Attendance Sessions across past 14 days
        $allPendingAttStmt = $db->prepare("
            SELECT COUNT(*)
            FROM time_tables tt
            LEFT JOIN attendance_sessions att ON (tt.id = att.time_table_id AND att.lecture_date = :td)
            WHERE (tt.tenant_id = :tid OR :is_super = 1)
              AND tt.staff_id = :sid
              AND tt.day_of_week = :day
              AND (tt.status = 'active' OR tt.status IS NULL)
              AND att.id IS NULL
        ");
        $allPendingAttStmt->execute([
            ':td' => $todayDate,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':sid' => $staffId,
            ':day' => $todayDay
        ]);
        $totalPendingAttendance = (int)($allPendingAttStmt->fetchColumn() ?: 0);

        // Recently Assigned Homework
        $hwStmt = $db->prepare("
            SELECT hw.*, s.name as subject_name, s.code as subject_code, sec.name as section_name
            FROM homework_assignments hw
            LEFT JOIN subjects s ON hw.subject_id = s.id
            LEFT JOIN sections sec ON hw.section_id = sec.id
            WHERE (hw.tenant_id = :tid OR :is_super = 1)
              AND hw.staff_id = :sid
            ORDER BY hw.id DESC
            LIMIT 5
        ");
        $hwStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':sid' => $staffId]);
        $recentHomework = $hwStmt->fetchAll();

        // Active Homework & Upcoming Deadlines count
        $activeHwStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM homework_assignments 
            WHERE (tenant_id = :tid OR :is_super = 1)
              AND staff_id = :sid
              AND status = 'active'
              AND due_date >= CURDATE()
        ");
        $activeHwStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':sid' => $staffId]);
        $activeHomeworkCount = (int)($activeHwStmt->fetchColumn() ?: 0);

        // Last Lesson Taught by this teacher
        $lastLessonStmt = $db->prepare("
            SELECT lr.*, sub.name as subject_name, sub.code as subject_code, sec.name as section_name
            FROM lesson_records lr
            LEFT JOIN subjects sub ON lr.subject_id = sub.id
            LEFT JOIN sections sec ON lr.section_id = sec.id
            WHERE (lr.tenant_id = :tid OR :is_super = 1)
              AND lr.staff_id = :sid
            ORDER BY lr.lecture_date DESC, lr.id DESC
            LIMIT 1
        ");
        $lastLessonStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':sid' => $staffId]);
        $lastLessonTaught = $lastLessonStmt->fetch();

        // Filter support (e.g. clicking 'Pending Attendance' filters today's lectures)
        $filter = Validator::sanitize($_GET['filter'] ?? 'all');
        $filteredLectures = $todayLectures;
        if ($filter === 'pending_attendance') {
            $filteredLectures = array_filter($todayLectures, fn($l) => $l['computed_status'] === 'pending_attendance');
        } elseif ($filter === 'completed') {
            $filteredLectures = array_filter($todayLectures, fn($l) => $l['computed_status'] === 'completed');
        } elseif ($filter === 'upcoming') {
            $filteredLectures = array_filter($todayLectures, fn($l) => $l['computed_status'] === 'upcoming');
        }

        // Active academic session
        $sessionStmt = $db->prepare("SELECT name FROM academic_sessions WHERE (tenant_id = :tid OR :is_super = 1) AND is_current = 1 LIMIT 1");
        $sessionStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $currentSessionName = $sessionStmt->fetchColumn() ?: '2026-2027';

        // Count of authorized students in teacher's department / assignments
        $tDeptId = (int)($staff['department_id'] ?? 0);
        $totalDeptStudents = 0;
        if ($tDeptId > 0) {
            $stCntStmt = $db->prepare("
                SELECT COUNT(DISTINCT s.id) 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE (s.tenant_id = :tid OR :is_super = 1) 
                  AND (s.department_id = :did OR c.department_id = :did2)
                  AND (s.status = 'active' OR s.status IS NULL)
            ");
            $stCntStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0), ':did' => $tDeptId, ':did2' => $tDeptId]);
            $totalDeptStudents = (int)$stCntStmt->fetchColumn();
        }

        Response::view('dashboard/role_teacher', [
            'user' => $user,
            'role' => $role,
            'staff' => $staff,
            'departmentName' => $departmentName,
            'todayDate' => $todayDate,
            'todayDay' => $todayDay,
            'currentSessionName' => $currentSessionName,
            'assignedSubjects' => $assignedSubjects,
            'todayLectures' => $filteredLectures,
            'allTodayLectures' => $todayLectures,
            'stats' => [
                'today_total' => count($todayLectures),
                'completed' => $completedLecturesCount,
                'upcoming' => $upcomingLecturesCount,
                'pending_attendance' => $totalPendingAttendance,
                'active_homework' => $activeHomeworkCount,
                'assigned_subjects_count' => count($assignedSubjects),
                'total_dept_students' => $totalDeptStudents,
            ],
            'recentHomework' => $recentHomework,
            'lastLessonTaught' => $lastLessonTaught,
            'currentFilter' => $filter,
            'totalDeptStudents' => $totalDeptStudents,
        ]);
    }

    /**
     * Weekly Timetable (matching handwritten diagram)
     */
    public function showWeeklyTimetable(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('time_table');

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isHod = ($role === 'hod');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal', 'hod', 'administrator', 'erp_admin'], true);
        $canManageTimetable = $isAdminRole || in_array($role, ['teacher', 'faculty'], true) || !empty($user['can_manage_timetable']) || !empty($_SESSION['teacher_can_manage_timetable']);

        $staff = $this->resolveStaffRecord($user, $db);
        $myStaffId = $staff['id'] ?? null;
        $hodDeptId = $staff['department_id'] ?? Session::get('selected_dept_id') ?? 116;

        // View Mode: 'teacher' or 'class' (Student always locked to 'class')
        $viewMode = isset($_GET['view_mode']) ? Validator::sanitize($_GET['view_mode']) : ($role === 'student' ? 'class' : 'teacher');
        if ($role === 'student') {
            $viewMode = 'class';
        }

        // Student Data Scoping
        $studentScope = null;
        if ($role === 'student') {
            $stStmt = $db->prepare("
                SELECT s.*, 
                       COALESCE(s.course_id, b.course_id) as course_id, 
                       COALESCE(s.department_id, c.department_id) as department_id,
                       d.name as department_name, d.code as department_code,
                       c.name as course_name, c.code as course_code,
                       cl.name as class_name, cl.code as class_code,
                       CASE 
                           WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                           WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                           WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                           ELSE 'FY'
                       END as academic_year_code
                FROM students s 
                LEFT JOIN batches b ON s.batch_id = b.id 
                LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id 
                LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id 
                LEFT JOIN classes cl ON s.class_id = cl.id
                WHERE s.user_id = :uid LIMIT 1
            ");
            $stStmt->execute([':uid' => $user['id']]);
            $studentScope = $stStmt->fetch();
        }

        // Selected week calculation
        $requestedDate = isset($_GET['date']) ? Validator::sanitize($_GET['date']) : date('Y-m-d');
        $timeObj = strtotime($requestedDate);
        if (!$timeObj) {
            $timeObj = time();
        }
        $dayOfWeekNum = (int)date('N', $timeObj); // 1 = Monday, 7 = Sunday
        $mondayTimestamp = strtotime("-" . ($dayOfWeekNum - 1) . " days", $timeObj);
        $weekStartDate = date('Y-m-d', $mondayTimestamp);
        $weekEndDate = date('Y-m-d', strtotime('+5 days', $mondayTimestamp)); // Saturday
        $prevWeekDate = date('Y-m-d', strtotime('-7 days', $mondayTimestamp));
        $nextWeekDate = date('Y-m-d', strtotime('+7 days', $mondayTimestamp));

        // Weekdays mapping (Monday to Saturday)
        $weekdays = [
            'Monday' => date('Y-m-d', $mondayTimestamp),
            'Tuesday' => date('Y-m-d', strtotime('+1 day', $mondayTimestamp)),
            'Wednesday' => date('Y-m-d', strtotime('+2 days', $mondayTimestamp)),
            'Thursday' => date('Y-m-d', strtotime('+3 days', $mondayTimestamp)),
            'Friday' => date('Y-m-d', strtotime('+4 days', $mondayTimestamp)),
            'Saturday' => date('Y-m-d', strtotime('+5 days', $mondayTimestamp)),
        ];

        // Fetch Courses List for Tenant
        $coursesList = $db->query("
            SELECT id, name, code, department_id 
            FROM courses 
            WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) 
              AND (status = 'active' OR status IS NULL) 
            ORDER BY name ASC
        ")->fetchAll();

        // Course filter resolution (e.g. 'bsc_cs', 'bsc_it', 'data_science', 'bca', 'bcom', 'bms', or ID)
        $selectedCourseKey = isset($_GET['course']) ? Validator::sanitize($_GET['course']) : (isset($_GET['course_id']) ? Validator::sanitize($_GET['course_id']) : 'all');
        $selectedCourseId = null;

        if ($role === 'student' && $studentScope) {
            $selectedCourseId = (int)($studentScope['course_id'] ?? 0);
            $selectedCourseKey = (string)$selectedCourseId;
        } elseif ($selectedCourseKey !== 'all') {
            if (is_numeric($selectedCourseKey)) {
                $selectedCourseId = (int)$selectedCourseKey;
            } else {
                $aliasMap = [
                    'bsc_cs' => ['BSCCS_24', 'BSC-CS', '%Computer Science%'],
                    'bsc_it' => ['BSCIT_24', 'BSC-IT', '%Information Technology%', '%B.Sc. IT%'],
                    'data_science' => ['BSCDS_24', '%Data Science%'],
                    'bca' => ['BCA', 'BCA_AUTO_681', '%BCA%', '%Computer Applications%'],
                    'bcom' => ['BCOM', 'BCOM_24', '%B.Com%', '%Commerce%'],
                    'bms' => ['BMS', 'BMS_24', '%BMS%', '%Management Studies%'],
                ];
                if (isset($aliasMap[$selectedCourseKey])) {
                    $patterns = $aliasMap[$selectedCourseKey];
                    foreach ($coursesList as $c) {
                        foreach ($patterns as $p) {
                            if (str_starts_with($p, '%') && str_ends_with($p, '%')) {
                                $sub = trim($p, '%');
                                if (stripos($c['name'], $sub) !== false) {
                                    $selectedCourseId = (int)$c['id'];
                                    break 2;
                                }
                            } elseif (strcasecmp($c['code'], $p) === 0) {
                                $selectedCourseId = (int)$c['id'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Year filter resolution (FY = 1, SY = 2, TY = 3)
        $selectedYear = isset($_GET['year']) ? strtoupper(Validator::sanitize($_GET['year'])) : (isset($_GET['standard_id']) ? (int)$_GET['standard_id'] : 'ALL');
        $selectedStandardId = null;

        if ($role === 'student' && $studentScope) {
            $selectedYear = $studentScope['academic_year_code'] ?? 'FY';
            $selectedStandardId = ($selectedYear === 'SY' ? 2 : ($selectedYear === 'TY' ? 3 : 1));
        } elseif ($selectedYear !== 'ALL') {
            if ($selectedYear === 'FY' || $selectedYear === 1 || $selectedYear === '1') {
                $selectedStandardId = 1;
                $selectedYear = 'FY';
            } elseif ($selectedYear === 'SY' || $selectedYear === 2 || $selectedYear === '2') {
                $selectedStandardId = 2;
                $selectedYear = 'SY';
            } elseif ($selectedYear === 'TY' || $selectedYear === 3 || $selectedYear === '3') {
                $selectedStandardId = 3;
                $selectedYear = 'TY';
            }
        }

        // Teacher filter resolution
        $selectedTeacherId = null;
        if ($role === 'student') {
            $selectedTeacherId = null;
        } elseif ($role === 'teacher' && !$isAdminRole) {
            $selectedTeacherId = $myStaffId; // Locked strictly to logged-in teacher
        } elseif (isset($_GET['teacher_id']) && $_GET['teacher_id'] !== 'all' && is_numeric($_GET['teacher_id'])) {
            $selectedTeacherId = (int)$_GET['teacher_id'];
        }

        // Additional filters
        $selectedClassId = isset($_GET['section_id']) && $_GET['section_id'] !== 'all' ? (int)$_GET['section_id'] : null;
        $selectedSubjectId = isset($_GET['subject_id']) && $_GET['subject_id'] !== 'all' ? (int)$_GET['subject_id'] : null;

        // Fetch Dropdown Data
        $teachersList = [];
        if ($isHod) {
            $tStmt = $db->prepare("SELECT id, first_name, last_name, emp_id, department_id FROM staff WHERE (department_id = :did OR department_id IS NULL) AND (tenant_id = :tid OR :is_super = 1) AND (LOWER(status) = 'active' OR status IS NULL) ORDER BY first_name ASC");
            $tStmt->execute([':did' => $hodDeptId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $teachersList = $tStmt->fetchAll();
        } else {
            $teachersList = $db->query("SELECT id, first_name, last_name, emp_id, department_id FROM staff WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) AND (LOWER(status) = 'active' OR status IS NULL) ORDER BY first_name ASC")->fetchAll();
        }

        $sectionsList = $db->query("SELECT id, name, class_id FROM sections WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) ORDER BY name ASC")->fetchAll();
        $subjectsList = $db->query("SELECT id, name, code, course_id, type FROM subjects WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) ORDER BY name ASC")->fetchAll();
        $departmentsList = $db->query("SELECT id, name, code FROM departments WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) ORDER BY name ASC")->fetchAll();
        $classroomsList = $db->query("SELECT id, room_number, building_block FROM classrooms WHERE (tenant_id = " . (int)$tenantId . " OR " . ($isSuperAdmin ? 1 : 0) . " = 1) ORDER BY room_number ASC")->fetchAll();

        // Build Timetable Query
        $sql = "
            SELECT tt.*, 
                   d.name as department_name, d.code as department_code,
                   c.name as course_name, c.code as course_code,
                   sem.name as semester_name,
                   sec.name as section_name,
                   sub.name as resolved_subject_name, sub.code as subject_code, sub.type as subject_type,
                   st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.emp_id as teacher_emp_id,
                   cr.room_number as classroom_number
            FROM time_tables tt
            LEFT JOIN departments d ON tt.department_id = d.id
            LEFT JOIN courses c ON tt.course_id = c.id
            LEFT JOIN semesters sem ON tt.semester_id = sem.id
            LEFT JOIN sections sec ON tt.section_id = sec.id
            LEFT JOIN subjects sub ON tt.subject_id = sub.id
            LEFT JOIN staff st ON tt.staff_id = st.id
            LEFT JOIN classrooms cr ON tt.classroom_id = cr.id
            WHERE (tt.tenant_id = :tid OR :is_super = 1)
              AND (tt.status = 'active' OR tt.status IS NULL)
        ";

        $params = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];

        if ($role === 'student') {
            if ($studentScope) {
                $sql .= " AND (tt.course_id = :s_course OR tt.course_id IS NULL)
                          AND (
                              tt.standard_id = :s_std
                              OR (tt.standard_id IS NULL AND (tt.section_id = :s_sec OR tt.batch_id = :s_batch OR tt.department_id = :s_dept))
                          )";
                $params[':s_course'] = $selectedCourseId;
                $params[':s_std'] = $selectedStandardId;
                $params[':s_sec'] = $studentScope['section_id'] ?? 0;
                $params[':s_batch'] = $studentScope['batch_id'] ?? 0;
                $params[':s_dept'] = $studentScope['department_id'] ?? 0;
                if (!empty($studentScope['semester_id'])) {
                    $sql .= " AND (tt.semester_id = :s_sem OR tt.semester_id IS NULL)";
                    $params[':s_sem'] = (int)$studentScope['semester_id'];
                }
            } else {
                $sql .= " AND 1=0";
            }
        } else {
            // Non-student filtering
            if ($viewMode === 'teacher' && $selectedTeacherId) {
                $sql .= " AND tt.staff_id = :stfid";
                $params[':stfid'] = $selectedTeacherId;
            }

            if ($selectedCourseId) {
                $sql .= " AND (tt.course_id = :cid OR tt.batch_id IN (SELECT id FROM batches WHERE course_id = :cid_b))";
                $params[':cid'] = $selectedCourseId;
                $params[':cid_b'] = $selectedCourseId;
            }

            if ($selectedStandardId) {
                $sql .= " AND (tt.standard_id = :stdid OR tt.section_id IN (SELECT id FROM sections WHERE standard_id = :stdid_s))";
                $params[':stdid'] = $selectedStandardId;
                $params[':stdid_s'] = $selectedStandardId;
            }

            if ($selectedClassId) {
                $sql .= " AND tt.section_id = :secid";
                $params[':secid'] = $selectedClassId;
            }

            if ($selectedSubjectId) {
                $sql .= " AND tt.subject_id = :subid";
                $params[':subid'] = $selectedSubjectId;
            }

            if ($isHod && !$selectedTeacherId && !$selectedCourseId) {
                $sql .= " AND (tt.department_id = :hod_dept OR st.department_id = :hod_dept2 OR c.department_id = :hod_dept3)";
                $params[':hod_dept'] = $hodDeptId;
                $params[':hod_dept2'] = $hodDeptId;
                $params[':hod_dept3'] = $hodDeptId;
            }
        }

        $sql .= " ORDER BY FIELD(tt.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), tt.start_time ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $allEntries = $stmt->fetchAll();

        // Resolve periods from database `timetable_periods`
        $periodStmt = $db->prepare("
            SELECT * FROM timetable_periods 
            WHERE (tenant_id = :tid OR :is_super = 1) 
              AND status = 'active' 
            ORDER BY start_time ASC
        ");
        $periodStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $dbPeriods = $periodStmt->fetchAll();

        // If no periods seeded yet for this tenant, seed default standard periods + any entry slots
        if (empty($dbPeriods)) {
            $initialSeeds = [
                ['period_key' => 'period_1', 'period_number' => 1, 'label' => 'Period 1', 'start_time' => '08:00:00', 'end_time' => '09:00:00'],
                ['period_key' => 'period_2', 'period_number' => 2, 'label' => 'Period 2', 'start_time' => '09:00:00', 'end_time' => '10:00:00'],
                ['period_key' => 'period_3', 'period_number' => 3, 'label' => 'Period 3', 'start_time' => '10:00:00', 'end_time' => '11:00:00'],
                ['period_key' => 'period_4', 'period_number' => 4, 'label' => 'Period 4', 'start_time' => '11:00:00', 'end_time' => '12:00:00'],
                ['period_key' => 'period_5', 'period_number' => 5, 'label' => 'Period 5', 'start_time' => '12:00:00', 'end_time' => '13:00:00'],
            ];

            // If there are existing entries with other slots (e.g. 07:10:00 - 08:00:00), seed them too
            $existingSlots = $db->prepare("SELECT DISTINCT start_time, end_time FROM time_tables WHERE (tenant_id = :tid OR :is_super = 1) AND start_time IS NOT NULL AND end_time IS NOT NULL ORDER BY start_time ASC");
            $existingSlots->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $foundSlots = $existingSlots->fetchAll();

            foreach ($foundSlots as $fs) {
                $st = substr($fs['start_time'], 0, 8);
                $et = substr($fs['end_time'], 0, 8);
                $matched = false;
                foreach ($initialSeeds as $is) {
                    if ($st < $is['end_time'] && $et > $is['start_time']) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $cnt = count($initialSeeds) + 1;
                    $initialSeeds[] = [
                        'period_key' => 'slot_' . substr($st, 0, 2) . substr($st, 3, 2),
                        'period_number' => $cnt,
                        'label' => 'Period ' . $cnt,
                        'start_time' => $st,
                        'end_time' => $et,
                    ];
                }
            }

            $insertP = $db->prepare("
                INSERT IGNORE INTO timetable_periods (tenant_id, period_key, period_number, label, start_time, end_time, status)
                VALUES (:tid, :pkey, :pnum, :lbl, :st, :et, 'active')
            ");
            foreach ($initialSeeds as $pItem) {
                $insertP->execute([
                    ':tid' => $tenantId,
                    ':pkey' => $pItem['period_key'],
                    ':pnum' => $pItem['period_number'],
                    ':lbl' => $pItem['label'],
                    ':st' => $pItem['start_time'],
                    ':et' => $pItem['end_time']
                ]);
            }

            // Re-fetch db periods
            $periodStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $dbPeriods = $periodStmt->fetchAll();
        }

        $periods = [];
        foreach ($dbPeriods as $dbp) {
            $k = $dbp['period_key'] ?: ('slot_' . $dbp['id']);
            $periods[$k] = [
                'id' => (int)$dbp['id'],
                'period_number' => (int)$dbp['period_number'],
                'label' => $dbp['label'],
                'start_time' => substr($dbp['start_time'], 0, 8),
                'end_time' => substr($dbp['end_time'], 0, 8),
                'time_slot' => date('g:i A', strtotime($dbp['start_time'])) . ' - ' . date('g:i A', strtotime($dbp['end_time'])),
            ];
        }

        // Dynamically add any slots that are in allEntries but not yet in periods (and not deleted)
        $deletedStmt = $db->prepare("SELECT start_time, end_time, period_key FROM timetable_periods WHERE (tenant_id = :tid OR :is_super = 1) AND status = 'deleted'");
        $deletedStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $deletedRows = $deletedStmt->fetchAll();
        $isDeletedSlot = function($st, $et) use ($deletedRows) {
            foreach ($deletedRows as $dr) {
                if ($st === substr($dr['start_time'], 0, 8) && $et === substr($dr['end_time'], 0, 8)) {
                    return true;
                }
            }
            return false;
        };

        foreach ($allEntries as $entry) {
            $st = substr($entry['start_time'] ?? '09:00:00', 0, 8);
            $et = substr($entry['end_time'] ?? '10:00:00', 0, 8);
            if ($isDeletedSlot($st, $et)) {
                continue;
            }
            $overlapsWithPeriod = false;
            foreach ($periods as $p) {
                if ($st < $p['end_time'] && $et > $p['start_time']) {
                    $overlapsWithPeriod = true;
                    break;
                }
            }
            if (!$overlapsWithPeriod) {
                $slotKey = 'slot_' . substr($st, 0, 2) . substr($st, 3, 2);
                if (!isset($periods[$slotKey])) {
                    $newPeriodNum = count($periods) + 1;
                    $newLabel = 'Period ' . $newPeriodNum;
                    $periods[$slotKey] = [
                        'id' => 0,
                        'period_number' => $newPeriodNum,
                        'label' => $newLabel,
                        'start_time' => $st,
                        'end_time' => $et,
                        'time_slot' => date('g:i A', strtotime($st)) . ' - ' . date('g:i A', strtotime($et)),
                    ];
                    $ins = $db->prepare("INSERT IGNORE INTO timetable_periods (tenant_id, period_key, period_number, label, start_time, end_time, status) VALUES (:tid, :pk, :pn, :lbl, :st, :et, 'active')");
                    $ins->execute([':tid' => $tenantId, ':pk' => $slotKey, ':pn' => $newPeriodNum, ':lbl' => $newLabel, ':st' => $st, ':et' => $et]);
                }
            }
        }

        // Sort periods chronologically
        uasort($periods, function ($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });

        // Assemble Matrix: [day][periodKey] => array of cards
        $matrix = [];
        foreach ($weekdays as $day => $dateStr) {
            $matrix[$day] = [];
            foreach ($periods as $pKey => $pInfo) {
                $matrix[$day][$pKey] = [];
            }
        }

        foreach ($allEntries as $entry) {
            $day = $entry['day_of_week'];
            if (!isset($weekdays[$day])) continue;

            $dateStr = $weekdays[$day];
            $st = substr($entry['start_time'] ?? '09:00:00', 0, 8);
            $et = substr($entry['end_time'] ?? '10:00:00', 0, 8);

            // Compute Duration
            $stStamp = strtotime($st);
            $etStamp = strtotime($et);
            $durationMinutes = max(1, (int)round(($etStamp - $stStamp) / 60));
            $entry['duration_minutes'] = $durationMinutes;

            // Resolve Course Label & Academic Year Display
            $courseLabel = $entry['course_code'] ?? $entry['course_name'] ?? 'Degree';
            if (stripos($entry['course_name'] ?? '', 'Computer Science') !== false) $courseLabel = 'BSc CS';
            elseif (stripos($entry['course_name'] ?? '', 'Information Technology') !== false || stripos($entry['course_name'] ?? '', 'B.Sc. IT') !== false) $courseLabel = 'BSc IT';
            elseif (stripos($entry['course_name'] ?? '', 'Data Science') !== false) $courseLabel = 'Data Science';
            elseif (stripos($entry['course_name'] ?? '', 'Applications') !== false || stripos($entry['course_name'] ?? '', 'BCA') !== false) $courseLabel = 'BCA';
            elseif (stripos($entry['course_name'] ?? '', 'Commerce') !== false || stripos($entry['course_name'] ?? '', 'B.Com') !== false) $courseLabel = 'BCom';
            elseif (stripos($entry['course_name'] ?? '', 'Management') !== false || stripos($entry['course_name'] ?? '', 'BMS') !== false) $courseLabel = 'BMS';

            $yearDisplay = 'FY';
            if ((int)($entry['standard_id'] ?? 0) === 2 || stripos($entry['section_name'] ?? '', 'SY') !== false) $yearDisplay = 'SY';
            elseif ((int)($entry['standard_id'] ?? 0) === 3 || stripos($entry['section_name'] ?? '', 'TY') !== false) $yearDisplay = 'TY';

            $entry['course_label'] = $courseLabel;
            $entry['academic_year_display'] = $yearDisplay;
            $entry['course_cohort_display'] = $courseLabel . ' · ' . $yearDisplay;

            // Find matching period slot
            $matchedPeriodKey = null;
            foreach ($periods as $pKey => $pInfo) {
                if ($st < $pInfo['end_time'] && $et > $pInfo['start_time']) {
                    $matchedPeriodKey = $pKey;
                    break;
                }
            }
            if (!$matchedPeriodKey) {
                $matchedPeriodKey = array_key_first($periods);
            }

            // Attendance & Lesson records
            $attCheck = $db->prepare("SELECT id, status FROM attendance_sessions WHERE time_table_id = :ttid AND lecture_date = :ld LIMIT 1");
            $attCheck->execute([':ttid' => $entry['id'], ':ld' => $dateStr]);
            $attSession = $attCheck->fetch();

            $entry['lecture_date'] = $dateStr;
            $entry['has_attendance'] = !empty($attSession);
            if (!empty($attSession) && $attSession['status'] === 'completed') {
                $entry['status_badge'] = 'success';
                $entry['status_text'] = 'Attendance Marked';
            } elseif ($dateStr < date('Y-m-d') || ($dateStr === date('Y-m-d') && date('H:i:s') > $et)) {
                $entry['status_badge'] = 'warning';
                $entry['status_text'] = 'Pending Attendance';
            } else {
                $entry['status_badge'] = 'info';
                $entry['status_text'] = 'Upcoming';
            }

            $matrix[$day][$matchedPeriodKey][] = $entry;
        }

        // Active Teacher Info Display
        $viewingTeacherName = $staff ? ($staff['first_name'] . ' ' . $staff['last_name']) : $user['username'];
        if ($selectedTeacherId) {
            foreach ($teachersList as $t) {
                if ((int)$t['id'] === $selectedTeacherId) {
                    $viewingTeacherName = 'Prof. ' . $t['first_name'] . ' ' . $t['last_name'] . (!empty($t['emp_id']) ? ' (' . $t['emp_id'] . ')' : '');
                    break;
                }
            }
        } elseif ($viewMode === 'class') {
            $viewingTeacherName = 'Class-wise Schedule';
        } elseif ($isAdminRole) {
            $viewingTeacherName = 'All Faculty Schedule';
        }

        Response::view('teacher/weekly_timetable', [
            'user' => $user,
            'role' => $role,
            'isAdminRole' => $isAdminRole,
            'canManageTimetable' => $canManageTimetable,
            'staff' => $staff,
            'weekStartDate' => $weekStartDate,
            'weekEndDate' => $weekEndDate,
            'prevWeekDate' => $prevWeekDate,
            'nextWeekDate' => $nextWeekDate,
            'weekdays' => $weekdays,
            'periods' => $periods,
            'timeSlots' => $periods, // backward compatibility
            'matrix' => $matrix,
            'allEntries' => $allEntries,
            'teachersList' => $teachersList,
            'sectionsList' => $sectionsList,
            'subjectsList' => $subjectsList,
            'coursesList' => $coursesList,
            'departmentsList' => $departmentsList,
            'classroomsList' => $classroomsList,
            'selectedTeacherId' => $selectedTeacherId,
            'selectedClassId' => $selectedClassId,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedCourseKey' => $selectedCourseKey,
            'selectedCourseId' => $selectedCourseId,
            'selectedYear' => $selectedYear,
            'selectedStandardId' => $selectedStandardId,
            'viewMode' => $viewMode,
            'viewingTeacherName' => $viewingTeacherName,
            'isHod' => $isHod,
            'studentScope' => $studentScope,
        ]);
    }

    /**
     * Lecture Details & Classroom Session Hub
     */
    public function showLectureDetails(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal'], true);

        $timetableId = (int)($_GET['id'] ?? 0);
        $lectureDate = Validator::sanitize($_GET['date'] ?? date('Y-m-d'));

        if ($timetableId <= 0) {
            Session::setFlash('error', 'Invalid Lecture ID specified.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        // Fetch Timetable Entry with full relations
        $stmt = $db->prepare("
            SELECT tt.*, 
                   d.name as department_name, d.code as department_code,
                   c.name as course_name, c.code as course_code,
                   sem.name as semester_name, sem.semester_number,
                   sec.name as section_name,
                   sub.name as resolved_subject_name, sub.code as subject_code, sub.type as subject_type,
                   st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.emp_id as teacher_emp_id, st.user_id as teacher_user_id,
                   cr.room_number as classroom_number, cr.building_block
            FROM time_tables tt
            LEFT JOIN departments d ON tt.department_id = d.id
            LEFT JOIN courses c ON tt.course_id = c.id
            LEFT JOIN semesters sem ON tt.semester_id = sem.id
            LEFT JOIN sections sec ON tt.section_id = sec.id
            LEFT JOIN subjects sub ON tt.subject_id = sub.id
            LEFT JOIN staff st ON tt.staff_id = st.id
            LEFT JOIN classrooms cr ON tt.classroom_id = cr.id
            WHERE tt.id = :id AND (tt.tenant_id = :tid OR :is_super = 1)
            LIMIT 1
        ");
        $stmt->execute([':id' => $timetableId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $lecture = $stmt->fetch();

        if (!$lecture) {
            Session::setFlash('error', 'The requested lecture timetable entry was not found or does not belong to your institution.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        // Security / Authorization Verification
        $staff = $this->resolveStaffRecord($user, $db);
        $myStaffId = $staff['id'] ?? 0;

        if (!$isAdminRole && (int)$lecture['staff_id'] !== (int)$myStaffId) {
            Session::setFlash('error', 'Access Denied: You are not authorized to access or modify another faculty member\'s classroom session.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        // Active Student Enrollment Roster for this class/section
        // Query active students belonging to this section or course/department
        $studentSql = "
            SELECT DISTINCT s.id, s.unique_id, s.admission_no, s.roll_no, 
                   s.first_name, s.middle_name, s.last_name, s.gender, s.email, s.phone, s.photo
            FROM students s
            LEFT JOIN student_enrollments se ON (s.id = se.student_id AND se.status = 'active')
            WHERE (s.tenant_id = :tid OR :is_super = 1)
              AND (LOWER(s.status) = 'active' OR s.status IS NULL)
        ";
        $studentParams = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];

        if (!empty($lecture['section_id'])) {
            $studentSql .= " AND (s.section_id = :secid OR se.section_id = :secid2 OR (s.section_id IS NULL AND (s.course_id = :cid_fallback OR :cid_fb2 = 0)))";
            $studentParams[':secid'] = $lecture['section_id'];
            $studentParams[':secid2'] = $lecture['section_id'];
            $studentParams[':cid_fallback'] = (int)($lecture['course_id'] ?? 0);
            $studentParams[':cid_fb2'] = (int)($lecture['course_id'] ?? 0);
        } elseif (!empty($lecture['course_id'])) {
            $studentSql .= " AND (s.course_id = :cid OR se.course_id = :cid2)";
            $studentParams[':cid'] = $lecture['course_id'];
            $studentParams[':cid2'] = $lecture['course_id'];
        }

        $studentSql .= " ORDER BY CAST(s.roll_no AS UNSIGNED) ASC, s.roll_no ASC, s.first_name ASC";
        $stStmt = $db->prepare($studentSql);
        $stStmt->execute($studentParams);
        $students = $stStmt->fetchAll();

        // Fetch Existing Attendance Session & Records for this lecture and date
        $attSessionStmt = $db->prepare("
            SELECT * FROM attendance_sessions 
            WHERE time_table_id = :ttid AND lecture_date = :ld AND (tenant_id = :tid OR :is_super = 1)
            LIMIT 1
        ");
        $attSessionStmt->execute([':ttid' => $timetableId, ':ld' => $lectureDate, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $attendanceSession = $attSessionStmt->fetch();

        $savedAttendanceMap = [];
        if ($attendanceSession) {
            $recStmt = $db->prepare("
                SELECT student_id, status, remarks 
                FROM attendance_records 
                WHERE session_id = :sid AND (tenant_id = :tid OR :is_super = 1)
            ");
            $recStmt->execute([':sid' => $attendanceSession['id'], ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $records = $recStmt->fetchAll();
            foreach ($records as $r) {
                $savedAttendanceMap[$r['student_id']] = [
                    'status' => $r['status'],
                    'remarks' => $r['remarks'] ?? ''
                ];
            }
        }

        // Attendance stats
        $totalStudents = count($students);
        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $excusedCount = 0;

        foreach ($savedAttendanceMap as $sData) {
            if ($sData['status'] === 'present') $presentCount++;
            elseif ($sData['status'] === 'absent') $absentCount++;
            elseif ($sData['status'] === 'late') $lateCount++;
            elseif ($sData['status'] === 'excused') $excusedCount++;
        }

        $completionPercentage = ($totalStudents > 0 && !empty($savedAttendanceMap)) 
            ? round(($presentCount / $totalStudents) * 100, 1) 
            : 0;

        // Fetch Existing Lesson Record for this lecture and date
        $lesStmt = $db->prepare("
            SELECT * FROM lesson_records 
            WHERE time_table_id = :ttid AND lecture_date = :ld AND (tenant_id = :tid OR :is_super = 1)
            LIMIT 1
        ");
        $lesStmt->execute([':ttid' => $timetableId, ':ld' => $lectureDate, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $currentLesson = $lesStmt->fetch();

        // Fetch Last Lesson Taught for this Subject & Class prior to this date
        $secid = (int)($lecture['section_id'] ?? 0);
        $subid = (int)($lecture['subject_id'] ?? 0);

        $lastLessonSql = "
            SELECT * FROM lesson_records 
            WHERE (tenant_id = :tid OR :is_super = 1)
              AND lecture_date < :ld
        ";
        $lastLessonParams = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':ld' => $lectureDate
        ];
        if ($subid > 0) {
            $lastLessonSql .= " AND subject_id = :subid";
            $lastLessonParams[':subid'] = $subid;
        }
        if ($secid > 0) {
            $lastLessonSql .= " AND section_id = :secid";
            $lastLessonParams[':secid'] = $secid;
        }
        $lastLessonSql .= " ORDER BY lecture_date DESC, id DESC LIMIT 1";
        $lastLessonStmt = $db->prepare($lastLessonSql);
        $lastLessonStmt->execute($lastLessonParams);
        $lastLessonTaught = $lastLessonStmt->fetch();

        // Fetch Previous Lesson History for this subject & class
        $histSql = "
            SELECT lr.*, u.username as recorded_by_username
            FROM lesson_records lr
            LEFT JOIN users u ON lr.created_by = u.id
            WHERE (lr.tenant_id = :tid OR :is_super = 1)
        ";
        $histParams = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];
        if ($subid > 0) {
            $histSql .= " AND lr.subject_id = :subid";
            $histParams[':subid'] = $subid;
        }
        if ($secid > 0) {
            $histSql .= " AND lr.section_id = :secid";
            $histParams[':secid'] = $secid;
        }
        $histSql .= " ORDER BY lr.lecture_date DESC, lr.id DESC LIMIT 10";
        $historyLessonStmt = $db->prepare($histSql);
        $historyLessonStmt->execute($histParams);
        $previousLessons = $historyLessonStmt->fetchAll();

        // Fetch Homework Assignments for this lecture / class / subject
        $hwSql = "
            SELECT hw.*, u.username as creator_username
            FROM homework_assignments hw
            LEFT JOIN users u ON hw.created_by = u.id
            WHERE (hw.tenant_id = :tid OR :is_super = 1)
        ";
        $hwParams = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];
        if ($subid > 0) {
            $hwSql .= " AND hw.subject_id = :subid";
            $hwParams[':subid'] = $subid;
        }
        if ($secid > 0) {
            $hwSql .= " AND hw.section_id = :secid";
            $hwParams[':secid'] = $secid;
        }
        $hwSql .= " ORDER BY hw.id DESC LIMIT 10";
        $hwStmt = $db->prepare($hwSql);
        $hwStmt->execute($hwParams);
        $homeworkList = $hwStmt->fetchAll();

        // Fetch Classroom Remarks for this lecture and date
        $remStmt = $db->prepare("
            SELECT cr.*, s.first_name, s.last_name, s.roll_no, u.username as author_username
            FROM classroom_remarks cr
            LEFT JOIN students s ON cr.student_id = s.id
            LEFT JOIN users u ON cr.created_by = u.id
            WHERE cr.time_table_id = :ttid 
              AND cr.lecture_date = :ld 
              AND (cr.tenant_id = :tid OR :is_super = 1)
            ORDER BY cr.id DESC
        ");
        $remStmt->execute([
            ':ttid' => $timetableId,
            ':ld' => $lectureDate,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ]);
        $classroomRemarks = $remStmt->fetchAll();

        Response::view('teacher/lecture_details', [
            'user' => $user,
            'role' => $role,
            'staff' => $staff,
            'lecture' => $lecture,
            'lectureDate' => $lectureDate,
            'students' => $students,
            'attendanceSession' => $attendanceSession,
            'savedAttendanceMap' => $savedAttendanceMap,
            'attendanceStats' => [
                'total' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'excused' => $excusedCount,
                'percentage' => $completionPercentage,
            ],
            'currentLesson' => $currentLesson,
            'lastLessonTaught' => $lastLessonTaught,
            'previousLessons' => $previousLessons,
            'homeworkList' => $homeworkList,
            'classroomRemarks' => $classroomRemarks,
        ]);
    }

    /**
     * Save Student Attendance for the Lecture
     */
    public function saveAttendance(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthenticated session.'], 401);
            return;
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal'], true);

        // Verify CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validate($token)) {
            Response::json(['error' => 'Security token expired or invalid.'], 403);
            return;
        }

        $timetableId = (int)($_POST['time_table_id'] ?? 0);
        $lectureDate = Validator::sanitize($_POST['date'] ?? $_POST['lecture_date'] ?? date('Y-m-d'));
        $attendanceData = $_POST['attendance'] ?? []; // student_id => status ('present','absent','late','excused')
        $remarksData = $_POST['remarks'] ?? []; // student_id => string

        if ($timetableId <= 0) {
            Response::json(['error' => 'Invalid timetable lecture ID.'], 400);
            return;
        }

        // Fetch timetable entry to check ownership
        $ttStmt = $db->prepare("SELECT * FROM time_tables WHERE id = :id AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
        $ttStmt->execute([':id' => $timetableId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $lecture = $ttStmt->fetch();

        if (!$lecture) {
            Response::json(['error' => 'Timetable lecture entry not found.'], 404);
            return;
        }

        $staff = $this->resolveStaffRecord($user, $db);
        $myStaffId = $staff['id'] ?? 0;

        if (!$isAdminRole && (int)$lecture['staff_id'] !== (int)$myStaffId) {
            Response::json(['error' => 'Unauthorized: You are not authorized to mark attendance for this lecture.'], 403);
            return;
        }

        if (empty($attendanceData)) {
            Response::json(['error' => 'No student attendance records were marked.'], 400);
            return;
        }

        // Calculate counts
        $totalStudents = count($attendanceData);
        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $excusedCount = 0;

        $allowedStatuses = ['present', 'absent', 'late', 'excused'];
        foreach ($attendanceData as $sid => $st) {
            $stClean = strtolower(trim($st));
            if (!in_array($stClean, $allowedStatuses, true)) {
                $stClean = 'present';
            }
            if ($stClean === 'present') $presentCount++;
            elseif ($stClean === 'absent') $absentCount++;
            elseif ($stClean === 'late') $lateCount++;
            elseif ($stClean === 'excused') $excusedCount++;
        }

        try {
            $db->beginTransaction();

            // 1. Create or Update attendance_sessions
            $sessStmt = $db->prepare("
                INSERT INTO attendance_sessions 
                (tenant_id, time_table_id, staff_id, lecture_date, subject_id, section_id, total_students, present_count, absent_count, late_count, excused_count, status, created_by)
                VALUES 
                (:tid, :ttid, :sid, :ld, :subid, :secid, :total, :pres, :abs, :late, :exc, 'completed', :cb)
                ON DUPLICATE KEY UPDATE 
                    total_students = VALUES(total_students),
                    present_count = VALUES(present_count),
                    absent_count = VALUES(absent_count),
                    late_count = VALUES(late_count),
                    excused_count = VALUES(excused_count),
                    status = 'completed',
                    updated_at = CURRENT_TIMESTAMP
            ");
            $sessStmt->execute([
                ':tid' => $tenantId,
                ':ttid' => $timetableId,
                ':sid' => $lecture['staff_id'],
                ':ld' => $lectureDate,
                ':subid' => $lecture['subject_id'],
                ':secid' => $lecture['section_id'],
                ':total' => $totalStudents,
                ':pres' => $presentCount,
                ':abs' => $absentCount,
                ':late' => $lateCount,
                ':exc' => $excusedCount,
                ':cb' => $user['id']
            ]);

            // Get session ID
            $sessionCheck = $db->prepare("SELECT id FROM attendance_sessions WHERE time_table_id = :ttid AND lecture_date = :ld AND tenant_id = :tid LIMIT 1");
            $sessionCheck->execute([':ttid' => $timetableId, ':ld' => $lectureDate, ':tid' => $tenantId]);
            $sessionId = (int)$sessionCheck->fetchColumn();

            // 2. Insert or update individual student attendance records
            $recStmt = $db->prepare("
                INSERT INTO attendance_records 
                (tenant_id, session_id, time_table_id, student_id, status, remarks)
                VALUES 
                (:tid, :sessid, :ttid, :stuid, :st, :rem)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    remarks = VALUES(remarks),
                    updated_at = CURRENT_TIMESTAMP
            ");

            // Backward compatibility sync with existing student_attendance table
            $legacyStmt = $db->prepare("
                INSERT INTO student_attendance 
                (tenant_id, student_id, batch_id, date, status, marked_by)
                VALUES 
                (:tid, :stuid, :bid, :ld, :st, :uid)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    marked_by = VALUES(marked_by)
            ");

            foreach ($attendanceData as $studentId => $status) {
                $studentIdInt = (int)$studentId;
                $statusClean = strtolower(trim($status));
                if (!in_array($statusClean, $allowedStatuses, true)) {
                    $statusClean = 'present';
                }
                $remarkClean = Validator::sanitize($remarksData[$studentId] ?? '');

                $recStmt->execute([
                    ':tid' => $tenantId,
                    ':sessid' => $sessionId,
                    ':ttid' => $timetableId,
                    ':stuid' => $studentIdInt,
                    ':st' => $statusClean,
                    ':rem' => $remarkClean
                ]);

                // Map 'excused' to 'leave' in legacy table enum
                $legacyStatus = ($statusClean === 'excused') ? 'leave' : $statusClean;
                $legacyStmt->execute([
                    ':tid' => $tenantId,
                    ':stuid' => $studentIdInt,
                    ':bid' => $lecture['batch_id'] ?? 1,
                    ':ld' => $lectureDate,
                    ':st' => $legacyStatus,
                    ':uid' => $user['id']
                ]);
            }

            $db->commit();

            $pct = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;
            Response::json([
                'success' => true,
                'message' => "Attendance saved successfully: {$presentCount} Present, {$absentCount} Absent, {$lateCount} Late ({$pct}%).",
                'stats' => [
                    'total' => $totalStudents,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount,
                    'percentage' => $pct
                ]
            ]);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Save Attendance Error: " . $e->getMessage());
            Response::json(['error' => 'Database Transaction Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save Daily Lesson Record / Topic Taught
     */
    public function saveLessonRecord(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthenticated session.'], 401);
            return;
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal'], true);

        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validate($token)) {
            Response::json(['error' => 'Invalid security token.'], 403);
            return;
        }

        $timetableId = (int)($_POST['time_table_id'] ?? 0);
        $lectureDate = Validator::sanitize($_POST['date'] ?? $_POST['lecture_date'] ?? date('Y-m-d'));
        $topicTitle = Validator::sanitize($_POST['topic_title'] ?? '');
        $description = Validator::sanitize($_POST['description'] ?? '');
        $status = Validator::sanitize($_POST['status'] ?? 'completed');

        if ($timetableId <= 0 || empty($topicTitle)) {
            Response::json(['error' => 'Topic title is required.'], 400);
            return;
        }

        // Ownership validation
        $ttStmt = $db->prepare("SELECT * FROM time_tables WHERE id = :id AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
        $ttStmt->execute([':id' => $timetableId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $lecture = $ttStmt->fetch();

        if (!$lecture) {
            Response::json(['error' => 'Timetable lecture entry not found.'], 404);
            return;
        }

        $staff = $this->resolveStaffRecord($user, $db);
        $myStaffId = $staff['id'] ?? 0;

        if (!$isAdminRole && (int)$lecture['staff_id'] !== (int)$myStaffId) {
            Response::json(['error' => 'Unauthorized: You cannot modify lesson records for another faculty member.'], 403);
            return;
        }

        $allowedStatuses = ['planned', 'completed', 'partially_completed'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'completed';
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO lesson_records 
                (tenant_id, time_table_id, staff_id, subject_id, section_id, lecture_date, topic_title, description, status, created_by)
                VALUES 
                (:tid, :ttid, :sid, :subid, :secid, :ld, :topic, :desc, :st, :cb)
                ON DUPLICATE KEY UPDATE 
                    topic_title = VALUES(topic_title),
                    description = VALUES(description),
                    status = VALUES(status),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':ttid' => $timetableId,
                ':sid' => $lecture['staff_id'],
                ':subid' => $lecture['subject_id'],
                ':secid' => $lecture['section_id'],
                ':ld' => $lectureDate,
                ':topic' => $topicTitle,
                ':desc' => $description,
                ':st' => $status,
                ':cb' => $user['id']
            ]);

            Response::json([
                'success' => true,
                'message' => 'Daily lesson topic recorded successfully!',
                'lesson' => [
                    'topic_title' => $topicTitle,
                    'description' => $description,
                    'status' => $status,
                    'date' => $lectureDate
                ]
            ]);

        } catch (\Throwable $e) {
            error_log("Save Lesson Record Error: " . $e->getMessage());
            Response::json(['error' => 'Failed to save lesson record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create or Assign Homework
     */
    public function saveHomework(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
            return;
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'teacher';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal'], true);

        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validate($token)) {
            Session::setFlash('error', 'Security token invalid.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
            return;
        }

        $timetableId = (int)($_POST['time_table_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $title = Validator::sanitize($_POST['title'] ?? '');
        $description = Validator::sanitize($_POST['description'] ?? '');
        $assignedDate = Validator::sanitize($_POST['assigned_date'] ?? date('Y-m-d'));
        $dueDate = Validator::sanitize($_POST['due_date'] ?? date('Y-m-d', strtotime('+3 days')));

        if (empty($title)) {
            Session::setFlash('error', 'Homework title is required.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
            return;
        }

        if ($dueDate < $assignedDate) {
            Session::setFlash('error', 'Due date cannot precede the assigned date.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
            return;
        }

        $staff = $this->resolveStaffRecord($user, $db);
        $staffId = $staff['id'] ?? 1;

        // Secure file upload handling
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['pdf', 'docx', 'doc', 'txt', 'zip', 'png', 'jpg', 'jpeg'];
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                Session::setFlash('error', 'Invalid file type. Allowed formats: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG.');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
                return;
            }

            if ($file['size'] > 10 * 1024 * 1024) { // 10MB
                Session::setFlash('error', 'File size exceeds maximum permitted limit (10MB).');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
                return;
            }

            $uploadDir = BASE_PATH . '/public/uploads/homework/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeFilename = 'hw_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destination = $uploadDir . $safeFilename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentPath = 'uploads/homework/' . $safeFilename;
            }
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO homework_assignments 
                (tenant_id, title, description, staff_id, department_id, course_id, section_id, subject_id, time_table_id, assigned_date, due_date, attachment_path, status, created_by)
                VALUES 
                (:tid, :title, :desc, :sid, :did, :cid, :secid, :subid, :ttid, :ad, :dd, :att, 'active', :cb)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':title' => $title,
                ':desc' => $description,
                ':sid' => $staffId,
                ':did' => $departmentId > 0 ? $departmentId : null,
                ':cid' => $courseId > 0 ? $courseId : null,
                ':secid' => $sectionId > 0 ? $sectionId : null,
                ':subid' => $subjectId,
                ':ttid' => $timetableId > 0 ? $timetableId : null,
                ':ad' => $assignedDate,
                ':dd' => $dueDate,
                ':att' => $attachmentPath,
                ':cb' => $user['id']
            ]);

            Session::setFlash('success', 'Homework assignment published successfully!');
        } catch (\Throwable $e) {
            error_log("Save Homework Error: " . $e->getMessage());
            Session::setFlash('error', 'Failed to save homework assignment: ' . $e->getMessage());
        }

        Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
    }

    /**
     * Delete or Archive Homework
     */
    public function deleteHomework(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthenticated.'], 401);
            return;
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $isAdminRole = in_array($user['role_name'], ['super_admin', 'school_admin', 'principal'], true);

        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validate($token)) {
            Response::json(['error' => 'Invalid CSRF token.'], 403);
            return;
        }

        $hwId = (int)($_POST['id'] ?? $_POST['homework_id'] ?? 0);
        $staff = $this->resolveStaffRecord($user, $db);
        $staffId = $staff['id'] ?? 0;

        $checkStmt = $db->prepare("SELECT * FROM homework_assignments WHERE id = :id AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
        $checkStmt->execute([':id' => $hwId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $hw = $checkStmt->fetch();

        if (!$hw) {
            Response::json(['error' => 'Homework record not found.'], 404);
            return;
        }

        if (!$isAdminRole && (int)$hw['staff_id'] !== (int)$staffId) {
            Response::json(['error' => 'Unauthorized to delete this homework.'], 403);
            return;
        }

        $db->prepare("DELETE FROM homework_assignments WHERE id = :id")->execute([':id' => $hwId]);
        Response::json(['success' => true, 'message' => 'Homework assignment removed.']);
    }

    /**
     * Save Classroom Remark
     */
    public function saveClassroomRemark(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthenticated.'], 401);
            return;
        }

        Session::start();
        $user = Auth::user();
        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $isAdminRole = in_array($user['role_name'], ['super_admin', 'school_admin', 'principal'], true);

        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validate($token)) {
            Response::json(['error' => 'Invalid security token.'], 403);
            return;
        }

        $timetableId = (int)($_POST['time_table_id'] ?? 0);
        $lectureDate = Validator::sanitize($_POST['date'] ?? $_POST['lecture_date'] ?? date('Y-m-d'));
        $studentId = isset($_POST['student_id']) && (int)$_POST['student_id'] > 0 ? (int)$_POST['student_id'] : null;
        $remarkType = Validator::sanitize($_POST['remark_type'] ?? 'general_class');
        $remarkText = Validator::sanitize($_POST['remark_text'] ?? '');

        if ($timetableId <= 0 || empty($remarkText)) {
            Response::json(['error' => 'Remark text is required.'], 400);
            return;
        }

        $staff = $this->resolveStaffRecord($user, $db);
        $staffId = $staff['id'] ?? 1;

        if ($remarkType === 'academic') {
            $remarkType = 'academic_performance';
        }
        $allowedTypes = ['general_class', 'academic_performance', 'participation', 'behavior'];
        if (!in_array($remarkType, $allowedTypes, true)) {
            $remarkType = 'general_class';
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO classroom_remarks 
                (tenant_id, time_table_id, staff_id, student_id, lecture_date, remark_type, remark_text, created_by)
                VALUES 
                (:tid, :ttid, :sid, :stuid, :ld, :type, :text, :cb)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':ttid' => $timetableId,
                ':sid' => $staffId,
                ':stuid' => $studentId,
                ':ld' => $lectureDate,
                ':type' => $remarkType,
                ':text' => $remarkText,
                ':cb' => $user['id']
            ]);

            Response::json([
                'success' => true,
                'message' => 'Classroom remark saved successfully!',
                'remark' => [
                    'id' => (int)$db->lastInsertId(),
                    'type' => $remarkType,
                    'text' => $remarkText,
                    'author' => $user['username'],
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Throwable $e) {
            error_log("Save Remark Error: " . $e->getMessage());
            Response::json(['error' => 'Failed to save remark: ' . $e->getMessage()], 500);
        }
    }
}
