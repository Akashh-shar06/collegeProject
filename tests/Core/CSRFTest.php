<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\CSRF;
use App\Core\Session;

class CSRFTest extends TestCase {
    public function setUp(): void {
        Session::start();
        $_SESSION = [];
    }

    public function testGetTokenGeneratesValidHexToken(): void {
        $token = CSRF::getToken();
        $this->assertNotNull($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes hex string = 64 chars
        $this->assertEquals($token, Session::get('csrf_token'));
    }

    public function testValidateCorrectToken(): void {
        $token = CSRF::getToken();
        $this->assertTrue(CSRF::validate($token));
    }

    public function testValidateIncorrectToken(): void {
        CSRF::getToken();
        $this->assertFalse(CSRF::validate('invalid_token_value_1234567890'));
        $this->assertFalse(CSRF::validate(null));
    }

    public function testInputFieldGeneration(): void {
        $field = CSRF::inputField();
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $token = Session::get('csrf_token');
        $this->assertStringContainsString($token, $field);
    }

    public function testValidationRejectsTokenWhenSessionTokenIsMissing(): void {
        // An authenticated session must not be allowed to nominate its own CSRF token.
        Session::set('user_id', 123);
        $this->assertFalse(CSRF::validate(str_repeat('a', 64)));
        $this->assertNull(Session::get('csrf_token'));
    }

    public function testValidationRejectsMismatchedTokenForAuthenticatedSession(): void {
        Session::set('user_id', 123);
        $expectedToken = CSRF::getToken();
        $this->assertFalse(CSRF::validate(str_repeat('a', 64)));
        $this->assertEquals($expectedToken, Session::get('csrf_token'));
    }
}
