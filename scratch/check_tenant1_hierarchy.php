<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Controllers\AcademicController;

$data = AcademicController::getAcademicHierarchyData(1, false);
echo "Tenant 1 Hierarchy:\n";
echo "Departments: " . count($data['departments']) . "\n";
echo "Stats: " . json_encode($data['stats'], JSON_PRETTY_PRINT) . "\n";
