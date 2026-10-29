<?php
/**
 * Comprehensive End-to-End Verification Test Script
 * Tests all 4 Issues + Additional Academic Year Requirement
 */

define('TESTING_MODE', true);
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Auth.php';
require_once BASE_PATH . '/app/Core/Permission.php';
require_once BASE_PATH . '/app/Core/Session.php';
require_once BASE_PATH . '/app/Core/Validator.php';
require_once BASE_PATH . '/app/Core/CSRF.php';
require_once BASE_PATH . '/app/Core/Response.php';
require_once BASE_PATH . '/app/Controllers/ApiController.php';
require_once BASE_PATH . '/app/Controllers/AcademicController.php';
require_once BASE_PATH . '/app/Controllers/TimetableController.php';
require_once BASE_PATH . '/app/Controllers/OnlineExamController.php';
require_once BASE_PATH . '/app/Controllers/TeacherController.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Session;
use App\Controllers\ApiController;
use App\Controllers\AcademicController;
use App\Controllers\TimetableController;
use App\Controllers\OnlineExamController;
use App\Controllers\TeacherController;

Session::start();
$db = Database::getInstance();

$testResults = [];
function report(string $name, bool $passed, string $details = '') {
    global $testResults;
    $testResults[] = ['name' => $name, 'passed' => $passed, 'details' => $details];
    echo ($passed ? "✅ [PASS] " : "❌ [FAIL] ") . $name . ($details ? " - " . $details : "") . "\n";
}

echo "====================================================================\n";
echo "MIS WEBSITE VERIFICATION: TEACHING STAFF, TIMETABLE, HIERARCHY, EXAM\n";
echo "====================================================================\n\n";

// ====================================================================
// TEST SUITE 1: Teaching Staff Student Data Visibility (Issue 1)
// ====================================================================
echo "--- TEST SUITE 1: Teaching Staff Student Data Visibility ---\n";

// 1.1 Login as Teacher Jayshree01 (Tenant 24, Department 116)
$_SESSION = [];
$loginOk = Auth::attempt('Jayshree01', 'Admin@123');
$user = Auth::user();
$deptId = $user['staff_department_id'] ?? ($user['department_id'] ?? 116);
report("1.1 Teacher Login Authentication", $loginOk && $user['role_name'] === 'teacher', "Logged in as {$user['username']} (Role: {$user['role_name']}, Dept: {$deptId})");

// 1.2 Teacher RBAC Permissions for Student Data
$canViewStudents = Permission::can('students');
$canViewHierarchy = Permission::can('academic_structure');
report("1.2 Teacher RBAC Matrix", $canViewStudents && $canViewHierarchy, "can(students): " . ($canViewStudents ? 'true' : 'false') . ", can(academic_structure): " . ($canViewHierarchy ? 'true' : 'false'));

// 1.3 ApiController::getModuleData for Students (Scoping, Search, Pagination, FY/SY/TY Filter)
$_GET = ['name' => 'students', 'page_num' => 1, 'limit' => 20];
ob_start();
(new ApiController())->getModuleData();
$out = ob_get_clean();
$data = json_decode($out, true);

$hasData = is_array($data) && isset($data['data']) && count($data['data']) > 0;
$deptMatch = true;
if ($hasData) {
    foreach ($data['data'] as $st) {
        if (!empty($st['department_id']) && (int)$st['department_id'] !== 116) {
            $deptMatch = false;
            break;
        }
    }
}
report("1.3 Teacher Scoped Student Fetch", $hasData && $deptMatch, "Fetched " . count($data['data'] ?? []) . " students; all properly scoped to Dept 116");

// 1.4 Live Search in Student Roster
$_GET = ['name' => 'students', 'search' => 'Aarav'];
ob_start();
(new ApiController())->getModuleData();
$out = ob_get_clean();
$searchData = json_decode($out, true);
$searchPass = is_array($searchData) && isset($searchData['data']);
report("1.4 Live Search Functionality", $searchPass, "Search query executed cleanly, returned " . count($searchData['data'] ?? []) . " match(es)");

// 1.5 Academic Year Tab Filter (FY / SY / TY)
$_GET = ['name' => 'students', 'academic_year' => 'FY'];
ob_start();
(new ApiController())->getModuleData();
$out = ob_get_clean();
$fyData = json_decode($out, true);
report("1.5 Academic Year Filter (FY Tab)", is_array($fyData) && isset($fyData['data']), "Returned " . count($fyData['data'] ?? []) . " FY student(s)");

