<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

$sql = "INSERT INTO `notices` (`id`, `tenant_id`, `title`, `content`, `target_role`, `posted_by`, `publish_date`, `status`) VALUES
(1, 1, 'Welcome to Semester 1 Academic Year 2026', 'Classes begin promptly at 09:00 AM. All students and faculty are requested to complete registration.', 'all', 1, '2026-08-01', 'published'),
(2, 1, 'Midterm Exam Schedule & Guidelines', 'Midterm examination schedules have been published under the Academic Schedule tab.', 'student', 1, '2026-08-10', 'published'),
(3, 1, 'Faculty & Staff Monthly Briefing', 'All staff members are invited to attend the monthly institutional strategy briefing in Auditorium A.', 'teacher', 1, '2026-08-15', 'published')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`)";

$db->exec($sql);
echo "Notices seeded successfully!\n";
