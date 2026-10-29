<?php
namespace Tests\Controllers;

use Tests\TestCase;
use App\Controllers\ApiController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class ApiControllerTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testGetModuleDataUnauthorized(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; (new App\Controllers\ApiController())->getModuleData();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('Unauthorized', $data['error']);
    }

    public function testGetModuleDataEnquiryModule(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_GET[\'module\'] = \'enquiry\'; (new App\Controllers\ApiController())->getModuleData();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('Enquiry Module', $data['title']);
        $this->assertIsArray($data['data']);
    }

    public function testSubmitEnquiryApi(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_POST[\'student_name\'] = \'API Enquiry User\'; $_POST[\'email\'] = \'apienquiry@campus360.edu\'; $_POST[\'phone\'] = \'9876543210\'; $_POST[\'course_interested\'] = \'Information Technology\'; (new App\Controllers\ApiController())->submitEnquiry();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertEquals('Enquiry logged successfully!', $data['message']);

        // Clean up database
        $db = Database::getInstance();
        $db->exec("DELETE FROM enquiries WHERE student_name = 'API Enquiry User'");
    }

    public function testFilterStudentsForIdCardsUnauthorized(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; (new App\Controllers\ApiController())->filterStudentsForIdCards();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('error', $data['status']);
    }

    public function testFilterStudentsForIdCardsSuccess(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_GET[\'department_id\'] = 116; $_GET[\'academic_year\'] = \'FY\'; (new App\Controllers\ApiController())->filterStudentsForIdCards();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('success', $data['status']);
        $this->assertTrue($data['count'] > 0);
        $this->assertIsArray($data['data']);
    }

    public function testFilterStudentsForIdCardsEmpty(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Auth::attempt(\'admin\', \'Admin@123\'); $_GET[\'department_id\'] = 99999; $_GET[\'academic_year\'] = \'FY\'; (new App\Controllers\ApiController())->filterStudentsForIdCards();"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals(0, $data['count']);
        $this->assertEmpty($data['data']);
    }
}
