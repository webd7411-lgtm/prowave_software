<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public const ICONS = [
        'lead' => 'bi-person-lines-fill',
        'followup' => 'bi-calendar-check',
        'client' => 'bi-buildings',
        'project' => 'bi-folder-symlink',
        'task' => 'bi-check2-square',
        'leave' => 'bi-door-open',
        'update' => 'bi-journal-text',
        'invoice' => 'bi-receipt',
        'payment' => 'bi-cash-coin',
        'expense' => 'bi-receipt-cutoff',
        'meeting' => 'bi-people',
        'document' => 'bi-file-earmark',
        'system' => 'bi-bell',
    ];

    public function send(User $user, string $title, string $message = '', array $opts = []): void
    {
        $category = $opts['category'] ?? 'system';
        $user->notify(new AppNotification(
            title: $title,
            message: $message,
            icon: $opts['icon'] ?? (self::ICONS[$category] ?? self::ICONS['system']),
            color: $opts['color'] ?? 'primary',
            url: $opts['url'] ?? null,
            category: $category,
        ));
    }

    /**
     * Notify multiple users (respects each user's preferences).
     */
    public function sendMany(iterable $users, string $title, string $message = '', array $opts = []): void
    {
        foreach ($users as $user) {
            if ($user instanceof User && $user->id === auth()->id()) {
                continue;
            }
            $this->send($user, $title, $message, $opts);
        }
    }

    /**
     * Notify all users holding a given permission, excluding the current actor.
     */
    public function sendToPermission(string $permission, string $title, string $message = '', array $opts = []): void
    {
        $users = User::query()->active()->whereHas('roles', function ($q) use ($permission) {
            $q->whereHas('permissions', fn ($p) => $p->where('name', $permission));
        })->get();

        $this->sendMany($users, $title, $message, $opts);
    }

    public function notifyRelated(Model $subject, string $action, array $opts = []): void
    {
        $relation = $opts['relation'] ?? null;
        $users = collect();

        if ($relation && method_exists($subject, $relation)) {
            $users = $subject->{$relation}()->get();
        }

        $this->sendMany($users, $opts['title'] ?? $action, $opts['message'] ?? '', $opts);
    }

    public function unreadCount(?User $user = null): int
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return 0;
        }

        return (int) DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->whereNull('read_at')
            ->count();
    }
}
