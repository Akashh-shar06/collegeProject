<?php
namespace App\Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $config = require BASE_PATH . '/config/config.php';
            $sessionConfig = $config['session'];

            session_set_cookie_params([
                'lifetime' => $sessionConfig['lifetime'],
                'path' => '/',
                'domain' => '',
                'secure' => $sessionConfig['secure'],
                'httponly' => $sessionConfig['httponly'],
                'samesite' => $sessionConfig['samesite']
            ]);

            session_start();
        } elseif (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function setFlash(string $key, string $message): void {
        self::set('flash_' . $key, $message);
    }

    public static function getFlash(string $key): ?string {
        $val = self::get('flash_' . $key);
        if ($val !== null) {
            self::remove('flash_' . $key);
        }
        return $val;
    }

    public static function regenerate(): void {
        self::start();
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(false);
        }
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
