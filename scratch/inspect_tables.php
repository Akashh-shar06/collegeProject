<?php
require_once __DIR__ . '/../tests/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== ALL TABLES IN DATABASE ===\n";
echo json_encode($tables, JSON_PRETTY_PRINT) . "\n";

foreach (['academic_sessions', 'academic_levels', 'classes', 'sections', 'subjects', 'subject_offerings', 'student_promotions', 'time_tables', 'classrooms', 'student_enrollments', 'teacher_assignments', 'students'] as $tbl) {
    if (in_array($tbl, $tables)) {
        echo "\n=== SCHEMA FOR $tbl ===\n";
        $cols = $db->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "{$c['Field']} ({$c['Type']})\n";
        }
    }
}
