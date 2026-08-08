<?php

namespace App\Models;

use App\Enums\DailyUpdateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'tasks_completed',
        'work_description',
        'problems',
        'hours_worked',
        'tomorrow_plan',
        'additional_notes',
        'status',
        'review_remarks',
        'reviewed_by',
        'reviewed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'float',
        'status' => DailyUpdateStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', DailyUpdateStatus::Submitted->value);
    }
}
