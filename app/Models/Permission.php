<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Permission extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = ['slug'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
{
    return $this->belongsToMany(\App\Models\User::class, 'permission_user', 'permission_id', 'user_id');
}
}
