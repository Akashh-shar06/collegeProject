<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Validator;
use PDO;

class ApiController
{
    public function getModuleData(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $moduleKey = Validator::sanitize($_GET['module'] ?? ($_GET['name'] ?? ''));
        $db = Database::getInstance();

        switch ($moduleKey) {
            case 'enquiry':
                $data = $db->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 10")->fetchAll();
                Response::json(['title' => 'Enquiry Module', 'data' => $data]);
                break;

            case 'students':
            case 'student_mis':
                $user = Auth::user();
                $tenantId = $user['tenant_id'] ?? 1;
                $isSuperAdmin = ($user['role_name'] === 'super_admin');
                $params = [];
                $where = [];

                if (!$isSuperAdmin && $tenantId !== null) {
                    $where[] = "s.tenant_id = :tid";
                    $params[':tid'] = (int) $tenantId;
                }

                // Teacher Scoping: restrict to assigned department, classes, or timetable courses
                if ($user['role_name'] === 'teacher') {
                    $stfStmt = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = :uid LIMIT 1");
                    $stfStmt->execute([':uid' => $user['id']]);
                    $stf = $stfStmt->fetch();
                    $stfId = (int) ($stf['id'] ?? 0);
                    $stfDeptId = (int) ($stf['department_id'] ?? 0);

                    if ($stfId > 0) {
                        $where[] = "((s.department_id = :t_dept OR c.department_id = :t_dept2) 
                                     OR s.section_id IN (SELECT section_id FROM teacher_assignments WHERE staff_id = :t_stf1 AND section_id IS NOT NULL) 
                                     OR COALESCE(s.course_id, b.course_id) IN (SELECT course_id FROM time_tables WHERE staff_id = :t_stf2 AND course_id IS NOT NULL) 
                                     OR s.id IN (SELECT student_id FROM student_attendance WHERE marked_by = :t_stf3))";
                        $params[':t_dept'] = $stfDeptId;
                        $params[':t_dept2'] = $stfDeptId;
                        $params[':t_stf1'] = $stfId;
                        $params[':t_stf2'] = $stfId;
                        $params[':t_stf3'] = $stfId;
                    }
                } elseif ($user['role_name'] === 'student') {
                    $where[] = "s.user_id = :st_uid";
                    $params[':st_uid'] = (int) $user['id'];
                } elseif ($user['role_name'] === 'parent') {
                    $where[] = "(s.parent_user_id = :pr_uid OR s.id IN (SELECT student_id FROM parent_student_map WHERE parent_user_id = :pr_uid2))";
                    $params[':pr_uid'] = (int) $user['id'];
                    $params[':pr_uid2'] = (int) $user['id'];
                }

                // Department filter
                $reqDeptId = (int) ($_GET['department_id'] ?? 0);
                if ($reqDeptId > 0) {
                    $where[] = "(s.department_id = :req_did OR c.department_id = :req_did2)";
                    $params[':req_did'] = $reqDeptId;
                    $params[':req_did2'] = $reqDeptId;
                }

                // Search Filter
                $search = trim(Validator::sanitize($_GET['search'] ?? $_GET['q'] ?? ''));
                if (!empty($search)) {
                    $where[] = "(s.first_name LIKE :srch1 OR s.last_name LIKE :srch2 OR s.admission_no LIKE :srch3 OR s.roll_no LIKE :srch4 OR s.phone LIKE :srch5 OR s.email LIKE :srch6)";
                    $params[':srch1'] = "%{$search}%";
                    $params[':srch2'] = "%{$search}%";
                    $params[':srch3'] = "%{$search}%";
                    $params[':srch4'] = "%{$search}%";
                    $params[':srch5'] = "%{$search}%";
                    $params[':srch6'] = "%{$search}%";
                }

                // Academic Year Filter
                $yearFilter = strtoupper(trim(Validator::sanitize($_GET['academic_year'] ?? $_GET['year'] ?? '')));
                if (!empty($yearFilter) && $yearFilter !== 'ALL') {
                    if ($yearFilter === 'FY') {
                        $where[] = "(s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%')";
                    } elseif ($yearFilter === 'SY') {
                        $where[] = "(s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%')";
                    } elseif ($yearFilter === 'TY') {
                        $where[] = "(s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%')";
                    }
                }

                $whereStr = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

                $countSql = "SELECT COUNT(DISTINCT s.id) 
                             FROM students s 
                             LEFT JOIN batches b ON s.batch_id = b.id 
                             LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id 
                             LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id 
                             LEFT JOIN classes cl ON s.class_id = cl.id 
                             {$whereStr}";
                $countStmt = $db->prepare($countSql);
                $countStmt->execute($params);
                $totalRecords = (int) $countStmt->fetchColumn();

                $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 50;
                $page = isset($_GET['page_num']) ? max(1, (int) $_GET['page_num']) : 1;
                $offset = ($page - 1) * $limit;

                $sortBy = Validator::sanitize($_GET['sort_by'] ?? 'name');
                $sortOrder = strtoupper(Validator::sanitize($_GET['order'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
                $orderClause = match ($sortBy) {
                    'roll_no' => "CAST(s.roll_no AS UNSIGNED) {$sortOrder}, s.roll_no {$sortOrder}",
                    'admission_no' => "s.admission_no {$sortOrder}",
                    'id' => "s.id {$sortOrder}",
                    default => "s.first_name {$sortOrder}, s.last_name {$sortOrder}"
                };

                $sql = "SELECT s.id, s.admission_no, s.roll_no, s.first_name, s.last_name, 
                               CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name,
                               s.photo, s.gender, s.dob, s.email, s.phone,
                               COALESCE(d.name, 'General') as department_name,
                               COALESCE(c.name, 'General Degree') as course_name,
                               CASE 
                                   WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'First Year (FY)'
                                   WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'Second Year (SY)'
                                   WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'Third Year (TY)'
                                   ELSE 'First Year (FY)'
                               END as student_year,
                               CASE 
                                   WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                                   WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                                   WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                                   ELSE 'FY'
                               END as year_code,
                               COALESCE(cl.name, 'Division A') as class_name,
                               COALESCE(b.batch_name, 'Batch 2026') as batch_name,
                               s.status
                        FROM students s 
                        LEFT JOIN batches b ON s.batch_id = b.id 
                        LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id 
                        LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id 
                        LEFT JOIN classes cl ON s.class_id = cl.id 
                        {$whereStr}
                        GROUP BY s.id
                        ORDER BY {$orderClause}
                        LIMIT {$limit} OFFSET {$offset}";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                Response::json([
                    'title' => 'Student MIS',
                    'status' => 'success',
                    'data' => $data,
                    'count' => count($data),
                    'total' => $totalRecords,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => $limit > 0 ? ceil($totalRecords / $limit) : 1
                ]);
                break;

            case 'fees':
            case 'fees_collection':
                $data = $db->query("SELECT fc.*, s.first_name, s.last_name, s.admission_no FROM fee_collections fc JOIN students s ON fc.student_id = s.id ORDER BY fc.id DESC LIMIT 10")->fetchAll();
                Response::json(['title' => 'Fees Collection Module', 'data' => $data]);
                break;

            case 'staff_data':
            case 'hr_management':
                $data = $db->query("SELECT st.*, d.name as department_name FROM staff st JOIN departments d ON st.department_id = d.id ORDER BY st.id DESC LIMIT 10")->fetchAll();
                Response::json(['title' => 'Staff Data & HR Module', 'data' => $data]);
                break;

            case 'tickets':
            case 'ticket_management':
                $data = $db->query("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.id DESC LIMIT 10")->fetchAll();
                Response::json(['title' => 'Ticket Management Module', 'data' => $data]);
                break;

            case 'notice':
            case 'news_events':
                $data = $db->query("SELECT n.*, u.username FROM notices n JOIN users u ON n.posted_by = u.id ORDER BY n.id DESC LIMIT 10")->fetchAll();
                Response::json(['title' => 'News & Events Module', 'data' => $data]);
                break;

            default:
                Response::json(['title' => ucwords(str_replace('_', ' ', $moduleKey)), 'message' => 'Module engine active and synchronized with MySQL database.']);
                break;
        }
    }

