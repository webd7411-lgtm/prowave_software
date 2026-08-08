<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Exports\UsersExport;
use App\Exports\UsersPdfExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportUsersRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\Department;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\Admin\UserService;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $repository,
        protected UserService $service,
        protected RoleRepository $roles,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'department_id', 'role', 'trashed']);

        $users = $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15);

        $data = [
            'users' => $users,
            'filters' => $filters,
            'roles' => $this->roles->options(),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => UserStatus::options(),
            'stats' => [
                'total' => User::withTrashed()->count(),
                'active' => User::where('status', UserStatus::Active->value)->count(),
                'inactive' => User::where('status', UserStatus::Inactive->value)->count(),
                'suspended' => User::where('status', UserStatus::Suspended->value)->count(),
            ],
        ];

        return view('admin.users.index', $data);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->roles->all(),
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'managers' => $this->repository->managers(),
            'statuses' => UserStatus::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->input('roles', []));

        return redirect()
            ->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'User created successfully.']);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'department', 'reportingManager', 'subordinates']);

        return view('admin.users.show', [
            'user' => $user,
            'loginLogs' => $user->loginLogs()->latest()->limit(10)->get(),
            'activities' => $user->activities()->with('causer')->latest()->limit(10)->get(),
        ]);
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles->all(),
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'managers' => $this->repository->managers(),
            'statuses' => UserStatus::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->update($user, $request->validated(), $request->input('roles', []));

        return redirect()
            ->route('admin.users.show', $user)
            ->with('toast', ['type' => 'success', 'message' => 'User updated successfully.']);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->delete($user);

        return back()->with('toast', ['type' => 'success', 'message' => 'User moved to trash.']);
    }

    public function restore(int $id): RedirectResponse
    {
        $user = $this->repository->find($id, true);

        abort_if(! $user || ! $user->trashed(), 404);

        $this->authorize('restore', $user);

        $this->service->restore($user);

        return back()->with('toast', ['type' => 'success', 'message' => 'User restored successfully.']);
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $user = $this->repository->find($id, true);

        abort_if(! $user, 404);

        $this->authorize('forceDelete', $user);

        $this->service->forceDelete($user);

        return back()->with('toast', ['type' => 'success', 'message' => 'User permanently deleted.']);
    }

    public function changeStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:active,inactive,suspended']]);

        $ability = $request->input('status') === UserStatus::Suspended->value ? 'suspend' : 'activate';
        $this->authorize($ability, $user);

        $this->service->changeStatus($user, $request->string('status'));

        return back()->with('toast', ['type' => 'success', 'message' => 'User status updated to '.ucfirst($request->string('status')).'.']);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'string'],
            'action' => ['required', 'in:activate,suspend,inactive,delete'],
        ]);

        $ids = collect(explode(',', $request->string('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $message = match ($request->string('action')) {
            'delete' => $this->service->bulkDelete($ids).' user(s) moved to trash.',
            default => $this->service->bulkStatus($ids, $request->string('action')).' user(s) updated.',
        };

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function import(): View
    {
        return view('admin.users.import', [
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'roles' => $this->roles->all(),
        ]);
    }

    public function importStore(ImportUsersRequest $request): RedirectResponse
    {
        $import = new UsersImport;
        Excel::import($import, $request->file('file'));

        $results = $import->results();

        $this->audit->log('Users imported', 'user', properties: ['results' => $results]);

        return redirect()
            ->route('admin.users.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Import complete: {$results['created']} created, {$results['updated']} updated, {$results['skipped']} skipped.",
            ]);
    }

    public function export(Request $request)
    {
        $this->authorize('export', User::class);

        $filters = $request->only(['search', 'status', 'department_id', 'role']);

        return Excel::download(new UsersExport($filters), 'users-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('export', User::class);

        $filters = $request->only(['search', 'status', 'department_id', 'role']);

        return app(UsersPdfExport::class, ['filters' => $filters])->download('users-'.now()->format('Y-m-d').'.pdf');
    }

    public function loginLogs(User $user): View
    {
        $logs = $user->loginLogs()->latest()->paginate(25);

        return view('admin.users.login-logs', compact('user', 'logs'));
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'department_id', 'role', 'trashed']);

        $query = $this->repository->query($filters);

        $total = (clone $query)->count();

        $rows = $query
            ->skip($request->integer('start', 0))
            ->take($request->integer('length', 15))
            ->get();

        $me = auth()->user();

        return response()->json([
            'draw' => $request->integer('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows->map(function (User $user) use ($me) {
                $avatar = $user->avatar_url
                    ? '<img src="'.$user->avatar_url.'" class="avatar-sm rounded-circle" alt="">'
                    : '<span class="avatar-fallback sm">'.e(initials($user->name)).'</span>';

                $nameLink = '<a href="'.route('admin.users.show', $user).'" class="fw-500 text-decoration-none">'.e($user->name).'</a>';
                $meta = '<div class="fs-xs text-muted">'.e($user->email ?? '').'</div>';

                $status = $user->trashed()
                    ? '<span class="badge badge-soft-danger"><i class="bi bi-trash me-1"></i>Trashed</span>'
                    : '<span class="badge badge-soft-'.$user->status->color().'">'.e($user->status->label()).'</span>';

                $roles = $user->roles->map(fn ($role) => '<span class="badge badge-soft-primary">'.e($role->name).'</span>')->implode(' ');

                $actions = '<div class="d-flex justify-content-end gap-1">';

                if ($user->trashed()) {
                    if ($me->can('users.restore')) {
                        $actions .= '<form method="POST" action="'.route('admin.users.restore', $user->id).'" class="d-inline">'.csrf_field().'
                            <button class="btn btn-icon btn-soft-primary btn-sm" data-confirm data-confirm-title="Restore user?" data-confirm-text="This user will be restored." data-confirm-confirm-text="Yes, restore" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button></form>';
                    }
                    if ($me->can('users.force-delete')) {
                        $actions .= '<form method="POST" action="'.route('admin.users.force-delete', $user->id).'" class="d-inline">'.csrf_field().method_field('DELETE').'
                            <button class="btn btn-icon btn-soft-danger btn-sm" data-confirm data-confirm-title="Delete permanently?" data-confirm-text="This cannot be undone." data-confirm-confirm-text="Yes, delete" title="Delete forever"><i class="bi bi-trash"></i></button></form>';
                    }
                } else {
                    $actions .= '<a class="btn btn-icon btn-soft-secondary btn-sm" href="'.route('admin.users.show', $user).'" title="View"><i class="bi bi-eye"></i></a>';
                    if ($me->can('users.edit')) {
                        $actions .= '<a class="btn btn-icon btn-soft-primary btn-sm" href="'.route('admin.users.edit', $user).'" title="Edit"><i class="bi bi-pencil"></i></a>';
                    }
                    if ($me->can('users.delete') && $user->id !== $me->id && ! $user->isSuperAdmin()) {
                        $actions .= '<form method="POST" action="'.route('admin.users.destroy', $user).'" class="d-inline">'.csrf_field().method_field('DELETE').'
                            <button class="btn btn-icon btn-soft-danger btn-sm" data-confirm data-confirm-title="Delete user?" data-confirm-text="'.$user->name.' will be moved to trash." data-confirm-confirm-text="Yes, delete" title="Delete"><i class="bi bi-trash"></i></button></form>';
                    }
                }

                $actions .= '</div>';

                return [
                    'DT_RowId' => 'user-'.$user->id,
                    'avatar' => $avatar,
                    'employee_id' => e($user->employee_id ?? '—'),
                    'name' => $nameLink.$meta,
                    'roles' => $roles ?: '<span class="text-muted">—</span>',
                    'department' => e($user->department?->name ?? ($user->department_label ?? '—')),
                    'status' => $status,
                    'last_login' => $user->last_login_at ? format_datetime($user->last_login_at) : '<span class="text-muted">Never</span>',
                    'joined' => format_date($user->created_at),
                    'actions' => $actions,
                ];
            }),
        ]);
    }
}
