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
        Schema::create('sangh_fee_slabs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_members');
            $table->unsignedInteger('max_members')->nullable(); // null = no upper limit
            $table->decimal('annual_fee', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sangh_fee_slabs');
    }
};
