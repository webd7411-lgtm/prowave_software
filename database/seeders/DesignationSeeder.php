<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'Software Developer', 'code' => 'DEV', 'department' => 'IT'],
            ['name' => 'Senior Developer', 'code' => 'SDEV', 'department' => 'IT'],
            ['name' => 'Project Manager', 'code' => 'PM', 'department' => 'OPS'],
            ['name' => 'UI/UX Designer', 'code' => 'UIUX', 'department' => 'DES'],
            ['name' => 'Graphic Designer', 'code' => 'GD', 'department' => 'DES'],
            ['name' => 'SEO Specialist', 'code' => 'SEO', 'department' => 'MKT'],
            ['name' => 'Sales Executive', 'code' => 'SE', 'department' => 'SAL'],
            ['name' => 'HR Manager', 'code' => 'HRM', 'department' => 'HR'],
            ['name' => 'Accountant', 'code' => 'ACC', 'department' => 'FIN'],
            ['name' => 'QA Engineer', 'code' => 'QA', 'department' => 'QA'],
            ['name' => 'Chief Technology Officer', 'code' => 'CTO', 'department' => 'MGT'],
        ];

        foreach ($designations as $designation) {
            Designation::updateOrCreate(
                ['code' => $designation['code']],
                [
                    'name' => $designation['name'],
                    'department_id' => Department::where('code', $designation['department'])->value('id'),
                ]
            );
        }
    }
}
