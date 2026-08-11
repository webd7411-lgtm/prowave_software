@extends('layouts.app')
@section('title', 'Financial Categories & Methods')
@section('content')
<div class="page-header mb-4">
    <h1 class="page-title mb-1">Categories & Payment Methods</h1>
    <p class="text-muted mb-0">Configure income categories, expense categories and payment methods</p>
</div>

<div class="row g-3">
    {{-- Expense Categories --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-receipt-cutoff me-2 text-danger"></i>Expense Categories</h6>
                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addExpenseCat">
                    <i class="bi bi-plus-circle me-1"></i>Add
                </button>
            </div>
            <div class="card-body">
                <div class="collapse mb-3" id="addExpenseCat">
                    <form method="POST" action="{{ route('finance.categories.expense.store') }}" class="border rounded p-2">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm mb-2" placeholder="Category name" required>
                        <input type="text" name="color" class="form-control form-control-sm mb-2" placeholder="Color (e.g. #dc3545)" value="#6c757d">
                        <input type="text" name="icon" class="form-control form-control-sm mb-2" placeholder="Icon (e.g. bi-truck)">
                        <textarea name="description" rows="2" class="form-control form-control-sm mb-2" placeholder="Description (optional)"></textarea>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check me-1"></i>Save Category</button>
                    </form>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($expenseCategories as $cat)
                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="badge rounded-pill" style="background:{{ $cat->color }}20;color:{{ $cat->color }};min-width:34px;text-align:center">
                                <i class="bi {{ $cat->icon ?? 'bi-tag' }}"></i>
                            </span>
                            <span class="text-truncate">{{ $cat->name }}</span>
                            @if($cat->is_system)
                            <span class="badge bg-light-subtle text-muted rounded-pill" style="font-size:.62rem">SYSTEM</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            @if(!$cat->is_active)
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>
                            @endif
                            @if(!$cat->is_system)
                            <form method="POST" action="{{ route('finance.categories.expense.destroy', $cat) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Delete {{ $cat->name }}?')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3 small">No expense categories</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Income Categories --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-arrow-down-circle me-2 text-success"></i>Income Categories</h6>
                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addIncomeCat">
                    <i class="bi bi-plus-circle me-1"></i>Add
                </button>
            </div>
            <div class="card-body">
                <div class="collapse mb-3" id="addIncomeCat">
                    <form method="POST" action="{{ route('finance.categories.income.store') }}" class="border rounded p-2">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm mb-2" placeholder="Category name" required>
                        <input type="text" name="color" class="form-control form-control-sm mb-2" placeholder="Color (e.g. #198754)" value="#198754">
                        <input type="text" name="icon" class="form-control form-control-sm mb-2" placeholder="Icon (e.g. bi-briefcase)">
                        <textarea name="description" rows="2" class="form-control form-control-sm mb-2" placeholder="Description (optional)"></textarea>
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check me-1"></i>Save Category</button>
                    </form>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($incomeCategories as $cat)
                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="badge rounded-pill" style="background:{{ $cat->color }}20;color:{{ $cat->color }};min-width:34px;text-align:center">
                                <i class="bi {{ $cat->icon ?? 'bi-tag' }}"></i>
                            </span>
                            <span class="text-truncate">{{ $cat->name }}</span>
                            @if($cat->is_system)
                            <span class="badge bg-light-subtle text-muted rounded-pill" style="font-size:.62rem">SYSTEM</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            @if(!$cat->is_active)
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>
                            @endif
                            @if(!$cat->is_system)
                            <form method="POST" action="{{ route('finance.categories.income.destroy', $cat) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Delete {{ $cat->name }}?')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3 small">No income categories</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Methods --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-credit-card me-2 text-primary"></i>Payment Methods</h6>
                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addMethod">
                    <i class="bi bi-plus-circle me-1"></i>Add
                </button>
            </div>
            <div class="card-body">
                <div class="collapse mb-3" id="addMethod">
                    <form method="POST" action="{{ route('finance.categories.method.store') }}" class="border rounded p-2">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm mb-2" placeholder="Method name (e.g. JazzCash)" required>
                        <input type="text" name="icon" class="form-control form-control-sm mb-2" placeholder="Icon (e.g. bi-phone)">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check me-1"></i>Save Method</button>
                    </form>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($paymentMethods as $m)
                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="badge bg-primary-soft rounded-pill" style="min-width:34px;text-align:center">
                                <i class="bi {{ $m->icon ?? 'bi-wallet2' }}"></i>
                            </span>
                            <span class="text-truncate">{{ $m->name }}</span>
                            @if($m->is_system)
                            <span class="badge bg-light-subtle text-muted rounded-pill" style="font-size:.62rem">SYSTEM</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            @if(!$m->is_active)
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>
                            @endif
                            @if(!$m->is_system)
                            <form method="POST" action="{{ route('finance.categories.method.destroy', $m) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Delete {{ $m->name }}?')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3 small">No payment methods</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
