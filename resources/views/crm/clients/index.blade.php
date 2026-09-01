@extends('layouts.app')
@section('title', 'Clients')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clients</h1>
        <p class="page-subtitle">Your customer relationships, revenue and account health in one place.</p>
    </div>
    <a href="{{ route('crm.clients.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Client
    </a>
</div>

<x-card body-class="p-3">
    <form class="mb-3" method="GET" action="{{ route('crm.clients.index') }}">
        <input name="search" class="form-control" value="{{ request('search') }}" placeholder="Search clients by name, code, contact, phone...">
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Manager</th>
                    <th>Outstanding</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>
                            <a href="{{ route('crm.clients.show', $client) }}" class="fw-600 text-decoration-none">{{ $client->company_name }}</a>
                            <div class="fs-xs text-muted">{{ $client->client_number }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $client->phone ?: '—' }}</div>
                            @if($client->contact_person || $client->email)
                                <div class="fs-xs text-muted">
                                    {{ $client->contact_person ?: 'No Name' }}
                                    @if($client->email)
                                        <span>&middot; {{ $client->email }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>{{ $client->manager?->name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($client->outstanding(), 2) }} PKR</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('crm.clients.show', $client) }}" class="btn btn-sm btn-soft-primary" title="View Details">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @can('clients.edit')
                                    <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-sm btn-soft-secondary" title="Edit Client">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('clients.delete')
                                    <form action="{{ route('crm.clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete Client">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-empty-state title="No clients found" text="Clients converted from leads and added directly will appear here." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<div class="mt-3 d-flex justify-content-end">
    {{ $clients->links() }}
</div>
@endsection
