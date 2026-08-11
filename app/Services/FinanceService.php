<?php
namespace App\Services;

use App\Models\{CashAccount, BankAccount, Expense, IncomeRecord, FinancialTransaction};
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Record a debit (expense/transfer out) against an account and update balance.
     */
    public function recordDebit(string $accountType, int $accountId, float $amount, string $reference, string $description, string $date, object $source): FinancialTransaction
    {
        return DB::transaction(function () use ($accountType, $accountId, $amount, $reference, $description, $date, $source) {
            $account = $this->getAccount($accountType, $accountId);
            $newBalance = (float) $account->current_balance - $amount;
            $account->update(['current_balance' => $newBalance]);

            return FinancialTransaction::create([
                'reference'        => $reference,
                'type'             => 'expense',
                'account_type'     => $accountType,
                'cash_account_id'  => $accountType === 'cash' ? $accountId : null,
                'bank_account_id'  => $accountType === 'bank' ? $accountId : null,
                'description'      => $description,
                'debit'            => $amount,
                'credit'           => 0,
                'balance_after'    => $newBalance,
                'transaction_date' => $date,
                'source_type'      => get_class($source),
                'source_id'        => $source->id,
                'created_by'       => auth()->id(),
            ]);
        });
    }

    /**
     * Record a credit (income/transfer in) against an account and update balance.
     */
    public function recordCredit(string $accountType, int $accountId, float $amount, string $reference, string $description, string $date, object $source, string $type = 'income'): FinancialTransaction
    {
        return DB::transaction(function () use ($accountType, $accountId, $amount, $reference, $description, $date, $source, $type) {
            $account = $this->getAccount($accountType, $accountId);
            $newBalance = (float) $account->current_balance + $amount;
            $account->update(['current_balance' => $newBalance]);

            return FinancialTransaction::create([
                'reference'        => $reference,
                'type'             => $type,
                'account_type'     => $accountType,
                'cash_account_id'  => $accountType === 'cash' ? $accountId : null,
                'bank_account_id'  => $accountType === 'bank' ? $accountId : null,
                'description'      => $description,
                'debit'            => 0,
                'credit'           => $amount,
                'balance_after'    => $newBalance,
                'transaction_date' => $date,
                'source_type'      => get_class($source),
                'source_id'        => $source->id,
                'created_by'       => auth()->id(),
            ]);
        });
    }

    /**
     * Reverse a transaction (for deletion/rejection).
     */
    public function reverseTransaction(FinancialTransaction $tx): void
    {
        DB::transaction(function () use ($tx) {
            $account = $this->getAccount($tx->account_type, $tx->account_type === 'cash' ? $tx->cash_account_id : $tx->bank_account_id);
            if ($tx->debit > 0) {
                $account->increment('current_balance', $tx->debit);
            } elseif ($tx->credit > 0) {
                $account->decrement('current_balance', $tx->credit);
            }
            $tx->delete();
        });
    }

    /**
     * Get summary stats for the dashboard.
     */
    public function getDashboardStats(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ?? now()->startOfMonth()->toDateString();
        $end   = $endDate   ?? now()->endOfMonth()->toDateString();

        $totalIncome   = IncomeRecord::whereBetween('income_date', [$start, $end])->sum('amount');
        $totalExpenses = Expense::where('status', '!=', 'rejected')->whereBetween('expense_date', [$start, $end])->sum('amount');
        $netProfit     = $totalIncome - $totalExpenses;

        $cashBalance = CashAccount::sum('current_balance');
        $bankBalance = BankAccount::where('status', 'active')->sum('current_balance');

        $invoiceStats = \App\Models\Invoice::selectRaw("
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status IN ('sent', 'partially_paid', 'overdue') THEN 1 ELSE 0 END) as unpaid_count,
            SUM(CASE WHEN status IN ('sent', 'partially_paid', 'overdue') THEN (total - paid_amount) ELSE 0 END) as pending_receivables,
            SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as paid_total
        ")->first();

        return compact('totalIncome', 'totalExpenses', 'netProfit', 'cashBalance', 'bankBalance', 'invoiceStats', 'start', 'end');
    }

    /**
     * Get monthly income vs expenses for chart (last 12 months).
     */
    public function getMonthlyChart(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $months[] = [
                'label'    => $d->format('M Y'),
                'income'   => (float) IncomeRecord::whereYear('income_date', $d->year)->whereMonth('income_date', $d->month)->sum('amount'),
                'expenses' => (float) Expense::where('status', '!=', 'rejected')->whereYear('expense_date', $d->year)->whereMonth('expense_date', $d->month)->sum('amount'),
            ];
        }
        return $months;
    }

    /**
     * Get expense breakdown by category for chart.
     */
    public function getExpenseBreakdown(?string $start = null, ?string $end = null): array
    {
        $query = Expense::where('status', '!=', 'rejected')
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id');

        if ($start) $query->where('expense_date', '>=', $start);
        if ($end)   $query->where('expense_date', '<=', $end);

        return $query->get()->map(fn($e) => [
            'label' => $e->category?->name ?? 'Uncategorized',
            'color' => $e->category?->color ?? '#6c757d',
            'total' => (float) $e->total,
        ])->toArray();
    }

    private function getAccount(string $type, int $id): CashAccount|BankAccount
    {
        return $type === 'cash'
            ? CashAccount::findOrFail($id)
            : BankAccount::findOrFail($id);
    }
}
