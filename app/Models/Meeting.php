<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
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
        'title',
        'start_at',
        'group_id',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
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

        if ($this->created_by === $user->id || $this->assigned_to === $user->id) {
            return true;
        }

        return $this->group_id && $this->group()->whereHas('users', fn ($q) => $q->where('users.id', $user->id))->exists();
    }

    public function isManageableBy(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->created_by === $user->id
            || $this->assigned_to === $user->id;
    }
}
