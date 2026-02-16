<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Modules\Properties\Entities\PropertyOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:properties.owners.view|properties.owners.manage')->only(['index', 'search']);
        $this->middleware('permission:properties.owners.create|properties.owners.manage')->only('store');
        $this->middleware('permission:properties.owners.edit|properties.owners.manage')->only('update');
        $this->middleware('permission:properties.owners.delete|properties.owners.manage')->only('destroy');
    }

    public function index()
    {
        $owners = PropertyOwner::with('creator')->withCount('properties')->latest()->paginate(20);
        return view('properties::user.owners.index', compact('owners'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:property_owners,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['created_by'] = auth()->id();

        $owner = PropertyOwner::create($data);
        $owner->load('creator');
        $owner->loadCount('properties');

        return response()->json([
            'success' => true,
            'message' => 'مالک جدید با موفقیت ایجاد شد.',
            'owner' => $owner
        ]);
    }

    public function update(Request $request, PropertyOwner $owner)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:255',
                Rule::unique('property_owners', 'phone')->ignore($owner->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $owner->update($validator->validated());
        $owner->load('creator');
        $owner->loadCount('properties');

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات مالک ویرایش شد.',
            'owner' => $owner
        ]);
    }

    public function destroy(PropertyOwner $owner)
    {
        if ($owner->properties()->exists()) {
            return back()->with('error', 'این مالک دارای ملک ثبت شده است و قابل حذف نیست.');
        }

        $owner->delete();
        return back()->with('success', 'مالک حذف شد.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $owners = PropertyOwner::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        })
        ->limit(20)
        ->get();

        return response()->json($owners);
    }
}
