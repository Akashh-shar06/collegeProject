<?php
require_once __DIR__ . '/../tests/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== FULL ACADEMIC AUDIT ACROSS ALL ENTITIES ===\n\n";

// 1. Departments by tenant and name/code
echo "--- DEPARTMENTS ---\n";
$depts = $db->query("SELECT tenant_id, LOWER(TRIM(name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes FROM departments GROUP BY tenant_id, LOWER(TRIM(name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($depts);

// 2. Courses by tenant, department_id, norm_name
echo "--- COURSES ---\n";
$courses = $db->query("SELECT tenant_id, department_id, LOWER(TRIM(name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes, GROUP_CONCAT(status) as statuses FROM courses GROUP BY tenant_id, department_id, LOWER(TRIM(name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($courses);

// 3. Batches by tenant, course_id, batch_name
echo "--- BATCHES ---\n";
$batches = $db->query("SELECT tenant_id, course_id, LOWER(TRIM(batch_name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM batches GROUP BY tenant_id, course_id, LOWER(TRIM(batch_name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($batches);

// 4. Academic Levels by tenant, course_id, level_order
echo "--- ACADEMIC LEVELS ---\n";
$levels = $db->query("SELECT tenant_id, course_id, level_order, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(name) as names FROM academic_levels GROUP BY tenant_id, course_id, level_order HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($levels);

// 5. Semesters by tenant, course_id, semester_number
echo "--- SEMESTERS ---\n";
$sems = $db->query("SELECT tenant_id, course_id, semester_number, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM semesters GROUP BY tenant_id, course_id, semester_number HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($sems);

// 6. Classes by tenant, code or name
echo "--- CLASSES ---\n";
$cls = $db->query("SELECT tenant_id, LOWER(TRIM(name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(code) as codes FROM classes GROUP BY tenant_id, LOWER(TRIM(name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($cls);

// 7. Sections by tenant, class_id, name
echo "--- SECTIONS ---\n";
$secs = $db->query("SELECT tenant_id, class_id, LOWER(TRIM(name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM sections GROUP BY tenant_id, class_id, LOWER(TRIM(name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($secs);

// 8. Academic Sessions by tenant, name
echo "--- ACADEMIC SESSIONS ---\n";
$sessions = $db->query("SELECT tenant_id, LOWER(TRIM(name)) as norm_name, COUNT(*) as cnt, GROUP_CONCAT(id) as ids FROM academic_sessions GROUP BY tenant_id, LOWER(TRIM(name)) HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($sessions);
