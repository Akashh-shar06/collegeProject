<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();

echo "=== SEEDING ACADEMIC HIERARCHY (DEPT -> COURSES -> FY/SY/TY -> STUDENTS) ===" . PHP_EOL;

// 1. Ensure Classes (FY, SY, TY) exist for tenant 24 and tenant 1
foreach ([1, 24] as $tid) {
    $existingClasses = $db->query("SELECT id, name, code FROM classes WHERE tenant_id = {$tid}")->fetchAll(PDO::FETCH_ASSOC);
    $classMap = [];
    foreach ($existingClasses as $ec) {
        $classMap[strtoupper($ec['code'])] = $ec['id'];
    }

    $defaultClasses = [
        ['name' => 'First Year (FY)', 'code' => 'FY'],
        ['name' => 'Second Year (SY)', 'code' => 'SY'],
        ['name' => 'Third Year (TY)', 'code' => 'TY'],
    ];

    foreach ($defaultClasses as $dc) {
        if (!isset($classMap[$dc['code']])) {
            $ins = $db->prepare("INSERT INTO classes (tenant_id, name, code) VALUES (:tid, :name, :code)");
            $ins->execute([':tid' => $tid, ':name' => $dc['name'], ':code' => $dc['code']]);
            echo "Created Class {$dc['code']} for tenant {$tid}" . PHP_EOL;
        }
    }
}

