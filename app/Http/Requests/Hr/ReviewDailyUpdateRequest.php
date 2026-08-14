<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDailyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('daily_reports.review');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['reviewed', 'approved', 'needs_revision'])],
            'review_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
