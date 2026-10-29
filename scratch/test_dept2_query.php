<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/tests/bootstrap.php';

use App\Core\Session;
use App\Core\Database;

Session::start();
Session::set('user_id', 2);
Session::set('tenant_id', 1);
Session::set('role_name', 'school_admin');
Session::set('selected_dept_id', 2);

$db = Database::getInstance();
$selectedDeptId = Session::get('selected_dept_id');

$stmt = $db->prepare("SELECT s.* FROM students s WHERE s.batch_id IN (SELECT id FROM batches WHERE course_id IN (SELECT id FROM courses WHERE department_id = :did))");
$stmt->execute([':did' => $selectedDeptId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($rows);
