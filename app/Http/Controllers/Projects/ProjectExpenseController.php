<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectExpenseRequest;
use App\Models\{Project, ProjectExpense, ProjectActivity};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProjectExpenseController extends Controller
{
    public function store(ProjectExpenseRequest $request): RedirectResponse
    {
        $this->authorize('project_expenses.create');

        $validated = $request->validated();
        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('project_expenses/' . $validated['project_id'], 'public');
        }

        $expense = ProjectExpense::create([
            'project_id' => $validated['project_id'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
            'receipt_path' => $receiptPath,
            'added_by' => auth()->id(),
        ]);

        ProjectActivity::log(
            $validated['project_id'],
            'Expense added',
            "Project expense of " . number_format($expense->amount, 2) . " ({$expense->category}) recorded.",
            'ProjectExpense',
            $expense->id
        );

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Project expense recorded.']);
    }

    public function destroy(ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('project_expenses.edit');

        $projectId = $expense->project_id;
        $amount = $expense->amount;
        $expense->delete();

        ProjectActivity::log($projectId, 'Expense deleted', "Expense of " . number_format($amount, 2) . " was removed.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Expense deleted.']);
    }
}
