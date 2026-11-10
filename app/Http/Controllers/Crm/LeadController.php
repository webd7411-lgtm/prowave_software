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

        $query = Lead::with(['owner', 'followups' => fn ($q) => $q->orderBy('follow_up_date', 'desc')->orderBy('id', 'desc')])
            ->select('leads.*')
            ->orderBy('id', 'asc');

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        if ($request->filled('status')) {
            if ($request->status === 'contacted') {
                $query->where('status', '!=', 'new');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('owner_id')) {
            if ($request->owner_id === 'unassigned') {
                $query->whereNull('owner_id');
            } else {
                $query->where('owner_id', $request->owner_id);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(fn ($q) => $q->where('lead_number', 'like', '%' . $search . '%')
                ->orWhere('company_name', 'like', '%' . $search . '%')
                ->orWhere('contact_person', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%'));
        }
        $statusList = $this->statusList();

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $canAssign = $this->canAssignLeads();
        $salesTeam = $canAssign ? $this->salesTeamUsers() : collect();

        return view('crm.leads.index', [
            'leads' => $query->paginate($perPage)->withQueryString(),
            'filters' => $request->all(),
            'owners' => User::orderBy('name')->pluck('name', 'id'),
            'statuses' => $statusList,
            'statusColors' => $statusList->pluck('color', 'slug')->all(),
            'canAssign' => $canAssign,
            'salesTeam' => $salesTeam,
            'unassignedCount' => Lead::whereNull('owner_id')->count(),
            'myLeadsCount' => Lead::where('owner_id', auth()->id())->count(),
            'totalLeadsCount' => Lead::count(),
        ]);
    }

    public function create(): View
    {
        return view('crm.leads.form', [
            'lead' => new Lead,
            'owners' => User::orderBy('name')->get(),
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
            'priority' => 'medium',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        $lead = Lead::create($data);

        if (in_array($data['status'], ['won', 'converted'])) {
            $this->autoConvertToClient($lead);
        }

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
            'canAssign' => $this->canAssignLeads(),
            'salesTeam' => $this->canAssignLeads() ? $this->salesTeamUsers() : collect(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        return view('crm.leads.form', [
            'lead' => $lead,
            'owners' => User::orderBy('name')->get(),
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

        if (in_array($data['status'], ['won', 'converted']) && ! $lead->converted_client_id) {
            $this->autoConvertToClient($lead);
        }

        return redirect()->route('crm.leads.show', $lead)
            ->with('toast', ['type' => 'success', 'message' => 'Lead updated.']);
    }

    public function move(Request $request, Lead $lead): RedirectResponse
    {
        $rules = ['stage' => 'required|in:new,contacted,qualified,proposal,negotiation,meeting,won,lost'];

        if ($request->stage === 'meeting' && ($request->filled('follow_up_date') || $request->filled('follow_up_time'))) {
            $rules['follow_up_date'] = 'required|date|after_or_equal:today';
            $rules['follow_up_time'] = 'required|date_format:H:i';
        }

        $request->validate($rules);

        $status = $request->stage === 'proposal' ? 'proposal_sent' : ($request->stage === 'meeting' ? 'meeting_scheduled' : $request->stage);
        $stageUpdate = $this->updateStage($lead, $request->stage, $status);

        if ((in_array($request->stage, ['won']) || in_array($status, ['won', 'converted'])) && ! $lead->converted_client_id) {
            $this->autoConvertToClient($lead);
        }

        if ($request->stage === 'meeting') {
            $date = $request->input('follow_up_date', now()->format('Y-m-d'));
            $time = $request->input('follow_up_time', '10:00');
            $this->scheduleMeeting($lead, $date, $time);
        }

        return $stageUpdate;
    }

    public function updateStatus(Request $request, Lead $lead): \Illuminate\Http\JsonResponse
    {
        $rules = ['status' => 'required|string|in:' . implode(',', $this->statusSlugs())];

        if (in_array($request->status, ['meeting_scheduled', 'call_again'])) {
            $rules['follow_up_date'] = 'required|date';
            $rules['follow_up_time'] = 'nullable';
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

        $converted = false;
        if (in_array($status, ['won', 'converted']) && ! $lead->converted_client_id) {
            $this->autoConvertToClient($lead);
            $converted = true;
        }

        if (in_array($status, ['meeting_scheduled', 'call_again']) && $request->filled('follow_up_date')) {
            if ($status === 'meeting_scheduled') {
                $this->scheduleMeeting($lead, $request->follow_up_date, $request->follow_up_time);
            } else {
                $this->scheduleCallAgain($lead, $request->follow_up_date, $request->follow_up_time);
            }
        }

        $msg = match ($status) {
            'meeting_scheduled' => 'Meeting scheduled successfully.',
            'call_again' => 'Call scheduled successfully.',
            'won', 'converted' => $converted ? 'Status updated & Lead converted to Client successfully.' : 'Status updated successfully.',
            default => 'Status updated successfully.',
        };

        return response()->json([
            'success' => true,
            'status' => $status,
            'stage' => $stage,
            'message' => $msg,
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

        $client = $this->autoConvertToClient($lead, $request->client_id);
        $lead->update(['status' => 'converted', 'stage' => 'won']);

        return redirect()->route('crm.clients.show', $client)
            ->with('toast', ['type' => 'success', 'message' => 'Lead converted to client successfully.']);
    }

    private function autoConvertToClient(Lead $lead, ?int $clientId = null): Client
    {
        if ($lead->converted_client_id && $client = Client::find($lead->converted_client_id)) {
            return $client;
        }

        return DB::transaction(function () use ($lead, $clientId) {
            $client = null;

            if ($clientId) {
                $client = Client::find($clientId);
            }

            if (! $client && ! empty($lead->email)) {
                $client = Client::where('email', $lead->email)->first();
            }

            if (! $client && ! empty($lead->phone)) {
                $client = Client::where('phone', $lead->phone)->first();
            }

            if (! $client) {
                $client = Client::create([
                    'client_number' => Client::generateNumber(),
                    'company_name' => $lead->company_name ?: ($lead->contact_person ?: 'Client ' . $lead->lead_number),
                    'contact_person' => $lead->contact_person,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'website' => $lead->website,
                    'address' => $lead->address,
                    'city' => $lead->city,
                    'country' => $lead->country,
                    'account_manager_id' => $lead->owner_id ?: auth()->id(),
                    'status' => 'active',
                    'created_by' => auth()->id() ?? $lead->created_by ?? 1,
                    'updated_by' => auth()->id() ?? $lead->updated_by ?? 1,
                ]);
            }

            $lead->update([
                'converted_client_id' => $client->id,
                'converted_at' => $lead->converted_at ?: now(),
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'type' => 'conversion',
                'subject' => 'Converted to client ' . $client->client_number,
                'created_by' => auth()->id() ?? $lead->created_by ?? 1,
            ]);

            $ns = app(NotificationService::class);
            $ns->sendToPermission(
                'clients.view',
                'Lead converted to client',
                $lead->company_name . ' is now a client',
                ['category' => 'client', 'url' => route('crm.clients.show', $client), 'color' => 'success']
            );

            if ($lead->owner_id) {
                $owner = User::find($lead->owner_id);
                if ($owner) {
                    $ns->send(
                        $owner,
                        'Lead converted',
                        'Congratulations — ' . $lead->company_name . ' converted to a client.',
                        ['category' => 'lead', 'url' => route('crm.clients.show', $client), 'color' => 'success']
                    );
                }
            }

            return $client;
        });
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('crm.leads.index')
            ->with('toast', ['type' => 'success', 'message' => 'Lead deleted.']);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id',
        ]);

        $query = Lead::whereIn('id', $request->ids);
        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        $count = $query->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$count} lead(s) deleted successfully.",
        ]);
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        if (! $this->canAssignLeads()) {
            abort(403, 'You do not have permission to assign leads.');
        }

        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:leads,id'],
            'assign_type' => ['required', 'in:single,equal,unassign'],
            'salesman_id' => ['nullable', 'required_if:assign_type,single', 'exists:users,id'],
            'salesmen_ids' => ['nullable', 'required_if:assign_type,equal', 'array', 'min:1'],
            'salesmen_ids.*' => ['exists:users,id'],
        ]);

        $leadIds = $request->input('ids', []);
        $total = count($leadIds);
        $type = $request->input('assign_type');
        $actor = auth()->user();

        DB::transaction(function () use ($leadIds, $type, $request, $actor, &$total) {
            if ($type === 'unassign') {
                Lead::whereIn('id', $leadIds)->update([
                    'owner_id' => null,
                    'updated_by' => $actor->id,
                ]);

                $activities = [];
                $now = now();
                foreach ($leadIds as $id) {
                    $activities[] = [
                        'lead_id' => $id,
                        'type' => 'assigned',
                        'subject' => 'Lead unassigned by ' . $actor->name,
                        'created_by' => $actor->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($activities, 500) as $chunk) {
                    LeadActivity::insert($chunk);
                }

                return;
            }

            if ($type === 'single') {
                $salesmanId = (int) $request->input('salesman_id');
                $salesman = User::findOrFail($salesmanId);

                Lead::whereIn('id', $leadIds)->update([
                    'owner_id' => $salesmanId,
                    'updated_by' => $actor->id,
                ]);

                $activities = [];
                $now = now();
                foreach ($leadIds as $id) {
                    $activities[] = [
                        'lead_id' => $id,
                        'type' => 'assigned',
                        'subject' => "Assigned to {$salesman->name} by {$actor->name}",
                        'created_by' => $actor->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($activities, 500) as $chunk) {
                    LeadActivity::insert($chunk);
                }

                app(NotificationService::class)->send(
                    $salesman,
                    'Leads Assigned',
                    "{$total} lead(s) have been assigned to you by {$actor->name}.",
                    ['category' => 'lead', 'url' => route('crm.leads.index', ['owner_id' => $salesmanId]), 'color' => 'success']
                );

                return;
            }

            if ($type === 'equal') {
                $salesmenIds = array_values(array_unique($request->input('salesmen_ids', [])));
                $numSalesmen = count($salesmenIds);

                $salesmen = User::whereIn('id', $salesmenIds)->get()->keyBy('id');
                $distribution = [];
                foreach ($salesmenIds as $sId) {
                    $distribution[$sId] = [];
                }

                foreach ($leadIds as $idx => $leadId) {
                    $assignedSalesmanId = $salesmenIds[$idx % $numSalesmen];
                    $distribution[$assignedSalesmanId][] = $leadId;
                }

                $now = now();
                $allActivities = [];

                foreach ($distribution as $sId => $assignedIds) {
                    if (empty($assignedIds)) {
                        continue;
                    }

                    $sUser = $salesmen->get($sId);
                    $salesmanName = $sUser?->name ?? 'Sales Person';

                    Lead::whereIn('id', $assignedIds)->update([
                        'owner_id' => $sId,
                        'updated_by' => $actor->id,
                    ]);

                    foreach ($assignedIds as $leadId) {
                        $allActivities[] = [
                            'lead_id' => $leadId,
                            'type' => 'assigned',
                            'subject' => "Assigned to {$salesmanName} by {$actor->name}",
                            'created_by' => $actor->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if ($sUser) {
                        $countAssigned = count($assignedIds);
                        app(NotificationService::class)->send(
                            $sUser,
                            'Leads Assigned',
                            "{$countAssigned} lead(s) have been assigned to you by {$actor->name}.",
                            ['category' => 'lead', 'url' => route('crm.leads.index', ['owner_id' => $sId]), 'color' => 'success']
                        );
                    }
                }

                if (! empty($allActivities)) {
                    foreach (array_chunk($allActivities, 500) as $chunk) {
                        LeadActivity::insert($chunk);
                    }
                }
            }
        });

        $message = "Successfully updated {$total} lead(s).";
        if ($type === 'single') {
            $salesman = User::find($request->input('salesman_id'));
            $message = "Successfully assigned {$total} lead(s) to {$salesman?->name}.";
        } elseif ($type === 'equal') {
            $countSalesmen = count((array) $request->input('salesmen_ids'));
            $message = "Successfully distributed {$total} lead(s) equally across {$countSalesmen} salesmen.";
        } elseif ($type === 'unassign') {
            $message = "Successfully unassigned {$total} lead(s).";
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function distribute(Request $request): RedirectResponse
    {
        if (! $this->canAssignLeads()) {
            abort(403, 'You do not have permission to distribute leads.');
        }

        $request->validate([
            'source_pool' => ['required', 'in:unassigned,my_leads,all,status'],
            'status' => ['nullable', 'string'],
            'total_limit' => ['nullable', 'integer', 'min:1'],
            'distribution_mode' => ['required', 'in:equal,custom'],
            'salesmen_ids' => ['nullable', 'required_if:distribution_mode,equal', 'array', 'min:1'],
            'salesmen_ids.*' => ['exists:users,id'],
            'custom_counts' => ['nullable', 'required_if:distribution_mode,custom', 'array'],
        ]);

        $sourcePool = $request->input('source_pool');
        $query = Lead::query();

        if ($sourcePool === 'unassigned') {
            $query->whereNull('owner_id');
        } elseif ($sourcePool === 'my_leads') {
            $query->where('owner_id', auth()->id());
        } elseif ($sourcePool === 'status' && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status') && $sourcePool !== 'status') {
            $query->where('status', $request->status);
        }

        $query->orderBy('id', 'asc');

        if ($request->filled('total_limit') && (int) $request->total_limit > 0) {
            $query->limit((int) $request->total_limit);
        }

        $leads = $query->get(['id', 'owner_id']);
        $totalAvailable = $leads->count();

        if ($totalAvailable === 0) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'No matching leads found in the selected pool to distribute.',
            ]);
        }

        $mode = $request->input('distribution_mode');
        $distribution = [];
        $actor = auth()->user();

        if ($mode === 'equal') {
            $salesmenIds = array_values(array_unique($request->input('salesmen_ids', [])));
            $numSalesmen = count($salesmenIds);

            if ($numSalesmen === 0) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => 'Please select at least one salesman for distribution.',
                ]);
            }

            foreach ($salesmenIds as $sId) {
                $distribution[$sId] = [];
            }

            foreach ($leads as $idx => $lead) {
                $assignedId = $salesmenIds[$idx % $numSalesmen];
                $distribution[$assignedId][] = $lead->id;
            }
        } else {
            $customCounts = (array) $request->input('custom_counts', []);
            $leadIndex = 0;

            foreach ($customCounts as $salesmanId => $count) {
                $count = (int) $count;
                if ($count <= 0) {
                    continue;
                }

                $distribution[$salesmanId] = [];
                for ($i = 0; $i < $count && $leadIndex < $totalAvailable; $i++) {
                    $distribution[$salesmanId][] = $leads[$leadIndex]->id;
                    $leadIndex++;
                }
            }
        }

        $totalDistributed = 0;
        $salesmen = User::whereIn('id', array_keys($distribution))->get()->keyBy('id');
        $now = now();
        $allActivities = [];

        DB::transaction(function () use (&$distribution, $salesmen, $actor, $now, &$allActivities, &$totalDistributed) {
            foreach ($distribution as $salesmanId => $assignedIds) {
                if (empty($assignedIds)) {
                    continue;
                }

                $totalDistributed += count($assignedIds);
                $sUser = $salesmen->get($salesmanId);
                $salesmanName = $sUser?->name ?? 'Sales Person';

                Lead::whereIn('id', $assignedIds)->update([
                    'owner_id' => $salesmanId,
                    'updated_by' => $actor->id,
                ]);

                foreach ($assignedIds as $leadId) {
                    $allActivities[] = [
                        'lead_id' => $leadId,
                        'type' => 'assigned',
                        'subject' => "Lead distributed and assigned to {$salesmanName} by {$actor->name}",
                        'created_by' => $actor->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($sUser) {
                    $c = count($assignedIds);
                    app(NotificationService::class)->send(
                        $sUser,
                        'New Leads Assigned',
                        "{$c} lead(s) have been distributed and assigned to you by {$actor->name}.",
                        ['category' => 'lead', 'url' => route('crm.leads.index', ['owner_id' => $salesmanId]), 'color' => 'success']
                    );
                }
            }

            if (! empty($allActivities)) {
                foreach (array_chunk($allActivities, 500) as $chunk) {
                    LeadActivity::insert($chunk);
                }
            }
        });

        $numSalesmen = count(array_filter($distribution, fn ($arr) => ! empty($arr)));
        $avg = $numSalesmen > 0 ? round($totalDistributed / $numSalesmen) : 0;

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Successfully distributed {$totalDistributed} lead(s) among {$numSalesmen} salesmen (approx. {$avg} each).",
        ]);
    }

    public function assignSingle(Request $request, Lead $lead): RedirectResponse
    {
        if (! $this->canAssignLeads()) {
            abort(403, 'You do not have permission to assign leads.');
        }

        $request->validate([
            'owner_id' => ['nullable', 'exists:users,id'],
        ]);

        $ownerId = $request->input('owner_id') ?: null;
        $actor = auth()->user();

        $lead->update([
            'owner_id' => $ownerId,
            'updated_by' => $actor->id,
        ]);

        $ownerName = $ownerId ? User::find($ownerId)?->name : 'Unassigned';

        LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => 'assigned',
            'subject' => $ownerId ? "Assigned to {$ownerName} by {$actor->name}" : "Unassigned by {$actor->name}",
            'created_by' => $actor->id,
        ]);

        if ($ownerId && $newOwner = User::find($ownerId)) {
            app(NotificationService::class)->send(
                $newOwner,
                'Lead Assigned',
                "Lead '{$lead->company_name}' has been assigned to you by {$actor->name}.",
                ['category' => 'lead', 'url' => route('crm.leads.show', $lead), 'color' => 'info']
            );
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Lead owner updated to {$ownerName}.",
        ]);
    }

    private function canAssignLeads(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('leads.assign')
            || $user->can('leads.view_all')
            || $user->hasRole(['Super Admin', 'Administrator', 'Sales Manager']);
    }

    private function salesTeamUsers()
    {
        return User::where('status', 'active')
            ->with('roles')
            ->withCount('leads')
            ->orderBy('name')
            ->get();
    }



    private function statuses(): array
    {
        return LeadStatus::where('is_active', 1)->orderBy('sort_order')->pluck('name', 'slug')->all();
    }

    private function statusList()
    {
        cache()->forget('lead_statuses_active');
        return LeadStatus::where('is_active', 1)->orderBy('sort_order')->get(['slug', 'name', 'color']);
    }

    private function statusSlugs(): array
    {
        return LeadStatus::where('is_active', 1)->pluck('slug')->all();
    }

    private function stage(string $status): string
    {
        return match ($status) {
            'proposal_sent' => 'proposal',
            'converted' => 'won',
            'meeting_scheduled' => 'meeting',
            'call_again' => 'contacted',
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

        cache()->forget('lead_statuses_active');

        return back()->with('toast', ['type' => 'success', 'message' => 'Status "' . trim($request->name) . '" added.']);
    }

    public function destroyStatus(string $leadStatus): RedirectResponse
    {
        $status = LeadStatus::where('slug', $leadStatus)->firstOrFail();
        $status->update(['is_active' => 0]);

        cache()->forget('lead_statuses_active');

        return back()->with('toast', ['type' => 'success', 'message' => 'Status "' . $status->name . '" deactivated.']);
    }

    private function scheduleMeeting(Lead $lead, string $date, ?string $time = null): LeadFollowup
    {
        $fu = $lead->followups()->create([
            'follow_up_date' => $date,
            'follow_up_time' => $time ?: null,
            'type' => 'meeting',
            'status' => 'scheduled',
            'reminder' => 1,
            'assigned_to' => $lead->owner_id ?: auth()->id(),
            'notes' => 'Meeting scheduled via lead status update',
            'created_by' => auth()->id(),
        ]);

        $lead->loadMissing('owner');
        $when = date('M d, Y', strtotime($date)) . ($time ? ' at ' . date('h:i A', strtotime($time)) : '');
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

    private function scheduleCallAgain(Lead $lead, string $date, ?string $time = null): LeadFollowup
    {
        $fu = $lead->followups()->create([
            'follow_up_date' => $date,
            'follow_up_time' => $time ?: null,
            'type' => 'call',
            'status' => 'scheduled',
            'reminder' => 1,
            'assigned_to' => $lead->owner_id ?: auth()->id(),
            'notes' => 'Call again scheduled via lead status update',
            'created_by' => auth()->id(),
        ]);

        $lead->loadMissing('owner');
        $when = date('M d, Y', strtotime($date)) . ($time ? ' at ' . date('h:i A', strtotime($time)) : '');
        $contactDetails = implode(' · ', array_filter([$lead->contact_person, $lead->phone ?: $lead->email]));
        $ownerName = $lead->owner?->name ?? 'Unassigned';

        $title = 'Call Scheduled: ' . $lead->company_name;
        $msg = "Call Again: {$when}\nBusiness: {$lead->company_name}\nContact: " . ($contactDetails ?: 'N/A') . "\nLead Owner: {$ownerName}";

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
            'category' => 'followup',
            'url' => route('crm.leads.show', $lead),
            'color' => 'warning',
            'include_self' => true,
        ]);

        return $fu;
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Lead::with('owner')->orderBy('id', 'desc');

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        if ($request->filled('status')) {
            if ($request->status === 'contacted') {
                $query->where('status', '!=', 'new');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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
                'Website', 'Address', 'City', 'Country', 'Service Interested',
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
        $query = Lead::with(['owner', 'followups'])->orderBy('id', 'asc');

        if (! auth()->user()->can('leads.view_all')) {
            $query->where('owner_id', auth()->id());
        }

        if ($request->filled('selected')) {
            $ids = explode(',', $request->input('selected'));
            $query->whereIn('id', $ids);
        } else {
        if ($request->filled('status')) {
            if ($request->status === 'contacted') {
                $query->where('status', '!=', 'new');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            if ($request->filled('search')) {
                $query->where(fn ($q) => $q->where('company_name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%'));
            }
        }

        return view('crm.leads.print', [
            'leads' => $query->get(),
            'filters' => $request->only(['search', 'status', 'from_date', 'to_date', 'owner_id']),
        ]);
    }

    public function importTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Company *', 'Contact Person', 'Phone *', 'Email', 'Website',
                'Address', 'City', 'Country', 'Service Interested', 'Estimated Budget',
                'Status', 'Priority', 'Expected Closing Date', 'Notes',
            ]);
            fputcsv($handle, [
                'Acme Corp', 'John Doe', '+92 300 1234567', 'john@acme.com', 'https://acme.com',
                '123 Main St', 'Karachi', 'Pakistan', 'Web Development', '500000',
                'new', 'medium', '2026-09-30', 'Interested in a quote.',
            ]);
            fclose($handle);
        }, 'leads-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, ['csv', 'txt', 'xlsx', 'xls', 'ods'], true)) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Invalid file format. Please upload an Excel (.xlsx, .xls) or CSV file.',
            ]);
        }

        $rows = [];
        try {
            if (in_array($ext, ['xlsx', 'xls', 'ods'], true)) {
                $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $file);
                $rows = $sheets[0] ?? [];
            } else {
                $rows = array_map('str_getcsv', file($file->getRealPath()));
            }
        } catch (\Throwable $e) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } catch (\Throwable $ex) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => 'Could not parse file: ' . $ex->getMessage(),
                ]);
            }
        }

        if (empty($rows)) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Uploaded file is empty.']);
        }

        $rawHeader = array_shift($rows) ?? [];
        $header = array_map(
            fn ($h) => preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $h))),
            $rawHeader
        );

        $assignOption = $request->input('assign_option', 'me');
        $singleAssignId = $request->input('assign_single_id');
        $splitSalesmenIds = array_values(array_filter((array) $request->input('assign_split_ids', [])));
        $numSplit = count($splitSalesmenIds);
        $assignedStats = [];
        if ($assignOption === 'split' && $numSplit > 0) {
            foreach ($splitSalesmenIds as $sId) {
                $assignedStats[$sId] = 0;
            }
        }

        $aliases = [
            'company_name' => ['companyname', 'company', 'name', 'business', 'businessname', 'client', 'leadname', 'organisation', 'organization'],
            'contact_person' => ['contactperson', 'contactname', 'person', 'personname', 'fullname', 'clientperson', 'contact'],
            'phone' => ['phone', 'phonenumber', 'contactnumber', 'mobile', 'mobilenumber', 'phoneno', 'mobileno', 'cell', 'whatsapp', 'number', 'tel', 'phone_number'],
            'email' => ['email', 'emailaddress', 'mail', 'email_address'],
            'website' => ['website', 'web', 'url', 'domain'],
            'address' => ['address', 'addressline', 'street', 'location'],
            'city' => ['city', 'town'],
            'country' => ['country', 'state'],
            'service_interested' => ['serviceinterested', 'service', 'services', 'interestedservice', 'product'],
            'estimated_budget' => ['estimatedbudget', 'budget', 'amount', 'value', 'price'],
            'status' => ['status', 'leadstatus', 'stage'],
            'priority' => ['priority', 'urgency'],
            'expected_closing_date' => ['expectedclosingdate', 'closingdate', 'close_date', 'expecteddate', 'date'],
            'notes' => ['notes', 'remarks', 'comment', 'description'],
        ];

        $colMap = [];
        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                $foundIdx = array_search($name, $header, true);
                if ($foundIdx !== false) {
                    $colMap[$field] = $foundIdx;
                    break;
                }
            }
        }

        $statuses = $this->statusSlugs();
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $created = 0;
        $duplicates = 0;
        $missingData = 0;

        foreach ($rows as $row) {
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $data = [];
            foreach ($colMap as $field => $idx) {
                $val = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
                if (is_numeric($val) && str_contains($val, '.0')) {
                    $val = preg_replace('/\.0+$/', '', $val);
                }
                $data[$field] = $val;
            }

            $rawPhone = isset($data['phone']) ? trim((string) $data['phone']) : '';
            // Normalize: digits and + only, strip trailing .0 from Excel
            $phone = preg_replace('/[^0-9+]/', '', preg_replace('/\.0+$/', '', $rawPhone));
            // Zero-stripped variant for loose comparison: 09230001234 == 9230001234
            $phoneNorm = ltrim($phone, '0');

            $company = trim($data['company_name'] ?? '');
            $contact = trim($data['contact_person'] ?? '');

            if (empty($phone) && empty($company) && empty($contact)) {
                $missingData++;
                continue;
            }

            // ── Duplicate check BEFORE assigning fallback phone ──────────
            $isDuplicate = false;

            if (! empty($phone)) {
                // Match exact OR zero-stripped OR zero-prefixed variant
                $isDuplicate = Lead::whereNull('deleted_at')
                    ->where(function ($q) use ($phone, $phoneNorm) {
                        $q->where('phone', $phone);
                        if ($phoneNorm !== $phone) {
                            $q->orWhere('phone', $phoneNorm)
                              ->orWhere('phone', '0' . $phoneNorm);
                        }
                    })
                    ->exists();
            } elseif (! empty($company) || ! empty($contact)) {
                // No phone: match by company + contact combination
                $isDuplicate = Lead::whereNull('deleted_at')
                    ->when(! empty($company), fn ($q) => $q->where('company_name', $company))
                    ->when(! empty($contact), fn ($q) => $q->where('contact_person', $contact))
                    ->exists();
            }

            if ($isDuplicate) {
                $duplicates++;
                continue;
            }

            // ── Fallback phone (only after duplicate check passed) ────────
            if (empty($phone)) {
                // Use a deterministic key so same company won't create two
                $phone = 'NOPH-' . substr(md5(strtolower($company . '|' . $contact)), 0, 10);
            }
            $data['phone'] = $phone;

            if (empty($company)) {
                $data['company_name'] = $contact ?: 'Lead-' . uniqid();
            }

            $data['status'] = in_array($data['status'] ?? '', $statuses, true) ? $data['status'] : 'new';
            $data['priority'] = in_array($data['priority'] ?? '', $priorities, true) ? $data['priority'] : 'medium';

            if ($data['expected_closing_date'] ?? '') {
                $time = strtotime($data['expected_closing_date']);
                $data['expected_closing_date'] = $time ? date('Y-m-d', $time) : null;
            } else {
                unset($data['expected_closing_date']);
            }

            $ownerId = auth()->id();
            if ($assignOption === 'unassigned') {
                $ownerId = null;
            } elseif ($assignOption === 'single' && $singleAssignId) {
                $ownerId = (int) $singleAssignId;
            } elseif ($assignOption === 'split' && $numSplit > 0) {
                $ownerId = (int) $splitSalesmenIds[$created % $numSplit];
                $assignedStats[$ownerId] = ($assignedStats[$ownerId] ?? 0) + 1;
            }

            $data['owner_id'] = $ownerId;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $data['stage'] = $this->stage($data['status']);
            $data['lead_number'] = Lead::generateNumber();

            Lead::create($data);
            $created++;
        }

        $msg = "Import complete: {$created} lead(s) created.";
        if ($duplicates > 0 || $missingData > 0) {
            $msg .= " ({$duplicates} duplicate phone(s) skipped, {$missingData} empty row(s) skipped)";
        }

        if ($assignOption === 'split' && $numSplit > 0 && $created > 0) {
            $actorName = auth()->user()->name;
            foreach ($assignedStats as $sId => $assignedCount) {
                if ($assignedCount > 0 && $user = User::find($sId)) {
                    app(NotificationService::class)->send(
                        $user,
                        'New Leads Imported',
                        "{$assignedCount} new lead(s) have been imported and assigned to you by {$actorName}.",
                        ['category' => 'lead', 'url' => route('crm.leads.index', ['owner_id' => $sId]), 'color' => 'info']
                    );
                }
            }
            $breakdown = [];
            foreach ($assignedStats as $sId => $cnt) {
                $uName = User::find($sId)?->name ?? "User #{$sId}";
                $breakdown[] = "{$uName}: {$cnt}";
            }
            $msg .= " · Auto-split: " . implode(', ', $breakdown);
        } elseif ($assignOption === 'single' && $singleAssignId && $created > 0) {
            if ($user = User::find($singleAssignId)) {
                app(NotificationService::class)->send(
                    $user,
                    'New Leads Imported',
                    "{$created} new lead(s) have been imported and assigned to you by " . auth()->user()->name . ".",
                    ['category' => 'lead', 'url' => route('crm.leads.index', ['owner_id' => $singleAssignId]), 'color' => 'info']
                );
                $msg .= " · Assigned to {$user->name}";
            }
        } elseif ($assignOption === 'unassigned' && $created > 0) {
            $msg .= " · Saved as Unassigned";
        }

        return back()->with('toast', [
            'type' => $created > 0 ? 'success' : 'warning',
            'message' => $msg,
        ]);
    }
}
