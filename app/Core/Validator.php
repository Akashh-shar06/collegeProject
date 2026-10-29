<?php
namespace App\Core;

class Validator {
    public static function sanitize(string $data): string {
        return trim($data);
    }

    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone(string $phone): bool {
        return preg_match('/^[0-9\-\+\s\(\)]{7,20}$/', $phone) === 1;
    }

    public static function validateInt(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function validateFloat(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }
}
