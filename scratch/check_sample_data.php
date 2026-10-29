<?php
require_once __DIR__ . '/../tests/bootstrap.php';

$db = App\Core\Database::getInstance();
$cols = $db->query('DESCRIBE time_tables')->fetchAll(PDO::FETCH_ASSOC);
echo "=== TIME_TABLES COLUMNS ===\n";
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

$rows = $db->query('SELECT * FROM time_tables LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== FIRST ROWS ===\n";
print_r($rows);
