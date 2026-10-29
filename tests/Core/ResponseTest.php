<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Response;

class ResponseTest extends TestCase {
    public function testJsonResponseOutput(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Response::json([\'status\' => \'success\', \'code\' => 200]);"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $data = json_decode(trim($output), true);
        $this->assertIsArray($data);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals(200, $data['code']);
    }

    public function testViewResponseOutput(): void {
        $cmd = 'C:\\xampp\\php\\php.exe -r "define(\'BASE_PATH\', \'' . addslashes(BASE_PATH) . '\'); require \'' . addslashes(BASE_PATH) . '/tests/bootstrap.php\'; App\Core\Response::view(\'auth/login\');"';
        $output = shell_exec($cmd);
        $this->assertNotNull($output);
        $this->assertStringContainsString('Campus360', $output);
    }
}
