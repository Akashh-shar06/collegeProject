<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== SEEDING REAL TEACHERS, SUBJECTS & TIMETABLE SLOTS FOR TENANT 24 ===" . PHP_EOL;

// 1. Staff Members from Image 3
$professors = [
    ['first_name' => 'Rahul', 'last_name' => 'Sharma', 'dept_id' => 116, 'emp_id' => 'EMP-T101'],
    ['first_name' => 'Neha', 'last_name' => 'Shah', 'dept_id' => 116, 'emp_id' => 'EMP-T102'],
    ['first_name' => 'Priya', 'last_name' => 'Patil', 'dept_id' => 116, 'emp_id' => 'EMP-T103'],
    ['first_name' => 'Suresh', 'last_name' => 'Shah', 'dept_id' => 117, 'emp_id' => 'EMP-T104'],
    ['first_name' => 'Anjali', 'last_name' => 'Deshmukh', 'dept_id' => 117, 'emp_id' => 'EMP-T105'],
    ['first_name' => 'Rohit', 'last_name' => 'Mehta', 'dept_id' => 116, 'emp_id' => 'EMP-T106'],
    ['first_name' => 'Vikram', 'last_name' => 'Singh', 'dept_id' => 116, 'emp_id' => 'EMP-T107'],
    ['first_name' => 'Nitin', 'last_name' => 'Kulkarni', 'dept_id' => 117, 'emp_id' => 'EMP-T108'],
    ['first_name' => 'Kavita', 'last_name' => 'Rao', 'dept_id' => 117, 'emp_id' => 'EMP-T109'],
    ['first_name' => 'Amit', 'last_name' => 'Verma', 'dept_id' => 116, 'emp_id' => 'EMP-T110'],
    ['first_name' => 'Sneha', 'last_name' => 'Joshi', 'dept_id' => 116, 'emp_id' => 'EMP-T111'],
    ['first_name' => 'Karan', 'last_name' => 'Desai', 'dept_id' => 116, 'emp_id' => 'EMP-T112'],
    ['first_name' => 'Manish', 'last_name' => 'Jain', 'dept_id' => 117, 'emp_id' => 'EMP-T113'],
    ['first_name' => 'Meera', 'last_name' => 'Joshi', 'dept_id' => 117, 'emp_id' => 'EMP-T114'],
];

$teacherMap = []; // Name -> Staff ID

