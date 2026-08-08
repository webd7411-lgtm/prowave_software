<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'general' => [
                'name' => 'Prowave ERP',
                'logo' => 'images/logo.png',
                'email' => 'info@prowave.com',
                'phone' => '+1 555 123 4567',
                'address' => 'Software Technology Park',
                'website' => 'https://prowave.com',
                'currency' => 'USD',
                'allow_registration' => false,
                'default_role' => 'Employee',
            ],
            'security' => [
                'session_lifetime' => 120,
                'lockout_threshold' => 5,
                'lockout_duration' => 15,
            ],
            'company' => [
                'name' => 'Prowave Software Technology',
                'tagline' => 'Enterprise Software Solutions',
            ],
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value]
                );
            }
        }
    }
}
