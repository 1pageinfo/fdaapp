<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * One sheet from plain arrays. WithStrictNullComparison keeps real zeros (e.g.
 * पुरुष सभासद = 0, बाकी रक्कम = 0) instead of writing them as blank cells, so an
 * exported file re-imports to exactly the same data.
 */
class ArraySheetExport implements FromArray, WithHeadings, WithTitle, WithStrictNullComparison
{
    public function __construct(
        private string $title,
        private array $headings,
        private array $rows
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
