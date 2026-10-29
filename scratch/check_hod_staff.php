<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();
$u = $db->query("SELECT u.id, u.username, u.role_id, r.name as role_name, r.display_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = 'anish11'")->fetch(PDO::FETCH_ASSOC);
print_r($u);
$st = $db->query("SELECT * FROM staff WHERE user_id = 121 OR email = 'Anish11@gmail.com'")->fetch(PDO::FETCH_ASSOC);
print_r($st);
