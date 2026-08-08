@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add User</li>
                </ol>
            </nav>
            <h1 class="page-title">Add New User</h1>
            <p class="page-subtitle">Create a user account and assign roles.</p>
        </div>
    </div>

    <x-card title="New User" subtitle="Fill in the required information" icon="bi-person-plus" body-class="p-4">
        @include('admin.users._form', [
            'route' => route('admin.users.store'),
            'method' => 'POST',
        ])
    </x-card>
@endsection
