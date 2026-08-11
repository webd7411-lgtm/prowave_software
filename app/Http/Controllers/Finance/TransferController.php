<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{AccountTransfer, CashAccount, BankAccount};
use App\Services\FinanceService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index(): View
    {
        $records      = AccountTransfer::with(['fromCashAccount', 'fromBankAccount', 'toCashAccount', 'toBankAccount', 'createdBy'])->latest()->paginate(20);
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('status', 'active')->orderBy('bank_name')->get();
        return view('finance.transfers.index', compact('records', 'cashAccounts', 'bankAccounts'));
    }

    public function store(Request $request, FinanceService $service): RedirectResponse
    {
        $data = $request->validate([
            'from_account_type'    => 'required|in:cash,bank',
            'from_cash_account_id' => 'nullable|required_if:from_account_type,cash|exists:cash_accounts,id',
            'from_bank_account_id' => 'nullable|required_if:from_account_type,bank|exists:bank_accounts,id',
            'to_account_type'      => 'required|in:cash,bank',
            'to_cash_account_id'   => 'nullable|required_if:to_account_type,cash|exists:cash_accounts,id',
            'to_bank_account_id'   => 'nullable|required_if:to_account_type,bank|exists:bank_accounts,id',
            'amount'               => 'required|numeric|min:0.01',
            'transfer_date'        => 'required|date',
            'reference'            => 'nullable|string|max:100',
            'description'          => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $service) {
            $data['transfer_number'] = AccountTransfer::generateNumber();
            $data['created_by'] = auth()->id();
            $transfer = AccountTransfer::create($data);

            // Debit from account
            $fromId = $data['from_account_type'] === 'cash' ? $data['from_cash_account_id'] : $data['from_bank_account_id'];
            $service->recordDebit($data['from_account_type'], $fromId, $data['amount'], $transfer->transfer_number . '-OUT', 'Transfer out: ' . ($data['description'] ?? ''), $data['transfer_date'], $transfer);

            // Credit to account
            $toId = $data['to_account_type'] === 'cash' ? $data['to_cash_account_id'] : $data['to_bank_account_id'];
            $service->recordCredit($data['to_account_type'], $toId, $data['amount'], $transfer->transfer_number . '-IN', 'Transfer in: ' . ($data['description'] ?? ''), $data['transfer_date'], $transfer, 'transfer');
        });

        return redirect()->route('finance.transfers.index')
            ->with('toast', ['type' => 'success', 'message' => 'Transfer recorded successfully.']);
    }
}
