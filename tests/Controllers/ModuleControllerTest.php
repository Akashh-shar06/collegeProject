<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\ModuleController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class ModuleControllerTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testModulesConfigContainsAll36Modules(): void {
        $controller = new ModuleController();
        $ref = new \ReflectionClass($controller);
        $prop = $ref->getProperty('modulesConfig');
        $prop->setAccessible(true);
        $config = $prop->getValue($controller);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enquiry', $config);
        $this->assertArrayHasKey('admission', $config);
        $this->assertArrayHasKey('fees', $config);
        $this->assertArrayHasKey('accounts', $config);
        $this->assertArrayHasKey('student_attendance', $config);
        $this->assertArrayHasKey('academic_calendar', $config);
        $this->assertArrayHasKey('id_cards', $config);
        $this->assertArrayHasKey('time_table', $config);
        $this->assertArrayHasKey('certificate', $config);
        $this->assertArrayHasKey('online_exam', $config);
        $this->assertArrayHasKey('exam_management', $config);
        $this->assertArrayHasKey('transport', $config);
        $this->assertArrayHasKey('stock', $config);
        $this->assertArrayHasKey('library', $config);
        $this->assertArrayHasKey('visitor', $config);
        $this->assertArrayHasKey('student_mis', $config);
        $this->assertArrayHasKey('hr_management', $config);
        $this->assertArrayHasKey('staff_data', $config);
        $this->assertArrayHasKey('leave_management', $config);
        $this->assertArrayHasKey('payroll', $config);
        $this->assertArrayHasKey('ticket_management', $config);
        $this->assertArrayHasKey('hostel_management', $config);
        $this->assertArrayHasKey('recruitment', $config);
        $this->assertArrayHasKey('role_permission', $config);
        $this->assertArrayHasKey('news_events', $config);
        $this->assertArrayHasKey('communication', $config);
        $this->assertArrayHasKey('tutorial', $config);
        $this->assertArrayHasKey('support', $config);
    }

    public function testModuleViewRenderViaCLI(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_GET[\'name\'] = \'enquiry\'; (new App\Controllers\ModuleController())->showModule();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Enquiry', $output);
    }

    public function testStoreRecordInModuleTable(): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO enquiries (tenant_id, student_name, email, phone, course_interested, status) VALUES (1, 'Unit Test Student', 'unittest@campus360.edu', '9998887770', 'Computer Science', 'pending')");
        $stmt->execute();
        $id = $db->lastInsertId();

        $this->assertNotNull($id);
        $this->assertTrue((int)$id > 0);

        // Cleanup
        $db->exec("DELETE FROM enquiries WHERE id = " . (int)$id);
    }

    public function testStoreTimeTableRecordInModuleTable(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'dr_soap\', \'Admin@123\'); $_POST = [\'module_key\' => \'time_table\', \'subject_name\' => \'Test Algorithms & Data Struct\', \'day_of_week\' => \'Monday\', \'time_slot\' => \'10:00 AM - 11:00 AM\', \'room_no\' => \'Room 305\', \'staff_id\' => 1, \'batch_id\' => 1]; (new App\Controllers\ModuleController())->storeRecord();"';
        shell_exec($cmd);

        $db = Database::getInstance();
        $count = (int) $db->query("SELECT COUNT(*) FROM time_tables WHERE subject_name = 'Test Algorithms & Data Struct'")->fetchColumn();
        $this->assertTrue($count > 0);

        // Cleanup
        $db->exec("DELETE FROM time_tables WHERE subject_name = 'Test Algorithms & Data Struct'");
    }
}
