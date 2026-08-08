<?php

namespace App\Http\Controllers\Hr;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\UploadDocumentRequest;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Services\AuditService;
use App\Services\Hr\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $service,
        protected EmployeeRepository $employees,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $query = EmployeeDocument::query()->with(['employee:id,name,employee_id,avatar']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return view('hr.documents.index', [
            'documents' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['employee_id', 'type']),
            'employees' => $this->employees->employeesOptions(),
            'types' => DocumentType::options(),
        ]);
    }

    public function store(UploadDocumentRequest $request): RedirectResponse
    {
        $document = $this->service->upload($request->file('file'), $request->integer('employee_id'), $request->validated());

        $this->audit->log('Document uploaded', 'hr', $document, event: 'created');

        return back()->with('toast', ['type' => 'success', 'message' => 'Document uploaded successfully.']);
    }

    public function download(EmployeeDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, Str::slug($document->title).'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(EmployeeDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $this->service->delete($document);

        $this->audit->log('Document deleted', 'hr', $document, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Document deleted successfully.']);
    }
}
