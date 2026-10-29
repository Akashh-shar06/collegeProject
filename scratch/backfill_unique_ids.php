<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $students = $db->query("SELECT id, tenant_id, unique_id FROM students ORDER BY id ASC")->fetchAll();
    
    $tenantCounters = [];

    foreach ($students as $st) {
        if (empty($st['unique_id'])) {
            $tid = (int)$st['tenant_id'];
            if (!isset($tenantCounters[$tid])) {
                $tenantCounters[$tid] = 1;
            } else {
                $tenantCounters[$tid]++;
            }
            $numStr = str_pad((string)$tenantCounters[$tid], 2, '0', STR_PAD_LEFT);
            $uid = "MU2026-{$numStr}";

            $up = $db->prepare("UPDATE students SET unique_id = :uid WHERE id = :id");
            $up->execute([':uid' => $uid, ':id' => $st['id']]);
            echo "Updated Student ID {$st['id']} with Unique ID: {$uid}\n";
        }
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
