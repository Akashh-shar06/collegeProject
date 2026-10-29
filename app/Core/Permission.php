<?php
namespace App\Core;

class Permission
{
    private static array $roleMatrix = [
        'principal' => [
            'enquiry' => 'full', 'admission' => 'full', 'fees' => 'full', 'accounts' => 'full',
            'student_attendance' => 'full', 'academic_calendar' => 'full', 'id_cards' => 'full',
            'time_table' => 'full', 'certificate' => 'full', 'online_exam' => 'full',
            'exam_management' => 'full', 'exam_management_module' => 'full',
            'transport' => 'full', 'stock' => 'full', 'library' => 'full', 'visitor' => 'full',
            'student_mis' => 'full', 'hr_management' => 'full', 'staff_data' => 'full',
            'staff_management' => 'full',
            'staff_attendance' => 'full', 'leave_management' => 'full', 'payroll' => 'full',
            'ticket_management' => 'full', 'asset_management' => 'full', 'task_management' => 'full',
            'hostel_management' => 'full', 'day_boarding' => 'full', 'infirmary' => 'full',
            'recruitment' => 'full',
            'news_events' => 'full', 'communication' => 'full', 'tutorial' => 'full',
            'support' => 'full', 'dashboard' => 'full',
            'courses' => 'full', 'departments' => 'full', 'subjects' => 'full',
            'academic_structure' => 'full', 'student_promotion' => 'full', 'promotion_history' => 'full',
            'teacher_panel' => 'full', 'homework' => 'full', 'lesson_record' => 'full', 'classroom_remarks' => 'full',
        ],
        'super_admin' => [
            'enquiry' => 'full', 'admission' => 'full', 'fees' => 'full', 'accounts' => 'full',
            'student_attendance' => 'full', 'academic_calendar' => 'full', 'id_cards' => 'full',
            'time_table' => 'full', 'certificate' => 'full', 'online_exam' => 'full',
            'exam_management' => 'full', 'exam_management_module' => 'full',
            'transport' => 'full', 'stock' => 'full', 'library' => 'full', 'visitor' => 'full',
            'student_mis' => 'full', 'hr_management' => 'full', 'staff_data' => 'full',
            'staff_management' => 'full',
            'staff_attendance' => 'full', 'leave_management' => 'full', 'payroll' => 'full',
            'ticket_management' => 'full', 'asset_management' => 'full', 'task_management' => 'full',
            'hostel_management' => 'full', 'day_boarding' => 'full', 'infirmary' => 'full',
            'app_analysis' => 'full', 'recruitment' => 'full', 'role_permission' => 'full',
            'news_events' => 'full', 'communication' => 'full', 'tutorial' => 'full',
            'support' => 'full', 'dashboard' => 'full',
            'courses' => 'full', 'departments' => 'full', 'subjects' => 'full',
            'academic_structure' => 'full', 'student_promotion' => 'full', 'promotion_history' => 'full',
            'teacher_panel' => 'full', 'homework' => 'full', 'lesson_record' => 'full', 'classroom_remarks' => 'full',
        ],

        'school_admin' => [
            'enquiry' => 'full', 'admission' => 'full', 'fees' => 'full', 'accounts' => 'full',
            'student_attendance' => 'full', 'academic_calendar' => 'full', 'id_cards' => 'full',
            'time_table' => 'full', 'certificate' => 'full', 'online_exam' => 'full',
            'exam_management' => 'full', 'exam_management_module' => 'full',
            'transport' => 'full', 'stock' => 'full', 'library' => 'full', 'visitor' => 'full',
            'student_mis' => 'full', 'hr_management' => 'full', 'staff_data' => 'full',
            'staff_attendance' => 'full', 'leave_management' => 'full', 'payroll' => 'full',
            'ticket_management' => 'full', 'asset_management' => 'full', 'task_management' => 'full',
            'hostel_management' => 'full', 'day_boarding' => 'full', 'infirmary' => 'full',
            'app_analysis' => 'full', 'recruitment' => 'full', 'role_permission' => 'full',
            'news_events' => 'full', 'communication' => 'full', 'tutorial' => 'full',
            'support' => 'full', 'dashboard' => 'full',
            'courses' => 'full', 'departments' => 'full', 'subjects' => 'full',
            'academic_structure' => 'full', 'student_promotion' => 'full', 'promotion_history' => 'full',
            'teacher_panel' => 'full', 'homework' => 'full', 'lesson_record' => 'full', 'classroom_remarks' => 'full',
        ],

        'hod' => [
            'enquiry' => 'view', 'admission' => 'full', 'fees' => 'view', 'accounts' => 'view',
            'student_attendance' => 'full', 'academic_calendar' => 'full', 'id_cards' => 'full',
            'time_table' => 'full', 'certificate' => 'full', 'online_exam' => 'full',
            'exam_management' => 'full', 'exam_management_module' => 'full',
            'student_mis' => 'full', 'hr_management' => 'view', 'staff_data' => 'full',
            'staff_management' => 'full',
            'staff_attendance' => 'full', 'leave_management' => 'full',
            'ticket_management' => 'full', 'task_management' => 'full',
            'communication' => 'full', 'tutorial' => 'full',
            'support' => 'full', 'dashboard' => 'full',
            'courses' => 'full', 'departments' => 'full', 'subjects' => 'full',
            'academic_structure' => 'full', 'student_promotion' => 'full', 'promotion_history' => 'full',
            'teacher_panel' => 'full', 'homework' => 'full', 'lesson_record' => 'full', 'classroom_remarks' => 'full',
        ],

        'teacher' => [
            'student_attendance' => 'full',
            'exam_management_module' => 'full',
            'time_table' => 'full',
            'online_exam' => 'full',
            'exam_management' => 'full',
            'student_mis' => 'view',
            'students' => 'view',
            'id_cards' => 'view',
            'academic_structure' => 'view',
            'communication' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
            'teacher_panel' => 'full',
            'homework' => 'full',
            'lesson_record' => 'full',
            'classroom_remarks' => 'full',
        ],
        'admin_officer' => [
            'enquiry' => 'full',
            'admission' => 'full',
            'id_cards' => 'full',
            'certificate' => 'full',
            'student_mis' => 'full',
            'visitor' => 'full',
            'communication' => 'full',
            'news_events' => 'full',
            'ticket_management' => 'full',
            'support' => 'full',
            'task_management' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'accountant' => [
            'fees' => 'full',
            'accounts' => 'full',
            'payroll' => 'full',
            'student_mis' => 'view',
            'ticket_management' => 'full',
            'support' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'hr_executive' => [
            'hr_management' => 'full',
            'staff_data' => 'full',
            'staff_attendance' => 'full',
            'leave_management' => 'full',
            'payroll' => 'full',
            'recruitment' => 'full',
            'student_mis' => 'view',
            'communication' => 'full',
            'task_management' => 'full',
            'support' => 'full',
            'dashboard' => 'full',
        ],
        'hr' => [
            'hr_management' => 'full',
            'staff_data' => 'full',
            'staff_attendance' => 'full',
            'leave_management' => 'full',
            'payroll' => 'full',
            'recruitment' => 'full',
            'student_mis' => 'view',
            'communication' => 'full',
            'task_management' => 'full',
            'support' => 'full',
            'dashboard' => 'full',
        ],
        'librarian' => [
            'library' => 'full',
            'student_mis' => 'view',
            'staff_data' => 'view',
            'id_cards' => 'view',
            'ticket_management' => 'full',
            'communication' => 'full',
            'support' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'transport_hostel_admin' => [
            'transport' => 'full',
            'hostel_management' => 'full',
            'day_boarding' => 'full',
            'student_mis' => 'view',
            'id_cards' => 'view',
            'visitor' => 'full',
            'ticket_management' => 'full',
            'task_management' => 'full',
            'communication' => 'full',
            'support' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'inventory_manager' => [
            'stock' => 'full',
            'asset_management' => 'full',
            'task_management' => 'full',
            'student_mis' => 'view',
            'staff_data' => 'view',
            'ticket_management' => 'full',
            'communication' => 'full',
            'support' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'receptionist' => [
            'enquiry' => 'full',
            'admission' => 'view',
            'visitor' => 'full',
            'id_cards' => 'view',
            'communication' => 'full',
            'news_events' => 'full',
            'ticket_management' => 'full',
            'support' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],
        'erp_admin' => [
            'role_permission' => 'full',
            'app_analysis' => 'full',
            'staff_data' => 'full',
            'communication' => 'full',
            'ticket_management' => 'full',
            'support' => 'full',
            'task_management' => 'full',
            'leave_management' => 'view',
            'dashboard' => 'full',
        ],

        'student' => [
            'student_attendance' => 'view',
            'academic_calendar' => 'view',
            'time_table' => 'view',
            'online_exam' => 'full',
            'exam_management' => 'view',
            'fees' => 'view',
            'library' => 'full',
            'student_mis' => 'view',
            'news_events' => 'view',
            'communication' => 'view',
            'tutorial' => 'full',
            'support' => 'full',
            'dashboard' => 'full',
        ],
        'parent' => [
            'admission' => 'full',
            'fees' => 'full',
            'student_attendance' => 'view',
            'academic_calendar' => 'view',
            'id_cards' => 'view',
            'time_table' => 'view',
            'certificate' => 'view',
            'online_exam' => 'view',
            'exam_management' => 'view',
            'exam_management_module' => 'view',
            'transport' => 'full',
            'library' => 'view',
            'student_mis' => 'view',
            'ticket_management' => 'full',
            'hostel_management' => 'full',
            'day_boarding' => 'full',
            'infirmary' => 'full',
            'news_events' => 'view',
            'communication' => 'full',
            'tutorial' => 'view',
            'support' => 'full',
            'dashboard' => 'full',
        ]
    ];

    public static function getAccessLevel(string $action): string
    {
        $user = Auth::user();
        if (!$user) {
            return 'none';
        }

        $role = $user['role_name'] ?? '';

        // Role alias normalization
        if ($role === 'hr') {
            $role = 'hr_executive';
        }

        // Check hardcoded matrix first
        if (isset(self::$roleMatrix[$role][$action])) {
            return self::$roleMatrix[$role][$action];
        }

        // Check Database dynamic role_permissions if matrix has no explicit entry
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT rp.permission_type, rp.is_allowed 
                FROM role_permissions rp 
                JOIN roles r ON rp.role_id = r.id 
                WHERE (r.name = :r1 OR r.name = :r2) AND rp.module_key = :m
            ");
            $stmt->execute([':r1' => $role, ':r2' => $user['role_name'], ':m' => $action]);
            $perms = $stmt->fetchAll();
            if (!empty($perms)) {
                $hasManage = false;
                $hasView = false;
                foreach ($perms as $p) {
                    if ((int)$p['is_allowed'] === 1) {
                        if (in_array($p['permission_type'], ['manage', 'create', 'edit', 'delete', 'approve'], true)) {
                            $hasManage = true;
                        }
                        if ($p['permission_type'] === 'view') {
                            $hasView = true;
                        }
                    }
                }
                if ($hasManage) return 'full';
                if ($hasView) return 'view';
            }
        } catch (\Throwable $e) {
            // Fallback silently if DB is unreachable
        }

        if ($action === 'dashboard') {
            return 'full';
        }

        return 'none';
    }

    public static function can(string $action): bool
    {
        $level = self::getAccessLevel($action);
        return $level === 'full' || $level === 'view' || $level === 'admin_limited';
    }

    public static function canAction(string $module, string $actionType): bool
    {
        $level = self::getAccessLevel($module);
        if ($level === 'none') {
            return false;
        }

        if ($level === 'full') {
            return true;
        }

        if ($level === 'view') {
            return strtolower($actionType) === 'view';
        }

        if ($level === 'admin_limited') {
            return in_array(strtolower($actionType), ['view', 'create', 'edit'], true);
        }

        return false;
    }

    public static function canManage(string $action): bool
    {
        $level = self::getAccessLevel($action);
        return $level === 'full' || $level === 'admin_limited';
    }

    public static function enforce(string $action): void
    {
        if (!self::can($action)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::json(['error' => '403 Forbidden: You do not have permission to access this module.'], 403);
            } else {
                Response::view('auth/forbidden', ['action' => $action]);
            }
            exit;
        }
    }

    public static function enforceAction(string $module, string $actionType): void
    {
        if (!self::canAction($module, $actionType)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::json(['error' => "403 Forbidden: You do not have '{$actionType}' permission for this module."], 403);
            } else {
                Response::view('auth/forbidden', ['action' => "{$module} ({$actionType})"]);
            }
            exit;
        }
    }

    public static function enforceManage(string $action): void
    {
        if (!self::canManage($action)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::json(['error' => '403 Forbidden: You do not have management permissions for this module.'], 403);
            } else {
                Response::view('auth/forbidden', ['action' => $action]);
            }
            exit;
        }
    }

    public static function getAssignedModulesForRole(string $role): array
    {
        $roleKey = strtolower($role);
        if ($roleKey === 'hr') {
            $roleKey = 'hr_executive';
        }
        if (isset(self::$roleMatrix[$roleKey])) {
            return array_keys(self::$roleMatrix[$roleKey]);
        }
        return ['dashboard'];
    }
}

