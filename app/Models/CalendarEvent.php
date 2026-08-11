<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    public const TYPES = [
        'event' => 'Event',
        'holiday' => 'Holiday',
        'announcement' => 'Announcement',
        'reminder' => 'Reminder',
    ];

    protected $fillable = [
        'title',
        'event_type',
        'event_date',
        'start_time',
        'end_time',
        'all_day',
        'color',
        'description',
        'location',
        'reminder',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'all_day' => 'boolean',
        'reminder' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())->orderBy('event_date')->orderBy('start_time');
    }
}
