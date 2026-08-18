<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sangh_fee_slabs', function (Blueprint $table) {
            $table->decimal('development_fee', 12, 2)->nullable()->after('max_members');
        });

        DB::table('sangh_fee_slabs')->update(['development_fee' => DB::raw('annual_fee')]);

        Schema::table('sangh_fee_slabs', function (Blueprint $table) {
            $table->dropColumn('annual_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sangh_fee_slabs', function (Blueprint $table) {
            $table->decimal('annual_fee', 12, 2)->nullable()->after('max_members');
        });

        DB::table('sangh_fee_slabs')->update(['annual_fee' => DB::raw('development_fee')]);

        Schema::table('sangh_fee_slabs', function (Blueprint $table) {
            $table->dropColumn('development_fee');
        });
    }
};
