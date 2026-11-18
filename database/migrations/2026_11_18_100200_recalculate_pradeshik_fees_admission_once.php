<?php

use App\Support\PradeshikFees;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * प्रादेशिक प्रवेश शुल्क is now admission fee × 1 (was × 2). Recalculate the
 * stored प्रादेशिक fees of every Sangh that has member counts.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sanghs')
            ->select(['id', 'male', 'female'])
            ->where(fn ($q) => $q->whereNotNull('male')->orWhereNotNull('female'))
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('sanghs')->where('id', $row->id)->update(PradeshikFees::attributes($row->male, $row->female));
                }
            });
    }

    public function down(): void
    {
        // Recalculated values follow the code's multipliers; nothing to restore.
    }
};
