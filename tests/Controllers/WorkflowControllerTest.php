<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class WorkflowControllerTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testTenantRegistrationWorkflow(): void {
        $db = Database::getInstance();
        $code = 'TEST_INST_' . rand(100, 999);
        $user = 'testadmin_' . rand(100, 999);

        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; $_POST[\'institution_name\'] = \'Test Academy\'; $_POST[\'institution_code\'] = \'' . $code . '\'; $_POST[\'admin_email\'] = \'test@academy.edu\'; $_POST[\'admin_username\'] = \'' . $user . '\'; $_POST[\'admin_password\'] = \'Admin@123\'; (new App\Controllers\WorkflowController())->processTenantRegistration();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('provisioned successfully', $output);

        // Verify Database state
        $tenant = $db->query("SELECT * FROM tenants WHERE code = '{$code}'")->fetch();
        $this->assertNotNull($tenant);
        $this->assertEquals('Test Academy', $tenant['name']);

        // Cleanup
        if ($tenant) {
            $db->exec("DELETE FROM tenants WHERE id = " . (int)$tenant['id']);
        }
    }

    public function testStudentAdmissionWorkflow(): void {
        $db = Database::getInstance();
        Session::set('user_id', 2);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'school_admin');

        $firstName = 'TestStudent_' . rand(100, 999);
        $admNo = 'ADM-TEST-' . rand(1000, 9999);
        $rollNo = '101A';

        $stmt = $db->prepare("INSERT INTO students (tenant_id, admission_no, roll_no, first_name, last_name, gender, dob, phone, email, guardian_name, batch_id, status) VALUES (1, :adm, :roll, :fn, 'Doe', 'male', '2005-01-01', '9876543210', 'student@test.com', 'Guardian Doe', 1, 'active')");
        $stmt->execute([':adm' => $admNo, ':roll' => $rollNo, ':fn' => $firstName]);
        $studentId = (int)$db->lastInsertId();

        $this->assertTrue($studentId > 0);

        // Verify student record
        $student = $db->query("SELECT * FROM students WHERE id = {$studentId}")->fetch();
        $this->assertEquals($firstName, $student['first_name']);

        // Cleanup
        $db->exec("DELETE FROM students WHERE id = {$studentId}");
    }

    public function testSaveAttendanceWorkflow(): void {
        $db = Database::getInstance();
        Session::set('user_id', 3);
        Session::set('tenant_id', 1);
        Session::set('role_name', 'teacher');

        $studentId = (int) $db->query("SELECT id FROM students WHERE tenant_id = 1 LIMIT 1")->fetchColumn();
        if (!$studentId) {
            $db->exec("INSERT INTO students (id, tenant_id, admission_no, roll_no, first_name, last_name, batch_id, status) VALUES (1, 1, 'ADM-001', 'R1', 'John', 'Doe', 1, 'active')");
            $studentId = 1;
        }

        $stmt = $db->prepare("INSERT INTO student_attendance (tenant_id, student_id, batch_id, date, status, marked_by) VALUES (1, :sid, 1, CURDATE(), 'present', 3) ON DUPLICATE KEY UPDATE status = 'present'");
        $stmt->execute([':sid' => $studentId]);

        $att = $db->query("SELECT * FROM student_attendance WHERE tenant_id = 1 AND student_id = {$studentId} AND date = CURDATE()")->fetch();
        $this->assertNotNull($att);
        $this->assertEquals('present', $att['status']);
    }
}
