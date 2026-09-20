<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Link extends Model
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
        'user_id',
        'assigned_to',
        'title',
        'platform',
        'category',
        'url',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->user_id === $user->id
            || $this->assigned_to === $user->id;
    }

    public function isManageableBy(User $user): bool
    {
        return $this->isVisibleTo($user);
    }

    // optional helper scopes
    public function scopeSocial($query)
    {
        return $query->where('category', 'social');
    }

    public function scopeOther($query)
    {
        return $query->where('category', 'other');
    }

    public function scopeCustom($query)
    {
        return $query->where('category', 'custom');
    }
}
