<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanghRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'sangh_id',
        'renewal_year',
        'is_paid',
        'feskcom_receipt_no',
        'feskcom_receipt_date',
        'male_members',
        'female_members',
        'total_members',
        'annual_fee',
        'development_fee',
        'penalty_fee',
        'paid_amount',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'feskcom_receipt_date' => 'date',
        'annual_fee' => 'decimal:2',
        'development_fee' => 'decimal:2',
        'penalty_fee' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function sangh()
    {
        return $this->belongsTo(Sangh::class);
    }
}
