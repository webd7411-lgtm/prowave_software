<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'deadline',
        'status',
        'progress',
        'notes',
        'order',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'progress' => 'integer',
        'order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'milestone_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Completed' => 'bg-success-subtle text-success',
            'In Progress' => 'bg-primary-subtle text-primary',
            'Pending' => 'bg-secondary-subtle text-secondary',
            'On Hold' => 'bg-warning-subtle text-warning',
            'Overdue' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function recalculateProgress(): void
    {
        $total = $this->tasks()->count();
        if ($total > 0) {
            $completed = $this->tasks()->where('status', 'Completed')->count();
            $progress = (int) round(($completed / $total) * 100);
            $status = $progress === 100 ? 'Completed' : ($progress > 0 ? 'In Progress' : $this->status);
            $this->update(['progress' => $progress, 'status' => $status]);
        }
    }
}