    public function getStandards(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $deptId = (int) ($_GET['department_id'] ?? 0);
        $db = Database::getInstance();

        $params = [':tid' => $tenantId];
        $sql = "SELECT id, name, code, sequence FROM academic_standards WHERE tenant_id = :tid";

        if ($deptId > 0) {
            $sql .= " AND (department_id = :did OR department_id IS NULL)";
            $params[':did'] = $deptId;
        }

        // Teacher scoping check
        if ($user['role_name'] === 'teacher') {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $staffId = $stfStmt->fetchColumn();

            if ($staffId) {
                $assignCheck = $db->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE staff_id = :stfid");
                $assignCheck->execute([':stfid' => $staffId]);
                if ((int) $assignCheck->fetchColumn() > 0) {
                    $sql .= " AND id IN (SELECT standard_id FROM teacher_assignments WHERE staff_id = :stfid AND standard_id IS NOT NULL)";
                    $params[':stfid'] = $staffId;
                }
            }
        }

        $sql .= " ORDER BY sequence ASC, name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $standards = $stmt->fetchAll();

        // If no standards exist for this department, fallback to global default FY, SY, TY standards
        if (empty($standards)) {
            $standards = $db->query("SELECT id, name, code, sequence FROM academic_standards WHERE tenant_id = " . (int) $tenantId . " ORDER BY sequence ASC, name ASC")->fetchAll();
        }

        Response::json(['status' => 'success', 'data' => $standards]);
    }

