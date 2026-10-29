<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Core\Auth;
use App\Core\Session;
class DashboardControllerTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
        $db = \App\Core\Database::getInstance();
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL");
    }
    public function testSuperAdminDashboardRender(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); (new App\Controllers\DashboardController())->showDashboard();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Super', $output);
    }

    public function testTeacherDashboardRender(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'dr_soap\', \'Admin@123\'); (new App\Controllers\DashboardController())->showDashboard();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Teacher', $output);
    }

    public function testAccountantDashboardRender(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'accountant_mary\', \'Admin@123\'); (new App\Controllers\DashboardController())->showDashboard();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Accountant', $output);
    }

    public function testToggleTenantStatus(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_POST[\'tenant_id\'] = 1; $_POST[\'status\'] = \'suspended\'; (new App\Controllers\DashboardController())->toggleTenantStatus();"';
        shell_exec($cmd);
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("SELECT status FROM tenants WHERE id = 1");
        $tenant = $stmt->fetch();
        $this->assertEquals('suspended', $tenant['status']);

        // Restore active status
        $db->exec("UPDATE tenants SET status = 'active' WHERE id = 1");
    }

    public function testDeleteTenant(): void {
        $db = \App\Core\Database::getInstance();
        $db->exec("INSERT INTO tenants (name, code, domain, status) VALUES ('Test Delete Inst', 'DEL99', 'del.test.edu', 'active')");
        $tenantId = (int) $db->lastInsertId();

        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_POST[\'tenant_id\'] = ' . $tenantId . '; (new App\Controllers\DashboardController())->deleteTenant();"';
        shell_exec($cmd);

        $stmt = $db->prepare("SELECT * FROM tenants WHERE id = :id");
        $stmt->execute([':id' => $tenantId]);
        $row = $stmt->fetch();
        $this->assertTrue($row === false || $row === null);
    }
}
