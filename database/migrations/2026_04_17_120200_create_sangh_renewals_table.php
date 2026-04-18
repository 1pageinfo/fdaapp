<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sangh_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sangh_id')->constrained('sanghs')->cascadeOnDelete();
            $table->unsignedSmallInteger('renewal_year');
            $table->boolean('is_paid')->default(false);

            $table->string('feskcom_receipt_no')->nullable();
            $table->date('feskcom_receipt_date')->nullable();
            $table->unsignedInteger('male_members')->nullable();
            $table->unsignedInteger('female_members')->nullable();
            $table->unsignedInteger('total_members')->nullable();
            $table->decimal('annual_fee', 12, 2)->nullable();
            $table->decimal('development_fee', 12, 2)->nullable();
            $table->decimal('penalty_fee', 12, 2)->nullable();
            $table->decimal('paid_amount', 12, 2)->nullable();

            $table->timestamps();

            $table->unique(['sangh_id', 'renewal_year']);
            $table->index(['renewal_year', 'is_paid']);
        });

        $currentYear = (int) date('Y');
        $years = range(2010, $currentYear);
        $sanghIds = DB::table('sanghs')->pluck('id');

        foreach ($sanghIds as $sanghId) {
            $rows = [];
            foreach ($years as $year) {
                $rows[] = [
                    'sangh_id' => $sanghId,
                    'renewal_year' => $year,
                    'is_paid' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rows)) {
                DB::table('sangh_renewals')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sangh_renewals');
    }
};
