<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.manage') || $this->user()->can('attendance.create');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'half_day', 'on_leave', 'late'])],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}

