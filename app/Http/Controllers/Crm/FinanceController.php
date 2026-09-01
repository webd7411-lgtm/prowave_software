<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\{Client, ClientLedger, Invoice, Payment, Quotation, QuotationItem, User};
use App\Services\NotificationService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function invoices(): View
    {
        $this->authorize('invoices.view');

        return view('crm.finance.invoices', [
            'records' => Invoice::with('client')->latest()->paginate(20),
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function createInvoice(): View
    {
        $this->authorize('invoices.create');
        
        return view('crm.finance.invoice_form', [
            'invoice' => null,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function storeInvoice(Request $r): RedirectResponse
    {
        $this->authorize('invoices.create');

        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:3000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($d['items'] as &$item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $disc = (float) ($item['discount'] ?? 0);
            $tax = (float) ($item['tax'] ?? 0);
            $lineTotal = ($qty * $price) - $disc + $tax;
            $item['total'] = round($lineTotal, 2);
            $subtotal += $item['total'];
        }
        unset($item);

        $totalDiscount = array_sum(array_map(fn($i) => (float) ($i['discount'] ?? 0), $d['items']));
        $totalTax = array_sum(array_map(fn($i) => (float) ($i['tax'] ?? 0), $d['items']));

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . now()->format('ymdHis'),
            'client_id' => $d['client_id'],
            'invoice_date' => $d['invoice_date'],
            'due_date' => $d['due_date'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'tax' => round($totalTax, 2),
            'total' => round($subtotal, 2),
            'paid_amount' => 0,
            'status' => 'unpaid',
            'notes' => $d['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($d['items'] as $item) {
            $invoice->items()->create($item);
        }

        ClientLedger::create([
            'client_id' => $invoice->client_id,
            'transaction_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'type' => 'invoice',
            'description' => 'Invoice Issued',
            'debit' => $invoice->total,
            'credit' => 0,
            'balance' => 0,
            'source_type' => Invoice::class,
            'source_id' => $invoice->id,
            'created_by' => auth()->id(),
        ]);
        ClientLedger::recalculate($invoice->client_id);

        $ns = app(NotificationService::class);
        $ns->sendToPermission('invoices.view', 'Invoice created', $invoice->invoice_number . ' created for ' . $invoice->client->company_name, [
            'category' => 'invoice',
            'url' => route('crm.invoices.show', $invoice),
            'color' => 'primary',
        ]);

        return redirect()->route('crm.invoices.show', $invoice)
            ->with('toast', ['type' => 'success', 'message' => 'Invoice created successfully.']);
    }

    public function showInvoice(Invoice $invoice): View
    {
        $this->authorize('invoices.view');
        
        $invoice->load(['client', 'items', 'payments.creator']);
        return view('crm.finance.invoice_show', compact('invoice'));
    }

    public function printInvoice(Invoice $invoice): View
    {
        $this->authorize('invoices.view');
        
        $invoice->load(['client', 'items', 'payments.creator']);
        return view('crm.finance.invoice_print', compact('invoice'));
    }

    public function editInvoice(Invoice $invoice): View
    {
        $this->authorize('invoices.edit');
        
        $invoice->load('items');
        return view('crm.finance.invoice_form', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function updateInvoice(Request $r, Invoice $invoice): RedirectResponse
    {
        $this->authorize('invoices.edit');

        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:3000',
            'status' => 'required|in:unpaid,partially_paid,paid,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($d['items'] as &$item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $disc = (float) ($item['discount'] ?? 0);
            $tax = (float) ($item['tax'] ?? 0);
            $lineTotal = ($qty * $price) - $disc + $tax;
            $item['total'] = round($lineTotal, 2);
            $subtotal += $item['total'];
        }
        unset($item);

        $totalDiscount = array_sum(array_map(fn($i) => (float) ($i['discount'] ?? 0), $d['items']));
        $totalTax = array_sum(array_map(fn($i) => (float) ($i['tax'] ?? 0), $d['items']));

        $oldClientId = $invoice->client_id;
        $oldTotal = $invoice->total;

        $invoice->update([
            'client_id' => $d['client_id'],
            'invoice_date' => $d['invoice_date'],
            'due_date' => $d['due_date'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'tax' => round($totalTax, 2),
            'total' => round($subtotal, 2),
            'notes' => $d['notes'] ?? null,
            'status' => $d['status'],
        ]);

        $invoice->items()->delete();
        foreach ($d['items'] as $item) {
            $invoice->items()->create($item);
        }

        if ($oldTotal != $invoice->total || $oldClientId != $invoice->client_id) {
            ClientLedger::where('source_type', Invoice::class)->where('source_id', $invoice->id)->update([
                'client_id' => $invoice->client_id,
                'debit' => $invoice->total,
                'transaction_date' => $invoice->invoice_date,
            ]);
            ClientLedger::recalculate($oldClientId);
            if ($oldClientId != $invoice->client_id) {
                ClientLedger::recalculate($invoice->client_id);
            }
        }

        return redirect()->route('crm.invoices.show', $invoice)
            ->with('toast', ['type' => 'success', 'message' => 'Invoice updated successfully.']);
    }

    public function destroyInvoice(Invoice $invoice): RedirectResponse
    {
        $this->authorize('invoices.delete');
        
        $clientId = $invoice->client_id;
        $invoice->items()->delete();
        
        ClientLedger::where('source_type', Invoice::class)->where('source_id', $invoice->id)->delete();
        
        $invoice->delete();
        
        ClientLedger::recalculate($clientId);

        return redirect()->route('crm.invoices.index')
            ->with('toast', ['type' => 'success', 'message' => 'Invoice deleted.']);
    }

    public function quotations(): View
    {
        $this->authorize('quotations.view');

        return view('crm.finance.quotations', [
            'records' => Quotation::with('client')->latest()->paginate(20),
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function createQuotation(): View
    {
        return view('crm.finance.quotation_form', [
            'quotation' => null,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function storeQuotation(Request $r): RedirectResponse
    {
        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'notes' => 'nullable|string|max:3000',
            'terms' => 'nullable|string|max:3000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($d['items'] as &$item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $disc = (float) ($item['discount'] ?? 0);
            $tax = (float) ($item['tax'] ?? 0);
            $lineTotal = ($qty * $price) - $disc + $tax;
            $item['total'] = round($lineTotal, 2);
            $subtotal += $item['total'];
        }
        unset($item);

        $totalDiscount = array_sum(array_map(fn($i) => (float) ($i['discount'] ?? 0), $d['items']));
        $totalTax = array_sum(array_map(fn($i) => (float) ($i['tax'] ?? 0), $d['items']));

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-' . now()->format('ymdHis'),
            'client_id' => $d['client_id'],
            'quotation_date' => $d['quotation_date'],
            'valid_until' => $d['valid_until'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'tax' => round($totalTax, 2),
            'total' => round($subtotal, 2),
            'notes' => $d['notes'] ?? null,
            'terms' => $d['terms'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        foreach ($d['items'] as $item) {
            $quotation->items()->create($item);
        }

        $ns = app(NotificationService::class);
        $ns->sendToPermission('quotations.view', 'Quotation created', $quotation->quotation_number . ' created for ' . $quotation->client->company_name, [
            'category' => 'quotation',
            'url' => route('crm.quotations.show', $quotation),
            'color' => 'primary',
        ]);

        return redirect()->route('crm.quotations.show', $quotation)
            ->with('toast', ['type' => 'success', 'message' => 'Quotation created successfully.']);
    }

    public function showQuotation(Quotation $quotation): View
    {
        $quotation->load(['client', 'items']);
        return view('crm.finance.quotation_show', ['quotation' => $quotation]);
    }

    public function printQuotation(Quotation $quotation): View
    {
        $quotation->load(['client', 'items']);
        return view('crm.finance.quotation_print', ['quotation' => $quotation]);
    }

    public function editQuotation(Quotation $quotation): View
    {
        $quotation->load('items');
        return view('crm.finance.quotation_form', [
            'quotation' => $quotation,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function updateQuotation(Request $r, Quotation $quotation): RedirectResponse
    {
        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'notes' => 'nullable|string|max:3000',
            'terms' => 'nullable|string|max:3000',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($d['items'] as &$item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $disc = (float) ($item['discount'] ?? 0);
            $tax = (float) ($item['tax'] ?? 0);
            $lineTotal = ($qty * $price) - $disc + $tax;
            $item['total'] = round($lineTotal, 2);
            $subtotal += $item['total'];
        }
        unset($item);

        $totalDiscount = array_sum(array_map(fn($i) => (float) ($i['discount'] ?? 0), $d['items']));
        $totalTax = array_sum(array_map(fn($i) => (float) ($i['tax'] ?? 0), $d['items']));

        $quotation->update([
            'client_id' => $d['client_id'],
            'quotation_date' => $d['quotation_date'],
            'valid_until' => $d['valid_until'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'tax' => round($totalTax, 2),
            'total' => round($subtotal, 2),
            'notes' => $d['notes'] ?? null,
            'terms' => $d['terms'] ?? null,
            'status' => $d['status'],
        ]);

        $quotation->items()->delete();
        foreach ($d['items'] as $item) {
            $quotation->items()->create($item);
        }

        return redirect()->route('crm.quotations.show', $quotation)
            ->with('toast', ['type' => 'success', 'message' => 'Quotation updated successfully.']);
    }

    public function destroyQuotation(Quotation $quotation): RedirectResponse
    {
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('crm.quotations.index')
            ->with('toast', ['type' => 'success', 'message' => 'Quotation deleted.']);
    }

    public function payments(): View
    {
        $this->authorize('payments.view');

        return view('crm.finance.payments', [
            'records' => Payment::with(['client', 'invoice', 'project'])->latest()->paginate(20),
            'clients' => Client::with('projects')->orderBy('company_name')->get(),
            'invoices' => Invoice::whereColumn('paid_amount', '<', 'total')->with('client')->orderByDesc('id')->get(),
            'projects' => \App\Models\Project::orderBy('name')->get(),
        ]);
    }

    public function storePayment(Request $r): RedirectResponse
    {
        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,jazzcash,easypaisa,card,cheque,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:3000',
        ]);

        if (!empty($d['invoice_id'])) {
            $invoice = Invoice::findOrFail($d['invoice_id']);
            $remainingBalance = round($invoice->balance(), 2);

            if ($remainingBalance <= 0) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'amount' => "Invoice {$invoice->invoice_number} is already fully paid."
                    ]);
            }

            if (round((float) $d['amount'], 2) > $remainingBalance) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'amount' => "Payment amount (" . number_format($d['amount'], 2) . " PKR) cannot exceed the remaining invoice balance of " . number_format($remainingBalance, 2) . " PKR."
                    ]);
            }
        }

        if (empty($d['project_id']) && !empty($d['client_id'])) {
            $clientProjects = \App\Models\Project::where('client_id', $d['client_id'])->get();
            if ($clientProjects->count() === 1) {
                $d['project_id'] = $clientProjects->first()->id;
            }
        }

        $p = Payment::create($d + [
            'payment_number' => 'PAY-' . now()->format('ymdHis'),
            'created_by' => auth()->id(),
        ]);

        if ($p->invoice) {
            $p->invoice->increment('paid_amount', $p->amount);
            $p->invoice->update([
                'status' => $p->invoice->balance() <= 0 ? 'paid' : 'partially_paid',
            ]);
        }

        ClientLedger::syncProjectBudgets($p->client_id);

        ClientLedger::create([
            'client_id' => $p->client_id,
            'transaction_date' => $p->payment_date,
            'reference' => $p->payment_number,
            'type' => 'payment',
            'description' => 'Payment received' . ($p->project ? ' for ' . $p->project->name : ''),
            'debit' => 0,
            'credit' => $p->amount,
            'balance' => 0,
            'source_type' => Payment::class,
            'source_id' => $p->id,
            'created_by' => auth()->id(),
        ]);

        ClientLedger::recalculate($p->client_id);

        $ns = app(NotificationService::class);
        $ns->sendToPermission('invoices.view', 'Payment received', $p->client->company_name . ' paid ' . $p->amount, [
            'category' => 'payment',
            'url' => route('crm.payments.index'),
            'color' => 'success',
        ]);

        if ($p->invoice?->created_by) {
            $ns->send(User::find($p->invoice->created_by), 'Payment received on invoice ' . $p->invoice->invoice_number, $p->client->company_name . ' paid ' . $p->amount, [
                'category' => 'payment',
                'url' => route('crm.payments.index'),
                'color' => 'success',
            ]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Payment recorded and receipt reference generated.']);
    }

    public function ledger(Client $client): View
    {
        ClientLedger::syncProjectBudgets($client->id);

        return view('crm.clients.ledger', [
            'client' => $client,
            'entries' => $client->ledger()->paginate(30),
        ]);
    }
}
