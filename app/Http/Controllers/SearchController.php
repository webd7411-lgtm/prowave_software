<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DailyTask;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        return view('search.index', ['term' => $request->string('q')->toString()]);
    }

    public function data(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        if (mb_strlen($term) < 2) {
            return response()->json(['groups' => collect()]);
        }

        $groups = collect();

        if ($request->user()->can('staff.view') || $request->user()->can('users.view')) {
            $groups->push($this->group('Staff', 'bi-people', 'text-primary',
                User::query()->active()->search($term)->limit(5)->get()->map(fn ($u) => [
                    'id' => $u->id,
                    'title' => $u->name,
                    'subtitle' => $u->employee_id.' · '.$u->job_title,
                    'url' => $request->user()->can('staff.view') ? route('hr.staff.show', $u) : route('admin.users.show', $u),
                    'avatar' => $u->avatar_url,
                ])
            ));
        }

        if ($request->user()->can('clients.view')) {
            $groups->push($this->group('Clients', 'bi-buildings', 'text-info',
                Client::query()->where(function ($q) use ($term) {
                    $q->where('company_name', 'like', "%{$term}%")
                        ->orWhere('contact_person', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('client_number', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->company_name,
                    'subtitle' => $c->client_number.' · '.($c->contact_person ?: 'No contact'),
                    'url' => route('crm.clients.show', $c),
                ])
            ));
        }

        if ($request->user()->can('leads.view')) {
            $groups->push($this->group('Leads', 'bi-person-lines-fill', 'text-warning',
                Lead::query()->where(function ($q) use ($term) {
                    $q->where('company_name', 'like', "%{$term}%")
                        ->orWhere('contact_person', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('lead_number', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn ($l) => [
                    'id' => $l->id,
                    'title' => $l->company_name,
                    'subtitle' => $l->lead_number.' · '.ucfirst($l->stage),
                    'url' => route('crm.leads.show', $l),
                ])
            ));
        }

        if ($request->user()->can('projects.view')) {
            $groups->push($this->group('Projects', 'bi-folder-symlink', 'text-indigo',
                Project::query()->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('project_code', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->name,
                    'subtitle' => $p->project_code.' · '.($p->client?->company_name ?? 'No client'),
                    'url' => route('projects.show', $p),
                ])
            ));
        }

        $taskTitle = fn ($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'subtitle' => $t->status.' · '.($t->project?->name ?? ''),
            'url' => method_exists($t, 'assignedTo') ? route('projects.tasks.show', $t) : route('hr.tasks.index', ['search' => $term]),
        ];

        if ($request->user()->can('project_tasks.view')) {
            $groups->push($this->group('Tasks', 'bi-check2-square', 'text-success',
                ProjectTask::query()->with('project')->where('title', 'like', "%{$term}%")->limit(5)->get()->map($taskTitle)
            ));
        }

        if ($request->user()->can('tasks.view')) {
            $groups->push($this->group('Daily Tasks', 'bi-list-check', 'text-success',
                DailyTask::query()->with('assignee:id,name')->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%");
                })->limit(5)->get()->map($taskTitle)
            ));
        }

        if ($request->user()->can('invoices.view')) {
            $groups->push($this->group('Invoices', 'bi-receipt', 'text-danger',
                Invoice::query()->with('client')->where(function ($q) use ($term) {
                    $q->where('invoice_number', 'like', "%{$term}%")
                        ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$term}%"));
                })->limit(5)->get()->map(fn ($i) => [
                    'id' => $i->id,
                    'title' => $i->invoice_number,
                    'subtitle' => $i->client?->company_name.' · '.number_format($i->total, 2),
                    'url' => route('crm.invoices.index', ['search' => $i->invoice_number]),
                ])
            ));
        }

        if ($request->user()->can('payments.view')) {
            $groups->push($this->group('Payments', 'bi-cash-coin', 'text-success',
                Payment::query()->with('client')->where(function ($q) use ($term) {
                    $q->where('payment_number', 'like', "%{$term}%")
                        ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$term}%"));
                })->limit(5)->get()->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->payment_number,
                    'subtitle' => $p->client?->company_name.' · '.number_format($p->amount, 2),
                    'url' => route('crm.payments.index', ['search' => $p->payment_number]),
                ])
            ));
        }

        if ($request->user()->can('expenses.view')) {
            $groups->push($this->group('Expenses', 'bi-receipt-cutoff', 'text-warning',
                Expense::query()->where(function ($q) use ($term) {
                    $q->where('expense_number', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->expense_number.' — '.number_format($e->amount, 2),
                    'subtitle' => ucfirst($e->status),
                    'url' => route('finance.expenses.show', $e),
                ])
            ));
        }

        if ($request->user()->can('meetings.view')) {
            $groups->push($this->group('Meetings', 'bi-people', 'text-info',
                Meeting::query()->where('title', 'like', "%{$term}%")->limit(5)->get()->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'subtitle' => $m->meeting_date?->format('M d, Y').' · '.ucfirst($m->type),
                    'url' => route('meetings.index', ['search' => $m->title]),
                ])
            ));
        }

        $groups = $groups->filter(fn ($g) => $g['results']->isNotEmpty())->values();

        return response()->json(['groups' => $groups, 'term' => $term]);
    }

    protected function group(string $label, string $icon, string $color, $results): array
    {
        return [
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
            'count' => $results->count(),
            'results' => $results,
        ];
    }
}
