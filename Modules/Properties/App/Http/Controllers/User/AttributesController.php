<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\PropertyAttribute;

class AttributesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:properties.attributes.view|properties.attributes.manage')->only('index');
        $this->middleware('permission:properties.attributes.create|properties.attributes.manage')->only('store');
        $this->middleware('permission:properties.attributes.edit|properties.attributes.manage')->only('update');
        $this->middleware('permission:properties.attributes.delete|properties.attributes.manage')->only('destroy');
    }

    public function index()
    {
        $detailsAttributes = PropertyAttribute::where('section', 'details')->orderBy('sort_order')->get();
        $featuresAttributes = PropertyAttribute::where('section', 'features')->orderBy('sort_order')->get();

        return view('properties::user.settings.attributes', compact('detailsAttributes', 'featuresAttributes'));
    }

    public function store(Request $request)
    {
        Log::info('--- AttributesController Store Debug ---');
        Log::info('Request Data:', $request->all());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'section' => 'required|in:details,features',
            'options' => 'nullable|string',
            'is_filterable' => 'nullable|boolean',
            'is_range_filter' => 'nullable|boolean',
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        $data['sort_order'] = PropertyAttribute::where('section', $data['section'])->max('sort_order') + 1;

        // Checkbox handling
        $data['is_filterable'] = $request->has('is_filterable');
        $data['is_range_filter'] = $request->has('is_range_filter');
        $data['is_active'] = true;

        Log::info('Data to Create:', $data);

        $attr = PropertyAttribute::create($data);

        Log::info('Created Attribute:', $attr->toArray());

        return back()->with('success', 'ویژگی با موفقیت اضافه شد.');
    }

    public function update(Request $request, PropertyAttribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'options' => 'nullable|string',
            'is_filterable' => 'nullable|boolean',
            'is_range_filter' => 'nullable|boolean',
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        // Checkbox handling
        $data['is_filterable'] = $request->has('is_filterable');
        $data['is_range_filter'] = $request->has('is_range_filter');

        $attribute->update($data);

        return back()->with('success', 'ویژگی با موفقیت ویرایش شد.');
    }

    public function destroy(PropertyAttribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'ویژگی حذف شد.');
    }
}
