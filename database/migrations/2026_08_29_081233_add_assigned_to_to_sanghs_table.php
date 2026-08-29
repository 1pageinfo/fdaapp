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
            $table->unsignedBigInteger('assigned_to')->nullable()->after('created_by');
            // We won't add a strict foreign key constraint to avoid issues with soft-deleted users,
            // or we can add it, but since created_by doesn't have one, we'll follow convention.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanghs', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }
};
