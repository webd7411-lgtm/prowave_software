<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('meetings.view');

        $query = Meeting::query()
            ->with(['client:id,company_name', 'project:id,name', 'creator:id,name'])
            ->latest('meeting_date');

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('location', 'like', '%'.$request->search.'%'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('meeting_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('meeting_date', '<=', $request->to);
        }

        return view('meetings.index', [
            'meetings' => $query->paginate(15)->withQueryString(),
            'filters' => $request->all(),
            'clients' => Client::query()->where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->with('roles')->active()->orderBy('name')->get(['id', 'name', 'job_title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('meetings.create');

        $data = $this->validateData($request);
        $data['participant_ids'] = array_values(array_filter(array_map('intval', (array) ($data['participant_ids'] ?? []))));
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $meeting = Meeting::create($data);

        $this->notifyParticipants($meeting, 'Meeting scheduled', 'You are invited to "'.$meeting->title.'" on '.$meeting->meeting_date->format('M d, Y').'.');

        activity()->on($meeting)->causedBy(auth()->user())->log('created meeting');

        return redirect()->route('meetings.index')->with('toast', [
            'type' => 'success',
            'message' => 'Meeting scheduled successfully.',
        ]);
    }

    public function show(Meeting $meeting): View
    {
        $this->authorize('meetings.view');

        $meeting->load(['client', 'project', 'creator']);

        return view('meetings.show', [
            'meeting' => $meeting,
            'participants' => $meeting->participants(),
            'clients' => Client::query()->where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->with('roles')->active()->orderBy('name')->get(['id', 'name', 'job_title']),
        ]);
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('meetings.edit');

        $data = $this->validateData($request);
        $data['participant_ids'] = array_values(array_filter(array_map('intval', (array) ($data['participant_ids'] ?? []))));
        $data['updated_by'] = auth()->id();

        $oldParticipants = (array) ($meeting->participant_ids ?? []);
        $meeting->update($data);

        $newParticipants = array_values(array_diff((array) $meeting->participant_ids, $oldParticipants));
        if ($newParticipants) {
            $this->notifyParticipants(
                Meeting::find($meeting->id),
                'Meeting invitation',
                'You are invited to "'.$meeting->title.'" on '.$meeting->meeting_date->format('M d, Y').'.',
                $newParticipants
            );
        }

        activity()->on($meeting)->causedBy(auth()->user())->log('updated meeting');

        return redirect()->route('meetings.show', $meeting)->with('toast', [
            'type' => 'success',
            'message' => 'Meeting updated successfully.',
        ]);
    }

    public function updateNotes(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('meetings.edit');

        $data = $request->validate([
            'status' => ['required', 'in:scheduled,ongoing,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'decisions' => ['nullable', 'string', 'max:10000'],
            'action_items' => ['nullable', 'string', 'max:10000'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $meeting->update($data + ['updated_by' => auth()->id()]);

        activity()->on($meeting)->causedBy(auth()->user())->log('recorded meeting notes');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Meeting notes saved.',
        ]);
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        $this->authorize('meetings.delete');

        activity()->on($meeting)->causedBy(auth()->user())->log('deleted meeting');
        $meeting->delete();

        return redirect()->route('meetings.index')->with('toast', [
            'type' => 'success',
            'message' => 'Meeting deleted.',
        ]);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:client,internal,project,sales,hr,other'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'agenda' => ['nullable', 'string', 'max:10000'],
        ]);
    }

    protected function notifyParticipants(Meeting $meeting, string $title, string $message, ?array $onlyIds = null): void
    {
        $ids = $onlyIds ?? (array) ($meeting->participant_ids ?? []);
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (! $ids) {
            return;
        }

        $participants = User::whereIn('id', $ids)->get();

        app(NotificationService::class)->sendMany($participants, $title, $message, [
            'category' => 'meeting',
            'url' => route('meetings.show', $meeting),
            'color' => 'info',
            'include_self' => true,
        ]);
    }
}