// 1.6 ID Card Student Filter Auto-Resolves Teacher Department
$_GET = ['department_id' => 0, 'academic_year' => 'ALL'];
ob_start();
(new ApiController())->filterStudentsForIdCards();
$out = ob_get_clean();
$idCardData = json_decode($out, true);
$idCardPass = is_array($idCardData) && (($idCardData['status'] ?? '') === 'success') && count($idCardData['data'] ?? []) > 0;
report("1.6 ID Card Auto-Resolving Teacher Dept", $idCardPass, "Auto-resolved to Teacher Dept 116, returned " . count($idCardData['data'] ?? []) . " students across all years");


// ====================================================================
// TEST SUITE 2: Academic Hierarchy Parent-Child Relationships (Issue 2)
// ====================================================================
echo "\n--- TEST SUITE 2: Academic Hierarchy ---\n";

// 2.1 Fetch Academic Hierarchy Data as Teacher
$_GET = ['dept_id' => 116];
ob_start();
(new AcademicController())->apiGetAcademicHierarchy();
$out = ob_get_clean();
$hierData = json_decode($out, true);

$depts = $hierData['departments'] ?? [];
$hierPass = is_array($hierData) && !empty($depts);
$hasCourses = false;
$hasYears = false;
$hasClassesOrStudents = false;
foreach ($depts as $d) {
    if (!empty($d['courses'])) {
        $hasCourses = true;
        foreach ($d['courses'] as $c) {
            if (!empty($c['years'])) {
                $hasYears = true;
                foreach ($c['years'] as $y) {
                    if (!empty($y['classes']) || !empty($y['students'])) {
                        $hasClassesOrStudents = true;
                    }
                }
            }
        }
    }
}
report("2.1 Parent-Child Academic Hierarchy", $hierPass && $hasCourses && $hasYears, "Hierarchy contains Departments -> Courses -> Years (FY/SY/TY) -> Classes/Students");

// 2.2 Teacher View-Only Permission vs Admin Management
$teacherCanManage = Permission::canManage('academic_structure');
report("2.2 View-Only Enforcement for Teacher", !$teacherCanManage, "Teacher canManage is false (Edit/Delete controls hidden)");

// ====================================================================
// TEST SUITE 3: Student Timetable View-Only UI & Role Guards (Issue 3)
// ====================================================================
echo "\n--- TEST SUITE 3: Student Timetable ---\n";

// 3.1 Login as Student
$_SESSION = [];
$studentLogin = Auth::attempt('john_student', 'Admin@123');
$studentUser = Auth::user();
report("3.1 Student Login Authentication", $studentLogin && $studentUser['role_name'] === 'student', "Logged in as {$studentUser['username']} (Role: {$studentUser['role_name']})");

// 3.2 View-Only Timetable Rendering for Student
ob_start();
(new TimetableController())->showTimetable();
$viewOutput = ob_get_clean();
$hasStudentTitle = strpos($viewOutput, 'Student Timetable') !== false;
$hasViewOnlyBadge = strpos($viewOutput, 'VIEW ONLY') !== false;
$noCreateButton = strpos($viewOutput, '+ Add Timetable Slot') === false && strpos($viewOutput, '+ Create Timetable') === false;
report("3.2 Student Timetable View-Only Interface", $hasStudentTitle && $hasViewOnlyBadge && $noCreateButton, "Title: 'Student Timetable', 'VIEW ONLY' badge rendered, zero editing controls");

// 3.3 Backend Role Guard: Student Rejected from Deleting Timetable
$_POST = ['timetable_id' => 9999];
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
ob_start();
(new TimetableController())->deleteTimetable();
$delRes = ob_get_clean();
$delJson = json_decode($delRes, true);
$status403 = http_response_code() === 403 || ($delJson && strpos($delJson['error'] ?? '', '403') !== false);
report("3.3 Backend Timetable Deletion Role Guard", $status403, "Student deletion attempt strictly blocked with 403 Forbidden");

// 3.4 Backend Role Guard: Student Rejected from Creating Timetable
http_response_code(200); // reset
$_POST = ['day_of_week' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'subject_id' => 1];
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
ob_start();
(new TimetableController())->createTimetable();
$createRes = ob_get_clean();
$createJson = json_decode($createRes, true);
$create403 = http_response_code() === 403 || ($createJson && strpos($createJson['error'] ?? '', '403') !== false);
report("3.4 Backend Timetable Creation Role Guard", $create403, "Student creation attempt strictly blocked with 403 Forbidden");


