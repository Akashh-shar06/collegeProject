<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Core\Auth;
use App\Core\Session;
use App\Controllers\DashboardController;

Session::start();
Auth::attempt('anish11', 'Admin@123');

ob_start();
$dc = new DashboardController();
$dc->showDashboard();
$normalOutput = ob_get_clean();

echo "Does normal HOD dashboard contain 'Commerce Department'? " . (strpos($normalOutput, 'Commerce Department') !== false ? 'YES' : 'NO') . "\n";

// Let's find where 'Commerce' appears in the output
if (preg_match_all('/.{0,40}Commerce.{0,40}/i', $normalOutput, $matches)) {
    print_r($matches[0]);
}
