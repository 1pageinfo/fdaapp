<?php

namespace App\Support;

/**
 * The one year range used everywhere in the Sangh module (form, filters,
 * receipts, import): from 1979-80 up to the current year + the next two.
 *
 * A year is stored as the year a financial year ends in and shown with
 * @fy(), so 1980 is "1979-1980".
 */
class SanghYears
{
    /** First year: 1980 = financial year 1979-80. */
    public const FIRST = 1980;

    /** Last year: current year + the next two years. */
    public static function last(): int
    {
        return (int) date('Y') + 2;
    }

    /** Every year, newest first (for dropdowns). @return int[] */
    public static function all(): array
    {
        return range(self::last(), self::FIRST);
    }

    public static function contains(?int $year): bool
    {
        return $year !== null && $year >= self::FIRST && $year <= self::last();
    }

    /** Laravel validation rule for a year field. */
    public static function rule(bool $required = false): string
    {
        return ($required ? 'required' : 'nullable') . '|integer|min:' . self::FIRST . '|max:' . self::last();
    }
}
