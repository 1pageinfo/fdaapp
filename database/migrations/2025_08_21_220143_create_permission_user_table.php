<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure parent tables exist before creating the pivot
        if (!Schema::hasTable('permissions') || !Schema::hasTable('users')) {
            // Skip if parents missing to avoid FK errors
            return;
        }

        Schema::create('permission_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['permission_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
