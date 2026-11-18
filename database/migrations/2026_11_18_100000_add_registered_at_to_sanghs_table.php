<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * संघ नोंदणी दिनांक on the Sangh itself: the date a receipt of this Sangh was
 * first set to "Sangh Registered". Every Sangh operation (numbering FY/quarter,
 * date filters, dashboard) uses this date; created_date is kept for activity
 * tracking only.
 *
 * Backfilled with the earliest registered date of its registration receipt / renewals.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanghs', function (Blueprint $t) {
            $t->date('registered_at')->nullable()->index()->after('created_date');
        });

        $dates = [];
        foreach (['sangh_registration_receipts', 'sangh_renewals'] as $table) {
            DB::table($table)->whereNotNull('registered_at')
                ->selectRaw('sangh_id, MIN(registered_at) AS d')->groupBy('sangh_id')
                ->get()->each(function ($row) use (&$dates) {
                    $d = substr((string) $row->d, 0, 10);
                    if (! isset($dates[$row->sangh_id]) || $d < $dates[$row->sangh_id]) {
                        $dates[$row->sangh_id] = $d;
                    }
                });
        }

        foreach ($dates as $sanghId => $date) {
            DB::table('sanghs')->where('id', $sanghId)->update(['registered_at' => $date]);
        }
    }

    public function down(): void
    {
        Schema::table('sanghs', function (Blueprint $t) {
            $t->dropIndex(['registered_at']);
            $t->dropColumn('registered_at');
        });
    }
};
