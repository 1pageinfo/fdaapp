<?php

namespace App\Http\Middleware;

use App\Support\Digits;
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

        return Digits::toEnglish($value);
    }
}
