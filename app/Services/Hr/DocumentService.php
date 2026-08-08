<?php

namespace App\Services\Hr;

use App\Models\EmployeeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    protected array $allowedMimes = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt', 'csv',
    ];

    public function upload(UploadedFile $file, int $employeeId, array $data): EmployeeDocument
    {
        $path = $file->store('employee-documents/'.$employeeId, 'public');

        return EmployeeDocument::create([
            'employee_id' => $employeeId,
            'uploaded_by' => auth()->id(),
            'title' => $data['title'],
            'type' => $data['type'] ?? 'other',
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $data['description'] ?? null,
        ]);
    }

    public function delete(EmployeeDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    public function allowedMimes(): array
    {
        return $this->allowedMimes;
    }
}
