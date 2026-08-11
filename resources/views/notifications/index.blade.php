@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
            <h1 class="page-title">Notification Center</h1>
            <p class="page-subtitle">All your activity alerts, approvals and reminders in one place.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($notifications->total() > 0)
                <button type="button" class="btn btn-outline" data-form-submit="#mark-all-form">
                    <i class="bi bi-check2-all me-1"></i>Mark all as read
                </button>
                <button type="button" class="btn btn-soft-danger" data-confirm="Clear your entire notification history?" data-form-submit="#clear-all-form">
                    <i class="bi bi-trash me-1"></i>Clear history
                </button>
            @endif
        </div>
    </div>

    <form id="mark-all-form" method="POST" action="{{ route('notifications.read-all') }}" class="d-none">@csrf</form>
    <form id="clear-all-form" method="POST" action="{{ route('notifications.clear') }}" class="d-none">@csrf</form>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            <x-card title="Inbox" icon="bi-bell" body-class="p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('notifications.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ blank($filter) ? 'active' : '' }}">
                        <span><i class="bi bi-stack me-2"></i>All</span>
                        <span class="badge badge-soft-secondary rounded-pill">{{ $notifications->total() }}</span>
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $filter === 'unread' ? 'active' : '' }}">
                        <span><i class="bi bi-envelope-exclamation me-2"></i>Unread</span>
                        <span class="badge badge-soft-primary rounded-pill">{{ $unreadCount }}</span>
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'read']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $filter === 'read' ? 'active' : '' }}">
                        <span><i class="bi bi-envelope-check me-2"></i>Read</span>
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'sent']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $filter === 'sent' ? 'active' : '' }}">
                        <span><i class="bi bi-clock-history me-2"></i>Today</span>
                    </a>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-lg-9">
            <x-card body-class="p-0" :title="blank($filter) ? 'All Notifications' : ucfirst($filter) . ' Notifications'">
                <div class="notification-list">
                    @forelse ($notifications as $notification)
                        @php
                            $data = (array) $notification->data;
                            $url = $data['url'] ?? null;
                            $color = $data['color'] ?? 'primary';
                            $icon = $data['icon'] ?? 'bi-bell';
                        @endphp
                        <div class="notification-item {{ $notification->read_at ? 'is-read' : '' }}">
                            <div class="notification-icon bg-{{ $color }}-soft text-{{ $color }}">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <div class="notification-body flex-grow-1 min-w-0">
                                <div class="fw-600 text-body">{{ $data['title'] ?? 'Notification' }}</div>
                                @if (! empty($data['message']))
                                    <div class="text-muted fs-sm text-truncate">{{ $data['message'] }}</div>
                                @endif
                                <div class="fs-xs text-muted mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                    @if (! empty($data['category']))
                                        · <span class="badge badge-soft-secondary">{{ ucfirst($data['category']) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="notification-actions d-flex align-items-center gap-1">
                                @if (! $notification->read_at)
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-sm btn-soft-primary" title="Mark as read" aria-label="Mark as read">
                                        <i class="bi bi-check2"></i>
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-soft-danger" title="Delete" aria-label="Delete"
                                    data-confirm="Delete this notification?"
                                    data-form-submit="#delete-form-{{ $notification->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            @if ($url)
                                <a href="{{ route('notifications.read', $notification->id) }}" class="notification-link" aria-label="Open related record"></a>
                            @endif
                            <form id="delete-form-{{ $notification->id }}" method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="d-none">@csrf @method('DELETE')</form>
                        </div>
                    @empty
                        <div class="py-5">
                            <x-empty-state
                                title="No {{ blank($filter) ? '' : $filter . ' ' }}notifications"
                                text="You're all caught up. New alerts about your work will appear here."
                                icon="bi-bell-slash"
                            />
                        </div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="p-3 border-top">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
