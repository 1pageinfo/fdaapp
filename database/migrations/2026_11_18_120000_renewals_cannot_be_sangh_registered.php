<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Sangh Registered" happens once, on the registration receipt. Renewal / backdated
 * receipts only have Unpaid / Information approved, so existing renewals marked
 * "Sangh Registered" become "Information approved" (they were paid/approved).
 * The Sangh's own संघ नोंदणी दिनांक and numbering are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sangh_renewals')->where('status', 'sangh_registered')->update([
            'status' => 'information_approved',
            'is_paid' => true,
            'registered_at' => null,
        ]);
    }

    public function down(): void
    {
        // Not reversible: which renewals were "Sangh Registered" is not kept.
    }
};
