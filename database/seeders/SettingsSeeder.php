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
                'name' => 'Prowave Technologies',
                'logo' => 'images/logo.jpg',
                'email' => 'prowavesolution06@gmail.com',
                'phone' => '+1 555 123 4567',
                'address' => 'Software Technology Park',
                'website' => 'https://prowavetechnologies.com',
                'currency' => 'PKR',
                'allow_registration' => false,
                'default_role' => 'Employee',
                'whatsapp' => '',
                'city' => '',
                'country' => '',
                'tax_number' => '',
                'registration_number' => '',
                'timezone' => 'UTC',
                'date_format' => 'd M, Y',
                'time_format' => 'h:i A',
                'fiscal_year_start' => '01-01',
                'pagination_size' => 15,
                'language' => 'en',
                'default_theme' => 'light',
                'working_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'weekend_days' => ['Sat', 'Sun'],
                'default_tax' => 0,
                'working_hours' => 8,
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
            'branding' => [
                'primary_color' => '#2563EB',
                'secondary_color' => '#6366F1',
                'email_footer' => '',
            ],
            'email' => [
                'host' => '',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_name' => '',
                'from_address' => '',
            ],
            'notifications' => [
                'lead' => true,
                'followup' => true,
                'client' => true,
                'project' => true,
                'task' => true,
                'leave' => true,
                'update' => true,
                'invoice' => true,
                'payment' => true,
                'expense' => true,
                'meeting' => true,
                'document' => true,
                'system' => true,
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
