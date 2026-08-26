<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronTask;
use App\Models\CronTaskLog;
use App\Services\Cron\CronService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CronManagementController extends Controller
{
    /**
     * Display the Cron Control Center dashboard.
     */
    public function index(Request $request): View
    {
        // Ensure default tasks are seeded
        CronService::seedDefaultTasks();

        $heartbeat = CronService::getHeartbeatInfo();
        $permissions = CronService::checkPermissions();

        // Metrics
        $totalTasks = CronTask::count();
        $activeTasks = CronTask::where('is_active', true)->count();
        $failed24h = CronTaskLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $avgDuration = (int) round(CronTaskLog::where('status', 'success')
            ->where('created_at', '>=', now()->subDays(7))
            ->avg('duration_ms') ?? 0);

        // Modules for tabs
        $modules = CronTask::select('module')->distinct()->pluck('module')->filter()->values();

        // Query tasks
        $query = CronTask::with('latestLog');

        if ($request->filled('module') && $request->module !== 'all') {
            $query->where('module', $request->module);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'failed') {
                $query->where('last_status', 'failed');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('command', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('module')->orderBy('name')->get();

        return view('admin.cron.index', compact(
            'heartbeat',
            'permissions',
            'totalTasks',
            'activeTasks',
            'failed24h',
            'avgDuration',
            'modules',
            'tasks'
        ));
    }

    /**
     * Toggle task active state via AJAX.
     */
    public function toggle(CronTask $task): JsonResponse
    {
        $task->is_active = !$task->is_active;
        $task->save();

        return response()->json([
            'success' => true,
            'is_active' => $task->is_active,
            'message' => $task->is_active ? "تسک «{$task->name}» فعال شد." : "تسک «{$task->name}» غیرفعال شد.",
        ]);
    }

    /**
     * Store a new custom cron task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'command' => 'required|string|max:255',
            'module' => 'nullable|string|max:64',
            'expression' => 'required|string|max:64',
            'description' => 'nullable|string|max:1000',
            'prevent_overlap' => 'nullable|boolean',
            'run_in_background' => 'nullable|boolean',
        ]);

        $validated['module'] = $validated['module'] ?: 'Custom';
        $validated['prevent_overlap'] = $request->boolean('prevent_overlap', true);
        $validated['run_in_background'] = $request->boolean('run_in_background', false);
        $validated['is_active'] = true;
        $validated['is_system'] = false;
        $validated['next_run_at'] = CronService::calculateNextRun($validated['expression']);

        $task = CronTask::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تسک جدید با موفقیت به زمان‌بندی سیستم افزوده شد.',
                'task' => $task,
            ]);
        }

        return redirect()->route('admin.cron.index')->with('success', 'تسک جدید با موفقیت به زمان‌بندی سیستم افزوده شد.');
    }

    /**
     * Update task configuration.
     */
    public function update(Request $request, CronTask $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expression' => 'required|string|max:64',
            'description' => 'nullable|string|max:1000',
            'prevent_overlap' => 'nullable|boolean',
            'run_in_background' => 'nullable|boolean',
        ]);

        $validated['prevent_overlap'] = $request->boolean('prevent_overlap', true);
        $validated['run_in_background'] = $request->boolean('run_in_background', false);
        $validated['next_run_at'] = CronService::calculateNextRun($validated['expression']);

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "تسک «{$task->name}» با موفقیت به‌روزرسانی شد.",
                'task' => $task,
            ]);
        }

        return redirect()->route('admin.cron.index')->with('success', "تنظیمات تسک «{$task->name}» به‌روزرسانی شد.");
    }

    /**
     * Delete a custom task.
     */
    public function destroy(CronTask $task)
    {
        if ($task->is_system) {
            return back()->with('error', 'تسک‌های پیش‌فرض و سیستمی قابل حذف نیستند؛ اما می‌توانید آن‌ها را غیرفعال کنید.');
        }

        $name = $task->name;
        $task->delete();

        return redirect()->route('admin.cron.index')->with('success', "تسک «{$name}» با موفقیت حذف شد.");
    }

    /**
     * Manually trigger a task execution immediately.
     */
    public function runManual(CronTask $task): JsonResponse
    {
        $userName = auth()->user() ? auth()->user()->name : 'مدیر سیستم';
        $result = CronService::runTask($task, "manual ({$userName})");

        return response()->json([
            'success' => $result['success'],
            'status' => $result['status'],
            'duration_ms' => $result['duration_ms'],
            'output' => $result['output'],
            'error' => $result['error'],
            'last_run_jalali' => $task->jalali_last_run,
            'relative_last_run' => $task->relative_last_run,
            'next_run_jalali' => $task->jalali_next_run,
            'message' => $result['success'] 
                ? "دستور با موفقیت در {$result['duration_ms']} میلی‌ثانیه اجرا شد." 
                : "اجرای دستور با خطا مواجه گردید: {$result['error']}",
        ]);
    }

    /**
     * Get paginated logs for a specific task.
     */
    public function logs(Request $request, CronTask $task): JsonResponse
    {
        $logs = $task->logs()
            ->take(30)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'status' => $log->status,
                    'duration_ms' => $log->duration_ms,
                    'started_at' => $log->jalali_created_at,
                    'relative_time' => $log->relative_time,
                    'triggered_by' => $log->triggered_by === 'system_cron' ? 'کرون خودکار سرور' : $log->triggered_by,
                    'output' => $log->output,
                    'error_message' => $log->error_message,
                ];
            });

        return response()->json([
            'task' => [
                'id' => $task->id,
                'name' => $task->name,
                'command' => $task->command,
                'module' => $task->module,
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Clear task logs.
     */
    public function clearLogs(Request $request, ?CronTask $task = null)
    {
        $days = (int) $request->input('days', 0);

        if ($task) {
            $query = $task->logs();
            if ($days > 0) {
                $query->where('created_at', '<', now()->subDays($days));
            }
            $query->delete();
            return back()->with('success', "لاگ‌های تسک «{$task->name}» پاکسازی شدند.");
        }

        // Global clear
        $query = CronTaskLog::query();
        if ($days > 0) {
            $query->where('created_at', '<', now()->subDays($days));
        }
        $count = $query->delete();

        return back()->with('success', "تعداد {$count} لاگ با موفقیت از پایگاه داده پاکسازی شد.");
    }
}
