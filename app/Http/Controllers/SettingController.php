<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\Permission;
use App\Support\SanghFormFields;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * All valid "feature.action" slugs, as defined in config/app_permissions.php.
     * Anything outside this set is rejected — permissions are curated, not free text.
     */
    /**
     * Features a superadmin can grant per user — config minus always-on features
     * (every user has them) and superadmin-only ones (never grantable).
     */
    private function grantableCategories(): array
    {
        $out = [];
        foreach (config('app_permissions.categories', []) as $key => $category) {
            $category['features'] = array_filter(
                $category['features'],
                fn ($def) => empty($def['always_on']) && empty($def['superadmin_only'])
            );
            if ($category['features']) {
                $out[$key] = $category;
            }
        }

        return $out;
    }

    private function validSlugs(): array
    {
        $slugs = [];
        foreach ($this->grantableCategories() as $category) {
            foreach ($category['features'] as $feature => $def) {
                foreach ($def['actions'] as $action) {
                    $slugs[] = "{$feature}.{$action}";
                }
            }
        }

        return $slugs;
    }

    // -------------------------------
    // INDEX
    // -------------------------------
    public function index(Request $request)
    {
        $canManagePermissions = $request->user()->hasRole('superadmin');

        $users = $canManagePermissions ? User::orderBy('name')->get() : collect();
        $selectedUser = null;
        $assignedSlugs = [];

        if ($canManagePermissions && $request->filled('user_id')) {
            $selectedUser = User::with('permissions')->find($request->user_id);
            if ($selectedUser) {
                $assignedSlugs = $selectedUser->permissions->pluck('slug')->toArray();
            }
        }

        $settings = [
            'app_name' => Setting::getValue('app_name', config('app.name')),
            'contact_email' => Setting::getValue('contact_email', ''),
        ];

        $categories = $this->grantableCategories();

        return view('settings.index', compact('users', 'selectedUser', 'assignedSlugs', 'settings', 'categories', 'canManagePermissions'));
    }

    // -------------------------------
    // UPDATE (THIS MUST BE INSIDE CLASS)
    // -------------------------------
    public function update(Request $request)
    {
        Log::info('Settings.update called', $request->all());

        // Save app settings (optional)
        if ($request->filled('app_name') || $request->has('contact_email')) {
            abort_unless($request->user()->hasRole('superadmin') || $request->user()->hasPermission('settings.edit'), 403);

            $request->validate([
                'app_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
            ]);

            if ($request->filled('app_name')) {
                Setting::set('app_name', $request->input('app_name'));
            }
            if ($request->has('contact_email')) {
                Setting::set('contact_email', $request->input('contact_email'));
            }
        }

        // Save user permissions — superadmin only. Granting permissions is itself a
        // privileged action; a user with generic "settings.edit" must never be able
        // to hand out permissions (including to themselves).
        if ($request->filled('user_id')) {
            abort_unless($request->user()->hasRole('superadmin'), 403);

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permissions' => 'array',
                'permissions.*' => 'string',
            ]);

            $user = User::findOrFail($request->user_id);
            $validSlugs = $this->validSlugs();
            $submittedSlugs = array_values(array_intersect($request->input('permissions', []), $validSlugs));

            Log::info("Updating permissions for User {$user->id}", [
                'slugs' => $submittedSlugs
            ]);

            DB::beginTransaction();
            try {
                $permissionIds = Permission::whereIn('slug', $submittedSlugs)->pluck('id');
                $user->permissions()->sync($permissionIds);

                DB::commit();

                return redirect()
                    ->route('settings.index', ['user_id' => $user->id])
                    ->with('success', 'Permissions updated!');
            }
            catch (\Throwable $e) {
                DB::rollBack();
                Log::error($e);
                return back()->withErrors('Error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Settings Saved!');
    }

    // -------------------------------
    // SANGH FORM FIELD ACCESS (per user)
    // -------------------------------
    /**
     * Save which Sangh form fields a user may edit. The form submits the fields
     * left switched ON (editable); everything else is stored as locked.
     */
    public function updateSanghFields(Request $request)
    {
        abort_unless($request->user()->hasRole('superadmin'), 403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'editable_fields' => 'array',
            'editable_fields.*' => 'string',
        ]);

        $user = User::findOrFail($request->user_id);
        $editable = SanghFormFields::sanitize($request->input('editable_fields', []));
        $locked = array_values(array_diff(SanghFormFields::names(), $editable));

        $user->forceFill(['locked_sangh_fields' => $locked ?: null])->save();

        $message = $locked
            ? count($locked) . ' Sangh form field(s) locked for ' . $user->name . '.'
            : 'All Sangh form fields are editable for ' . $user->name . '.';

        return redirect()
            ->route('settings.index', ['user_id' => $user->id, 'tab' => 'fields'])
            ->with('success', $message);
    }
}
