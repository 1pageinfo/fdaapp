<?php

namespace App\Http\Middleware;

use App\Support\Digits;
use App\Support\Phone;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Every form and AJAX request: turn Marathi/Devanagari digits (०-९) and other
 * Indic digits into English 0-9 before validation and saving, so the database
 * only ever holds English digits.
 */
class ConvertDigitsToEnglish extends TransformsRequest
{
    /** Never alter secrets — a password typed with ५ must stay exactly as typed. */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ];

    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        // Contact numbers: also strip spaces / dashes / +91 so "+91 98765-43210" → 9876543210.
        // ($key is the full dotted path, e.g. "president_phone".)
        if (is_string($value) && Phone::isPhoneField((string) $key)) {
            return Phone::clean($value);
        }

        return Digits::toEnglish($value);
    }
}
