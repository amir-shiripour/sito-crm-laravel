<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectDocument;
use Modules\Projects\App\Http\Requests\StoreProjectDocumentRequest;

class ProjectsDocumentController extends Controller
{
    use FileUploadTrait;

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $allowedCategories = $project->allowedDocumentCategoriesFor(auth()->id());

        $query = $project->documents()->with('uploader')->latest();
        $query->where(function($q) use ($allowedCategories) {
            $q->whereNull('category')
              ->orWhere('category', '')
              ->orWhereIn('category', $allowedCategories)
              ->orWhere('uploaded_by', auth()->id());
        });

        $documents = $query->paginate(20);

        return view('projects::documents.index', compact('project', 'documents'));
    }

    public function create(Project $project)
    {
        $this->authorize('manageDocuments', $project);

        return view('projects::documents.create', compact('project'));
    }

    public function store(StoreProjectDocumentRequest $request, Project $project)
    {
        $this->authorize('uploadDocument', $project);

        $category = $request->input('category');
        if ($category && !$project->userCanViewDocumentCategory(auth()->id(), $category)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'شما مجاز به بارگذاری سند در این دسته‌بندی نیستید.',
                    'errors' => ['category' => ['شما مجاز به بارگذاری سند در این دسته‌بندی نیستید.']]
                ], 422);
            }
            return back()->withErrors(['category' => 'شما مجاز به بارگذاری سند در این دسته‌بندی نیستید.']);
        }

        $data = $request->safe()->only(['type', 'category', 'title', 'description', 'link_url']);
        $data['project_id'] = $project->id;
        $data['uploaded_by'] = auth()->id();

        if ($request->input('type') === 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            $path = $this->uploadFile($file, 'projects/documents/' . $project->id, 'public');

            $data['file_path'] = $path;
            $data['file_original_name'] = $originalName;
            $data['file_mime'] = Storage::disk('public')->exists($path) ? Storage::disk('public')->mimeType($path) : $file->getClientMimeType();
            $data['file_size'] = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : $file->getSize();
        }

        $document = ProjectDocument::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($document->load('uploader'), 201);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'documents'])
            ->with('success', 'سند با موفقیت ثبت شد.');
    }

    public function show(Project $project, ProjectDocument $document)
    {
        $this->authorize('view', $project);

        if (!$project->userCanViewDocumentCategory(auth()->id(), $document->category) && $document->uploaded_by !== auth()->id()) {
            abort(403, 'شما دسترسی لازم برای مشاهده اسناد این دسته‌بندی را ندارید.');
        }

        $document->load('uploader');

        return view('projects::documents.show', compact('project', 'document'));
    }

    public function download(Project $project, ProjectDocument $document)
    {
        $this->authorize('view', $project);

        if (!$project->userCanViewDocumentCategory(auth()->id(), $document->category) && $document->uploaded_by !== auth()->id()) {
            abort(403, 'شما دسترسی لازم برای دانلود اسناد این دسته‌بندی را ندارید.');
        }

        if ($document->type !== 'file' || !$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'فایل مورد نظر یافت نشد.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_original_name ?? $document->title);
    }

    public function destroy(Project $project, ProjectDocument $document)
    {
        $this->authorize('deleteDocument', $project);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'سند با موفقیت حذف شد.');
    }
}
