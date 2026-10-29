<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$cols = $db->query("DESCRIBE students")->fetchAll();
foreach ($cols as $c) {
    echo "{$c['Field']} - {$c['Type']} - Null: {$c['Null']} - Key: {$c['Key']}\n";
}
