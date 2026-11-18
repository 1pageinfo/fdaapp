<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "moderator" role is now called "admin". The role row is renamed in place,
 * so its id — and every user who holds it, and its permissions — carry over unchanged.
 * If an "admin" role somehow exists already, moderator users and permissions are
 * moved onto it and the moderator role is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rename('moderator', 'admin');
    }

    public function down(): void
    {
        $this->rename('admin', 'moderator');
    }

    private function rename(string $from, string $to): void
    {
        $fromId = DB::table('roles')->where('slug', $from)->value('id');
        if (! $fromId) {
            return;
        }

        $toId = DB::table('roles')->where('slug', $to)->value('id');
        if (! $toId) {
            DB::table('roles')->where('id', $fromId)->update(['slug' => $to, 'updated_at' => now()]);

            return;
        }

        // Both exist: merge into the target role, then drop the old one.
        foreach (DB::table('role_user')->where('role_id', $fromId)->pluck('user_id') as $userId) {
            DB::table('role_user')->insertOrIgnore(['role_id' => $toId, 'user_id' => $userId]);
        }
        foreach (DB::table('permission_role')->where('role_id', $fromId)->pluck('permission_id') as $permissionId) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $toId, 'permission_id' => $permissionId]);
        }
        DB::table('role_user')->where('role_id', $fromId)->delete();
        DB::table('permission_role')->where('role_id', $fromId)->delete();
        DB::table('roles')->where('id', $fromId)->delete();
    }
};
