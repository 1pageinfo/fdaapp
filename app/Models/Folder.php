<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Folder extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = ['name', 'parent_id', 'owner_group_id', 'year', 'sort_order', 'created_by', 'assigned_to'];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id')->orderBy('sort_order');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'owner_group_id');
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

        if ($this->owner_group_id) {
            return $this->group()->whereHas('users', fn ($q) => $q->where('users.id', $user->id))->exists();
        }

        return false;
    }

    public function isManageableBy(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->created_by === $user->id
            || $this->assigned_to === $user->id;
    }
}
