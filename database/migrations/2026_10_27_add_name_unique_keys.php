<?php
/**
 * Migration: Add unique constraints for course name within department & tenant,
 * and department name within tenant.
 * Date: 2026-10-27
 */
require_once __DIR__ . '/../../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

try {
    $db->exec("ALTER TABLE courses ADD UNIQUE KEY unique_tenant_dept_course_name (tenant_id, department_id, name)");
    echo "Courses unique key added.\n";
} catch (\PDOException $e) {
    echo "Courses key: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE departments ADD UNIQUE KEY unique_tenant_dept_name (tenant_id, name)");
    echo "Departments unique key added.\n";
} catch (\PDOException $e) {
    echo "Departments key: " . $e->getMessage() . "\n";
}
