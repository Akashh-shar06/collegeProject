<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Permission;
use App\Core\Session;

class LeaveWorkflowTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        $_SESSION = [];
        Session::set('user_id', 3); // Dr. Soap (Teacher)
    }

    public function testTeacherHasLeaveManagementPermission(): void
    {
        Session::set('role_name', 'teacher');
        $this->assertTrue(Permission::can('leave_management'), 'Teacher must have access to Leave Management');
    }

    public function testNonTeachingStaffHasLeaveManagementPermission(): void
    {
        $nonTeachingRoles = [
            'admin_officer',
            'accountant',
            'librarian',
            'transport_hostel_admin',
            'inventory_manager',
            'receptionist',
            'erp_admin'
        ];

        foreach ($nonTeachingRoles as $role) {
            Session::set('role_name', $role);
            $this->assertTrue(Permission::can('leave_management'), "{$role} must have access to Leave Management");
        }
    }

    public function testTeacherHasNineModulesTotal(): void
    {
        Session::set('role_name', 'teacher');

        $nineModules = [
            'student_attendance',
            'exam_management_module',
            'time_table',
            'online_exam',
            'exam_management',
            'student_mis',
            'communication',
            'leave_management'
        ];

        foreach ($nineModules as $m) {
            $this->assertTrue(Permission::can($m), "Teacher must have access to {$m}");
        }

        // Must NOT have financial or full administrative modules
        $forbidden = ['fees', 'accounts', 'role_permission', 'payroll'];
        foreach ($forbidden as $f) {
            $this->assertFalse(Permission::can($f), "Teacher MUST NOT have access to {$f}");
        }
    }

    public function testLeaveDatabaseTablesExist(): void
    {
        $db = \App\Core\Database::getInstance();
        $tables = ['leave_types', 'leave_balances', 'leave_requests', 'leave_audit_logs'];

        foreach ($tables as $t) {
            $stmt = $db->query("SHOW TABLES LIKE '{$t}'");
            $this->assertEquals($t, $stmt->fetchColumn(), "Table {$t} must exist in database");
        }
    }
}