// 2. Setup Tenant 24 (Science Dept 116 & Commerce Dept 117)
$t24Depts = $db->query("SELECT id, name, code FROM departments WHERE tenant_id = 24 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Tenant 24 departments: " . count($t24Depts) . PHP_EOL;

$scienceDept = null;
$commerceDept = null;
foreach ($t24Depts as $d) {
    if (stripos($d['name'], 'science') !== false) {
        $scienceDept = $d;
    } elseif (stripos($d['name'], 'commerce') !== false) {
        $commerceDept = $d;
    }
}

if (!$scienceDept && count($t24Depts) > 0) {
    $scienceDept = $t24Depts[0];
}
if (!$commerceDept && count($t24Depts) > 1) {
    $commerceDept = $t24Depts[1];
}

echo "Science Dept ID: " . ($scienceDept['id'] ?? 'none') . PHP_EOL;
echo "Commerce Dept ID: " . ($commerceDept['id'] ?? 'none') . PHP_EOL;

// Ensure Courses for Tenant 24
$coursesToSeed = [
    [
        'tenant_id' => 24,
        'department_id' => $scienceDept['id'],
        'name' => 'B.Sc. Information Technology',
        'code' => 'BSCIT_24',
        'duration_years' => 3,
        'description' => 'Bachelor of Science in Information Technology - 3 Year Program'
    ],
    [
        'tenant_id' => 24,
        'department_id' => $scienceDept['id'],
        'name' => 'B.Sc. Computer Science',
        'code' => 'BSCCS_24',
        'duration_years' => 3,
        'description' => 'Bachelor of Science in Computer Science - 3 Year Program'
    ],
    [
        'tenant_id' => 24,
        'department_id' => $commerceDept['id'],
        'name' => 'Bachelor of Commerce',
        'code' => 'BCOM_24',
        'duration_years' => 3,
        'description' => 'Bachelor of Commerce (General & Financial) - 3 Year Program'
    ],
    [
        'tenant_id' => 24,
        'department_id' => $commerceDept['id'],
        'name' => 'Bachelor of Management Studies',
        'code' => 'BMS_24',
        'duration_years' => 3,
        'description' => 'Bachelor of Management Studies - 3 Year Program'
    ],
];

foreach ($coursesToSeed as $cData) {
    $cCheck = $db->prepare("SELECT id FROM courses WHERE tenant_id = :tid AND code = :code LIMIT 1");
    $cCheck->execute([':tid' => $cData['tenant_id'], ':code' => $cData['code']]);
    $courseId = $cCheck->fetchColumn();

    if (!$courseId) {
        $insC = $db->prepare("INSERT INTO courses (tenant_id, department_id, name, code, duration_years, description, status) VALUES (:tid, :did, :name, :code, :dur, :desc, 'active')");
        $insC->execute([
            ':tid' => $cData['tenant_id'],
            ':did' => $cData['department_id'],
            ':name' => $cData['name'],
            ':code' => $cData['code'],
            ':dur' => $cData['duration_years'],
            ':desc' => $cData['description']
        ]);
        $courseId = (int)$db->lastInsertId();
        echo "Created Course {$cData['name']} (ID: {$courseId})" . PHP_EOL;
    } else {
        $courseId = (int)$courseId;
        echo "Course {$cData['name']} already exists (ID: {$courseId})" . PHP_EOL;
    }

    // Seed 3 Years in academic_levels for this course
    $yearLevels = [
        ['name' => 'First Year (FY)', 'order' => 1, 'desc' => 'FY - Semester 1 & 2'],
        ['name' => 'Second Year (SY)', 'order' => 2, 'desc' => 'SY - Semester 3 & 4'],
        ['name' => 'Third Year (TY)', 'order' => 3, 'desc' => 'TY - Semester 5 & 6'],
    ];

    foreach ($yearLevels as $yl) {
        $lvlCheck = $db->prepare("SELECT id FROM academic_levels WHERE tenant_id = :tid AND course_id = :cid AND level_order = :ord LIMIT 1");
        $lvlCheck->execute([':tid' => $cData['tenant_id'], ':cid' => $courseId, ':ord' => $yl['order']]);
        $lvlId = $lvlCheck->fetchColumn();
        if (!$lvlId) {
            $insL = $db->prepare("INSERT INTO academic_levels (tenant_id, course_id, name, level_order, description) VALUES (:tid, :cid, :name, :ord, :desc)");
            $insL->execute([
                ':tid' => $cData['tenant_id'],
                ':cid' => $courseId,
                ':name' => $yl['name'],
                ':ord' => $yl['order'],
                ':desc' => $yl['desc']
            ]);
            $lvlId = (int)$db->lastInsertId();
        } else {
            $lvlId = (int)$lvlId;
        }

        // Ensure 2 semesters per year
        $sem1Num = ($yl['order'] * 2) - 1;
        $sem2Num = ($yl['order'] * 2);
        foreach ([$sem1Num, $sem2Num] as $semNum) {
            $semCheck = $db->prepare("SELECT id FROM semesters WHERE tenant_id = :tid AND course_id = :cid AND semester_number = :snum LIMIT 1");
            $semCheck->execute([':tid' => $cData['tenant_id'], ':cid' => $courseId, ':snum' => $semNum]);
            if (!$semCheck->fetchColumn()) {
                $insSem = $db->prepare("INSERT INTO semesters (tenant_id, course_id, academic_level_id, name, semester_number) VALUES (:tid, :cid, :alid, :name, :snum)");
                $insSem->execute([
                    ':tid' => $cData['tenant_id'],
                    ':cid' => $courseId,
                    ':alid' => $lvlId,
                    ':name' => "Semester {$semNum}",
                    ':snum' => $semNum
                ]);
            }
        }
    }
}

// 3. Seed Multiple Students in FY, SY, TY for Tenant 24
$studentsToSeed = [
    // B.Sc. IT - FY Students
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 1, 'first' => 'Aarav', 'last' => 'Patel', 'roll' => 'FYIT-01', 'adm' => 'ADM-24-FYIT01', 'email' => 'aarav.fyit@college.edu'],
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 1, 'first' => 'Priya', 'last' => 'Sharma', 'roll' => 'FYIT-02', 'adm' => 'ADM-24-FYIT02', 'email' => 'priya.fyit@college.edu'],
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 1, 'first' => 'Rohan', 'last' => 'Mehta', 'roll' => 'FYIT-03', 'adm' => 'ADM-24-FYIT03', 'email' => 'rohan.fyit@college.edu'],

    // B.Sc. IT - SY Students
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 2, 'first' => 'Ananya', 'last' => 'Deshmukh', 'roll' => 'SYIT-01', 'adm' => 'ADM-24-SYIT01', 'email' => 'ananya.syit@college.edu'],
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 2, 'first' => 'Karan', 'last' => 'Verma', 'roll' => 'SYIT-02', 'adm' => 'ADM-24-SYIT02', 'email' => 'karan.syit@college.edu'],

    // B.Sc. IT - TY Students
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 3, 'first' => 'Aditya', 'last' => 'Kulkarni', 'roll' => 'TYIT-01', 'adm' => 'ADM-24-TYIT01', 'email' => 'aditya.tyit@college.edu'],
    ['course' => 'BSCIT_24', 'dept' => $scienceDept['id'], 'year' => 3, 'first' => 'Neha', 'last' => 'Joshi', 'roll' => 'TYIT-02', 'adm' => 'ADM-24-TYIT02', 'email' => 'neha.tyit@college.edu'],

    // Commerce B.Com - FY Students
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 1, 'first' => 'Vikram', 'last' => 'Shah', 'roll' => 'FYCOM-01', 'adm' => 'ADM-24-FYCOM01', 'email' => 'vikram.fycom@college.edu'],
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 1, 'first' => 'Sneha', 'last' => 'Reddy', 'roll' => 'FYCOM-02', 'adm' => 'ADM-24-FYCOM02', 'email' => 'sneha.fycom@college.edu'],

    // Commerce B.Com - SY Students
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 2, 'first' => 'Harsh', 'last' => 'Trivedi', 'roll' => 'SYCOM-01', 'adm' => 'ADM-24-SYCOM01', 'email' => 'harsh.sycom@college.edu'],
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 2, 'first' => 'Pooja', 'last' => 'Nair', 'roll' => 'SYCOM-02', 'adm' => 'ADM-24-SYCOM02', 'email' => 'pooja.sycom@college.edu'],

    // Commerce B.Com - TY Students
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 3, 'first' => 'Manish', 'last' => 'Chawla', 'roll' => 'TYCOM-01', 'adm' => 'ADM-24-TYCOM01', 'email' => 'manish.tycom@college.edu'],
    ['course' => 'BCOM_24', 'dept' => $commerceDept['id'], 'year' => 3, 'first' => 'Ritu', 'last' => 'Bansal', 'roll' => 'TYCOM-02', 'adm' => 'ADM-24-TYCOM02', 'email' => 'ritu.tycom@college.edu'],
];

