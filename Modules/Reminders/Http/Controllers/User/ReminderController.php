<?php

namespace Modules\Reminders\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Reminders\Entities\Reminder;
use Modules\Tasks\Entities\Task;
use Modules\FollowUps\Entities\FollowUp;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\ValidationException;


class ReminderController extends Controller
{
    /* ------------ لیست یادآوری‌ها + فیلترها و دسته‌بندی ------------ */

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('reminders.view') && ! $user->can('reminders.view.own')) {
            abort(403);
        }

        // فیلتر وضعیت یادآوری: open / done / canceled / all
        $status = $request->get('status', 'open');

        // فیلتر زمانی: today / week / month / year / custom / all
        $period = $request->get('period', 'today');

        [$from, $to] = $this->resolvePeriod($request, $period);

        $baseQuery = Reminder::query()
            ->visibleForUser($user)
            ->forTasks() // فقط یادآوری‌هایی که روی Task/FollowUp ساخته شده‌اند
            ->with(['task', 'followUp', 'user']);

        // وضعیت
        if ($status !== 'all') {
            $statusMap = [
                'open'     => Reminder::STATUS_OPEN,
                'done'     => Reminder::STATUS_DONE,
                'canceled' => Reminder::STATUS_CANCELED,
            ];

            if (isset($statusMap[$status])) {
                $baseQuery->where('status', $statusMap[$status]);
            }
        }

        // بازه زمانی بر اساس remind_at
        if ($from) {
            $baseQuery->where('remind_at', '>=', $from);
        }
        if ($to) {
            $baseQuery->where('remind_at', '<=', $to);
        }

        // برای اینکه تعداد خیلی زیاد نشود
        $baseReminders = $baseQuery
            ->orderBy('remind_at')
            ->limit(300)
            ->get();

        // دسته‌بندی بر اساس نوع وظیفه (TaskType)
        $taskReminders = $baseReminders->filter(function (Reminder $reminder) {
            $task = $reminder->task;
            return $task && $task->task_type !== Task::TYPE_FOLLOW_UP;
        });

        $followUpReminders = $baseReminders->filter(function (Reminder $reminder) {
            $task = $reminder->task;
            return $task && $task->task_type === Task::TYPE_FOLLOW_UP;
        });

        // مرتب‌سازی داخل هر دسته:
        // 1) بر اساس اولویت Task/FU (CRITICAL > HIGH > MEDIUM > LOW)
        // 2) بر اساس remind_at صعودی
        $taskReminders = $this->sortRemindersByPriorityAndTime($taskReminders);
        $followUpReminders = $this->sortRemindersByPriorityAndTime($followUpReminders);

        // برای فیلترها
        $statusFilterOptions = [
            'open'     => 'باز (در انتظار)',
            'done'     => 'انجام‌شده',
            'canceled' => 'لغو شده',
            'all'      => 'همه',
        ];

        $periodOptions = [
            'today'  => 'امروز',
            'week'   => 'این هفته',
            'month'  => 'این ماه',
            'year'   => 'امسال',
            'custom' => 'بازه دلخواه',
            'all'    => 'بدون فیلتر زمانی',
        ];

        return view('reminders::user.reminders.index', compact(
            'taskReminders',
            'followUpReminders',
            'status',
            'period',
            'statusFilterOptions',
            'periodOptions',
            'from',
            'to'
        ));
    }

    private function sortRemindersByPriorityAndTime($collection)
    {
        $priorityWeight = [
            Task::PRIORITY_CRITICAL => 4,
            Task::PRIORITY_HIGH     => 3,
            Task::PRIORITY_MEDIUM   => 2,
            Task::PRIORITY_LOW      => 1,
        ];

        return $collection->sort(function (Reminder $a, Reminder $b) use ($priorityWeight) {
            $taskA = $a->task;
            $taskB = $b->task;

            $prioA = $priorityWeight[$taskA->priority ?? Task::PRIORITY_MEDIUM] ?? 2;
            $prioB = $priorityWeight[$taskB->priority ?? Task::PRIORITY_MEDIUM] ?? 2;

            // اولویت بیشتر بالاتر
            if ($prioA !== $prioB) {
                return $prioB <=> $prioA;
            }

            // اگر اولویت برابر بود، بر اساس remind_at
            return ($a->remind_at ?? now())->timestamp <=> ($b->remind_at ?? now())->timestamp;
        });
    }

    /**
     * تعیین بازه زمانی بر اساس period + تاریخ‌های دلخواه
     */
    private function resolvePeriod(Request $request, string $period): array
    {
        $from = null;
        $to   = null;

        $now = now();

        switch ($period) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $to   = $now->copy()->endOfDay();
                break;

            case 'week':
                $from = $now->copy()->startOfWeek();
                $to   = $now->copy()->endOfWeek();
                break;

            case 'month':
                $from = $now->copy()->startOfMonth();
                $to   = $now->copy()->endOfMonth();
                break;

            case 'year':
                $from = $now->copy()->startOfYear();
                $to   = $now->copy()->endOfYear();
                break;

            case 'custom':
                // از روی ورودی (میلادی یا شمسی)
                $from = $this->parseDateInput(
                    $request->input('from'),
                    $request->input('from_jalali')
                );
                $to = $this->parseDateInput(
                    $request->input('to'),
                    $request->input('to_jalali'),
                    endOfDay: true
                );
                break;

            case 'all':
            default:
                $from = null;
                $to   = null;
                break;
        }

        return [$from, $to];
    }

    private function parseDateInput(?string $gregorian, ?string $jalali, bool $endOfDay = false): ?Carbon
    {
        if ($jalali) {
            try {
                $j = Jalalian::fromFormat('Y/m/d', trim($jalali));
                $c = $j->toCarbon();
                return $endOfDay ? $c->endOfDay() : $c->startOfDay();
            } catch (\Throwable $e) {
                // نادیده بگیر
            }
        }

        if ($gregorian) {
            try {
                $c = Carbon::parse($gregorian);
                return $endOfDay ? $c->endOfDay() : $c->startOfDay();
            } catch (\Throwable $e) {
                // نادیده بگیر
            }
        }

        return null;
    }

    /* ------------ ایجاد یادآوری ------------ */

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('reminders.create')) {
            abort(403);
        }

        $channels = array_keys(config('reminders.channels', []));

        $data = $request->validate([
            'related_type'        => ['required', 'string', 'max:100'],
            'related_id'          => ['required', 'integer'],
            // دو حالت: یا remind_at میلادی، یا تاریخ جلالی + ساعت
            'remind_at'           => ['nullable', 'date', 'required_without:remind_date_jalali'],
            'remind_date_jalali'  => ['nullable', 'string', 'required_without:remind_at'],
            'remind_time'         => ['nullable', 'string'], // مثل 14:30
            'channel'             => ['nullable', 'string', Rule::in($channels)],
            'message'             => ['nullable', 'string', 'max:255'],
        ]);

        // تعیین زمان نهایی یادآوری
        if (! empty($data['remind_at'])) {
            $remindAt = Carbon::parse($data['remind_at']);
        } else {
            if (empty($data['remind_date_jalali']) || empty($data['remind_time'])) {
                throw ValidationException::withMessages([
                    'remind_date_jalali' => 'تاریخ و ساعت یادآوری الزامی است.',
                ]);
            }

            $jalali = Jalalian::fromFormat('Y/m/d', $data['remind_date_jalali']);
            $remindAt = $jalali->toCarbon()->setTimeFromTimeString($data['remind_time']);
        }

        $reminder = Reminder::create([
            'user_id'      => $user->id,
            'related_type' => $data['related_type'],
            'related_id'   => $data['related_id'],
            'remind_at'    => $remindAt,
            'channel'      => $data['channel'] ?? config('reminders.default_channel', 'IN_APP'),
            'message'      => $data['message'] ?? null,
            'status'       => Reminder::STATUS_OPEN,
            'is_sent'      => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'یادآوری ثبت شد.',
                'reminder' => $reminder,
            ]);
        }

        return back()->with('status', 'یادآوری ثبت شد.');
    }


    /* ------------ فرم ویرایش ------------ */

    public function edit(Reminder $reminder)
    {
        $user = Auth::user();

        if (! $reminder->canBeEditedBy($user)) {
            abort(403);
        }

        $channels = config('reminders.channels', []);

        return view('reminders::user.reminders.edit', compact('reminder', 'channels'));
    }

    /* ------------ به‌روزرسانی ------------ */

    public function update(Request $request, Reminder $reminder)
    {
        $user = Auth::user();

        if (! $reminder->canBeEditedBy($user)) {
            abort(403);
        }

        $channels = array_keys(config('reminders.channels', []));

        $data = $request->validate([
            'remind_at' => ['required', 'date'],
            'channel'   => ['nullable', 'string', Rule::in($channels)],
            'message'   => ['nullable', 'string', 'max:255'],
            'status'    => ['required', Rule::in([
                Reminder::STATUS_OPEN,
                Reminder::STATUS_DONE,
                Reminder::STATUS_CANCELED,
            ])],
        ]);

        $reminder->update([
            'remind_at' => $data['remind_at'],
            'channel'   => $data['channel'] ?? $reminder->channel,
            'message'   => $data['message'] ?? null,
            'status'    => $data['status'],
        ]);

        return redirect()
            ->route('user.reminders.index')
            ->with('status', 'یادآوری به‌روزرسانی شد.');
    }

    /* ------------ تغییر وضعیت تکی ------------ */

    public function updateStatus(Request $request, Reminder $reminder)
    {
        $user = Auth::user();

        if (! $reminder->canChangeStatus($user)) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in([
                Reminder::STATUS_OPEN,
                Reminder::STATUS_DONE,
                Reminder::STATUS_CANCELED,
            ])],
        ]);

        $reminder->status = $data['status'];

        if ($data['status'] === Reminder::STATUS_DONE) {
            $reminder->is_sent = true;
            $reminder->sent_at = now();
        }

        $reminder->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'وضعیت یادآوری به‌روزرسانی شد.',
                'reminder' => $reminder,
            ]);
        }

        return back()->with('status', 'وضعیت یادآوری به‌روزرسانی شد.');
    }

    /* ------------ تغییر وضعیت گروهی ------------ */

    public function bulkUpdateStatus(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('reminders.edit')) {
            abort(403);
        }

        $data = $request->validate([
            'ids'    => ['required', 'array'],
            'ids.*'  => ['integer'],
            'status' => ['required', Rule::in([
                Reminder::STATUS_OPEN,
                Reminder::STATUS_DONE,
                Reminder::STATUS_CANCELED,
            ])],
        ]);

        $query = Reminder::visibleForUser($user)->whereIn('id', $data['ids']);

        $updated = 0;

        $query->chunkById(100, function ($chunk) use (&$updated, $data) {
            foreach ($chunk as $reminder) {
                $reminder->status = $data['status'];

                if ($data['status'] === Reminder::STATUS_DONE) {
                    $reminder->is_sent = true;
                    $reminder->sent_at = now();
                }

                $reminder->save();
                $updated++;
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
            ]);
        }

        return back()->with('status', "وضعیت {$updated} یادآوری به‌روزرسانی شد.");
    }


    /* ------------ حذف ------------ */

    public function destroy(Reminder $reminder)
    {
        $user = Auth::user();

        if (! $reminder->canBeDeletedBy($user)) {
            abort(403);
        }

        $reminder->delete();

        return back()->with('status', 'یادآوری حذف شد.');
    }
}
