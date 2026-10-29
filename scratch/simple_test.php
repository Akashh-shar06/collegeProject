<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Starting bootstrap check...\n";
require_once __DIR__ . '/../tests/bootstrap.php';
echo "Bootstrap completed successfully.\n";
