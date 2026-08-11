@extends('layouts.app')

@section('title', 'Create New Project')

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Create Project</h1>
            <p class="text-muted mb-0 fs-sm">Set up a new software project, assign client, PM, team members, initial milestones and tasks.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Projects
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please resolve the following issues:</div>
            <ul class="mb-0 fs-sm ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            {{-- Main Info Column --}}
            <div class="col-12 col-lg-8">
                {{-- Basic Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Project Details</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            {{-- Project Name --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Project Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Enterprise E-Commerce Portal" value="{{ old('name') }}" required>
                            </div>

                            {{-- Client & Type --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold fs-sm">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">-- Internal / Select Client --</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_name }} ({{ $c->contact_person ?? 'No Contact' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold fs-sm">Project Type <span class="text-danger">*</span></label>
                                <select name="project_type" class="form-select" required>
                                    @foreach(['Website', 'Web Application', 'Mobile Application', 'Desktop Software', 'ERP', 'CRM', 'E-commerce', 'Maintenance', 'SEO', 'Digital Marketing', 'Other'] as $type)
                                        <option value="{{ $type }}" {{ old('project_type', 'Web Application') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Description & Scope</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Brief project scope, requirements, tech stack, key deliverables...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Initial Milestones --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-flag text-primary me-2"></i>Initial Milestones</h5>
                        <button type="button" class="btn btn-sm btn-soft-primary" id="addMilestoneBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Milestone
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div id="milestonesContainer">
                            {{-- Preset Default Milestones --}}
                            @php
                                $defaultMilestones = ['Planning & Scope', 'UI/UX Design', 'Frontend & Backend Development', 'QA & Client Testing', 'Deployment'];
                            @endphp
                            @foreach($defaultMilestones as $index => $msName)
                                <div class="row g-2 align-items-center mb-2 milestone-row">
                                    <div class="col-7 col-md-8">
                                        <input type="text" name="milestones[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $msName }}" placeholder="Milestone Name">
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <input type="date" name="milestones[{{ $index }}][deadline]" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-1 text-end">
                                        <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Initial Tasks --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check text-primary me-2"></i>Initial Tasks</h5>
                        <button type="button" class="btn btn-sm btn-soft-primary" id="addTaskBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Task
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div id="tasksContainer">
                            <div class="row g-2 align-items-center mb-2 task-row">
                                <div class="col-12 col-md-5">
                                    <input type="text" name="tasks[0][title]" class="form-control form-control-sm" placeholder="Task Title (e.g. Database Architecture)">
                                </div>
                                <div class="col-6 col-md-3">
                                    <select name="tasks[0][assigned_to]" class="form-select form-select-sm">
                                        <option value="">Unassigned</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5 col-md-3">
                                    <select name="tasks[0][priority]" class="form-select form-select-sm">
                                        <option value="Medium">Medium</option>
                                        <option value="Low">Low</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar Column --}}
            <div class="col-12 col-lg-4">
                {{-- Status & Timeline --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i>Status & Timeline</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Project Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled'] as $st)
                                    <option value="{{ $st }}" {{ old('status', 'Planning') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach(['Low', 'Medium', 'High', 'Urgent'] as $pr)
                                    <option value="{{ $pr }}" {{ old('priority', 'Medium') === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Project Manager</label>
                            <select name="project_manager_id" class="form-select">
                                <option value="">-- Assign Project Manager --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('project_manager_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Deadline</label>
                                <input type="date" name="deadline" class="form-control" value="{{ old('deadline') }}">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Budget ($)</label>
                                <input type="number" step="0.01" min="0" name="budget" class="form-control" placeholder="0.00" value="{{ old('budget') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Est. Hours</label>
                                <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" placeholder="0" value="{{ old('estimated_hours') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assign Team Members --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i>Assign Team</h5>
                        <button type="button" class="btn btn-sm btn-soft-primary" id="addTeamMemberBtn">
                            <i class="bi bi-plus me-1"></i> Add Member
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div id="teamContainer">
                            <div class="row g-2 align-items-center mb-2 team-row">
                                <div class="col-6">
                                    <select name="team[0][user_id]" class="form-select form-select-sm">
                                        <option value="">-- Select Member --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="team[0][role]" class="form-select form-select-sm">
                                        @foreach(['Team Lead', 'Developer', 'Designer', 'QA', 'SEO', 'Content Writer', 'Sales', 'Other'] as $r)
                                            <option value="{{ $r }}">{{ $r }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-2 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Save & Create Project
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-light w-100 text-muted">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let milestoneIdx = {{ count($defaultMilestones) }};
        let taskIdx = 1;
        let teamIdx = 1;

        // Dynamic Add Milestone
        document.getElementById('addMilestoneBtn').addEventListener('click', function () {
            const container = document.getElementById('milestonesContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 milestone-row';
            row.innerHTML = `
                <div class="col-7 col-md-8">
                    <input type="text" name="milestones[${milestoneIdx}][name]" class="form-control form-control-sm" placeholder="Milestone Name">
                </div>
                <div class="col-4 col-md-3">
                    <input type="date" name="milestones[${milestoneIdx}][deadline]" class="form-control form-control-sm">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                </div>
            `;
            container.appendChild(row);
            milestoneIdx++;
        });

        // Dynamic Add Task
        document.getElementById('addTaskBtn').addEventListener('click', function () {
            const container = document.getElementById('tasksContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 task-row';
            row.innerHTML = `
                <div class="col-12 col-md-5">
                    <input type="text" name="tasks[${taskIdx}][title]" class="form-control form-control-sm" placeholder="Task Title">
                </div>
                <div class="col-6 col-md-3">
                    <select name="tasks[${taskIdx}][assigned_to]" class="form-select form-select-sm">
                        <option value="">Unassigned</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5 col-md-3">
                    <select name="tasks[${taskIdx}][priority]" class="form-select form-select-sm">
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                </div>
            `;
            container.appendChild(row);
            taskIdx++;
        });

        // Dynamic Add Team Member
        document.getElementById('addTeamMemberBtn').addEventListener('click', function () {
            const container = document.getElementById('teamContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 team-row';
            row.innerHTML = `
                <div class="col-6">
                    <select name="team[${teamIdx}][user_id]" class="form-select form-select-sm">
                        <option value="">-- Select Member --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5">
                    <select name="team[${teamIdx}][role]" class="form-select form-select-sm">
                        @foreach(['Team Lead', 'Developer', 'Designer', 'QA', 'SEO', 'Content Writer', 'Sales', 'Other'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm text-danger remove-row-btn"><i class="bi bi-x-lg"></i></button>
                </div>
            `;
            container.appendChild(row);
            teamIdx++;
        });

        // Remove row delegated event
        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('.milestone-row, .task-row, .team-row');
                if (row) row.remove();
            }
        });
    });
</script>
@endpush
@endsection
