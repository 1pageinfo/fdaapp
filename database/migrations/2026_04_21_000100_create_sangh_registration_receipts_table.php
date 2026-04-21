<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sangh_registration_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sangh_id')->constrained('sanghs')->cascadeOnDelete();
            $table->unsignedSmallInteger('receipt_year');
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

            $table->unique('sangh_id');
            $table->index(['receipt_year', 'is_paid']);
        });

        // Backfill from existing renewal rows matching registration year (if present)
        $rows = DB::table('sanghs as s')
            ->leftJoin('sangh_renewals as r', function ($join) {
                $join->on('r.sangh_id', '=', 's.id')
                    ->on('r.renewal_year', '=', 's.registration_year');
            })
            ->select([
                's.id as sangh_id',
                's.registration_year',
                's.male as sangh_male',
                's.female as sangh_female',
                'r.is_paid',
                'r.feskcom_receipt_no',
                'r.feskcom_receipt_date',
                'r.annual_fee',
                'r.development_fee',
                'r.penalty_fee',
                'r.paid_amount',
                'r.created_at as renewal_created_at',
                'r.updated_at as renewal_updated_at',
            ])
            ->whereNotNull('s.registration_year')
            ->get();

        $insertRows = [];
        foreach ($rows as $row) {
            $male = is_numeric($row->sangh_male) ? (int) $row->sangh_male : null;
            $female = is_numeric($row->sangh_female) ? (int) $row->sangh_female : null;
            $total = ($male === null && $female === null) ? null : (($male ?? 0) + ($female ?? 0));

            $insertRows[] = [
                'sangh_id' => $row->sangh_id,
                'receipt_year' => (int) $row->registration_year,
                'is_paid' => (bool) ($row->is_paid ?? false),
                'feskcom_receipt_no' => $row->feskcom_receipt_no,
                'feskcom_receipt_date' => $row->feskcom_receipt_date,
                'male_members' => $male,
                'female_members' => $female,
                'total_members' => $total,
                'annual_fee' => $row->annual_fee,
                'development_fee' => $row->development_fee,
                'penalty_fee' => $row->penalty_fee,
                'paid_amount' => $row->paid_amount,
                'created_at' => $row->renewal_created_at ?? now(),
                'updated_at' => $row->renewal_updated_at ?? now(),
            ];
        }

        if (!empty($insertRows)) {
            DB::table('sangh_registration_receipts')->insert($insertRows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sangh_registration_receipts');
    }
};
