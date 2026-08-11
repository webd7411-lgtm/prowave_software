<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{Expense, ExpenseCategory, CashAccount, BankAccount, PaymentMethod, Vendor, Project};
use App\Services\FinanceService;
use App\Services\NotificationService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    private function formData(): array
    {
        return [
            'categories'     => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'cashAccounts'   => CashAccount::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts'   => BankAccount::where('status', 'active')->orderBy('bank_name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'vendors'        => Vendor::where('status', 'active')->orderBy('name')->get(),
            'projects'       => Project::orderBy('name')->get(),
        ];
    }

    public function index(Request $request): View
    {
        $query = Expense::with(['category', 'vendor', 'project', 'addedBy', 'approvedBy'])
            ->latest('expense_date');

        if ($request->filled('category'))   $query->where('category_id', $request->category);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('project'))    $query->where('project_id', $request->project);
        if ($request->filled('vendor'))     $query->where('vendor_id', $request->vendor);
        if ($request->filled('from_date'))  $query->where('expense_date', '>=', $request->from_date);
        if ($request->filled('to_date'))    $query->where('expense_date', '<=', $request->to_date);
        if ($request->filled('min_amount')) $query->where('amount', '>=', $request->min_amount);
        if ($request->filled('max_amount')) $query->where('amount', '<=', $request->max_amount);
        if ($request->filled('search')) {
            $q = '%'.$request->search.'%';
            $query->where(fn($w) => $w->where('expense_number', 'like', $q)->orWhere('description', 'like', $q)->orWhere('reference_number', 'like', $q));
        }

        $records   = $query->paginate(20)->withQueryString();
        $totalAmount = $query->toBase()->sum('amount');
        $filters   = $this->formData();

        return view('finance.expenses.index', compact('records', 'totalAmount', 'filters'));
    }

    public function create(): View
    {
        return view('finance.expenses.create', $this->formData());
    }

    public function store(Request $request, FinanceService $service): RedirectResponse
    {
        $data = $request->validate([
            'category_id'       => 'nullable|exists:expense_categories,id',
            'amount'            => 'required|numeric|min:0.01',
            'expense_date'      => 'required|date',
            'account_type'      => 'required|in:cash,bank',
            'cash_account_id'   => 'nullable|required_if:account_type,cash|exists:cash_accounts,id',
            'bank_account_id'   => 'nullable|required_if:account_type,bank|exists:bank_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference_number'  => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:2000',
            'vendor_id'         => 'nullable|exists:vendors,id',
            'project_id'        => 'nullable|exists:projects,id',
            'notes'             => 'nullable|string|max:3000',
        ]);

        $data['expense_number'] = Expense::generateNumber();
        $data['added_by'] = auth()->id();
        $data['status'] = 'pending';

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        DB::transaction(function () use ($data, $service) {
            $expense = Expense::create($data);

            $this->notifySubmitted($expense);
        });

        return redirect()->route('finance.expenses.index')
            ->with('toast', ['type' => 'success', 'message' => 'Expense recorded successfully. Pending approval.']);
    }

    public function show(Expense $expense): View
    {
        $expense->load(['category', 'vendor', 'project', 'cashAccount', 'bankAccount', 'paymentMethod', 'addedBy', 'approvedBy']);
        return view('finance.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        return view('finance.expenses.edit', array_merge(['expense' => $expense], $this->formData()));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        if (in_array($expense->status, ['approved', 'paid'])) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot edit an approved or paid expense.']);
        }

        $data = $request->validate([
            'category_id'       => 'nullable|exists:expense_categories,id',
            'amount'            => 'required|numeric|min:0.01',
            'expense_date'      => 'required|date',
            'account_type'      => 'required|in:cash,bank',
            'cash_account_id'   => 'nullable|required_if:account_type,cash|exists:cash_accounts,id',
            'bank_account_id'   => 'nullable|required_if:account_type,bank|exists:bank_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference_number'  => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:2000',
            'vendor_id'         => 'nullable|exists:vendors,id',
            'project_id'        => 'nullable|exists:projects,id',
            'notes'             => 'nullable|string|max:3000',
        ]);

        $data['updated_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expense->update($data);

        activity()->on($expense)->causedBy(auth()->user())->log('updated expense');

        return redirect()->route('finance.expenses.show', $expense)
            ->with('toast', ['type' => 'success', 'message' => 'Expense updated successfully.']);
    }

    public function approve(Request $request, Expense $expense, FinanceService $service): RedirectResponse
    {
        if ($expense->status !== 'pending') {
            return back()->with('toast', ['type' => 'error', 'message' => 'Only pending expenses can be approved.']);
        }

        $request->validate(['approval_remarks' => 'nullable|string|max:1000']);

        DB::transaction(function () use ($expense, $request, $service) {
            $expense->update([
                'status'           => 'approved',
                'approved_by'      => auth()->id(),
                'approved_at'      => now(),
                'approval_remarks' => $request->approval_remarks,
            ]);

            // Deduct from account
            $accountId = $expense->account_type === 'cash' ? $expense->cash_account_id : $expense->bank_account_id;
            if ($accountId) {
                $service->recordDebit(
                    $expense->account_type,
                    $accountId,
                    (float) $expense->amount,
                    $expense->expense_number,
                    $expense->description ?? 'Expense: ' . ($expense->category?->name ?? ''),
                    $expense->expense_date->toDateString(),
                    $expense
                );
            }
        });

        activity()->on($expense)->causedBy(auth()->user())->log('approved expense');

        if ($expense->added_by && $expense->added_by !== auth()->id()) {
            app(NotificationService::class)->send(
                $expense->addedBy,
                'Expense approved',
                'Expense '.$expense->expense_number.' ('.number_format($expense->amount, 2).') was approved.',
                ['category' => 'expense', 'url' => route('finance.expenses.show', $expense), 'color' => 'success']
            );
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Expense approved and recorded.']);
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $request->validate(['approval_remarks' => 'required|string|max:1000']);

        $expense->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'approval_remarks' => $request->approval_remarks,
        ]);

        activity()->on($expense)->causedBy(auth()->user())->log('rejected expense');

        if ($expense->added_by && $expense->added_by !== auth()->id()) {
            app(NotificationService::class)->send(
                $expense->addedBy,
                'Expense rejected',
                'Expense '.$expense->expense_number.' ('.number_format($expense->amount, 2).') was rejected.',
                ['category' => 'expense', 'url' => route('finance.expenses.show', $expense), 'color' => 'danger']
            );
        }

        return back()->with('toast', ['type' => 'warning', 'message' => 'Expense rejected.']);
    }

    protected function notifySubmitted(Expense $expense): void
    {
        app(NotificationService::class)->sendToPermission(
            'expenses.approve',
            'Expense submitted for approval',
            'Expense '.$expense->expense_number.' ('.number_format($expense->amount, 2).') — '.($expense->category?->name ?? 'General'),
            ['category' => 'expense', 'url' => route('finance.expenses.show', $expense), 'color' => 'warning']
        );
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->status === 'paid') {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot delete a paid expense.']);
        }

        $expense->update(['deleted_by' => auth()->id()]);
        $expense->delete();

        activity()->causedBy(auth()->user())->log('deleted expense #' . $expense->expense_number);

        return redirect()->route('finance.expenses.index')
            ->with('toast', ['type' => 'success', 'message' => 'Expense deleted.']);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Expense::with(['category', 'vendor', 'project', 'addedBy'])->latest('expense_date');
        if ($request->filled('category'))  $query->where('category_id', $request->category);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from_date')) $query->where('expense_date', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->where('expense_date', '<=', $request->to_date);

        $expenses = $query->get();

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=expenses.csv'];

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Expense No', 'Category', 'Amount', 'Date', 'Status', 'Vendor', 'Project', 'Description', 'Added By']);
            foreach ($expenses as $i => $e) {
                fputcsv($handle, [
                    $i + 1, $e->expense_number, $e->category?->name,
                    number_format($e->amount, 2), $e->expense_date->format('d M Y'),
                    ucfirst($e->status), $e->vendor?->name, $e->project?->name,
                    $e->description, $e->addedBy?->name,
                ]);
            }
            fclose($handle);
        }, 'expenses.csv', $headers);
    }
}
