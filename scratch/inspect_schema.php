<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';
$db = \App\Core\Database::getInstance();
foreach (['departments', 'courses', 'standards', 'classes', 'subjects', 'students', 'online_exams'] as $tbl) {
    try {
        $cols = $db->query('SHOW COLUMNS FROM ' . $tbl)->fetchAll(PDO::FETCH_COLUMN);
        echo $tbl . ': ' . implode(', ', $cols) . PHP_EOL;
    } catch (Exception $e) {
        echo $tbl . ': NOT FOUND' . PHP_EOL;
    }
}
