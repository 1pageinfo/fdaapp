<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            // Optional: if you want to associate links with users
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('title'); // friendly title, e.g. "My Facebook"
            $table->string('platform')->nullable(); // e.g. facebook, instagram, website or custom name
            $table->enum('category', ['social', 'other', 'custom'])->default('social');
            $table->text('url');
            $table->string('icon')->nullable(); // optional icon class or svg reference
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // if you use users:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
