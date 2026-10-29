<?php
require_once __DIR__ . '/../tests/bootstrap.php';

$db = \App\Core\Database::getInstance();
foreach (['departments', 'courses', 'batches', 'academic_sessions', 'academic_levels'] as $tbl) {
    echo "=== $tbl ===" . PHP_EOL;
    $stmt = $db->query("SHOW INDEX FROM $tbl");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Key_name']} -> {$row['Column_name']} (Non_unique: {$row['Non_unique']})" . PHP_EOL;
    }
}
