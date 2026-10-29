<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\Validator;

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            Response::redirect('index.php?page=dashboard');
        }
        Response::view('auth/login');
    }

    public function processLogin(): void {
        $username = Validator::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Response::view('auth/login', ['error' => 'Please fill in all credentials.']);
            return;
        }

        if (Auth::attempt($username, $password)) {
            Response::redirect('index.php?page=dashboard');
        } else {
            Response::view('auth/login', ['error' => 'Invalid username/password or account locked due to failed attempts.']);
        }
    }

    public function logout(): void {
        Auth::logout();
        Response::redirect('index.php?page=login');
    }

    public function showForgotPassword(): void {
        if (Auth::check()) {
            Response::redirect('index.php?page=dashboard');
        }
        Response::view('auth/forgot_password');
    }

    public function processForgotPassword(): void {
        $identity = Validator::sanitize($_POST['identity'] ?? '');

        if (empty($identity)) {
            Response::view('auth/forgot_password', ['error' => 'Please enter your username or registered email address.']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT email, username FROM users WHERE username = :id1 OR email = :id2 LIMIT 1");
        $stmt->execute([':id1' => $identity, ':id2' => $identity]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::view('auth/forgot_password', ['error' => 'No user account found with that username or email address.']);
            return;
        }

        $token = bin2hex(random_bytes(32));

        // Delete existing reset tokens for this email
        $delStmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $delStmt->execute([':email' => $user['email']]);

        // Insert new token with 1 hour expiration using MySQL server time
        $insStmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $insStmt->execute([
            ':email' => $user['email'],
            ':token' => $token
        ]);

        $resetUrl = "index.php?page=reset_password&token={$token}";

        Response::view('auth/forgot_password', [
            'success' => "Password reset token generated for user '{$user['username']}'.",
            'resetUrl' => $resetUrl,
            'token'    => $token
        ]);
    }

    public function showResetPassword(): void {
        $token = Validator::sanitize($_GET['token'] ?? '');

        if (empty($token)) {
            Response::view('auth/forgot_password', ['error' => 'Invalid password reset link.']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Response::view('auth/forgot_password', ['error' => 'Invalid or expired password reset link. Please request a new one.']);
            return;
        }

        Response::view('auth/reset_password', ['token' => $token]);
    }

    public function processResetPassword(): void {
        $token = Validator::sanitize($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            Response::view('auth/forgot_password', ['error' => 'Invalid reset session. Please request a new reset link.']);
            return;
        }

        if (empty($password) || strlen($password) < 6 || $password !== $confirmPassword) {
            Response::view('auth/reset_password', [
                'token' => $token,
                'error' => 'Passwords must match and be at least 6 characters long.'
            ]);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Response::view('auth/forgot_password', ['error' => 'Invalid or expired password reset link. Please request a new one.']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $updStmt = $db->prepare("UPDATE users SET password_hash = :hash, failed_attempts = 0, status = 'active', lock_until = NULL WHERE email = :email");
        $updStmt->execute([
            ':hash'  => $hash,
            ':email' => $reset['email']
        ]);

        $delStmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $delStmt->execute([':email' => $reset['email']]);

        Response::view('auth/login', [
            'success' => 'Your password has been successfully updated! You can now sign in with your new password.'
        ]);
    }
}
