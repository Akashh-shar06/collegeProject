<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();

echo "=== REFERENTIAL CONSTRAINTS FOR EXAMS ===\n";
$stmt = $db->query("
    SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME, DELETE_RULE, UPDATE_RULE 
    FROM information_schema.REFERENTIAL_CONSTRAINTS 
    WHERE REFERENCED_TABLE_NAME = 'online_exams' OR TABLE_NAME LIKE 'online_exam%'
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . "\n";
}