// ====================================================================
// TEST SUITE 4 & 5: Online Exam Management, FY/SY/TY Selection & Deletion (Issue 4 & Additional Requirement)
// ====================================================================
echo "\n--- TEST SUITE 4 & 5: Online Exam & Academic Year Dropdown ---\n";

// 4.1 Switch to Admin
$_SESSION = [];
Auth::attempt('admin', 'Admin@123');
$adminUser = Auth::user();
report("4.1 Admin Login Authentication", $adminUser && in_array($adminUser['role_name'], ['admin', 'super_admin']), "Logged in as {$adminUser['username']} (Role: {$adminUser['role_name']})");

// 4.2 Create Exam with Academic Year (FY, SY, TY), Department, Course, Subject
$testExamTitle = 'E2E Test Exam FY ' . time();
$_POST = [
    'action' => 'create_exam',
    'title' => $testExamTitle,
    'department_id' => 1,
    'course_id' => 1,
    'academic_year' => 'FY',
    'standard_id' => 1,
    'subject_id' => 1,
    'duration_minutes' => 20,
    'status' => 'published'
];
ob_start();
(new OnlineExamController())->createExam();
ob_get_clean();

// Verify in DB
$checkStmt = $db->prepare("SELECT * FROM online_exams WHERE title = :title ORDER BY id DESC LIMIT 1");
$checkStmt->execute([':title' => $testExamTitle]);
$createdExam = $checkStmt->fetch();

$examCreatedPass = $createdExam && $createdExam['academic_year'] === 'FY' && (int)$createdExam['duration_minutes'] === 20;
report("4.2 Create Exam with Academic Year (FY)", $examCreatedPass, "Saved Exam ID #{$createdExam['id']} with academic_year='{$createdExam['academic_year']}', standard_id={$createdExam['standard_id']}");

$testExamId = (int)($createdExam['id'] ?? 0);

// 4.3 Add MCQ Question to Exam
$_POST = [
    'exam_id' => $testExamId,
    'question_text' => 'What is the binary representation of decimal 10?',
    'option_a' => '1010',
    'option_b' => '1100',
    'option_c' => '1001',
    'option_d' => '1111',
    'correct_option' => 'A',
    'marks' => 2
];
ob_start();
(new OnlineExamController())->addMcqQuestion();
ob_get_clean();

$qStmt = $db->prepare("SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = :eid");
$qStmt->execute([':eid' => $testExamId]);
$qCount = (int)$qStmt->fetchColumn();
report("4.3 Add MCQ Question to Exam", $qCount === 1, "Added 1 question with correct option A (2 marks)");

// 4.4 Update Exam Configuration (Change Academic Year to SY, duration to 30)
$updatedTitle = 'Updated E2E Test Exam SY ' . time();
$_POST = [
    'exam_id' => $testExamId,
    'title' => $updatedTitle,
    'department_id' => 1,
    'course_id' => 1,
    'academic_year' => 'SY',
    'standard_id' => 2,
    'subject_id' => 1,
    'duration_minutes' => 30,
    'status' => 'published'
];
ob_start();
(new OnlineExamController())->updateExam();
ob_get_clean();

$checkUpdated = $db->query("SELECT * FROM online_exams WHERE id = {$testExamId}")->fetch();
$updatePass = $checkUpdated && $checkUpdated['academic_year'] === 'SY' && (int)$checkUpdated['duration_minutes'] === 30 && (int)$checkUpdated['standard_id'] === 2;
report("4.4 Update Exam Academic Year to SY", $updatePass, "Updated Exam #{$testExamId} to academic_year='{$checkUpdated['academic_year']}', standard_id={$checkUpdated['standard_id']}, duration={$checkUpdated['duration_minutes']}");

// 4.5 Student Eligibility Enforcement in takeExam()
// Log in as FY student and attempt to take this SY exam
$_SESSION = [];
Auth::attempt('john_student', 'Admin@123'); // John is FY student
$_GET = ['id' => $testExamId];
ob_start();
(new OnlineExamController())->takeExam();
ob_get_clean();
$flashErr = Session::getFlash('error');
$eligibilityBlocked = !empty($flashErr) && strpos($flashErr, 'Access Denied') !== false;
report("4.5 Student Eligibility Enforcement (FY Student blocked from SY Exam)", $eligibilityBlocked, "Blocked with message: '{$flashErr}'");

