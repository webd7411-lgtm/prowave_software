<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{IncomeRecord, IncomeCategory, CashAccount, BankAccount, PaymentMethod, Client, Project, Invoice, Payment};
use App\Services\FinanceService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    private function formData(): array
    {
        return [
            'categories'     => IncomeCategory::where('is_active', true)->orderBy('name')->get(),
            'cashAccounts'   => CashAccount::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts'   => BankAccount::where('status', 'active')->orderBy('bank_name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'clients'        => Client::orderBy('company_name')->get(),
            'projects'       => Project::orderBy('name')->get(),
        ];
    }

    public function index(Request $request): View
    {
        $this->authorize('income.view');

        $query = IncomeRecord::with(['category', 'client', 'project', 'addedBy'])->latest('income_date');

        if ($request->filled('category'))   $query->where('category_id', $request->category);
        if ($request->filled('client'))     $query->where('client_id', $request->client);
        if ($request->filled('project'))    $query->where('project_id', $request->project);
        if ($request->filled('from_date'))  $query->where('income_date', '>=', $request->from_date);
        if ($request->filled('to_date'))    $query->where('income_date', '<=', $request->to_date);
        if ($request->filled('min_amount')) $query->where('amount', '>=', $request->min_amount);
        if ($request->filled('max_amount')) $query->where('amount', '<=', $request->max_amount);
        if ($request->filled('search')) {
            $q = '%'.$request->search.'%';
            $query->where(fn($w) => $w->where('income_number', 'like', $q)->orWhere('description', 'like', $q)->orWhere('reference_number', 'like', $q));
        }

        $records     = $query->paginate(20)->withQueryString();
        $totalAmount = $query->toBase()->sum('amount');
        $filters     = $this->formData();

        return view('finance.income.index', compact('records', 'totalAmount', 'filters'));
    }

    public function create(): View
    {
        return view('finance.income.create', $this->formData());
    }

    public function store(Request $request, FinanceService $service): RedirectResponse
    {
        $data = $request->validate([
            'category_id'       => 'nullable|exists:income_categories,id',
            'client_id'         => 'nullable|exists:clients,id',
            'project_id'        => 'nullable|exists:projects,id',
            'invoice_id'        => 'nullable|exists:invoices,id',
            'amount'            => 'required|numeric|min:0.01',
            'income_date'       => 'required|date',
            'account_type'      => 'required|in:cash,bank',
            'cash_account_id'   => 'nullable|required_if:account_type,cash|exists:cash_accounts,id',
            'bank_account_id'   => 'nullable|required_if:account_type,bank|exists:bank_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference_number'  => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:2000',
            'notes'             => 'nullable|string|max:3000',
        ]);

        $data['income_number'] = IncomeRecord::generateNumber();
        $data['added_by'] = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('finance/income', 'public');
        }

        DB::transaction(function () use ($data, $service) {
            $income = IncomeRecord::create($data);

            $accountId = $income->account_type === 'cash' ? $income->cash_account_id : $income->bank_account_id;
            if ($accountId) {
                $service->recordCredit(
                    $income->account_type,
                    $accountId,
                    (float) $income->amount,
                    $income->income_number,
                    $income->description ?? 'Income: ' . ($income->category?->name ?? ''),
                    $income->income_date->toDateString(),
                    $income
                );
            }
        });

        return redirect()->route('finance.income.index')
            ->with('toast', ['type' => 'success', 'message' => 'Income recorded successfully.']);
    }

    public function show(IncomeRecord $income): View
    {
        $income->load(['category', 'client', 'project', 'invoice', 'cashAccount', 'bankAccount', 'paymentMethod', 'addedBy']);
        return view('finance.income.show', compact('income'));
    }

    public function edit(IncomeRecord $income): View
    {
        return view('finance.income.edit', array_merge(['income' => $income], $this->formData()));
    }

    public function update(Request $request, IncomeRecord $income): RedirectResponse
    {
        $data = $request->validate([
            'category_id'       => 'nullable|exists:income_categories,id',
            'client_id'         => 'nullable|exists:clients,id',
            'project_id'        => 'nullable|exists:projects,id',
            'amount'            => 'required|numeric|min:0.01',
            'income_date'       => 'required|date',
            'account_type'      => 'required|in:cash,bank',
            'cash_account_id'   => 'nullable|required_if:account_type,cash|exists:cash_accounts,id',
            'bank_account_id'   => 'nullable|required_if:account_type,bank|exists:bank_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference_number'  => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:2000',
            'notes'             => 'nullable|string|max:3000',
        ]);
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('finance/income', 'public');
        }

        $income->update($data);
        activity()->on($income)->causedBy(auth()->user())->log('updated income record');

        return redirect()->route('finance.income.show', $income)
            ->with('toast', ['type' => 'success', 'message' => 'Income record updated.']);
    }

    public function destroy(IncomeRecord $income): RedirectResponse
    {
        $income->delete();
        activity()->causedBy(auth()->user())->log('deleted income #' . $income->income_number);

        return redirect()->route('finance.income.index')
            ->with('toast', ['type' => 'success', 'message' => 'Income record deleted.']);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = IncomeRecord::with(['category', 'client', 'project', 'addedBy'])->latest('income_date');
        if ($request->filled('category'))  $query->where('category_id', $request->category);
        if ($request->filled('client'))    $query->where('client_id', $request->client);
        if ($request->filled('from_date')) $query->where('income_date', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->where('income_date', '<=', $request->to_date);

        $records = $query->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Income No', 'Category', 'Client', 'Project', 'Amount', 'Date', 'Reference', 'Description', 'Added By']);
            foreach ($records as $i => $r) {
                fputcsv($handle, [
                    $i + 1, $r->income_number, $r->category?->name,
                    $r->client?->company_name, $r->project?->name,
                    number_format($r->amount, 2), $r->income_date->format('d M Y'),
                    $r->reference_number, $r->description, $r->addedBy?->name,
                ]);
            }
            fclose($handle);
        }, 'income-records.csv', ['Content-Type' => 'text/csv']);
    }
}
