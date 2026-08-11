<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message = '',
        public string $icon = 'bi-bell',
        public string $color = 'primary',
        public ?string $url = null,
        public string $category = 'system',
    ) {}

    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && ! $notifiable->wantsNotification($this->category)) {
            return [];
        }

        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'color' => $this->color,
            'url' => $this->url,
            'category' => $this->category,
        ];
    }
}
