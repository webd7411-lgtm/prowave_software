<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Client, Payment, PaymentReminder};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class ReceivablesController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('receivables.view');

        $query = Invoice::with('client')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereColumn('paid_amount', '<', 'total');

        if ($request->filled('client'))   $query->where('client_id', $request->client);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('overdue_only') && $request->overdue_only) {
            $query->where('due_date', '<', now()->toDateString());
        }
        if ($request->filled('from_date')) $query->where('invoice_date', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->where('invoice_date', '<=', $request->to_date);

        $records = $query->latest('invoice_date')->paginate(25)->withQueryString();
        $clients = Client::orderBy('company_name')->get();

        $totalOutstanding = Invoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(total - paid_amount) as total')->value('total') ?? 0;

        $overdueCount = Invoice::where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return view('finance.receivables.index', compact('records', 'clients', 'totalOutstanding', 'overdueCount'));
    }

    public function sendReminder(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate(['message' => 'nullable|string|max:1000']);

        PaymentReminder::create([
            'invoice_id' => $invoice->id,
            'client_id'  => $invoice->client_id,
            'channel'    => 'manual',
            'message'    => $request->message ?? 'Payment reminder sent.',
            'sent_at'    => now(),
            'status'     => 'sent',
            'sent_by'    => auth()->id(),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Payment reminder recorded for ' . $invoice->client?->company_name . '.']);
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $records = Invoice::with('client')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereColumn('paid_amount', '<', 'total')
            ->latest('invoice_date')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice No', 'Client', 'Invoice Date', 'Due Date', 'Total', 'Paid', 'Outstanding', 'Days Overdue', 'Status']);
            foreach ($records as $r) {
                $daysOverdue = $r->due_date && $r->due_date->isPast() ? $r->due_date->diffInDays(now()) : 0;
                fputcsv($handle, [
                    $r->invoice_number, $r->client?->company_name,
                    $r->invoice_date->format('d M Y'),
                    $r->due_date ? $r->due_date->format('d M Y') : '—',
                    number_format($r->total, 2), number_format($r->paid_amount, 2),
                    number_format($r->balance(), 2), $daysOverdue, ucfirst($r->status),
                ]);
            }
            fclose($handle);
        }, 'receivables.csv', ['Content-Type' => 'text/csv']);
    }
}
