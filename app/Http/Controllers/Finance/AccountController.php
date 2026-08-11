<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{CashAccount, BankAccount};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class AccountController extends Controller
{
    public function cashIndex(): View
    {
        $accounts = CashAccount::latest()->get();
        return view('finance.accounts.cash-index', compact('accounts'));
    }

    public function cashCreate(): View
    {
        return view('finance.accounts.cash-create');
    }

    public function cashStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'account_code'    => 'required|string|max:50|unique:cash_accounts',
            'opening_balance' => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);
        $data['current_balance'] = $data['opening_balance'];
        $data['created_by'] = auth()->id();
        CashAccount::create($data);

        return redirect()->route('finance.accounts.cash')
            ->with('toast', ['type' => 'success', 'message' => 'Cash account created.']);
    }

    public function cashEdit(CashAccount $cashAccount): View
    {
        return view('finance.accounts.cash-edit', compact('cashAccount'));
    }

    public function cashUpdate(Request $request, CashAccount $cashAccount): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $data['updated_by'] = auth()->id();
        $cashAccount->update($data);

        return redirect()->route('finance.accounts.cash')
            ->with('toast', ['type' => 'success', 'message' => 'Cash account updated.']);
    }

    public function bankIndex(): View
    {
        $accounts = BankAccount::latest()->get();
        return view('finance.accounts.bank-index', compact('accounts'));
    }

    public function bankCreate(): View
    {
        return view('finance.accounts.bank-create');
    }

    public function bankStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name'       => 'required|string|max:100',
            'account_title'   => 'required|string|max:100',
            'account_number'  => 'nullable|string|max:50',
            'iban'            => 'nullable|string|max:50',
            'branch'          => 'nullable|string|max:100',
            'swift_code'      => 'nullable|string|max:20',
            'opening_balance' => 'required|numeric|min:0',
            'currency'        => 'required|string|max:10',
            'notes'           => 'nullable|string|max:500',
        ]);
        $data['current_balance'] = $data['opening_balance'];
        $data['created_by'] = auth()->id();
        BankAccount::create($data);

        return redirect()->route('finance.accounts.bank')
            ->with('toast', ['type' => 'success', 'message' => 'Bank account created.']);
    }

    public function bankEdit(BankAccount $bankAccount): View
    {
        return view('finance.accounts.bank-edit', compact('bankAccount'));
    }

    public function bankUpdate(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $data = $request->validate([
            'bank_name'     => 'required|string|max:100',
            'account_title' => 'required|string|max:100',
            'branch'        => 'nullable|string|max:100',
            'swift_code'    => 'nullable|string|max:20',
            'currency'      => 'required|string|max:10',
            'status'        => 'required|in:active,inactive',
            'notes'         => 'nullable|string|max:500',
        ]);
        $data['updated_by'] = auth()->id();
        $bankAccount->update($data);

        return redirect()->route('finance.accounts.bank')
            ->with('toast', ['type' => 'success', 'message' => 'Bank account updated.']);
    }
}
