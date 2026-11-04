<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "Paid" receipt status was removed; receipts/renewals are now
 * Unpaid, Information approved or Sangh Registered. Existing "paid" rows
 * become "information_approved" (still counted as paid via is_paid).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['sangh_registration_receipts', 'sangh_renewals'] as $table) {
            DB::table($table)->where('status', 'paid')->update([
                'status' => 'information_approved',
                'is_paid' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Not reversible: after this runs, converted rows can't be told apart from
        // ones that were genuinely "information_approved".
    }
};
