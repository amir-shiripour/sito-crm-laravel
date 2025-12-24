<?php

namespace Modules\Clients\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clients\Entities\Client;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(config('clients.items_per_page', 20));
        return view('clients::index', compact('clients'));
    }

    public function create()
    {
        return view('clients::create');
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

        return redirect()->route('clients.index')->with('success','Client created.');
    }

    public function edit(Client $client)
    {
        return view('clients::edit', compact('client'));
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

        return redirect()->route('clients.index')->with('success','Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success','Client deleted.');
    }
}
