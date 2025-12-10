<?php

namespace Modules\Clients\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Modules\Clients\App\Http\Requests\StoreClientRequest;
use Modules\Clients\App\Http\Requests\UpdateClientRequest;

class ClientController extends Controller
{
    public function __construct()
    {
        // دسترسی‌ها بر اساس پرمیژن
        $this->middleware('permission:clients.view')->only(['index','show','profile']);
        $this->middleware('permission:clients.create')->only(['create','store']);
        $this->middleware('permission:clients.edit')->only(['edit','update']);
        $this->middleware('permission:clients.delete')->only(['destroy']);
    }

    /**
     * لیست کلاینت‌ها، فیلتر شده بر اساس قوانین visibility
     */
    public function index()
    {
        $user = auth()->user();
        /*$clients = Client::query()
            ->with(['creator', 'status'])
            ->visibleForUser($user)
            ->latest()
            ->paginate(12);*/

        $clients = Client::visibleForUser($user)
            ->with([
                'creator',
                'status',
                'calls.user',
            ])
            ->visibleForUser($user)
            ->latest()
            ->paginate(12);

        return view('clients::user.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients::user.clients.create');
    }

    /**
     * این متد عملاً فعلاً استفاده نمی‌شود (ما از Livewire فرم پویا داریم)
     * ولی برای سازگاری نگهش می‌داریم.
     */
    public function store(StoreClientRequest $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|unique:clients,email',
            'phone'     => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        Client::create($data);

        return redirect()
            ->route('user.clients.index')
            ->with('success', 'Client created.');
    }

    /**
     * هلپر داخلی برای چک کردن این‌که آیا یوزر اجازه دیدن این کلاینت را دارد یا نه
     */
    protected function ensureVisible(Client $client): void
    {
        $user = auth()->user();

        if (! $client->isVisibleFor($user)) {
            abort(403, 'شما به این پرونده دسترسی ندارید.');
        }
    }

    public function show(Client $client)
    {
        $this->ensureVisible($client);

        $client->load([
            'creator',
            'status',
            'calls.user',
            'followUps.assignee',
        ]);

        return view('clients::user.clients.show', compact('client'));
    }


    public function edit(Client $client)
    {
        $this->ensureVisible($client);

        return view('clients::user.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->ensureVisible($client);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => "nullable|email|unique:clients,email,{$client->id}",
            'phone'     => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $client->update($data);

        return redirect()
            ->route('user.clients.index')
            ->with('success','Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->ensureVisible($client);

        // بسته به منطق پروژه: soft delete یا force delete
        // $client->delete();
        $client->forceDelete();

        return back()->with('success','Client deleted.');
    }

    /**
     * پروفایل کاربری client از دید پنل user (ادمین‌ها)
     * این متد را اگر واقعاً استفاده نمی‌کنی، بعداً می‌تونیم تمیزترش کنیم
     */
    public function profile()
    {
        $user = auth()->user();

        // اگر رابطه‌ای مثل user->client داری، همین را نگه می‌داریم
        $client = $user->client ?? null;

        // می‌توانی اینجا هم از isVisibleFor استفاده کنی اگر لازم شد
        if ($client && ! $client->isVisibleFor($user)) {
            abort(403, 'شما به این پرونده دسترسی ندارید.');
        }

        return view('clients::user.clients.profile', compact('client'));
    }

    /**
     * ایجاد سریع کلاینت (برای ویجت / پاپ‌آپ quick create)
     *
     * اگر درخواست به‌صورت AJAX/JSON باشد، پاسخ JSON برمی‌گرداند،
     * در غیر این صورت مانند store رفتار می‌کند و redirect می‌دهد.
     */
    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|unique:clients,email',
            'phone'     => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        $client = Client::create($data);

        // اگر ویجت/فرانت انتظار JSON دارد (مثلاً با fetch/axios ارسال شده)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'مشتری با موفقیت ایجاد شد.',
                'client'  => $client->only(['id', 'full_name', 'email', 'phone']),
            ], 201);
        }

        // fallback برای ارسال معمولی فرم
        return redirect()
            ->route('user.clients.index')
            ->with('success', 'Client created.');
    }

}
