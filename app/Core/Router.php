<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $page = $_GET['page'] ?? null;

        if (!$page) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
            $path = trim($uri, '/');
            if (($pos = strrpos($path, 'public/')) !== false) {
                $path = substr($path, $pos + 7);
            } elseif (($pos = strrpos($path, '/')) !== false) {
                $path = substr($path, $pos + 1);
            }
            $page = (!empty($path) && $path !== 'index.php') ? $path : 'dashboard';
        }

        // Check CSRF for state-changing requests
        if ($method === 'POST') {
            $token = $_POST['csrf_token'] ?? $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!CSRF::validate($token)) {
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
                
                if ($isAjax) {
                    Response::json(['error' => 'Invalid or expired CSRF token.'], 403);
                } else {
                    Session::setFlash('error', 'Security token expired or invalid. Please try again.');
                    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard';
                    Response::redirect($referer);
                }
            }
        }

        if (isset($this->routes[$method][$page])) {
            list($controllerClass, $action) = $this->routes[$method][$page];
            $controller = new $controllerClass();
            $controller->$action();
        } else {
            // Default Fallback
            if (in_array($page, ['login', 'login_process', 'register_tenant', 'forgot_password', 'forgot_password_process', 'reset_password', 'reset_password_process'], true)) {
                (new \App\Controllers\AuthController())->showLogin();
            } else if (Auth::check()) {
                (new \App\Controllers\DashboardController())->showDashboard();
            } else {
                (new \App\Controllers\AuthController())->showLogin();
            }
        }
    }
}
