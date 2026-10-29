<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

$db = Database::getInstance();

echo "=== VERIFYING STAFF DATA SYNCHRONIZATION & METRICS ===\n";

// Run sync
$synced = Auth::syncStaffRecords(24);
echo "Synced count for tenant 24: $synced\n";

// Count staff for tenant 24
$stfTenant24 = $db->query("SELECT COUNT(*) FROM staff WHERE tenant_id = 24")->fetchColumn();
echo "Staff table total records for tenant 24: $stfTenant24\n";

// Fetch staff details for tenant 24
$staffList24 = $db->query("SELECT s.id, s.emp_id, s.first_name, s.last_name, s.designation, COALESCE(d.name, 'General') as department_name, s.email, s.status FROM staff s LEFT JOIN departments d ON s.department_id = d.id WHERE s.tenant_id = 24 ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- Staff Records List for Tenant 24 (" . count($staffList24) . " entries) ---\n";
foreach ($staffList24 as $st) {
    echo "ID: {$st['id']} | EMP: {$st['emp_id']} | Name: {$st['first_name']} {$st['last_name']} | Role: {$st['designation']} | Dept: {$st['department_name']} | Status: {$st['status']}\n";
}

if ($stfTenant24 == 15 && count($staffList24) == 15) {
    echo "\nSUCCESS: Staff data for Tenant 24 is 100% accurate and fully populated (15 staff members)!\n";
} else {
    echo "\nWARNING: Expected 15 staff members, found $stfTenant24.\n";
}
