<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'Software Developer', 'code' => 'DEV', 'department' => 'IT', 'role' => 'Developer'],
            ['name' => 'Senior Developer', 'code' => 'SDEV', 'department' => 'IT', 'role' => 'Developer'],
            ['name' => 'Project Manager', 'code' => 'PM', 'department' => 'OPS', 'role' => 'Project Manager'],
            ['name' => 'UI/UX Designer', 'code' => 'UIUX', 'department' => 'DES', 'role' => 'Designer'],
            ['name' => 'Graphic Designer', 'code' => 'GD', 'department' => 'DES', 'role' => 'Designer'],
            ['name' => 'SEO Specialist', 'code' => 'SEO', 'department' => 'MKT', 'role' => null],
            ['name' => 'Sales Executive', 'code' => 'SE', 'department' => 'SAL', 'role' => 'Sales Executive'],
            ['name' => 'HR Manager', 'code' => 'HRM', 'department' => 'HR', 'role' => 'HR Manager'],
            ['name' => 'Accountant', 'code' => 'ACC', 'department' => 'FIN', 'role' => 'Accountant'],
            ['name' => 'QA Engineer', 'code' => 'QA', 'department' => 'QA', 'role' => 'QA'],
            ['name' => 'Chief Technology Officer', 'code' => 'CTO', 'department' => 'MGT', 'role' => 'Administrator'],
        ];

        foreach ($designations as $designation) {
            Designation::updateOrCreate(
                ['code' => $designation['code']],
                [
                    'name' => $designation['name'],
                    'department_id' => Department::where('code', $designation['department'])->value('id'),
                    'role_id' => $designation['role'] ? Role::where('name', $designation['role'])->value('id') : null,
                ]
            );
        }
    }
}
