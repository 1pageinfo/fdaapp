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


    protected $fillable = ['name','description', 'sort_order', 'created_by', 'assigned_to'];

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $this->created_by === $user->id
            || $this->assigned_to === $user->id
            || $this->users()->where('users.id', $user->id)->exists();
    }

    public function isManageableBy(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->created_by === $user->id
            || $this->assigned_to === $user->id;
    }

    public function chats()
    {
        return $this->hasMany(Chat::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
