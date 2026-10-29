<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$referencingTables = [];
foreach ($tables as $t) {
    $cols = $db->query("SHOW COLUMNS FROM `{$t}`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('student_id', $cols)) {
        $referencingTables[] = $t;
    }
}
echo "Tables with student_id:\n";
print_r($referencingTables);

// Check if foreign keys exist on students
$fks = $db->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'students'
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nForeign keys referencing students:\n";
print_r($fks);
