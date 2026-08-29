<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = ['name','description', 'sort_order'];

    // Default tabs for a new group (you can seed/create these)
    public const DEFAULT_TABS = [
        'Correspondence',
        'Workshops / Adhiveshan',
        'Ahawal',
        'Coordination Committees of Collector',
        'Work Appraisal',
        'Samnvay Samiti',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin')->withTimestamps();
    }

    public function chats()
    {
        return $this->hasMany(Chat::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
