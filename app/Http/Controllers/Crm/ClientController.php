<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\ClientRequest;
use App\Models\{Client, User};
use App\Services\NotificationService;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('clients.view');

        $query = Client::with('manager')->latest();

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('company_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%'));
        }

        return view('crm.clients.index', [
            'clients' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('crm.clients.form', [
            'client' => new Client,
            'users' => User::orderBy('name')->get(),
            'action' => route('crm.clients.store'),
        ]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated() + [
            'client_number' => Client::generateNumber(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        app(NotificationService::class)->sendToPermission(
            'clients.view',
            'New client added',
            $client->company_name,
            ['category' => 'client', 'url' => route('crm.clients.show', $client), 'color' => 'success']
        );

        return redirect()->route('crm.clients.show', $client)
            ->with('toast', ['type' => 'success', 'message' => 'Client created successfully.']);
    }

    public function show(Client $client): View
    {
        $client->load(['manager', 'invoices', 'payments', 'quotations', 'ledger']);

        return view('crm.clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('crm.clients.form', [
            'client' => $client,
            'users' => User::orderBy('name')->get(),
            'action' => route('crm.clients.update', $client),
        ]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated() + ['updated_by' => auth()->id()]);

        return redirect()->route('crm.clients.show', $client)
            ->with('toast', ['type' => 'success', 'message' => 'Client updated.']);
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('crm.clients.index')
            ->with('toast', ['type' => 'success', 'message' => 'Client deleted.']);
    }
}
