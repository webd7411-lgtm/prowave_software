<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employee_documents.upload');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['cnic', 'contract', 'joining_letter', 'experience_letter', 'certificate', 'other'])],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,csv'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Only PDF, Word, Excel, image, text, and CSV files are allowed.',
        ];
    }
}
