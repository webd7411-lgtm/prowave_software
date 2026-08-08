<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'total_work_minutes',
        'break_minutes',
        'late_minutes',
        'early_minutes',
        'overtime_minutes',
        'status',
        'remarks',
        'shift_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_work_minutes' => 'integer',
        'break_minutes' => 'integer',
        'late_minutes' => 'integer',
        'early_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'status' => AttendanceStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}
