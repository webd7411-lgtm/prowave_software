<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use HasFactory;

    public const TYPES = [
        'client' => 'Client Meeting',
        'internal' => 'Internal Meeting',
        'project' => 'Project Meeting',
        'sales' => 'Sales Meeting',
        'hr' => 'HR Meeting',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'title',
        'type',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'client_id',
        'project_id',
        'participant_ids',
        'description',
        'agenda',
        'notes',
        'decisions',
        'action_items',
        'follow_up_date',
        'status',
        'reminder_sent',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'participant_ids' => 'array',
        'reminder_sent' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): \Illuminate\Support\Collection
    {
        return User::query()->whereIn('id', (array) ($this->participant_ids ?? []))->orderBy('name')->get();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('meeting_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('meeting_date')->orderBy('start_time');
    }
}
