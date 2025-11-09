<?php

namespace Modules\Clients\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;

class ClientController extends Controller
{
    public function __construct()
    {
        // می‌توانید middlewareهای permission را همینجا قرار دهید یا در routes استفاده کنید
        $this->middleware('permission:clients.view')->only(['index','show','profile']);
        $this->middleware('permission:clients.create')->only(['create','store']);
        $this->middleware('permission:clients.edit')->only(['edit','update']);
        $this->middleware('permission:clients.delete')->only(['destroy']);
    }

    public function index()
    {
        // محدود کردن نمایش بر اساس role یا owner اگر لازم است
        $clients = Client::latest()->paginate(12);
        return view('user.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('user.clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|unique:clients,email',
            'phone'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);
        $data['created_by'] = auth()->id();
        Client::create($data);
        return redirect()->route('user.clients.index')->with('success', 'Client created.');
    }

    public function show(Client $client)
    {
        return view('user.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('user.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>"nullable|email|unique:clients,email,{$client->id}",
            'phone'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);
        $client->update($data);
        return redirect()->route('user.clients.index')->with('success','Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success','Client deleted.');
    }

    // پروفایل کاربری client
    public function profile()
    {
        // اگر کاربر خودش یک client هست، client مربوطه را بارگذاری کنید
        $user = auth()->user();
        // فرض: user->client relation exists
        $client = $user->client ?? null;
        return view('user.clients.profile', compact('client'));
    }
}
