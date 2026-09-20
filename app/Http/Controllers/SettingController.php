<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * All valid "feature.action" slugs, as defined in config/app_permissions.php.
     * Anything outside this set is rejected — permissions are curated, not free text.
     */
    private function validSlugs(): array
    {
        $slugs = [];
        foreach (config('app_permissions.categories', []) as $category) {
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
            'app_name' => config('app.name'),
            'contact_email' => '',
        ];

        $categories = config('app_permissions.categories', []);

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
            Log::info('Saving app settings', [
                'app_name' => $request->input('app_name'),
                'contact_email' => $request->input('contact_email')
            ]);
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
}
