<?php
ob_start();
try {
    require_once __DIR__ . '/../run_tests.php';
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
$out = ob_get_clean();
file_put_contents(__DIR__ . '/test_results.txt', $out);
echo "DONE writing file: " . strlen($out) . " bytes\n";
echo $out;
