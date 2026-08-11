<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{FinancialTransaction, CashAccount, BankAccount};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = FinancialTransaction::with(['cashAccount', 'bankAccount', 'createdBy'])->latest('transaction_date');

        if ($request->filled('type'))         $query->where('type', $request->type);
        if ($request->filled('account_type')) $query->where('account_type', $request->account_type);
        if ($request->filled('cash_account')) $query->where('cash_account_id', $request->cash_account);
        if ($request->filled('bank_account')) $query->where('bank_account_id', $request->bank_account);
        if ($request->filled('from_date'))    $query->where('transaction_date', '>=', $request->from_date);
        if ($request->filled('to_date'))      $query->where('transaction_date', '<=', $request->to_date);
        if ($request->filled('search')) {
            $q = '%'.$request->search.'%';
            $query->where(fn($w) => $w->where('reference', 'like', $q)->orWhere('description', 'like', $q));
        }

        $records      = $query->paginate(25)->withQueryString();
        $totalDebit   = $query->toBase()->sum('debit');
        $totalCredit  = $query->toBase()->sum('credit');
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('status', 'active')->orderBy('bank_name')->get();

        return view('finance.transactions.index', compact('records', 'totalDebit', 'totalCredit', 'cashAccounts', 'bankAccounts'));
    }

    public function create(): View
    {
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('status', 'active')->orderBy('bank_name')->get();

        return view('finance.transactions.create', compact('cashAccounts', 'bankAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type'             => ['required', 'in:income,expense,transfer,payment,refund,adjustment'],
            'account_type'     => ['required', 'in:cash,bank'],
            'cash_account_id'  => ['nullable', 'required_if:account_type,cash', 'exists:cash_accounts,id'],
            'bank_account_id'  => ['nullable', 'required_if:account_type,bank', 'exists:bank_accounts,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'reference'        => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
        ]);

        $type = $validated['type'];

        // Income-side types increase the account balance (credit), outflows decrease it (debit).
        $isInflow  = in_array($type, ['income', 'payment', 'refund', 'adjustment'], true);
        $isDecrease = ($type === 'adjustment' && $request->input('adjustment_type') === 'decrease');

        $validated['debit']  = !$isInflow || $isDecrease ? $validated['amount'] : 0;
        $validated['credit'] = $isInflow && !$isDecrease ? $validated['amount'] : 0;
        unset($validated['amount']);

        if ($validated['account_type'] === 'cash') {
            $validated['bank_account_id'] = null;
        } else {
            $validated['cash_account_id'] = null;
        }

        $validated['created_by'] = $request->user()->id;

        DB::transaction(function () use ($validated) {
            $transaction = FinancialTransaction::create($validated);

            $account = $validated['account_type'] === 'cash'
                ? CashAccount::findOrFail($validated['cash_account_id'])
                : BankAccount::findOrFail($validated['bank_account_id']);

            $account->balance = ($account->balance ?? 0) + ($validated['credit'] - $validated['debit']);
            $account->save();
        });

        return redirect()->route('finance.transactions.index')
            ->with('toast', ['type' => 'success', 'message' => 'Transaction recorded successfully.']);
    }

    public function show(FinancialTransaction $transaction): View
    {
        $transaction->load(['cashAccount', 'bankAccount', 'createdBy']);

        return view('finance.transactions.show', compact('transaction'));
    }

    public function edit(FinancialTransaction $transaction): View
    {
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('status', 'active')->orderBy('bank_name')->get();

        return view('finance.transactions.edit', compact('transaction', 'cashAccounts', 'bankAccounts'));
    }

    public function update(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type'             => ['required', 'in:income,expense,transfer,payment,refund,adjustment'],
            'account_type'     => ['required', 'in:cash,bank'],
            'cash_account_id'  => ['nullable', 'required_if:account_type,cash', 'exists:cash_accounts,id'],
            'bank_account_id'  => ['nullable', 'required_if:account_type,bank', 'exists:bank_accounts,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'reference'        => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use (&$validated, $transaction) {
            // Revert the old transaction's effect on its account balance.
            $oldAccount = $transaction->account_type === 'cash'
                ? CashAccount::find($transaction->cash_account_id)
                : BankAccount::find($transaction->bank_account_id);
            if ($oldAccount) {
                $oldAccount->balance = ($oldAccount->balance ?? 0) - ($transaction->credit - $transaction->debit);
                $oldAccount->save();
            }

            $type = $validated['type'];
            $isInflow   = in_array($type, ['income', 'payment', 'refund', 'adjustment'], true);
            $isDecrease = ($type === 'adjustment' && $request->input('adjustment_type') === 'decrease');

            $validated['debit']  = !$isInflow || $isDecrease ? $validated['amount'] : 0;
            $validated['credit'] = $isInflow && !$isDecrease ? $validated['amount'] : 0;
            unset($validated['amount']);

            if ($validated['account_type'] === 'cash') {
                $validated['bank_account_id'] = null;
            } else {
                $validated['cash_account_id'] = null;
            }

            $transaction->update($validated);

            $newAccount = $validated['account_type'] === 'cash'
                ? CashAccount::findOrFail($validated['cash_account_id'])
                : BankAccount::findOrFail($validated['bank_account_id']);
            $newAccount->balance = ($newAccount->balance ?? 0) + ($validated['credit'] - $validated['debit']);
            $newAccount->save();
        });

        return redirect()->route('finance.transactions.index')
            ->with('toast', ['type' => 'success', 'message' => 'Transaction updated successfully.']);
    }

    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $account = $transaction->account_type === 'cash'
                ? CashAccount::find($transaction->cash_account_id)
                : BankAccount::find($transaction->bank_account_id);
            if ($account) {
                $account->balance = ($account->balance ?? 0) - ($transaction->debit - $transaction->credit);
                $account->save();
            }

            $transaction->delete();
        });

        return redirect()->route('finance.transactions.index')
            ->with('toast', ['type' => 'success', 'message' => 'Transaction deleted successfully.']);
    }
}
