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

    /** Query version of isVisibleTo(): created by, assigned to, or for a group the user is in. */
    public function scopeVisibleTo($query, User $user)
    {
        return $user->hasRole('superadmin') ? $query
            : $query->where(fn ($q) => $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhereIn('group_id', Group::query()->visibleTo($user)->select('id')));
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($this->created_by === $user->id || $this->assigned_to === $user->id) {
            return true;
        }

        // Same as the Meetings list: meetings of any group the user can see.
        return $this->group_id && $this->group && $this->group->isVisibleTo($user);
    }

    public function isManageableBy(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->created_by === $user->id
            || $this->assigned_to === $user->id;
    }
}
