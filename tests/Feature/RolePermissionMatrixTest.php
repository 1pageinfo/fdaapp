<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\File;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Link;
use App\Models\Meeting;
use App\Models\Permission;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Sangh;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role × permission × ownership, end to end through real requests:
 *  - Superadmin: every page and action.
 *  - Admin and Member: a feature action only with its permission, and only on
 *    records they created or that were assigned to them (never someone else's).
 *  - Always-on pages for everyone; Settings / Fees / Users & Roles superadmin only.
 */
class RolePermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    private User $super;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->super = $this->userWithRole('superadmin');
        $this->other = $this->userWithRole('member');
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['slug' => $slug])->id);

        return $user;
    }

    private function grant(User $user, string ...$slugs): void
    {
        foreach ($slugs as $slug) {
            $user->permissions()->syncWithoutDetaching([Permission::firstOrCreate(['slug' => $slug])->id]);
        }
    }

    private function hit(User $user, string $method, string $url): int
    {
        $this->app['auth']->forgetGuards();

        return $this->actingAs($user)->call($method, $url)->getStatusCode();
    }

    private function assertAllowed(User $user, string $method, string $url, string $why): void
    {
        $code = $this->hit($user, $method, $url);
        $this->assertTrue($code !== 403 && $code !== 404 && $code < 500, "{$why}: expected allowed, got {$code} for {$method} {$url}");
    }

    private function assertDenied(User $user, string $method, string $url, string $why): void
    {
        $code = $this->hit($user, $method, $url);
        $this->assertSame(403, $code, "{$why}: expected 403, got {$code} for {$method} {$url}");
    }

    /** One record of every kind, owned by $owner (and optionally assigned to $assignee). */
    private function records(User $owner, ?User $assignee = null): array
    {
        $a = $assignee?->id;
        $group = Group::create(['name' => 'G ' . uniqid(), 'created_by' => $owner->id, 'assigned_to' => $a]);
        $folder = Folder::create(['name' => 'F ' . uniqid(), 'created_by' => $owner->id, 'assigned_to' => $a]);

        return [
            'sangh' => Sangh::create(['name_of_sangh' => 'संघ ' . uniqid(), 'created_by' => $owner->id, 'assigned_to' => $a, 'registration_year' => 2026]),
            'group' => $group,
            'chat' => Chat::create(['group_id' => $group->id, 'tab' => 'main']),
            'folder' => $folder,
            'file' => File::create(['folder_id' => $folder->id, 'name' => 'f.pdf', 'path' => 'x/f.pdf', 'disk_path' => 'x/f.pdf', 'uploaded_by' => $owner->id]),
            'meeting' => Meeting::create(['title' => 'M', 'start_at' => now()->addDay(), 'created_by' => $owner->id, 'assigned_to' => $a]),
            'link' => Link::create(['title' => 'L', 'url' => 'https://example.com', 'user_id' => $owner->id, 'assigned_to' => $a]),
            'receipt' => Receipt::create(['subject' => 'R', 'amount' => 10, 'user_id' => $owner->id]),
        ];
    }

    /** feature => [action => [method, url]] for one set of records. */
    private function routes(array $r): array
    {
        return [
            'sanghs' => [
                'view' => [['GET', '/sanghs'], ['GET', "/sanghs/{$r['sangh']->id}"], ['GET', "/sanghs/{$r['sangh']->id}/receipts"]],
                'create' => [['GET', '/sanghs/create']],
                'edit' => [['GET', "/sanghs/{$r['sangh']->id}/edit"]],
            ],
            'groups' => [
                'view' => [['GET', '/groups'], ['GET', "/groups/{$r['group']->id}"]],
                'create' => [['GET', '/groups/create']],
                'edit' => [['GET', "/groups/{$r['group']->id}/edit"]],
            ],
            'chats' => [
                'view' => [['GET', "/groups/{$r['group']->id}/chat/{$r['chat']->id}"]],
            ],
            'folders' => [
                'view' => [['GET', '/folders'], ['GET', "/folders/{$r['folder']->id}"]],
                'create' => [['GET', '/folders/create']],
                'edit' => [['GET', "/folders/{$r['folder']->id}/edit"]],
            ],
            'files' => [
                'view' => [['GET', '/files']],
                'create' => [['GET', '/files/create']],
                'edit' => [['GET', "/files/{$r['file']->id}/edit"]],
            ],
            'meetings' => [
                'view' => [['GET', '/meetings'], ['GET', "/meetings/{$r['meeting']->id}"]],
                'create' => [['GET', '/meetings/create']],
                'edit' => [['GET', "/meetings/{$r['meeting']->id}/edit"]],
            ],
            'links' => [
                'view' => [['GET', '/links']],
                'create' => [['GET', '/links/create']],
                'edit' => [['GET', "/links/{$r['link']->id}/edit"]],
            ],
            'receipts' => [
                'view' => [['GET', '/receipts']],
                'create' => [['GET', '/receipts/create']],
            ],
        ];
    }

    /** Record-specific URLs (ones that point at a single record). */
    private function recordUrls(array $r): array
    {
        return [
            'sanghs' => ["/sanghs/{$r['sangh']->id}", "/sanghs/{$r['sangh']->id}/edit", "/sanghs/{$r['sangh']->id}/receipts"],
            'groups' => ["/groups/{$r['group']->id}", "/groups/{$r['group']->id}/edit"],
            'chats' => ["/groups/{$r['group']->id}/chat/{$r['chat']->id}"],
            'folders' => ["/folders/{$r['folder']->id}", "/folders/{$r['folder']->id}/edit"],
            'files' => ["/files/{$r['file']->id}/edit"],
            'meetings' => ["/meetings/{$r['meeting']->id}", "/meetings/{$r['meeting']->id}/edit"],
            'links' => ["/links/{$r['link']->id}/edit"],
        ];
    }

    private const ALWAYS_ON = ['/dashboard', '/profile', '/contacts', '/search?q=test', '/notifications', '/admin/activity-logs'];
    private const SUPERADMIN_ONLY = ['/settings', '/settings/sangh-fees', '/admin/user-roles'];

    public function test_superadmin_can_access_everything_including_others_records(): void
    {
        $r = $this->records($this->other);
        foreach ($this->routes($r) as $feature => $actions) {
            foreach ($actions as $action => $urls) {
                foreach ($urls as [$m, $u]) {
                    $this->assertAllowed($this->super, $m, $u, "superadmin {$feature}.{$action}");
                }
            }
        }
        foreach ([...self::ALWAYS_ON, ...self::SUPERADMIN_ONLY, '/sanghs-export', '/receipts-export', '/groups-export', '/sanghs-template'] as $u) {
            $this->assertAllowed($this->super, 'GET', $u, 'superadmin page');
        }
        // Delete someone else's records.
        foreach (['sanghs' => $r['sangh'], 'meetings' => $r['meeting'], 'links' => $r['link']] as $feature => $model) {
            $this->assertAllowed($this->super, 'DELETE', "/{$feature}/{$model->id}", "superadmin delete {$feature}");
        }
    }

    public function test_admin_and_member_without_permissions_get_only_always_on_pages(): void
    {
        foreach (['admin', 'member'] as $role) {
            $user = $this->userWithRole($role);
            $r = $this->records($user); // even their OWN records need the permission
            foreach (self::ALWAYS_ON as $u) {
                $this->assertAllowed($user, 'GET', $u, "{$role} always-on");
            }
            foreach (self::SUPERADMIN_ONLY as $u) {
                $this->assertDenied($user, 'GET', $u, "{$role} superadmin-only");
            }
            foreach ($this->routes($r) as $feature => $actions) {
                foreach ($actions as $action => $urls) {
                    foreach ($urls as [$m, $u]) {
                        $this->assertDenied($user, $m, $u, "{$role} without {$feature}.{$action}");
                    }
                }
            }
            $this->assertDenied($user, 'DELETE', "/sanghs/{$r['sangh']->id}", "{$role} without sanghs.delete");
        }
    }

    public function test_each_permission_opens_only_its_own_action(): void
    {
        foreach (['admin', 'member'] as $role) {
            foreach (['sanghs', 'groups', 'folders', 'files', 'meetings', 'links', 'receipts'] as $feature) {
                foreach (['view', 'create', 'edit'] as $granted) {
                    $user = $this->userWithRole($role);
                    $r = $this->records($user);
                    $map = $this->routes($r)[$feature];
                    if (! isset($map[$granted])) {
                        continue;
                    }
                    $this->grant($user, "{$feature}.{$granted}");
                    foreach ($map as $action => $urls) {
                        foreach ($urls as [$m, $u]) {
                            $action === $granted
                                ? $this->assertAllowed($user, $m, $u, "{$role} with {$feature}.{$granted}")
                                : $this->assertDenied($user, $m, $u, "{$role} with only {$feature}.{$granted} trying {$action}");
                        }
                    }
                }
            }
        }
    }

    public function test_admin_and_member_see_own_and_assigned_records_but_never_others(): void
    {
        foreach (['admin', 'member'] as $role) {
            $user = $this->userWithRole($role);
            foreach (['sanghs', 'groups', 'chats', 'folders', 'files', 'meetings', 'links', 'receipts'] as $f) {
                $this->grant($user, "{$f}.view", "{$f}.create", "{$f}.edit", "{$f}.delete");
            }
            $own = $this->records($user);
            $assigned = $this->records($this->other, $user);
            $others = $this->records($this->other);

            foreach ($this->recordUrls($own) as $f => $urls) {
                foreach ($urls as $u) {
                    $this->assertAllowed($user, 'GET', $u, "{$role} own {$f}");
                }
            }
            foreach ($this->recordUrls($assigned) as $f => $urls) {
                if ($f === 'files') {
                    continue; // a file follows its folder; assigned folder covered above
                }
                foreach ($urls as $u) {
                    $this->assertAllowed($user, 'GET', $u, "{$role} assigned {$f}");
                }
            }
            foreach ($this->recordUrls($others) as $f => $urls) {
                foreach ($urls as $u) {
                    $this->assertDenied($user, 'GET', $u, "{$role} someone else's {$f}");
                }
            }

            // Lists show own + assigned, never others'.
            $this->actingAs($user)->get('/sanghs')->assertSee($own['sangh']->name_of_sangh)
                ->assertSee($assigned['sangh']->name_of_sangh)->assertDontSee($others['sangh']->name_of_sangh);
            $this->actingAs($user)->get('/groups')->assertSee($own['group']->name)
                ->assertSee($assigned['group']->name)->assertDontSee($others['group']->name);
            $this->actingAs($user)->get('/folders')->assertSee($own['folder']->name)
                ->assertDontSee($others['folder']->name);

            // Delete: someone else's record refused, own allowed.
            $this->assertDenied($user, 'DELETE', "/sanghs/{$others['sangh']->id}", "{$role} delete others' sangh");
            $this->assertDenied($user, 'DELETE', "/meetings/{$others['meeting']->id}", "{$role} delete others' meeting");
            $this->assertDenied($user, 'DELETE', "/links/{$others['link']->id}", "{$role} delete others' link");
            $this->assertAllowed($user, 'DELETE', "/sanghs/{$own['sangh']->id}", "{$role} delete own sangh");
            $this->assertNull(Sangh::find($own['sangh']->id));
            $this->assertNotNull(Sangh::find($others['sangh']->id));
        }
    }

    public function test_admin_and_member_cannot_use_superadmin_actions_even_with_all_permissions(): void
    {
        foreach (['admin', 'member'] as $role) {
            $user = $this->userWithRole($role);
            foreach (['sanghs', 'groups', 'folders', 'files', 'meetings', 'links', 'receipts', 'chats'] as $f) {
                $this->grant($user, "{$f}.view", "{$f}.create", "{$f}.edit", "{$f}.delete");
            }
            $this->assertDenied($user, 'PUT', "/admin/user-roles/{$this->other->id}", "{$role} change roles");
            $this->assertDenied($user, 'PUT', '/settings', "{$role} change settings");
            $this->assertDenied($user, 'PUT', '/settings/sangh-fees', "{$role} change fees");
            $this->assertDenied($user, 'POST', '/sanghs-import-range', "{$role} import by Sangh No.");
            $this->assertDenied($user, 'POST', '/sanghs-seed-placeholders', "{$role} seed placeholders");

            // Cannot hand a Sangh to someone (assigned_to ignored for non-superadmins).
            $this->actingAs($user)->post('/sanghs', [
                'name_of_sangh' => 'स्वतःचा संघ', 'category_code' => 'R', 'sangh_type_code' => 'G',
                'pradeshik_vibhag' => 'पुणे', 'district' => 'पुणे', 'male' => '20', 'female' => '10',
                'assigned_to' => $this->other->id,
            ]);
            $created = Sangh::where('name_of_sangh', 'स्वतःचा संघ')->where('created_by', $user->id)->first();
            $this->assertNotNull($created, "{$role} could not create a Sangh with sanghs.create");
            $this->assertNull($created->assigned_to, "{$role} must not be able to assign a Sangh");
            $created->delete();
        }
    }

    public function test_activity_log_shows_only_own_actions_for_admin_and_member(): void
    {
        $user = $this->userWithRole('admin');
        $this->grant($user, 'sanghs.create', 'sanghs.view');
        $this->actingAs($this->other);
        Sangh::create(['name_of_sangh' => 'दुसऱ्याचा संघ', 'created_by' => $this->other->id]);
        $this->actingAs($user);
        Sangh::create(['name_of_sangh' => 'माझा संघ', 'created_by' => $user->id]);

        $this->actingAs($user)->get('/admin/activity-logs')->assertOk()->assertDontSee('दुसऱ्याचा संघ');
        $this->actingAs($this->super)->get('/admin/activity-logs')->assertOk();
    }
}
