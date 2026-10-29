<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();

echo "--- STAFF TABLE COLUMNS ---\n";
$cols = $db->query("SHOW COLUMNS FROM staff")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "{$col['Field']} - {$col['Type']} - Null:{$col['Null']} - Default:{$col['Default']}\n";
}

echo "\n--- ALL STAFF RECORDS ---\n";
$stmt = $db->query("SELECT s.*, u.username FROM staff s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.id DESC");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($staff);
