<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/TestHarness.php';

$tests = [
    'Core/SessionTest.php' => \Tests\Core\SessionTest::class,
    'Core/ValidatorTest.php' => \Tests\Core\ValidatorTest::class,
    'Core/CSRFTest.php' => \Tests\Core\CSRFTest::class,
    'Core/PermissionTest.php' => \Tests\Core\PermissionTest::class,
    'Core/RbacIntegrityTest.php' => \Tests\Core\RbacIntegrityTest::class,
    'Core/LeaveWorkflowTest.php' => \Tests\Core\LeaveWorkflowTest::class,
    'Core/HierarchyIntegrityTest.php' => \Tests\Core\HierarchyIntegrityTest::class,
    'Core/AuthTest.php' => \Tests\Core\AuthTest::class,
    'Core/DatabaseTest.php' => \Tests\Core\DatabaseTest::class,
    'Core/RouterTest.php' => \Tests\Core\RouterTest::class,
    'Core/ResponseTest.php' => \Tests\Core\ResponseTest::class,
    'Controllers/AuthControllerTest.php' => \Tests\Controllers\AuthControllerTest::class,
    'Controllers/DashboardControllerTest.php' => \Tests\Controllers\DashboardControllerTest::class,
    'Controllers/ModuleControllerTest.php' => \Tests\Controllers\ModuleControllerTest::class,
    'Controllers/WorkflowControllerTest.php' => \Tests\Controllers\WorkflowControllerTest::class,
    'Controllers/ApiControllerTest.php' => \Tests\Controllers\ApiControllerTest::class,
    'Controllers/OnlineExamControllerTest.php' => \Tests\Controllers\OnlineExamControllerTest::class,
    'Controllers/TimetableControllerTest.php' => \Tests\Controllers\TimetableControllerTest::class,
];

$logFile = __DIR__ . '/step_log.txt';
file_put_contents($logFile, "Starting step test...\n");

foreach ($tests as $file => $class) {
    file_put_contents($logFile, "Requiring {$file}...\n", FILE_APPEND);
    require_once __DIR__ . '/../tests/' . $file;
    file_put_contents($logFile, "Running {$class}...\n", FILE_APPEND);
    $runner = new \Tests\TestRunner();
    $runner->runClass($class);
    file_put_contents($logFile, "Finished {$class}\n", FILE_APPEND);
}

file_put_contents($logFile, "ALL COMPLETED SUCCESSFULLY\n", FILE_APPEND);
