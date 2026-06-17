<?php

namespace Modules\Notifications\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Entities\Notification;

class NotificationController extends Controller
{
    /**
     * نمایش لیست اعلان‌های کاربر جاری.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $filter = $request->get('filter', 'all'); // all, unread, read
        
        $query = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->orderByDesc('created_at')->paginate(15);

        return view('notifications::user.index', compact('notifications', 'filter'));
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
            ->whereNull('read_at')
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
}
