<?php
define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
$db = Database::getInstance();

$dupCoursesT24 = [28, 30, 32, 34];
$canonicalCourseT24 = 24;

echo "=== CANONICAL COURSE 24 LEVELS & SEMESTERS ===\n";
$lvl24 = $db->query("SELECT * FROM academic_levels WHERE course_id = 24")->fetchAll(PDO::FETCH_ASSOC);
print_r($lvl24);
$sem24 = $db->query("SELECT * FROM semesters WHERE course_id = 24")->fetchAll(PDO::FETCH_ASSOC);
print_r($sem24);

echo "=== CHECK REFERENCES TO DUPLICATE SEMESTERS/LEVELS ===\n";
$inDup = implode(',', $dupCoursesT24);
$dupSemesters = $db->query("SELECT id FROM semesters WHERE course_id IN ($inDup)")->fetchAll(PDO::FETCH_COLUMN);
$dupLevels = $db->query("SELECT id FROM academic_levels WHERE course_id IN ($inDup)")->fetchAll(PDO::FETCH_COLUMN);

echo "Dup Semesters: " . implode(',', $dupSemesters) . "\n";
echo "Dup Levels: " . implode(',', $dupLevels) . "\n";

foreach (['students', 'time_tables', 'online_exams'] as $tbl) {
    if (!empty($dupSemesters)) {
        $inSem = implode(',', $dupSemesters);
        $scnt = $db->query("SELECT semester_id, COUNT(*) as cnt FROM $tbl WHERE semester_id IN ($inSem) GROUP BY semester_id")->fetchAll(PDO::FETCH_ASSOC);
        echo "$tbl referencing dup semesters:\n";
        print_r($scnt);
    }
}
