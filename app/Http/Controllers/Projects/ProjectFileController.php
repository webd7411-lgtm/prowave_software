<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectFile, ProjectActivity};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectFileController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('project_files.upload');

        $validated = $request->validate([
            'file_type' => 'required|string|in:Requirements,Design Files,Client Documents,Contracts,Screenshots,Source Documents,Other Files',
            'file' => 'required|file|max:30720', // 30MB
        ]);

        $file = $request->file('file');
        $path = $file->store('project_files/' . $project->id, 'public');

        $projectFile = ProjectFile::create([
            'project_id' => $project->id,
            'task_id' => $request->get('task_id'),
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $validated['file_type'],
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        ProjectActivity::log($project->id, 'File uploaded', "Document '{$projectFile->name}' was uploaded.", 'ProjectFile', $projectFile->id);

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'File uploaded successfully.']);
    }

    public function download(ProjectFile $file): StreamedResponse
    {
        $this->authorize('project_files.view');

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('public')->download($file->file_path, $file->name);
    }

    public function destroy(ProjectFile $file): RedirectResponse
    {
        $this->authorize('project_files.delete');

        $projectId = $file->project_id;
        $name = $file->name;

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        ProjectActivity::log($projectId, 'File deleted', "File '{$name}' was deleted.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'File deleted.']);
    }
}
