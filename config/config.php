<?php
/**
 * Campus360 Enterprise ERP - Main Configuration
 */

// Define Base Paths safely
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
if (!defined('APP_PATH')) define('APP_PATH', BASE_PATH . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', BASE_PATH . '/storage');

// Load Environment Variables manually if .env exists
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, '"\' ');
    }
}

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Campus360 Enterprise ERP',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/collegeProject/public',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'name' => $_ENV['DB_NAME'] ?? 'college_erp_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'security' => [
        'max_login_attempts' => 5,
        'lockout_time_seconds' => 900, // 15 Minutes
        'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token',
    ]
];
