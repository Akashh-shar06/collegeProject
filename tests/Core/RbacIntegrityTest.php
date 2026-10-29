<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Permission;
use App\Core\Session;

class RbacIntegrityTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        $_SESSION = [];
        Session::set('user_id', 1);
    }


    public function testTeacherIsIsolatedToEightAcademicModules(): void
    {
        Session::set('user_id', 3);
        Session::set('role_name', 'teacher');

        $allowedModules = [
            'student_attendance',
            'exam_management_module',
            'time_table',
            'online_exam',
            'exam_management',
            'student_mis',
            'communication',
            'dashboard'
        ];

        foreach ($allowedModules as $mod) {
            $this->assertTrue(Permission::can($mod), "Teacher should have access to {$mod}");
        }

        $forbiddenModules = [
            'accounts', 'fees', 'hr_management', 'payroll', 'recruitment',
            'stock', 'asset_management', 'role_permission', 'admission', 'enquiry', 'staff_data'
        ];

        foreach ($forbiddenModules as $mod) {
            $this->assertFalse(Permission::can($mod), "Teacher MUST NOT have access to {$mod}");
        }
    }

    public function testJobRoleMatricesIntegrity(): void
    {
        // 1. Administrative Officer
        Session::set('role_name', 'admin_officer');
        $this->assertTrue(Permission::can('enquiry'));
        $this->assertTrue(Permission::can('admission'));
        $this->assertTrue(Permission::can('id_cards'));
        $this->assertTrue(Permission::can('certificate'));
        $this->assertFalse(Permission::can('payroll'));
        $this->assertFalse(Permission::can('role_permission'));

        // 2. Accountant
        Session::set('role_name', 'accountant');
        $this->assertTrue(Permission::can('fees'));
        $this->assertTrue(Permission::can('accounts'));
        $this->assertTrue(Permission::can('payroll'));
        $this->assertFalse(Permission::can('hr_management'));
        $this->assertFalse(Permission::can('role_permission'));

        // 3. HR Executive
        Session::set('role_name', 'hr_executive');
        $this->assertTrue(Permission::can('hr_management'));
        $this->assertTrue(Permission::can('staff_data'));
        $this->assertTrue(Permission::can('staff_attendance'));
        $this->assertTrue(Permission::can('leave_management'));
        $this->assertTrue(Permission::can('payroll'));
        $this->assertTrue(Permission::can('recruitment'));
        $this->assertFalse(Permission::can('exam_management'));
        $this->assertFalse(Permission::can('role_permission'));

        // 4. Librarian
        Session::set('role_name', 'librarian');
        $this->assertTrue(Permission::can('library'));
        $this->assertFalse(Permission::can('fees'));

        // 5. Transport & Hostel Admin
        Session::set('role_name', 'transport_hostel_admin');
        $this->assertTrue(Permission::can('transport'));
        $this->assertTrue(Permission::can('hostel_management'));
        $this->assertTrue(Permission::can('day_boarding'));
        $this->assertFalse(Permission::can('payroll'));

        // 6. Inventory Manager
        Session::set('role_name', 'inventory_manager');
        $this->assertTrue(Permission::can('stock'));
        $this->assertTrue(Permission::can('asset_management'));
        $this->assertFalse(Permission::can('payroll'));

        // 7. Receptionist
        Session::set('role_name', 'receptionist');
        $this->assertTrue(Permission::can('enquiry'));
        $this->assertTrue(Permission::can('visitor'));
        $this->assertFalse(Permission::can('payroll'));

        // 8. ERP Admin
        Session::set('role_name', 'erp_admin');
        $this->assertTrue(Permission::can('role_permission'));
        $this->assertTrue(Permission::can('app_analysis'));

        // 9. Student
        Session::set('role_name', 'student');
        $this->assertTrue(Permission::can('fees'));
        $this->assertFalse(Permission::canManage('fees'));
        $this->assertFalse(Permission::can('accounts'));
        $this->assertFalse(Permission::can('role_permission'));

        // 10. Parent
        Session::set('role_name', 'parent');
        $this->assertTrue(Permission::can('fees'));
        $this->assertFalse(Permission::can('accounts'));
        $this->assertFalse(Permission::can('role_permission'));
    }

    public function testActionPermissions(): void
    {
        Session::set('role_name', 'student');
        $this->assertTrue(Permission::canAction('fees', 'view'));
        $this->assertFalse(Permission::canAction('fees', 'create'));
        $this->assertFalse(Permission::canAction('fees', 'delete'));

        Session::set('role_name', 'principal');
        $this->assertTrue(Permission::canAction('fees', 'view'));
        $this->assertTrue(Permission::canAction('fees', 'create'));
        $this->assertTrue(Permission::canAction('fees', 'edit'));
        $this->assertTrue(Permission::canAction('fees', 'delete'));
    }
}
