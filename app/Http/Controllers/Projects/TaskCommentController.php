<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{ProjectTask, TaskComment, ProjectActivity};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TaskCommentController extends Controller
{
    public function store(Request $request, ProjectTask $task): RedirectResponse
    {
        $this->authorize('task_comments.create');

        $validated = $request->validate([
            'comment' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('task_comments/' . $task->id, 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        ProjectActivity::log($task->project_id, 'Comment added', "Comment added on task '{$task->title}'.", 'TaskComment', $comment->id);

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Comment posted successfully.']);
    }

    public function destroy(TaskComment $comment): RedirectResponse
    {
        $this->authorize('task_comments.delete');

        if ($comment->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['Super Admin', 'Administrator', 'Project Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $comment->delete();

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Comment removed.']);
    }
}
