<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectTask, ProjectMilestone};
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('projects.view');

        $projects = Project::orderBy('name')->get();
        $selectedProjectId = $request->get('project_id');

        $taskQuery = ProjectTask::with(['project', 'assignedTo']);
        $milestoneQuery = ProjectMilestone::with('project');

        if ($selectedProjectId) {
            $taskQuery->where('project_id', $selectedProjectId);
            $milestoneQuery->where('project_id', $selectedProjectId);
        }

        $tasks = $taskQuery->whereNotNull('deadline')->get();
        $milestones = $milestoneQuery->whereNotNull('deadline')->get();

        $events = [];

        foreach ($tasks as $task) {
            $events[] = [
                'id' => 'task_' . $task->id,
                'title' => '[Task] ' . $task->title,
                'start' => $task->deadline->format('Y-m-d'),
                'url' => route('projects.tasks.show', $task),
                'className' => match ($task->priority) {
                    'Urgent' => 'bg-danger text-white',
                    'High' => 'bg-warning text-dark',
                    default => 'bg-primary text-white',
                },
                'type' => 'Task',
                'status' => $task->status,
                'project_name' => $task->project->name,
            ];
        }

        foreach ($milestones as $ms) {
            $events[] = [
                'id' => 'ms_' . $ms->id,
                'title' => '[Milestone] ' . $ms->name,
                'start' => $ms->deadline->format('Y-m-d'),
                'url' => route('projects.show', $ms->project_id),
                'className' => 'bg-success text-white',
                'type' => 'Milestone',
                'status' => $ms->status,
                'project_name' => $ms->project->name,
            ];
        }

        return view('projects.calendar', compact('events', 'projects', 'selectedProjectId'));
    }
}
