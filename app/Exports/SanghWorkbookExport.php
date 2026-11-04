<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Sangh workbook: any number of sheets, each [title, headings, rows].
 * Used by Export and Template (Sangh Master, Registration Receipts, Renewals).
 */
class SanghWorkbookExport implements WithMultipleSheets
{
    /** @param array<int, array{0: string, 1: array, 2: array}> $sheets */
    public function __construct(private array $sheets)
    {
    }

    public function sheets(): array
    {
        return array_map(fn ($s) => new ArraySheetExport($s[0], $s[1], $s[2]), $this->sheets);
    }
}
