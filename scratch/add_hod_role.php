<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    // Check if hod role exists
    $check = $db->query("SELECT id FROM roles WHERE name = 'hod' OR display_name LIKE '%HOD%'")->fetch();
    
    if (!$check) {
        $stmt = $db->prepare("INSERT INTO roles (name, display_name, description) VALUES ('hod', 'HOD / Head of Department', 'Head of Academic Department management, faculty oversight & curriculum')");
        $stmt->execute();
        echo "SUCCESS: Inserted HOD role into `roles` table.\n";
    } else {
        echo "NOTICE: HOD role already exists with ID: " . $check['id'] . "\n";
    }

    $roles = $db->query("SELECT id, name, display_name FROM roles ORDER BY id ASC")->fetchAll();
    echo "Current Database Roles:\n";
    foreach ($roles as $r) {
        echo " - ID {$r['id']}: {$r['name']} ({$r['display_name']})\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