    public function getClasses(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $standardId = (int) ($_GET['standard_id'] ?? 0);
        $deptId = (int) ($_GET['department_id'] ?? 0);
        $db = Database::getInstance();

        $params = [':tid' => $tenantId];
        $sql = "SELECT id, name, code FROM sections WHERE tenant_id = :tid";

        if ($standardId > 0) {
            $sql .= " AND (standard_id = :sid OR standard_id IS NULL)";
            $params[':sid'] = $standardId;
        }

        if ($deptId > 0) {
            $sql .= " AND (department_id = :did OR department_id IS NULL)";
            $params[':did'] = $deptId;
        }

        // Teacher scoping check
        if ($user['role_name'] === 'teacher') {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $staffId = $stfStmt->fetchColumn();

            if ($staffId) {
                $assignCheck = $db->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE staff_id = :stfid");
                $assignCheck->execute([':stfid' => $staffId]);
                if ((int) $assignCheck->fetchColumn() > 0) {
                    $sql .= " AND id IN (SELECT section_id FROM teacher_assignments WHERE staff_id = :stfid AND section_id IS NOT NULL)";
                    $params[':stfid'] = $staffId;
                }
            }
        }

        $sql .= " ORDER BY name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $classes = $stmt->fetchAll();

        if (empty($classes)) {
            $classes = $db->query("SELECT id, name, code FROM sections WHERE tenant_id = " . (int) $tenantId . " ORDER BY name ASC")->fetchAll();
        }

        Response::json(['status' => 'success', 'data' => $classes]);
    }

    public function getSubjects(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $deptId = (int) ($_GET['department_id'] ?? 0);
        $courseId = (int) ($_GET['course_id'] ?? 0);
        $standardId = (int) ($_GET['standard_id'] ?? 0);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $db = Database::getInstance();

        $params = [':tid' => $tenantId];
        $sql = "SELECT id, name, code FROM subjects WHERE tenant_id = :tid";

        if ($deptId > 0) {
            $sql .= " AND (department_id = :did OR department_id IS NULL)";
            $params[':did'] = $deptId;
        }

        if ($courseId > 0) {
            $sql .= " AND (course_id = :cid OR course_id IS NULL)";
            $params[':cid'] = $courseId;
        }

        if ($standardId > 0) {
            $sql .= " AND (standard_id = :sid OR standard_id IS NULL)";
            $params[':sid'] = $standardId;
        }

        if ($sectionId > 0) {
            $sql .= " AND (section_id = :secid OR section_id IS NULL)";
            $params[':secid'] = $sectionId;
        }

        // Teacher / Staff scoping check (either requested via staff_id/teacher_id or automatically for teacher role)
        $filterStaffId = (int) ($_GET['staff_id'] ?? ($_GET['teacher_id'] ?? 0));
        if ($filterStaffId <= 0 && $user['role_name'] === 'teacher') {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $filterStaffId = (int) ($stfStmt->fetchColumn() ?: 0);
        }

        if ($filterStaffId > 0) {
            $stfSubCheck = $db->prepare("
                SELECT DISTINCT subject_id FROM time_tables WHERE staff_id = :sid AND subject_id IS NOT NULL
                UNION
                SELECT DISTINCT subject_id FROM teacher_assignments WHERE staff_id = :sid2 AND subject_id IS NOT NULL
            ");
            $stfSubCheck->execute([':sid' => $filterStaffId, ':sid2' => $filterStaffId]);
            $subIds = $stfSubCheck->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subIds)) {
                $inPlaceholders = implode(',', array_map('intval', array_filter($subIds)));
                if (!empty($inPlaceholders)) {
                    $sql .= " AND id IN ($inPlaceholders)";
                }
            }
        }

