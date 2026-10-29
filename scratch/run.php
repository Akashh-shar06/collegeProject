<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

$log = [];
$log[] = "Starting tests...";

require_once __DIR__ . '/../tests/bootstrap.php';
$log[] = "Bootstrap done.";

require_once __DIR__ . '/../tests/TestHarness.php';

$testFiles = glob(__DIR__ . '/../tests/{Core,Controllers}/*.php', GLOB_BRACE);
foreach ($testFiles as $f) {
    if (basename($f) === 'bootstrap.php' || basename($f) === 'TestHarness.php') continue;
    require_once $f;
    $log[] = "Loaded " . basename($f);
}

$allTestClasses = [
    \Tests\Core\SessionTest::class,
    \Tests\Core\ValidatorTest::class,
    \Tests\Core\CSRFTest::class,
    \Tests\Core\PermissionTest::class,
    \Tests\Core\RbacIntegrityTest::class,
    \Tests\Core\LeaveWorkflowTest::class,
    \Tests\Core\HierarchyIntegrityTest::class,
    \Tests\Core\AuthTest::class,
    \Tests\Core\DatabaseTest::class,
    \Tests\Core\RouterTest::class,
    \Tests\Core\ResponseTest::class,
    \Tests\Controllers\AuthControllerTest::class,
    \Tests\Controllers\DashboardControllerTest::class,
    \Tests\Controllers\ModuleControllerTest::class,
    \Tests\Controllers\WorkflowControllerTest::class,
    \Tests\Controllers\ApiControllerTest::class,
    \Tests\Controllers\OnlineExamControllerTest::class,
    \Tests\Controllers\TimetableControllerTest::class,
];

$runner = new \Tests\TestRunner();
foreach ($allTestClasses as $class) {
    $runner->runClass($class);
}

ob_start();
$runner->renderReport();
$report = ob_get_clean();

$log[] = $report;

file_put_contents(__DIR__ . '/report.txt', implode("\n", $log));
echo "FINISHED";
