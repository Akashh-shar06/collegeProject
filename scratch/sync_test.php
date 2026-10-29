<?php
require_once __DIR__ . '/../tests/bootstrap.php';

$db = App\Core\Database::getInstance();

function syncStaffRecords(?int $tenantId = null): int {
    $db = App\Core\Database::getInstance();
    
    // Find all users with staff roles who do not have a staff table entry
    $sql = "SELECT u.id, u.tenant_id, u.username, u.email, r.display_name as role_display 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            LEFT JOIN staff s ON u.id = s.user_id 
            WHERE r.name NOT IN ('super_admin', 'school_admin', 'student', 'parent') 
              AND s.id IS NULL";
    $params = [];
    if ($tenantId !== null) {
        $sql .= " AND u.tenant_id = :tid";
        $params[':tid'] = $tenantId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $unlinkedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $syncedCount = 0;
    foreach ($unlinkedUsers as $u) {
        $tid = (int)($u['tenant_id'] ?? 1);
        
        // Find default department for tenant
        $deptStmt = $db->prepare("SELECT id FROM departments WHERE tenant_id = :tid ORDER BY id ASC LIMIT 1");
        $deptStmt->execute([':tid' => $tid]);
        $deptId = (int)$deptStmt->fetchColumn();
        if (!$deptId) {
            // Fallback department if none exists for tenant
            $anyDept = $db->query("SELECT id FROM departments ORDER BY id ASC LIMIT 1")->fetchColumn();
            $deptId = (int)($anyDept ?: 1);
        }
        
        // Generate emp_id
        $empId = 'EMP-' . str_pad((string)$u['id'], 3, '0', STR_PAD_LEFT);
        $chkEmp = $db->prepare("SELECT COUNT(*) FROM staff WHERE tenant_id = :tid AND emp_id = :emp");
        $chkEmp->execute([':tid' => $tid, ':emp' => $empId]);
        if ((int)$chkEmp->fetchColumn() > 0) {
            $empId = 'EMP-' . $u['id'] . '-' . rand(10, 99);
        }
        
        // Parse name from username
        $parts = explode('_', $u['username']);
        $firstName = ucfirst($parts[0] ?? 'Staff');
        $lastName = isset($parts[1]) ? ucfirst($parts[1]) : 'Member';
        
        // Clean designation
        $designation = $u['role_display'] ?? 'Staff Member';
        
        $insStmt = $db->prepare("INSERT INTO staff (tenant_id, user_id, emp_id, first_name, last_name, designation, department_id, phone, email, joining_date, salary, status) VALUES (:tid, :uid, :empid, :fname, :lname, :desig, :dept, '', :email, CURRENT_DATE(), 0.00, 'active')");
        $insStmt->execute([
            ':tid' => $tid,
            ':uid' => $u['id'],
            ':empid' => $empId,
            ':fname' => $firstName,
            ':lname' => $lastName,
            ':desig' => $designation,
            ':dept' => $deptId,
            ':email' => $u['email']
        ]);
        $syncedCount++;
    }
    
    return $syncedCount;
}

$count = syncStaffRecords();
echo "Synced $count staff records into staff table!\n";

$allStaff = $db->query("SELECT s.id, s.emp_id, s.first_name, s.last_name, s.designation, d.name as dept_name, s.tenant_id FROM staff s LEFT JOIN departments d ON s.department_id = d.id")->fetchAll(PDO::FETCH_ASSOC);
echo "Total staff records in staff table now: " . count($allStaff) . "\n";
print_r($allStaff);
