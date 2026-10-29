<?php
/**
 * Migration: Add unique constraints to cohorts (batches), academic sessions, and academic levels
 * Date: 2026-10-27
 */
require_once __DIR__ . '/../../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

try {
    $db->exec("ALTER TABLE batches ADD UNIQUE KEY unique_tenant_course_batch (tenant_id, course_id, batch_name)");
    echo "Batches unique key added.\n";
} catch (\PDOException $e) {
    echo "Batches: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE academic_sessions ADD UNIQUE KEY unique_tenant_session_name (tenant_id, name)");
    echo "Academic sessions unique key added.\n";
} catch (\PDOException $e) {
    echo "Sessions: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE academic_levels ADD UNIQUE KEY unique_tenant_course_level (tenant_id, course_id, level_order)");
    echo "Academic levels unique key added.\n";
} catch (\PDOException $e) {
    echo "Levels: " . $e->getMessage() . "\n";
}
