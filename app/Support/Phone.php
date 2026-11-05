<?php

namespace App\Support;

/**
 * Contact numbers are stored as digits only.
 *
 * clean() removes formatting people commonly type — spaces, dashes, dots, brackets,
 * a leading +91 / 91 / 0 in front of a 10-digit mobile — so "+91 98765-43210" becomes
 * "9876543210". Anything with letters stays as it is so validation can reject it.
 */
class Phone
{
    /** Request / import fields that hold contact numbers. */
    public const FIELD_PATTERN = '/(^|_)(phone|whatsapp|mobile|tel_no)$/';

    /** Mobile / WhatsApp: exactly 10 digits. */
    public const MOBILE_RULE = 'nullable|digits:10';

    /** Landline (with STD code) or mobile: 6–12 digits. */
    public const TELEPHONE_RULE = 'nullable|digits_between:6,12';

    public static function isPhoneField(string $key): bool
    {
        return (bool) preg_match(self::FIELD_PATTERN, $key);
    }

    public static function clean(mixed $value): mixed
    {
        if (! is_string($value) && ! is_int($value)) {
            return $value;
        }
        $value = trim(Digits::toEnglish((string) $value));
        if ($value === '') {
            return null;
        }
        // Only formatting characters around digits? Then strip them.
        if (! preg_match('/^[+\d\s\-().\/]+$/', $value)) {
            return $value; // contains letters etc. — leave for validation to reject
        }
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    public static function isValidMobile(mixed $value): bool
    {
        return $value === null || $value === '' || (bool) preg_match('/^\d{10}$/', (string) $value);
    }
}
