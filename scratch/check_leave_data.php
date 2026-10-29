<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$r1 = $db->query("SELECT COUNT(*) FROM leave_balances")->fetchColumn();
$r2 = $db->query("SELECT COUNT(*) FROM leave_types")->fetchColumn();
$r3 = $db->query("SELECT COUNT(*) FROM leave_audit_logs")->fetchColumn();
echo "leave_balances count: $r1\n";
echo "leave_types count: $r2\n";
echo "leave_audit_logs count: $r3\n";

$colsBal = $db->query("DESCRIBE leave_balances")->fetchAll(PDO::FETCH_ASSOC);
echo "=== LEAVE_BALANCES COLUMNS ===\n";
foreach ($colsBal as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

$colsTypes = $db->query("DESCRIBE leave_types")->fetchAll(PDO::FETCH_ASSOC);
echo "=== LEAVE_TYPES COLUMNS ===\n";
foreach ($colsTypes as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}
