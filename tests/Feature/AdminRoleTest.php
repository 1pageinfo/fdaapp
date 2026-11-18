<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/** The old "moderator" role is called "admin"; users who had it keep it. */
class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_leave_admin_role_and_no_moderator(): void
    {
        $this->assertTrue(Role::where('slug', 'admin')->exists());
        $this->assertFalse(Role::where('slug', 'moderator')->exists());
    }

    public function test_seeder_renames_a_leftover_moderator_role_keeping_its_users(): void
    {
        Role::where('slug', 'admin')->update(['slug' => 'moderator']);   // as before the rename
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'moderator')->value('id'));

        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);

        $this->assertFalse(Role::where('slug', 'moderator')->exists());
        $this->assertTrue($user->fresh()->hasRole('admin'), 'user keeps the role under its new name');
    }
}
