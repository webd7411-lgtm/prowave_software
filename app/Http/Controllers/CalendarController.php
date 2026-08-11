<?php

namespace App\Http\Controllers;

use App\Enums\LeaveStatus;
use App\Models\CalendarEvent;
use App\Models\DailyTask;
use App\Models\LeaveRequest;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('calendar.view');

        $meetings = Meeting::query()
            ->where('meeting_date', '>=', now()->subMonths(2)->toDateString())
            ->orderBy('meeting_date')
            ->get();

        $upcoming = collect()
            ->merge($this->taskEvents(now()->toDateString(), now()->addDays(14)->toDateString()))
            ->merge($this->meetingEvents(now()->toDateString(), now()->addDays(14)->toDateString()))
            ->merge($this->leaveEvents(now()->toDateString(), now()->addDays(14)->toDateString()))
            ->sortBy('start')
            ->take(12);

        return view('calendar.index', [
            'meetings' => $meetings,
            'upcoming' => $upcoming,
            'view' => $request->string('view')->toString() ?: 'month',
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $this->authorize('calendar.view');

        $start = $request->date('start') ?? now()->startOfMonth();
        $end = $request->date('end') ?? now()->endOfMonth();

        $events = collect();

        if ($request->user()->can('project_tasks.view')) {
            $events = $events->merge($this->taskEvents($start, $end));
        }
        if ($request->user()->can('projects.view')) {
            $events = $events->merge($this->milestoneEvents($start, $end));
        }
        if ($request->user()->can('tasks.view')) {
            $events = $events->merge($this->dailyTaskEvents($start, $end));
        }
        if ($request->user()->can('leave.view')) {
            $events = $events->merge($this->leaveEvents($start, $end));
        }
        if ($request->user()->can('meetings.view')) {
            $events = $events->merge($this->meetingEvents($start, $end));
        }

        $events = $events->merge(
            CalendarEvent::query()
                ->whereBetween('event_date', [$start, $end])
                ->get()
                ->map(fn ($e) => [
                    'id' => 'ce_'.$e->id,
                    'title' => $e->title,
                    'start' => $e->all_day ? $e->event_date->format('Y-m-d') : $e->event_date->format('Y-m-d').'T'.($e->start_time ?? '09:00'),
                    'end' => $e->all_day ? null : $e->event_date->format('Y-m-d').'T'.($e->end_time ?? '17:00'),
                    'allDay' => (bool) $e->all_day,
                    'color' => $e->color,
                    'type' => ucfirst($e->event_type),
                    'editable' => $e->created_by === auth()->id(),
                    'meta' => ['event_type' => $e->event_type],
                ])
        );

        return response()->json($events->values());
    }

    protected function taskEvents($start, $end)
    {
        return ProjectTask::query()
            ->with(['project:id,name', 'assignedTo:id,name'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$start, $end])
            ->get()
            ->map(fn ($t) => [
                'id' => 'task_'.$t->id,
                'title' => $t->title,
                'start' => $t->deadline->format('Y-m-d'),
                'allDay' => true,
                'color' => match ($t->priority) {
                    'Urgent' => '#EF4444',
                    'High' => '#F59E0B',
                    default => '#2563EB',
                },
                'type' => 'Task',
                'status' => $t->status,
                'url' => route('projects.tasks.show', $t),
                'project' => $t->project?->name,
            ]);
    }

    protected function milestoneEvents($start, $end)
    {
        return ProjectMilestone::query()
            ->with('project:id,name')
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$start, $end])
            ->get()
            ->map(fn ($m) => [
                'id' => 'ms_'.$m->id,
                'title' => $m->name,
                'start' => $m->deadline->format('Y-m-d'),
                'allDay' => true,
                'color' => '#10B981',
                'type' => 'Milestone',
                'status' => $m->status,
                'url' => route('projects.show', $m->project_id),
                'project' => $m->project?->name,
            ]);
    }

    protected function dailyTaskEvents($start, $end)
    {
        return DailyTask::query()
            ->with(['assignee:id,name'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$start, $end])
            ->get()
            ->map(fn ($t) => [
                'id' => 'dtask_'.$t->id,
                'title' => $t->title,
                'start' => $t->deadline->format('Y-m-d'),
                'allDay' => true,
                'color' => $t->status === 'overdue' ? '#EF4444' : '#8B5CF6',
                'type' => 'Daily Task',
                'status' => $t->status,
                'url' => route('hr.tasks.index', ['search' => $t->title]),
            ]);
    }

    protected function leaveEvents($start, $end)
    {
        return LeaveRequest::query()
            ->with(['employee:id,name', 'leaveType:id,name'])
            ->where('status', LeaveStatus::Approved->value)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->map(fn ($l) => [
                'id' => 'leave_'.$l->id,
                'title' => ($l->employee?->name ?? 'Employee').' — '.($l->leaveType?->name ?? 'Leave'),
                'start' => $l->start_date->format('Y-m-d'),
                'end' => $l->end_date->addDay()->format('Y-m-d'),
                'allDay' => true,
                'color' => '#F59E0B',
                'type' => 'Leave',
                'url' => route('hr.leave.index', ['status' => 'approved']),
            ]);
    }

    protected function meetingEvents($start, $end)
    {
        return Meeting::query()
            ->with(['client:id,company_name', 'project:id,name'])
            ->whereBetween('meeting_date', [$start, $end])
            ->get()
            ->map(fn ($m) => [
                'id' => 'meeting_'.$m->id,
                'title' => $m->title,
                'start' => $m->meeting_date->format('Y-m-d').'T'.($m->start_time ?? '10:00'),
                'end' => $m->meeting_date->format('Y-m-d').'T'.($m->end_time ?? '11:00'),
                'allDay' => false,
                'color' => match ($m->type) {
                    'client' => '#0EA5E9',
                    'sales' => '#2563EB',
                    'hr' => '#F59E0B',
                    'project' => '#10B981',
                    default => '#6366F1',
                },
                'type' => 'Meeting',
                'status' => $m->status,
                'url' => route('meetings.index', ['search' => $m->title]),
                'meta' => ['meeting_id' => $m->id],
            ]);
    }
}
