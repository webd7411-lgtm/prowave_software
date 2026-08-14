<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{Vendor, VendorPayment, CashAccount, BankAccount, PaymentMethod, Expense};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('vendors.view');

        $query = Vendor::withCount('vendorPayments')->withSum('expenses as total_expenses_sum', 'amount')->latest();
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('search')) {
            $q = '%'.$request->search.'%';
            $query->where(fn($w) => $w->where('name', 'like', $q)->orWhere('phone', 'like', $q)->orWhere('email', 'like', $q));
        }
        $records    = $query->paginate(20)->withQueryString();
        $categories = Vendor::distinct()->pluck('category')->filter()->sort()->values();
        return view('finance.vendors.index', compact('records', 'categories'));
    }

    public function create(): View
    {
        return view('finance.vendors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:150',
            'address'        => 'nullable|string|max:500',
            'category'       => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:2000',
            'status'         => 'required|in:active,inactive',
        ]);
        $data['vendor_number'] = Vendor::generateNumber();
        $data['created_by'] = auth()->id();
        Vendor::create($data);

        return redirect()->route('finance.vendors.index')
            ->with('toast', ['type' => 'success', 'message' => 'Vendor created successfully.']);
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load('vendorPayments.paymentMethod');
        $expenses       = Expense::where('vendor_id', $vendor->id)->with('category')->latest()->get();
        $cashAccounts   = CashAccount::where('is_active', true)->get();
        $bankAccounts   = BankAccount::where('status', 'active')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('finance.vendors.show', compact('vendor', 'expenses', 'cashAccounts', 'bankAccounts', 'paymentMethods'));
    }

    public function edit(Vendor $vendor): View
    {
        return view('finance.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:150',
            'address'        => 'nullable|string|max:500',
            'category'       => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:2000',
            'status'         => 'required|in:active,inactive',
        ]);
        $data['updated_by'] = auth()->id();
        $vendor->update($data);

        return redirect()->route('finance.vendors.show', $vendor)
            ->with('toast', ['type' => 'success', 'message' => 'Vendor updated.']);
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['updated_by' => auth()->id()]);
        $vendor->delete();
        return redirect()->route('finance.vendors.index')
            ->with('toast', ['type' => 'success', 'message' => 'Vendor deleted.']);
    }

    public function storePayment(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'expense_id'        => 'nullable|exists:expenses,id',
            'amount'            => 'required|numeric|min:0.01',
            'payment_date'      => 'required|date',
            'account_type'      => 'required|in:cash,bank',
            'cash_account_id'   => 'nullable|required_if:account_type,cash|exists:cash_accounts,id',
            'bank_account_id'   => 'nullable|required_if:account_type,bank|exists:bank_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference_number'  => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
        ]);
        $data['vendor_id']      = $vendor->id;
        $data['payment_number'] = VendorPayment::generateNumber();
        $data['created_by']     = auth()->id();
        VendorPayment::create($data);

        return back()->with('toast', ['type' => 'success', 'message' => 'Vendor payment recorded.']);
    }
}
