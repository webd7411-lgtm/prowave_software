<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectTask, ProjectExpense, ProjectTimeEntry, Client, User};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('project_reports.view');

        $reportType = $request->get('type', 'status');

        $clients = Client::orderBy('company_name')->get();
        $managers = User::orderBy('name')->get();
        $employees = User::where('status', 'active')->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        // Project Status Report Data
        $statusCounts = [
            'Planning' => Project::where('status', 'Planning')->count(),
            'In Progress' => Project::where('status', 'In Progress')->count(),
            'On Hold' => Project::where('status', 'On Hold')->count(),
            'Completed' => Project::where('status', 'Completed')->count(),
            'Overdue' => Project::where('status', '!=', 'Completed')->where('deadline', '<', now()->startOfDay())->count(),
        ];

        // Task Stats
        $taskCounts = [
            'To Do' => ProjectTask::where('status', 'To Do')->count(),
            'In Progress' => ProjectTask::where('status', 'In Progress')->count(),
            'Review' => ProjectTask::where('status', 'Review')->count(),
            'Testing' => ProjectTask::where('status', 'Testing')->count(),
            'Completed' => ProjectTask::where('status', 'Completed')->count(),
            'Overdue' => ProjectTask::where('status', '!=', 'Completed')->where('deadline', '<', now()->startOfDay())->count(),
        ];

        // Report Items List Query
        $query = Project::with(['client', 'manager', 'tasks', 'expenses']);

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('manager_id')) {
            $query->where('project_manager_id', $request->manager_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('deadline', '<=', $request->end_date);
        }

        $reportProjects = $query->get();

        return view('projects.reports', compact(
            'reportType',
            'clients',
            'managers',
            'employees',
            'projects',
            'statusCounts',
            'taskCounts',
            'reportProjects'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('project_reports.export');

        $type = $request->get('type', 'status');
        $projects = Project::with(['client', 'manager', 'tasks', 'expenses'])->get();

        $filename = "project_{$type}_report_" . date('Y_m_d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($projects, $type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'expenses') {
                fputcsv($handle, ['Project Code', 'Project Name', 'Client', 'Category', 'Expense Date', 'Amount (USD)', 'Description']);
                foreach (ProjectExpense::with(['project.client'])->get() as $exp) {
                    fputcsv($handle, [
                        $exp->project->project_code ?? '',
                        $exp->project->name ?? '',
                        $exp->project->client?->company_name ?? '',
                        $exp->category,
                        $exp->expense_date->format('Y-m-d'),
                        $exp->amount,
                        $exp->description,
                    ]);
                }
            } else {
                fputcsv($handle, ['Project Code', 'Project Name', 'Client', 'Manager', 'Type', 'Status', 'Progress (%)', 'Budget', 'Expenses', 'Est Profit', 'Deadline', 'Health']);
                foreach ($projects as $p) {
                    $totalExp = $p->expenses->sum('amount');
                    fputcsv($handle, [
                        $p->project_code,
                        $p->name,
                        $p->client?->company_name ?? 'N/A',
                        $p->manager?->name ?? 'Unassigned',
                        $p->project_type,
                        $p->status,
                        $p->progress,
                        $p->budget,
                        $totalExp,
                        $p->budget - $totalExp,
                        $p->deadline ? $p->deadline->format('Y-m-d') : '',
                        $p->health,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function print(Request $request): View
    {
        $this->authorize('project_reports.view');

        $query = Project::with(['client', 'manager', 'tasks', 'expenses']);

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('manager_id')) {
            $query->where('project_manager_id', $request->manager_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('deadline', '<=', $request->end_date);
        }

        $reportProjects = $query->get();

        return view('projects.reports_print', [
            'projects' => $reportProjects,
            'filters' => $request->only(['type', 'project_id', 'client_id', 'manager_id', 'status', 'start_date', 'end_date']),
        ]);
    }
}
