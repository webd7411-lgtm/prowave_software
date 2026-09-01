<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectTask};
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

        if ($selectedProjectId) {
            $taskQuery->where('project_id', $selectedProjectId);
        }

        $tasks = $taskQuery->whereNotNull('deadline')->get();

        $projectQuery = Project::whereNotNull('deadline');
        if ($selectedProjectId) {
            $projectQuery->where('id', $selectedProjectId);
        }
        $projectsWithDeadlines = $projectQuery->get();

        $events = [];

        foreach ($projectsWithDeadlines as $proj) {
            $events[] = [
                'id' => 'proj_' . $proj->id,
                'title' => '[Project] ' . $proj->name,
                'start' => $proj->deadline->format('Y-m-d'),
                'url' => route('projects.show', $proj),
                'className' => 'bg-success text-white fw-bold',
                'type' => 'Project',
                'status' => $proj->status,
                'project_name' => $proj->name,
            ];
        }

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

        return view('projects.calendar', compact('events', 'projects', 'selectedProjectId'));
    }
}
