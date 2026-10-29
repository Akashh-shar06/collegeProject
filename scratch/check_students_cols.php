<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$cols = $db->query("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_ASSOC);

echo "--- STUDENTS TABLE COLUMNS ---\n";
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}
