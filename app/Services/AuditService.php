<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class AuditService
{
    public function log(
        string $description,
        string $logName = 'default',
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
        ?string $event = null
    ): Activity {
        $activity = activity()
            ->useLog($logName)
            ->causedBy($causer ?? auth()->user())
            ->withProperties($properties);

        if ($subject !== null) {
            $activity->performedOn($subject);
        }

        if ($event) {
            $activity->event($event);
        }

        return $activity->log($description);
    }

    public function logOn(Model $subject, string $description, array $properties = [], ?string $event = null): Activity
    {
        return $this->log($description, 'model', $subject, null, $properties, $event);
    }

    public function logSystem(string $description, array $properties = []): Activity
    {
        return $this->log($description, 'system', null, null, $properties);
    }

    public function logAuth(string $description, array $properties = []): Activity
    {
        return $this->log($description, 'auth', null, null, $properties);
    }

    public function paginate(?int $perPage = 20, array $filters = [])
    {
        $query = Activity::query()->with('causer');

        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['search'])) {
            $query->where('description', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->paginate($perPage);
    }
}
