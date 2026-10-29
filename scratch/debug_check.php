<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        file_put_contents(__DIR__ . '/debug_err.txt', var_export($err, true));
    }
});

file_put_contents(__DIR__ . '/debug_start.txt', 'started at ' . date('Y-m-d H:i:s'));

try {
    require_once __DIR__ . '/../tests/bootstrap.php';
    file_put_contents(__DIR__ . '/debug_boot.txt', 'bootstrap done');
} catch (\Throwable $e) {
    file_put_contents(__DIR__ . '/debug_boot_err.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
}
