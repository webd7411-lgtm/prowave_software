<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreShiftRequest;
use App\Http\Requests\Hr\UpdateShiftRequest;
use App\Models\Shift;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request): View
    {
        $this->authorize('shifts.view');

        $query = Shift::query()
            ->withCount('employees')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->boolean('status')));

        return view('hr.shifts.index', [
            'shifts' => $query->orderBy('start_time')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('hr.shifts.create');
    }

    public function store(StoreShiftRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $shift = Shift::create($data);

        $this->audit->log('Shift created', 'hr', $shift, event: 'created');

        return redirect()
            ->route('hr.shifts.index')
            ->with('toast', ['type' => 'success', 'message' => 'Shift created successfully.']);
    }

    public function edit(Shift $shift): View
    {
        return view('hr.shifts.edit', compact('shift'));
    }

    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $shift->update($data);

        $this->audit->log('Shift updated', 'hr', $shift, event: 'updated');

        return redirect()
            ->route('hr.shifts.index')
            ->with('toast', ['type' => 'success', 'message' => 'Shift updated successfully.']);
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->employees()->count() > 0) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot delete a shift that has employees assigned.']);
        }

        $shift->delete();

        $this->audit->log('Shift deleted', 'hr', $shift, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Shift deleted successfully.']);
    }
}
