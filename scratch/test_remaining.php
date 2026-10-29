<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/TestHarness.php';

$testClasses = [
    'WorkflowController' => \Tests\Controllers\WorkflowControllerTest::class,
    'ApiController' => \Tests\Controllers\ApiControllerTest::class,
    'OnlineExamController' => \Tests\Controllers\OnlineExamControllerTest::class,
    'TimetableController' => \Tests\Controllers\TimetableControllerTest::class,
];

$logFile = __DIR__ . '/rem_log.txt';
file_put_contents($logFile, "Starting remaining tests...\n");

foreach ($testClasses as $name => $class) {
    file_put_contents($logFile, "Loading {$name}...\n", FILE_APPEND);
    $file = __DIR__ . '/../tests/Controllers/' . $name . 'Test.php';
    require_once $file;

    $runner = new \Tests\TestRunner();
    $res = $runner->runClass($class);
    
    file_put_contents($logFile, "Finished {$name}: Passed {$res['passed']}, Failed {$res['failed']}\n", FILE_APPEND);
}

file_put_contents($logFile, "Done with remaining tests!\n", FILE_APPEND);
