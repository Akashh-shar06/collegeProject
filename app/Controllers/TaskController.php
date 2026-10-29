<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Session;
use App\Core\Validator;

class TaskController
{
    public function assignTask(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role_name'] ?? '', ['principal', 'super_admin', 'school_admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden - Only Principal or Super Admin can assign staff tasks.";
            exit;
        }

        $title = Validator::sanitize($_POST['title'] ?? '');
        $description = Validator::sanitize($_POST['description'] ?? '');
        $assignedToUserId = (int)($_POST['assigned_to_user_id'] ?? 0);
        $priority = Validator::sanitize($_POST['priority'] ?? 'medium');
        $startDate = Validator::sanitize($_POST['start_date'] ?? date('Y-m-d'));
        $dueDate = Validator::sanitize($_POST['due_date'] ?? date('Y-m-d', strtotime('+3 days')));

        if (empty($title) || empty($description) || !$assignedToUserId) {
            Session::set('flash_error', 'Please fill task title, description, and assigned employee.');
            header('Location: index.php?page=dashboard');
            exit;
        }

        $db = Database::getInstance();
        $tenantId = $user['tenant_id'] ?? 1;

        // Fetch staff_id and department_id if available
        $staffId = null;
        $deptId = null;
        try {
            $stfStmt = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = :uid LIMIT 1");
            $stfStmt->execute([':uid' => $assignedToUserId]);
            $stf = $stfStmt->fetch();
            if ($stf) {
                $staffId = $stf['id'];
                $deptId = $stf['department_id'];
            }
        } catch (\Throwable $e) {
        }

        try {
            $stmt = $db->prepare("INSERT INTO staff_tasks 
                (tenant_id, title, description, assigned_by_user_id, assigned_to_user_id, assigned_to_staff_id, department_id, priority, start_date, due_date, status)
                VALUES (:tid, :title, :desc, :byuid, :touid, :sid, :did, :prio, :sdate, :ddate, 'assigned')");

            $stmt->execute([
                ':tid' => $tenantId,
                ':title' => $title,
                ':desc' => $description,
                ':byuid' => $user['id'],
                ':touid' => $assignedToUserId,
                ':sid' => $staffId,
                ':did' => $deptId,
                ':prio' => $priority,
                ':sdate' => $startDate,
                ':ddate' => $dueDate
            ]);

            Session::set('flash_success', 'Task assigned to staff member successfully!');
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to assign task: ' . $e->getMessage());
        }

        header('Location: index.php?page=dashboard');
        exit;
    }

    public function updateTaskStatus(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = Validator::sanitize($_POST['status'] ?? 'in_progress');
        $remarks = Validator::sanitize($_POST['staff_remarks'] ?? '');

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("UPDATE staff_tasks SET status = :status, staff_remarks = :remarks WHERE id = :id AND (assigned_to_user_id = :uid OR :is_principal = 1)");
            $isPrincipal = in_array($user['role_name'] ?? '', ['principal', 'super_admin']) ? 1 : 0;
            $stmt->execute([
                ':status' => $status,
                ':remarks' => $remarks,
                ':id' => $taskId,
                ':uid' => $user['id'],
                ':is_principal' => $isPrincipal
            ]);

            Session::set('flash_success', 'Task status updated to ' . strtoupper(str_replace('_', ' ', $status)));
        } catch (\Throwable $e) {
            Session::set('flash_error', 'Failed to update task status.');
        }

        header('Location: index.php?page=dashboard');
        exit;
    }
}
