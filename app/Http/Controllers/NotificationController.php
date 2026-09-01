<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = auth()->user()->notifications()->latest();

        if ($request->filled('filter')) {
            if ($request->string('filter')->toString() === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->string('filter')->toString() === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->string('filter')->toString() === 'sent') {
                $query->whereDate('created_at', today());
            }
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'filter' => $request->string('filter')->toString(),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id): RedirectResponse
    {
        $notification = $this->findNotification($id);
        $notification->markAsRead();

        $url = $this->notificationUrl($notification);

        return $url ? redirect($url) : back();
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->findNotification($id)->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Notification deleted.',
        ]);
    }

    public function destroyAll(): RedirectResponse
    {
        auth()->user()->notifications()->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Notification history cleared.',
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        if (session()->isStarted()) {
            session()->save();
        }

        return response()->json(['count' => auth()->user()->unreadNotifications()->count()]);
    }

    public function recent(): JsonResponse
    {
        if (session()->isStarted()) {
            session()->save();
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('leads:meeting-reminders');
        } catch (\Throwable $e) {
            // best-effort reminder check
        }

        $notifications = auth()->user()->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'icon' => $n->data['icon'] ?? 'bi-bell',
                'color' => $n->data['color'] ?? 'primary',
                'url' => $n->data['url'] ?? null,
                'read' => (bool) $n->read_at,
                'time' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'unread' => auth()->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    protected function findNotification(string $id): DatabaseNotification
    {
        return auth()->user()->notifications()->findOrFail($id);
    }

    protected function notificationUrl(DatabaseNotification $notification): ?string
    {
        $data = (array) $notification->data;

        return $data['url'] ?? null;
    }
}
