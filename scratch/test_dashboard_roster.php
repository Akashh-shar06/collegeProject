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

echo "Dashboard HTML Length: " . strlen($html) . "\n";

$checks = [
    'Academic Hierarchy Card' => strpos($html, 'Institutional Academic Structure & Student Hierarchy') !== false,
    'Live Hierarchy JSON' => strpos($html, 'id="academicHierarchyJson"') !== false,
    'Student Roster Modal' => strpos($html, 'id="modalStudentRoster"') !== false,
    'Quick Enroll Modal' => strpos($html, 'id="modalEnrollStudent"') !== false,
    'openCourseRoster function' => strpos($html, 'function openCourseRoster(') !== false,
    'openInstitutionCohortRoster function' => strpos($html, 'function openInstitutionCohortRoster(') !== false,
    'openDepartmentRoster function' => strpos($html, 'function openDepartmentRoster(') !== false,
    'Cohort FY Pill Button' => strpos($html, "onclick=\"openCourseRoster(") !== false,
    'Search input field' => strpos($html, 'id="rosterSearchInput"') !== false,
    'Export CSV button' => strpos($html, 'exportRosterToCSV()') !== false,
    'Print button' => strpos($html, 'printRosterTable()') !== false,
];

$allPassed = true;
foreach ($checks as $name => $passed) {
    if ($passed) {
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name}\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\nALL CHECKS PASSED SUCCESSFULLY!\n";
} else {
    echo "\nSOME CHECKS FAILED!\n";
    exit(1);
}
