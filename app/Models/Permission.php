<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
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
