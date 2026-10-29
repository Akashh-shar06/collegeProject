<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

// Check if courses 22, 41 or depts 120, 128 have dependent students or timetables
foreach ([22, 41] as $cid) {
    $st = $db->query("SELECT COUNT(*) FROM students WHERE course_id = $cid")->fetchColumn();
    $tt = $db->query("SELECT COUNT(*) FROM time_tables WHERE course_id = $cid")->fetchColumn();
    echo "Course $cid: students=$st, timetables=$tt\n";
    if ($st == 0 && $tt == 0) {
        $db->exec("DELETE FROM semesters WHERE course_id = $cid");
        $db->exec("DELETE FROM academic_levels WHERE course_id = $cid");
        $db->exec("DELETE FROM courses WHERE id = $cid");
        echo "Deleted old test course $cid\n";
    }
}

foreach ([120, 128] as $did) {
    $st = $db->query("SELECT COUNT(*) FROM students WHERE department_id = $did")->fetchColumn();
    $stf = $db->query("SELECT COUNT(*) FROM staff WHERE department_id = $did")->fetchColumn();
    $tt = $db->query("SELECT COUNT(*) FROM time_tables WHERE department_id = $did")->fetchColumn();
    echo "Dept $did: students=$st, staff=$stf, timetables=$tt\n";
    if ($st == 0 && $stf == 0 && $tt == 0) {
        $db->exec("DELETE FROM departments WHERE id = $did");
        echo "Deleted old test dept $did\n";
    }
}
