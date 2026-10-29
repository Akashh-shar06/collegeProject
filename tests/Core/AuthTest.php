<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Auth;
use App\Core\Session;

class AuthTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];

        // Reset test user state in DB
        $db = \App\Core\Database::getInstance();
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, failed_attempts = 0, status = 'active', lock_until = NULL WHERE username = 'admin'");
        $stmt->execute([':hash' => $hash]);
    }

    public function testAuthAttemptWithValidCredentials(): void {
        $result = Auth::attempt('admin', 'Admin@123');
        $this->assertTrue($result);
        $this->assertTrue(Auth::check());

        $user = Auth::user();
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user['username']);
        $this->assertEquals('super_admin', $user['role_name']);
    }

    public function testAuthAttemptWithInvalidCredentials(): void {
        $result = Auth::attempt('admin', 'WrongPassword!99');
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function testLogoutClearsSession(): void {
        Auth::attempt('admin', 'Admin@123');
        $this->assertTrue(Auth::check());

        Auth::logout();
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());
    }
}
