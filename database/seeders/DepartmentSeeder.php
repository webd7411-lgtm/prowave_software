<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'Software development, infrastructure and support.'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Recruitment, onboarding and people operations.'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Accounting, invoicing and payroll.'],
            ['name' => 'Sales', 'code' => 'SAL', 'description' => 'Sales and account management.'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Branding, campaigns and lead generation.'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Project delivery and business operations.'],
            ['name' => 'Quality Assurance', 'code' => 'QA', 'description' => 'Testing and quality control.'],
            ['name' => 'Design', 'code' => 'DES', 'description' => 'UI/UX and visual design.'],
            ['name' => 'Management', 'code' => 'MGT', 'description' => 'Executive leadership.'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(['code' => $department['code']], $department);
        }
    }
}
