<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$teachers = $db->query("SELECT u.id, u.username, u.role_id, s.id as staff_id, s.department_id, s.first_name, s.last_name FROM users u JOIN staff s ON u.id = s.user_id WHERE u.tenant_id = 24")->fetchAll();
print_r($teachers);

$tt_teachers = $db->query("SELECT DISTINCT staff_id FROM time_tables WHERE tenant_id = 24")->fetchAll(PDO::FETCH_COLUMN);
echo "Staff IDs in time_tables for tenant 24: " . implode(',', $tt_teachers) . "\n";
foreach ($tt_teachers as $sid) {
    $stf = $db->query("SELECT s.id, s.first_name, s.last_name, u.username FROM staff s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = $sid")->fetch();
    echo "Staff $sid: " . ($stf['username'] ?? 'No user') . " (" . $stf['first_name'] . " " . $stf['last_name'] . ")\n";
}
