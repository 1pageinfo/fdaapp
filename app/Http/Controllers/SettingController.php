<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    // -------------------------------
    // INDEX
    // -------------------------------
    public function index(Request $request)
    {
        $users = User::orderBy('name')->get();
        $selectedUser = null;
        $assignedSlugs = [];

        if ($request->filled('user_id')) {
            $selectedUser = User::with('permissions')->find($request->user_id);
            if ($selectedUser) {
                $assignedSlugs = $selectedUser->permissions->pluck('slug')->toArray();
            }
        }

        $settings = [
            'app_name' => config('app.name'),
            'contact_email' => '',
        ];

        $features = [
            'dashboard','receipts','sanghs','meetings','folders','files','groups','chats',
            'users','settings','reports','export','profile','search','notifications','tabs',
            'pin','audit','sangh_fee','coordination','work_app'
        ];

        return view('settings.index', compact('users','selectedUser','assignedSlugs','settings','features'));
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

        // Save user permissions
        if ($request->filled('user_id')) {

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permissions' => 'array',
                'permissions.*' => 'string'
            ]);

            $user = User::findOrFail($request->user_id);
            $submittedSlugs = $request->input('permissions', []);

            Log::info("Updating permissions for User {$user->id}", [
                'slugs' => $submittedSlugs
            ]);

            DB::beginTransaction();
            try {

                $permissionIds = [];
                foreach ($submittedSlugs as $slug) {
                    $perm = Permission::firstOrCreate(['slug' => $slug]);
                    $permissionIds[] = $perm->id;
                }

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
