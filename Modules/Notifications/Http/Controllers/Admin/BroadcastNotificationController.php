<?php

namespace Modules\Notifications\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Entities\NotificationBroadcast;
use Modules\Notifications\Services\NotificationService;
use Spatie\Permission\Models\Role;

class BroadcastNotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * نمایش تاریخچه اطلاعیه‌های همگانی
     */
    public function index()
    {
        $broadcasts = NotificationBroadcast::with('creator')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('notifications::admin.broadcast.index', compact('broadcasts'));
    }

    /**
     * فرم ارسال اطلاعیه همگانی جدید
     */
    public function create()
    {
        $roles = Role::all();
        $categories = config('notifications.categories', []);
        $priorities = config('notifications.priorities', []);
        $severities = config('notifications.severities', []);

        return view('notifications::admin.broadcast.create', compact(
            'roles',
            'categories',
            'priorities',
            'severities'
        ));
    }

    /**
     * ثبت و ارسال اطلاعیه همگانی
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'message'       => 'required|string',
            'category'      => 'required|string',
            'priority'      => 'required|string',
            'severity'      => 'required|string',
            'target_type'   => 'required|in:all,role,users',
            'target_roles'  => 'nullable|array',
            'target_users'  => 'nullable|array',
            'channels'      => 'required|array|min:1',
            'action_url'    => 'nullable|string|max:500',
        ]);

        // تعیین لیست کاربران هدف
        $recipientsQuery = User::query();

        if ($validated['target_type'] === 'role' && !empty($validated['target_roles'])) {
            $recipientsQuery->whereHas('roles', function ($q) use ($validated) {
                $q->whereIn('name', $validated['target_roles']);
            });
        } elseif ($validated['target_type'] === 'users' && !empty($validated['target_users'])) {
            $recipientsQuery->whereIn('id', $validated['target_users']);
        }

        $recipients = $recipientsQuery->get();
        $recipientsCount = $recipients->count();

        if ($recipientsCount === 0) {
            return back()->withInput()->with('error', 'هیچ کاربری با معیارهای انتخاب شده یافت نشد.');
        }

        // ثبت لاگ اطلاعیه
        $targetValues = match ($validated['target_type']) {
            'role'  => $validated['target_roles'] ?? [],
            'users' => $validated['target_users'] ?? [],
            default => null,
        };

        $broadcast = NotificationBroadcast::create([
            'created_by'       => Auth::id(),
            'title'            => $validated['title'],
            'message'          => $validated['message'],
            'category'         => $validated['category'],
            'priority'         => $validated['priority'],
            'severity'         => $validated['severity'],
            'target_type'      => $validated['target_type'],
            'target_values'    => $targetValues,
            'channels'         => $validated['channels'],
            'action_url'       => $validated['action_url'],
            'recipients_count' => $recipientsCount,
            'sent_at'          => now(),
        ]);

        // ارسال واقعی از طریق سرویس مرکزی
        $sentCount = $this->notificationService->send(
            recipients: $recipients,
            title: $validated['title'],
            message: $validated['message'],
            category: $validated['category'],
            priority: $validated['priority'],
            severity: $validated['severity'],
            actionUrl: $validated['action_url'],
            channels: $validated['channels'],
            options: [
                'broadcast_id' => $broadcast->id,
            ]
        );

        $broadcast->update(['recipients_count' => $sentCount]);

        return redirect()->route('admin.notifications.broadcast.index')
            ->with('success', "اطلاعیه همگانی با موفقیت برای {$sentCount} کاربر ارسال شد.");
    }

    /**
     * حذف لاگ اطلاعیه
     */
    public function destroy($id)
    {
        $broadcast = NotificationBroadcast::findOrFail($id);
        $broadcast->delete();

        return back()->with('success', 'لاگ اطلاعیه با موفقیت حذف شد.');
    }
}
