<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\LeadRequest;
use App\Models\{Client, Lead, LeadActivity, LeadFollowup, LeadStageHistory, LeadStatus, User};
use App\Services\NotificationService;

use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('leads.view');

        $query = Lead::with('owner')->select('leads.*')->latest();

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        if ($request->filled('status') && $request->status === 'contacted') {
            $query->where('status', '!=', 'new');
        } else {
            foreach (['status', 'source', 'priority', 'owner_id'] as $field) {
                if ($request->filled($field)) {
                    $query->where($field, $request->$field);
                }
            }
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('company_name', 'like', '%' . $request->search . '%')
                ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%'));
        }

        $statusList = $this->statusList();

        return view('crm.leads.index', [
            'leads' => $query->simplePaginate(15)->withQueryString(),
            'filters' => $request->all(),
            'owners' => User::orderBy('name')->pluck('name', 'id'),
            'sources' => $this->sources(),
            'statuses' => $statusList,
            'statusColors' => $statusList->pluck('color', 'slug')->all(),
        ]);
    }

    public function create(): View
    {
        return view('crm.leads.form', [
            'lead' => new Lead,
            'owners' => User::orderBy('name')->get(),
            'sources' => $this->sources(),
            'statuses' => $this->statuses(),
            'statusColors' => $this->statusList()->pluck('color', 'slug')->all(),
            'action' => route('crm.leads.store'),
        ]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['follow_up_date', 'follow_up_time'])->all();

        if (! auth()->user()->can('leads.view_all')) {
            $data['owner_id'] = auth()->id();
        } else {
            $data['owner_id'] = $data['owner_id'] ?? auth()->id();
        }

        $data += [
            'lead_number' => Lead::generateNumber(),
            'stage' => $this->stage($data['status']),
            'source' => 'other',
            'priority' => 'medium',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        $lead = Lead::create($data);

        if ($data['status'] === 'meeting_scheduled') {
            $this->scheduleMeeting($lead, $request->follow_up_date, $request->follow_up_time);
        }

        LeadStageHistory::create([
            'lead_id' => $lead->id,
            'to_stage' => $lead->stage,
            'changed_by' => auth()->id(),
            'entered_at' => now(),
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => 'created',
            'subject' => 'Lead created',
            'created_by' => auth()->id(),
        ]);

        app(NotificationService::class)->sendToPermission(
            'leads.view',
            'New lead received',
            $lead->company_name . ($lead->contact_person ? ' (' . $lead->contact_person . ')' : ''),
            ['category' => 'lead', 'url' => route('crm.leads.show', $lead)]
        );

        return redirect()->route('crm.leads.show', $lead)
            ->with('toast', ['type' => 'success', 'message' => 'Lead created successfully.']);
    }

    public function show(Lead $lead): View
    {
        $lead->load(['owner', 'followups.assignee', 'activities.author', 'stageHistories', 'client']);

        return view('crm.leads.show', [
            'lead' => $lead,
            'statusColors' => $this->statusList()->pluck('color', 'slug')->all(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        return view('crm.leads.form', [
            'lead' => $lead,
            'owners' => User::orderBy('name')->get(),
            'sources' => $this->sources(),
            'statuses' => $this->statuses(),
            'statusColors' => $this->statusList()->pluck('color', 'slug')->all(),
            'action' => route('crm.leads.update', $lead),
        ]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();
        $data = collect($data)->except(['follow_up_date', 'follow_up_time'])->all();

        if (! auth()->user()->can('leads.view_all')) {
            unset($data['owner_id']);
        }

        $stage = $this->stage($data['status']);

        if ($stage !== $lead->stage) {
            LeadStageHistory::where('lead_id', $lead->id)->whereNull('left_at')->update(['left_at' => now()]);
            LeadStageHistory::create([
                'lead_id' => $lead->id,
                'from_stage' => $lead->stage,
                'to_stage' => $stage,
                'changed_by' => auth()->id(),
                'entered_at' => now(),
            ]);
        }

        if ($data['status'] === 'meeting_scheduled') {
            $this->scheduleMeeting($lead, $request->follow_up_date, $request->follow_up_time);
        }

        $lead->update($data + ['stage' => $stage, 'updated_by' => auth()->id()]);

        return redirect()->route('crm.leads.show', $lead)
            ->with('toast', ['type' => 'success', 'message' => 'Lead updated.']);
    }

    public function move(Request $request, Lead $lead): RedirectResponse
    {
        $rules = ['stage' => 'required|in:new,contacted,qualified,proposal,negotiation,meeting,won,lost'];

        if ($request->stage === 'meeting') {
            $rules['follow_up_date'] = 'required|date|after_or_equal:today';
            $rules['follow_up_time'] = 'required|date_format:H:i';
        }

        $request->validate($rules);

        $status = $request->stage === 'proposal' ? 'proposal_sent' : ($request->stage === 'meeting' ? 'meeting_scheduled' : $request->stage);
        $stageUpdate = $this->updateStage($lead, $request->stage, $status);

        if ($request->stage === 'meeting' && $request->filled('follow_up_date') && $request->filled('follow_up_time')) {
            $this->scheduleMeeting($lead, $request->follow_up_date, $request->follow_up_time);
        }

        return $stageUpdate;
    }

    public function updateStatus(Request $request, Lead $lead): \Illuminate\Http\JsonResponse
    {
        $rules = ['status' => 'required|string|in:' . implode(',', $this->statusSlugs())];

        if ($request->status === 'meeting_scheduled') {
            $rules['follow_up_date'] = 'required|date';
            $rules['follow_up_time'] = 'required';
        }

        $request->validate($rules);

        $status = $request->status;
        $stage = $this->stage($status);

        if ($stage !== $lead->stage) {
            LeadStageHistory::where('lead_id', $lead->id)->whereNull('left_at')->update(['left_at' => now()]);
            LeadStageHistory::create([
                'lead_id' => $lead->id,
                'from_stage' => $lead->stage,
                'to_stage' => $stage,
                'changed_by' => auth()->id(),
                'entered_at' => now(),
            ]);
        }

        $lead->update(['status' => $status, 'stage' => $stage, 'updated_by' => auth()->id()]);

        if ($status === 'meeting_scheduled' && $request->filled('follow_up_date') && $request->filled('follow_up_time')) {
            $this->scheduleMeeting($lead, $request->follow_up_date, $request->follow_up_time);
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'stage' => $stage,
            'message' => $status === 'meeting_scheduled' ? 'Meeting scheduled.' : 'Status updated.',
        ]);
    }

    private function updateStage(Lead $lead, string $stage, string $status): RedirectResponse
    {
        LeadStageHistory::where('lead_id', $lead->id)->whereNull('left_at')->update(['left_at' => now()]);
        LeadStageHistory::create([
            'lead_id' => $lead->id,
            'from_stage' => $lead->stage,
            'to_stage' => $stage,
            'changed_by' => auth()->id(),
            'entered_at' => now(),
        ]);
        $lead->update(['stage' => $stage, 'status' => $status, 'updated_by' => auth()->id()]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Pipeline stage updated.']);
    }

    public function pipeline(): View
    {
        return view('crm.leads.pipeline', [
            'leads' => Lead::with('owner')->whereNotIn('stage', ['won', 'lost'])->get()->groupBy('stage'),
            'stages' => [
                'new' => 'New',
                'contacted' => 'Contacted',
                'qualified' => 'Qualified',
                'proposal' => 'Proposal',
                'negotiation' => 'Negotiation',
                'meeting' => 'Meeting',
                'won' => 'Won',
                'lost' => 'Lost',
            ],
        ]);
    }

    public function followup(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate([
            'follow_up_date' => 'required|date',
            'follow_up_time' => 'nullable',
            'type' => 'required|in:call,email,meeting,other',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:3000',
        ]);

        $fu = $lead->followups()->create($request->only('follow_up_date', 'follow_up_time', 'type', 'assigned_to', 'notes') + [
            'status' => 'pending',
            'reminder' => $request->boolean('reminder'),
            'created_by' => auth()->id(),
        ]);

        if ($request->type === 'meeting') {
            $lead->update(['status' => 'meeting_scheduled', 'stage' => 'meeting']);
        }

        $ns = app(NotificationService::class);

        if ($fu->assigned_to) {
            $ns->send(
                User::find($fu->assigned_to),
                'Follow-up assigned: ' . $lead->company_name,
                ucfirst($fu->type) . ' scheduled for ' . $fu->follow_up_date->format('M d, Y'),
                ['category' => 'followup', 'url' => route('crm.leads.show', $lead), 'color' => 'info']
            );
        } else {
            $ns->sendToPermission(
                'followups.view',
                'New follow-up scheduled',
                $lead->company_name . ' — ' . ucfirst($fu->type) . ' on ' . $fu->follow_up_date->format('M d, Y'),
                ['category' => 'followup', 'url' => route('crm.leads.show', $lead), 'color' => 'info', 'include_self' => true]
            );
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Follow-up scheduled.']);
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate(['client_id' => 'nullable|exists:clients,id']);

        return DB::transaction(function () use ($request, $lead) {
            $client = $request->client_id
                ? Client::findOrFail($request->client_id)
                : Client::firstOrCreate(
                    ['email' => $lead->email],
                    [
                        'client_number' => Client::generateNumber(),
                        'company_name' => $lead->company_name,
                        'contact_person' => $lead->contact_person,
                        'phone' => $lead->phone,
                        'website' => $lead->website,
                        'address' => $lead->address,
                        'city' => $lead->city,
                        'country' => $lead->country,
                        'account_manager_id' => $lead->owner_id,
                        'status' => 'active',
                        'created_by' => auth()->id(),
                    ]
                );

            $lead->update([
                'status' => 'converted',
                'stage' => 'won',
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'type' => 'conversion',
                'subject' => 'Converted to client ' . $client->client_number,
                'created_by' => auth()->id(),
            ]);

            $ns = app(NotificationService::class);
            $ns->sendToPermission(
                'clients.view',
                'Lead converted to client',
                $lead->company_name . ' is now a client',
                ['category' => 'client', 'url' => route('crm.clients.show', $client), 'color' => 'success']
            );

            if ($lead->owner_id) {
                $ns->send(
                    User::find($lead->owner_id),
                    'Lead converted',
                    'Congratulations — ' . $lead->company_name . ' converted to a client.',
                    ['category' => 'lead', 'url' => route('crm.clients.show', $client), 'color' => 'success']
                );
            }

            return redirect()->route('crm.clients.show', $client)
                ->with('toast', ['type' => 'success', 'message' => 'Lead converted to client.']);
        });
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('crm.leads.index')
            ->with('toast', ['type' => 'success', 'message' => 'Lead deleted.']);
    }

    private function sources(): array
    {
        return [
            'website' => 'Website',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'referral' => 'Referral',
            'fiverr' => 'Fiverr',
            'upwork' => 'Upwork',
            'google' => 'Google',
            'linkedin' => 'LinkedIn',
            'direct' => 'Direct',
            'other' => 'Other',
        ];
    }

    private function statuses(): array
    {
        return LeadStatus::where('is_active', 1)->orderBy('sort_order')->pluck('name', 'slug')->all();
    }

    private function statusList()
    {
        return cache()->remember('lead_statuses_active', 3600, fn () => LeadStatus::where('is_active', 1)->orderBy('sort_order')->get(['slug', 'name', 'color']));
    }

    private function statusSlugs(): array
    {
        return LeadStatus::pluck('slug')->all();
    }

    private function stage(string $status): string
    {
        return match ($status) {
            'proposal_sent' => 'proposal',
            'converted' => 'won',
            'meeting_scheduled' => 'meeting',
            default => $status,
        };
    }

    public function storeStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
        ]);

        $slug = Str::slug(trim($request->name));

        if (! $slug) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Invalid status name.']);
        }

        if (LeadStatus::where('slug', $slug)->exists()) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Status already exists.']);
        }

        LeadStatus::create([
            'name' => trim($request->name),
            'slug' => $slug,
            'color' => $request->color ?: '#6c757d',
            'sort_order' => (int) LeadStatus::max('sort_order') + 1,
            'is_active' => 1,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Status "' . trim($request->name) . '" added.']);
    }

    public function destroyStatus(string $leadStatus): RedirectResponse
    {
        $status = LeadStatus::where('slug', $leadStatus)->firstOrFail();
        $status->update(['is_active' => 0]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Status "' . $status->name . '" deactivated.']);
    }

    private function scheduleMeeting(Lead $lead, string $date, string $time): LeadFollowup
    {
        $fu = $lead->followups()->create([
            'follow_up_date' => $date,
            'follow_up_time' => $time,
            'type' => 'meeting',
            'status' => 'scheduled',
            'reminder' => 1,
            'assigned_to' => $lead->owner_id ?: auth()->id(),
            'notes' => 'Meeting scheduled via lead status update',
            'created_by' => auth()->id(),
        ]);

        $lead->loadMissing('owner');
        $when = date('M d, Y', strtotime($date)) . ' at ' . date('h:i A', strtotime($time));
        $contactDetails = implode(' · ', array_filter([$lead->contact_person, $lead->phone ?: $lead->email]));
        $ownerName = $lead->owner?->name ?? 'Unassigned';

        $title = 'Meeting Scheduled: ' . $lead->company_name;
        $msg = "Meeting: {$when}\nBusiness: {$lead->company_name}\nContact: " . ($contactDetails ?: 'N/A') . "\nLead Owner: {$ownerName}";

        $ns = app(NotificationService::class);

        $targetRoles = ['Sales Manager', 'Sales Executive', 'Administrator', 'Super Admin'];
        $managers = User::query()->active()->whereHas('roles', fn ($q) => $q->whereIn('name', $targetRoles))->get();

        $recipients = collect($managers);
        if ($lead->owner_id) {
            $owner = User::find($lead->owner_id);
            if ($owner) {
                $recipients->push($owner);
            }
        }
        if (auth()->user()) {
            $recipients->push(auth()->user());
        }
        $recipients = $recipients->unique('id');

        $ns->sendMany($recipients, $title, $msg, [
            'category' => 'meeting',
            'url' => route('crm.leads.show', $lead),
            'color' => 'success',
            'include_self' => true,
        ]);

        return $fu;
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Lead::with('owner')->latest();

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        foreach (['status', 'source', 'priority', 'owner_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('company_name', 'like', '%' . $request->search . '%')
                ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%'));
        }

        $leads = $query->get();

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Lead Number', 'Company', 'Contact Person', 'Phone', 'Email',
                'Website', 'Address', 'City', 'Country', 'Source', 'Service Interested',
                'Estimated Budget', 'Owner', 'Status', 'Priority', 'Expected Closing Date', 'Notes',
            ]);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->lead_number,
                    $lead->company_name,
                    $lead->contact_person,
                    $lead->phone,
                    $lead->email,
                    $lead->website,
                    $lead->address,
                    $lead->city,
                    $lead->country,
                    $lead->source,
                    $lead->service_interested,
                    $lead->estimated_budget,
                    $lead->owner?->name,
                    $lead->status,
                    $lead->priority,
                    $lead->expected_closing_date?->format('Y-m-d'),
                    $lead->notes,
                ]);
            }

            fclose($handle);
        }, 'leads-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function printLeads(Request $request): View
    {
        $query = Lead::with('owner')->latest();

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        if ($request->filled('selected')) {
            $ids = explode(',', $request->input('selected'));
            $query->whereIn('id', $ids);
        } else {
            foreach (['status', 'source', 'priority', 'owner_id'] as $field) {
                if ($request->filled($field)) {
                    $query->where($field, $request->$field);
                }
            }

            if ($request->filled('search')) {
                $query->where(fn ($q) => $q->where('company_name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%'));
            }
        }

        return view('crm.leads.print', [
            'leads' => $query->get(),
            'filters' => $request->only(['search', 'status', 'source', 'priority', 'owner_id']),
        ]);
    }

    public function importTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Company *', 'Contact Person', 'Phone *', 'Email', 'Website',
                'Address', 'City', 'Country', 'Source', 'Service Interested', 'Estimated Budget',
                'Status', 'Priority', 'Expected Closing Date', 'Notes',
            ]);
            fputcsv($handle, [
                'Acme Corp', 'John Doe', '+92 300 1234567', '', 'john@acme.com', 'https://acme.com',
                '123 Main St', 'Karachi', 'Pakistan', 'website', 'Web Development', '500000',
                'new', 'medium', '2026-09-30', 'Interested in a quote.',
            ]);
            fclose($handle);
        }, 'leads-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:4096']);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_map(
            fn ($h) => strtolower(trim((string) preg_replace(['/^\xEF\xBB\xBF/', '/\s*\*+$/'], ['', ''], $h))),
            array_shift($rows) ?? []
        );

        $aliases = [
            'company_name' => ['company_name', 'company', 'name', 'business'],
            'contact_person' => ['contact_person', 'contact person', 'contact', 'contact name', 'person'],
            'phone' => ['phone', 'phone number', 'contact number', 'mobile'],
            'email' => ['email', 'email address', 'e-mail'],
            'website' => ['website', 'web', 'url'],
            'address' => ['address', 'address line'],
            'city' => ['city'],
            'country' => ['country'],
            'source' => ['source', 'lead source'],
            'service_interested' => ['service_interested', 'service', 'service interested'],
            'estimated_budget' => ['estimated_budget', 'budget', 'estimated budget'],
            'status' => ['status', 'lead status'],
            'priority' => ['priority'],
            'expected_closing_date' => ['expected_closing_date', 'closing date', 'expected closing date', 'expected_close_date'],
            'notes' => ['notes', 'remarks', 'comment'],
        ];

        $colMap = [];
        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                if (in_array($name, $header, true)) {
                    $colMap[$field] = array_search($name, $header, true);
                    break;
                }
            }
        }

        $sources = ['website', 'facebook', 'instagram', 'referral', 'fiverr', 'upwork', 'google', 'linkedin', 'direct', 'other'];
        $statuses = $this->statusSlugs();
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $data = [];
            foreach ($colMap as $field => $idx) {
                $data[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
            }

            if (($data['phone'] ?? '') === '') {
                $skipped++;
                continue;
            }

            $data['source'] = in_array($data['source'] ?? '', $sources, true) ? $data['source'] : 'other';
            $data['status'] = in_array($data['status'] ?? '', $statuses, true) ? $data['status'] : 'new';
            $data['priority'] = in_array($data['priority'] ?? '', $priorities, true) ? $data['priority'] : 'medium';

            if ($data['expected_closing_date'] ?? '') {
                $data['expected_closing_date'] = date('Y-m-d', strtotime($data['expected_closing_date']));
            }

            $data['estimated_budget'] = $data['estimated_budget'] ?? 0;
            $data['owner_id'] = auth()->id();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $data['stage'] = $this->stage($data['status']);
            $data['lead_number'] = Lead::generateNumber();

            Lead::create($data);
            $created++;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Import complete: {$created} created, {$skipped} skipped.",
        ]);
    }
}
