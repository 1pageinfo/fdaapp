<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = ['superadmin', 'admin', 'moderator', 'member'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role]);
        }

        // Permissions
        $permissions = [
            'view-dashboard',
            'manage-users',
            'manage-groups',
            'manage-sanghs',
            'manage-receipts',
            'manage-meetings',
            'manage-files',
            'use-chat',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm]);
        }

        // Assign all permissions to superadmin
        $superadmin = Role::where('slug', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->permissions()->sync(Permission::pluck('id'));
        }
    }
}