foreach ($professors as $p) {
    $chk = $db->prepare("SELECT id FROM staff WHERE tenant_id = 24 AND first_name = :fn AND last_name = :ln LIMIT 1");
    $chk->execute([':fn' => $p['first_name'], ':ln' => $p['last_name']]);
    $stfId = $chk->fetchColumn();
    if (!$stfId) {
        $ins = $db->prepare("
            INSERT INTO staff (tenant_id, department_id, first_name, last_name, emp_id, designation, status, phone, email) 
            VALUES (24, :did, :fn, :ln, :emp, 'Assistant Professor', 'active', '+91 9876543210', :em)
        ");
        $em = strtolower($p['first_name'] . '.' . $p['last_name'] . '@college.edu');
        $ins->execute([
            ':did' => $p['dept_id'],
            ':fn' => $p['first_name'],
            ':ln' => $p['last_name'],
            ':emp' => $p['emp_id'],
            ':em' => $em
        ]);
        $stfId = $db->lastInsertId();
    }
    $fullName = 'Prof. ' . $p['first_name'] . ' ' . $p['last_name'];
    $teacherMap[$fullName] = (int)$stfId;
}

echo "Seeded " . count($teacherMap) . " professors.\n";

// 2. Course ID lookup for Tenant 24
$courseLookup = [
    'BSc CS' => 17,
    'BSc IT' => 16,
    'BCA' => 26,
    'Data Science' => 40,
    'BCom' => 18,
    'BMS' => 19,
];

// Standard / Year mapping
$stdLookup = [
    'FY' => 1,
    'SY' => 2,
    'TY' => 3
];
$classLookup = [
    'FY' => 79,
    'SY' => 80,
    'TY' => 81
];

// 3. The 24 Lectures from Image 3
$lectures = [
    // 9-10 AM
    ['day' => 'Monday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'C Programming', 'code' => 'CS101', 'crs' => 'BSc CS', 'yr' => 'FY', 'teacher' => 'Prof. Rahul Sharma', 'room' => 'Block 101'],
    ['day' => 'Tuesday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'Java Programming', 'code' => 'IT201', 'crs' => 'BSc IT', 'yr' => 'SY', 'teacher' => 'Prof. Neha Shah', 'room' => 'Block 102'],
    ['day' => 'Wednesday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'Python Programming', 'code' => 'BCA101', 'crs' => 'BCA', 'yr' => 'FY', 'teacher' => 'Prof. Rahul Sharma', 'room' => 'Block 103'],
    ['day' => 'Thursday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'Statistics', 'code' => 'DS101', 'crs' => 'Data Science', 'yr' => 'FY', 'teacher' => 'Prof. Priya Patil', 'room' => 'Block 104'],
    ['day' => 'Friday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'Financial Accounting', 'code' => 'COM101', 'crs' => 'BCom', 'yr' => 'FY', 'teacher' => 'Prof. Anjali Deshmukh', 'room' => 'Block 105'],
    ['day' => 'Saturday', 'st' => '09:00:00', 'et' => '10:00:00', 'ts' => '9–10 AM', 'sub' => 'Management', 'code' => 'BMS101', 'crs' => 'BMS', 'yr' => 'FY', 'teacher' => 'Prof. Suresh Shah', 'room' => 'Block 106'],

    // 10-11 AM
    ['day' => 'Monday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'Mathematics', 'code' => 'CS102', 'crs' => 'BSc CS', 'yr' => 'FY', 'teacher' => 'Prof. Priya Patil', 'room' => 'Block 101'],
    ['day' => 'Tuesday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'DBMS', 'code' => 'CS201', 'crs' => 'BSc CS', 'yr' => 'SY', 'teacher' => 'Prof. Rohit Mehta', 'room' => 'Block 102'],
    ['day' => 'Wednesday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'Machine Learning', 'code' => 'DS201', 'crs' => 'Data Science', 'yr' => 'SY', 'teacher' => 'Prof. Neha Shah', 'room' => 'Block 103'],
    ['day' => 'Thursday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'Web Development', 'code' => 'BCA301', 'crs' => 'BCA', 'yr' => 'TY', 'teacher' => 'Prof. Vikram Singh', 'room' => 'Block 104'],
    ['day' => 'Friday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'Business Law', 'code' => 'COM201', 'crs' => 'BCom', 'yr' => 'SY', 'teacher' => 'Prof. Nitin Kulkarni', 'room' => 'Block 105'],
    ['day' => 'Saturday', 'st' => '10:00:00', 'et' => '11:00:00', 'ts' => '10–11 AM', 'sub' => 'Marketing Management', 'code' => 'BMS201', 'crs' => 'BMS', 'yr' => 'SY', 'teacher' => 'Prof. Kavita Rao', 'room' => 'Block 106'],

    // 11 AM-12 PM
    ['day' => 'Monday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Digital Electronics', 'code' => 'CS103', 'crs' => 'BSc CS', 'yr' => 'FY', 'teacher' => 'Prof. Amit Verma', 'room' => 'Block 101'],
    ['day' => 'Tuesday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Computer Networks', 'code' => 'IT202', 'crs' => 'BSc IT', 'yr' => 'SY', 'teacher' => 'Prof. Sneha Joshi', 'room' => 'Block 102'],
    ['day' => 'Wednesday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Cloud Computing', 'code' => 'IT301', 'crs' => 'BSc IT', 'yr' => 'TY', 'teacher' => 'Prof. Karan Desai', 'room' => 'Block 103'],
    ['day' => 'Thursday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Deep Learning', 'code' => 'DS301', 'crs' => 'Data Science', 'yr' => 'TY', 'teacher' => 'Prof. Karan Desai', 'room' => 'Block 104'],
    ['day' => 'Friday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Taxation', 'code' => 'COM301', 'crs' => 'BCom', 'yr' => 'TY', 'teacher' => 'Prof. Kavita Rao', 'room' => 'Block 105'],
    ['day' => 'Saturday', 'st' => '11:00:00', 'et' => '12:00:00', 'ts' => '11 AM–12 PM', 'sub' => 'Strategic Management', 'code' => 'BMS301', 'crs' => 'BMS', 'yr' => 'TY', 'teacher' => 'Prof. Suresh Shah', 'room' => 'Block 106'],

    // 12-1 PM
    ['day' => 'Monday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Practical Lab', 'code' => 'CS202', 'crs' => 'BSc CS', 'yr' => 'SY', 'teacher' => 'Prof. Neha Shah', 'room' => 'Lab 101'],
    ['day' => 'Tuesday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Python Lab', 'code' => 'DS102', 'crs' => 'Data Science', 'yr' => 'FY', 'teacher' => 'Prof. Rahul Sharma', 'room' => 'Lab 102'],
    ['day' => 'Wednesday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Java Lab', 'code' => 'BCA201', 'crs' => 'BCA', 'yr' => 'SY', 'teacher' => 'Prof. Neha Shah', 'room' => 'Lab 103'],
    ['day' => 'Thursday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Web Technology', 'code' => 'CS301', 'crs' => 'BSc CS', 'yr' => 'TY', 'teacher' => 'Prof. Vikram Singh', 'room' => 'Lab 101'],
    ['day' => 'Friday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Auditing', 'code' => 'COM302', 'crs' => 'BCom', 'yr' => 'TY', 'teacher' => 'Prof. Manish Jain', 'room' => 'Block 105'],
    ['day' => 'Saturday', 'st' => '12:00:00', 'et' => '13:00:00', 'ts' => '12–1 PM', 'sub' => 'Business Communication', 'code' => 'BMS102', 'crs' => 'BMS', 'yr' => 'FY', 'teacher' => 'Prof. Meera Joshi', 'room' => 'Block 106'],
];

// Clean existing tenant 24 time_tables and subjects to seed fresh
$db->exec("DELETE FROM time_tables WHERE tenant_id = 24");
$db->exec("DELETE FROM subjects WHERE tenant_id = 24");

$subjectMap = [];

foreach ($lectures as $lec) {
    $courseId = $courseLookup[$lec['crs']] ?? 16;
    $stdId = $stdLookup[$lec['yr']] ?? 1;
    $staffId = $teacherMap[$lec['teacher']] ?? 123;
    $deptId = in_array($lec['crs'], ['BCom', 'BMS']) ? 117 : 116;

    // Check or insert Subject
    $classId = $classLookup[$lec['yr']] ?? 79;
    $subKey = $lec['sub'] . '_' . $courseId;
    if (!isset($subjectMap[$subKey])) {
        $insSub = $db->prepare("
            INSERT INTO subjects (tenant_id, department_id, course_id, standard_id, class_id, name, code, type, status) 
            VALUES (24, :did, :cid, :sid, :clsid, :name, :code, 'theory', 'active')
        ");
        $insSub->execute([
            ':did' => $deptId,
            ':cid' => $courseId,
            ':sid' => $stdId,
            ':clsid' => $classId,
            ':name' => $lec['sub'],
            ':code' => $lec['code']
        ]);
        $subjectMap[$subKey] = (int)$db->lastInsertId();
    }
    $subjectId = $subjectMap[$subKey];

    // Insert Time Table slot
    $insTT = $db->prepare("
        INSERT INTO time_tables 
        (tenant_id, academic_year, department_id, course_id, standard_id, batch_id, staff_id, subject_id, subject_name, day_of_week, start_time, end_time, time_slot, room_no, status, created_by)
        VALUES 
        (24, '2026-2027', :did, :cid, :stdid, 4, :stfid, :subid, :subname, :day, :st, :et, :ts, :room, 'active', 1)
    ");
    $insTT->execute([
        ':did' => $deptId,
        ':cid' => $courseId,
        ':stdid' => $stdId,
        ':stfid' => $staffId,
        ':subid' => $subjectId,
        ':subname' => $lec['sub'],
        ':day' => $lec['day'],
        ':st' => $lec['st'],
        ':et' => $lec['et'],
        ':ts' => $lec['ts'],
        ':room' => $lec['room']
    ]);
}

$seededCount = (int)$db->query("SELECT COUNT(*) FROM time_tables WHERE tenant_id = 24")->fetchColumn();
$subCount = (int)$db->query("SELECT COUNT(*) FROM subjects WHERE tenant_id = 24")->fetchColumn();

echo "SUCCESS: Seeded {$seededCount} timetable slots and {$subCount} subjects in Tenant 24!" . PHP_EOL;
