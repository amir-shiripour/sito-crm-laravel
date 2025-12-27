<?php

namespace Modules\ClientCalls\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Modules\ClientCalls\Entities\ClientCall;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Auth;
use Modules\Tasks\Entities\Task;

class ClientCallController extends Controller
{
    public function __construct()
    {
        // اگر خواستی می‌تونی اینجا هم middleware بذاری، فعلاً در routes گذاشتیم
        // $this->middleware('permission:client-calls.view')->only('index');
        // ...
    }

    public function index(Client $client)
    {
        $user = auth()->user();

        // اگر این کلاینت برای کاربر قابل مشاهده نباشد → 403
        if (! $client->isVisibleFor($user)) {
            abort(403);
        }

        $calls = $client->calls()
            ->visibleForUser($user)   // استفاده از اسکوپ روی مدل ClientCall
            ->with('user')
            ->orderByDesc('call_date')
            ->orderByDesc('call_time')
            ->paginate(20);

        // 🔹 نام‌فضای صحیح ویو ماژول: clientcalls::
        return view('clientcalls::user.calls.index', compact('client', 'calls'));
    }

    public function create(Client $client)
    {
        $user = auth()->user();

        if (! $client->isVisibleFor($user)) {
            abort(403);
        }

        return view('clientcalls::user.calls.form', [
            'client' => $client,
            'call'   => null,
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $user = auth()->user();

        if (! $client->isVisibleFor($user)) {
            abort(403);
        }

        // 1) ولیدیشن ورودی‌ها
        $validated = $request->validate([
            'call_date_jalali' => ['required', 'string'],        // 1403/09/12
            'call_time'        => ['required', 'date_format:H:i'],
            'reason'           => ['required', 'string', 'max:255'],
            'result'           => ['required', 'string'],
            'status'           => ['required', 'in:planned,done,failed,cancelled'],
        ]);

        // 2) تبدیل تاریخ جلالی → میلادی
        $jalali        = Jalalian::fromFormat('Y/m/d', $validated['call_date_jalali']);
        $gregorianDate = $jalali->toCarbon()->toDateString();   // 2025-12-03

        // 3) ساخت تماس
        $call = ClientCall::create([
            'client_id' => $client->id,
            'user_id'   => $user->id,
            'call_date' => $gregorianDate,
            'call_time' => $validated['call_time'],
            'reason'    => $validated['reason'],
            'result'    => $validated['result'],
            'status'    => $validated['status'],
        ]);

        // 4) آماده کردن لینک ایجاد پیگیری (در صورت امکان)
        $followupUrl = null;

        if (
            $call->status === 'done'
            && class_exists(\Modules\FollowUps\Entities\FollowUp::class)
            && class_exists(Task::class)
            && $user->can('followups.create')
        ) {
            $followupUrl = route('user.followups.create', [
                'related_type' => Task::RELATED_TYPE_CLIENT,
                'related_id'   => $client->id,
            ]);
        }

        // 5) اگر درخواست AJAX بود → JSON (برای ویجت / مودال)
        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'تماس با موفقیت ثبت شد.',
                'followup_url' => $followupUrl,
            ]);
        }

