<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks renewal receipts created from "Backdated / Current Year" (any year from
 * 1969) as opposed to the regular next-year Renewal, so the All Receipts table
 * can label them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->boolean('is_backdated')->default(false)->after('renewal_year');
        });
    }

    public function down(): void
    {
        Schema::table('sangh_renewals', fn (Blueprint $table) => $table->dropColumn('is_backdated'));
    }
};
