<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone',
        'designation',
        'photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * User ↔ Roles (Many to Many)
     */
   public function roles()
{
    return $this->belongsToMany(Role::class);
}


public function hasRole($role)
{
    return $this->roles()->where('name',$role)->exists();
}

    /**
     * User ↔ Groups (Many to Many)
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class)->withPivot('is_admin');
    }

    /**
     * User ↔ Permissions (through Roles)
     */
    public function permissions()
    {
         return $this->belongsToMany(\App\Models\Permission::class, 'permission_user', 'user_id', 'permission_id');
    }

    public function hasPermission($permission)
{
    return $this->roles()->whereHas('permissions', function($q) use ($permission){
        $q->where('name',$permission);
    })->exists();
}
}
