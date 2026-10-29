<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Permission;
use App\Core\Session;

class HierarchyIntegrityTest extends TestCase
{
    public function setUp(): void
    {
        Session::start();
        $_SESSION = [];
    }

    public function testSuperAdminHasCompleteFullAccess(): void
    {
        Session::set('role_name', 'super_admin');
        Session::set('user_id', 1);

        $systemAdminModules = ['role_permission', 'app_analysis', 'staff_management', 'leave_management', 'fees', 'accounts'];
        foreach ($systemAdminModules as $m) {
            $this->assertTrue(Permission::can($m), "Super Admin must have full access to {$m}");
        }
    }

    public function testPrincipalHasStaffManagementButNoTechnicalAdmin(): void
    {
        Session::set('role_name', 'principal');
        Session::set('user_id', 2);

        // Management modules MUST be accessible
        $allowed = ['staff_management', 'staff_data', 'staff_attendance', 'leave_management', 'task_management', 'hr_management', 'recruitment', 'exam_management'];
        foreach ($allowed as $m) {
            $this->assertTrue(Permission::can($m), "Principal must have access to staff manager module {$m}");
        }

        // Technical System Control MUST NOT be accessible
        $forbidden = ['role_permission', 'app_analysis'];
        foreach ($forbidden as $f) {
            $this->assertFalse(Permission::can($f), "Principal MUST NOT have technical admin module {$f}");
        }
    }

    public function testStaffTaskTableExists(): void
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("SHOW TABLES LIKE 'staff_tasks'");
        $this->assertEquals('staff_tasks', $stmt->fetchColumn(), "staff_tasks table must exist");
    }
}
