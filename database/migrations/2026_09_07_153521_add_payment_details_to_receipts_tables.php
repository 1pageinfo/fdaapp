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
            $table->string('payment_mode')->nullable();
            $table->string('transaction_number')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('bank_name')->nullable();
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->string('payment_mode')->nullable();
            $table->string('transaction_number')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('bank_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'transaction_number', 'transaction_date', 'bank_name']);
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'transaction_number', 'transaction_date', 'bank_name']);
        });
    }
};
