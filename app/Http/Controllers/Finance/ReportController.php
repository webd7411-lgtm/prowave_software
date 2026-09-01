<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{Expense, IncomeRecord, Invoice, Payment, Project, CashAccount, BankAccount, ExpenseCategory};
use App\Services\FinanceService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('financial_reports.view');

        return view('finance.reports.index');
    }

    public function profitLoss(Request $request): View
    {
        $from = $request->from_date ?? now()->startOfYear()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        $totalIncome   = IncomeRecord::whereBetween('income_date', [$from, $to])->sum('amount');
        $totalExpenses = Expense::where('status', '!=', 'rejected')->whereBetween('expense_date', [$from, $to])->sum('amount');
        $netProfit     = $totalIncome - $totalExpenses;
        $profitPct     = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0;

        // Monthly breakdown
        $monthly = [];
        $start = \Carbon\Carbon::parse($from)->startOfMonth();
        $end   = \Carbon\Carbon::parse($to)->endOfMonth();
        while ($start->lte($end)) {
            $monthly[] = [
                'label'    => $start->format('M Y'),
                'income'   => (float) IncomeRecord::whereYear('income_date', $start->year)->whereMonth('income_date', $start->month)->sum('amount'),
                'expenses' => (float) Expense::where('status', '!=', 'rejected')->whereYear('expense_date', $start->year)->whereMonth('expense_date', $start->month)->sum('amount'),
            ];
            $start->addMonth();
        }

        return view('finance.reports.profit-loss', compact('from', 'to', 'totalIncome', 'totalExpenses', 'netProfit', 'profitPct', 'monthly'));
    }

    public function expenseReport(Request $request): View
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        $expenses = Expense::with(['category', 'vendor', 'project', 'addedBy'])
            ->where('status', '!=', 'rejected')
            ->whereBetween('expense_date', [$from, $to])
            ->orderBy('expense_date')
            ->get();

        $byCategory = $expenses->groupBy('category.name')->map(fn($g) => $g->sum('amount'));
        $total      = $expenses->sum('amount');

        return view('finance.reports.expenses', compact('expenses', 'byCategory', 'total', 'from', 'to'));
    }

    public function incomeReport(Request $request): View
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        $records = IncomeRecord::with(['category', 'client', 'project', 'addedBy'])
            ->whereBetween('income_date', [$from, $to])
            ->orderBy('income_date')
            ->get();

        $byCategory = $records->groupBy('category.name')->map(fn($g) => $g->sum('amount'));
        $total      = $records->sum('amount');

        return view('finance.reports.income', compact('records', 'byCategory', 'total', 'from', 'to'));
    }

    public function projectProfitability(Request $request): View
    {
        $projects = Project::with(['client'])
            ->withSum('expenses as total_expenses', 'amount')
            ->latest()
            ->paginate(20);

        $projects->getCollection()->transform(function (Project $project) {
            $invoices = Invoice::where('project_id', $project->id)->where('status', '!=', 'draft')->get();
            $project->total_invoiced = (float) $invoices->sum('total');
            $rawReceived = (float) $invoices->sum('paid_amount');
            $project->total_received = $project->budget > 0 ? min((float) $project->budget, $rawReceived) : $rawReceived;
            return $project;
        });

        return view('finance.reports.project-profitability', compact('projects'));
    }

    public function officeExpenses(Request $request): View
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        // Office expenses = non-project expenses
        $query = Expense::with(['category', 'vendor', 'addedBy'])
            ->whereNull('project_id')
            ->where('status', '!=', 'rejected')
            ->whereBetween('expense_date', [$from, $to]);

        if ($request->filled('category')) $query->where('category_id', $request->category);

        $expenses   = $query->orderBy('expense_date')->get();
        $byCategory = $expenses->groupBy('category.name')->map(fn($g) => $g->sum('amount'));
        $total      = $expenses->sum('amount');
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        // Current month vs previous month
        $currentMonth = Expense::whereNull('project_id')->where('status', '!=', 'rejected')
            ->whereYear('expense_date', now()->year)->whereMonth('expense_date', now()->month)->sum('amount');
        $prevMonth = Expense::whereNull('project_id')->where('status', '!=', 'rejected')
            ->whereYear('expense_date', now()->subMonth()->year)->whereMonth('expense_date', now()->subMonth()->month)->sum('amount');

        return view('finance.reports.office-expenses', compact('expenses', 'byCategory', 'total', 'from', 'to', 'categories', 'currentMonth', 'prevMonth'));
    }

    public function cashFlow(Request $request): View
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        $transactions = \App\Models\FinancialTransaction::with(['cashAccount', 'bankAccount'])
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->get();

        $totalIn  = $transactions->sum('credit');
        $totalOut = $transactions->sum('debit');
        $net      = $totalIn - $totalOut;

        $cashBalance = CashAccount::sum('current_balance');
        $bankBalance = BankAccount::where('status', 'active')->sum('current_balance');

        return view('finance.reports.cash-flow', compact('transactions', 'totalIn', 'totalOut', 'net', 'cashBalance', 'bankBalance', 'from', 'to'));
    }
}
