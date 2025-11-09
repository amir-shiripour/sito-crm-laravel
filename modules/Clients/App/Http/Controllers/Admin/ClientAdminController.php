<?php

namespace Modules\Clients\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;

class ClientAdminController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|unique:clients,email,' . $client->id,
            'phone'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);
        $client->update($data);
        return redirect()->route('admin.clients.index')->with('success','Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success','Client deleted.');
    }
}
