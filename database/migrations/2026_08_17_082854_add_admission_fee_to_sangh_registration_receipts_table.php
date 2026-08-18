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
            $table->decimal('admission_fee', 12, 2)->nullable()->after('annual_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->dropColumn('admission_fee');
        });
    }
};
