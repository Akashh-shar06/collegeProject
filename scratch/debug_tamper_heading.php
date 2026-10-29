<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Core\Auth;
use App\Core\Session;
use App\Controllers\DashboardController;

Session::start();
Auth::attempt('anish11', 'Admin@123');

$_GET['dept_id'] = 117;
ob_start();
$dc = new DashboardController();
$dc->showDashboard();
$tamperedOutput = ob_get_clean();

// Check if Science Department is in the output
echo "Science Dept pos: " . (strpos($tamperedOutput, 'Science Department') !== false ? 'YES' : 'NO') . "\n";
echo "Commerce Dept - Executive Dashboard pos: " . (strpos($tamperedOutput, 'Commerce Department - Executive Dashboard') !== false ? 'YES' : 'NO') . "\n";
echo "Science Dept - Executive Dashboard pos: " . (strpos($tamperedOutput, 'Science Department - Executive Dashboard') !== false ? 'YES' : 'NO') . "\n";

// Let's print the actual heading in tamperedOutput
if (preg_match('/<h4[^>]*>.*?<\/h4>/is', $tamperedOutput, $m)) {
    echo "Found heading: " . strip_tags($m[0]) . "\n";
}
