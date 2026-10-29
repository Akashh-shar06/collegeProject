<?php
require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();

echo "Updating empty phone numbers and 0.00 salaries in staff table...\n";

$staffList = $db->query("SELECT id, first_name, last_name, designation, phone, salary FROM staff")->fetchAll(PDO::FETCH_ASSOC);

$samplePhones = [
    '9876543210', '9876543211', '9876543212', '9876543213', '9876543214',
    '9876543215', '9876543216', '9876543217', '9876543218', '9876543219',
    '9819001122', '9820003344', '9833005566', '9844007788', '9855009900'
];

$defaultSalaries = [
    'Teacher' => 55000.00,
    'Professor' => 75000.00,
    'Lecturer' => 48000.00,
    'Administrative' => 40000.00,
    'Accountant' => 42000.00,
    'HR' => 45000.00,
    'Librarian' => 35000.00,
    'Transport' => 38000.00,
    'Store' => 32000.00,
    'Receptionist' => 28000.00,
    'ERP' => 50000.00,
    'Principal' => 95000.00
];

foreach ($staffList as $idx => $stf) {
    $id = $stf['id'];
    $phone = trim($stf['phone'] ?? '');
    $salary = (float)($stf['salary'] ?? 0);
    $desig = $stf['designation'] ?? '';

    $newPhone = $phone;
    if (empty($phone)) {
        $newPhone = $samplePhones[$idx % count($samplePhones)];
    }

    $newSalary = $salary;
    if ($salary <= 0) {
        $assigned = 45000.00;
        foreach ($defaultSalaries as $key => $val) {
            if (stripos($desig, $key) !== false) {
                $assigned = $val;
                break;
            }
        }
        $newSalary = $assigned;
    }

    $stmt = $db->prepare("UPDATE staff SET phone = :phone, salary = :salary WHERE id = :id");
    $stmt->execute([':phone' => $newPhone, ':salary' => $newSalary, ':id' => $id]);
    echo "Updated Staff ID {$id} ({$stf['first_name']} {$stf['last_name']}): Phone = {$newPhone}, Salary = ₹{$newSalary}\n";
}

echo "Staff table data update completed.\n";
