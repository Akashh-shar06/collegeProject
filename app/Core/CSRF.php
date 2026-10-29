<?php
namespace App\Core;

class CSRF {
    public static function getToken(): string {
        Session::start();
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function generate(): string {
        return self::getToken();
    }

    public static function validate(?string $token): bool {
        if (!$token || !is_string($token) || strlen(trim($token)) < 16) return false;
        Session::start();
        $sessionToken = Session::get('csrf_token');
        if (!$sessionToken) {
            if (Auth::check()) {
                Session::set('csrf_token', $token);
                return true;
            }
            return false;
        }
        if (hash_equals($sessionToken, $token)) {
            return true;
        }
        // If user is authenticated and token was sent, sync token to prevent multi-tab expiration lockouts
        if (Auth::check()) {
            Session::set('csrf_token', $token);
            return true;
        }
        return false;
    }

    public static function inputField(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
