<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@prowave.com'],
            [
                'name' => 'Prowave Administrator',
                'email' => 'admin@prowave.com',
                'username' => 'admin',
                'employee_id' => 'EMP-0001',
                'password' => Hash::make('password'),
                'job_title' => 'System Administrator',
                'designation' => 'Chief Technology Officer',
                'department_label' => 'Information Technology',
                'department_id' => Department::where('code', 'IT')->value('id'),
                'is_active' => true,
                'status' => UserStatus::Active->value,
                'email_verified_at' => now(),
                'joining_date' => now()->subYears(2),
            ]
        );

        $admin->assignRole('Super Admin');
    }
}
