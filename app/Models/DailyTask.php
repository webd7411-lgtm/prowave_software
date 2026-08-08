<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DailyTask extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'department_id',
        'priority',
        'start_date',
        'deadline',
        'status',
        'estimated_hours',
        'actual_hours',
        'attachment',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'priority' => Priority::class,
        'status' => TaskStatus::class,
        'start_date' => 'date',
        'deadline' => 'date',
        'estimated_hours' => 'float',
        'actual_hours' => 'float',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'assigned_to', 'deadline'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('task');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== TaskStatus::Completed->value
            && $this->status !== TaskStatus::Cancelled->value
            && $this->deadline
            && $this->deadline->isPast();
    }
}
