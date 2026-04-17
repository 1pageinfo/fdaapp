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

    public function hasRole(string|array $role): bool
    {
        $roles = is_array($role) ? $role : [$role];

        return $this->roles()->whereIn('slug', $roles)->exists();
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

    public function hasPermission(string|array $permission): bool
    {
        $permissions = is_array($permission) ? $permission : [$permission];

        if ($this->permissions()->whereIn('slug', $permissions)->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($q) use ($permissions) {
            $q->whereIn('slug', $permissions);
        })->exists();
    }

    public function hasPermissionTo(string|array $permission): bool
    {
        return $this->hasPermission($permission);
    }

    public function allPermissionSlugs(): array
    {
        $this->loadMissing('permissions:id,slug', 'roles.permissions:id,slug');

        $direct = $this->permissions->pluck('slug');
        $viaRoles = $this->roles->flatMap(function ($role) {
            return $role->permissions->pluck('slug');
        });

        return $direct->merge($viaRoles)->unique()->values()->all();
    }
}
