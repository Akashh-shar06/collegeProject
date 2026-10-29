<?php
require_once __DIR__ . '/../tests/bootstrap.php';

$db = App\Core\Database::getInstance();

$staffId = 1;
$tenantId = 1;

$subStmt = $db->prepare("
    SELECT DISTINCT s.id, s.name, s.code, s.type, d.name as department_name, c.name as course_name
    FROM time_tables tt
    JOIN subjects s ON tt.subject_id = s.id
    LEFT JOIN departments d ON tt.department_id = d.id
    LEFT JOIN courses c ON tt.course_id = c.id
    WHERE (tt.tenant_id = :tid OR 0 = 1)
      AND tt.staff_id = :sid
      AND (tt.status = 'active' OR tt.status IS NULL)
");
$subStmt->execute([':tid' => $tenantId, ':sid' => $staffId]);
$res1 = $subStmt->fetchAll();
echo "Res1 with JOIN subjects count: " . count($res1) . "\n";

// Now test with LEFT JOIN or fallback:
$subStmt2 = $db->prepare("
    SELECT DISTINCT 
        COALESCE(s.id, tt.subject_id, 0) as id,
        COALESCE(s.name, tt.subject_name, 'General Subject') as name,
        COALESCE(s.code, 'SUB') as code,
        COALESCE(s.type, 'theory') as type,
        d.name as department_name,
        c.name as course_name
    FROM time_tables tt
    LEFT JOIN subjects s ON tt.subject_id = s.id
    LEFT JOIN departments d ON tt.department_id = d.id
    LEFT JOIN courses c ON tt.course_id = c.id
    WHERE (tt.tenant_id = :tid OR 0 = 1)
      AND tt.staff_id = :sid
      AND (tt.status = 'active' OR tt.status IS NULL)
");
$subStmt2->execute([':tid' => $tenantId, ':sid' => $staffId]);
$res2 = $subStmt2->fetchAll();
echo "Res2 with LEFT JOIN subjects count: " . count($res2) . "\n";
print_r($res2);
