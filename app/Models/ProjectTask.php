<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ProjectTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_code',
        'title',
        'description',
        'project_id',
        'assigned_to',
        'assigned_by',
        'priority',
        'status',
        'start_date',
        'deadline',
        'estimated_hours',
        'actual_hours',
        'progress',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress' => 'integer',
        'order' => 'integer',
    ];

    public static function generateCode(): string
    {
        $prefix = 'TSK-';

        return DB::transaction(function () use ($prefix) {
            $record = static::withTrashed()->lockForUpdate()->orderByDesc('id')->first();
            $last = $record?->task_code;
            $num = $last ? ((int) substr($last, 4)) + 1 : 1;

            return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id')->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'task_id')->latest();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ProjectTimeEntry::class, 'task_id')->latest();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Completed' => 'bg-success-subtle text-success',
            'In Progress' => 'bg-primary-subtle text-primary',
            'To Do' => 'bg-secondary-subtle text-secondary',
            'Review' => 'bg-info-subtle text-info',
            'Testing' => 'bg-purple-subtle text-purple',
            'On Hold' => 'bg-warning-subtle text-warning',
            'Cancelled' => 'bg-dark-subtle text-dark',
            'Overdue' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Urgent' => 'bg-danger text-white',
            'High' => 'bg-warning text-dark',
            'Medium' => 'bg-info text-dark',
            'Low' => 'bg-light text-dark border',
            default => 'bg-secondary text-white',
        };
    }
}
