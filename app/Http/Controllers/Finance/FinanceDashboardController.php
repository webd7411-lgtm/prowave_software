<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{CashAccount, BankAccount, Client, Expense, IncomeRecord, Invoice};
use App\Services\FinanceService;
use Illuminate\View\View;

class FinanceDashboardController extends Controller
{
    public function __invoke(FinanceService $service): View
    {
        $this->authorize('finance.dashboard');

        $stats = $service->getDashboardStats();
        $monthlyChart = $service->getMonthlyChart();
        $breakdown    = $service->getExpenseBreakdown(
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString()
        );

        $recentExpenses = Expense::with(['category', 'addedBy'])
            ->latest()->limit(8)->get();

        $recentIncome = IncomeRecord::with(['category', 'client'])
            ->latest()->limit(8)->get();

        $overdueInvoices = Invoice::with('client')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->latest()
            ->limit(10)
            ->get();

        $cashAccounts = CashAccount::where('is_active', true)->get();
        $bankAccounts = BankAccount::where('status', 'active')->get();

        return view('finance.dashboard', compact(
            'stats', 'monthlyChart', 'breakdown',
            'recentExpenses', 'recentIncome', 'overdueInvoices',
            'cashAccounts', 'bankAccounts'
        ));
    }
}
