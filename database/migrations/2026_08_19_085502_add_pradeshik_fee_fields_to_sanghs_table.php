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
        Schema::table('sanghs', function (Blueprint $table) {
            $table->decimal('pradeshik_admission_fee', 12, 2)->nullable()->after('total_members');
            $table->decimal('pradeshik_annual_fee', 12, 2)->nullable()->after('pradeshik_admission_fee');
            $table->decimal('pradeshik_development_fee', 12, 2)->nullable()->after('pradeshik_annual_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanghs', function (Blueprint $table) {
            $table->dropColumn(['pradeshik_admission_fee', 'pradeshik_annual_fee', 'pradeshik_development_fee']);
        });
    }
};
