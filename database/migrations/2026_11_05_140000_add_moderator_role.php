<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "moderator" role. Like "member" it starts blank — only the always-on
 * basics; every feature (Sanghs, Folders, Groups, …) is granted per user in
 * Settings → Roles & Access. Both roles also get audit.view (see own activity).
 */
return new class extends Migration
{
    private const BASELINE = ['dashboard.view', 'profile.view', 'profile.edit', 'search.view', 'notifications.view', 'contacts.view', 'audit.view'];

    public function up(): void
    {
        $now = now();
        if (! DB::table('roles')->where('slug', 'moderator')->exists()) {
            DB::table('roles')->insert(['slug' => 'moderator', 'created_at' => $now, 'updated_at' => $now]);
        }

        $permissionIds = DB::table('permissions')->whereIn('slug', self::BASELINE)->pluck('id');
        foreach (DB::table('roles')->whereIn('slug', ['member', 'moderator'])->pluck('id') as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('slug', 'moderator')->value('id');
        if ($roleId) {
            DB::table('permission_role')->where('role_id', $roleId)->delete();
            DB::table('role_user')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
