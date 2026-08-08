<?php

namespace App\Imports;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected array $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));

            if (! $email) {
                $this->results['skipped']++;

                continue;
            }

            $user = User::where('email', $email)->first();

            $data = [
                'name' => $row['name'] ?? 'Unknown',
                'phone' => $row['phone'] ?? null,
                'username' => $row['username'] ?? null,
                'employee_id' => $row['employee_id'] ?? null,
                'designation' => $row['designation'] ?? null,
                'status' => in_array(($row['status'] ?? ''), ['active', 'inactive', 'suspended'], true)
                    ? $row['status']
                    : UserStatus::Active->value,
            ];

            $department = $row['department'] ?? null;
            if ($department) {
                $dept = Department::where('name', $department)->orWhere('code', $department)->first();
                $data['department_id'] = $dept?->id;
            }

            if ($user) {
                $user->update($data);
                $this->results['updated']++;
            } else {
                $data['email'] = $email;
                $data['password'] = Hash::make('Password@'.Str::random(6));
                $data['username'] = $data['username'] ?? Str::slug($data['name'], '_');
                $data['is_active'] = $data['status'] === UserStatus::Active->value;

                $user = User::create($data);
                $this->results['created']++;
            }

            if (! empty($row['role'])) {
                $user->syncRoles([$row['role']]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function results(): array
    {
        return $this->results;
    }
}
