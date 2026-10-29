<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Session;
use App\Controllers\AcademicController;

Session::start();
Auth::attempt('dsjc', 'Admin@123');

ob_start();
$controller = new AcademicController();
$controller->showAcademicHierarchy();
$html = ob_get_clean();

echo "Rendered HTML length: " . strlen($html) . " bytes\n";
if (strpos($html, 'Science Department') !== false && strpos($html, 'Commerce Department') !== false) {
    echo "SUCCESS: Both Science and Commerce Departments rendered in HTML!\n";
} else {
    echo "FAILURE: Departments not found in rendered HTML!\n";
}

if (strpos($html, 'FYIT-01') !== false && strpos($html, 'Aryan Sharma') !== false) {
    echo "SUCCESS: FY students found in rendered HTML!\n";
} else {
    echo "FAILURE: FY students not found in HTML!\n";
}
