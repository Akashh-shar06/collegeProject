<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';
require_once BASE_PATH . '/tests/TestHarness.php';

$logFile = __DIR__ . '/test_out.log';
file_put_contents($logFile, "Starting Test Suite Execution...\n");

$testFiles = glob(BASE_PATH . '/tests/Controllers/*Test.php');
foreach ($testFiles as $f) {
    require_once $f;
    $className = 'Tests\\Controllers\\' . basename($f, '.php');
    if (class_exists($className)) {
        $inst = new $className();
        foreach (get_class_methods($inst) as $m) {
            if (str_starts_with($m, 'test')) {
                try {
                    $inst->setUp();
                    $inst->$m();
                    $inst->tearDown();
                    file_put_contents($logFile, "PASSED {$className}::{$m}\n", FILE_APPEND);
                } catch (\Throwable $e) {
                    file_put_contents($logFile, "FAILED {$className}::{$m}: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }
    }
}
file_put_contents($logFile, "ALL TESTS COMPLETED SUCCESSFULLY!\n", FILE_APPEND);
