<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Validator;

class ValidatorTest extends TestCase {
    public function testSanitizeInput(): void {
        $input = "   <script>alert('xss');</script>   ";
        $sanitized = Validator::sanitize($input);
        $this->assertEquals("<script>alert('xss');</script>", $sanitized);
    }

    public function testValidateEmail(): void {
        $this->assertTrue(Validator::validateEmail('test@campus360.edu'));
        $this->assertTrue(Validator::validateEmail('john.doe@example.org'));
        $this->assertFalse(Validator::validateEmail('invalid-email-string'));
        $this->assertFalse(Validator::validateEmail('user@domain'));
    }

    public function testValidatePhone(): void {
        $this->assertTrue(Validator::validatePhone('+1-555-123-4567'));
        $this->assertTrue(Validator::validatePhone('9876543210'));
        $this->assertTrue(Validator::validatePhone('(123) 456-7890'));
        $this->assertFalse(Validator::validatePhone('abc12345'));
        $this->assertFalse(Validator::validatePhone('123')); // too short
    }

    public function testValidateInt(): void {
        $this->assertTrue(Validator::validateInt(123));
        $this->assertTrue(Validator::validateInt('456'));
        $this->assertFalse(Validator::validateInt('abc'));
        $this->assertFalse(Validator::validateInt('12.34'));
    }

    public function testValidateFloat(): void {
        $this->assertTrue(Validator::validateFloat(123.45));
        $this->assertTrue(Validator::validateFloat('99.99'));
        $this->assertTrue(Validator::validateFloat('100'));
        $this->assertFalse(Validator::validateFloat('abc'));
    }
}
