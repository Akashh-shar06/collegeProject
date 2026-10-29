<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = App\Core\Database::getInstance();

$tenantId = 24;

// Ensure batch exists for tenant 24
$batchId = (int)$db->query("SELECT id FROM batches WHERE tenant_id = {$tenantId} LIMIT 1")->fetchColumn();
if (!$batchId) {
    $db->exec("INSERT INTO batches (tenant_id, course_id, batch_name, start_year, end_year) VALUES ({$tenantId}, 16, 'Academic Batch 2024-2027', 2024, 2027)");
    $batchId = (int)$db->lastInsertId();
}

// Clean any old test students in tenant 24 to have a fresh, pristine roster
$db->exec("DELETE FROM student_enrollments WHERE tenant_id = {$tenantId}");
$db->exec("DELETE FROM students WHERE tenant_id = {$tenantId}");

// Also clean up any extra test courses like CS01 if desired, or keep only 16, 17, 18, 19
$db->exec("DELETE FROM courses WHERE tenant_id = {$tenantId} AND id NOT IN (16, 17, 18, 19)");

// Ensure classes FY, SY, TY exist for tenant 24
$classes = [
    ['name' => 'First Year', 'code' => 'FY'],
    ['name' => 'Second Year', 'code' => 'SY'],
    ['name' => 'Third Year', 'code' => 'TY'],
];
foreach ($classes as $cl) {
    $exists = $db->query("SELECT id FROM classes WHERE tenant_id = {$tenantId} AND code = '{$cl['code']}'")->fetchColumn();
    if (!$exists) {
        $db->exec("INSERT INTO classes (tenant_id, name, code, status) VALUES ({$tenantId}, '{$cl['name']}', '{$cl['code']}', 'active')");
    }
}

// Cohorts definition: 4 Courses x 3 Years x 3 Students = 36 Students
$cohorts = [
    // Dept 116: Science Department
    // Course 16: B.Sc. IT
    16 => [
        1 => [ // FY
            ['first_name' => 'Aryan', 'last_name' => 'Sharma', 'gender' => 'male', 'roll' => 'FYIT-01'],
            ['first_name' => 'Neha', 'last_name' => 'Patil', 'gender' => 'female', 'roll' => 'FYIT-02'],
            ['first_name' => 'Rahul', 'last_name' => 'Varma', 'gender' => 'male', 'roll' => 'FYIT-03'],
        ],
        2 => [ // SY
            ['first_name' => 'Priya', 'last_name' => 'Nair', 'gender' => 'female', 'roll' => 'SYIT-01'],
            ['first_name' => 'Sneha', 'last_name' => 'Kulkarni', 'gender' => 'female', 'roll' => 'SYIT-02'],
            ['first_name' => 'Dev', 'last_name' => 'Joshi', 'gender' => 'male', 'roll' => 'SYIT-03'],
        ],
        3 => [ // TY
            ['first_name' => 'Aditya', 'last_name' => 'Deshmukh', 'gender' => 'male', 'roll' => 'TYIT-01'],
            ['first_name' => 'Rohan', 'last_name' => 'Gupta', 'gender' => 'male', 'roll' => 'TYIT-02'],
            ['first_name' => 'Pooja', 'last_name' => 'Mishra', 'gender' => 'female', 'roll' => 'TYIT-03'],
        ]
    ],
    // Course 17: B.Sc. CS
    17 => [
        1 => [ // FY
            ['first_name' => 'Kunal', 'last_name' => 'Mehta', 'gender' => 'male', 'roll' => 'FYCS-01'],
            ['first_name' => 'Riya', 'last_name' => 'Jain', 'gender' => 'female', 'roll' => 'FYCS-02'],
            ['first_name' => 'Tanvi', 'last_name' => 'Shah', 'gender' => 'female', 'roll' => 'FYCS-03'],
        ],
        2 => [ // SY
            ['first_name' => 'Aniket', 'last_name' => 'Sawant', 'gender' => 'male', 'roll' => 'SYCS-01'],
            ['first_name' => 'Shruti', 'last_name' => 'Shinde', 'gender' => 'female', 'roll' => 'SYCS-02'],
            ['first_name' => 'Mayur', 'last_name' => 'More', 'gender' => 'male', 'roll' => 'SYCS-03'],
        ],
        3 => [ // TY
            ['first_name' => 'Siddhesh', 'last_name' => 'Kadam', 'gender' => 'male', 'roll' => 'TYCS-01'],
            ['first_name' => 'Sanjana', 'last_name' => 'Rao', 'gender' => 'female', 'roll' => 'TYCS-02'],
            ['first_name' => 'Vicky', 'last_name' => 'Yadav', 'gender' => 'male', 'roll' => 'TYCS-03'],
        ]
    ],
    // Dept 117: Commerce Department
    // Course 18: Bachelor of Commerce
    18 => [
        1 => [ // FY
            ['first_name' => 'Yash', 'last_name' => 'Singhania', 'gender' => 'male', 'roll' => 'FYBC-01'],
            ['first_name' => 'Sakshi', 'last_name' => 'Agarwal', 'gender' => 'female', 'roll' => 'FYBC-02'],
            ['first_name' => 'Harsh', 'last_name' => 'Chawla', 'gender' => 'male', 'roll' => 'FYBC-03'],
        ],
        2 => [ // SY
            ['first_name' => 'Bhavik', 'last_name' => 'Shah', 'gender' => 'male', 'roll' => 'SYBC-01'],
            ['first_name' => 'Meera', 'last_name' => 'Kapoor', 'gender' => 'female', 'roll' => 'SYBC-02'],
            ['first_name' => 'Karan', 'last_name' => 'Thapar', 'gender' => 'male', 'roll' => 'SYBC-03'],
        ],
        3 => [ // TY
            ['first_name' => 'Vivek', 'last_name' => 'Oberoi', 'gender' => 'male', 'roll' => 'TYBC-01'],
            ['first_name' => 'Deepa', 'last_name' => 'Bhatia', 'gender' => 'female', 'roll' => 'TYBC-02'],
            ['first_name' => 'Akash', 'last_name' => 'Mittal', 'gender' => 'male', 'roll' => 'TYBC-03'],
        ]
    ],
    // Course 19: Bachelor of Management Studies
    19 => [
        1 => [ // FY
            ['first_name' => 'Gaurav', 'last_name' => 'Malhotra', 'gender' => 'male', 'roll' => 'FYBM-01'],
            ['first_name' => 'Alok', 'last_name' => 'Saxena', 'gender' => 'male', 'roll' => 'FYBM-02'],
            ['first_name' => 'Simran', 'last_name' => 'Bedi', 'gender' => 'female', 'roll' => 'FYBM-03'],
        ],
        2 => [ // SY
            ['first_name' => 'Rohit', 'last_name' => 'Shetty', 'gender' => 'male', 'roll' => 'SYBM-01'],
            ['first_name' => 'Diya', 'last_name' => 'Sen', 'gender' => 'female', 'roll' => 'SYBM-02'],
            ['first_name' => 'Manav', 'last_name' => 'Trivedi', 'gender' => 'male', 'roll' => 'SYBM-03'],
        ],
        3 => [ // TY
            ['first_name' => 'Varun', 'last_name' => 'Dhawan', 'gender' => 'male', 'roll' => 'TYBM-01'],
            ['first_name' => 'Kriti', 'last_name' => 'Sanon', 'gender' => 'female', 'roll' => 'TYBM-02'],
            ['first_name' => 'Kabir', 'last_name' => 'Das', 'gender' => 'male', 'roll' => 'TYBM-03'],
        ]
    ]
];

