<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sangh extends Model
{
    use HasFactory;

    protected $fillable = [
        'sangh_sr_no',
        'unique_ref_no',
        'pradeshik_sr_no',
        'pradeshik_ref_no',
        'district_sr_no',
        'district_ref_no',
        'registration_year',
        'name_of_sangh',
        'category_code',
        'sangh_type_code',
        'pradeshik_vibhag',
        'pradeshik_vibhag_code',
        'district',
        'district_code',
        'district_no',
        'taluka',
        'village',
        'city',
        'mukkam_post',
        'pincode',
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
        'road_path',
        'ward_section',
        'president',
        'president_phone',
        'president_whatsapp',
        'president_email',
        'tel_no',
        'alt_tel_no',
        'email',
        'secretary',
        'secretary_phone',
        'secretary_whatsapp',
        'secretary_email',
        'created_by',
        'created_date',
    ];

    /**
     * Correct casting for date fields
     */
    protected $casts = [
        'registration_year' => 'integer',
        'pradeshik_sr_no' => 'integer',
        'district_sr_no' => 'integer',
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

        public function renewals()
        {
            return $this->hasMany(SanghRenewal::class);
        }
}
