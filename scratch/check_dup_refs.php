<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$duplicateIds = array_merge(
    range(248, 272), // Adm 24
    [274, 275, 276, 278, 281, 285, 288], // TestStudent Promote
    [277, 279, 282, 286, 289] // Rajesh Kapadia
);

$refTables = [
    'admissions', 'attendance_records', 'certificates', 'classroom_remarks', 
    'exam_results', 'fee_collections', 'fee_installments', 'hostel_allocations', 
    'library_issues', 'online_exam_submissions', 'online_exams', 'student_attendance', 
    'student_enrollments', 'student_promotions'
];

foreach ($duplicateIds as $sid) {
    $foundRefs = [];
    foreach ($refTables as $tbl) {
        $cnt = (int)$db->query("SELECT COUNT(*) FROM `{$tbl}` WHERE student_id = {$sid}")->fetchColumn();
        if ($cnt > 0) {
            $foundRefs[] = "{$tbl}: {$cnt}";
        }
    }
    // Also check user_id
    $uid = $db->query("SELECT user_id FROM students WHERE id = {$sid}")->fetchColumn();
    if (!empty($foundRefs) || $uid) {
        echo "Student ID {$sid} (user_id: {$uid}): " . implode(', ', $foundRefs) . "\n";
    }
}
