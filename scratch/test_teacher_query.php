<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();

function testTeacherModuleQuery($db, $userId) {
    $uStmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :uid");
    $uStmt->execute([':uid' => $userId]);
    $user = $uStmt->fetch();
    echo "Testing User: {$user['username']} (ID: {$user['id']}, Role: {$user['role_name']}, Tenant: {$user['tenant_id']})\n";

    $tenantId = $user['tenant_id'];
    $tableName = 'students';
    $moduleKey = 'student_mis';
    $columns = ['id', 'tenant_id', 'admission_no', 'department_id', 'course_id', 'standard_id', 'class_id', 'section_id', 'batch_id', 'status'];
    $hasDeptCol = true;

    $conditions = [];
    $params = [];

    if ($tenantId !== null && $user['role_name'] !== 'super_admin') {
        $conditions[] = "tenant_id = :tid";
        $params[':tid'] = $tenantId;
    }

    if ($user['role_name'] === 'teacher') {
        $stfRow = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = :uid LIMIT 1");
        $stfRow->execute([':uid' => $user['id']]);
        $stfData = $stfRow->fetch();
        $stfId = $stfData['id'] ?? null;
        $stfDeptId = $stfData['department_id'] ?? null;
        echo "  Found staff: id=" . var_export($stfId, true) . ", dept=" . var_export($stfDeptId, true) . "\n";
        if ($stfId && in_array('staff_id', $columns)) {
            $conditions[] = "staff_id = :stf_scope_id";
            $params[':stf_scope_id'] = (int) $stfId;
        }
        if ($tableName === 'students' && $stfId) {
            $conditions[] = "(s.department_id IN (SELECT department_id FROM teacher_assignments WHERE staff_id = :t_stf_id AND department_id IS NOT NULL) OR s.section_id IN (SELECT section_id FROM teacher_assignments WHERE staff_id = :t_stf_id2 AND section_id IS NOT NULL) OR s.department_id = :t_stf_dept OR s.id IN (SELECT student_id FROM student_attendance WHERE marked_by = :t_stf_id3))";
            $params[':t_stf_id'] = (int) $stfId;
            $params[':t_stf_id2'] = (int) $stfId;
            $params[':t_stf_dept'] = (int) ($stfDeptId ?: 0);
            $params[':t_stf_id3'] = (int) $stfId;
        }
    }

    $aliasedConditions = array_map(function ($cond) {
        if (strpos($cond, 'tenant_id') === 0 || strpos($cond, 'department_id') === 0 || strpos($cond, 'batch_id') === 0) {
            return 's.' . $cond;
        }
        return $cond;
    }, $conditions);

    $aliasedWhere = !empty($aliasedConditions) ? "WHERE " . implode(' AND ', $aliasedConditions) : "";

    $sql = "SELECT s.id, s.admission_no, s.roll_no, s.first_name, s.last_name, s.department_id, s.course_id, s.class_id, s.batch_id, s.status FROM students s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN tenants t ON s.tenant_id = t.id LEFT JOIN batches b ON s.batch_id = b.id LEFT JOIN courses c ON COALESCE(s.course_id, b.course_id) = c.id LEFT JOIN departments d ON COALESCE(s.department_id, c.department_id) = d.id LEFT JOIN classes cl ON s.class_id = cl.id {$aliasedWhere} GROUP BY s.id ORDER BY s.id DESC LIMIT 50";

    echo "  SQL: $sql\n";
    echo "  Params: " . json_encode($params) . "\n";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "  Result count: " . count($results) . "\n";
        if (count($results) > 0) {
            echo "  First student: " . json_encode($results[0]) . "\n";
        }
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

testTeacherModuleQuery($db, 3); // dr_soap (Tenant 1)
testTeacherModuleQuery($db, 52); // Jayshree01 (Tenant 24)
testTeacherModuleQuery($db, 61); // Sahil02 (Tenant 24)
