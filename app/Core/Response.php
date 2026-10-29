<?php
namespace App\Core;

class Response {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (defined('TESTING_MODE') && TESTING_MODE) {
            return;
        }
        exit;
    }

    public static function view(string $viewPath, array $data = []): void {
        extract($data);
        $fullPath = BASE_PATH . '/app/Views/' . $viewPath . '.php';
        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            echo "View file standard not found: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
        }
        if (defined('TESTING_MODE') && TESTING_MODE) {
            return;
        }
        exit;
    }

    public static function redirect(string $url): void {
        if (defined('TESTING_MODE') && TESTING_MODE) {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: " . $url);
        exit;
    }
}
