<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DailyTask;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    // ─── Lead Conversion ───────────────────────────────────────────────────────

    public function leadConversion(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfYear();
        $to = $request->date('to') ?? now()->endOfDay();

        $totals = Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->selectRaw("count(case when status = 'lost' then 1 end) as lost")
            ->selectRaw("count(case when stage in ('proposal','negotiation','qualified','contacted','new') and status not in ('won','converted','lost') then 1 end) as pipeline")
            ->first();

        $monthly = Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $ownerPerformance = Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->with('owner:id,name')
            ->selectRaw('owner_id, count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->groupBy('owner_id')
            ->orderByDesc('total')
            ->get();

        $conversionRate = $totals->total > 0 ? round(($totals->won / $totals->total) * 100, 1) : 0;

        return view('reports.lead-conversion', compact('totals', 'conversionRate', 'monthly', 'ownerPerformance', 'from', 'to'));
    }

    // ─── Lead Sources ──────────────────────────────────────────────────────────

    public function leadSources(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfYear();
        $to = $request->date('to') ?? now()->endOfDay();

        $sources = Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('source, count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->selectRaw("count(case when status = 'lost' then 1 end) as lost")
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        return view('reports.lead-sources', compact('sources', 'from', 'to'));
    }

    // ─── Client Revenue ────────────────────────────────────────────────────────

    public function clientRevenue(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfYear();
        $to = $request->date('to') ?? now()->endOfDay();

        $clients = Client::query()
            ->withCount(['invoices as invoice_count'])
            ->withSum(['invoices as invoiced' => fn ($q) => $q->whereBetween('invoice_date', [$from, $to])], 'total')
            ->withSum(['payments as received' => fn ($q) => $q->whereBetween('payment_date', [$from, $to])], 'amount')
            ->having('invoiced', '>', 0)
            ->orHaving('received', '>', 0)
            ->orderByDesc('received')
            ->paginate(20)
            ->withQueryString();

        return view('reports.client-revenue', compact('clients', 'from', 'to'));
    }

    // ─── Invoice Aging ─────────────────────────────────────────────────────────

    public function invoiceAging(Request $request): View
    {
        $buckets = [
            'current' => [0, 0],
            '1-30' => [1, 30],
            '31-60' => [31, 60],
            '61-90' => [61, 90],
            '90+' => [91, null],
        ];

        $aging = collect();

        foreach ($buckets as $label => [$min, $max]) {
            $query = Invoice::query()
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->whereColumn('paid_amount', '<', 'total');

            if ($label === 'current') {
                $query->whereDate('due_date', '>=', now()->toDateString());
            } else {
                $query->whereDate('due_date', '<', now()->toDateString());
                $query->whereDate('due_date', '>=', now()->subDays($max)->toDateString());
            }

            $aging->push([
                'label' => $label,
                'count' => (clone $query)->count(),
                'amount' => (clone $query)->selectRaw('COALESCE(SUM(total - paid_amount),0) as due')->value('due'),
            ]);
        }

        $invoices = Invoice::query()
            ->with('client:id,company_name')
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->whereColumn('paid_amount', '<', 'total')
            ->when($request->filled('aging'), function ($q) use ($request, $buckets) {
                $key = $request->aging;
                if (! isset($buckets[$key])) {
                    return;
                }
                [$min, $max] = $buckets[$key];
                if ($key === 'current') {
                    $q->whereDate('due_date', '>=', now()->toDateString());
                } else {
                    $q->whereDate('due_date', '<', now()->toDateString());
                    $q->whereDate('due_date', '>=', now()->subDays($max)->toDateString());
                }
            })
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('reports.invoice-aging', compact('aging', 'invoices'));
    }

    // ─── Task Performance ──────────────────────────────────────────────────────

    public function taskPerformance(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfDay();

        $projectTasks = ProjectTask::query()
            ->with('assignedTo:id,name')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('assigned_to, count(*) as total')
            ->selectRaw("count(case when status = 'Completed' then 1 end) as completed")
            ->selectRaw("count(case when status = 'Overdue' then 1 end) as overdue")
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->get();

        $dailyTasks = DailyTask::query()
            ->with('assignee:id,name')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('assigned_to, count(*) as total')
            ->selectRaw("count(case when status = 'completed' then 1 end) as completed")
            ->selectRaw("count(case when status = 'overdue' then 1 end) as overdue")
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->get();

        $rows = $projectTasks->map(fn ($t) => [
            'name' => $t->assignedTo?->name ?? 'Unassigned',
            'total' => (int) $t->total,
            'completed' => (int) $t->completed,
            'overdue' => (int) $t->overdue,
        ])->concat($dailyTasks->map(fn ($t) => [
            'name' => $t->assignee?->name ?? 'Unassigned',
            'total' => (int) $t->total,
            'completed' => (int) $t->completed,
            'overdue' => (int) $t->overdue,
        ]))->groupBy('name')
            ->map(fn ($group) => [
                'name' => $group->first()['name'],
                'total' => $group->sum('total'),
                'completed' => $group->sum('completed'),
                'overdue' => $group->sum('overdue'),
            ])
            ->values()
            ->sortByDesc('total')
            ->take(30)
            ->values();

        return view('reports.task-performance', compact('rows', 'from', 'to'));
    }

    // ─── Export (CSV) ──────────────────────────────────────────────────────────

    public function export(Request $request): StreamedResponse
    {
        $report = $request->string('report')->toString();
        $from = $request->date('from') ?? now()->startOfYear();
        $to = $request->date('to') ?? now()->endOfDay();

        $filename = "{$report}-report.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->streamDownload(function () use ($report, $from, $to, $request) {
            $handle = fopen('php://output', 'w');

            match ($report) {
                'lead-conversion' => $this->exportLeadConversion($handle, $from, $to),
                'lead-sources' => $this->exportLeadSources($handle, $from, $to),
                'client-revenue' => $this->exportClientRevenue($handle, $from, $to),
                'invoice-aging' => $this->exportInvoiceAging($handle, $request),
                'task-performance' => $this->exportTaskPerformance($handle, $from, $to),
                default => fputcsv($handle, ['Invalid report']),
            };

            fclose($handle);
        }, $headers);
    }

    protected function exportLeadConversion($handle, $from, $to): void
    {
        fputcsv($handle, ['Month', 'Total Leads', 'Won/Converted', 'Lost', 'Conversion %']);

        Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->selectRaw("count(case when status = 'lost' then 1 end) as lost")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->each(fn ($r) => fputcsv($handle, [
                $r->month, $r->total, $r->won, $r->lost,
                $r->total > 0 ? round(($r->won / $r->total) * 100, 1).'%' : '0%',
            ]));
    }

    protected function exportLeadSources($handle, $from, $to): void
    {
        fputcsv($handle, ['Source', 'Total Leads', 'Won', 'Lost', 'Conversion %']);

        Lead::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('source, count(*) as total')
            ->selectRaw("count(case when status in ('won','converted') then 1 end) as won")
            ->selectRaw("count(case when status = 'lost' then 1 end) as lost")
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->each(fn ($r) => fputcsv($handle, [
                ucfirst($r->source), $r->total, $r->won, $r->lost,
                $r->total > 0 ? round(($r->won / $r->total) * 100, 1).'%' : '0%',
            ]));
    }

    protected function exportClientRevenue($handle, $from, $to): void
    {
        fputcsv($handle, ['Client', 'Invoices', 'Invoiced', 'Received', 'Outstanding']);

        Client::query()
            ->withCount(['invoices as invoice_count'])
            ->withSum(['invoices as invoiced' => fn ($q) => $q->whereBetween('invoice_date', [$from, $to])], 'total')
            ->withSum(['payments as received' => fn ($q) => $q->whereBetween('payment_date', [$from, $to])], 'amount')
            ->orderByDesc('received')
            ->get()
            ->each(fn ($c) => fputcsv($handle, [
                $c->company_name,
                $c->invoice_count,
                number_format((float) $c->invoiced, 2),
                number_format((float) $c->received, 2),
                number_format((float) ($c->invoiced - $c->received), 2),
            ]));
    }

    protected function exportInvoiceAging($handle, $request): void
    {
        fputcsv($handle, ['Invoice', 'Client', 'Due Date', 'Total', 'Paid', 'Outstanding', 'Days Overdue']);

        Invoice::query()
            ->with('client:id,company_name')
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->whereColumn('paid_amount', '<', 'total')
            ->orderBy('due_date')
            ->get()
            ->each(fn ($i) => fputcsv($handle, [
                $i->invoice_number,
                $i->client?->company_name,
                $i->due_date?->format('Y-m-d'),
                number_format($i->total, 2),
                number_format($i->paid_amount, 2),
                number_format($i->total - $i->paid_amount, 2),
                $i->due_date && $i->due_date->isPast() ? $i->due_date->diffInDays(now()) : 0,
            ]));
    }

    protected function exportTaskPerformance($handle, $from, $to): void
    {
        fputcsv($handle, ['Employee', 'Total Tasks', 'Completed', 'Overdue', 'Completion %']);

        ProjectTask::query()
            ->with('assignedTo:id,name')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('assigned_to, count(*) as total')
            ->selectRaw("count(case when status = 'Completed' then 1 end) as completed")
            ->groupBy('assigned_to')
            ->get()
            ->each(fn ($r) => fputcsv($handle, [
                $r->assignedTo?->name ?? 'Unassigned',
                $r->total,
                $r->completed,
                $r->total > 0 ? round(($r->completed / $r->total) * 100, 1).'%' : '0%',
            ]));
    }
}
