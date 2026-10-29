<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

$students = $db->query("
    SELECT s.id, s.tenant_id, s.first_name, s.last_name, s.email, s.phone, s.roll_no, s.admission_no, 
           s.course_id, c.name as course_name, s.standard_id, s.status, s.user_id
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE s.tenant_id = 24
    ORDER BY s.course_id, s.standard_id, s.first_name, s.last_name
")->fetchAll(PDO::FETCH_ASSOC);

echo "Tenant 24 Students (" . count($students) . "):\n";
foreach ($students as $s) {
    echo sprintf(
        "ID: %-4d | Course: %-25s | Std: %s | Roll: %-12s | Adm: %-18s | Name: %s %s | Email: %s\n",
        $s['id'],
        substr($s['course_name'] ?? 'N/A', 0, 25),
        $s['standard_id'] ?? '-',
        $s['roll_no'] ?? '-',
        $s['admission_no'] ?? '-',
        $s['first_name'],
        $s['last_name'],
        $s['email']
    );
}
