<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * संघ नोंदणी दिनांक: the date a registration receipt / renewal's status was set
 * to "Sangh Registered". Set automatically on save; shown on the receipt.
 */
return new class extends Migration
{
    private const TABLES = ['sangh_registration_receipts', 'sangh_renewals'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->date('registered_at')->nullable()->after('status');
            });

            // Already-registered rows: best available date is their last update.
            DB::table($table)->where('status', 'sangh_registered')->whereNull('registered_at')
                ->update(['registered_at' => DB::raw('DATE(updated_at)')]);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('registered_at'));
        }
    }
};
