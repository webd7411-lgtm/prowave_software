<?php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\{ExpenseCategory, IncomeCategory, PaymentMethod};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $expenseCategories = ExpenseCategory::latest()->get();
        $incomeCategories  = IncomeCategory::latest()->get();
        $paymentMethods    = PaymentMethod::latest()->get();
        return view('finance.categories.index', compact('expenseCategories', 'incomeCategories', 'paymentMethods'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'color' => 'nullable|string|max:20', 'icon' => 'nullable|string|max:50', 'description' => 'nullable|string|max:300']);
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        $data['created_by'] = auth()->id();
        ExpenseCategory::create($data);
        return back()->with('toast', ['type' => 'success', 'message' => 'Expense category added.']);
    }

    public function storeIncome(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'color' => 'nullable|string|max:20', 'icon' => 'nullable|string|max:50', 'description' => 'nullable|string|max:300']);
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        $data['created_by'] = auth()->id();
        IncomeCategory::create($data);
        return back()->with('toast', ['type' => 'success', 'message' => 'Income category added.']);
    }

    public function storeMethod(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string|max:50']);
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        PaymentMethod::create($data);
        return back()->with('toast', ['type' => 'success', 'message' => 'Payment method added.']);
    }

    public function destroyExpense(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->is_system) return back()->with('toast', ['type' => 'error', 'message' => 'System categories cannot be deleted.']);
        $expenseCategory->delete();
        return back()->with('toast', ['type' => 'success', 'message' => 'Expense category deleted.']);
    }

    public function destroyIncome(IncomeCategory $incomeCategory): RedirectResponse
    {
        if ($incomeCategory->is_system) return back()->with('toast', ['type' => 'error', 'message' => 'System categories cannot be deleted.']);
        $incomeCategory->delete();
        return back()->with('toast', ['type' => 'success', 'message' => 'Income category deleted.']);
    }

    public function destroyMethod(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->is_system) return back()->with('toast', ['type' => 'error', 'message' => 'System methods cannot be deleted.']);
        $paymentMethod->delete();
        return back()->with('toast', ['type' => 'success', 'message' => 'Payment method deleted.']);
    }
}
