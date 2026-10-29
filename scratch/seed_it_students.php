<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

$sql = "INSERT INTO `students` (`id`, `tenant_id`, `user_id`, `admission_no`, `roll_no`, `first_name`, `last_name`, `gender`, `dob`, `phone`, `email`, `guardian_name`, `guardian_phone`, `batch_id`, `class_id`, `section_id`, `status`) VALUES
(2, 1, NULL, 'ADM-2025-002', '25IT001', 'Harsh', 'Singh', 'male', '2005-02-10', '+1 555-0102', 'harsh@campus360.edu', 'Vikram Singh', '+1 555-0103', 2, 1, 1, 'active'),
(3, 1, NULL, 'ADM-2025-003', '25IT002', 'Sarah', 'Connor', 'female', '2005-06-18', '+1 555-0104', 'sarah@campus360.edu', 'John Connor', '+1 555-0105', 2, 1, 1, 'active')
ON DUPLICATE KEY UPDATE `first_name` = VALUES(`first_name`)";

$db->exec($sql);
echo "IT Students seeded successfully!\n";
