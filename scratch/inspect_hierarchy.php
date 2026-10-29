<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();

foreach ([1, 24] as $tid) {
    echo "==========================================" . PHP_EOL;
    echo "TENANT {$tid} ACADEMIC BREAKDOWN" . PHP_EOL;
    echo "==========================================" . PHP_EOL;
    
    $depts = $db->query("SELECT id, name, code FROM departments WHERE tenant_id = {$tid} ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "Departments count: " . count($depts) . PHP_EOL;
    foreach ($depts as $d) {
        echo "  [Dept #{$d['id']}] {$d['name']} ({$d['code']})" . PHP_EOL;
        $courses = $db->query("SELECT id, name, code, duration_years FROM courses WHERE tenant_id = {$tid} AND department_id = {$d['id']} ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo "    Courses count: " . count($courses) . PHP_EOL;
        foreach ($courses as $c) {
            echo "      [Course #{$c['id']}] {$c['name']} ({$c['code']}, {$c['duration_years']} yrs)" . PHP_EOL;
            // Count students in FY, SY, TY
            $levels = ['FY' => [1], 'SY' => [2], 'TY' => [3]];
            foreach (['FY' => 1, 'SY' => 2, 'TY' => 3] as $yrName => $yrOrder) {
                $stCount = $db->query("
                    SELECT COUNT(*) FROM students 
                    WHERE tenant_id = {$tid} AND course_id = {$c['id']} AND (standard_id = {$yrOrder} OR class_id = {$yrOrder})
                ")->fetchColumn();
                echo "        - {$yrName}: {$stCount} students" . PHP_EOL;
            }
        }
    }
}
