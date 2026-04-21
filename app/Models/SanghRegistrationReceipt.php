<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanghRegistrationReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sangh_id',
        'user_id',
        'receipt_year',
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
        'receipt_year' => 'integer',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
