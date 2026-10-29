<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use PDO;

class OnlineExamController
{
    private function ensureTablesExist(PDO $db): void
    {
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `online_exams` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `title` VARCHAR(150) NOT NULL,
                `course_id` INT NULL,
                `student_id` INT NULL,
                `duration_minutes` INT NOT NULL DEFAULT 15,
                `total_questions` INT NOT NULL DEFAULT 5,
                `status` ENUM('draft', 'published', 'closed') DEFAULT 'published',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Auto migration: ensure required columns exist if table was pre-existing
            try {
                $cols = $db->query("SHOW COLUMNS FROM `online_exams`")->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('department_id', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
                }
                if (!in_array('student_id', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `student_id` INT NULL AFTER `course_id`");
                }
                if (!in_array('academic_year', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `academic_year` VARCHAR(20) NULL AFTER `course_id`");
                }
                if (!in_array('standard_id', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `standard_id` INT NULL AFTER `academic_year`");
                }
                if (!in_array('subject_id', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `subject_id` INT NULL AFTER `standard_id`");
                }
                if (in_array('course_id', $cols)) {
                    $db->exec("ALTER TABLE `online_exams` MODIFY COLUMN `course_id` INT NULL");
                }
                // Ensure status enum supports 'archived'
                $statusType = $db->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'online_exams' AND COLUMN_NAME = 'status'")->fetchColumn();
                if ($statusType && strpos($statusType, "'archived'") === false) {
                    $db->exec("ALTER TABLE `online_exams` MODIFY COLUMN `status` ENUM('draft', 'published', 'closed', 'archived') DEFAULT 'published'");
                }
            } catch (\Throwable $e) {
                // Ignore alter errors if already present
            }

            $db->exec("CREATE TABLE IF NOT EXISTS `online_exam_questions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `exam_id` INT NOT NULL,
                `question_text` TEXT NOT NULL,
                `option_a` VARCHAR(255) NOT NULL,
                `option_b` VARCHAR(255) NOT NULL,
                `option_c` VARCHAR(255) NOT NULL,
                `option_d` VARCHAR(255) NOT NULL,
                `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
                `marks` INT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->exec("CREATE TABLE IF NOT EXISTS `online_exam_submissions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT NOT NULL,
                `exam_id` INT NOT NULL,
                `student_id` INT NOT NULL,
                `score` DECIMAL(5,2) DEFAULT 0,
                `total_score` DECIMAL(5,2) DEFAULT 0,
                `submission_status` ENUM('submitted', 'auto_submitted_tab_switch', 'auto_submitted_back_button', 'auto_submitted_timeout') DEFAULT 'submitted',
                `submission_reason` VARCHAR(100) DEFAULT 'manual',
                `started_at` DATETIME NULL,
                `submitted_at` DATETIME NULL,
                FOREIGN KEY (`exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->exec("CREATE TABLE IF NOT EXISTS `online_exam_answers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `submission_id` INT NOT NULL,
                `question_id` INT NOT NULL,
                `selected_option` ENUM('A', 'B', 'C', 'D') NULL,
                `is_correct` TINYINT(1) DEFAULT 0,
                FOREIGN KEY (`submission_id`) REFERENCES `online_exam_submissions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log("OnlineExamController ensureTablesExist Exception: " . $e->getMessage());
        }
    }

    public function showOnlineExamModule(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();

        $this->ensureTablesExist($db);

        // Fetch departments for dropdown
        $departments = [];
        try {
            $stmt = $db->prepare("SELECT id, name, code FROM departments WHERE (tenant_id = :tid OR :is_super = 1) AND (status = 'active' OR status IS NULL) ORDER BY name ASC");
            $stmt->execute([':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            $departments = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $departments = [];
        }

        // Fetch courses for dropdown
        $courses = [];
        try {
            $stmt = $db->prepare("SELECT id, name, code, department_id FROM courses WHERE (tenant_id = :tid OR :is_super = 1) AND (status = 'active' OR status IS NULL) ORDER BY name ASC");
            $stmt->execute([':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            $courses = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $courses = [];
        }

        // Fetch subjects for dropdown
        $subjects = [];
        try {
            $stmt = $db->prepare("SELECT id, name, code, department_id, course_id, standard_id FROM subjects WHERE (tenant_id = :tid OR :is_super = 1) ORDER BY name ASC");
            $stmt->execute([':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            $subjects = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $subjects = [];
        }

        // Fetch students for dropdown with academic year and course linkage
        $students = [];
        try {
            $stmt = $db->prepare("
                SELECT s.id, s.first_name, s.last_name, s.roll_no, s.admission_no, 
                       COALESCE(s.course_id, b.course_id) as course_id,
                       COALESCE(s.department_id, c.department_id) as department_id,
                       s.standard_id, s.class_id,
                       CASE 
                           WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                           WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                           WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                           ELSE 'FY'
                       END as academic_year_code
                FROM students s 
                LEFT JOIN batches b ON s.batch_id = b.id 
                LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                WHERE (s.tenant_id = :tid OR :is_super = 1)
                ORDER BY s.first_name ASC
            ");
            $stmt->execute([':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
            $students = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $students = [];
        }

        // Auto seed default sample exam ONLY on a completely pristine installation (0 exams ever created in entire database)
        $totalSystemExamsEver = (int) $db->query("SELECT MAX(id) FROM online_exams")->fetchColumn();
        if ($totalSystemExamsEver === 0) {
            $courseId = !empty($courses) ? $courses[0]['id'] : 1;
            $deptId = !empty($departments) ? $departments[0]['id'] : null;
            $insExam = $db->prepare("INSERT INTO online_exams (tenant_id, department_id, title, course_id, academic_year, standard_id, student_id, duration_minutes, total_questions, status) 
                                      VALUES (:tid, :did, 'Computer Networks & Internet Protocols MCQ Quiz', :cid, 'FY', 1, NULL, 15, 3, 'published')");
            $insExam->execute([':tid' => $tenantId, ':did' => $deptId, ':cid' => $courseId]);
            $newExamId = (int) $db->lastInsertId();

            $sampleQuestions = [
                [
                    'text' => 'What does HTTP stand for in Web Development?',
                    'a' => 'HyperText Transfer Protocol',
                    'b' => 'HighText Transfer Protocol',
                    'c' => 'HyperText Transmission Protocol',
                    'd' => 'Hyperlink Text Transfer Protocol',
                    'correct' => 'A',
                    'marks' => 1
                ],
                [
                    'text' => 'Which OSI model layer is responsible for IP Addressing and Routing?',
                    'a' => 'Data Link Layer',
                    'b' => 'Network Layer',
                    'c' => 'Transport Layer',
                    'd' => 'Application Layer',
                    'correct' => 'B',
                    'marks' => 1
                ],
                [
                    'text' => 'What is the default port number used by HTTPS?',
                    'a' => '80',
                    'b' => '21',
                    'c' => '443',
                    'd' => '8080',
                    'correct' => 'C',
                    'marks' => 1
                ]
            ];

            foreach ($sampleQuestions as $sq) {
                $insQ = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) 
                                       VALUES (:eid, :q, :a, :b, :c, :d, :correct, :marks)");
                $insQ->execute([
                    ':eid' => $newExamId,
                    ':q' => $sq['text'],
                    ':a' => $sq['a'],
                    ':b' => $sq['b'],
                    ':c' => $sq['c'],
                    ':d' => $sq['d'],
                    ':correct' => $sq['correct'],
                    ':marks' => $sq['marks']
                ]);
            }
        }

        // Count archived exams for Principal / Teacher toggles
        $archivedCount = 0;
        try {
            $archivedCount = (int) $db->query("SELECT COUNT(*) FROM online_exams WHERE tenant_id = {$tenantId} AND status = 'archived'")->fetchColumn();
        } catch (\Throwable $e) {
            $archivedCount = 0;
        }

        $viewArchived = !empty($_GET['view_archived']);
        $selectedYearFilter = !empty($_GET['academic_year']) ? strtoupper(Validator::sanitize($_GET['academic_year'])) : 'ALL';

        // Selected exam for managing questions
        $selectedExamId = (int) ($_GET['selected_exam_id'] ?? 0);

        // Fetch list of online exams with details
        $exams = [];
        try {
            $whereClauses = ["e.tenant_id = :tid"];
            $params = [':tid' => $tenantId];

            if ($user['role_name'] === 'student') {
                $stRow = $db->prepare("
                    SELECT s.id, 
                           COALESCE(s.course_id, b.course_id) as course_id,
                           COALESCE(s.department_id, c.department_id) as department_id,
                           s.standard_id,
                           CASE 
                               WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                               WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                               WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                               ELSE 'FY'
                           END as academic_year_code
                    FROM students s 
                    LEFT JOIN batches b ON s.batch_id = b.id 
                    LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id
                    LEFT JOIN classes cl ON s.class_id = cl.id
                    WHERE s.user_id = :uid LIMIT 1
                ");
                $stRow->execute([':uid' => $user['id']]);
                $stData = $stRow->fetch();
                $stId = (int) ($stData['id'] ?? 0);
                $stCourseId = (int) ($stData['course_id'] ?? 0);
                $stDeptId = (int) ($stData['department_id'] ?? 0);
                $stYear = $stData['academic_year_code'] ?? 'FY';
                $stStdId = ($stYear === 'FY') ? 1 : (($stYear === 'SY') ? 2 : 3);

                $whereClauses[] = "e.status = 'published'";
                
                // Student Eligibility Filter: Exam must be specifically targeted to this student OR match student's course, department, and academic year
                $studentEligibilityClause = "((e.student_id IS NOT NULL AND e.student_id > 0 AND e.student_id = :sid)";
                $studentEligibilityClause .= " OR ((e.student_id IS NULL OR e.student_id = 0)";
                
                if ($stCourseId > 0) {
                    $studentEligibilityClause .= " AND (e.course_id IS NULL OR e.course_id = 0 OR e.course_id = :cid)";
                    $params[':cid'] = $stCourseId;
                }
                if ($stDeptId > 0) {
                    $studentEligibilityClause .= " AND (e.department_id IS NULL OR e.department_id = 0 OR e.department_id = :did)";
                    $params[':did'] = $stDeptId;
                }
                $studentEligibilityClause .= " AND (e.academic_year IS NULL OR e.academic_year = '' OR UPPER(e.academic_year) = :ay OR e.standard_id IS NULL OR e.standard_id = 0 OR e.standard_id = :stdid)";
                $studentEligibilityClause .= "))";

                $whereClauses[] = $studentEligibilityClause;
                $params[':sid'] = $stId;
                $params[':ay'] = $stYear;
                $params[':stdid'] = $stStdId;
            } else {
                if ($viewArchived) {
                    $whereClauses[] = "e.status = 'archived'";
                } else {
                    $whereClauses[] = "(e.status != 'archived' OR e.status IS NULL)";
                }

                // Academic Year filter for teachers/admins
                if (in_array($selectedYearFilter, ['FY', 'SY', 'TY'], true)) {
                    $stdMap = ['FY' => 1, 'SY' => 2, 'TY' => 3];
                    $whereClauses[] = "(UPPER(e.academic_year) = :ay_filter OR e.standard_id = :std_filter)";
                    $params[':ay_filter'] = $selectedYearFilter;
                    $params[':std_filter'] = $stdMap[$selectedYearFilter];
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            $sql = "SELECT e.*, 
                           c.name as course_name, c.code as course_code,
                           d.name as department_name, d.code as department_code,
                           sub.name as subject_name, sub.code as subject_code,
                           s.first_name as student_fname, s.last_name as student_lname, s.roll_no as student_roll,
                           (SELECT COUNT(*) FROM online_exam_questions q WHERE q.exam_id = e.id) as question_count,
                           (SELECT SUM(marks) FROM online_exam_questions q WHERE q.exam_id = e.id) as total_marks,
                           (SELECT COUNT(*) FROM online_exam_submissions sub WHERE sub.exam_id = e.id) as submission_count
                    FROM online_exams e 
                    LEFT JOIN courses c ON e.course_id = c.id 
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN subjects sub ON e.subject_id = sub.id
                    LEFT JOIN students s ON e.student_id = s.id 
                    WHERE {$whereSql} 
                    ORDER BY e.id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $exams = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $exams = [];
        }

        // Fetch questions map for all exams
        $examQuestionsMap = [];
        if (!empty($exams)) {
            $examIds = array_column($exams, 'id');
            $inClause = implode(',', array_map('intval', $examIds));
            try {
                $qStmt = $db->query("SELECT * FROM online_exam_questions WHERE exam_id IN ({$inClause}) ORDER BY id ASC");
                $allQ = $qStmt->fetchAll();
                foreach ($allQ as $q) {
                    $examQuestionsMap[$q['exam_id']][] = $q;
                }
            } catch (\Throwable $e) {
                $examQuestionsMap = [];
            }
        }

        // Selected exam for managing questions
        $selectedExam = null;
        $questions = [];
        if ($selectedExamId > 0) {
            foreach ($exams as $ex) {
                if ($ex['id'] == $selectedExamId) {
                    $selectedExam = $ex;
                    break;
                }
            }
            $questions = $examQuestionsMap[$selectedExamId] ?? [];
        }

        // Fetch submissions log
        $submissions = [];
        $studentSubmissionsMap = [];
        try {
            if ($user['role_name'] === 'student') {
                $stRow = $db->prepare("SELECT id FROM students WHERE user_id = :uid LIMIT 1");
                $stRow->execute([':uid' => $user['id']]);
                $stId = (int) ($stRow->fetchColumn() ?: 1);

                $subStmt = $db->prepare("SELECT sub.*, e.title as exam_title 
                                         FROM online_exam_submissions sub 
                                         JOIN online_exams e ON sub.exam_id = e.id 
                                         WHERE sub.tenant_id = :tid AND sub.student_id = :sid 
                                         ORDER BY sub.id DESC");
                $subStmt->execute([':tid' => $tenantId, ':sid' => $stId]);
                $submissions = $subStmt->fetchAll();
                foreach ($submissions as $sub) {
                    $studentSubmissionsMap[$sub['exam_id']] = $sub;
                }
            } else {
                $subStmt = $db->prepare("SELECT sub.*, e.title as exam_title, s.first_name, s.last_name, s.roll_no 
                                         FROM online_exam_submissions sub 
                                         JOIN online_exams e ON sub.exam_id = e.id 
                                         JOIN students s ON sub.student_id = s.id 
                                         WHERE sub.tenant_id = :tid 
                                         ORDER BY sub.id DESC LIMIT 50");
                $subStmt->execute([':tid' => $tenantId]);
                $submissions = $subStmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $submissions = [];
        }

        require BASE_PATH . '/app/Views/modules/online_exam_view.php';
    }

    public function createExam(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        if ($user['role_name'] === 'student' || $user['role_name'] === 'parent') {
            Session::setFlash('error', 'Students and parents are not authorized to create exams.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();
        $this->ensureTablesExist($db);

        $title = Validator::sanitize($_POST['title'] ?? '');
        $deptId = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $courseId = !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null;
        $academicYear = !empty($_POST['academic_year']) ? strtoupper(Validator::sanitize($_POST['academic_year'])) : 'FY';
        if (!in_array($academicYear, ['FY', 'SY', 'TY'], true)) {
            $academicYear = 'FY';
        }
        $standardId = ($academicYear === 'FY') ? 1 : (($academicYear === 'SY') ? 2 : 3);
        $subjectId = !empty($_POST['subject_id']) ? (int) $_POST['subject_id'] : null;
        $studentId = !empty($_POST['student_id']) && $_POST['student_id'] !== 'all' ? (int) $_POST['student_id'] : null;
        $duration = !empty($_POST['duration_minutes']) ? (int) $_POST['duration_minutes'] : 15;
        $status = Validator::sanitize($_POST['status'] ?? 'published');

        if (!empty($title)) {
            $stmt = $db->prepare("
                INSERT INTO online_exams 
                (tenant_id, department_id, title, course_id, academic_year, standard_id, subject_id, student_id, duration_minutes, total_questions, status) 
                VALUES 
                (:tid, :did, :title, :cid, :ay, :stdid, :subid, :sid, :dur, 0, :status)
            ");
            $stmt->execute([
                ':tid' => $tenantId,
                ':did' => $deptId,
                ':title' => $title,
                ':cid' => $courseId,
                ':ay' => $academicYear,
                ':stdid' => $standardId,
                ':subid' => $subjectId,
                ':sid' => $studentId,
                ':dur' => $duration,
                ':status' => $status
            ]);
            $createdId = $db->lastInsertId();
            Session::setFlash('success', 'Online Exam (' . $academicYear . ') created successfully! You can now add MCQ questions below.');
            Response::redirect('index.php?page=module&name=online_exam&selected_exam_id=' . $createdId);
            return;
        }

        Session::setFlash('error', 'Exam Title is required.');
        Response::redirect('index.php?page=module&name=online_exam');
    }

    public function updateExam(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        if ($user['role_name'] === 'student' || $user['role_name'] === 'parent' || !Permission::canAction('online_exam', 'edit')) {
            Session::setFlash('error', 'You do not have permission to edit exams.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $examId = (int)($_POST['exam_id'] ?? 0);
        if ($examId <= 0) {
            Session::setFlash('error', 'Invalid exam ID.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();
        $this->ensureTablesExist($db);

        $title = Validator::sanitize($_POST['title'] ?? '');
        $deptId = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $courseId = !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null;
        $academicYear = !empty($_POST['academic_year']) ? strtoupper(Validator::sanitize($_POST['academic_year'])) : 'FY';
        if (!in_array($academicYear, ['FY', 'SY', 'TY'], true)) {
            $academicYear = 'FY';
        }
        $standardId = ($academicYear === 'FY') ? 1 : (($academicYear === 'SY') ? 2 : 3);
        $subjectId = !empty($_POST['subject_id']) ? (int) $_POST['subject_id'] : null;
        $studentId = !empty($_POST['student_id']) && $_POST['student_id'] !== 'all' ? (int) $_POST['student_id'] : null;
        $duration = !empty($_POST['duration_minutes']) ? (int) $_POST['duration_minutes'] : 15;
        $status = Validator::sanitize($_POST['status'] ?? 'published');

        if (empty($title)) {
            Session::setFlash('error', 'Exam Title is required.');
            Response::redirect('index.php?page=module&name=online_exam&selected_exam_id=' . $examId);
            return;
        }

        $stmt = $db->prepare("
            UPDATE online_exams 
            SET title = :title,
                department_id = :did,
                course_id = :cid,
                academic_year = :ay,
                standard_id = :stdid,
                subject_id = :subid,
                student_id = :sid,
                duration_minutes = :dur,
                status = :status
            WHERE id = :eid AND (tenant_id = :tid OR :is_super = 1)
        ");
        $stmt->execute([
            ':title' => $title,
            ':did' => $deptId,
            ':cid' => $courseId,
            ':ay' => $academicYear,
            ':stdid' => $standardId,
            ':subid' => $subjectId,
            ':sid' => $studentId,
            ':dur' => $duration,
            ':status' => $status,
            ':eid' => $examId,
            ':tid' => $tenantId,
            ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)
        ]);

        Session::setFlash('success', 'Exam configuration updated successfully!');
        Response::redirect('index.php?page=module&name=online_exam&selected_exam_id=' . $examId);
    }

    public function addMcqQuestion(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        if ($user['role_name'] === 'student') {
            Session::setFlash('error', 'Students are not authorized to add questions.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $examId = (int) ($_POST['exam_id'] ?? 0);
        $db = Database::getInstance();
        $addedCount = 0;

        // Process batch questions array if present
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $q) {
                $qText = trim($q['question_text'] ?? '');
                $optA = trim($q['option_a'] ?? '');
                $optB = trim($q['option_b'] ?? '');
                $optC = trim($q['option_c'] ?? '');
                $optD = trim($q['option_d'] ?? '');
                $correct = strtoupper(trim($q['correct_option'] ?? 'A'));
                $marks = !empty($q['marks']) ? (int) $q['marks'] : 1;

                if ($examId > 0 && !empty($qText) && !empty($optA) && !empty($optB)) {
                    $stmt = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) 
                                          VALUES (:eid, :q, :a, :b, :c, :d, :correct, :marks)");
                    $stmt->execute([
                        ':eid' => $examId,
                        ':q' => $qText,
                        ':a' => $optA,
                        ':b' => $optB,
                        ':c' => $optC,
                        ':d' => $optD,
                        ':correct' => $correct,
                        ':marks' => $marks
                    ]);
                    $addedCount++;
                }
            }
        }

        // Process single question fields if passed
        $questionText = trim($_POST['question_text'] ?? '');
        $optA = trim($_POST['option_a'] ?? '');
        $optB = trim($_POST['option_b'] ?? '');
        $optC = trim($_POST['option_c'] ?? '');
        $optD = trim($_POST['option_d'] ?? '');
        $correct = strtoupper(trim($_POST['correct_option'] ?? 'A'));
        $marks = !empty($_POST['marks']) ? (int) $_POST['marks'] : 1;

        if ($examId > 0 && !empty($questionText) && !empty($optA) && !empty($optB)) {
            $stmt = $db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) 
                                  VALUES (:eid, :q, :a, :b, :c, :d, :correct, :marks)");
            $stmt->execute([
                ':eid' => $examId,
                ':q' => $questionText,
                ':a' => $optA,
                ':b' => $optB,
                ':c' => $optC,
                ':d' => $optD,
                ':correct' => $correct,
                ':marks' => $marks
            ]);
            $addedCount++;
        }

        if ($addedCount > 0) {
            // Update total questions count in online_exams
            $qCount = (int) $db->query("SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = {$examId}")->fetchColumn();
            $upd = $db->prepare("UPDATE online_exams SET total_questions = :qc WHERE id = :eid");
            $upd->execute([':qc' => $qCount, ':eid' => $examId]);

            Session::setFlash('success', "🎉 {$addedCount} MCQ Question(s) added successfully!");
        } else {
            Session::setFlash('error', 'Please fill in question text and options.');
        }

        Response::redirect('index.php?page=module&name=online_exam&selected_exam_id=' . $examId);
    }

    public function deleteMcqQuestion(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        if ($user['role_name'] === 'student') {
            Session::setFlash('error', 'Students are not authorized to delete questions.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $qId = (int) ($_POST['question_id'] ?? 0);
        $examId = (int) ($_POST['exam_id'] ?? 0);

        if ($qId > 0) {
            $db = Database::getInstance();
            $del = $db->prepare("DELETE FROM online_exam_questions WHERE id = :qid");
            $del->execute([':qid' => $qId]);

            if ($examId > 0) {
                $qCount = (int) $db->query("SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = {$examId}")->fetchColumn();
                $upd = $db->prepare("UPDATE online_exams SET total_questions = :qc WHERE id = :eid");
                $upd->execute([':qc' => $qCount, ':eid' => $examId]);
            }
            Session::setFlash('success', 'MCQ question deleted.');
        }

        Response::redirect('index.php?page=module&name=online_exam&selected_exam_id=' . $examId);
    }

    public function deleteExam(): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (!Auth::check()) {
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '401 Unauthorized: Please log in to continue.'], 401);
                return;
            }
            Response::redirect('index.php?page=login');
            return;
        }

        $user = Auth::user();
        $role = $user['role_name'] ?? '';

        // Role-based authorization: Principal, Super Admin, School Admin, Teacher, and HOD can delete/manage exams.
        // Students and Parents are strictly forbidden.
        if ($role === 'student' || $role === 'parent' || !Permission::canAction('online_exam', 'delete')) {
            $msg = 'You do not have permission to delete this exam.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $msg], 403);
                return;
            }
            Session::setFlash('error', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $examId = (int) ($_POST['exam_id'] ?? 0);
        if ($examId <= 0) {
            $msg = 'Exam not found or already deleted.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $msg], 404);
                return;
            }
            Session::setFlash('error', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        $db = Database::getInstance();
        $this->ensureTablesExist($db);

        // Fetch exam record
        $stmt = $db->prepare("SELECT * FROM online_exams WHERE id = :eid");
        $stmt->execute([':eid' => $examId]);
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exam) {
            $msg = 'Exam not found or already deleted.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $msg], 404);
                return;
            }
            Session::setFlash('error', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        // Multi-tenant boundary check: Super admin has global tenant rights; Principal/Teacher restricted to their tenant
        $userTenantId = $user['tenant_id'] !== null ? (int)$user['tenant_id'] : null;
        $examTenantId = (int)$exam['tenant_id'];

        if ($role !== 'super_admin' && $userTenantId !== null && $userTenantId !== $examTenantId) {
            $msg = 'You do not have permission to delete this exam.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $msg], 403);
                return;
            }
            Session::setFlash('error', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        // Check if exam is actively in-progress by any student
        try {
            $inProgStmt = $db->prepare("SELECT COUNT(*) FROM online_exam_submissions WHERE exam_id = :eid AND (submitted_at IS NULL OR submission_status = 'in_progress')");
            $inProgStmt->execute([':eid' => $examId]);
            if ((int)$inProgStmt->fetchColumn() > 0) {
                $msg = 'Cannot delete an examination that is currently in progress.';
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $msg], 409);
                    return;
                }
                Session::setFlash('error', $msg);
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }
        } catch (\Throwable $e) {
            // Ignore in-progress check if column not present
        }

        // Check for student attempts and historical results
        $subStmt = $db->prepare("SELECT COUNT(*) FROM online_exam_submissions WHERE exam_id = :eid");
        $subStmt->execute([':eid' => $examId]);
        $submissionsCount = (int)$subStmt->fetchColumn();

        if ($submissionsCount > 0) {
            $allowArchive = !isset($_POST['archive_if_has_submissions']) || $_POST['archive_if_has_submissions'] === '1';
            if (!$allowArchive) {
                $msg = 'This exam cannot be permanently deleted because it has associated student records.';
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $msg], 409);
                    return;
                }
                Session::setFlash('error', $msg);
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            // Institutional Data Retention Compliance:
            // Safe Soft Delete: Archive the exam, preserving student results, questions, and scores
            $upd = $db->prepare("UPDATE online_exams SET status = 'archived' WHERE id = :eid");
            $upd->execute([':eid' => $examId]);

            $msg = 'Exam has been archived to preserve historical student attempts and results.';
            if ($isAjax) {
                Response::json(['success' => true, 'message' => $msg, 'action' => 'archived', 'exam_id' => $examId], 200);
                return;
            }
            Session::setFlash('success', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        // No student submissions: Safe permanent deletion inside database transaction
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM online_exam_questions WHERE exam_id = :eid")->execute([':eid' => $examId]);
            $del = $db->prepare("DELETE FROM online_exams WHERE id = :eid");
            $del->execute([':eid' => $examId]);
            $db->commit();

            $msg = 'Exam deleted successfully.';
            if ($isAjax) {
                Response::json(['success' => true, 'message' => $msg, 'action' => 'deleted', 'exam_id' => $examId], 200);
                return;
            }
            Session::setFlash('success', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("OnlineExamController deleteExam Exception: " . $e->getMessage());
            $msg = 'Unable to delete the exam. Please try again.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $msg], 500);
                return;
            }
            Session::setFlash('error', $msg);
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }
    }

    public function takeExam(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();
        $this->ensureTablesExist($db);

        $examId = (int) ($_GET['id'] ?? 0);

        // Fetch Exam
        $examStmt = $db->prepare("
            SELECT e.*, c.name as course_name, d.name as department_name, sub.name as subject_name 
            FROM online_exams e 
            LEFT JOIN courses c ON e.course_id = c.id 
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN subjects sub ON e.subject_id = sub.id
            WHERE e.id = :eid AND (e.tenant_id = :tid OR :is_super = 1)
        ");
        $examStmt->execute([':eid' => $examId, ':tid' => $tenantId, ':is_super' => ($user['role_name'] === 'super_admin' ? 1 : 0)]);
        $exam = $examStmt->fetch();

        if (!$exam) {
            Session::setFlash('error', 'Online Exam not found or access denied.');
            Response::redirect('index.php?page=module&name=online_exam');
            return;
        }

        // Identify Student ID & enforce strict student eligibility check
        $studentId = 1;
        if ($user['role_name'] === 'student') {
            if ($exam['status'] !== 'published') {
                Session::setFlash('error', 'This exam is currently not active or published.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            $stRow = $db->prepare("
                SELECT s.id, 
                       COALESCE(s.course_id, b.course_id) as course_id,
                       COALESCE(s.department_id, c.department_id) as department_id,
                       s.standard_id,
                       CASE 
                           WHEN s.standard_id = 1 OR cl.code IN ('FY', 'YR1') OR cl.name LIKE '%1st%' OR cl.name LIKE '%First%' OR s.admission_no LIKE '%-FY-%' OR s.roll_no LIKE 'FY%' THEN 'FY'
                           WHEN s.standard_id = 2 OR cl.code IN ('SY', 'YR2') OR cl.name LIKE '%2nd%' OR cl.name LIKE '%Second%' OR s.admission_no LIKE '%-SY-%' OR s.roll_no LIKE 'SY%' THEN 'SY'
                           WHEN s.standard_id = 3 OR cl.code IN ('TY', 'YR3') OR cl.name LIKE '%3rd%' OR cl.name LIKE '%Third%' OR s.admission_no LIKE '%-TY-%' OR s.roll_no LIKE 'TY%' THEN 'TY'
                           ELSE 'FY'
                       END as academic_year_code
                FROM students s 
                LEFT JOIN batches b ON s.batch_id = b.id 
                LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                WHERE s.user_id = :uid LIMIT 1
            ");
            $stRow->execute([':uid' => $user['id']]);
            $stData = $stRow->fetch();
            $studentId = (int) ($stData['id'] ?? 0);
            $stCourseId = (int) ($stData['course_id'] ?? 0);
            $stDeptId = (int) ($stData['department_id'] ?? 0);
            $stYear = $stData['academic_year_code'] ?? 'FY';

            // 1. Target Student Check: If specifically assigned to a single student
            if (!empty($exam['student_id']) && (int) $exam['student_id'] !== $studentId) {
                Session::setFlash('error', 'Access Denied: This exam is targeted for another specific student.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            // 2. Academic Year Eligibility Check
            if (!empty($exam['academic_year']) && strtoupper($exam['academic_year']) !== $stYear) {
                Session::setFlash('error', "Access Denied: This exam is designated for {$exam['academic_year']} students only. Your registered academic year is {$stYear}.");
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            // 3. Department Check
            if (!empty($exam['department_id']) && $stDeptId > 0 && (int) $exam['department_id'] !== $stDeptId) {
                Session::setFlash('error', 'Access Denied: This exam is assigned to another academic department.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            // 4. Course Check
            if (empty($exam['student_id']) && !empty($exam['course_id']) && $stCourseId > 0 && (int) $exam['course_id'] !== $stCourseId) {
                Session::setFlash('error', 'Access Denied: This exam is assigned to another course.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }
        }

        // Check if existing submission exists
        $subStmt = $db->prepare("SELECT * FROM online_exam_submissions WHERE exam_id = :eid AND student_id = :sid ORDER BY id DESC LIMIT 1");
        $subStmt->execute([':eid' => $examId, ':sid' => $studentId]);
        $existingSubmission = $subStmt->fetch();

        // Fetch Questions
        $qStmt = $db->prepare("SELECT * FROM online_exam_questions WHERE exam_id = :eid ORDER BY id ASC");
        $qStmt->execute([':eid' => $examId]);
        $questions = $qStmt->fetchAll();

        // If existing submission found, fetch submitted answers for review
        $submittedAnswers = [];
        if ($existingSubmission) {
            $ansStmt = $db->prepare("SELECT * FROM online_exam_answers WHERE submission_id = :sub_id");
            $ansStmt->execute([':sub_id' => $existingSubmission['id']]);
            $ansRows = $ansStmt->fetchAll();
            foreach ($ansRows as $ar) {
                $submittedAnswers[$ar['question_id']] = $ar;
            }
        }

        require BASE_PATH . '/app/Views/modules/take_exam_view.php';
    }

    public function submitExam(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
        }

        Permission::enforce('online_exam');

        $user = Auth::user();
        $tenantId = $user['tenant_id'] ?? 1;
        $db = Database::getInstance();
        $this->ensureTablesExist($db);

        $examId = (int) ($_POST['exam_id'] ?? 0);
        $submissionReason = Validator::sanitize($_POST['submission_reason'] ?? 'manual');
        $postedAnswers = $_POST['answers'] ?? [];

        // Determine submission status enum
        $submissionStatus = 'submitted';
        if ($submissionReason === 'auto_submitted_tab_switch') {
            $submissionStatus = 'auto_submitted_tab_switch';
        } elseif ($submissionReason === 'auto_submitted_back_button') {
            $submissionStatus = 'auto_submitted_back_button';
        } elseif ($submissionReason === 'auto_submitted_timeout') {
            $submissionStatus = 'auto_submitted_timeout';
        }

        // Identify Student ID & enforce student target check
        $studentId = 1;
        if ($user['role_name'] === 'student') {
            $examStmt = $db->prepare("SELECT * FROM online_exams WHERE id = :eid AND tenant_id = :tid");
            $examStmt->execute([':eid' => $examId, ':tid' => $tenantId]);
            $chkExam = $examStmt->fetch();

            if (!$chkExam || $chkExam['status'] !== 'published') {
                Session::setFlash('error', 'Exam submission failed: Exam not active.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            $stRow = $db->prepare("SELECT s.id, b.course_id FROM students s LEFT JOIN batches b ON s.batch_id = b.id WHERE s.user_id = :uid LIMIT 1");
            $stRow->execute([':uid' => $user['id']]);
            $stData = $stRow->fetch();
            $studentId = (int) ($stData['id'] ?? 0);
            $stCourseId = (int) ($stData['course_id'] ?? 0);

            if (!empty($chkExam['student_id']) && (int) $chkExam['student_id'] !== $studentId) {
                Session::setFlash('error', 'Access Denied: This exam is targeted for another specific student.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }

            if (empty($chkExam['student_id']) && !empty($chkExam['course_id']) && $stCourseId > 0 && (int) $chkExam['course_id'] !== $stCourseId) {
                Session::setFlash('error', 'Access Denied: This exam is assigned to another course.');
                Response::redirect('index.php?page=module&name=online_exam');
                return;
            }
        }

        // Verify existing submission
        $checkSub = $db->prepare("SELECT id FROM online_exam_submissions WHERE exam_id = :eid AND student_id = :sid LIMIT 1");
        $checkSub->execute([':eid' => $eid = $examId, ':sid' => $studentId]);
        if ($checkSub->fetchColumn()) {
            Session::setFlash('warning', 'Your exam has already been submitted.');
            Response::redirect('index.php?page=take_online_exam&id=' . $examId);
            return;
        }

        // Fetch Questions to evaluate
        $qStmt = $db->prepare("SELECT * FROM online_exam_questions WHERE exam_id = :eid ORDER BY id ASC");
        $qStmt->execute([':eid' => $examId]);
        $questions = $qStmt->fetchAll();

        $obtainedScore = 0;
        $totalPossibleScore = 0;
        $evaluations = [];

        foreach ($questions as $q) {
            $qId = $q['id'];
            $qMarks = (int) ($q['marks'] ?? 1);
            $totalPossibleScore += $qMarks;

            $userSelected = isset($postedAnswers[$qId]) ? strtoupper(trim($postedAnswers[$qId])) : null;
            $isCorrect = ($userSelected !== null && $userSelected === strtoupper($q['correct_option'])) ? 1 : 0;

            if ($isCorrect) {
                $obtainedScore += $qMarks;
            }

            $evaluations[] = [
                'question_id' => $qId,
                'selected_option' => $userSelected,
                'is_correct' => $isCorrect
            ];
        }

        // Save submission
        $insSub = $db->prepare("INSERT INTO online_exam_submissions 
                               (tenant_id, exam_id, student_id, score, total_score, submission_status, submission_reason, started_at, submitted_at) 
                               VALUES (:tid, :eid, :sid, :score, :total_score, :status, :reason, NOW(), NOW())");
        $insSub->execute([
            ':tid' => $tenantId,
            ':eid' => $examId,
            ':sid' => $studentId,
            ':score' => $obtainedScore,
            ':total_score' => $totalPossibleScore,
            ':status' => $submissionStatus,
            ':reason' => $submissionReason
        ]);
        $submissionId = $db->lastInsertId();

        // Save individual answers
        foreach ($evaluations as $eval) {
            $insAns = $db->prepare("INSERT INTO online_exam_answers (submission_id, question_id, selected_option, is_correct) 
                                   VALUES (:sub_id, :qid, :sel, :ic)");
            $insAns->execute([
                ':sub_id' => $submissionId,
                ':qid' => $eval['question_id'],
                ':sel' => $eval['selected_option'],
                ':ic' => $eval['is_correct']
            ]);
        }

        if ($submissionReason === 'auto_submitted_tab_switch') {
            Session::setFlash('warning', '⚠️ Tab switch detected! Your exam has been automatically submitted.');
        } elseif ($submissionReason === 'auto_submitted_back_button') {
            Session::setFlash('warning', '⚠️ Back button navigation detected! Your exam has been automatically submitted.');
        } elseif ($submissionReason === 'auto_submitted_timeout') {
            Session::setFlash('warning', '⏱️ Time limit expired! Your exam has been automatically submitted.');
        } else {
            Session::setFlash('success', '🎉 Online Exam submitted successfully!');
        }

        Response::redirect('index.php?page=take_online_exam&id=' . $examId);
    }
}
