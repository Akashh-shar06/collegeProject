<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();

$moreStudents = [
    // Course 17 (BSCCS_24) in Science Dept
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 1, 'first' => 'Siddharth', 'last' => 'Rao', 'roll' => 'FYCS-01', 'adm' => 'ADM-24-FYCS01', 'email' => 'siddharth.fycs@college.edu'],
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 1, 'first' => 'Tanvi', 'last' => 'Kadam', 'roll' => 'FYCS-02', 'adm' => 'ADM-24-FYCS02', 'email' => 'tanvi.fycs@college.edu'],
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 2, 'first' => 'Varun', 'last' => 'Iyer', 'roll' => 'SYCS-01', 'adm' => 'ADM-24-SYCS01', 'email' => 'varun.sycs@college.edu'],
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 2, 'first' => 'Divya', 'last' => 'Menon', 'roll' => 'SYCS-02', 'adm' => 'ADM-24-SYCS02', 'email' => 'divya.sycs@college.edu'],
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 3, 'first' => 'Sameer', 'last' => 'Kapoor', 'roll' => 'TYCS-01', 'adm' => 'ADM-24-TYCS01', 'email' => 'sameer.tycs@college.edu'],
    ['course' => 'BSCCS_24', 'dept' => 116, 'year' => 3, 'first' => 'Shruti', 'last' => 'Soman', 'roll' => 'TYCS-02', 'adm' => 'ADM-24-TYCS02', 'email' => 'shruti.tycs@college.edu'],

    // Course 19 (BMS_24) in Commerce Dept
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 1, 'first' => 'Aryan', 'last' => 'Gupta', 'roll' => 'FYBMS-01', 'adm' => 'ADM-24-FYBMS01', 'email' => 'aryan.fybms@college.edu'],
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 1, 'first' => 'Kavya', 'last' => 'Sen', 'roll' => 'FYBMS-02', 'adm' => 'ADM-24-FYBMS02', 'email' => 'kavya.fybms@college.edu'],
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 2, 'first' => 'Nikhil', 'last' => 'Bose', 'roll' => 'SYBMS-01', 'adm' => 'ADM-24-SYBMS01', 'email' => 'nikhil.sybms@college.edu'],
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 2, 'first' => 'Shreya', 'last' => 'Das', 'roll' => 'SYBMS-02', 'adm' => 'ADM-24-SYBMS02', 'email' => 'shreya.sybms@college.edu'],
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 3, 'first' => 'Kunal', 'last' => 'Bhatia', 'roll' => 'TYBMS-01', 'adm' => 'ADM-24-TYBMS01', 'email' => 'kunal.tybms@college.edu'],
    ['course' => 'BMS_24', 'dept' => 117, 'year' => 3, 'first' => 'Isha', 'last' => 'Malhotra', 'roll' => 'TYBMS-02', 'adm' => 'ADM-24-TYBMS02', 'email' => 'isha.tybms@college.edu'],
];

$passHash = password_hash('Student@123', PASSWORD_BCRYPT);
$studentRoleId = 5;
$classes = $db->query("SELECT id, code FROM classes WHERE tenant_id = 24")->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($moreStudents as $s) {
    $cid = (int)$db->query("SELECT id FROM courses WHERE tenant_id = 24 AND code = '{$s['course']}' LIMIT 1")->fetchColumn();
    $yrCode = $s['year'] === 1 ? 'FY' : ($s['year'] === 2 ? 'SY' : 'TY');
    $classId = $classes[$yrCode] ?? $s['year'];

    $stCheck = $db->prepare("SELECT id FROM students WHERE tenant_id = 24 AND admission_no = :adm LIMIT 1");
    $stCheck->execute([':adm' => $s['adm']]);
    if (!$stCheck->fetchColumn()) {
        $uName = strtolower(str_replace(['-', ' '], '_', $s['adm']));
        $uIns = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (24, :u, :em, :p, :r, 'active')");
        $uIns->execute([
            ':u' => $uName,
            ':em' => $s['email'],
            ':p' => $passHash,
            ':r' => $studentRoleId
        ]);
        $userId = (int)$db->lastInsertId();

        $stIns = $db->prepare("
            INSERT INTO students 
            (tenant_id, user_id, unique_id, admission_no, roll_no, first_name, last_name, email, phone, gender, dob, department_id, course_id, standard_id, class_id, batch_id, status)
            VALUES 
            (24, :uid, :uniq, :adm, :roll, :fn, :ln, :em, '+91 9876543210', 'female', '2005-08-20', :did, :cid, :sid, :clsid, 1, 'active')
        ");
        $stIns->execute([
            ':uid' => $userId,
            ':uniq' => "MU2026-{$s['roll']}",
            ':adm' => $s['adm'],
            ':roll' => $s['roll'],
            ':fn' => $s['first'],
            ':ln' => $s['last'],
            ':em' => $s['email'],
            ':did' => $s['dept'],
            ':cid' => $cid,
            ':sid' => $s['year'],
            ':clsid' => $classId
        ]);
        $stId = (int)$db->lastInsertId();

        $db->exec("INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, standard_id, class_id, status) VALUES (24, {$stId}, '2026-2027', {$cid}, {$s['dept']}, {$s['year']}, {$classId}, 'active')");
        echo "Created student {$s['first']} {$s['last']} ({$yrCode}) in course {$s['course']}" . PHP_EOL;
    }
}
echo "Done!" . PHP_EOL;
