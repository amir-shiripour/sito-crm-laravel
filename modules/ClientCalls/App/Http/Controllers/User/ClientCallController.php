<?php

namespace Modules\ClientCalls\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Modules\ClientCalls\Entities\ClientCall;
use Morilog\Jalali\Jalalian;

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

        // 1) ولیدیشن ورودی‌ها (همه فیلدها اجباری)
        $validated = $request->validate([
            'call_date_jalali' => ['required', 'string'],        // 1403/09/12
            'call_time'        => ['required', 'date_format:H:i'],
            'reason'           => ['required', 'string', 'max:255'],
            'result'           => ['required', 'string'],
            'status'           => ['required', 'in:planned,done,failed,cancelled'],
        ]);

        // 2) تبدیل تاریخ جلالی → میلادی (Carbon)
        // ورودی مثلاً: 1403/09/12
        $jalali = Jalalian::fromFormat('Y/m/d', $validated['call_date_jalali']);
        $gregorianDate = $jalali->toCarbon()->toDateString();   // 2025-12-03

        $data = [
            'client_id' => $client->id,
            'user_id'   => $user->id,
            'call_date' => $gregorianDate,
            'call_time' => $validated['call_time'],
            'reason'    => $validated['reason'],
            'result'    => $validated['result'],
            'status'    => $validated['status'],
        ];

        ClientCall::create($data);

        return redirect()
            ->route('user.clients.calls.index', $client)
            ->with('success', 'تماس با موفقیت ثبت شد.');
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

        // 1) ولیدیشن
        $validated = $request->validate([
            'call_date_jalali' => ['required', 'string'],
            'call_time'        => ['required', 'date_format:H:i'],
            'reason'           => ['required', 'string', 'max:255'],
            'result'           => ['required', 'string'],
            'status'           => ['required', 'in:planned,done,failed,cancelled'],
        ]);

        // 2) تبدیل تاریخ جلالی → میلادی
        $jalali = Jalalian::fromFormat('Y/m/d', $validated['call_date_jalali']);
        $gregorianDate = $jalali->toCarbon()->toDateString();

        $data = [
            'call_date' => $gregorianDate,
            'call_time' => $validated['call_time'],
            'reason'    => $validated['reason'],
            'result'    => $validated['result'],
            'status'    => $validated['status'],
        ];

        $call->update($data);

        return redirect()
            ->route('user.clients.calls.index', $client)
            ->with('success', 'تماس با موفقیت به‌روزرسانی شد.');
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
}
