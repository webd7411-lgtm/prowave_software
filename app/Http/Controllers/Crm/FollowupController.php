<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\{LeadFollowup, User};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class FollowupController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('followups.view');

        $query = LeadFollowup::with(['lead', 'client', 'assignee'])->orderBy('follow_up_date');

        if ($request->filled('scope')) {
            $query->whereDate('follow_up_date', $request->scope === 'overdue' ? '<' : '=', today());
        }

        return view('crm.followups.index', [
            'followups' => $query->paginate(20),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, LeadFollowup $followup): RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,completed,cancelled,missed']);

        $followup->update($request->only('status'));

        return back()->with('toast', ['type' => 'success', 'message' => 'Follow-up updated.']);
    }
}
