<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('tasks.create') || $this->user()->can('tasks.assign');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'on_hold', 'completed', 'cancelled', 'overdue'])],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
