<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Auth.php';
require_once BASE_PATH . '/app/Core/Session.php';
require_once BASE_PATH . '/app/Core/Permission.php';
require_once BASE_PATH . '/app/Controllers/OnlineExamController.php';

\App\Core\Session::start();
$db = \App\Core\Database::getInstance();

echo "=== ALL EXISTING EXAMS IN DB ===\n";
$stmt = $db->query("SELECT e.*, (SELECT COUNT(*) FROM online_exam_questions WHERE exam_id = e.id) as q_cnt, (SELECT COUNT(*) FROM online_exam_submissions WHERE exam_id = e.id) as sub_cnt FROM online_exams e");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . "\n";
}
