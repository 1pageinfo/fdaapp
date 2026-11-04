<?php

namespace App\Support;

/**
 * The app stores and shows numbers with English digits (0-9) only.
 * Converts Marathi/Devanagari (०-९) and other Indic/Arabic digit forms
 * that a phone keyboard or a pasted value may contain.
 */
class Digits
{
    private const MAP = [
        // Devanagari (Marathi / Hindi)
        '०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4', '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9',
        // Gujarati
        '૦' => '0', '૧' => '1', '૨' => '2', '૩' => '3', '૪' => '4', '૫' => '5', '૬' => '6', '૭' => '7', '૮' => '8', '૯' => '9',
        // Gurmukhi
        '੦' => '0', '੧' => '1', '੨' => '2', '੩' => '3', '੪' => '4', '੫' => '5', '੬' => '6', '੭' => '7', '੮' => '8', '੯' => '9',
        // Bengali
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        // Arabic-Indic and Eastern Arabic-Indic
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /** MySQL REGEXP that matches any non-English digit handled here. */
    public const SQL_PATTERN = '[०-९૦-૯੦-੯০-৯٠-٩۰-۹]';

    public static function toEnglish(mixed $value): mixed
    {
        if (is_string($value)) {
            return strtr($value, self::MAP);
        }

        if (is_array($value)) {
            return array_map([self::class, 'toEnglish'], $value);
        }

        return $value;
    }
}
