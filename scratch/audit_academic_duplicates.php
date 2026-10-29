<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=============================================\n";
echo "   ACADEMIC AUDIT: DEPARTMENTS, COURSES, COHORTS\n";
echo "=============================================\n\n";

echo "--- 1. DEPARTMENTS ---\n";
$depts = $db->query("SELECT id, tenant_id, course_id, name, code, status, created_at FROM departments ORDER BY tenant_id, name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($depts as $d) {
    echo "ID: {$d['id']} | Tenant: {$d['tenant_id']} | CourseID: " . ($d['course_id'] ?? 'NULL') . " | Name: {$d['name']} | Code: {$d['code']} | Status: {$d['status']}\n";
}

echo "\n--- 2. COURSES ---\n";
$courses = $db->query("SELECT id, tenant_id, department_id, name, code, duration_years, status, created_at FROM courses ORDER BY tenant_id, name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($courses as $c) {
    echo "ID: {$c['id']} | Tenant: {$c['tenant_id']} | DeptID: {$c['department_id']} | Name: {$c['name']} | Code: {$c['code']} | Status: {$c['status']}\n";
}

echo "\n--- 3. ACADEMIC SESSIONS ---\n";
$sessions = $db->query("SELECT id, tenant_id, name, start_date, end_date, is_current, status FROM academic_sessions ORDER BY tenant_id, name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sessions as $s) {
    echo "ID: {$s['id']} | Tenant: {$s['tenant_id']} | Name: {$s['name']} | Current: {$s['is_current']} | Status: {$s['status']}\n";
}

echo "\n--- 4. ACADEMIC LEVELS (COHORTS) ---\n";
$levels = $db->query("SELECT id, tenant_id, course_id, name, level_order, description FROM academic_levels ORDER BY tenant_id, course_id, level_order")->fetchAll(PDO::FETCH_ASSOC);
foreach ($levels as $l) {
    echo "ID: {$l['id']} | Tenant: {$l['tenant_id']} | CourseID: " . ($l['course_id'] ?? 'NULL') . " | Name: {$l['name']} | Order: {$l['level_order']}\n";
}

echo "\n--- 5. BATCHES ---\n";
$batches = $db->query("SELECT id, tenant_id, course_id, batch_name, start_year, end_year FROM batches ORDER BY tenant_id, course_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($batches as $b) {
    echo "ID: {$b['id']} | Tenant: {$b['tenant_id']} | CourseID: {$b['course_id']} | Name: {$b['batch_name']} ({$b['start_year']}-{$b['end_year']})\n";
}

echo "\n--- 6. HOD USER DETAILS ---\n";
$hod = $db->query("SELECT u.id, u.username, u.email, u.tenant_id, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'hod'")->fetchAll(PDO::FETCH_ASSOC);
print_r($hod);

echo "\n--- 7. HOD STAFF RECORD ---\n";
$hodStaff = $db->query("SELECT s.*, d.name as dept_name FROM staff s LEFT JOIN departments d ON s.department_id = d.id WHERE s.user_id IN (SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'hod')")->fetchAll(PDO::FETCH_ASSOC);
print_r($hodStaff);

echo "\n--- 8. SUSPECTED DUPLICATES IN DEPARTMENTS ---\n";
$deptDups = $db->query("SELECT tenant_id, code, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(name) as names FROM departments GROUP BY tenant_id, code HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($deptDups);

$deptNameDups = $db->query("SELECT tenant_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes FROM departments GROUP BY tenant_id, LOWER(name) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($deptNameDups);

echo "\n--- 9. SUSPECTED DUPLICATES IN COURSES ---\n";
$courseDups = $db->query("SELECT tenant_id, code, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(name) as names FROM courses GROUP BY tenant_id, code HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($courseDups);

$courseNameDups = $db->query("SELECT tenant_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes FROM courses GROUP BY tenant_id, LOWER(name) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($courseNameDups);

echo "\n--- 10. SUSPECTED DUPLICATES IN ACADEMIC SESSIONS ---\n";
$sessDups = $db->query("SELECT tenant_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM academic_sessions GROUP BY tenant_id, name HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($sessDups);

echo "\n--- 11. SUSPECTED DUPLICATES IN ACADEMIC LEVELS (COHORTS) ---\n";
$lvlDups = $db->query("SELECT tenant_id, course_id, name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM academic_levels GROUP BY tenant_id, course_id, name HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($lvlDups);
