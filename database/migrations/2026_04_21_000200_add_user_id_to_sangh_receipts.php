<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->dropForeignIdFor('User');
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->dropForeignIdFor('User');
        });
    }
};
