<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Database;
use App\Core\Session;

class DatabaseTest extends TestCase {
    public function testDatabaseInstanceIsPDO(): void {
        $db = Database::getInstance();
        $this->assertNotNull($db);
        $this->assertTrue($db instanceof \PDO);
    }

    public function testDatabaseTablesExist(): void {
        $db = Database::getInstance();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->assertContains('tenants', $tables);
        $this->assertContains('roles', $tables);
        $this->assertContains('users', $tables);
        $this->assertContains('students', $tables);
        $this->assertContains('notices', $tables);
    }

    public function testTenantQueryExecutesSuccessfully(): void {
        Session::start();
        Session::set('tenant_id', 1);
        Session::set('role_name', 'teacher');

        $stmt = Database::tenantQuery("SELECT * FROM notices");
        $this->assertNotNull($stmt);
        $this->assertTrue($stmt instanceof \PDOStatement);
    }
}
