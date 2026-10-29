<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/TestHarness.php';
require_once __DIR__ . '/../tests/Controllers/ModuleControllerTest.php';

$logFile = __DIR__ . '/mod_log.txt';
file_put_contents($logFile, "Starting ModuleControllerTest detail...\n");

$test = new \Tests\Controllers\ModuleControllerTest();
$methods = get_class_methods($test);

foreach ($methods as $m) {
    if (str_starts_with($m, 'test')) {
        file_put_contents($logFile, "Running {$m}...\n", FILE_APPEND);
        try {
            $test->setUp();
            $test->$m();
            $test->tearDown();
            file_put_contents($logFile, "Passed {$m}\n", FILE_APPEND);
        } catch (\Throwable $e) {
            file_put_contents($logFile, "Failed {$m}: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

file_put_contents($logFile, "ModuleControllerTest Done!\n", FILE_APPEND);
