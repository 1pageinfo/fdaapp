<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'address'     => $request->address,
            'phone'       => $request->phone,
            'designation' => $request->designation,
        ]);

        // Assign default role "member"
        $memberRole = Role::where('slug', 'member')->first();
        if ($memberRole) {
            $user->roles()->syncWithoutDetaching([$memberRole->id]);
        }

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! You are registered.');
    }
}
