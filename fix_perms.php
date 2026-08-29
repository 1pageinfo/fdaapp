<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

$user = App\Models\User::where('email', 'sample_user@example.com')->first();
$role = Role::where('slug', 'member')->first();

if ($user && $role) {
    // Attach role to user
    if (!$user->roles()->where('id', $role->id)->exists()) {
        $user->roles()->attach($role->id);
    }
}

$perms = Permission::whereIn('slug', ['sanghs.view', 'sanghs.create', 'sanghs.edit', 'manage-sanghs'])->pluck('id')->toArray();
if ($role) {
    $role->permissions()->syncWithoutDetaching($perms);
}

echo "Permissions added successfully!\n";
