<?php

use App\Support\PradeshikFees;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * प्रादेशिक विभागाची फी is now calculated automatically from Settings → Sangh Fees
 * (admission × 2, annual slab × 2, development × 3). Bring existing Sanghs in line.
 * Sanghs without member counts are cleared (nothing to calculate from).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sanghs')
            ->select(['id', 'male', 'female'])
            ->where(fn ($q) => $q->whereNotNull('male')->orWhereNotNull('female')
                ->orWhereNotNull('pradeshik_admission_fee')->orWhereNotNull('pradeshik_annual_fee')->orWhereNotNull('pradeshik_development_fee'))
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('sanghs')->where('id', $row->id)->update(PradeshikFees::attributes($row->male, $row->female));
                }
            });
    }

    public function down(): void
    {
        // Not reversible: previously typed-in amounts are not kept.
    }
};
