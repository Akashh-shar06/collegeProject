<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Session;
use App\Controllers\DashboardController;

Session::start();
Auth::attempt('dsjc', 'Admin@123');

ob_start();
$controller = new DashboardController();
$controller->showDashboard();
$html = ob_get_clean();

echo "Rendered Dashboard HTML length: " . strlen($html) . " bytes\n";
if (strpos($html, 'Institutional Academic Structure & Student Hierarchy') !== false) {
    echo "SUCCESS: Academic Structure & Student Hierarchy card found on School Admin dashboard!\n";
} else {
    echo "FAILURE: Hierarchy card not found on dashboard!\n";
}

if (strpos($html, 'Science Department') !== false && strpos($html, 'Commerce Department') !== false) {
    echo "SUCCESS: Both departments found on School Admin dashboard!\n";
} else {
    echo "FAILURE: Departments not found on dashboard!\n";
}
