<?php

namespace Modules\Clients\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;

class ClientAdminController extends Controller
{
    /**
     * لیست کلاینت‌ها برای بخش ادمین
     * (با احترام به visibility اگر بخواهی برای نقش‌هایی مثل manager محدود کنی)
     */
    public function index()
    {
        $user = auth()->user();
        $clients = Client::query()
            ->with(['creator', 'status'])
            ->visibleForUser($user)
            ->latest()
            ->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * هلپر برای چک کردن دسترسی دیدن/ویرایش این کلاینت در بخش ادمین
     */
    protected function ensureVisible(Client $client): void
    {
        $user = auth()->user();

        if (! $client->isVisibleFor($user)) {
            abort(403, 'شما به این پرونده دسترسی ندارید.');
        }
    }

    public function edit(Client $client)
    {
        $this->ensureVisible($client);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->ensureVisible($client);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|unique:clients,email,' . $client->id,
            'phone'     => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $client->update($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success','Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->ensureVisible($client);

        $client->delete();

        return back()->with('success','Client deleted.');
    }
}