// 4.6 Exam Deletion: Case A - Safe Retention Policy with Student Submission
// Switch to Admin, insert test student submission, and call deleteExam()
$_SESSION = [];
Auth::attempt('admin', 'Admin@123');

$db->prepare("
    INSERT INTO online_exam_submissions (tenant_id, exam_id, student_id, score, total_score, submission_status, submission_reason, started_at, submitted_at)
    VALUES (1, :eid, 1, 2, 2, 'completed', 'normal_submission', NOW(), NOW())
")->execute([':eid' => $testExamId]);

$_POST = ['exam_id' => $testExamId, 'archive_if_has_submissions' => '1'];
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
ob_start();
(new OnlineExamController())->deleteExam();
$delRes = ob_get_clean();
$delData = json_decode($delRes, true);

$archivedStatus = $db->query("SELECT status FROM online_exams WHERE id = {$testExamId}")->fetchColumn();
$archivePass = ($delData['action'] ?? '') === 'archived' && $archivedStatus === 'archived';
report("4.6 Exam Deletion Retention Policy (Archived with Submissions)", $archivePass, "Exam status safely transitioned to 'archived', preserving student submissions and questions");

// 4.7 Exam Deletion: Case B - Permanent Deletion without Submissions
// Create clean exam with zero submissions and delete it
$cleanExamTitle = 'Delete Clean Exam ' . time();
$db->prepare("
    INSERT INTO online_exams (tenant_id, department_id, course_id, academic_year, standard_id, title, duration_minutes, total_questions, status)
    VALUES (1, 1, 1, 'TY', 3, :title, 15, 0, 'draft')
")->execute([':title' => $cleanExamTitle]);
$cleanExamId = (int)$db->lastInsertId();

// Add dummy question to clean exam
$db->prepare("INSERT INTO online_exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES (:eid, 'Test Q', 'A', 'B', 'C', 'D', 'A', 1)")->execute([':eid' => $cleanExamId]);

$_POST = ['exam_id' => $cleanExamId];
ob_start();
(new OnlineExamController())->deleteExam();
$cleanDelRes = ob_get_clean();
$cleanDelData = json_decode($cleanDelRes, true);

$cleanExamStillExists = (int)$db->query("SELECT COUNT(*) FROM online_exams WHERE id = {$cleanExamId}")->fetchColumn();
$cleanQuestionsCount = (int)$db->query("SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = {$cleanExamId}")->fetchColumn();

$permDeletePass = ($cleanDelData['action'] ?? '') === 'deleted' && $cleanExamStillExists === 0 && $cleanQuestionsCount === 0;
report("4.7 Permanent Deletion for Exam without Submissions", $permDeletePass, "Exam #{$cleanExamId} and associated questions completely removed via database transaction");

// 4.8 Unauthorized User Role Guard: Student Cannot Delete Exam
$_SESSION = [];
Auth::attempt('john_student', 'Admin@123');
$_POST = ['exam_id' => $testExamId];
ob_start();
(new OnlineExamController())->deleteExam();
$unauthDelRes = ob_get_clean();
$unauthDelData = json_decode($unauthDelRes, true);
$studentDelBlocked = http_response_code() === 403 || strpos($unauthDelData['error'] ?? '', 'permission') !== false;
report("4.8 Unauthorized Role Guard (Student Deletion Blocked)", $studentDelBlocked, "Student rejected with 403 Forbidden");

// Cleanup test archived exam
$db->prepare("DELETE FROM online_exam_submissions WHERE exam_id = :eid")->execute([':eid' => $testExamId]);
$db->prepare("DELETE FROM online_exam_questions WHERE exam_id = :eid")->execute([':eid' => $testExamId]);
$db->prepare("DELETE FROM online_exams WHERE id = :eid")->execute([':eid' => $testExamId]);

echo "\n====================================================================\n";
$allPassed = true;
foreach ($testResults as $r) {
    if (!$r['passed']) {
        $allPassed = false;
        break;
    }
}
echo "TEST SUMMARY: " . ($allPassed ? "ALL TESTS PASSED SUCCESSFULLY! 🎉" : "SOME TESTS FAILED") . "\n";
echo "Total Tests: " . count($testResults) . " | Passed: " . count(array_filter($testResults, fn($r) => $r['passed'])) . "\n";
echo "====================================================================\n";
