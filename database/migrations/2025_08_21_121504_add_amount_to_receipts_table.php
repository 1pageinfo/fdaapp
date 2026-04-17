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
    Schema::table('receipts', function (Blueprint $table) {
        $table->decimal('amount', 10, 2)->nullable()->after('subject');
    });
}

public function down(): void
{
    Schema::table('receipts', function (Blueprint $table) {
        $table->dropColumn('amount');
    });
}
};
