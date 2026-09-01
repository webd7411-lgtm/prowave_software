<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\{Client, Invoice, Lead, LeadFollowup, LeadStatus, Payment, User};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CrmDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if (! auth()->user()->can('crm_dashboard.view')) {
            abort(403, 'Unauthorized access to CRM Dashboard');
        }

        $user = auth()->user();
        $canViewAll = $user->can('leads.view_all');

        // Selected owner filter (if allowed) or enforce current user
        $ownerId = $canViewAll ? $request->input('owner_id') : $user->id;

        // Date / Time Period Filter
        $period = $request->input('period', 'all');
        $fromDateInput = $request->input('from_date');
        $toDateInput = $request->input('to_date');

        $fromDate = null;
        $toDate = null;

        if ($period && $period !== 'custom' && $period !== 'all') {
            [$fromDate, $toDate] = match ($period) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
                'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
                'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
                'this_year' => [now()->startOfYear(), now()->endOfYear()],
                default => [null, null],
            };
        } elseif ($period === 'custom' || ($fromDateInput || $toDateInput)) {
            $period = 'custom';
            $fromDate = $fromDateInput ? Carbon::parse($fromDateInput)->startOfDay() : null;
            $toDate = $toDateInput ? Carbon::parse($toDateInput)->endOfDay() : null;
        }

        // Base Lead Query with ownership scope & date range
        $leadQuery = Lead::query();
        if ($ownerId) {
            $leadQuery->where('owner_id', $ownerId);
        }
        if ($fromDate) {
            $leadQuery->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $leadQuery->where('created_at', '<=', $toDate);
        }

        // Fetch dynamic statuses from LeadStatus table
        $activeStatuses = LeadStatus::where('is_active', 1)->orderBy('sort_order')->get();

        // Calculate dynamic counts by status
        $statusCounts = (clone $leadQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $totalLeads = (clone $leadQuery)->count();
        $newLeads = $statusCounts['new'] ?? 0;
        
        // "Contacted" count: leads with status 'contacted', 'call_again', 'meeting_scheduled', 'proposal_sent' or stage 'contacted'
        $contactedLeads = (clone $leadQuery)
            ->where(function ($q) {
                $q->whereIn('status', ['contacted', 'call_again', 'meeting_scheduled', 'proposal_sent'])
                  ->orWhere('stage', 'contacted');
            })->count();

        $qualifiedLeads = $statusCounts['qualified'] ?? 0;
        $wonLeads = (clone $leadQuery)->whereIn('status', ['won', 'converted'])->count();
        $lostLeads = $statusCounts['lost'] ?? 0;

        // Followups query
        $followupQuery = LeadFollowup::query();
        if ($ownerId) {
            $followupQuery->where(function ($q) use ($ownerId) {
                $q->where('assigned_to', $ownerId)
                  ->orWhereHas('lead', fn ($l) => $l->where('owner_id', $ownerId));
            });
        }
        if ($fromDate) {
            $followupQuery->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $followupQuery->where('created_at', '<=', $toDate);
        }

        $pendingFollowups = (clone $followupQuery)->where('status', 'pending')->count();
        $overdueFollowups = (clone $followupQuery)->where('status', 'pending')->whereDate('follow_up_date', '<', today())->count();

        // Client count for this scope
        $clientQuery = Client::query();
        if ($ownerId) {
            $clientQuery->where('account_manager_id', $ownerId);
        }
        if ($fromDate) {
            $clientQuery->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $clientQuery->where('created_at', '<=', $toDate);
        }
        $totalClients = $clientQuery->count();

        // Financial summary (Invoices / Payments)
        $invoiceQuery = Invoice::query();
        $paymentQuery = Payment::query();
        if ($ownerId) {
            $invoiceQuery->whereHas('client', fn ($c) => $c->where('account_manager_id', $ownerId));
            $paymentQuery->whereHas('client', fn ($c) => $c->where('account_manager_id', $ownerId));
        }
        $totalInvoiced = $invoiceQuery->sum('total');
        $totalPaid = $paymentQuery->sum('amount');
        $outstandingPayments = max(0, $totalInvoiced - $totalPaid);

        // Conversion Rate
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        // Pipeline data (counts per active status)
        $pipelineData = [];
        foreach ($activeStatuses as $st) {
            $pipelineData[] = [
                'name' => $st->name,
                'slug' => $st->slug,
                'color' => $st->color ?: '#3b82f6',
                'count' => $statusCounts[$st->slug] ?? 0,
            ];
        }

        // Recent Followups
        $recentFollowups = (clone $followupQuery)
            ->with(['lead', 'assignee'])
            ->orderBy('follow_up_date', 'asc')
            ->whereDate('follow_up_date', '>=', today())
            ->limit(6)
            ->get();

        // Recent Leads
        $recentLeads = (clone $leadQuery)
            ->with(['owner'])
            ->latest()
            ->limit(6)
            ->get();

        // Sales Users (for filter dropdown if user can view all)
        $salesAgents = $canViewAll ? User::orderBy('name')->pluck('name', 'id') : collect();

        return view('crm.dashboard', [
            'canViewAll' => $canViewAll,
            'ownerId' => $ownerId,
            'period' => $period,
            'fromDate' => $fromDateInput ?: ($fromDate ? $fromDate->format('Y-m-d') : ''),
            'toDate' => $toDateInput ?: ($toDate ? $toDate->format('Y-m-d') : ''),
            'salesAgents' => $salesAgents,
            'activeStatuses' => $activeStatuses,
            'stats' => [
                'total_leads' => $totalLeads,
                'new_leads' => $newLeads,
                'contacted_leads' => $contactedLeads,
                'qualified_leads' => $qualifiedLeads,
                'won_leads' => $wonLeads,
                'lost_leads' => $lostLeads,
                'total_clients' => $totalClients,
                'pending_followups' => $pendingFollowups,
                'overdue_followups' => $overdueFollowups,
                'outstanding_payments' => $outstandingPayments,
                'conversion_rate' => $conversionRate,
            ],
            'statusCounts' => $statusCounts,
            'pipelineData' => $pipelineData,
            'recentFollowups' => $recentFollowups,
            'recentLeads' => $recentLeads,
        ]);
    }
}
