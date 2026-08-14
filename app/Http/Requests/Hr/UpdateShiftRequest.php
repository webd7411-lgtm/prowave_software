<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends StoreShiftRequest
{
    public function rules(): array
    {
        $id = $this->route('shift');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('shifts', 'code')->ignore($id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'grace_period' => ['nullable', 'integer', 'min:0', 'max:180'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:300'],
        ];
    }
}
