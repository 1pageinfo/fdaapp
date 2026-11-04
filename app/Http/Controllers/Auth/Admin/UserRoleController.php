<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserRoleController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();
        return view('admin.user_roles.index', compact('users','roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);
        $roleIds = array_map('intval', $request->input('roles', []));
        $superadminId = Role::where('slug', 'superadmin')->value('id');
        $removingSuperadmin = $user->hasRole('superadmin') && ! in_array($superadminId, $roleIds, true);

        // Never lock the app out: you can't drop your own superadmin role, and at
        // least one superadmin must always remain.
        if ($removingSuperadmin && $user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own superadmin role.');
        }
        if ($removingSuperadmin && Role::find($superadminId)->users()->count() <= 1) {
            return back()->with('error', 'At least one superadmin is required.');
        }

        // Every account keeps a role; with none selected it falls back to Member.
        if (! $roleIds) {
            $roleIds = [Role::where('slug', 'member')->value('id')];
        }

        $user->roles()->sync($roleIds);
        return back()->with('success','Roles updated.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        DB::transaction(function () use ($user) {
            // Detach roles, permissions, and group memberships
            $user->roles()->detach();
            $user->permissions()->detach();
            $user->groups()->detach();

            // Delete profile photo from storage if exists
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }

            // Anonymize personal info and soft delete
            $user->update([
                'name' => 'Deleted User',
                'email' => 'deleted_' . $user->id . '_' . time() . '@example.com',
                'password' => bcrypt(Str::random(40)),
                'address' => null,
                'phone' => null,
                'designation' => null,
                'photo_path' => null,
            ]);

            $user->delete();
        });

        return back()->with('success', 'User has been successfully deleted and anonymized.');
    }
}
