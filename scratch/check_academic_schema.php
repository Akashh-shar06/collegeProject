<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
echo "=== academic_standards schema ===" . PHP_EOL;
$stmt = $db->query("DESCRIBE academic_standards");
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$col['Field']} ({$col['Type']}) " . PHP_EOL;
}
echo "=== academic_standards rows ===" . PHP_EOL;
print_r($db->query("SELECT * FROM academic_standards LIMIT 20")->fetchAll(PDO::FETCH_ASSOC));
