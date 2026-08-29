<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SanghFeeSlab extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = [
        'min_members',
        'max_members',
        'annual_fee',
    ];

    protected $casts = [
        'min_members' => 'integer',
        'max_members' => 'integer',
        'annual_fee' => 'decimal:2',
    ];

    public static function annualFeeForMemberCount(?int $totalMembers): ?float
    {
        if ($totalMembers === null) {
            return null;
        }

        $slab = static::query()
            ->where('min_members', '<=', $totalMembers)
            ->where(function ($q) use ($totalMembers) {
                $q->whereNull('max_members')->orWhere('max_members', '>=', $totalMembers);
            })
            ->orderBy('min_members')
            ->first();

        return $slab ? (float) $slab->annual_fee : null;
    }
}
