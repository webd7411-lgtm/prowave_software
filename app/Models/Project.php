<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'name',
        'client_id',
        'project_manager_id',
        'description',
        'project_type',
        'start_date',
        'deadline',
        'priority',
        'status',
        'budget',
        'estimated_hours',
        'actual_hours',
        'progress',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'budget' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress' => 'integer',
    ];

    public static function generateCode(): string
    {
        $prefix = 'PRJ-';

        return DB::transaction(function () use ($prefix) {
            $record = static::withTrashed()->lockForUpdate()->orderByDesc('id')->first();
            $last = $record?->project_code;
            $num = $last ? ((int) substr($last, 4)) + 1 : 1;

            return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot(['role', 'assigned_date', 'hours_allocation', 'status'])
                    ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ProjectTimeEntry::class)->latest();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getHealthAttribute(): string
    {
        if ($this->status === 'Completed') {
            return 'Completed';
        }
        if ($this->status === 'Cancelled') {
            return 'Cancelled';
        }

        $today = now()->startOfDay();
        $hasOverdueDeadline = $this->deadline && $this->deadline->lt($today);

        if ($hasOverdueDeadline) {
            return 'Delayed';
        }

        $overdueTasksCount = $this->tasks()
            ->where('status', '!=', 'Completed')
            ->where('deadline', '<', $today)
            ->count();

        $daysRemaining = $this->deadline ? $today->diffInDays($this->deadline, false) : 999;

        if ($overdueTasksCount > 0 || ($daysRemaining <= 5 && $this->progress < 70)) {
            return 'At Risk';
        }

        return 'Healthy';
    }

    public function getHealthBadgeClassAttribute(): string
    {
        return match ($this->health) {
            'Healthy' => 'bg-success-subtle text-success border border-success-subtle',
            'At Risk' => 'bg-warning-subtle text-warning border border-warning-subtle',
            'Delayed' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'Completed' => 'bg-info-subtle text-info border border-info-subtle',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Completed' => 'bg-success-subtle text-success',
            'In Progress' => 'bg-primary-subtle text-primary',
            'Planning' => 'bg-info-subtle text-info',
            'Not Started' => 'bg-secondary-subtle text-secondary',
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

    public function getProgressAttribute($value): int
    {
        if ($this->status === 'Completed') {
            return 100;
        }
        return (int) ($value ?? 0);
    }

    public function recalculateProgress(): void
    {
        if ($this->status === 'Completed') {
            $this->update(['progress' => 100]);
            return;
        }

        $totalTasks = $this->tasks()->count();
        if ($totalTasks > 0) {
            $completedTasks = $this->tasks()->where('status', 'Completed')->count();
            $avgProgress = (int) round(($completedTasks / $totalTasks) * 100);
            $this->update(['progress' => $avgProgress]);
        }
    }
}
