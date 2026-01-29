<?php

namespace Modules\Properties\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Properties\Entities\Property;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with('status')->latest()->paginate(10);
        return view('properties::index', compact('properties'));
    }

    public function show($id)
    {
        $property = Property::with('status', 'creator')->findOrFail($id);
        return view('properties::show', compact('property'));
    }
}
