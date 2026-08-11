<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Morning Shift', 'code' => 'MORN', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'grace_period' => 15, 'break_minutes' => 60],
            ['name' => 'Evening Shift', 'code' => 'EVE', 'start_time' => '14:00:00', 'end_time' => '23:00:00', 'grace_period' => 15, 'break_minutes' => 45],
            ['name' => 'Night Shift', 'code' => 'NIGHT', 'start_time' => '22:00:00', 'end_time' => '07:00:00', 'grace_period' => 15, 'break_minutes' => 30],
            ['name' => 'Half Day Shift', 'code' => 'HALF', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'grace_period' => 10, 'break_minutes' => 0],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(['code' => $shift['code']], $shift);
        }
    }
}
