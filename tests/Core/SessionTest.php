<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Session;

class SessionTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testSessionSetAndGet(): void {
        Session::set('user_role', 'admin');
        $this->assertTrue(Session::has('user_role'));
        $this->assertEquals('admin', Session::get('user_role'));
    }

    public function testSessionGetDefaultValue(): void {
        $val = Session::get('non_existent_key', 'default_val');
        $this->assertEquals('default_val', $val);
    }

    public function testSessionRemove(): void {
        Session::set('temp_token', '123456');
        $this->assertTrue(Session::has('temp_token'));
        Session::remove('temp_token');
        $this->assertFalse(Session::has('temp_token'));
        $this->assertNull(Session::get('temp_token'));
    }

    public function testSessionDestroy(): void {
        Session::set('key1', 'val1');
        Session::set('key2', 'val2');
        Session::destroy();
        $this->assertFalse(Session::has('key1'));
        $this->assertFalse(Session::has('key2'));
    }

    public function testSessionFlashMessages(): void {
        Session::setFlash('success', 'Exam created');
        $this->assertEquals('Exam created', Session::getFlash('success'));
        $this->assertNull(Session::getFlash('success'), 'Flash message should be cleared after retrieval.');
    }
}
