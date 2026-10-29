<?php
define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$db = App\Core\Database::getInstance();
$sql = "SELECT s.id, s.admission_no, s.admission_no as student_id, s.roll_no, s.first_name, s.last_name, CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as full_name, s.photo, s.gender, s.dob, s.email, s.phone, s.phone as student_phone, s.guardian_name, s.guardian_phone, s.guardian_phone as father_mobile, s.mother_phone, s.mother_phone as mother_mobile, COALESCE(d.name, 'General') as department, COALESCE(d.name, 'General') as department_name, COALESCE(c.name, '') as course_name, CASE WHEN s.standard_id = 1 THEN '1st Year (FY)' WHEN s.standard_id = 2 THEN '2nd Year (SY)' WHEN s.standard_id = 3 THEN '3rd Year (TY)' WHEN s.standard_id = 4 THEN '4th Year (Final)' ELSE '1st Year (FY)' END as student_year, COALESCE(cl.name, 'Class A') as class_name, s.status, u.username as student_username FROM students s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN classes cl ON s.class_id = cl.id LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON b.course_id = c.id ORDER BY s.id DESC LIMIT 10";

$records = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach ($records as $idx => $stRec) {
    $stDisplayName = trim(($stRec['first_name'] ?? '') . ' ' . ($stRec['last_name'] ?? ''));
    if (empty($stDisplayName) && !empty($stRec['full_name'])) {
        $stDisplayName = $stRec['full_name'];
    }
    if (empty($stDisplayName)) {
        $stDisplayName = 'Student #' . ($stRec['id'] ?? $idx + 1);
    }
    $stCode = !empty($stRec['admission_no']) ? $stRec['admission_no'] : (!empty($stRec['roll_no']) ? $stRec['roll_no'] : (!empty($stRec['student_id']) ? $stRec['student_id'] : 'N/A'));
    echo $idx . ": " . $stDisplayName . " (" . $stCode . ") | Photo: " . ($stRec['photo'] ?? 'None') . "\n";
}

