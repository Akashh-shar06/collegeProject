<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== TESTING SALARY UPDATE ===\n";

$staffId = 85;
$newSalary = 35000.50;

$stmt = $db->prepare("UPDATE staff SET salary = :sal WHERE id = :id");
$res = $stmt->execute([':sal' => $newSalary, ':id' => $staffId]);

echo "Direct SQL Execute result: " . ($res ? 'SUCCESS' : 'FAILED') . "\n";

$updatedSal = $db->query("SELECT salary FROM staff WHERE id = {$staffId}")->fetchColumn();
echo "Salary in DB now: {$updatedSal}\n";
