<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SanghWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private array $masterHeadings,
        private array $masterRows,
        private array $renewalHeadings,
        private array $renewalRows
    ) {
    }

    public function sheets(): array
    {
        return [
            new ArraySheetExport('Sangh Master', $this->masterHeadings, $this->masterRows),
            new ArraySheetExport('Renewals', $this->renewalHeadings, $this->renewalRows),
        ];
    }
}
