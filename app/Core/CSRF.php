<?php
namespace App\Core;

/**
 * Session-backed CSRF token generation and validation.
 *
 * Never trust a token supplied by the request when the session has no token:
 * only the application may create the expected token.
 */
class CSRF {
    public static function getToken(): string {
        Session::start();
        $token = Session::get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function generate(): string {
        return self::getToken();
    }

    public static function validate(?string $token): bool {
        if (!is_string($token) || $token === '') {
            return false;
        }

        Session::start();
        $sessionToken = Session::get('csrf_token');

        // The expected value must already exist in the server-side session.
        // Accepting an incoming value as the expected value enables CSRF bypass.
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function inputField(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
