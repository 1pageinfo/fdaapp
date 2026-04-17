<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sangh extends Model
{
    use HasFactory;

    protected $fillable = [
        'sangh_sr_no',
        'name_of_sangh',
        'district',
        'district_no',
        'taluka',
        'city',
        'division',
        'division_no',
        'total_m_f',
        'date_meeting',
        'receipt_no',
        'receipt_date',
        'receipt_amount',
        'division_membership_no',
        'male',
        'female',
        'total_members',
        'address',
        'president',
        'tel_no',
        'alt_tel_no',
        'email',
        'secretary',
        'created_by',
        'created_date',
    ];

    /**
     * Correct casting for date fields
     */
    protected $casts = [
        'date_meeting'  => 'date',
        'receipt_date'  => 'date',
        'created_date'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
}
