<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();

echo "=== ONLINE EXAMS SCHEMA ===\n";
$stmt = $db->query("DESCRIBE online_exams");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' (' . $row['Type'] . ') Null:' . $row['Null'] . "\n";
}

echo "\n=== ONLINE EXAM QUESTIONS SCHEMA ===\n";
$stmt = $db->query("DESCRIBE online_exam_questions");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' (' . $row['Type'] . ') Null:' . $row['Null'] . "\n";
}

echo "\n=== ONLINE EXAM SUBMISSIONS SCHEMA ===\n";
$stmt = $db->query("DESCRIBE online_exam_submissions");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' (' . $row['Type'] . ') Null:' . $row['Null'] . "\n";
}

echo "\n=== ACADEMIC HIERARCHY TABLES & COLUMNS ===\n";
foreach (['departments', 'courses', 'batches', 'classes', 'standards', 'academic_years', 'sections', 'students'] as $t) {
    echo "Table: $t\n";
    try {
        $st = $db->query("DESCRIBE $t");
        $cols = [];
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) { $cols[] = $r['Field']; }
        echo "  " . implode(', ', $cols) . "\n";
    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
