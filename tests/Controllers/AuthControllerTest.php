<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\AuthController;
use App\Core\Auth;
use App\Core\Session;

class AuthControllerTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testShowLoginViewExecution(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; (new App\Controllers\AuthController())->showLogin();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Login', $output);
    }

    public function testProcessLoginFailure(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; $_POST[\'username\'] = \'admin\'; $_POST[\'password\'] = \'wrong\'; (new App\Controllers\AuthController())->processLogin();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Invalid username/password', $output);
    }

    public function testLogoutAction(): void {
        Session::set('user_id', 1);
        $this->assertTrue(Auth::check());
        Auth::logout();
        $this->assertFalse(Auth::check());
    }

    public function testShowForgotPasswordView(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; (new App\Controllers\AuthController())->showForgotPassword();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Reset Password', $output);
    }

    public function testProcessForgotPasswordAndResetFlow(): void {
        $db = \App\Core\Database::getInstance();
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'admin'");

        // 1. Process Forgot Password
        $cmd1 = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; $_POST[\'identity\'] = \'admin\'; (new App\Controllers\AuthController())->processForgotPassword();"';
        $output1 = shell_exec($cmd1);
        $this->assertStringContainsString('token generated', $output1);

        // Fetch token from database
        $stmt = $db->query("SELECT token FROM password_resets WHERE email = 'admin@campus360.edu'");
        $row = $stmt->fetch();
        $this->assertTrue(!empty($row));
        $token = $row['token'];

        // 2. Process Reset Password
        $cmd2 = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; $_POST[\'token\'] = \'' . $token . '\'; $_POST[\'password\'] = \'NewPass@123\'; $_POST[\'confirm_password\'] = \'NewPass@123\'; (new App\Controllers\AuthController())->processResetPassword();"';
        $output2 = shell_exec($cmd2);
        $this->assertStringContainsString('successfully updated', $output2);

        // 3. Verify new password works
        $loginResult = Auth::attempt('admin', 'NewPass@123');
        $this->assertTrue($loginResult);

        // Cleanup & restore default admin password
        $db->exec("UPDATE users SET password_hash = '{$hash}', failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'admin'");
    }
}
