<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectPhase;

class ProjectsPhaseController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('managePhases', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:30',
        ]);

        $maxSort = (int) $project->phases()->max('sort_order') + 1;

        $phase = $project->phases()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'sort_order' => $maxSort,
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'project.updated',
            subject: "ایجاد فاز جدید «{$phase->name}» در پروژه",
            userId: auth()->id()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "فاز «{$phase->name}» با موفقیت ایجاد شد.",
                'phase' => $phase,
            ], 201);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "فاز «{$phase->name}» با موفقیت ایجاد شد.");
    }

    public function update(Request $request, Project $project, ProjectPhase $phase)
    {
        $this->authorize('managePhases', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:30',
        ]);

        $oldName = $phase->name;
        $phase->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $phase->description,
            'color' => $validated['color'] ?? $phase->color,
        ]);

        $project->tasks()->where('phase_id', $phase->id)->update(['group_name' => $phase->name]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'project.updated',
            subject: "ویرایش عنوان فاز «{$oldName}» به «{$phase->name}»",
            userId: auth()->id()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "فاز «{$phase->name}» با موفقیت به‌روزرسانی شد.",
                'phase' => $phase,
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "فاز با موفقیت ویرایش شد.");
    }

    public function destroy(Project $project, ProjectPhase $phase)
    {
        $this->authorize('deletePhases', $project);

        $phaseName = $phase->name;
        $project->tasks()->where('phase_id', $phase->id)->update([
            'phase_id' => null,
            'group_name' => null,
        ]);

        $phase->delete();

        ProjectActivity::log(
            projectId: $project->id,
            action: 'project.updated',
            subject: "حذف فاز «{$phaseName}» از پروژه",
            userId: auth()->id()
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "فاز «{$phaseName}» حذف شد و کارهای آن به وظایف عمومی منتقل گردیدند.",
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "فاز «{$phaseName}» با موفقیت حذف شد.");
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $this->authorize('deletePhases', $project);

        if ($project->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'این پروژه لغو شده است و امکان حذف فاز وجود ندارد.'], 422);
            }
            return back()->with('error', 'این پروژه لغو شده است.');
        }

        $validated = $request->validate([
            'phase_ids' => 'required|array',
            'phase_ids.*' => 'integer|exists:projects_phases,id',
            'delete_tasks' => 'nullable|boolean',
        ]);

        $phases = $project->phases()->whereIn('id', $validated['phase_ids'])->get();
        $count = $phases->count();

        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'هیچ فازی برای حذف یافت نشد.'], 404);
        }

        $names = $phases->pluck('name')->implode('، ');

        if (!empty($validated['delete_tasks'])) {
            $tasks = $project->tasks()->whereIn('phase_id', $phases->pluck('id'))->get();
            foreach ($tasks as $task) {
                $task->checklistItems()->delete();
                $task->timeLogs()->delete();
                $task->comments()->delete();
                $task->delete();
            }
        } else {
            $project->tasks()->whereIn('phase_id', $phases->pluck('id'))->update([
                'phase_id' => null,
                'group_name' => null,
            ]);
        }

        $project->phases()->whereIn('id', $phases->pluck('id'))->delete();

        ProjectActivity::log(
            projectId: $project->id,
            action: 'project.updated',
            subject: "حذف گروهی {$count} فاز («{$names}») از پروژه",
            userId: auth()->id()
        );

        $project->refreshProgress();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "تعداد {$count} فاز با موفقیت حذف شد.",
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "تعداد {$count} فاز با موفقیت حذف شد.");
    }
}
