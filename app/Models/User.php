<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Enums\Gender;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'username',
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'whatsapp',
        'father_name',
        'cnic',
        'gender',
        'dob',
        'joining_date',
        'department_id',
        'job_title',
        'department_label',
        'designation',
        'employment_type',
        'employee_status',
        'salary',
        'shift_id',
        'reporting_manager_id',
        'bio',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'emergency_contact',
        'emergency_phone',
        'notes',
        'theme',
        'language',
        'locale',
        'timezone',
        'date_format',
        'is_active',
        'status',
        'login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'status' => UserStatus::class,
        'employee_status' => EmployeeStatus::class,
        'employment_type' => EmploymentType::class,
        'gender' => Gender::class,
        'dob' => 'date',
        'joining_date' => 'date',
        'salary' => 'float',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'login_attempts' => 'integer',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'username', 'employee_id', 'job_title',
                'designation', 'department_id', 'reporting_manager_id', 'status', 'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }

        return null;
    }

    public function activities(): HasMany
    {
        return $this->hasMany(config('activitylog.activity_model'), 'causer_id');
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class, 'employee_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class, 'assigned_to');
    }

    public function dailyWorkUpdates(): HasMany
    {
        return $this->hasMany(DailyWorkUpdate::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'reporting_manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', UserStatus::Inactive->value);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', UserStatus::Suspended->value);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('employee_id', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function updateLastLogin(): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->save();
    }

    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');

        $threshold = (int) setting('security.lockout_threshold', 5);

        if ($this->fresh()->login_attempts >= $threshold) {
            $this->lockAccount();
        }
    }

    public function resetLoginAttempts(): void
    {
        $this->forceFill([
            'login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    public function lockAccount(): void
    {
        $minutes = (int) setting('security.lockout_duration', 15);

        $this->forceFill([
            'locked_until' => now()->addMinutes($minutes),
        ])->save();
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function remainingLockSeconds(): int
    {
        if (! $this->isLocked()) {
            return 0;
        }

        return max(0, $this->locked_until->diffInSeconds(now(), false));
    }
}
