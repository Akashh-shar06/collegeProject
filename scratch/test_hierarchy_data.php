<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Controllers\AcademicController;

$data = AcademicController::getAcademicHierarchyData(24, false);
echo "Tenant 24 Hierarchy:\n";
echo "Departments: " . count($data['departments']) . "\n";
echo "Stats: " . json_encode($data['stats'], JSON_PRETTY_PRINT) . "\n";
foreach ($data['departments'] as $d) {
    echo "\n=== DEPT: {$d['name']} ({$d['code']}) ===\n";
    foreach ($d['courses'] as $c) {
        echo "  - COURSE: {$c['name']} ({$c['code']})\n";
        foreach ($c['years'] as $y) {
            echo "     * YEAR {$y['code']} ({$y['name']}): " . count($y['students']) . " students\n";
            foreach ($y['students'] as $s) {
                echo "         Student: {$s['first_name']} {$s['last_name']} (Roll: {$s['roll_no']}, Adm: {$s['admission_no']})\n";
            }
        }
    }
}
