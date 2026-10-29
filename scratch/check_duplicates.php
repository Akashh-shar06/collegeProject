<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$students = $db->query("SELECT id, tenant_id, user_id, first_name, middle_name, last_name, email, phone, roll_no, admission_no, course_id, standard_id FROM students ORDER BY tenant_id, course_id, first_name, last_name, id")->fetchAll(PDO::FETCH_ASSOC);

echo "Total students in DB: " . count($students) . "\n\n";

// Group by tenant_id, and lower(trim(concat(first_name, ' ', last_name))) or email or phone
$grouped = [];
foreach ($students as $s) {
    $name = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
    $key = $s['tenant_id'] . '|' . strtolower($name);
    $grouped[$key][] = $s;
}

$duplicatesFound = 0;
foreach ($grouped as $k => $list) {
    if (count($list) > 1) {
        $duplicatesFound += count($list) - 1;
        echo "Duplicate Group [{$k}]: " . count($list) . " records\n";
        foreach ($list as $st) {
            echo "  ID: {$st['id']}, Name: {$st['first_name']} {$st['last_name']}, Email: {$st['email']}, Phone: {$st['phone']}, Roll: {$st['roll_no']}, Adm: {$st['admission_no']}, Course: {$st['course_id']}, Std: {$st['standard_id']}\n";
        }
    }
}

echo "\nTotal duplicate entries: {$duplicatesFound}\n";
