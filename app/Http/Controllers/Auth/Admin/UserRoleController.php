<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

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
        $user->roles()->sync($request->roles ?? []);
        return back()->with('success','Roles updated.');
    }
}
