<?php
/**
 * Campus360 Enterprise ERP - Public Front Controller
 */

declare(strict_types=1);

// Define Base Paths
define('BASE_PATH', dirname(__DIR__));

// Load Security Headers & Config
require_once BASE_PATH . '/config/security.php';
SecurityHeaders::apply();

// Autoload Classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ApiController;
use App\Controllers\ModuleController;
use App\Controllers\WorkflowController;
use App\Controllers\LeaveController;
use App\Controllers\TaskController;
use App\Controllers\OnlineExamController;
use App\Controllers\TimetableController;
use App\Controllers\AcademicController;
use App\Controllers\TeacherController;
use App\Controllers\FeeController;

// Start Secure Session
Session::start();

// Initialize Router
$router = new Router();

// Define Application Routes
$router->get('login', [AuthController::class, 'showLogin']);
$router->post('login_process', [AuthController::class, 'processLogin']);
$router->get('logout', [AuthController::class, 'logout']);

// Password Reset Routes
$router->get('forgot_password', [AuthController::class, 'showForgotPassword']);
$router->post('forgot_password_process', [AuthController::class, 'processForgotPassword']);
$router->get('reset_password', [AuthController::class, 'showResetPassword']);
$router->post('reset_password_process', [AuthController::class, 'processResetPassword']);

// Onboarding Routes
$router->get('register_tenant', [WorkflowController::class, 'showTenantRegistration']);
$router->post('register_tenant_process', [WorkflowController::class, 'processTenantRegistration']);

$router->get('dashboard', [DashboardController::class, 'showDashboard']);
$router->post('toggle_tenant_status', [DashboardController::class, 'toggleTenantStatus']);
$router->post('delete_tenant', [DashboardController::class, 'deleteTenant']);

$router->get('module', [ModuleController::class, 'showModule']);
$router->post('module_store', [ModuleController::class, 'storeRecord']);
$router->post('module_update', [ModuleController::class, 'updateRecord']);
$router->post('module_delete', [ModuleController::class, 'deleteRecord']);

// Department & Class Timetable Routes
$router->get('timetable', [TimetableController::class, 'showTimetable']);
$router->post('create_timetable', [TimetableController::class, 'createTimetable']);
$router->post('update_timetable', [TimetableController::class, 'updateTimetable']);
$router->post('delete_timetable', [TimetableController::class, 'deleteTimetable']);
$router->post('copy_timetable', [TimetableController::class, 'copyTimetable']);
$router->post('update_timeslot_row', [TimetableController::class, 'updateTimeSlotRow']);
$router->post('delete_timeslot_row', [TimetableController::class, 'deleteTimeSlotRow']);
$router->post('add_timeslot_row', [TimetableController::class, 'addTimeSlotRow']);
$router->get('api_timetable_entry', [TimetableController::class, 'getTimetableEntryJson']);
$router->get('api_dependent_options', [TimetableController::class, 'getDependentOptions']);

// Teacher Panel, Weekly Timetable, Attendance, Homework & Lesson Management Routes
$router->get('teacher_dashboard', [TeacherController::class, 'showDashboard']);
$router->get('teacher_timetable', [TeacherController::class, 'showWeeklyTimetable']);
$router->get('lecture_details', [TeacherController::class, 'showLectureDetails']);
$router->post('save_lecture_attendance', [TeacherController::class, 'saveAttendance']);
$router->post('save_lesson_record', [TeacherController::class, 'saveLessonRecord']);
$router->post('save_homework', [TeacherController::class, 'saveHomework']);
$router->post('delete_homework', [TeacherController::class, 'deleteHomework']);
$router->post('save_classroom_remark', [TeacherController::class, 'saveClassroomRemark']);

// Online Exam & Anti-Cheating Workflow Routes
$router->post('create_online_exam', [OnlineExamController::class, 'createExam']);
$router->post('update_online_exam', [OnlineExamController::class, 'updateExam']);
$router->get('take_online_exam', [OnlineExamController::class, 'takeExam']);
$router->post('submit_online_exam', [OnlineExamController::class, 'submitExam']);
$router->post('add_mcq_question', [OnlineExamController::class, 'addMcqQuestion']);
$router->post('delete_mcq_question', [OnlineExamController::class, 'deleteMcqQuestion']);
$router->post('delete_online_exam', [OnlineExamController::class, 'deleteExam']);

// Staff Leave Management Workflow Routes
$router->post('leave_apply', [LeaveController::class, 'applyLeave']);
$router->post('leave_cancel', [LeaveController::class, 'cancelLeave']);
$router->post('leave_hr_review', [LeaveController::class, 'hrReview']);
$router->post('leave_principal_approve', [LeaveController::class, 'principalApproval']);
$router->post('leave_superadmin_approve', [LeaveController::class, 'superAdminLeaveApproval']);
$router->post('leave_type_save', [LeaveController::class, 'saveLeaveType']);

