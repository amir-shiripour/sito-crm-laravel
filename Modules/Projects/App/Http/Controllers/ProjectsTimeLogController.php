<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Http\Models\ProjectTimeLog;

class ProjectsTimeLogController extends Controller
{

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $logs = $project->timeLogs()
            ->with(['user', 'task'])
            ->latest('started_at')
            ->get();

        return response()->json($logs);
    }


    public function start(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('manageTasks', $project);

        $userId = auth()->id();
        $activeLog = ProjectTimeLog::where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();

        if ($activeLog) {
            $diff = max(1, (int)now()->diffInMinutes($activeLog->started_at));
            $activeLog->update([
                'ended_at' => now(),
                'duration_minutes' => $diff,
            ]);
        }

        $log = ProjectTimeLog::create([
            'project_id' => $project->id,
            'task_id' => $task->id,
            'user_id' => $userId,
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تایمر با موفقیت آغاز شد.',
            'log' => $log->load(['user', 'task']),
        ]);
    }

    public function stop(Request $request, Project $project, ProjectTask $task, ProjectTimeLog $timeLog)
    {
        $this->authorize('manageTasks', $project);

        if (!$timeLog->isRunning()) {
            return response()->json(['success' => false, 'message' => 'این لاگ قبلاً متوقف شده است.'], 422);
        }

        $duration = max(1, (int)now()->diffInMinutes($timeLog->started_at));
        $timeLog->update([
            'ended_at' => now(),
            'duration_minutes' => $duration,
            'note' => $request->input('note', $timeLog->note),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تایمر متوقف و زمان کار ثبت شد.',
            'log' => $timeLog->fresh(['user', 'task']),
            'task_total_time' => $task->fresh()->formattedTotalTime(),
        ]);
    }

    public function storeManual(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('manageTasks', $project);

        $validated = $request->validate([
            'duration_minutes' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        $startedAt = !empty($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : now()->subMinutes($validated['duration_minutes']);
        $endedAt = (clone $startedAt)->addMinutes($validated['duration_minutes']);

        $log = ProjectTimeLog::create([
            'project_id' => $project->id,
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_minutes' => $validated['duration_minutes'],
            'note' => $validated['note'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'زمان کار با موفقیت افزوده شد.',
                'log' => $log->load(['user', 'task']),
                'task_total_time' => $task->fresh()->formattedTotalTime(),
            ], 201);
        }

        return back()->with('success', 'زمان با موفقیت ثبت شد.');
    }

    public function destroy(Project $project, ProjectTimeLog $timeLog)
    {
        $this->authorize('manageTasks', $project);

        $timeLog->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'لاگ زمان حذف شد.');
    }
}
