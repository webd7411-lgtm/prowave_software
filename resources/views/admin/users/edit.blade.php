@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">Update account details for {{ $user->name }}.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline">
                <i class="bi bi-eye me-1"></i>View Profile
            </a>
        </div>
    </div>

    <x-card title="Edit User" subtitle="Update information below" icon="bi-pencil-square" body-class="p-4">
        @include('admin.users._form', [
            'user' => $user,
            'route' => route('admin.users.update', $user),
            'method' => 'PATCH',
        ])
    </x-card>
@endsection