$passHash = password_hash('Student@123', PASSWORD_BCRYPT);
$studentRoleId = 5;

// Fetch FY, SY, TY classes in tenant 24
$classes = $db->query("SELECT id, code FROM classes WHERE tenant_id = 24")->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($studentsToSeed as $s) {
    // Look up course ID
    $cid = (int)$db->query("SELECT id FROM courses WHERE tenant_id = 24 AND code = '{$s['course']}' LIMIT 1")->fetchColumn();
    $yrCode = $s['year'] === 1 ? 'FY' : ($s['year'] === 2 ? 'SY' : 'TY');
    $classId = $classes[$yrCode] ?? $s['year'];

    // Check if student exists
    $stCheck = $db->prepare("SELECT id FROM students WHERE tenant_id = 24 AND admission_no = :adm LIMIT 1");
    $stCheck->execute([':adm' => $s['adm']]);
    $stId = $stCheck->fetchColumn();

    if (!$stId) {
        // Create user
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
            (24, :uid, :uniq, :adm, :roll, :fn, :ln, :em, '+91 9876543210', 'male', '2005-05-15', :did, :cid, :sid, :clsid, 1, 'active')
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
            ':sid' => $s['year'], // 1=FY, 2=SY, 3=TY
            ':clsid' => $classId
        ]);
        $stId = (int)$db->lastInsertId();

        // Enrollment record
        $db->exec("INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, standard_id, class_id, status) VALUES (24, {$stId}, '2026-2027', {$cid}, {$s['dept']}, {$s['year']}, {$classId}, 'active')");
        echo "Created student {$s['first']} {$s['last']} ({$yrCode}) in course {$s['course']}" . PHP_EOL;
    } else {
        echo "Student {$s['first']} {$s['last']} already exists." . PHP_EOL;
    }
}

echo "=== SEEDING COMPLETED SUCCESSFULLY ===" . PHP_EOL;
