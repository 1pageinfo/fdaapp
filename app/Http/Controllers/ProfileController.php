<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,'.$user->id,
            'phone'       => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:255',
            'address'     => 'nullable|string|max:1000',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_photo'=> 'nullable|boolean',
        ]);

        // Handle photo remove or upload
        if ($request->boolean('remove_photo') && $user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
            $user->photo_path = null;
        }

        if ($request->hasFile('photo')) {
            // store in /public/profiles/{user_id}/filename
            $path = $request->file('photo')
                ->store('profiles/'.$user->id, 'public');

            // delete old if exists
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $user->photo_path = $path;
        }

        $user->name        = $validated['name'];
        $user->email       = $validated['email'];
        $user->phone       = $validated['phone'] ?? null;
        $user->designation = $validated['designation'] ?? null;
        $user->address     = $validated['address'] ?? null;
        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success','Password changed successfully.');
    }
}
