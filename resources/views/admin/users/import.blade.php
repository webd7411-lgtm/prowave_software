@extends('layouts.app')

@section('title', 'Import Users')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Import</li>
                </ol>
            </nav>
            <h1 class="page-title">Import Users</h1>
            <p class="page-subtitle">Bulk import users from an Excel or CSV file.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-7">
            <x-card title="Upload File" subtitle="Excel (.xlsx, .xls) or CSV, max 4 MB" icon="bi-upload">
                <x-alert type="info" :title="'Columns expected'">
                    <code>name</code>, <code>email</code>, <code>username</code>, <code>employee_id</code>,
                    <code>phone</code>, <code>designation</code>, <code>department</code>, <code>status</code>,
                    <code>role</code>. Existing emails are updated; new rows are created with a generated password.
                </x-alert>

                <x-form :action="route('admin.users.import.store')" method="POST" multipart spinner="Importing..." cancel-url="{{ route('admin.users.index') }}">
                    <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" accept=".xlsx,.xls,.csv">
                    @error('file')
                        <div class="invalid-feedback d-flex"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </x-form>
            </x-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-card title="Available Roles" subtitle="Role names recognized during import" icon="bi-shield-check">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <span class="badge badge-soft-primary">{{ $role->name }}</span>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
@endsection
