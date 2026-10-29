<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Permission;
use App\Core\Session;

class PermissionTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testUnauthenticatedUserHasNoPermission(): void {
        $this->assertFalse(Permission::can('dashboard'));
        $this->assertFalse(Permission::can('fees'));
        $this->assertEquals('none', Permission::getAccessLevel('fees'));
    }

    public function testSuperAdminHasFullPermissions(): void {
        Session::set('user_id', 1);
        Session::set('role_name', 'super_admin');
        $this->assertTrue(Permission::can('dashboard'));
        $this->assertTrue(Permission::canManage('fees'));
        $this->assertEquals('full', Permission::getAccessLevel('fees'));
    }

    public function testPrincipalPermissions(): void {
        Session::set('user_id', 8);
        Session::set('role_name', 'principal');

        // Principal has overall institutional access to all ERP modules
        $this->assertTrue(Permission::canManage('enquiry'));
        $this->assertTrue(Permission::canManage('staff_management'));
        $this->assertTrue(Permission::canManage('fees'));
        $this->assertTrue(Permission::canManage('accounts'));
        $this->assertTrue(Permission::canManage('student_attendance'));
        $this->assertEquals('full', Permission::getAccessLevel('fees'));
    }

    public function testTeacherPermissionsIsolatedToEightModules(): void {
        Session::set('user_id', 3);
        Session::set('role_name', 'teacher');

        // Teacher MUST have access to the 8 academic modules + communication
        $this->assertTrue(Permission::can('student_attendance'));
        $this->assertTrue(Permission::can('exam_management_module'));
        $this->assertTrue(Permission::can('time_table'));
        $this->assertTrue(Permission::can('online_exam'));
        $this->assertTrue(Permission::can('exam_management'));
        $this->assertTrue(Permission::can('student_mis'));

        // Teacher MUST NOT have access to administrative, financial or HR modules
        $this->assertFalse(Permission::can('enquiry'));
        $this->assertFalse(Permission::can('admission'));
        $this->assertFalse(Permission::can('fees'));
        $this->assertFalse(Permission::can('accounts'));
        $this->assertFalse(Permission::can('hr_management'));
        $this->assertFalse(Permission::can('payroll'));
        $this->assertFalse(Permission::can('role_permission'));
    }

    public function testAdministrativeOfficerPermissions(): void {
        Session::set('user_id', 11);
        Session::set('role_name', 'admin_officer');

        $this->assertTrue(Permission::can('enquiry'));
        $this->assertTrue(Permission::can('admission'));
        $this->assertTrue(Permission::can('id_cards'));
        $this->assertTrue(Permission::can('certificate'));

        $this->assertFalse(Permission::can('payroll'));
        $this->assertFalse(Permission::can('hr_management'));
        $this->assertFalse(Permission::can('role_permission'));
    }

    public function testAccountantPermissions(): void {
        Session::set('user_id', 5);
        Session::set('role_name', 'accountant');

        $this->assertTrue(Permission::can('fees'));
        $this->assertTrue(Permission::canManage('fees'));
        $this->assertTrue(Permission::canManage('accounts'));
        $this->assertTrue(Permission::canManage('payroll'));

        $this->assertFalse(Permission::can('student_attendance'));
        $this->assertFalse(Permission::can('online_exam'));
        $this->assertFalse(Permission::can('role_permission'));
    }

    public function testErpAdminPermissions(): void {
        Session::set('user_id', 15);
        Session::set('role_name', 'erp_admin');

        $this->assertTrue(Permission::can('role_permission'));
        $this->assertTrue(Permission::can('app_analysis'));
        $this->assertTrue(Permission::can('ticket_management'));
    }

    public function testStudentPermissions(): void {
        Session::set('user_id', 6);
        Session::set('role_name', 'student');

        $this->assertTrue(Permission::can('student_mis'));
        $this->assertTrue(Permission::can('fees'));
        $this->assertFalse(Permission::canManage('fees'));
        $this->assertTrue(Permission::can('online_exam'));

        // Student has view access only (no edit/update/delete) for time_table and communication
        $this->assertTrue(Permission::can('time_table'));
        $this->assertFalse(Permission::canManage('time_table'));
        $this->assertTrue(Permission::can('communication'));
        $this->assertFalse(Permission::canManage('communication'));

        $this->assertFalse(Permission::can('hr_management'));
        $this->assertFalse(Permission::can('staff_attendance'));
        $this->assertFalse(Permission::can('role_permission'));
    }

    public function testParentPermissions(): void {
        Session::set('user_id', 7);
        Session::set('role_name', 'parent');

        $this->assertTrue(Permission::canManage('admission'));
        $this->assertTrue(Permission::canManage('fees'));
        $this->assertTrue(Permission::canManage('transport'));

        $this->assertFalse(Permission::can('accounts'));
        $this->assertFalse(Permission::can('hr_management'));
        $this->assertFalse(Permission::can('role_permission'));
    }
}

