<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Modules\Properties\Entities\PropertyBuilding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:properties.buildings.view|properties.buildings.manage')->only(['index', 'search']);
        $this->middleware('permission:properties.buildings.create|properties.buildings.manage')->only('store');
        $this->middleware('permission:properties.buildings.edit|properties.buildings.manage')->only('update');
        $this->middleware('permission:properties.buildings.delete|properties.buildings.manage')->only('destroy');
    }

    public function index()
    {
        $buildings = PropertyBuilding::with('creator')->withCount('properties')->latest()->paginate(20);
        return view('properties::user.buildings.index', compact('buildings'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'floors_count' => 'nullable|string|max:255',
            'units_count' => 'nullable|string|max:255',
            'construction_year' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['created_by'] = auth()->id();

        $building = PropertyBuilding::create($data);
        $building->load('creator');
        $building->loadCount('properties');

        return response()->json([
            'success' => true,
            'message' => 'ساختمان جدید با موفقیت ایجاد شد.',
            'building' => $building
        ]);
    }

    public function update(Request $request, PropertyBuilding $building)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'floors_count' => 'nullable|string|max:255',
            'units_count' => 'nullable|string|max:255',
            'construction_year' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $building->update($validator->validated());
        $building->load('creator');
        $building->loadCount('properties');

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات ساختمان ویرایش شد.',
            'building' => $building
        ]);
    }

    public function destroy(PropertyBuilding $building)
    {
        if ($building->properties()->exists()) {
            return back()->with('error', 'این ساختمان دارای ملک ثبت شده است و قابل حذف نیست.');
        }

        $building->delete();
        return back()->with('success', 'ساختمان حذف شد.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $buildings = PropertyBuilding::where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($buildings);
    }
}
