<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();

try {
    $db->exec("ALTER TABLE students ADD COLUMN mother_phone VARCHAR(20) NULL AFTER guardian_phone");
    echo "Column mother_phone added to students table.\n";
} catch (\Throwable $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}