$passHash = password_hash('Student@123', PASSWORD_BCRYPT);
$inserted = 0;

foreach ($cohorts as $courseId => $years) {
    $deptId = (int)$db->query("SELECT department_id FROM courses WHERE id = {$courseId}")->fetchColumn();
    foreach ($years as $yearOrder => $studentsList) {
        $yearCode = $yearOrder === 1 ? 'FY' : ($yearOrder === 2 ? 'SY' : 'TY');
        $clsId = (int)$db->query("SELECT id FROM classes WHERE tenant_id = {$tenantId} AND code = '{$yearCode}' LIMIT 1")->fetchColumn() ?: $yearOrder;

        foreach ($studentsList as $idx => $st) {
            $username = strtolower($st['first_name'] . '_' . $st['roll'] . '_' . rand(100, 999));
            $email = strtolower($st['first_name'] . '.' . $st['last_name'] . rand(10, 99) . '@dsjc.edu');
            $admNo = "DSJC-" . date('Y') . "-{$yearCode}-" . str_pad((string)($inserted + 1), 3, '0', STR_PAD_LEFT);
            $uniqueId = "DSJC24ST" . str_pad((string)($inserted + 1), 4, '0', STR_PAD_LEFT);

            // Create user
            $uStmt = $db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role_id, status) VALUES (:tid, :u, :em, :p, 5, 'active')");
            $uStmt->execute([
                ':tid' => $tenantId,
                ':u' => $username,
                ':em' => $email,
                ':p' => $passHash
            ]);
            $userId = (int)$db->lastInsertId();

            // Create student
            $phone = '+91 98200' . str_pad((string)($inserted + 10), 5, '0', STR_PAD_LEFT);
            $stStmt = $db->prepare("
                INSERT INTO students 
                (tenant_id, user_id, unique_id, admission_no, roll_no, first_name, last_name, email, phone, gender, dob, batch_id, department_id, course_id, standard_id, class_id, status)
                VALUES 
                (:tid, :uid, :uniq, :adm, :roll, :fn, :ln, :em, :ph, :gen, '2005-06-15', :bid, :did, :cid, :sid, :clsid, 'active')
            ");
            $stStmt->execute([
                ':tid' => $tenantId,
                ':uid' => $userId,
                ':uniq' => $uniqueId,
                ':adm' => $admNo,
                ':roll' => $st['roll'],
                ':fn' => $st['first_name'],
                ':ln' => $st['last_name'],
                ':em' => $email,
                ':ph' => $phone,
                ':gen' => $st['gender'],
                ':bid' => $batchId,
                ':did' => $deptId,
                ':cid' => $courseId,
                ':sid' => $yearOrder,
                ':clsid' => $clsId
            ]);
            $studentId = (int)$db->lastInsertId();

            // Create enrollment
            $db->exec("
                INSERT INTO student_enrollments (tenant_id, student_id, academic_year, course_id, department_id, standard_id, class_id, status)
                VALUES ({$tenantId}, {$studentId}, '2026-2027', {$courseId}, {$deptId}, {$yearOrder}, {$clsId}, 'active')
            ");

            $inserted++;
        }
    }
}

echo "Successfully seeded {$inserted} students across 2 Departments, 4 Courses, and FY/SY/TY in Tenant 24!\n";
