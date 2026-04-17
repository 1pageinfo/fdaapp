<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('app_name', config('app.name', 'My App'));
        Setting::set('contact_email', 'admin@example.com');
    }
}
