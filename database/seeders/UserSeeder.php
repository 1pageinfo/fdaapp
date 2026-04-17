<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default Super Admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'), // 🔑 change in production
                'address' => 'Default Address',
                'phone' => '9999999999',
                'designation' => 'System Admin',
            ]
        );

        // Ensure admin has every role and permission.
        $user->roles()->sync(Role::pluck('id')->all());
        $user->permissions()->sync(Permission::pluck('id')->all());
    }
}
