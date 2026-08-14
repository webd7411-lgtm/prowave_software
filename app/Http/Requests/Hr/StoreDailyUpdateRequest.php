<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('daily_reports.create');
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'tasks_completed' => ['nullable', 'string', 'max:4000'],
            'work_description' => ['nullable', 'string', 'max:4000'],
            'problems' => ['nullable', 'string', 'max:4000'],
            'hours_worked' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'tomorrow_plan' => ['nullable', 'string', 'max:4000'],
            'additional_notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
