<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Please upload a valid Excel (.xlsx, .xls) or CSV file.',
            'file.max' => 'The import file must not exceed 4 MB.',
        ];
    }
}
