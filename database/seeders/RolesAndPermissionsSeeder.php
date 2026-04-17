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

        // Feature/action permissions used by the settings screen.
        $features = [
            'dashboard', 'receipts', 'sanghs', 'meetings', 'folders', 'files',
            'groups', 'chats', 'users', 'settings', 'reports', 'export',
            'profile', 'search', 'notifications', 'tabs', 'pin', 'audit',
            'sangh_fee', 'coordination', 'work_app',
        ];
        $actions = ['create', 'view', 'edit', 'update', 'delete'];

        $permissions = [];
        foreach ($features as $feature) {
            foreach ($actions as $action) {
                $permissions[] = "{$feature}.{$action}";
            }
        }

        // Keep legacy slugs for backward compatibility.
        $permissions = array_merge($permissions, [
            'view-dashboard',
            'manage-users',
            'manage-groups',
            'manage-sanghs',
            'manage-receipts',
            'manage-meetings',
            'manage-files',
            'use-chat',
        ]);

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
