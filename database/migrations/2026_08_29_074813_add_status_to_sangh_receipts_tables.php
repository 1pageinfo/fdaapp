<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->string('status')->default('unpaid')->after('is_paid');
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->string('status')->default('unpaid')->after('is_paid');
        });

        // Backfill data
        DB::statement("UPDATE sangh_registration_receipts SET status = 'paid' WHERE is_paid = 1");
        DB::statement("UPDATE sangh_registration_receipts SET status = 'unpaid' WHERE is_paid = 0");
        DB::statement("UPDATE sangh_renewals SET status = 'paid' WHERE is_paid = 1");
        DB::statement("UPDATE sangh_renewals SET status = 'unpaid' WHERE is_paid = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
