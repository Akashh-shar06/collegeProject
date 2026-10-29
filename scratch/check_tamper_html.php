<?php
require_once __DIR__ . '/../tests/bootstrap.php';
\App\Core\Session::start();
\App\Core\Auth::attempt('anish11', 'Admin@123');
$_GET['dept_id'] = 117;
ob_start();
(new \App\Controllers\DashboardController())->showDashboard();
$out = ob_get_clean();
if (preg_match('/<strong>Department:<\/strong>(.*?)&bull;/s', $out, $m)) {
    echo "MATCH: " . trim($m[1]) . PHP_EOL;
} else {
    echo "NO MATCH" . PHP_EOL;
}