        $sql .= " ORDER BY name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll();

        if (empty($subjects)) {
            $subjects = $db->query("SELECT id, name, code FROM subjects WHERE tenant_id = " . (int) $tenantId . " ORDER BY name ASC")->fetchAll();
        }

        Response::json(['status' => 'success', 'data' => $subjects]);
    }

    public function getTeachers(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $deptId = (int) ($_GET['department_id'] ?? 0);
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $db = Database::getInstance();

        $params = [':tid' => $tenantId];
        $sql = "SELECT id, first_name, last_name, emp_id FROM staff WHERE tenant_id = :tid";

        if ($deptId > 0) {
            $sql .= " AND (department_id = :did OR department_id IS NULL)";
            $params[':did'] = $deptId;
        }

        if ($subjectId > 0) {
            $sql .= " AND (id IN (SELECT staff_id FROM teacher_assignments WHERE subject_id = :subid) OR department_id = :did_sub)";
            $params[':subid'] = $subjectId;
            $params[':did_sub'] = $deptId > 0 ? $deptId : 0;
        }

        $sql .= " ORDER BY first_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $teachers = $stmt->fetchAll();

        Response::json(['status' => 'success', 'data' => $teachers]);
    }

    public function submitEnquiry(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $name = Validator::sanitize($_POST['student_name'] ?? '');
        $email = Validator::sanitize($_POST['email'] ?? '');
        $phone = Validator::sanitize($_POST['phone'] ?? '');
        $course = Validator::sanitize($_POST['course_interested'] ?? '');

        if (empty($name) || empty($phone) || empty($course)) {
            Response::json(['error' => 'Please fill in required fields.'], 400);
        }

        $tenantId = Auth::user()['tenant_id'] ?? 1;

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO enquiries (tenant_id, student_name, email, phone, course_interested, status) VALUES (:tid, :name, :email, :phone, :course, 'pending')");
        $stmt->execute([
            ':tid' => $tenantId,
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':course' => $course
        ]);

        Response::json(['success' => true, 'message' => 'Enquiry logged successfully!']);
    }

    public function getDashboardUpdates(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $db = Database::getInstance();

        try {
            $attSum = (float) $db->query("SELECT SUM(id + IFNULL(ASCII(status), 0) + IFNULL(marked_by, 0)) FROM student_attendance")->fetchColumn();
            $examSum = (float) $db->query("SELECT SUM(id + IFNULL(marks_obtained, 0) + IFNULL(ASCII(grade), 0)) FROM exam_results")->fetchColumn();
            $ttSum = (float) $db->query("SELECT SUM(id + CHAR_LENGTH(subject_name) + CHAR_LENGTH(time_slot)) FROM time_tables")->fetchColumn();
            $noticeSum = (float) $db->query("SELECT SUM(id + CHAR_LENGTH(title)) FROM notices")->fetchColumn();
            $feeSum = (float) $db->query("SELECT SUM(id + IFNULL(amount_paid, 0) + IFNULL(ASCII(status), 0)) FROM fee_collections")->fetchColumn()
                + (float) $db->query("SELECT SUM(id + IFNULL(amount, 0) + IFNULL(ASCII(status), 0)) FROM fee_installments")->fetchColumn();

            $hash = md5("{$attSum}_{$examSum}_{$ttSum}_{$noticeSum}_{$feeSum}");

            Response::json([
                'status' => 'success',
                'hash' => $hash,
                'timestamp' => time()
            ]);
        } catch (\Throwable $e) {
            Response::json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function searchStudentForPromotion(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $uniqueId = Validator::sanitize($_GET['unique_id'] ?? $_GET['q'] ?? '');

        if (empty($uniqueId)) {
            Response::json(['error' => 'Please enter a Student Unique ID to search.'], 400);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.id, s.unique_id, s.admission_no, s.roll_no, s.first_name, s.middle_name, s.last_name, 
                   s.gender, s.dob, s.phone, s.email, s.guardian_name, s.guardian_phone, s.mother_phone,
                   s.department_id, s.standard_id, s.class_id, s.section_id, s.status, s.created_at,
                   COALESCE(d.name, 'General') as department_name,
                   CASE 
                       WHEN s.standard_id = 1 THEN '1st Year (FY)'
                       WHEN s.standard_id = 2 THEN '2nd Year (SY)'
                       WHEN s.standard_id = 3 THEN '3rd Year (TY)'
                       WHEN s.standard_id = 4 THEN '4th Year (Final)'
                       ELSE CONCAT('Year ', COALESCE(s.standard_id, 1))
                   END as standard_name,
                   COALESCE(cl.name, 'Class A') as class_name,
                   u.username
            FROM students s 
            LEFT JOIN users u ON s.user_id = u.id 
            LEFT JOIN departments d ON s.department_id = d.id 
            LEFT JOIN classes cl ON s.class_id = cl.id 
            WHERE s.tenant_id = :tid AND (LOWER(s.unique_id) = LOWER(:uid) OR LOWER(s.admission_no) = LOWER(:uid) OR s.id = :sid)
            LIMIT 1
        ");
        $stmt->execute([
            ':tid' => $tenantId,
            ':uid' => $uniqueId,
            ':sid' => is_numeric($uniqueId) ? (int) $uniqueId : 0
        ]);
        $student = $stmt->fetch();

        if (!$student) {
            Response::json(['error' => "No student found with Unique ID '{$uniqueId}' in your institution database."], 404);
            return;
        }

        $nextStandardId = (int) $student['standard_id'] + 1;
        if ($nextStandardId > 4) {
            $nextStandardId = 4;
        }

        $nextYearLabel = match ($nextStandardId) {
            1 => '1st Year (FY)',
            2 => '2nd Year (SY)',
            3 => '3rd Year (TY)',
            4 => '4th Year (Final)',
            default => "Year {$nextStandardId}"
        };

        Response::json([
            'success' => true,
            'student' => $student,
            'next_standard_id' => $nextStandardId,
            'next_standard_name' => $nextYearLabel,
            'default_target_academic_year' => ((int) date('Y') + 1) . '-' . ((int) date('Y') + 2)
        ]);
    }

    /**
     * Filter students by department and academic year for Student Digital ID Card MIS Profile
     */
    public function filterStudentsForIdCards(): void
    {
        if (!Auth::check()) {
            Response::json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $deptId = (int) ($_GET['department_id'] ?? 0);
        $academicYear = strtoupper(trim(Validator::sanitize($_GET['academic_year'] ?? '')));

        // If teacher doesn't pass department, auto-resolve their assigned staff department
        if ($deptId <= 0 && $user['role_name'] === 'teacher') {
            $stfStmt = $db->prepare("SELECT department_id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $deptId = (int) ($stfStmt->fetchColumn() ?: 0);
        }

        if ($deptId <= 0 && empty($academicYear)) {
            Response::json([
                'status' => 'success',
                'data' => [],
                'count' => 0,
                'message' => 'Department selection is required to filter students.'
            ]);
            return;
        }

        $conditions = [];
        $params = [];

        // Multi-tenant isolation: Super Admin can view all or tenant-filtered; school tenants are strictly isolated
        if (!$isSuperAdmin && $tenantId !== null) {
            $conditions[] = "s.tenant_id = :tid";
            $params[':tid'] = (int) $tenantId;
        }

        // Department filter (matches direct department_id or course department_id)
        if ($deptId > 0) {
            $conditions[] = "(s.department_id = :did1 OR c.department_id = :did2)";
            $params[':did1'] = $deptId;
            $params[':did2'] = $deptId;
        }

        // Academic Year filter (optional if ALL or empty)
        if (!empty($academicYear) && $academicYear !== 'ALL') {
            if ($academicYear === 'FY') {
                $conditions[] = "(s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%')";
            } elseif ($academicYear === 'SY') {
                $conditions[] = "(s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%')";
            } elseif ($academicYear === 'TY') {
                $conditions[] = "(s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%')";
            } elseif ($academicYear === '4Y' || $academicYear === 'FINAL') {
                $conditions[] = "(s.standard_id = 4 OR cl.code IN ('4th', 'YR4') OR cl.name LIKE '%4th%' OR cl.name LIKE '%Final%')";
            } else {
                $conditions[] = "(cl.code = :yr_code OR s.standard_id = :yr_std)";
                $params[':yr_code'] = $academicYear;
                $params[':yr_std'] = (int) $academicYear;
            }
        }

        // Scoping for Teacher, Student, Parent roles
        if ($user['role_name'] === 'teacher') {
            $stfStmt = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $stf = $stfStmt->fetch();
            $stfId = $stf['id'] ?? 0;
            $stfDeptId = $stf['department_id'] ?? 0;
            if ($stfId) {
                $conditions[] = "((s.department_id = :t_stf_dept OR c.department_id = :t_stf_dept_c)
                                 OR s.department_id IN (SELECT department_id FROM teacher_assignments WHERE staff_id = :t_stf_id AND department_id IS NOT NULL) 
                                 OR s.section_id IN (SELECT section_id FROM teacher_assignments WHERE staff_id = :t_stf_id2 AND section_id IS NOT NULL) 
                                 OR s.course_id IN (SELECT course_id FROM time_tables WHERE staff_id = :t_stf_tt AND course_id IS NOT NULL)
                                 OR s.id IN (SELECT student_id FROM student_attendance WHERE marked_by = :t_stf_id3))";
                $params[':t_stf_id'] = (int) $stfId;
                $params[':t_stf_id2'] = (int) $stfId;
                $params[':t_stf_dept'] = (int) $stfDeptId;
                $params[':t_stf_dept_c'] = (int) $stfDeptId;
                $params[':t_stf_tt'] = (int) $stfId;
                $params[':t_stf_id3'] = (int) $stfId;
            }
        } elseif ($user['role_name'] === 'student') {
            $conditions[] = "s.user_id = :user_scope_id";
            $params[':user_scope_id'] = (int) $user['id'];
        } elseif ($user['role_name'] === 'parent') {
            $conditions[] = "(s.parent_user_id = :parent_scope_id OR s.id IN (SELECT student_id FROM parent_student_map WHERE parent_user_id = :parent_scope_id2))";
            $params[':parent_scope_id'] = (int) $user['id'];
            $params[':parent_scope_id2'] = (int) $user['id'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "SELECT 
            s.id,
            s.admission_no,
            s.admission_no as student_id,
            s.roll_no,
            s.first_name,
            s.last_name,
            CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name,
            s.photo,
            s.gender,
            s.dob,
            s.email,
            s.phone,
            s.phone as student_phone,
            s.guardian_name,
            s.guardian_phone,
            s.guardian_phone as father_mobile,
            s.mother_phone,
            s.mother_phone as mother_mobile,
            COALESCE(s.department_id, c.department_id) as department_id,
            COALESCE(d.name, 'General') as department,
            COALESCE(d.name, 'General') as department_name,
            COALESCE(c.name, '') as course_name,
            CASE 
                WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'First Year (FY)'
                WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'Second Year (SY)'
                WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'Third Year (TY)'
                WHEN s.standard_id = 4 OR cl.code IN ('4th', 'YR4') OR cl.name LIKE '%4th%' OR cl.name LIKE '%Final%' THEN 'Final Year (4th)'
                ELSE 'First Year (FY)'
            END as student_year,
            CASE 
                WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                ELSE 'FY'
            END as year_code,
            COALESCE(cl.name, 'Class A') as class_name,
            COALESCE(b.batch_name, 'Academic Session 2026-2027') as batch_name,
            COALESCE(t.name, 'CAMPUS360 ENTERPRISE ERP') as institution_name,
            s.status,
            u.username as student_username
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN tenants t ON s.tenant_id = t.id
        LEFT JOIN batches b ON s.batch_id = b.id
        LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id
        LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id
        LEFT JOIN classes cl ON s.class_id = cl.id
        {$whereClause}
        GROUP BY s.id
        ORDER BY s.id ASC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();

            Response::json([
                'status' => 'success',
                'data' => $students,
                'count' => count($students)
            ]);
        } catch (\Throwable $e) {
            error_log("Error in filterStudentsForIdCards: " . $e->getMessage());
            Response::json(['status' => 'error', 'message' => 'Database error occurred while filtering students.'], 500);
        }
    }
}


