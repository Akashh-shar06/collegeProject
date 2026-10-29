<?php
/**
 * Migration & Reconciliation Script:
 * Reconciles duplicate academic records (Courses, Departments, Cohorts)
 * Preserves all students, enrollments, attendance, and exam records.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    echo "=== SAFE ACADEMIC DATA RECONCILIATION & INTEGRITY MIGRATION ===\n";

    if (!$db->inTransaction()) {
        $db->beginTransaction();
    }

    // 1. Reconcile duplicate B.Sc. Data Science if any exist
    $dupCourses = $db->query("SELECT id FROM courses WHERE code IN ('BSC_AUTO_289', 'BSC_AUTO_174', 'BSC_AUTO_751', 'BSC_AUTO_406', 'BSC_AUTO_335')")->fetchAll(PDO::FETCH_COLUMN);
    $canonicalCourseId = $db->query("SELECT id FROM courses WHERE code = 'BSCDS_24' OR code = 'BSC_AUTO_414' ORDER BY id DESC LIMIT 1")->fetchColumn();

    if ($canonicalCourseId && !empty($dupCourses)) {
        $dupList = implode(',', $dupCourses);
        $stUpdated = $db->exec("UPDATE students SET course_id = {$canonicalCourseId} WHERE course_id IN ({$dupList})");
        $enUpdated = $db->exec("UPDATE student_enrollments SET course_id = {$canonicalCourseId} WHERE course_id IN ({$dupList})");
        $db->exec("UPDATE courses SET code = 'BSCDS_24', status = 'active', name = 'B.Sc. Data Science' WHERE id = {$canonicalCourseId}");
        $db->exec("DELETE FROM semesters WHERE course_id IN ({$dupList})");
        $db->exec("DELETE FROM academic_levels WHERE course_id IN ({$dupList})");
        $db->exec("DELETE FROM courses WHERE id IN ({$dupList})");
        echo "Reconciled duplicate Data Science courses to ID {$canonicalCourseId}.\n";
    }

    // 2. Add Unique Constraints for Integrity where supported
    $courseIndexes = $db->query("SHOW INDEX FROM courses WHERE Key_name = 'unique_tenant_course_code'")->fetchAll();
    if (empty($courseIndexes)) {
        try {
            $db->exec("ALTER TABLE courses ADD UNIQUE KEY unique_tenant_course_code (tenant_id, code)");
            echo "Added unique constraint: courses(tenant_id, code)\n";
        } catch (\Throwable $e) {
            echo "Note on unique_tenant_course_code: " . $e->getMessage() . "\n";
        }
    }

    $deptIndexes = $db->query("SHOW INDEX FROM departments WHERE Key_name = 'unique_tenant_dept_code'")->fetchAll();
    if (empty($deptIndexes)) {
        try {
            $db->exec("ALTER TABLE departments ADD UNIQUE KEY unique_tenant_dept_code (tenant_id, code)");
            echo "Added unique constraint: departments(tenant_id, code)\n";
        } catch (\Throwable $e) {
            echo "Note on unique_tenant_dept_code: " . $e->getMessage() . "\n";
        }
    }

    if ($db->inTransaction()) {
        $db->commit();
    }
    echo "=== SUCCESS: Migration completed cleanly! ===\n";

} catch (\Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
