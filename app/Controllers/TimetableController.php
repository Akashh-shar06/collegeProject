<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Permission;
use PDO;

class TimetableController
{
    /**
     * Check whether the user is an authorized administrator or has explicit timetable manage permission.
     */
    public static function canUserManageTimetable(?array $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if (!$user) {
            return false;
        }

        $role = $user['role_name'] ?? '';
        $adminRoles = ['super_admin', 'school_admin', 'principal', 'hod', 'administrator', 'erp_admin', 'teacher', 'faculty'];
        if (in_array($role, $adminRoles, true)) {
            return true;
        }

        if (!empty($user['can_manage_timetable']) || !empty($_SESSION['teacher_can_manage_timetable'])) {
            return true;
        }

        return false;
    }

    /**
     * Unified Timetable View: delegates to the Dark Navy Weekly Timetable matrix
     * keeping consistent UI/UX, filter support, and role-based scoping.
     */
    public function showTimetable(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
            return;
        }

        Permission::enforce('time_table');

        // Delegate directly to TeacherController::showWeeklyTimetable for unified dark navy experience
        (new TeacherController())->showWeeklyTimetable();
    }

    /**
     * Create a new lecture timetable slot with interval-overlap conflict validation.
     */
    public function createTimetable(): void
    {
        if (!Auth::check()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (isset($_POST['ajax']) && $_POST['ajax'] === '1');
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
                return;
            }
            Response::redirect('index.php?page=login');
            return;
        }

        $user = Auth::user();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

        if (!self::canUserManageTimetable($user)) {
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to create lectures.'], 403);
                return;
            }
            Session::setFlash('error', '403 Forbidden: You do not have permission to create lectures.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $deptId = (int)($_POST['department_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $standardId = (int)($_POST['standard_id'] ?? 0);
        $semesterId = (int)($_POST['semester_id'] ?? 0);
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $staffId = (int)($_POST['staff_id'] ?? 0);
        $classroomId = (int)($_POST['classroom_id'] ?? 0);
        $dayOfWeek = Validator::sanitize($_POST['day_of_week'] ?? 'Monday');
        $startTime = Validator::sanitize($_POST['start_time'] ?? '08:00:00');
        $endTime = Validator::sanitize($_POST['end_time'] ?? '09:00:00');
        $academicYear = Validator::sanitize($_POST['academic_year'] ?? '2026-2027');
        $lectureType = Validator::sanitize($_POST['lecture_type'] ?? 'theory');
        $notes = !empty($_POST['notes']) ? Validator::sanitize($_POST['notes']) : null;
        $redirectTo = !empty($_POST['redirect_to']) ? Validator::sanitize($_POST['redirect_to']) : 'index.php?page=teacher_timetable';

        // Normalize time strings to H:i:s
        if (strlen($startTime) === 5) $startTime .= ':00';
        if (strlen($endTime) === 5) $endTime .= ':00';

        // 1. Required field checks
        if (empty($dayOfWeek) || empty($startTime) || empty($endTime) || $subjectId <= 0 || $staffId <= 0) {
            $err = 'Validation Error: Please specify Day of Week, Start Time, End Time, Subject, and Assigned Teacher.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 2. Start vs End Time validation
        $startStamp = strtotime($startTime);
        $endStamp = strtotime($endTime);
        if ($endStamp <= $startStamp) {
            $err = 'Invalid Time Range: Lecture End Time must be later than Start Time.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 3. College schedule bounds validation (06:00 to 21:00)
        if ($startTime < '06:00:00' || $endTime > '21:00:00') {
            $err = 'Invalid Schedule: Lectures must be scheduled within college operating hours (06:00 AM - 09:00 PM).';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 4. Minimum duration validation (20 minutes)
        if (($endStamp - $startStamp) < 1200) {
            $err = 'Invalid Duration: Lecture duration must be at least 20 minutes.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // Auto-derive Section if not provided but Standard is given
        if ($sectionId <= 0 && $standardId > 0) {
            $secStmt = $db->prepare("SELECT id FROM sections WHERE standard_id = :sid AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
            $secStmt->execute([':sid' => $standardId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $sectionId = (int)($secStmt->fetchColumn() ?: 0);
        }

        // Auto-resolve Department ID if Course ID is provided
        if ($courseId > 0 && $deptId === 0) {
            $cStmt = $db->prepare("SELECT department_id FROM courses WHERE id = :cid");
            $cStmt->execute([':cid' => $courseId]);
            $deptId = (int)($cStmt->fetchColumn() ?: 0);
        }

        // Auto-resolve Course ID if Department ID is provided
        if ($deptId > 0 && $courseId === 0) {
            $cStmt = $db->prepare("SELECT id FROM courses WHERE department_id = :did LIMIT 1");
            $cStmt->execute([':did' => $deptId]);
            $courseId = (int)($cStmt->fetchColumn() ?: 0);
        }

        // Resolve subject name
        $subjectName = Validator::sanitize($_POST['subject_name'] ?? '');
        if (empty($subjectName) && $subjectId > 0) {
            $subStmt = $db->prepare("SELECT name FROM subjects WHERE id = :id");
            $subStmt->execute([':id' => $subjectId]);
            $subjectName = $subStmt->fetchColumn() ?: 'General Subject';
        }
        if (empty($subjectName)) {
            $subjectName = 'Lecture';
        }

        // Resolve classroom number
        $roomNo = Validator::sanitize($_POST['room_no'] ?? '');
        if (empty($roomNo) && $classroomId > 0) {
            $crStmt = $db->prepare("SELECT room_number FROM classrooms WHERE id = :id");
            $crStmt->execute([':id' => $classroomId]);
            $roomNo = $crStmt->fetchColumn() ?: 'Block 101';
        }
        if (empty($roomNo)) {
            $roomNo = 'Room 101';
        }

        // Human-readable time slot label
        $timeSlotFormatted = date("g:i A", $startStamp) . ' - ' . date("g:i A", $endStamp);
        if (!empty($_POST['time_slot'])) {
            $customSlot = Validator::sanitize($_POST['time_slot']);
            if (strlen($customSlot) > 2) {
                $timeSlotFormatted = $customSlot;
            }
        }

        // Batch ID fallback
        $batchId = 1;
        if ($courseId > 0) {
            $bStmt = $db->prepare("SELECT id FROM batches WHERE course_id = :cid LIMIT 1");
            $bStmt->execute([':cid' => $courseId]);
            $batchId = (int)($bStmt->fetchColumn() ?: 1);
        }

        // =========================================================================
        // INTERVAL OVERLAP CONFLICT DETECTION (True time span overlap checking)
        // Two intervals [st, et] and [t_start, t_end] overlap if:
        // t_start < et AND t_end > st
        // =========================================================================

        // 1. Teacher Interval-Overlap Conflict
        if ($staffId > 0) {
            $tCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, t.room_no,
                       st.first_name, st.last_name
                FROM time_tables t
                LEFT JOIN staff st ON t.staff_id = st.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.staff_id = :stfid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $tCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':stfid' => $staffId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $tConflict = $tCheck->fetch();
            if ($tConflict) {
                $cTeacher = trim(($tConflict['first_name'] ?? '') . ' ' . ($tConflict['last_name'] ?? ''));
                if (empty($cTeacher)) $cTeacher = 'Selected Faculty';
                $cStart = date('g:i A', strtotime($tConflict['start_time']));
                $cEnd = date('g:i A', strtotime($tConflict['end_time']));
                $err = "Teacher Conflict: Prof. {$cTeacher} is already scheduled for '{$tConflict['subject_name']}' on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        // 2. Classroom Interval-Overlap Conflict
        if (!empty($roomNo)) {
            $rCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, t.room_no,
                       st.first_name, st.last_name
                FROM time_tables t
                LEFT JOIN staff st ON t.staff_id = st.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.room_no = :rno
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $rCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':rno' => $roomNo,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $rConflict = $rCheck->fetch();
            if ($rConflict) {
                $cStart = date('g:i A', strtotime($rConflict['start_time']));
                $cEnd = date('g:i A', strtotime($rConflict['end_time']));
                $err = "Classroom Conflict: Room '{$roomNo}' is already occupied on {$dayOfWeek} from {$cStart} to {$cEnd} by '{$rConflict['subject_name']}'.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        // 3. Class / Division Conflict
        if ($sectionId > 0) {
            $secCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, sec.name as section_name
                FROM time_tables t
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.section_id = :secid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $secCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':secid' => $sectionId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $secConflict = $secCheck->fetch();
            if ($secConflict) {
                $cStart = date('g:i A', strtotime($secConflict['start_time']));
                $cEnd = date('g:i A', strtotime($secConflict['end_time']));
                $secName = $secConflict['section_name'] ?? 'Selected Division';
                $err = "Class Division Conflict: Class '{$secName}' already has '{$secConflict['subject_name']}' scheduled on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        } elseif ($courseId > 0 && $standardId > 0) {
            // Course Cohort Conflict
            $cohCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, c.name as course_name
                FROM time_tables t
                LEFT JOIN courses c ON t.course_id = c.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.course_id = :cid
                  AND t.standard_id = :stdid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $cohCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':cid' => $courseId,
                ':stdid' => $standardId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $cohConflict = $cohCheck->fetch();
            if ($cohConflict) {
                $cStart = date('g:i A', strtotime($cohConflict['start_time']));
                $cEnd = date('g:i A', strtotime($cohConflict['end_time']));
                $err = "Cohort Conflict: This degree class already has '{$cohConflict['subject_name']}' scheduled on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        $status = Validator::sanitize($_POST['status'] ?? 'active');
        $academicSessionId = isset($_POST['academic_session_id']) && (int)$_POST['academic_session_id'] > 0 ? (int)$_POST['academic_session_id'] : null;

        $stmt = $db->prepare("
            INSERT INTO time_tables 
            (tenant_id, academic_session_id, academic_year, department_id, standard_id, course_id, semester_id, section_id, batch_id, day_of_week, time_slot, start_time, end_time, subject_id, subject_name, staff_id, classroom_id, room_no, status, lecture_type, notes, created_by)
            VALUES 
            (:tid, :asid, :ay, :did, :stdid, :cid, :semid, :secid, :bid, :day, :ts, :st, :et, :subid, :subname, :stfid, :crid, :rno, :stt, :ltype, :notes, :cb)
        ");

        $stmt->execute([
            ':tid' => $tenantId,
            ':asid' => $academicSessionId,
            ':ay' => $academicYear,
            ':did' => $deptId > 0 ? $deptId : null,
            ':stdid' => $standardId > 0 ? $standardId : null,
            ':cid' => $courseId > 0 ? $courseId : null,
            ':semid' => $semesterId > 0 ? $semesterId : null,
            ':secid' => $sectionId > 0 ? $sectionId : null,
            ':bid' => $batchId,
            ':day' => $dayOfWeek,
            ':ts' => $timeSlotFormatted,
            ':st' => $startTime,
            ':et' => $endTime,
            ':subid' => $subjectId > 0 ? $subjectId : null,
            ':subname' => $subjectName,
            ':stfid' => $staffId > 0 ? $staffId : 1,
            ':crid' => $classroomId > 0 ? $classroomId : null,
            ':rno' => $roomNo,
            ':stt' => $status,
            ':ltype' => in_array($lectureType, ['theory', 'practical', 'tutorial'], true) ? $lectureType : 'theory',
            ':notes' => $notes,
            ':cb' => $user['id'],
        ]);

        $newId = (int)$db->lastInsertId();

        if ($isAjax) {
            Response::json([
                'success' => true,
                'message' => 'Lecture created successfully!',
                'id' => $newId
            ], 200);
            return;
        }

        Session::setFlash('success', 'Timetable entry created successfully!');
        Response::redirect($redirectTo);
    }

    /**
     * Update an existing lecture slot with full interval-overlap conflict verification.
     */
    public function updateTimetable(): void
    {
        if (!Auth::check()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (isset($_POST['ajax']) && $_POST['ajax'] === '1');
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
                return;
            }
            Response::redirect('index.php?page=login');
            return;
        }

        $user = Auth::user();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

        if (!self::canUserManageTimetable($user)) {
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to modify timetables.'], 403);
                return;
            }
            Session::setFlash('error', '403 Forbidden: You do not have permission to modify timetables.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $id = (int)($_POST['id'] ?? 0);
        $redirectTo = !empty($_POST['redirect_to']) ? Validator::sanitize($_POST['redirect_to']) : 'index.php?page=teacher_timetable';

        if ($id <= 0) {
            $err = 'Invalid timetable entry ID.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // Verify record existence and tenant boundary
        $checkStmt = $db->prepare("SELECT * FROM time_tables WHERE id = :id AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
        $checkStmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $existingEntry = $checkStmt->fetch();

        if (!$existingEntry) {
            $err = '404 Not Found: Timetable entry not found or unauthorized.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 404);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        $deptId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : (int)($existingEntry['department_id'] ?? 0);
        $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : (int)($existingEntry['course_id'] ?? 0);
        $standardId = isset($_POST['standard_id']) ? (int)$_POST['standard_id'] : (int)($existingEntry['standard_id'] ?? 0);
        $semesterId = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : (int)($existingEntry['semester_id'] ?? 0);
        $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : (int)($existingEntry['section_id'] ?? 0);
        $subjectId = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : (int)($existingEntry['subject_id'] ?? 0);
        $staffId = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : (int)($existingEntry['staff_id'] ?? 0);
        $classroomId = isset($_POST['classroom_id']) ? (int)$_POST['classroom_id'] : (int)($existingEntry['classroom_id'] ?? 0);
        $dayOfWeek = Validator::sanitize($_POST['day_of_week'] ?? ($existingEntry['day_of_week'] ?? 'Monday'));
        $startTime = Validator::sanitize($_POST['start_time'] ?? ($existingEntry['start_time'] ?? '08:00:00'));
        $endTime = Validator::sanitize($_POST['end_time'] ?? ($existingEntry['end_time'] ?? '09:00:00'));
        $academicYear = Validator::sanitize($_POST['academic_year'] ?? ($existingEntry['academic_year'] ?? '2026-2027'));
        $status = Validator::sanitize($_POST['status'] ?? ($existingEntry['status'] ?? 'active'));
        $lectureType = Validator::sanitize($_POST['lecture_type'] ?? ($existingEntry['lecture_type'] ?? 'theory'));
        $notes = isset($_POST['notes']) ? Validator::sanitize($_POST['notes']) : ($existingEntry['notes'] ?? null);

        // Normalize time strings to H:i:s
        if (strlen($startTime) === 5) $startTime .= ':00';
        if (strlen($endTime) === 5) $endTime .= ':00';

        // 1. Required fields
        if (empty($dayOfWeek) || empty($startTime) || empty($endTime) || $staffId <= 0) {
            $err = 'Validation Error: Please select Day, Start Time, End Time, and Assigned Teacher.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 2. Start vs End Time
        $startStamp = strtotime($startTime);
        $endStamp = strtotime($endTime);
        if ($endStamp <= $startStamp) {
            $err = 'Invalid Time Range: Lecture End Time must be later than Start Time.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 3. College Operating Hours (06:00 to 21:00)
        if ($startTime < '06:00:00' || $endTime > '21:00:00') {
            $err = 'Invalid Schedule: Lectures must be scheduled within college operating hours (06:00 AM - 09:00 PM).';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // 4. Minimum duration (20 minutes)
        if (($endStamp - $startStamp) < 1200) {
            $err = 'Invalid Duration: Lecture duration must be at least 20 minutes.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // Auto-derive Section if missing
        if ($sectionId <= 0 && $standardId > 0) {
            $secStmt = $db->prepare("SELECT id FROM sections WHERE standard_id = :sid AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
            $secStmt->execute([':sid' => $standardId, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
            $sectionId = (int)($secStmt->fetchColumn() ?: 0);
        }

        // Format time slot display
        $timeSlotFormatted = date("g:i A", $startStamp) . ' - ' . date("g:i A", $endStamp);
        if (!empty($_POST['time_slot'])) {
            $customSlot = Validator::sanitize($_POST['time_slot']);
            if (strlen($customSlot) > 2) {
                $timeSlotFormatted = $customSlot;
            }
        }

        // Classroom resolution
        $roomNo = Validator::sanitize($_POST['room_no'] ?? '');
        if (empty($roomNo) && $classroomId > 0) {
            $crStmt = $db->prepare("SELECT room_number FROM classrooms WHERE id = :id");
            $crStmt->execute([':id' => $classroomId]);
            $roomNo = $crStmt->fetchColumn() ?: 'Block 101';
        }
        if (empty($roomNo)) {
            $roomNo = $existingEntry['room_no'] ?? 'Room 101';
        }

        // =========================================================================
        // INTERVAL OVERLAP CONFLICT DETECTION (With self-exclusion: id != :id)
        // =========================================================================

        // 1. Teacher Interval-Overlap Conflict
        if ($staffId > 0) {
            $tCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, t.room_no,
                       st.first_name, st.last_name
                FROM time_tables t
                LEFT JOIN staff st ON t.staff_id = st.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.id != :id
                  AND t.staff_id = :stfid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $tCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':id' => $id,
                ':stfid' => $staffId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $tConflict = $tCheck->fetch();
            if ($tConflict) {
                $cTeacher = trim(($tConflict['first_name'] ?? '') . ' ' . ($tConflict['last_name'] ?? ''));
                if (empty($cTeacher)) $cTeacher = 'Selected Faculty';
                $cStart = date('g:i A', strtotime($tConflict['start_time']));
                $cEnd = date('g:i A', strtotime($tConflict['end_time']));
                $err = "Teacher Conflict: Prof. {$cTeacher} is already assigned to '{$tConflict['subject_name']}' on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        // 2. Classroom Interval-Overlap Conflict
        if (!empty($roomNo)) {
            $rCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, t.room_no,
                       st.first_name, st.last_name
                FROM time_tables t
                LEFT JOIN staff st ON t.staff_id = st.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.id != :id
                  AND t.room_no = :rno
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $rCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':id' => $id,
                ':rno' => $roomNo,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $rConflict = $rCheck->fetch();
            if ($rConflict) {
                $cStart = date('g:i A', strtotime($rConflict['start_time']));
                $cEnd = date('g:i A', strtotime($rConflict['end_time']));
                $err = "Classroom Conflict: Classroom '{$roomNo}' is already occupied on {$dayOfWeek} from {$cStart} to {$cEnd} by '{$rConflict['subject_name']}'.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        // 3. Class / Division Conflict
        if ($sectionId > 0) {
            $secCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, sec.name as section_name
                FROM time_tables t
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.id != :id
                  AND t.section_id = :secid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $secCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':id' => $id,
                ':secid' => $sectionId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $secConflict = $secCheck->fetch();
            if ($secConflict) {
                $cStart = date('g:i A', strtotime($secConflict['start_time']));
                $cEnd = date('g:i A', strtotime($secConflict['end_time']));
                $secName = $secConflict['section_name'] ?? 'Selected Division';
                $err = "Class Division Conflict: Class '{$secName}' already has another lecture scheduled on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        } elseif ($courseId > 0 && $standardId > 0) {
            // Cohort Conflict
            $cohCheck = $db->prepare("
                SELECT t.id, t.subject_name, t.start_time, t.end_time, c.name as course_name
                FROM time_tables t
                LEFT JOIN courses c ON t.course_id = c.id
                WHERE (t.tenant_id = :tid OR :is_super = 1)
                  AND t.id != :id
                  AND t.course_id = :cid
                  AND t.standard_id = :stdid
                  AND t.day_of_week = :day
                  AND (t.status = 'active' OR t.status IS NULL)
                  AND ((t.start_time < :et AND t.end_time > :st) OR (t.time_slot = :ts AND (t.start_time IS NULL OR t.end_time IS NULL)))
                LIMIT 1
            ");
            $cohCheck->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':id' => $id,
                ':cid' => $courseId,
                ':stdid' => $standardId,
                ':day' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':ts' => $timeSlotFormatted
            ]);
            $cohConflict = $cohCheck->fetch();
            if ($cohConflict) {
                $cStart = date('g:i A', strtotime($cohConflict['start_time']));
                $cEnd = date('g:i A', strtotime($cohConflict['end_time']));
                $err = "Cohort Conflict: This degree class already has another lecture scheduled on {$dayOfWeek} from {$cStart} to {$cEnd}.";
                if ($isAjax) {
                    Response::json(['success' => false, 'error' => $err], 409);
                    return;
                }
                Session::setFlash('error', $err);
                Response::redirect($redirectTo);
                return;
            }
        }

        // Subject name update
        $subjectName = Validator::sanitize($_POST['subject_name'] ?? '');
        if (empty($subjectName) && $subjectId > 0) {
            $subStmt = $db->prepare("SELECT name FROM subjects WHERE id = :id");
            $subStmt->execute([':id' => $subjectId]);
            $subjectName = $subStmt->fetchColumn() ?: ($existingEntry['subject_name'] ?? 'Subject');
        }
        if (empty($subjectName)) {
            $subjectName = $existingEntry['subject_name'] ?? 'Subject';
        }

        $stmt = $db->prepare("
            UPDATE time_tables 
            SET academic_year = :ay,
                department_id = :did,
                standard_id = :stdid,
                course_id = :cid,
                semester_id = :semid,
                section_id = :secid,
                day_of_week = :day,
                time_slot = :ts,
                start_time = :st,
                end_time = :et,
                subject_id = :subid,
                subject_name = :subname,
                staff_id = :stfid,
                classroom_id = :crid,
                room_no = :rno,
                status = :stt,
                lecture_type = :ltype,
                notes = :notes
            WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)
        ");

        $stmt->execute([
            ':ay' => $academicYear,
            ':did' => $deptId > 0 ? $deptId : null,
            ':stdid' => $standardId > 0 ? $standardId : null,
            ':cid' => $courseId > 0 ? $courseId : null,
            ':semid' => $semesterId > 0 ? $semesterId : null,
            ':secid' => $sectionId > 0 ? $sectionId : null,
            ':day' => $dayOfWeek,
            ':ts' => $timeSlotFormatted,
            ':st' => $startTime,
            ':et' => $endTime,
            ':subid' => $subjectId > 0 ? $subjectId : null,
            ':subname' => $subjectName,
            ':stfid' => $staffId > 0 ? $staffId : 1,
            ':crid' => $classroomId > 0 ? $classroomId : null,
            ':rno' => $roomNo,
            ':stt' => $status,
            ':ltype' => in_array($lectureType, ['theory', 'practical', 'tutorial'], true) ? $lectureType : 'theory',
            ':notes' => $notes,
            ':id' => $id,
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
        ]);

        if ($isAjax) {
            Response::json([
                'success' => true,
                'message' => 'Lecture updated successfully!'
            ], 200);
            return;
        }

        Session::setFlash('success', 'Timetable entry updated successfully!');
        Response::redirect($redirectTo);
    }

    /**
     * Delete an existing timetable lecture slot.
     */
    public function deleteTimetable(): void
    {
        if (!Auth::check()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (isset($_POST['ajax']) && $_POST['ajax'] === '1');
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
                return;
            }
            Response::redirect('index.php?page=login');
            return;
        }

        $user = Auth::user();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

        if (!self::canUserManageTimetable($user)) {
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to delete timetables.'], 403);
                return;
            }
            Session::setFlash('error', '403 Forbidden: You do not have permission to delete timetables.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::getInstance();
        $redirectTo = !empty($_POST['redirect_to']) ? Validator::sanitize($_POST['redirect_to']) : 'index.php?page=teacher_timetable';

        if ($id <= 0) {
            $err = 'Invalid timetable entry ID.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 400);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        // Verify record ownership before deletion
        $checkStmt = $db->prepare("SELECT id FROM time_tables WHERE id = :id AND (tenant_id = :tid OR :is_super = 1) LIMIT 1");
        $checkStmt->execute([':id' => $id, ':tid' => $user['tenant_id'], ':is_super' => (($user['role_name'] ?? '') === 'super_admin' ? 1 : 0)]);
        if (!$checkStmt->fetchColumn()) {
            $err = '404 Not Found: Timetable slot not found or unauthorized.';
            if ($isAjax) {
                Response::json(['success' => false, 'error' => $err], 404);
                return;
            }
            Session::setFlash('error', $err);
            Response::redirect($redirectTo);
            return;
        }

        $stmt = $db->prepare("DELETE FROM time_tables WHERE id = :id AND (tenant_id = :tid OR :is_super = 1)");
        $stmt->execute([
            ':id' => $id,
            ':tid' => $user['tenant_id'],
            ':is_super' => (($user['role_name'] ?? '') === 'super_admin' ? 1 : 0),
        ]);

        if ($isAjax) {
            Response::json(['success' => true, 'message' => 'Lecture deleted successfully.'], 200);
            return;
        }

        Session::setFlash('success', 'Timetable entry deleted successfully.');
        Response::redirect($redirectTo);
    }

    /**
     * Update an entire time slot row: updates label and timings in timetable_periods
     * and synchronizes start_time, end_time, and time_slot on all scheduled lectures in this slot.
     */
    public function updateTimeSlotRow(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
            return;
        }

        $user = Auth::user();
        if (!self::canUserManageTimetable($user)) {
            Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to edit timetable time slots.'], 403);
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $oldStartTime = trim(Validator::sanitize($_POST['old_start_time'] ?? ''));
        $oldEndTime = trim(Validator::sanitize($_POST['old_end_time'] ?? ''));
        $periodLabel = trim(Validator::sanitize($_POST['period_label'] ?? ''));
        $newStartTime = trim(Validator::sanitize($_POST['start_time'] ?? ''));
        $newEndTime = trim(Validator::sanitize($_POST['end_time'] ?? ''));
        $periodKey = trim(Validator::sanitize($_POST['period_key'] ?? ''));

        // Normalize time strings to H:i:s
        if (strlen($oldStartTime) === 5) $oldStartTime .= ':00';
        if (strlen($oldEndTime) === 5) $oldEndTime .= ':00';
        if (strlen($newStartTime) === 5) $newStartTime .= ':00';
        if (strlen($newEndTime) === 5) $newEndTime .= ':00';

        if (empty($newStartTime) || empty($newEndTime)) {
            Response::json(['success' => false, 'error' => 'Start time and end time are required.'], 400);
            return;
        }

        if ($newStartTime >= $newEndTime) {
            Response::json(['success' => false, 'error' => 'End time must be later than start time.'], 400);
            return;
        }

        if (empty($periodLabel)) {
            $periodLabel = 'Time Slot';
        }

        $newSlotLabel = date('g:i A', strtotime($newStartTime)) . ' - ' . date('g:i A', strtotime($newEndTime));

        // 1. Update or Insert in timetable_periods
        $checkStmt = $db->prepare("SELECT id FROM timetable_periods WHERE (tenant_id = :tid OR :is_super = 1) AND ((start_time = :ost AND end_time = :oet) OR period_key = :pkey) LIMIT 1");
        $checkStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':ost' => $oldStartTime,
            ':oet' => $oldEndTime,
            ':pkey' => $periodKey,
        ]);
        $periodId = $checkStmt->fetchColumn();

        if ($periodId) {
            $updPeriod = $db->prepare("
                UPDATE timetable_periods 
                SET label = :lbl, start_time = :nst, end_time = :net, status = 'active', updated_at = NOW()
                WHERE id = :id
            ");
            $updPeriod->execute([
                ':lbl' => $periodLabel,
                ':nst' => $newStartTime,
                ':net' => $newEndTime,
                ':id' => $periodId,
            ]);
        } else {
            $insPeriod = $db->prepare("
                INSERT INTO timetable_periods (tenant_id, period_key, period_number, label, start_time, end_time, status)
                VALUES (:tid, :pkey, 1, :lbl, :nst, :net, 'active')
            ");
            $insPeriod->execute([
                ':tid' => $tenantId,
                ':pkey' => $periodKey ?: ('slot_' . substr($newStartTime, 0, 2) . substr($newStartTime, 3, 2)),
                ':lbl' => $periodLabel,
                ':nst' => $newStartTime,
                ':net' => $newEndTime,
            ]);
        }

        // 2. Synchronize all lectures in time_tables currently in old slot
        $affectedLectures = 0;
        if (!empty($oldStartTime) && !empty($oldEndTime)) {
            $updTT = $db->prepare("
                UPDATE time_tables 
                SET start_time = :nst, end_time = :net, time_slot = :nslot, updated_at = NOW()
                WHERE (tenant_id = :tid OR :is_super = 1)
                  AND start_time = :ost AND end_time = :oet
            ");
            $updTT->execute([
                ':nst' => $newStartTime,
                ':net' => $newEndTime,
                ':nslot' => $newSlotLabel,
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':ost' => $oldStartTime,
                ':oet' => $oldEndTime,
            ]);
            $affectedLectures = $updTT->rowCount();
        }

        Response::json([
            'success' => true,
            'message' => "Time slot row '{$periodLabel}' updated successfully (" . $affectedLectures . " lecture(s) synchronized).",
            'affected_lectures' => $affectedLectures,
            'new_start_time' => $newStartTime,
            'new_end_time' => $newEndTime,
            'new_label' => $periodLabel
        ], 200);
    }

    /**
     * Delete an entire time slot row: deletes all lectures scheduled in this time slot
     * and marks/removes the period row so the entire row disappears from the timetable.
     */
    public function deleteTimeSlotRow(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
            return;
        }

        $user = Auth::user();
        if (!self::canUserManageTimetable($user)) {
            Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to delete timetable rows.'], 403);
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $startTime = trim(Validator::sanitize($_POST['start_time'] ?? ''));
        $endTime = trim(Validator::sanitize($_POST['end_time'] ?? ''));
        $periodKey = trim(Validator::sanitize($_POST['period_key'] ?? ''));

        if (strlen($startTime) === 5) $startTime .= ':00';
        if (strlen($endTime) === 5) $endTime .= ':00';

        if (empty($startTime) || empty($endTime)) {
            Response::json(['success' => false, 'error' => 'Invalid time slot parameters.'], 400);
            return;
        }

        // 1. Find all lecture IDs in this time slot
        $findStmt = $db->prepare("
            SELECT id FROM time_tables 
            WHERE (tenant_id = :tid OR :is_super = 1) 
              AND start_time = :st AND end_time = :et
        ");
        $findStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':st' => $startTime,
            ':et' => $endTime,
        ]);
        $lectureIds = $findStmt->fetchAll(PDO::FETCH_COLUMN);
        $deletedLectureCount = count($lectureIds);

        // Clean up linked attendance sessions and records if any
        if (!empty($lectureIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($lectureIds), '?'));
            
            try {
                $db->prepare("DELETE FROM attendance_records WHERE time_table_id IN ($inPlaceholders)")->execute($lectureIds);
            } catch (\Exception $e) {}

            try {
                $db->prepare("DELETE FROM attendance_sessions WHERE time_table_id IN ($inPlaceholders)")->execute($lectureIds);
            } catch (\Exception $e) {}

            try {
                $db->prepare("DELETE FROM lesson_records WHERE time_table_id IN ($inPlaceholders)")->execute($lectureIds);
            } catch (\Exception $e) {}

            try {
                $db->prepare("DELETE FROM classroom_remarks WHERE time_table_id IN ($inPlaceholders)")->execute($lectureIds);
            } catch (\Exception $e) {}

            // 2. Delete all matching lectures from time_tables
            $delTT = $db->prepare("
                DELETE FROM time_tables 
                WHERE (tenant_id = :tid OR :is_super = 1) 
                  AND start_time = :st AND end_time = :et
            ");
            $delTT->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':st' => $startTime,
                ':et' => $endTime,
            ]);
        }

        // 3. Mark or delete in timetable_periods so row is completely removed
        $delPeriod = $db->prepare("
            UPDATE timetable_periods 
            SET status = 'deleted', updated_at = NOW() 
            WHERE (tenant_id = :tid OR :is_super = 1) 
              AND ((start_time = :st AND end_time = :et) OR period_key = :pkey)
        ");
        $delPeriod->execute([
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':st' => $startTime,
            ':et' => $endTime,
            ':pkey' => $periodKey,
        ]);

        Response::json([
            'success' => true,
            'message' => "Time slot row and all scheduled lectures ({$deletedLectureCount} lecture(s)) deleted successfully.",
            'deleted_count' => $deletedLectureCount,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'period_key' => $periodKey,
        ], 200);
    }

    /**
     * Add a new time slot row into timetable_periods
     */
    public function addTimeSlotRow(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'error' => '401 Unauthorized: Please sign in.'], 401);
            return;
        }

        $user = Auth::user();
        if (!self::canUserManageTimetable($user)) {
            Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to add timetable time slots.'], 403);
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $periodLabel = trim(Validator::sanitize($_POST['period_label'] ?? ''));
        $startTime = trim(Validator::sanitize($_POST['start_time'] ?? ''));
        $endTime = trim(Validator::sanitize($_POST['end_time'] ?? ''));

        if (strlen($startTime) === 5) $startTime .= ':00';
        if (strlen($endTime) === 5) $endTime .= ':00';

        if (empty($startTime) || empty($endTime)) {
            Response::json(['success' => false, 'error' => 'Start time and end time are required.'], 400);
            return;
        }

        if ($startTime >= $endTime) {
            Response::json(['success' => false, 'error' => 'End time must be later than start time.'], 400);
            return;
        }

        if (empty($periodLabel)) {
            $periodLabel = 'New Time Slot';
        }

        $slotKey = 'slot_' . substr($startTime, 0, 2) . substr($startTime, 3, 2);

        // Check if already exists
        $checkStmt = $db->prepare("SELECT id FROM timetable_periods WHERE (tenant_id = :tid OR :is_super = 1) AND start_time = :st AND end_time = :et AND status = 'active' LIMIT 1");
        $checkStmt->execute([
            ':tid' => $tenantId,
            ':is_super' => ($isSuperAdmin ? 1 : 0),
            ':st' => $startTime,
            ':et' => $endTime,
        ]);
        if ($checkStmt->fetchColumn()) {
            Response::json(['success' => false, 'error' => 'A time slot row with these exact timings already exists.'], 400);
            return;
        }

        // Count existing periods to determine number
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM timetable_periods WHERE (tenant_id = :tid OR :is_super = 1) AND status = 'active'");
        $cntStmt->execute([':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $pNum = (int)$cntStmt->fetchColumn() + 1;

        $ins = $db->prepare("
            INSERT INTO timetable_periods (tenant_id, period_key, period_number, label, start_time, end_time, status)
            VALUES (:tid, :pkey, :pnum, :lbl, :st, :et, 'active')
            ON DUPLICATE KEY UPDATE label = VALUES(label), status = 'active', updated_at = NOW()
        ");
        $ins->execute([
            ':tid' => $tenantId,
            ':pkey' => $slotKey,
            ':pnum' => $pNum,
            ':lbl' => $periodLabel,
            ':st' => $startTime,
            ':et' => $endTime,
        ]);

        Response::json([
            'success' => true,
            'message' => "New time slot row '{$periodLabel}' added successfully.",
            'period_key' => $slotKey,
            'label' => $periodLabel,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ], 200);
    }

    /**
     * Copy timetable entries across weekdays with conflict validation.
     */
    public function copyTimetable(): void
    {
        if (!Auth::check()) {
            Response::redirect('index.php?page=login');
            return;
        }

        $user = Auth::user();
        if (!self::canUserManageTimetable($user)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            if ($isAjax) {
                Response::json(['success' => false, 'error' => '403 Forbidden: You do not have permission to copy timetables.'], 403);
                return;
            }
            Session::setFlash('error', '403 Forbidden: You do not have permission to copy timetables.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $sourceDay = Validator::sanitize($_POST['source_day'] ?? '');
        $targetDay = Validator::sanitize($_POST['target_day'] ?? '');
        $sectionId = (int)($_POST['section_id'] ?? 0);

        if (empty($sourceDay) || empty($targetDay) || $sourceDay === $targetDay) {
            Session::setFlash('error', 'Please choose different source and target weekdays.');
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $srcSql = "SELECT * FROM time_tables WHERE day_of_week = :sd AND (tenant_id = :tid OR :is_super = 1)";
        $params = [':sd' => $sourceDay, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
        if ($sectionId > 0) {
            $srcSql .= " AND section_id = :secid";
            $params[':secid'] = $sectionId;
        }

        $stmt = $db->prepare($srcSql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        if (empty($entries)) {
            Session::setFlash('error', "No timetable entries found on {$sourceDay} to duplicate.");
            Response::redirect('index.php?page=teacher_timetable');
            return;
        }

        $copiedCount = 0;
        $conflictCount = 0;

        foreach ($entries as $e) {
            $check = $db->prepare("
                SELECT id FROM time_tables 
                WHERE (tenant_id = :tid OR :is_super = 1)
                  AND day_of_week = :td 
                  AND (staff_id = :stfid OR room_no = :rno OR section_id = :secid)
                  AND ((start_time < :et AND end_time > :st) OR time_slot = :ts)
                LIMIT 1
            ");
            $check->execute([
                ':tid' => $tenantId,
                ':is_super' => ($isSuperAdmin ? 1 : 0),
                ':td' => $targetDay,
                ':stfid' => $e['staff_id'],
                ':rno' => $e['room_no'],
                ':secid' => $e['section_id'],
                ':st' => $e['start_time'],
                ':et' => $e['end_time'],
                ':ts' => $e['time_slot']
            ]);

            if ($check->fetchColumn()) {
                $conflictCount++;
                continue;
            }

            $ins = $db->prepare("
                INSERT INTO time_tables 
                (tenant_id, academic_year, department_id, standard_id, course_id, semester_id, section_id, batch_id, day_of_week, time_slot, start_time, end_time, subject_id, subject_name, staff_id, classroom_id, room_no, status, lecture_type, notes, created_by)
                VALUES 
                (:tid, :ay, :did, :stdid, :cid, :semid, :secid, :bid, :day, :ts, :st, :et, :subid, :subname, :stfid, :crid, :rno, 'active', :ltype, :notes, :cb)
            ");
            $ins->execute([
                ':tid' => $tenantId,
                ':ay' => $e['academic_year'],
                ':did' => $e['department_id'],
                ':stdid' => $e['standard_id'],
                ':cid' => $e['course_id'],
                ':semid' => $e['semester_id'],
                ':secid' => $e['section_id'],
                ':bid' => $e['batch_id'],
                ':day' => $targetDay,
                ':ts' => $e['time_slot'],
                ':st' => $e['start_time'],
                ':et' => $e['end_time'],
                ':subid' => $e['subject_id'],
                ':subname' => $e['subject_name'],
                ':stfid' => $e['staff_id'],
                ':crid' => $e['classroom_id'],
                ':rno' => $e['room_no'],
                ':ltype' => $e['lecture_type'] ?? 'theory',
                ':notes' => $e['notes'] ?? null,
                ':cb' => $user['id']
            ]);
            $copiedCount++;
        }

        $msg = "Duplication complete: {$copiedCount} entries copied to {$targetDay}.";
        if ($conflictCount > 0) {
            $msg .= " ({$conflictCount} slots skipped due to scheduling conflicts).";
        }
        Session::setFlash('success', $msg);
        Response::redirect('index.php?page=teacher_timetable');
    }

    /**
     * Secure API endpoint to fetch a single timetable entry.
     * Enforces authentication (401) and student/teacher scoping authorization (403).
     */
    public function getTimetableEntryJson(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => '401 Unauthorized: Authentication required.'], 401);
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Response::json(['error' => 'Invalid timetable entry ID.'], 400);
            return;
        }

        $user = Auth::user();
        $db = Database::getInstance();
        $role = $user['role_name'] ?? 'student';
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = ($role === 'super_admin');
        $isAdminRole = in_array($role, ['super_admin', 'school_admin', 'principal', 'hod', 'administrator', 'erp_admin'], true);

        $stmt = $db->prepare("
            SELECT t.*, 
                   d.name as department_name, d.code as department_code,
                   c.name as course_name, c.code as course_code,
                   sem.name as semester_name,
                   sec.name as section_name,
                   sub.name as resolved_subject_name, sub.code as subject_code, sub.type as subject_type,
                   st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.emp_id as teacher_emp_id,
                   cr.room_number as classroom_number
            FROM time_tables t
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN courses c ON t.course_id = c.id
            LEFT JOIN semesters sem ON t.semester_id = sem.id
            LEFT JOIN sections sec ON t.section_id = sec.id
            LEFT JOIN subjects sub ON t.subject_id = sub.id
            LEFT JOIN staff st ON t.staff_id = st.id
            LEFT JOIN classrooms cr ON t.classroom_id = cr.id
            WHERE t.id = :id AND (t.tenant_id = :tid OR :is_super = 1)
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)]);
        $entry = $stmt->fetch();

        if (!$entry) {
            Response::json(['error' => '404 Not Found: Timetable entry not found.'], 404);
            return;
        }

        // Student Access Scoping: Students may only view lectures for their assigned course and academic year
        if ($role === 'student') {
            $stStmt = $db->prepare("
                SELECT s.*, 
                       COALESCE(s.course_id, b.course_id) as course_id
                FROM students s 
                LEFT JOIN batches b ON s.batch_id = b.id 
                WHERE s.user_id = :uid LIMIT 1
            ");
            $stStmt->execute([':uid' => $user['id']]);
            $studentScope = $stStmt->fetch();

            if (!$studentScope) {
                Response::json(['error' => '403 Forbidden: Student profile not found.'], 403);
                return;
            }

            // Verify course matches
            if (!empty($entry['course_id']) && (int)$entry['course_id'] !== (int)$studentScope['course_id']) {
                Response::json(['error' => '403 Forbidden: You are not authorized to view timetable entries for another course.'], 403);
                return;
            }

            // Verify standard matches
            if (!empty($entry['standard_id']) && !empty($studentScope['standard_id']) && (int)$entry['standard_id'] !== (int)$studentScope['standard_id']) {
                Response::json(['error' => '403 Forbidden: You are not authorized to view timetable entries for another academic year.'], 403);
                return;
            }
        } elseif ($role === 'teacher' && !$isAdminRole) {
            // Teacher scoping: verify staff record
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $user['id']]);
            $myStaffId = $stfStmt->fetchColumn();

            $canManage = !empty($user['can_manage_timetable']) || !empty($_SESSION['teacher_can_manage_timetable']);
            if (!$canManage && $myStaffId && (int)$entry['staff_id'] !== (int)$myStaffId) {
                Response::json(['error' => '403 Forbidden: You are only authorized to view your own assigned lectures.'], 403);
                return;
            }
        }

        // Calculate duration in minutes for UI ease
        $st = strtotime($entry['start_time']);
        $et = strtotime($entry['end_time']);
        $entry['duration_minutes'] = ($st && $et && $et > $st) ? (int)round(($et - $st) / 60) : 60;

        Response::json(['success' => true, 'entry' => $entry]);
    }

    /**
     * Dynamic dropdown options helper for form selects.
     */
    public function getDependentOptions(): void
    {
        if (!Auth::check()) {
            Response::json(['error' => '401 Unauthorized.'], 401);
            return;
        }

        $type = Validator::sanitize($_GET['type'] ?? '');
        $user = Auth::user();
        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;
        $isSuperAdmin = (($user['role_name'] ?? '') === 'super_admin');

        $data = [];

        if ($type === 'courses') {
            $deptId = (int)($_GET['department_id'] ?? 0);
            $sql = "SELECT id, name, code FROM courses WHERE (tenant_id = :tid OR :is_super = 1)";
            $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
            if ($deptId > 0) {
                $sql .= " AND department_id = :did";
                $params[':did'] = $deptId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
        } elseif ($type === 'sections') {
            $deptId = (int)($_GET['department_id'] ?? 0);
            $sql = "SELECT id, name FROM sections WHERE (tenant_id = :tid OR :is_super = 1)";
            $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
            if ($deptId > 0) {
                $sql .= " AND (department_id = :did OR department_id IS NULL)";
                $params[':did'] = $deptId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
        } elseif ($type === 'subjects') {
            $courseId = (int)($_GET['course_id'] ?? 0);
            $deptId = (int)($_GET['department_id'] ?? 0);
            $sql = "SELECT id, name, code, type FROM subjects WHERE (tenant_id = :tid OR :is_super = 1)";
            $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
            if ($deptId > 0) {
                $sql .= " AND (department_id = :did OR department_id IS NULL)";
                $params[':did'] = $deptId;
            }
            if ($courseId > 0) {
                $sql .= " AND (course_id = :cid OR course_id IS NULL)";
                $params[':cid'] = $courseId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
        } elseif ($type === 'teachers') {
            $deptId = (int)($_GET['department_id'] ?? 0);
            $sql = "SELECT id, first_name, last_name, emp_id FROM staff WHERE (tenant_id = :tid OR :is_super = 1) AND (LOWER(status) = 'active' OR status IS NULL)";
            $params = [':tid' => $tenantId, ':is_super' => ($isSuperAdmin ? 1 : 0)];
            if ($deptId > 0) {
                $sql .= " AND (department_id = :did OR department_id IS NULL)";
                $params[':did'] = $deptId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
        }

        Response::json(['success' => true, 'data' => $data]);
    }
}
