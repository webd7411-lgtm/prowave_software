<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'is_running',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'is_running' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_minutes;
        if ($this->is_running && $this->start_time) {
            $minutes = now()->diffInMinutes($this->start_time);
        }
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02dh %02dm', $hours, $mins);
    }
}
