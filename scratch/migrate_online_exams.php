<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();

$cols = $db->query("SHOW COLUMNS FROM `online_exams`")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('department_id', $cols)) {
    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `department_id` INT NULL AFTER `tenant_id`");
    echo "Added department_id\n";
}
if (!in_array('academic_year', $cols)) {
    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `academic_year` VARCHAR(20) NULL AFTER `course_id`");
    echo "Added academic_year\n";
}
if (!in_array('standard_id', $cols)) {
    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `standard_id` INT NULL AFTER `academic_year`");
    echo "Added standard_id\n";
}
if (!in_array('subject_id', $cols)) {
    $db->exec("ALTER TABLE `online_exams` ADD COLUMN `subject_id` INT NULL AFTER `standard_id`");
    echo "Added subject_id\n";
}

echo "Columns in online_exams now:\n";
$st = $db->query("DESCRIBE online_exams");
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']})\n";
}
