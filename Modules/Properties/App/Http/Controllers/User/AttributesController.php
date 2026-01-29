<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyAttribute;

class AttributesController extends Controller
{
    public function index()
    {
        $detailsAttributes = PropertyAttribute::where('section', 'details')->orderBy('sort_order')->get();
        $featuresAttributes = PropertyAttribute::where('section', 'features')->orderBy('sort_order')->get();

        return view('properties::user.settings.attributes', compact('detailsAttributes', 'featuresAttributes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'section' => 'required|in:details,features',
            'options' => 'nullable|string', // Comma separated options for select
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        $data['sort_order'] = PropertyAttribute::where('section', $data['section'])->max('sort_order') + 1;

        PropertyAttribute::create($data);

        return back()->with('success', 'ویژگی با موفقیت اضافه شد.');
    }

    public function update(Request $request, PropertyAttribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'options' => 'nullable|string',
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        $attribute->update($data);

        return back()->with('success', 'ویژگی با موفقیت ویرایش شد.');
    }

    public function destroy(PropertyAttribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'ویژگی حذف شد.');
    }
}
