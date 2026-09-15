<?php

namespace Modules\Notifications\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Entities\Notification;

class NotificationController extends Controller
{
    /**
     * نمایش لیست اعلان‌های کاربر جاری با فیلترها و جستجو.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $statusFilter   = $request->get('filter', 'all'); // all, unread, read
        $categoryFilter = $request->get('category', 'all');
        $priorityFilter = $request->get('priority', 'all');
        $search         = $request->get('search');
        
        $baseQuery = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);

        // آمار سریع
        $unreadCount = (clone $baseQuery)->unread()->count();
        $readCount   = (clone $baseQuery)->read()->count();
        $totalCount  = (clone $baseQuery)->count();

        $query = clone $baseQuery;

        if ($statusFilter === 'unread') {
            $query->unread();
        } elseif ($statusFilter === 'read') {
            $query->read();
        }

        if ($categoryFilter && $categoryFilter !== 'all') {
            $query->category($categoryFilter);
        }

        if ($priorityFilter && $priorityFilter !== 'all') {
            $query->priority($priorityFilter);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        $notifications = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $categories = config('notifications.categories', []);
        $priorities = config('notifications.priorities', []);

        return view('notifications::user.index', compact(
            'notifications',
            'statusFilter',
            'categoryFilter',
            'priorityFilter',
            'search',
            'unreadCount',
            'readCount',
            'totalCount',
            'categories',
            'priorities'
        ));
    }

    /**
     * علامت‌گذاری یک اعلان به عنوان خوانده شده.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'اعلان به عنوان خوانده شده علامت‌گذاری شد.'
            ]);
        }

        return back()->with('success', 'اعلان خوانده شد.');
    }

    /**
     * علامت‌گذاری تمام اعلان‌ها به عنوان خوانده شده.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تمام اعلان‌ها به عنوان خوانده شده علامت‌گذاری شدند.'
            ]);
        }

        return back()->with('success', 'تمام اعلان‌ها خوانده شدند.');
    }

    /**
     * علامت‌گذاری دسته‌جمعی اعلان‌های انتخاب‌شده به عنوان خوانده شده.
     */
    public function bulkMarkAsRead(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'string',
        ]);

        $user = Auth::user();

        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereIn('id', $request->ids)
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => "تعداد {$count} اعلان به عنوان خوانده شده ثبت شدند."
            ]);
        }

        return back()->with('success', "تعداد {$count} اعلان به عنوان خوانده شده ثبت شدند.");
    }

    /**
     * حذف یک اعلان.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'اعلان با موفقیت حذف شد.'
            ]);
        }

        return back()->with('success', 'اعلان حذف شد.');
    }

    /**
     * حذف دسته‌جمعی اعلان‌های انتخاب‌شده.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'string',
        ]);

        $user = Auth::user();

        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereIn('id', $request->ids)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => "تعداد {$count} اعلان با موفقیت حذف شدند."
            ]);
        }

        return back()->with('success', "تعداد {$count} اعلان حذف شدند.");
    }

    /**
     * متد سبک برای بررسی تعداد اعلان‌های خوانده نشده (مناسب Polling زنده).
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * دریافت آخرین اعلان‌ها جهت به‌روزرسانی زنده تاپ‌بار بدون رفرش.
     */
    public function latest(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'items' => []]);
        }

        $unreadCount = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->unread()
            ->count();

        $items = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderByDesc('created_at')
            ->take(6)
            ->get()
            ->map(function ($n) {
                return [
                    'id'          => $n->id,
                    'title'       => $n->title,
                    'message'     => $n->message,
                    'action_url'  => $n->action_url,
                    'category'    => $n->category,
                    'priority'    => $n->priority,
                    'severity'    => $n->severity,
                    'is_unread'   => is_null($n->read_at),
                    'created_at'  => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'unreadCount' => $unreadCount,
            'items'       => $items,
        ]);
    }
}
