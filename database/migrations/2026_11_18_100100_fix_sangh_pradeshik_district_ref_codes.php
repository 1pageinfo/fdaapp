<?php

use App\Support\SanghCodes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pradeshik / District reference numbers must use the form's codes
 * (मुंबई → MM, मुंबई सिटी → MC, …). Registration numbering had been deriving the
 * code from the Marathi name, which always fell back to "SG" — so e.g. "SG/12"
 * was saved instead of "MM/12". Fix the stored codes and those "SG/<n>" numbers;
 * the sequence number itself is kept. Other numbers (e.g. imported ones) are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sanghs')
            ->select(['id', 'pradeshik_vibhag', 'district', 'pradeshik_vibhag_code', 'district_code', 'pradeshik_ref_no', 'district_ref_no'])
            ->where(fn ($q) => $q->whereNotNull('pradeshik_vibhag')->orWhereNotNull('district'))
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $update = [];
                    if ($code = SanghCodes::vibhag($row->pradeshik_vibhag)) {
                        $update['pradeshik_vibhag_code'] = $code;
                        if ($fixed = $this->fixRef($row->pradeshik_ref_no, $code)) {
                            $update['pradeshik_ref_no'] = $fixed;
                        }
                    }
                    if ($code = SanghCodes::district($row->district)) {
                        $update['district_code'] = $code;
                        if ($fixed = $this->fixRef($row->district_ref_no, $code)) {
                            $update['district_ref_no'] = $fixed;
                        }
                    }
                    if ($update) {
                        DB::table('sanghs')->where('id', $row->id)->update($update);
                    }
                }
            });
    }

    /** "SG/12" → "MM/12" when the right code is MM; null when nothing to fix. */
    private function fixRef(?string $ref, string $code): ?string
    {
        if ($ref === null || ! preg_match('#^SG/(\d+)$#', trim($ref), $m) || $code === 'SG') {
            return null;
        }

        return $code . '/' . $m[1];
    }

    public function down(): void
    {
        // Not reversible: the wrong "SG" codes are not restored.
    }
};
