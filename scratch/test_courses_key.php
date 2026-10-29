<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Controllers\AcademicController;

$data = AcademicController::getAcademicHierarchyData(24, false);
foreach ($data['departments'] as $dept) {
    echo $dept['name'] . ' (ID: ' . $dept['id'] . '): courses set? ' . (isset($dept['courses']) ? 'YES (' . count($dept['courses']) . ')' : 'NO') . PHP_EOL;
}
