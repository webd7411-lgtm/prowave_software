<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'days_per_year' => 12, 'is_paid' => true, 'description' => 'Short, unplanned time off.'],
            ['name' => 'Sick Leave', 'code' => 'SL', 'days_per_year' => 10, 'is_paid' => true, 'description' => 'Paid leave for illness or medical appointments.'],
            ['name' => 'Annual Leave', 'code' => 'AL', 'days_per_year' => 15, 'is_paid' => true, 'description' => 'Planned paid time off.'],
            ['name' => 'Emergency Leave', 'code' => 'EL', 'days_per_year' => 5, 'is_paid' => true, 'description' => 'Leave for urgent personal or family matters.'],
            ['name' => 'Unpaid Leave', 'code' => 'UL', 'days_per_year' => 10, 'is_paid' => false, 'description' => 'Leave without pay.'],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
