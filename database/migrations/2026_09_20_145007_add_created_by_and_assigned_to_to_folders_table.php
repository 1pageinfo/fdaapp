<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('owner_group_id');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'assigned_to']);
        });
    }
};
