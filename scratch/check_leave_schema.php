<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();
$cols = $db->query("DESCRIBE leave_requests")->fetchAll(PDO::FETCH_ASSOC);
echo "=== LEAVE_REQUESTS COLUMNS ===\n";
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

$colsAudit = $db->query("DESCRIBE leave_audit_logs")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== LEAVE_AUDIT_LOGS COLUMNS ===\n";
foreach ($colsAudit as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}
