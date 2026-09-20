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
        $roles = ['superadmin', 'member'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role]);
        }

        // Feature/action permissions, driven by config/app_permissions.php
        // (the single source of truth also used by the Settings screen).
        $validSlugs = [];
        foreach (config('app_permissions.categories', []) as $category) {
            foreach ($category['features'] as $feature => $def) {
                foreach ($def['actions'] as $action) {
                    $validSlugs[] = "{$feature}.{$action}";
                }
            }
        }

        foreach ($validSlugs as $slug) {
            Permission::firstOrCreate(['slug' => $slug]);
        }

        // Remove permission rows that no longer correspond to a real feature/action
        // (old speculative features like reports/export/tabs/pin/coordination/work_app/users,
        // legacy manage-* slugs, and the retired standalone "update" action). Pivot rows in
        // role_permission/permission_user cascade-delete automatically.
        Permission::whereNotIn('slug', $validSlugs)->delete();

        // Assign all current permissions to superadmin
        $superadmin = Role::where('slug', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->permissions()->sync(Permission::pluck('id'));
        }
    }
}
