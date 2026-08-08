@props([
    'permissionGroups' => collect(),
    'selected' => [],
    'fieldName' => 'permissions',
])

@php
    $selected = collect($selected);
    $permissionGroups = collect($permissionGroups);
@endphp

<div class="permission-matrix">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="position-relative flex-grow-1" style="max-width: 320px;">
            <i class="bi bi-search position-absolute" style="left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--pw-text-muted); pointer-events: none; z-index: 2;"></i>
            <input type="search" class="form-control ps-5" placeholder="Search permissions..." data-permission-search>
        </div>
        <button type="button" class="btn btn-soft-primary btn-sm" data-permission-select-all>
            <i class="bi bi-check2-all me-1"></i>Select All
        </button>
        <button type="button" class="btn btn-soft-danger btn-sm" data-permission-remove-all>
            <i class="bi bi-x-circle me-1"></i>Remove All
        </button>
    </div>

    <div class="row g-3">
        @foreach ($permissionGroups as $group => $permissions)
            @php
                $groupId = Str::slug((string) $group);
                $groupSelected = $permissions->every(fn ($p) => $selected->contains($p->id));
            @endphp
            <div class="col-12 col-lg-6 col-xxl-4">
                <div class="card permission-group h-100" data-permission-group="{{ $groupId }}" data-group-name="{{ $group }}">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input
                                class="form-check-input permission-group-toggle"
                                type="checkbox"
                                id="group-{{ $groupId }}"
                                data-group-toggle="{{ $groupId }}"
                                @checked($groupSelected)
                            >
                            <label class="form-check-label fw-600" for="group-{{ $groupId }}">
                                {{ $group }}
                                <span class="text-muted fw-400 fs-xs ms-1">({{ $permissions->count() }})</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        @foreach ($permissions as $permission)
                            <div class="form-check permission-check-item" data-permission-item data-group-member="{{ $groupId }}">
                                <input
                                    class="form-check-input permission-checkbox"
                                    type="checkbox"
                                    name="{{ $fieldName }}[]"
                                    value="{{ $permission->id }}"
                                    id="perm-{{ $permission->id }}"
                                    data-group-toggle-input="{{ $groupId }}"
                                    @checked($selected->contains($permission->id))
                                >
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const matrix = document.querySelector('.permission-matrix');
                if (!matrix) {
                    return;
                }

                const searchInput = matrix.querySelector('[data-permission-search]');
                const checkboxes = () => matrix.querySelectorAll('.permission-checkbox');
                const groupToggles = () => matrix.querySelectorAll('.permission-group-toggle');

                const refreshGroupStates = () => {
                    matrix.querySelectorAll('[data-permission-group]').forEach((group) => {
                        const members = group.querySelectorAll('.permission-checkbox');
                        const toggle = group.querySelector('.permission-group-toggle');
                        const checked = group.querySelectorAll('.permission-checkbox:checked').length;
                        toggle.checked = members.length > 0 && checked === members.length;
                        toggle.indeterminate = checked > 0 && checked < members.length;
                    });
                };

                const refreshSearch = () => {
                    const term = (searchInput.value || '').toLowerCase().trim();
                    matrix.querySelectorAll('[data-permission-group]').forEach((group) => {
                        let visible = 0;
                        group.querySelectorAll('[data-permission-item]').forEach((item) => {
                            const matches = !term || item.textContent.toLowerCase().includes(term);
                            item.style.display = matches ? '' : 'none';
                            if (matches) {
                                visible++;
                            }
                        });
                        group.style.display = visible > 0 || !term ? '' : 'none';
                    });
                };

                searchInput.addEventListener('input', refreshSearch);

                checkboxes().forEach((cb) => cb.addEventListener('change', refreshGroupStates));

                groupToggles().forEach((toggle) => {
                    toggle.addEventListener('change', () => {
                        const groupId = toggle.dataset.groupToggle;
                        matrix.querySelectorAll(`[data-group-member="${groupId}"] .permission-checkbox`)
                            .forEach((cb) => {
                                cb.checked = toggle.checked;
                            });
                        refreshGroupStates();
                    });
                });

                matrix.querySelector('[data-permission-select-all]').addEventListener('click', () => {
                    const visible = [...matrix.querySelectorAll('.permission-checkbox')].filter((cb) => cb.closest('[data-permission-item]').style.display !== 'none');
                    visible.forEach((cb) => {
                        cb.checked = true;
                    });
                    refreshGroupStates();
                });

                matrix.querySelector('[data-permission-remove-all]').addEventListener('click', () => {
                    matrix.querySelectorAll('.permission-checkbox').forEach((cb) => {
                        cb.checked = false;
                    });
                    refreshGroupStates();
                });

                refreshGroupStates();
            });
        </script>
    @endpush
@endonce
