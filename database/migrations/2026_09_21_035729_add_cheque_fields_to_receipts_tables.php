<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * cheque_no/cheque_date are already read and written by SanghController
     * (updateRegistrationReceipt, updateRenewal), the models' $fillable, and
     * the show/receipt-pdf views — but the columns were never migrated,
     * so every save on either receipt form has been failing with a
     * "column not found" SQL error.
     */
    public function up(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->string('cheque_no', 50)->nullable()->after('bank_name');
            $table->date('cheque_date')->nullable()->after('cheque_no');
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->string('cheque_no', 50)->nullable()->after('bank_name');
            $table->date('cheque_date')->nullable()->after('cheque_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sangh_registration_receipts', function (Blueprint $table) {
            $table->dropColumn(['cheque_no', 'cheque_date']);
        });

        Schema::table('sangh_renewals', function (Blueprint $table) {
            $table->dropColumn(['cheque_no', 'cheque_date']);
        });
    }
};
