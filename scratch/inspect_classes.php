<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/tests/bootstrap.php';
$db = \App\Core\Database::getInstance();
$rows = $db->query('SELECT * FROM classes')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Tenant: {$r['tenant_id']}, Name: {$r['name']}, Code: {$r['code']}\n";
}
