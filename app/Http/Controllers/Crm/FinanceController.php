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
        return view('crm.finance.invoices', [
            'records' => Invoice::with('client')->latest()->paginate(20),
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function quotations(): View
    {
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
        return view('crm.finance.payments', [
            'records' => Payment::with(['client', 'invoice'])->latest()->paginate(20),
            'clients' => Client::orderBy('company_name')->get(),
            'invoices' => Invoice::orderByDesc('id')->get(),
        ]);
    }

    public function storePayment(Request $r): RedirectResponse
    {
        $d = $r->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,jazzcash,easypaisa,card,cheque,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:3000',
        ]);

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

        $balance = (float) ClientLedger::where('client_id', $p->client_id)->latest('id')->value('balance') - (float) $p->amount;

        ClientLedger::create([
            'client_id' => $p->client_id,
            'transaction_date' => $p->payment_date,
            'reference' => $p->payment_number,
            'type' => 'payment',
            'description' => 'Payment received',
            'credit' => $p->amount,
            'balance' => $balance,
            'source_type' => Payment::class,
            'source_id' => $p->id,
            'created_by' => auth()->id(),
        ]);

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
        return view('crm.clients.ledger', [
            'client' => $client,
            'entries' => $client->ledger()->paginate(30),
        ]);
    }
}
