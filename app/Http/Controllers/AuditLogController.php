<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('audit.view');

        $query = Activity::query()->with('causer:id,name,avatar');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return view('audit.index', [
            'logs' => $query->latest()->paginate(25)->withQueryString(),
            'filters' => $request->all(),
            'logNames' => Activity::query()->select('log_name')->distinct()->orderBy('log_name')->pluck('log_name'),
            'users' => \App\Models\User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('audit.view');

        $query = Activity::query()->with('causer:id,name');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=audit-logs.csv',
        ];

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date/Time', 'User', 'Action', 'Module', 'Event']);

            $query->latest()->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->causer?->name ?? 'System',
                        $log->description,
                        $log->log_name,
                        $log->event ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $headers);
    }
}
