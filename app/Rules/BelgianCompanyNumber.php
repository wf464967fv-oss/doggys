<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Belgian enterprise number (BCE/KBO): 10 digits starting with 0 or 1, written 0123.456.789,
 * whose last two digits equal 97 - (first eight digits mod 97).
 */
class BelgianCompanyNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || self::normalize($value) === null) {
            $fail('pro.validation.bce')->translate();
        }
    }

    /**
     * "0123 456 789", "BE0123456789" or "0123.456.789" => "0123.456.789"; null when invalid.
     */
    public static function normalize(string $value): ?string
    {
        $digits = preg_replace('/^BE|[\s.\-]/i', '', trim($value));

        if (! preg_match('/^[01]\d{9}$/', (string) $digits)) {
            return null;
        }

        if (97 - ((int) substr($digits, 0, 8) % 97) !== (int) substr($digits, 8, 2)) {
            return null;
        }

        return substr($digits, 0, 4).'.'.substr($digits, 4, 3).'.'.substr($digits, 7, 3);
    }
}