// Staff Task Management Routes
$router->post('task_assign', [TaskController::class, 'assignTask']);
$router->post('task_update_status', [TaskController::class, 'updateTaskStatus']);


// Academic Structure, Course, Department, Subject & Student Promotion Routes
$router->get('courses', [AcademicController::class, 'showCourses']);
$router->post('course_save', [AcademicController::class, 'storeCourse']);
$router->post('course_delete', [AcademicController::class, 'deleteCourse']);

$router->get('departments', [AcademicController::class, 'showDepartments']);
$router->post('department_save', [AcademicController::class, 'storeDepartment']);
$router->post('department_delete', [AcademicController::class, 'deleteDepartment']);

$router->get('academic_structure', [AcademicController::class, 'showAcademicStructure']);
$router->post('academic_session_save', [AcademicController::class, 'storeAcademicSession']);
$router->post('academic_level_save', [AcademicController::class, 'storeAcademicLevel']);

$router->get('subjects', [AcademicController::class, 'showSubjects']);
$router->post('subject_save', [AcademicController::class, 'storeSubject']);
$router->post('subject_delete', [AcademicController::class, 'deleteSubject']);
$router->post('subject_offering_save', [AcademicController::class, 'storeSubjectOffering']);

$router->get('student_promotion', [AcademicController::class, 'showStudentPromotion']);
$router->post('promote_students_process', [AcademicController::class, 'processBulkPromotion']);
$router->get('promotion_history', [AcademicController::class, 'showPromotionHistory']);

$router->get('academic_hierarchy', [AcademicController::class, 'showAcademicHierarchy']);
$router->get('api_academic_hierarchy', [AcademicController::class, 'apiGetAcademicHierarchy']);
$router->post('quick_enroll_student', [AcademicController::class, 'quickEnrollStudent']);

// End-to-End Workflow Routes
$router->post('admit_student', [WorkflowController::class, 'admitStudent']);
$router->post('promote_student', [WorkflowController::class, 'promoteStudent']);
$router->get('transfer_certificate', [WorkflowController::class, 'generateTransferCertificate']);
$router->post('save_attendance', [WorkflowController::class, 'saveAttendance']);
$router->post('save_exam_marks', [WorkflowController::class, 'saveExamMarks']);
$router->post('payment_webhook', [WorkflowController::class, 'processPaymentWebhook']);
$router->post('store_class', [WorkflowController::class, 'storeClass']);
$router->post('update_class', [WorkflowController::class, 'updateClass']);
$router->post('delete_class', [WorkflowController::class, 'deleteClass']);
$router->post('approve_leave', [WorkflowController::class, 'approveLeave']);
$router->post('return_book', [WorkflowController::class, 'returnBook']);

$router->get('api_module', [ApiController::class, 'getModuleData']);
$router->get('api_search_student_promotion', [ApiController::class, 'searchStudentForPromotion']);
$router->get('api_dashboard_updates', [ApiController::class, 'getDashboardUpdates']);
$router->get('api_standards', [ApiController::class, 'getStandards']);
$router->get('api_classes', [ApiController::class, 'getClasses']);
$router->get('api_subjects', [ApiController::class, 'getSubjects']);
$router->get('api_teachers', [ApiController::class, 'getTeachers']);
$router->get('api_students_filter', [ApiController::class, 'filterStudentsForIdCards']);
$router->post('submit_enquiry', [ApiController::class, 'submitEnquiry']);

$router->post('assign_teacher', [ModuleController::class, 'assignTeacher']);
$router->post('delete_teacher_assignment', [ModuleController::class, 'deleteTeacherAssignment']);
$router->post('store_department', [ModuleController::class, 'storeDepartment']);
$router->post('delete_department', [ModuleController::class, 'deleteDepartment']);
$router->post('store_standard', [ModuleController::class, 'storeStandard']);

// Accountant & Financial Pending Installment Dues Routes
$router->get('pending_installments', [FeeController::class, 'showPendingInstallments']);
$router->get('api_pending_installments', [FeeController::class, 'getPendingInstallmentsJson']);
$router->get('api_student_installments', [FeeController::class, 'getStudentInstallmentDetailsJson']);
$router->post('record_installment_payment', [FeeController::class, 'recordPayment']);
$router->post('generate_student_installments', [FeeController::class, 'generateInstallments']);
$router->get('export_pending_installments_csv', [FeeController::class, 'exportCsv']);

// Dispatch Request
$router->dispatch();



