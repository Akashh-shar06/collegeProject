<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Controllers\TimetableController;

Auth::attempt('john_student', 'Admin@123');
$user = Auth::user();
echo "Auth User: " . print_r($user, true) . "\n";

$db = Database::getInstance();
$st = $db->prepare("SELECT s.*, b.course_id, c.department_id as course_dept_id FROM students s LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id WHERE s.user_id = :uid LIMIT 1");
$st->execute([':uid' => $user['id']]);
$studentScope = $st->fetch();
echo "Student Scope: " . print_r($studentScope, true) . "\n";

$tt = $db->query("SELECT * FROM time_tables")->fetchAll();
echo "All Timetables in DB: " . print_r($tt, true) . "\n";
