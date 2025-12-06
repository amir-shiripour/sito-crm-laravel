<?php

namespace Modules\Reminders\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Reminders\Entities\Reminder;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('reminders.view') && ! $user->can('reminders.view.own')) {
            abort(403);
        }

        $query = Reminder::query()
            ->where('user_id', $user->id)
            ->orderBy('remind_at');

        if ($onlyOpen = $request->boolean('only_open', true)) {
            $query->where('is_sent', false);
        }

        $reminders = $query->limit(100)->get();

        return view('reminders::user.reminders.index', compact('reminders'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('reminders.create')) {
            abort(403);
        }

        $channels = array_keys(config('reminders.channels', []));

        $data = $request->validate([
            'related_type' => ['required', 'string', 'max:100'],
            'related_id'   => ['required', 'integer'],
            'remind_at'    => ['required', 'date'],
            'channel'      => ['nullable', 'string', Rule::in($channels)],
            'message'      => ['nullable', 'string', 'max:255'],
        ]);

        $reminder = Reminder::create([
            'user_id'      => $user->id,
            'related_type' => $data['related_type'],
            'related_id'   => $data['related_id'],
            'remind_at'    => $data['remind_at'],
            'channel'      => $data['channel'] ?? config('reminders.default_channel', 'IN_APP'),
            'message'      => $data['message'] ?? null,
        ]);

        if ($reminder) {
            return back()->with('status', 'یادآوری ثبت شد.');
        } else {
            return back()->with('status', 'خطا در ثبت یادآوری.');
        }
    }


    public function destroy(Reminder $reminder)
    {
        $user = Auth::user();

        if (! $user->can('reminders.delete')) {
            abort(403);
        }

        if ($reminder->user_id !== $user->id && ! $user->can('reminders.manage')) {
            abort(403);
        }

        $reminder->delete();

        return back()->with('status', 'یادآوری حذف شد.');
    }
}
