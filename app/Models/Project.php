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
        'billing_type',
        'recurring_amount',
        'billing_start_date',
        'next_billing_date',
        'billing_alert_day',
        'last_invoiced_at',
        'start_date',
        'deadline',
        'priority',
        'status',
        'budget',
        'currency',
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
        'billing_start_date' => 'date',
        'next_billing_date' => 'date',
        'last_invoiced_at' => 'date',
        'recurring_amount' => 'decimal:2',
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function isRecurring(): bool
    {
        return in_array($this->billing_type, ['monthly', 'annually']);
    }

    public function isRenewalDue(): bool
    {
        if (! $this->isRecurring() || ! $this->next_billing_date) {
            return false;
        }

        $today = now()->startOfDay();
        $due = $this->next_billing_date->copy()->startOfDay();

        if ($this->billing_type === 'monthly') {
            $alertDay = $this->billing_alert_day ?: 5;
            // Trigger alert on or after the alert day of current month, or if overdue
            return $today->day >= $alertDay || $today->gte($due);
        }

        // Annually: alert N days before year completion
        $daysBefore = $this->billing_alert_day ?: 7;
        return $today->gte($due->copy()->subDays($daysBefore));
    }

    public function getPaidAmountAttribute(): float
    {
        $direct = (float) $this->payments()->sum('amount');
        $invoicePayments = (float) Payment::whereHas('invoice', function ($q) {
            $q->where('project_id', $this->id);
        })->whereNotIn('id', $this->payments()->pluck('id'))->sum('amount');

        $totalPaid = $direct + $invoicePayments;

        if ($this->budget > 0) {
            return min((float) $this->budget, $totalPaid);
        }

        return $totalPaid;
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->budget - $this->paid_amount);
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
        $totalTasks = $this->tasks()->count();
        if ($totalTasks > 0) {
            $completedTasks = $this->tasks()->where('status', 'Completed')->count();
            $avgProgress = (int) round(($completedTasks / $totalTasks) * 100);
            $data = ['progress' => $avgProgress];
            if ($avgProgress === 100 && $this->status !== 'Cancelled') {
                $data['status'] = 'Completed';
            }
            $this->update($data);
        } elseif ($this->status === 'Completed') {
            $this->update(['progress' => 100]);
        }
    }
}
