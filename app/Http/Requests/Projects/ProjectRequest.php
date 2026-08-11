<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'project_manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'project_type' => 'required|string|in:Website,Web Application,Mobile Application,Desktop Software,ERP,CRM,E-commerce,Maintenance,SEO,Digital Marketing,Other',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|string|in:Low,Medium,High,Urgent',
            'status' => 'required|string|in:Planning,Not Started,In Progress,On Hold,Completed,Cancelled,Overdue',
            'budget' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // Optional initial creation inputs
            'team' => 'nullable|array',
            'team.*.user_id' => 'required_with:team|exists:users,id',
            'team.*.role' => 'required_with:team|string',
            'team.*.hours_allocation' => 'nullable|numeric|min:0',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required_with:milestones|string|max:255',
            'milestones.*.deadline' => 'nullable|date',
            'tasks' => 'nullable|array',
            'tasks.*.title' => 'required_with:tasks|string|max:255',
            'tasks.*.assigned_to' => 'nullable|exists:users,id',
            'tasks.*.priority' => 'nullable|string',
            'tasks.*.deadline' => 'nullable|date',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|max:20480', // 20MB max per document
        ];
    }
}
