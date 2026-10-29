<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Controllers\AcademicController;

$data = AcademicController::getAcademicHierarchyData(24, false);
$json = json_encode($data['departments'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
echo "JSON length: " . strlen($json) . " bytes\n";
$decoded = json_decode($json, true);
echo "Decoded departments count: " . count($decoded) . "\n";
foreach ($decoded as $d) {
    echo "Dept: " . $d['name'] . " (" . count($d['courses']) . " courses)\n";
    foreach ($d['courses'] as $c) {
        $fyCount = count($c['years'][0]['students'] ?? []);
        $syCount = count($c['years'][1]['students'] ?? []);
        $tyCount = count($c['years'][2]['students'] ?? []);
        echo "  - Course {$c['name']}: FY={$fyCount}, SY={$syCount}, TY={$tyCount}\n";
    }
}