        // 6) حالت معمولی (فرم‌های کلاسیک)
        return redirect()
            ->route('user.clients.calls.index', $client)
            ->with('success', 'تماس با موفقیت ثبت شد.')
            ->with('followup_url', $followupUrl);
    }

    public function edit(Client $client, ClientCall $call)
    {
        $user = auth()->user();

        // تماس باید متعلق به همین کلاینت باشد
        abort_unless($call->client_id === $client->id, 404);

        // هم خود کلاینت و هم این تماس باید برای کاربر قابل مشاهده باشند
        if (! $client->isVisibleFor($user) || ! $call->isVisibleFor($user)) {
            abort(403);
        }

        return view('clientcalls::user.calls.form', [
            'client' => $client,
            'call'   => $call,
        ]);
    }

    public function update(Request $request, Client $client, ClientCall $call)
    {
        $user = auth()->user();

        abort_unless($call->client_id === $client->id, 404);

        if (! $client->isVisibleFor($user) || ! $call->isVisibleFor($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'call_date_jalali' => ['required', 'string'],
            'call_time'        => ['required', 'date_format:H:i'],
            'reason'           => ['required', 'string', 'max:255'],
            'result'           => ['required', 'string'],
            'status'           => ['required', 'in:planned,done,failed,cancelled'],
        ]);

        $jalali        = Jalalian::fromFormat('Y/m/d', $validated['call_date_jalali']);
        $gregorianDate = $jalali->toCarbon()->toDateString();

        $call->update([
            'call_date' => $gregorianDate,
            'call_time' => $validated['call_time'],
            'reason'    => $validated['reason'],
            'result'    => $validated['result'],
            'status'    => $validated['status'],
        ]);

        $redirect = redirect()
            ->route('user.clients.calls.index', $client)
            ->with('success', 'تماس با موفقیت به‌روزرسانی شد.');

        if (($call->status === 'done' || $call->status === 'failed') && $user->can('followups.create')) {
            $redirect->with('call_followup_suggestion', [
                'client_id'   => $client->id,
                'client_name' => $client->full_name ?: $client->username,
                'status'      => $call->status,
            ]);
        }

        return $redirect;
    }

    public function destroy(Client $client, ClientCall $call)
    {
        $user = auth()->user();

        abort_unless($call->client_id === $client->id, 404);

        if (! $client->isVisibleFor($user) || ! $call->isVisibleFor($user)) {
            abort(403);
        }

        $call->delete();

        return back()->with('success', 'تماس حذف شد.');
    }

    /**
     * ذخیره تماس سریع.
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'call_date_jalali' => 'required|date_format:Y/m/d',
            'call_time'        => 'required|date_format:H:i',
            'status'           => 'required|string|in:planned,done,failed,canceled',
            'reason'           => 'required|string',
            'result'           => 'required|string',
        ]);

        $callDate = Jalalian::fromFormat('Y/m/d', $request->call_date_jalali)
            ->toCarbon()
            ->startOfDay();

        $call = new ClientCall();
        $call->client_id = $request->client_id;
        $call->call_date = $callDate;
        $call->call_time = $request->call_time;
        $call->status    = $request->status;
        $call->reason    = $request->reason;
        $call->result    = $request->result;
        $call->user_id   = $request->user()->id;
        $call->save();

        // 🔗 اگر تماس انجام شد و کاربر اجازه ثبت پیگیری دارد، لینک ایجاد پیگیری را بساز
        $followupUrl = null;
        if (($call->status === 'done' || $call->status === 'failed') && $request->user()?->can('followups.create')) {
            $followupUrl = route('user.followups.create', [
                'related_type' => Task::RELATED_TYPE_CLIENT,
                'related_id'   => $call->client_id,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'تماس با موفقیت ثبت شد.',
                'followup_url' => $followupUrl,
            ]);
        }

        return back()->with('success', 'تماس با موفقیت ثبت شد.');
    }


    /**
     * جستجو مشتری‌ها برای ثبت تماس سریع و جستجوی عمومی.
     * فقط مشتری‌هایی که کاربر به آن‌ها دسترسی دارد را برمی‌گرداند.
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');
        $user = auth()->user();

        if (empty($query)) {
            return response()->json([]);
        }

        // جستجوی کلاینت‌ها بر اساس نام، شماره موبایل، کد ملی، و شماره پرونده
        // فقط مشتری‌هایی که کاربر به آن‌ها دسترسی دارد
        $clients = Client::query()
            ->visibleForUser($user)
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('full_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('national_code', 'like', "%{$query}%")
                    ->orWhere('case_number', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'full_name', 'phone', 'national_code', 'case_number']);

        // تشخیص اینکه query در کدام فیلد match شده و اضافه کردن matched_field
        $clients = $clients->map(function ($client) use ($query) {
            $matchedField = null;

            // چک کردن اینکه query در کدام فیلد match شده (اولویت: phone > national_code > case_number)
            // استفاده از mb_stripos برای پشتیبانی از کاراکترهای فارسی و بررسی دقیق‌تر
            if ($client->phone && mb_stripos($client->phone, $query) !== false) {
                $matchedField = 'phone';
            } elseif ($client->national_code && mb_stripos($client->national_code, $query) !== false) {
                $matchedField = 'national_code';
            } elseif ($client->case_number && mb_stripos($client->case_number, $query) !== false) {
                $matchedField = 'case_number';
            }
            // اگر هیچکدام match نشد، یعنی بر اساس نام جستجو شده (matchedField = null)

            // تبدیل به array برای اینکه matched_field در JSON شامل شود
            return [
                'id' => $client->id,
                'full_name' => $client->full_name,
                'phone' => $client->phone,
                'national_code' => $client->national_code,
                'case_number' => $client->case_number,
                'matched_field' => $matchedField,
            ];
        })->values(); // values() برای reset کردن keys

        return response()->json($clients);
    }
}
