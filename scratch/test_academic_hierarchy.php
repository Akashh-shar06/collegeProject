<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Auth.php';
require_once BASE_PATH . '/app/Core/Session.php';
require_once BASE_PATH . '/app/Core/Permission.php';
require_once BASE_PATH . '/app/Controllers/AcademicController.php';

\App\Core\Session::start();
$db = \App\Core\Database::getInstance();

echo "=== TESTING getAcademicHierarchyData FOR TENANT 24 ===\n";
try {
    $data24 = \App\Controllers\AcademicController::getAcademicHierarchyData(24, false, null);
    echo "Stats Tenant 24: " . json_encode($data24['stats']) . "\n";
    echo "Departments count: " . count($data24['departments']) . "\n";
    foreach ($data24['departments'] as $d) {
        echo "  Dept: {$d['name']} (ID: {$d['id']}), Courses: " . count($d['courses']) . ", Students: {$d['student_count']}\n";
        foreach ($d['courses'] as $c) {
            echo "    Course: {$c['name']} (ID: {$c['id']}), Years: " . count($c['years']) . "\n";
            foreach ($c['years'] as $y) {
                echo "      Year: {$y['code']} ({$y['name']}), Students: " . count($y['students']) . "\n";
            }
        }
    }
} catch (\Throwable $e) {
    echo "ERROR Tenant 24: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTING getAcademicHierarchyData FOR TENANT 1 ===\n";
try {
    $data1 = \App\Controllers\AcademicController::getAcademicHierarchyData(1, false, null);
    echo "Stats Tenant 1: " . json_encode($data1['stats']) . "\n";
    echo "Departments count: " . count($data1['departments']) . "\n";
    foreach ($data1['departments'] as $d) {
        echo "  Dept: {$d['name']} (ID: {$d['id']}), Courses: " . count($d['courses']) . ", Students: {$d['student_count']}\n";
    }
} catch (\Throwable $e) {
    echo "ERROR Tenant 1: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
