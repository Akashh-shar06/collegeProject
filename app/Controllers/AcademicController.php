<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Permission;
use App\Core\Session;
use App\Core\CSRF;
use PDO;

class AcademicController
{
    // -------------------------------------------------------------
    // 1. COURSE MANAGEMENT MODULE
    // -------------------------------------------------------------
    public function showCourses(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('courses');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $search = Validator::sanitize($_GET['search'] ?? '');
        $statusFilter = Validator::sanitize($_GET['status'] ?? 'all');

        $sql = "
            SELECT c.*, 
                   d.name as department_name,
                   (SELECT COUNT(*) FROM departments dept WHERE dept.course_id = c.id) as linked_depts_count,
                   (SELECT COUNT(*) FROM students st WHERE st.course_id = c.id) as student_count,
                   (SELECT COUNT(*) FROM subjects sub WHERE sub.course_id = c.id) as subject_count
            FROM courses c
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE (c.tenant_id = :tid OR :is_super = 1)
        ";
        $params = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];

        if (!empty($search)) {
            $sql .= " AND (c.name LIKE :q OR c.code LIKE :q OR c.description LIKE :q)";
            $params[':q'] = "%{$search}%";
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, ['active', 'inactive'], true)) {
            $sql .= " AND c.status = :st";
            $params[':st'] = $statusFilter;
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $courses = $stmt->fetchAll();

        // Fetch departments list for course parent department select
        $deptStmt = $db->prepare("SELECT id, name, code FROM departments WHERE (tenant_id = :tid OR :is_super = 1) AND status = 'active' ORDER BY name ASC");
        $deptStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $departments = $deptStmt->fetchAll();

        Response::view('academic/course_list', [
            'user' => $user,
            'courses' => $courses,
            'departments' => $departments,
            'search' => $search,
            'statusFilter' => $statusFilter
        ]);
    }

    public function storeCourse(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('courses');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token. Please try again.');
            Response::redirect('index.php?page=courses');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $id = (int)($_POST['id'] ?? 0);
        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = strtoupper(trim(Validator::sanitize($_POST['code'] ?? '')));
        $durationYears = (int)($_POST['duration_years'] ?? 3);
        $deptId = (int)($_POST['department_id'] ?? 0);
        $description = Validator::sanitize($_POST['description'] ?? '');
        $status = Validator::sanitize($_POST['status'] ?? 'active');
        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'courses');

        if (empty($name) || empty($code)) {
            Session::setFlash('error', 'Course Name and Course Code are required.');
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        // Validate duplicate course code within tenant
        $chkStmt = $db->prepare("SELECT id FROM courses WHERE (tenant_id = :tid OR :is_super = 1) AND code = :code AND id != :id LIMIT 1");
        $chkStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0),
            ':code' => $code,
            ':id' => $id
        ]);
        if ($chkStmt->fetchColumn()) {
            Session::setFlash('error', "Course Code '{$code}' is already in use by another course.");
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        // Validate duplicate course name within department & tenant
        $chkNameStmt = $db->prepare("SELECT id FROM courses WHERE (tenant_id = :tid OR :is_super = 1) AND department_id = :did AND LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id != :id AND status = 'active' LIMIT 1");
        $chkNameStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0),
            ':did' => $deptId > 0 ? $deptId : 1,
            ':name' => $name,
            ':id' => $id
        ]);
        if ($chkNameStmt->fetchColumn()) {
            Session::setFlash('error', "A course with the name '{$name}' already exists in this department.");
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        $courseId = 0;
        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE courses 
                SET name = :name, code = :code, duration_years = :dur, department_id = :did, description = :desc, status = :st 
                WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)
            ");
            $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':dur' => $durationYears > 0 ? $durationYears : 3,
                ':did' => $deptId > 0 ? $deptId : 1,
                ':desc' => $description,
                ':st' => $status,
                ':id' => $id,
                ':tid' => $tenantId,
                ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)
            ]);
            $courseId = $id;
            Session::setFlash('success', "Course '{$name}' updated successfully.");
        } else {
            $stmt = $db->prepare("
                INSERT INTO courses (tenant_id, department_id, name, code, duration_years, description, status) 
                VALUES (:tid, :did, :name, :code, :dur, :desc, :st)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':did' => $deptId > 0 ? $deptId : 1,
                ':name' => $name,
                ':code' => $code,
                ':dur' => $durationYears > 0 ? $durationYears : 3,
                ':desc' => $description,
                ':st' => $status
            ]);
            $courseId = (int)$db->lastInsertId();
            Session::setFlash('success', "New Course '{$name}' created with 3 Academic Years (FY, SY, TY)!");
        }

        // Auto-seed/ensure FY, SY, TY academic levels and semesters for this course
        if ($courseId > 0) {
            $yearsCount = max(1, min($durationYears > 0 ? $durationYears : 3, 5));
            $yearLabels = [
                1 => ['name' => 'First Year (FY)', 'code' => 'FY', 'desc' => 'FY - Semester 1 & 2'],
                2 => ['name' => 'Second Year (SY)', 'code' => 'SY', 'desc' => 'SY - Semester 3 & 4'],
                3 => ['name' => 'Third Year (TY)', 'code' => 'TY', 'desc' => 'TY - Semester 5 & 6'],
                4 => ['name' => 'Fourth Year (4Y)', 'code' => '4Y', 'desc' => '4th Year - Semester 7 & 8'],
                5 => ['name' => 'Fifth Year (5Y)', 'code' => '5Y', 'desc' => '5th Year - Semester 9 & 10'],
            ];

            for ($yr = 1; $yr <= $yearsCount; $yr++) {
                $lbl = $yearLabels[$yr] ?? ['name' => "Year {$yr}", 'code' => "Y{$yr}", 'desc' => "Year {$yr}"];
                $lvlChk = $db->prepare("SELECT id FROM academic_levels WHERE tenant_id = :tid AND course_id = :cid AND level_order = :ord LIMIT 1");
                $lvlChk->execute([':tid' => $tenantId, ':cid' => $courseId, ':ord' => $yr]);
                $levelId = $lvlChk->fetchColumn();

                if (!$levelId) {
                    $insLvl = $db->prepare("INSERT INTO academic_levels (tenant_id, course_id, name, level_order, description) VALUES (:tid, :cid, :name, :ord, :desc)");
                    $insLvl->execute([
                        ':tid' => $tenantId,
                        ':cid' => $courseId,
                        ':name' => $lbl['name'],
                        ':ord' => $yr,
                        ':desc' => $lbl['desc']
                    ]);
                    $levelId = (int)$db->lastInsertId();
                }

                // Ensure 2 semesters per year
                $semA = ($yr * 2) - 1;
                $semB = ($yr * 2);
                foreach ([$semA, $semB] as $sNum) {
                    $semChk = $db->prepare("SELECT id FROM semesters WHERE tenant_id = :tid AND course_id = :cid AND semester_number = :snum LIMIT 1");
                    $semChk->execute([':tid' => $tenantId, ':cid' => $courseId, ':snum' => $sNum]);
                    if (!$semChk->fetchColumn()) {
                        $insSem = $db->prepare("INSERT INTO semesters (tenant_id, course_id, academic_level_id, name, semester_number) VALUES (:tid, :cid, :alid, :name, :snum)");
                        $insSem->execute([
                            ':tid' => $tenantId,
                            ':cid' => $courseId,
                            ':alid' => $levelId,
                            ':name' => "Semester {$sNum}",
                            ':snum' => $sNum
                        ]);
                    }
                }
            }

            // Also ensure classes (FY, SY, TY) exist for tenant
            foreach ([['name' => 'First Year (FY)', 'code' => 'FY'], ['name' => 'Second Year (SY)', 'code' => 'SY'], ['name' => 'Third Year (TY)', 'code' => 'TY']] as $cDef) {
                $cChk = $db->prepare("SELECT id FROM classes WHERE tenant_id = :tid AND code = :code LIMIT 1");
                $cChk->execute([':tid' => $tenantId, ':code' => $cDef['code']]);
                if (!$cChk->fetchColumn()) {
                    $insCls = $db->prepare("INSERT INTO classes (tenant_id, name, code) VALUES (:tid, :name, :code)");
                    $insCls->execute([':tid' => $tenantId, ':name' => $cDef['name'], ':code' => $cDef['code']]);
                }
            }
        }

        Response::redirect('index.php?page=' . $redirectPage);
    }

    public function deleteCourse(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('courses');

        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'courses');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();
        $force = !empty($_POST['force_delete']);

        if ($id > 0) {
            // Safety check: Prevent broken foreign keys if linked to students or active modules
            $stCount = (int)$db->query("SELECT COUNT(*) FROM students WHERE course_id = {$id}")->fetchColumn();
            $deptCount = (int)$db->query("SELECT COUNT(*) FROM departments WHERE course_id = {$id}")->fetchColumn();
            $enrCount = (int)$db->query("SELECT COUNT(*) FROM student_enrollments WHERE course_id = {$id}")->fetchColumn();
            $subCount = (int)$db->query("SELECT COUNT(*) FROM subjects WHERE course_id = {$id}")->fetchColumn();

            if ($force) {
                // Force delete: unlink students and remove course structures
                $db->exec("UPDATE students SET course_id = NULL WHERE course_id = {$id}");
                $db->exec("UPDATE departments SET course_id = NULL WHERE course_id = {$id}");
                $db->exec("DELETE FROM student_enrollments WHERE course_id = {$id}");
                $db->exec("DELETE FROM subjects WHERE course_id = {$id}");
                $db->exec("DELETE FROM semesters WHERE course_id = {$id}");
                $db->exec("DELETE FROM academic_levels WHERE course_id = {$id}");
                
                $stmt = $db->prepare("DELETE FROM courses WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
                $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
                Session::setFlash('success', 'Course and associated associations permanently deleted.');
            } elseif ($stCount > 0 || $deptCount > 0 || $enrCount > 0 || $subCount > 0) {
                // Perform safe deactivation: removes course from active hierarchy roster without losing student history
                $db->exec("UPDATE courses SET status = 'inactive' WHERE id = {$id}");
                Session::setFlash('success', "Course deleted from active roster ({$stCount} student records safely archived).");
            } else {
                $db->exec("DELETE FROM semesters WHERE course_id = {$id}");
                $db->exec("DELETE FROM academic_levels WHERE course_id = {$id}");
                $stmt = $db->prepare("DELETE FROM courses WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
                $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
                Session::setFlash('success', 'Course deleted successfully.');
            }
        }

        Response::redirect('index.php?page=' . $redirectPage);
    }

    // -------------------------------------------------------------
    // 2. DEPARTMENT MANAGEMENT MODULE
    // -------------------------------------------------------------
    public function showDepartments(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('departments');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $search = Validator::sanitize($_GET['search'] ?? '');
        $statusFilter = Validator::sanitize($_GET['status'] ?? 'all');
        $courseFilter = (int)($_GET['course_id'] ?? 0);

        $sql = "
            SELECT d.*, 
                   c.name as parent_course_name, c.code as parent_course_code,
                   (SELECT COUNT(*) FROM students st WHERE st.department_id = d.id) as student_count,
                   (SELECT COUNT(*) FROM subjects sub WHERE sub.department_id = d.id) as subject_count,
                   (SELECT COUNT(*) FROM staff stf WHERE stf.department_id = d.id) as staff_count
            FROM departments d
            LEFT JOIN courses c ON d.course_id = c.id
            WHERE (d.tenant_id = :tid OR :is_super = 1)
        ";
        $params = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];

        if (!empty($search)) {
            $sql .= " AND (d.name LIKE :q OR d.code LIKE :q OR d.description LIKE :q)";
            $params[':q'] = "%{$search}%";
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, ['active', 'inactive'], true)) {
            $sql .= " AND d.status = :st";
            $params[':st'] = $statusFilter;
        }

        if ($courseFilter > 0) {
            $sql .= " AND d.course_id = :cid";
            $params[':cid'] = $courseFilter;
        }

        $sql .= " ORDER BY d.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $departments = $stmt->fetchAll();

        // Fetch courses list for selector
        $cStmt = $db->prepare("SELECT id, name, code FROM courses WHERE (tenant_id = :tid OR :is_super = 1) AND status = 'active' ORDER BY name ASC");
        $cStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $courses = $cStmt->fetchAll();

        Response::view('academic/department_list', [
            'user' => $user,
            'departments' => $departments,
            'courses' => $courses,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'courseFilter' => $courseFilter
        ]);
    }

    public function storeDepartment(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('departments');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=departments');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $id = (int)($_POST['id'] ?? 0);
        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = strtoupper(trim(Validator::sanitize($_POST['code'] ?? '')));
        $courseId = (int)($_POST['course_id'] ?? 0);
        $description = Validator::sanitize($_POST['description'] ?? '');
        $status = Validator::sanitize($_POST['status'] ?? 'active');
        $redirectPage = Validator::sanitize($_POST['redirect_page'] ?? 'departments');

        if (empty($name) || empty($code)) {
            Session::setFlash('error', 'Department Name and Department Code are required.');
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        // Validate duplicate department code within tenant
        $chkStmt = $db->prepare("SELECT id FROM departments WHERE (tenant_id = :tid OR :is_super = 1) AND code = :code AND id != :id LIMIT 1");
        $chkStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0),
            ':code' => $code,
            ':id' => $id
        ]);
        if ($chkStmt->fetchColumn()) {
            Session::setFlash('error', "Department Code '{$code}' is already registered.");
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        // Validate duplicate department name within tenant
        $chkDeptName = $db->prepare("SELECT id FROM departments WHERE (tenant_id = :tid OR :is_super = 1) AND LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id != :id AND status = 'active' LIMIT 1");
        $chkDeptName->execute([
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0),
            ':name' => $name,
            ':id' => $id
        ]);
        if ($chkDeptName->fetchColumn()) {
            Session::setFlash('error', "Department Name '{$name}' is already registered.");
            Response::redirect('index.php?page=' . $redirectPage);
            return;
        }

        $deptId = 0;
        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE departments 
                SET name = :name, code = :code, course_id = :cid, description = :desc, status = :st 
                WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)
            ");
            $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':cid' => $courseId > 0 ? $courseId : null,
                ':desc' => $description,
                ':st' => $status,
                ':id' => $id,
                ':tid' => $tenantId,
                ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)
            ]);
            $deptId = $id;
            Session::setFlash('success', "Department '{$name}' updated successfully.");
        } else {
            $stmt = $db->prepare("
                INSERT INTO departments (tenant_id, course_id, name, code, description, status) 
                VALUES (:tid, :cid, :name, :code, :desc, :st)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':cid' => $courseId > 0 ? $courseId : null,
                ':name' => $name,
                ':code' => $code,
                ':desc' => $description,
                ':st' => $status
            ]);
            $deptId = (int)$db->lastInsertId();
            Session::setFlash('success', "New Department '{$name}' added successfully.");
        }

        // Auto-seed default academic standards (FY, SY, TY) for this department
        if ($deptId > 0) {
            $standards = [
                ['name' => 'FY', 'code' => 'FY', 'seq' => 1],
                ['name' => 'SY', 'code' => 'SY', 'seq' => 2],
                ['name' => 'TY', 'code' => 'TY', 'seq' => 3],
            ];
            $stStmt = $db->prepare("INSERT INTO academic_standards (tenant_id, department_id, name, code, sequence) VALUES (:tid, :did, :name, :code, :seq)");
            foreach ($standards as $std) {
                $chkStd = $db->prepare("SELECT id FROM academic_standards WHERE tenant_id = :tid AND department_id = :did AND code = :code LIMIT 1");
                $chkStd->execute([':tid' => $tenantId, ':did' => $deptId, ':code' => $std['code']]);
                if (!$chkStd->fetchColumn()) {
                    $stStmt->execute([
                        ':tid' => $tenantId,
                        ':did' => $deptId,
                        ':name' => $std['name'],
                        ':code' => $std['code'],
                        ':seq' => $std['seq'],
                    ]);
                }
            }
        }

        Response::redirect('index.php?page=' . $redirectPage);
    }

    public function deleteDepartment(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('departments');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=departments');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        if ($id > 0) {
            $stCount = (int)$db->query("SELECT COUNT(*) FROM students WHERE department_id = {$id}")->fetchColumn();
            $subCount = (int)$db->query("SELECT COUNT(*) FROM subjects WHERE department_id = {$id}")->fetchColumn();
            $stfCount = (int)$db->query("SELECT COUNT(*) FROM staff WHERE department_id = {$id}")->fetchColumn();

            if ($stCount > 0 || $subCount > 0 || $stfCount > 0) {
                $db->exec("UPDATE departments SET status = 'inactive' WHERE id = {$id}");
                Session::setFlash('warning', "Department is referenced by records ({$stCount} students, {$subCount} subjects, {$stfCount} staff). Archived as Inactive.");
            } else {
                $stmt = $db->prepare("DELETE FROM departments WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
                $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
                Session::setFlash('success', 'Department deleted successfully.');
            }
        }

        Response::redirect('index.php?page=departments');
    }

    // -------------------------------------------------------------
    // 3. ACADEMIC STRUCTURE & SESSIONS MODULE
    // -------------------------------------------------------------
    public function showAcademicStructure(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('academic_calendar');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        // Academic Sessions
        $sessStmt = $db->prepare("SELECT * FROM academic_sessions WHERE (tenant_id = :tid OR :is_super = 1) ORDER BY id DESC");
        $sessStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $sessions = $sessStmt->fetchAll();

        // Academic Levels
        $lvlStmt = $db->prepare("
            SELECT al.*, c.name as course_name 
            FROM academic_levels al 
            LEFT JOIN courses c ON al.course_id = c.id 
            WHERE (al.tenant_id = :tid OR :is_super = 1) 
            ORDER BY al.level_order ASC, al.id ASC
        ");
        $lvlStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $levels = $lvlStmt->fetchAll();

        // Semesters
        $semStmt = $db->prepare("
            SELECT s.*, c.name as course_name, al.name as level_name 
            FROM semesters s 
            LEFT JOIN courses c ON s.course_id = c.id 
            LEFT JOIN academic_levels al ON s.academic_level_id = al.id 
            WHERE (s.tenant_id = :tid OR :is_super = 1) 
            ORDER BY s.semester_number ASC
        ");
        $semStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $semesters = $semStmt->fetchAll();

        $cStmt = $db->prepare("SELECT id, name, code FROM courses WHERE (tenant_id = :tid OR :is_super = 1) AND status = 'active' ORDER BY name ASC");
        $cStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $courses = $cStmt->fetchAll();

        Response::view('academic/academic_structure', [
            'user' => $user,
            'sessions' => $sessions,
            'levels' => $levels,
            'semesters' => $semesters,
            'courses' => $courses
        ]);
    }

    public function storeAcademicSession(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('academic_calendar');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=academic_structure');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $name = Validator::sanitize($_POST['name'] ?? '');
        $startDate = Validator::sanitize($_POST['start_date'] ?? '');
        $endDate = Validator::sanitize($_POST['end_date'] ?? '');
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;
        $status = Validator::sanitize($_POST['status'] ?? 'active');

        if (empty($name)) {
            Session::setFlash('error', 'Academic Session Name (e.g. 2026-2027) is required.');
            Response::redirect('index.php?page=academic_structure');
            return;
        }

        if ($isCurrent === 1) {
            $db->exec("UPDATE academic_sessions SET is_current = 0 WHERE tenant_id = {$tenantId}");
        }

        $stmt = $db->prepare("
            INSERT INTO academic_sessions (tenant_id, name, start_date, end_date, is_current, status) 
            VALUES (:tid, :name, :sd, :ed, :ic, :st)
            ON DUPLICATE KEY UPDATE name = VALUES(name), is_current = VALUES(is_current), status = VALUES(status)
        ");
        $stmt->execute([
            ':tid' => $tenantId,
            ':name' => $name,
            ':sd' => !empty($startDate) ? $startDate : null,
            ':ed' => !empty($endDate) ? $endDate : null,
            ':ic' => $isCurrent,
            ':st' => $status
        ]);

        Session::setFlash('success', "Academic Session '{$name}' saved successfully.");
        Response::redirect('index.php?page=academic_structure');
    }

    public function storeAcademicLevel(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('academic_calendar');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=academic_structure');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $name = Validator::sanitize($_POST['name'] ?? '');
        $courseId = (int)($_POST['course_id'] ?? 0);
        $order = (int)($_POST['level_order'] ?? 1);
        $description = Validator::sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            Session::setFlash('error', 'Academic Level Name is required.');
            Response::redirect('index.php?page=academic_structure');
            return;
        }

        // Validate duplicate academic level for same course and order/name
        $chkLvl = $db->prepare("SELECT id FROM academic_levels WHERE tenant_id = :tid AND course_id = :cid AND (level_order = :ord OR LOWER(TRIM(name)) = LOWER(TRIM(:name))) LIMIT 1");
        $chkLvl->execute([
            ':tid' => $tenantId,
            ':cid' => $courseId > 0 ? $courseId : null,
            ':ord' => $order,
            ':name' => $name
        ]);
        if ($chkLvl->fetchColumn()) {
            Session::setFlash('error', "An academic level with order {$order} or name '{$name}' already exists for this course.");
            Response::redirect('index.php?page=academic_structure');
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO academic_levels (tenant_id, course_id, name, level_order, description) 
            VALUES (:tid, :cid, :name, :ord, :desc)
        ");
        $stmt->execute([
            ':tid' => $tenantId,
            ':cid' => $courseId > 0 ? $courseId : null,
            ':name' => $name,
            ':ord' => $order,
            ':desc' => $description
        ]);

        Session::setFlash('success', "Academic Level '{$name}' configured.");
        Response::redirect('index.php?page=academic_structure');
    }

    // -------------------------------------------------------------
    // 4. SUBJECT MANAGEMENT MODULE
    // -------------------------------------------------------------
    public function showSubjects(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('subjects');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $search = Validator::sanitize($_GET['search'] ?? '');
        $courseFilter = (int)($_GET['course_id'] ?? 0);
        $deptFilter = (int)($_GET['department_id'] ?? 0);
        $levelFilter = (int)($_GET['academic_level_id'] ?? 0);
        $semFilter = (int)($_GET['semester_id'] ?? 0);
        $statusFilter = Validator::sanitize($_GET['status'] ?? 'all');

        $sql = "
            SELECT sub.*, 
                   c.name as course_name, c.code as course_code,
                   d.name as department_name, d.code as department_code,
                   al.name as level_name,
                   sem.name as semester_name
            FROM subjects sub
            LEFT JOIN courses c ON sub.course_id = c.id
            LEFT JOIN departments d ON sub.department_id = d.id
            LEFT JOIN academic_levels al ON sub.academic_level_id = al.id
            LEFT JOIN semesters sem ON sub.semester_id = sem.id
            WHERE (sub.tenant_id = :tid OR :is_super = 1)
        ";
        $params = [
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0)
        ];

        if (!empty($search)) {
            $sql .= " AND (sub.name LIKE :q OR sub.code LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($courseFilter > 0) {
            $sql .= " AND sub.course_id = :cid";
            $params[':cid'] = $courseFilter;
        }
        if ($deptFilter > 0) {
            $sql .= " AND sub.department_id = :did";
            $params[':did'] = $deptFilter;
        }
        if ($levelFilter > 0) {
            $sql .= " AND sub.academic_level_id = :alid";
            $params[':alid'] = $levelFilter;
        }
        if ($semFilter > 0) {
            $sql .= " AND sub.semester_id = :semid";
            $params[':semid'] = $semFilter;
        }
        if ($statusFilter !== 'all' && in_array($statusFilter, ['active', 'inactive'], true)) {
            $sql .= " AND sub.status = :st";
            $params[':st'] = $statusFilter;
        }

        $sql .= " ORDER BY sub.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll();

        // Dropdown Data
        $courses = $db->query("SELECT id, name, code FROM courses WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) AND status = 'active' ORDER BY name ASC")->fetchAll();
        $departments = $db->query("SELECT id, course_id, name, code FROM departments WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) AND status = 'active' ORDER BY name ASC")->fetchAll();
        $levels = $db->query("SELECT id, course_id, name FROM academic_levels WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) ORDER BY level_order ASC")->fetchAll();
        $semesters = $db->query("SELECT id, course_id, academic_level_id, name, semester_number FROM semesters WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) ORDER BY semester_number ASC")->fetchAll();

        // Offerings List
        $offeringStmt = $db->prepare("
            SELECT so.*, sub.name as subject_name, sub.code as subject_code, c.name as course_name, d.name as department_name, al.name as level_name
            FROM subject_offerings so
            JOIN subjects sub ON so.subject_id = sub.id
            LEFT JOIN courses c ON so.course_id = c.id
            LEFT JOIN departments d ON so.department_id = d.id
            LEFT JOIN academic_levels al ON so.academic_level_id = al.id
            WHERE (so.tenant_id = :tid OR :is_super = 1)
            ORDER BY so.id DESC LIMIT 50
        ");
        $offeringStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $offerings = $offeringStmt->fetchAll();

        Response::view('academic/subject_list', [
            'user' => $user,
            'subjects' => $subjects,
            'offerings' => $offerings,
            'courses' => $courses,
            'departments' => $departments,
            'levels' => $levels,
            'semesters' => $semesters,
            'filters' => [
                'search' => $search,
                'course_id' => $courseFilter,
                'department_id' => $deptFilter,
                'academic_level_id' => $levelFilter,
                'semester_id' => $semFilter,
                'status' => $statusFilter
            ]
        ]);
    }

    public function storeSubject(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('subjects');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $id = (int)($_POST['id'] ?? 0);
        $name = Validator::sanitize($_POST['name'] ?? '');
        $code = strtoupper(trim(Validator::sanitize($_POST['code'] ?? '')));
        $courseId = (int)($_POST['course_id'] ?? 0);
        $deptId = (int)($_POST['department_id'] ?? 0);
        $levelId = (int)($_POST['academic_level_id'] ?? 0);
        $semesterId = (int)($_POST['semester_id'] ?? 0);
        $type = Validator::sanitize($_POST['type'] ?? 'core');
        $credits = (int)($_POST['credits'] ?? 3);
        $maxMarks = (int)($_POST['max_marks'] ?? 100);
        $passingMarks = (int)($_POST['passing_marks'] ?? 40);
        $status = Validator::sanitize($_POST['status'] ?? 'active');

        if (empty($name) || empty($code)) {
            Session::setFlash('error', 'Subject Name and Subject Code are required.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        // Duplicate Code check within tenant
        $chkStmt = $db->prepare("SELECT id FROM subjects WHERE (tenant_id = :tid OR :is_super = 1) AND code = :code AND id != :id LIMIT 1");
        $chkStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0),
            ':code' => $code,
            ':id' => $id
        ]);
        if ($chkStmt->fetchColumn()) {
            Session::setFlash('error', "Subject Code '{$code}' already exists.");
            Response::redirect('index.php?page=subjects');
            return;
        }

        $classId = 1; // Fallback compatibility with existing schema

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE subjects 
                SET name = :name, code = :code, course_id = :cid, department_id = :did, academic_level_id = :alid, semester_id = :semid, type = :type, credits = :cr, max_marks = :mm, passing_marks = :pm, status = :st
                WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)
            ");
            $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':cid' => $courseId > 0 ? $courseId : null,
                ':did' => $deptId > 0 ? $deptId : null,
                ':alid' => $levelId > 0 ? $levelId : null,
                ':semid' => $semesterId > 0 ? $semesterId : null,
                ':type' => $type,
                ':cr' => $credits,
                ':mm' => $maxMarks,
                ':pm' => $passingMarks,
                ':st' => $status,
                ':id' => $id,
                ':tid' => $tenantId,
                ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)
            ]);
            Session::setFlash('success', "Subject '{$name}' updated.");
        } else {
            $stmt = $db->prepare("
                INSERT INTO subjects (tenant_id, course_id, department_id, academic_level_id, semester_id, class_id, name, code, type, credits, max_marks, passing_marks, status)
                VALUES (:tid, :cid, :did, :alid, :semid, :clid, :name, :code, :type, :cr, :mm, :pm, :st)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':cid' => $courseId > 0 ? $courseId : null,
                ':did' => $deptId > 0 ? $deptId : null,
                ':alid' => $levelId > 0 ? $levelId : null,
                ':semid' => $semesterId > 0 ? $semesterId : null,
                ':clid' => $classId,
                ':name' => $name,
                ':code' => $code,
                ':type' => $type,
                ':cr' => $credits,
                ':mm' => $maxMarks,
                ':pm' => $passingMarks,
                ':st' => $status
            ]);
            Session::setFlash('success', "New Subject '{$name}' added.");
        }

        Response::redirect('index.php?page=subjects');
    }

    public function deleteSubject(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('subjects');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        if ($id > 0) {
            $ttCount = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE subject_id = {$id}")->fetchColumn();
            if ($ttCount > 0) {
                $db->exec("UPDATE subjects SET status = 'inactive' WHERE id = {$id}");
                Session::setFlash('warning', 'Subject is linked to active timetable schedules. Archived as Inactive.');
            } else {
                $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
                $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
                Session::setFlash('success', 'Subject deleted.');
            }
        }

        Response::redirect('index.php?page=subjects');
    }

    public function storeSubjectOffering(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('subjects');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid token.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $deptId = (int)($_POST['department_id'] ?? 0);
        $levelId = (int)($_POST['academic_level_id'] ?? 0);
        $semesterId = (int)($_POST['semester_id'] ?? 0);

        if ($subjectId <= 0) {
            Session::setFlash('error', 'Subject selection is required.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        // Prevent accidental duplicate subject offerings
        $chk = $db->prepare("
            SELECT id FROM subject_offerings 
            WHERE tenant_id = :tid AND subject_id = :subid 
              AND (course_id = :cid1 OR :cid2 = 0)
              AND (department_id = :did1 OR :did2 = 0)
              AND (academic_level_id = :alid1 OR :alid2 = 0)
            LIMIT 1
        ");
        $chk->execute([
            ':tid' => $tenantId,
            ':subid' => $subjectId,
            ':cid1' => $courseId,
            ':cid2' => $courseId,
            ':did1' => $deptId,
            ':did2' => $deptId,
            ':alid1' => $levelId,
            ':alid2' => $levelId
        ]);

        if ($chk->fetchColumn()) {
            Session::setFlash('error', 'Subject offering relationship already assigned.');
            Response::redirect('index.php?page=subjects');
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO subject_offerings (tenant_id, subject_id, course_id, department_id, academic_level_id, semester_id)
            VALUES (:tid, :subid, :cid, :did, :alid, :semid)
        ");
        $stmt->execute([
            ':tid' => $tenantId,
            ':subid' => $subjectId,
            ':cid' => $courseId > 0 ? $courseId : null,
            ':did' => $deptId > 0 ? $deptId : null,
            ':alid' => $levelId > 0 ? $levelId : null,
            ':semid' => $semesterId > 0 ? $semesterId : null,
        ]);

        Session::setFlash('success', 'Subject offering assigned successfully.');
        Response::redirect('index.php?page=subjects');
    }

    // -------------------------------------------------------------
    // 5. STUDENT PROMOTION DASHBOARD & WORKFLOW
    // -------------------------------------------------------------
    public function showStudentPromotion(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('admission');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $sourceSession = Validator::sanitize($_GET['source_session'] ?? '2026-2027');
        $targetSession = Validator::sanitize($_GET['target_session'] ?? '2027-2028');
        $courseId = (int)($_GET['course_id'] ?? 0);
        $deptId = (int)($_GET['department_id'] ?? 0);
        $levelId = (int)($_GET['academic_level_id'] ?? 0);

        // Fetch eligible students for criteria
        $students = [];
        if ($courseId > 0 || $deptId > 0 || $levelId > 0) {
            $sql = "
                SELECT s.*, 
                       u.username,
                       c.name as course_name, c.code as course_code,
                       d.name as department_name, d.code as department_code,
                       al.name as level_name,
                       se.status as enrollment_status, se.academic_year as current_session
                FROM students s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN departments d ON s.department_id = d.id
                LEFT JOIN academic_levels al ON s.standard_id = al.id
                LEFT JOIN student_enrollments se ON (s.id = se.student_id AND se.status = 'active')
                WHERE (s.tenant_id = :tid OR :is_super = 1) AND s.status = 'active'
            ";
            $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];

            if ($courseId > 0) {
                $sql .= " AND s.course_id = :cid";
                $params[':cid'] = $courseId;
            }
            if ($deptId > 0) {
                $sql .= " AND s.department_id = :did";
                $params[':did'] = $deptId;
            }
            if ($levelId > 0) {
                $sql .= " AND s.standard_id = :alid";
                $params[':alid'] = $levelId;
            }

            $sql .= " ORDER BY s.first_name ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
        }

        // Sessions list & Levels list
        $sessions = $db->query("SELECT * FROM academic_sessions WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) ORDER BY id DESC")->fetchAll();
        $courses = $db->query("SELECT id, name, code FROM courses WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) AND status = 'active' ORDER BY name ASC")->fetchAll();
        $departments = $db->query("SELECT id, course_id, name, code FROM departments WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) AND status = 'active' ORDER BY name ASC")->fetchAll();
        $levels = $db->query("SELECT id, name, level_order FROM academic_levels WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) ORDER BY level_order ASC")->fetchAll();

        Response::view('academic/student_promotion', [
            'user' => $user,
            'students' => $students,
            'sessions' => $sessions,
            'courses' => $courses,
            'departments' => $departments,
            'levels' => $levels,
            'filters' => [
                'source_session' => $sourceSession,
                'target_session' => $targetSession,
                'course_id' => $courseId,
                'department_id' => $deptId,
                'academic_level_id' => $levelId
            ]
        ]);
    }

    public function processBulkPromotion(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('admission');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect('index.php?page=student_promotion');
            return;
        }

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $operatorId = $user['id'];
        $db = Database::getInstance();

        $selectedStudents = $_POST['student_ids'] ?? [];
        $sourceSession = Validator::sanitize($_POST['source_session'] ?? '2026-2027');
        $targetSession = Validator::sanitize($_POST['target_session'] ?? '2027-2028');
        $targetLevelId = (int)($_POST['target_academic_level_id'] ?? 0);
        $targetSemesterId = (int)($_POST['target_semester_id'] ?? 0);
        $remarks = Validator::sanitize($_POST['remarks'] ?? 'Bulk Year-End Student Academic Promotion');

        if (empty($selectedStudents) || !is_array($selectedStudents)) {
            Session::setFlash('error', 'Please select at least one student to promote.');
            Response::redirect('index.php?page=student_promotion');
            return;
        }

        if ($targetLevelId <= 0) {
            Session::setFlash('error', 'Target Academic Level selection is required.');
            Response::redirect('index.php?page=student_promotion');
            return;
        }

        // Get target level name
        $lvlStmt = $db->prepare("SELECT name FROM academic_levels WHERE id = :id");
        $lvlStmt->execute([':id' => $targetLevelId]);
        $targetLevelName = $lvlStmt->fetchColumn() ?: 'Next Level';

        $successCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        try {
            $db->beginTransaction();

            foreach ($selectedStudents as $sId) {
                $studentId = (int)$sId;

                // Lock student record
                if (($user['role_name'] ?? '') === 'super_admin') {
                    $stStmt = $db->prepare("SELECT * FROM students WHERE id = :sid FOR UPDATE");
                    $stStmt->execute([':sid' => $studentId]);
                } else {
                    $stStmt = $db->prepare("SELECT * FROM students WHERE id = :sid AND tenant_id = :tid FOR UPDATE");
                    $stStmt->execute([':sid' => $studentId, ':tid' => $tenantId]);
                }
                $student = $stStmt->fetch();

                if (!$student) {
                    $errorCount++;
                    continue;
                }

                // Check active enrollment
                $enrStmt = $db->prepare("SELECT * FROM student_enrollments WHERE student_id = :sid AND status = 'active' ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $enrStmt->execute([':sid' => $studentId]);
                $currentEnr = $enrStmt->fetch();

                $sourceLevelName = 'First Year';
                $fromEnrId = 0;

                if ($currentEnr) {
                    $fromEnrId = (int)$currentEnr['id'];
                    $sourceLevelName = $currentEnr['academic_year'] ?? 'Current Level';

                    // Close old enrollment
                    $updOld = $db->prepare("UPDATE student_enrollments SET status = 'promoted', promoted_at = NOW() WHERE id = :id");
                    $updOld->execute([':id' => $fromEnrId]);
                }

                // Check duplicate target enrollment
                $dupCheck = $db->prepare("SELECT id FROM student_enrollments WHERE student_id = :sid AND academic_year = :ts AND academic_level_id = :tlid LIMIT 1");
                $dupCheck->execute([':sid' => $studentId, ':ts' => $targetSession, ':tlid' => $targetLevelId]);
                if ($dupCheck->fetchColumn()) {
                    $skippedCount++;
                    continue;
                }

                // Create new enrollment
                $insEnr = $db->prepare("
                    INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, academic_level_id, semester_id, status, enrolled_at)
                    VALUES (:tid, :sid, :ay, :cid, :did, :alid, :semid, 'active', NOW())
                ");
                $insEnr->execute([
                    ':tid' => $tenantId,
                    ':sid' => $studentId,
                    ':ay' => $targetSession,
                    ':cid' => $student['course_id'],
                    ':did' => $student['department_id'],
                    ':alid' => $targetLevelId,
                    ':semid' => $targetSemesterId > 0 ? $targetSemesterId : ($student['semester_id'] ?? null),
                ]);
                $toEnrId = (int)$db->lastInsertId();

                // Record promotion audit trail
                $insPromo = $db->prepare("
                    INSERT INTO student_promotions (tenant_id, student_id, from_enrollment_id, to_enrollment_id, from_academic_session, to_academic_session, from_academic_level, to_academic_level, operator_id, status, remarks)
                    VALUES (:tid, :sid, :fid, :tid_e, :fas, :tas, :fal, :tal, :op, 'success', :rem)
                ");
                $insPromo->execute([
                    ':tid' => $tenantId,
                    ':sid' => $studentId,
                    ':fid' => $fromEnrId > 0 ? $fromEnrId : 0,
                    ':tid_e' => $toEnrId,
                    ':fas' => $sourceSession,
                    ':tas' => $targetSession,
                    ':fal' => $sourceLevelName,
                    ':tal' => $targetLevelName,
                    ':op' => $operatorId,
                    ':rem' => $remarks
                ]);

                // Update current student record pointers
                $updSt = $db->prepare("UPDATE students SET standard_id = :alid WHERE id = :sid");
                $updSt->execute([
                    ':alid' => $targetLevelId,
                    ':sid' => $studentId
                ]);

                $successCount++;
            }

            $db->commit();
            Session::setFlash('success', "Promotion Completed: {$successCount} students promoted to '{$targetLevelName}' ({$targetSession}). {$skippedCount} skipped.");

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Promotion Error: " . $e->getMessage());
            Session::setFlash('error', "Promotion Failed: " . $e->getMessage());
        }

        Response::redirect('index.php?page=student_promotion');
    }

    public function showPromotionHistory(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('admission');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $db = Database::getInstance();

        $search = Validator::sanitize($_GET['search'] ?? '');
        $sessionFilter = Validator::sanitize($_GET['session'] ?? 'all');

        $sql = "
            SELECT p.*,
                   s.unique_id, s.admission_no, s.first_name, s.last_name,
                   c.name as course_name,
                   d.name as department_name,
                   u.username as operator_name
            FROM student_promotions p
            JOIN students s ON p.student_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN users u ON p.operator_id = u.id
            WHERE (p.tenant_id = :tid OR :is_super = 1)
        ";
        $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];

        if (!empty($search)) {
            $sql .= " AND (s.first_name LIKE :q OR s.last_name LIKE :q OR s.unique_id LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($sessionFilter !== 'all') {
            $sql .= " AND (p.from_academic_session = :sfs OR p.to_academic_session = :sfs)";
            $params[':sfs'] = $sessionFilter;
        }

        $sql .= " ORDER BY p.id DESC LIMIT 100";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll();

        $sessions = $db->query("SELECT * FROM academic_sessions WHERE (tenant_id = {$tenantId} OR {$isSuperAdmin}) ORDER BY id DESC")->fetchAll();

        Response::view('academic/promotion_history', [
            'user' => $user,
            'history' => $history,
            'sessions' => $sessions,
            'search' => $search,
            'sessionFilter' => $sessionFilter
        ]);
    }

    // -------------------------------------------------------------
    // 7. ACADEMIC HIERARCHY EXPLORER & MANAGEMENT
    // (Department -> Courses -> Years [FY, SY, TY] -> Students)
    // -------------------------------------------------------------
    public static function getAcademicHierarchyData(int $tenantId, bool $isSuperAdmin = false, ?int $deptFilter = null): array
    {
        $db = Database::getInstance();
        $tid = (int)$tenantId;

        $tDept = $isSuperAdmin ? "1=1" : "d.tenant_id = {$tid}";
        $tCourse = $isSuperAdmin ? "1=1" : "c.tenant_id = {$tid}";
        $tStudent = $isSuperAdmin ? "1=1" : "s.tenant_id = {$tid}";

        $deptSql = "
            SELECT d.*,
                   (SELECT COUNT(DISTINCT c.id) FROM courses c WHERE c.department_id = d.id AND {$tCourse} AND c.status = 'active') as course_count,
                   (SELECT COUNT(DISTINCT s.id) FROM students s LEFT JOIN courses c2 ON s.course_id = c2.id WHERE (s.department_id = d.id OR c2.department_id = d.id) AND {$tStudent} AND (s.status = 'active' OR s.status IS NULL)) as student_count
            FROM departments d
            WHERE {$tDept} AND d.status = 'active'
        ";
        if ($deptFilter && $deptFilter > 0) {
            $deptSql .= " AND d.id = " . (int)$deptFilter;
        }
        $deptSql .= " ORDER BY d.id ASC";

        $departments = $db->query($deptSql)->fetchAll(PDO::FETCH_ASSOC);

        $hierarchy = [];
        $totalCourses = 0;
        $totalFyStudents = 0;
        $totalSyStudents = 0;
        $totalTyStudents = 0;
        $totalStudents = 0;

        foreach ($departments as $dept) {
            $did = (int)$dept['id'];

            $courseSql = "
                SELECT c.*,
                       (SELECT COUNT(DISTINCT s.id) FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE (s.course_id = c.id OR b.course_id = c.id) AND {$tStudent} AND (s.status = 'active' OR s.status IS NULL)) as total_students
                FROM courses c
                WHERE c.department_id = {$did} AND {$tCourse} AND c.status = 'active'
                ORDER BY c.id ASC
            ";
            $courses = $db->query($courseSql)->fetchAll(PDO::FETCH_ASSOC);
            $totalCourses += count($courses);

            $courseList = [];
            foreach ($courses as $c) {
                $cid = (int)$c['id'];

                $years = [
                    1 => ['code' => 'FY', 'name' => 'First Year (FY)', 'order' => 1, 'semesters' => 'Sem 1 & Sem 2', 'students' => []],
                    2 => ['code' => 'SY', 'name' => 'Second Year (SY)', 'order' => 2, 'semesters' => 'Sem 3 & Sem 4', 'students' => []],
                    3 => ['code' => 'TY', 'name' => 'Third Year (TY)', 'order' => 3, 'semesters' => 'Sem 5 & Sem 6', 'students' => []],
                ];

                $studentSql = "
                    SELECT s.id, s.admission_no, s.roll_no, s.first_name, s.middle_name, s.last_name, 
                           s.email, s.phone, s.gender, s.status, s.standard_id, s.class_id, s.unique_id, s.photo,
                           cl.code as class_code, cl.name as class_name,
                           u.username
                    FROM students s
                    LEFT JOIN users u ON s.user_id = u.id
                    LEFT JOIN classes cl ON s.class_id = cl.id
                    LEFT JOIN batches b ON s.batch_id = b.id
                    WHERE (s.course_id = {$cid} OR b.course_id = {$cid}) AND {$tStudent} AND (s.status = 'active' OR s.status IS NULL)
                    GROUP BY s.id
                    ORDER BY CAST(s.roll_no AS UNSIGNED) ASC, s.roll_no ASC, s.first_name ASC
                ";
                $allStudents = $db->query($studentSql)->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allStudents as $st) {
                    $yr = 1;
                    if (!empty($st['standard_id']) && (int)$st['standard_id'] >= 1 && (int)$st['standard_id'] <= 3) {
                        $yr = (int)$st['standard_id'];
                    } elseif (!empty($st['class_code'])) {
                        $cc = strtoupper(trim($st['class_code']));
                        if (in_array($cc, ['FY', 'YR1', '1ST'])) $yr = 1;
                        elseif (in_array($cc, ['SY', 'YR2', '2ND'])) $yr = 2;
                        elseif (in_array($cc, ['TY', 'YR3', '3RD'])) $yr = 3;
                    } elseif (!empty($st['class_name'])) {
                        $cn = strtoupper($st['class_name']);
                        if (strpos($cn, 'FIRST') !== false || strpos($cn, '1ST') !== false || strpos($cn, 'FY') !== false) $yr = 1;
                        elseif (strpos($cn, 'SECOND') !== false || strpos($cn, '2ND') !== false || strpos($cn, 'SY') !== false) $yr = 2;
                        elseif (strpos($cn, 'THIRD') !== false || strpos($cn, '3RD') !== false || strpos($cn, 'TY') !== false) $yr = 3;
                    } elseif (!empty($st['admission_no'])) {
                        $adm = strtoupper($st['admission_no']);
                        if (strpos($adm, '-FY-') !== false || strpos($adm, 'FY') !== false) $yr = 1;
                        elseif (strpos($adm, '-SY-') !== false || strpos($adm, 'SY') !== false) $yr = 2;
                        elseif (strpos($adm, '-TY-') !== false || strpos($adm, 'TY') !== false) $yr = 3;
                    } elseif (!empty($st['roll_no'])) {
                        $r = strtoupper($st['roll_no']);
                        if (strpos($r, 'FY') === 0) $yr = 1;
                        elseif (strpos($r, 'SY') === 0) $yr = 2;
                        elseif (strpos($r, 'TY') === 0) $yr = 3;
                    }

                    if ($yr < 1 || $yr > 3) {
                        $yr = 1;
                    }

                    $years[$yr]['students'][] = $st;
                    $totalStudents++;
                    if ($yr === 1) $totalFyStudents++;
                    elseif ($yr === 2) $totalSyStudents++;
                    elseif ($yr === 3) $totalTyStudents++;
                }

                $c['years'] = array_values($years);
                $courseList[] = $c;
            }

            $dept['courses'] = $courseList;
            $hierarchy[] = $dept;
        }

        return [
            'departments' => $hierarchy,
            'stats' => [
                'total_departments' => count($departments),
                'total_courses' => $totalCourses,
                'total_fy_students' => $totalFyStudents,
                'total_sy_students' => $totalSyStudents,
                'total_ty_students' => $totalTyStudents,
                'total_students' => $totalStudents,
            ]
        ];
    }

    public function showAcademicHierarchy(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('academic_structure');

        $user = Auth::user();
        $db = Database::getInstance();
        $tenantId = (int)($user['tenant_id'] ?? 1);
        $isSuperAdmin = ($user['role_name'] === 'super_admin');

        $deptFilter = isset($_GET['dept_id']) && $_GET['dept_id'] !== 'all' ? (int)$_GET['dept_id'] : null;

        // Scoping for Teaching Staff: automatically default to their assigned department
        if ($user['role_name'] === 'teacher' && !$deptFilter) {
            $stfStmt = $db->prepare("SELECT department_id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $tDeptId = (int)$stfStmt->fetchColumn();
            if ($tDeptId > 0) {
                $deptFilter = $tDeptId;
            }
        }

        $hierarchyData = self::getAcademicHierarchyData($tenantId, $isSuperAdmin, $deptFilter);

        $isSuper = $isSuperAdmin ? 1 : 0;
        $allDepts = $db->query("SELECT id, name, code FROM departments WHERE (tenant_id = {$tenantId} OR {$isSuper} = 1) AND status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $canManage = Permission::canManage('academic_structure');

        Response::view('academic/academic_hierarchy', [
            'user' => $user,
            'departments' => $hierarchyData['departments'],
            'stats' => $hierarchyData['stats'],
            'allDepartments' => $allDepts,
            'selectedDeptId' => $deptFilter,
            'canManage' => $canManage
        ]);
    }

    public function apiGetAcademicHierarchy(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $user = Auth::user();
        $tenantId = (int)($user['tenant_id'] ?? 1);
        $isSuperAdmin = ($user['role_name'] === 'super_admin');
        $deptFilter = isset($_GET['dept_id']) && $_GET['dept_id'] !== 'all' ? (int)$_GET['dept_id'] : null;

        $hierarchyData = self::getAcademicHierarchyData($tenantId, $isSuperAdmin, $deptFilter);
        Response::json($hierarchyData);
    }

    public function quickEnrollStudent(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }
        Permission::enforce('admission');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid security token.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=academic_hierarchy');
            return;
        }

        $user = Auth::user();
        $tenantId = (int)($user['tenant_id'] ?? 1);
        $db = Database::getInstance();

        $deptId = (int)($_POST['department_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $yearOrder = (int)($_POST['year_order'] ?? 1);
        $firstName = Validator::sanitize($_POST['first_name'] ?? '');
        $lastName = Validator::sanitize($_POST['last_name'] ?? '');
        $email = Validator::sanitize($_POST['email'] ?? '');
        $phone = Validator::sanitize($_POST['phone'] ?? '+91 9876543210');
        $gender = Validator::sanitize($_POST['gender'] ?? 'male');
        $rollNo = Validator::sanitize($_POST['roll_no'] ?? '');

        if (empty($firstName) || empty($lastName) || $deptId <= 0 || $courseId <= 0) {
            Session::setFlash('error', 'First Name, Last Name, Department, and Course are required.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=academic_hierarchy');
            return;
        }

        $yearCode = $yearOrder === 1 ? 'FY' : ($yearOrder === 2 ? 'SY' : 'TY');

        $clsStmt = $db->prepare("SELECT id FROM classes WHERE tenant_id = :tid AND code = :code LIMIT 1");
        $clsStmt->execute([':tid' => $tenantId, ':code' => $yearCode]);
        $classId = (int)($clsStmt->fetchColumn() ?: $yearOrder);

        if (empty($rollNo)) {
            $maxRoll = (int)$db->query("SELECT COUNT(*) FROM students WHERE course_id = {$courseId} AND standard_id = {$yearOrder}")->fetchColumn() + 1;
            $cCode = $db->query("SELECT code FROM courses WHERE id = {$courseId}")->fetchColumn() ?: 'CR';
            $rollNo = "{$yearCode}-{$cCode}-" . str_pad((string)$maxRoll, 2, '0', STR_PAD_LEFT);
        }

        $uniqueId = \App\Controllers\WorkflowController::generateNextUniqueId($tenantId);
        $admNo = "ADM-" . date('Y') . "-{$yearCode}-" . rand(1000, 9999);
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName)) . '_' . bin2hex(random_bytes(3));
        $passHash = password_hash('Student@123', PASSWORD_BCRYPT);

        try {
            $db->beginTransaction();

            $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :u, :em, :p, 5, 'active')");
            $uStmt->execute([
                ':tid' => $tenantId,
                ':u' => $username,
                ':em' => !empty($email) ? $email : "{$username}@college.edu",
                ':p' => $passHash
            ]);
            $userId = (int)$db->lastInsertId();

            $stStmt = $db->prepare("
                INSERT INTO students 
                (tenant_id, user_id, unique_id, admission_no, roll_no, first_name, last_name, email, phone, gender, dob, department_id, course_id, standard_id, class_id, batch_id, status)
                VALUES 
                (:tid, :uid, :uniq, :adm, :roll, :fn, :ln, :em, :ph, :gen, '2005-01-01', :did, :cid, :sid, :clsid, 1, 'active')
            ");
            $stStmt->execute([
                ':tid' => $tenantId,
                ':uid' => $userId,
                ':uniq' => $uniqueId,
                ':adm' => $admNo,
                ':roll' => $rollNo,
                ':fn' => $firstName,
                ':ln' => $lastName,
                ':em' => !empty($email) ? $email : "{$username}@college.edu",
                ':ph' => $phone,
                ':gen' => $gender,
                ':did' => $deptId,
                ':cid' => $courseId,
                ':sid' => $yearOrder,
                ':clsid' => $classId
            ]);
            $studentId = (int)$db->lastInsertId();

            $ay = date('Y') . '-' . ((int)date('Y') + 1);
            $db->exec("INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, standard_id, class_id, status) VALUES ({$tenantId}, {$studentId}, '{$ay}', {$courseId}, {$deptId}, {$yearOrder}, {$classId}, 'active')");

            $db->commit();
            Session::setFlash('success', "Student {$firstName} {$lastName} enrolled successfully in {$yearCode} (Roll: {$rollNo})!");
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::setFlash('error', "Enrollment failed: " . $e->getMessage());
        }

        Response::redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=academic_hierarchy');
    }
}
