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
        $validRoles = ['superadmin', 'member'];
        foreach ($validRoles as $role) {
            Role::firstOrCreate(['slug' => $role]);
        }

        // Remove any role outside the valid set (e.g. legacy admin/moderator).
        // role_user/permission_role pivot rows cascade-delete automatically.
        $obsoleteRoles = Role::whereNotIn('slug', $validRoles)->get();
        foreach ($obsoleteRoles as $role) {
            $affectedUsers = $role->users()->get(['name', 'email']);
            if ($affectedUsers->isNotEmpty() && isset($this->command)) {
                $list = $affectedUsers->map(fn ($u) => "{$u->name} <{$u->email}>")->implode(', ');
                $this->command->warn("Removing role '{$role->slug}' — held by: {$list}");
            }
            $role->delete();
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

        // Baseline permissions granted to every member by default — basic account-level
        // navigation (dashboard, own profile, search, notifications), never the sensitive
        // "core" org-data features (sanghs/receipts/groups/etc.), which stay per-user grants
        // made by a superadmin via Settings. Without this, a brand-new signup is locked out
        // of even seeing their own dashboard until someone manually grants permissions.
        $member = Role::where('slug', 'member')->first();
        if ($member) {
            $baselineSlugs = ['dashboard.view', 'profile.view', 'profile.edit', 'search.view', 'notifications.view', 'contacts.view'];
            $baselineIds = Permission::whereIn('slug', $baselineSlugs)->pluck('id');
            $member->permissions()->syncWithoutDetaching($baselineIds);
        }
    }
}
