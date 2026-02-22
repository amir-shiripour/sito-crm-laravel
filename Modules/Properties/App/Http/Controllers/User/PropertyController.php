<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyImage;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\PropertyAttribute;
use Modules\Properties\Entities\PropertyAttributeValue;
use Modules\Properties\Entities\PropertyOwner;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\PropertyBuilding;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use App\Models\User;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Start Query manually to control visibility logic
        $query = Property::query()
            ->with('status', 'creator', 'agent', 'category', 'building');

        // Check for trash view
        if ($request->has('trashed') && $request->trashed == '1') {
            $query->onlyTrashed();
        }

        // Visibility Logic:
        // Default: Show only user's properties (created_by OR agent_id)
        // If user has permission AND requests 'show_all', then show all.

        $canViewAll = $user->hasRole('super-admin') || $user->can('properties.view.all') || $user->can('properties.manage');

        if ($canViewAll) {
            if (!$request->has('show_all') || $request->show_all != '1') {
                // Restrict to own properties by default
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            }
            // If show_all=1, no restriction applied (shows all)
        } else {
            // Regular users always restricted
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('agent_id', $user->id);
            });
        }

        // 1. Search (Title, Code, Address)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 2. Listing Type
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // 3. Property Type
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // 4. Status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // 5. Publication Status
        if ($request->filled('publication_status')) {
            $query->where('publication_status', $request->publication_status);
        }

        // 6. Agent (For Admins/Managers)
        if ($request->filled('agent_id') && $canViewAll) {
            $query->where('agent_id', $request->agent_id);
        }

        // 7. Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 8. Building
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        $properties = $query->latest()->paginate(10)->withQueryString();

        // Data for filters
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();
        $categories = PropertyCategory::where('user_id', $user->id)->get();
        $buildings = PropertyBuilding::latest()->get(); // Or filter by user if needed

        // Agents list for filter
        $agents = [];
        if ($canViewAll) {
            $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
            $agents = User::role($agentRoles)->get(['id', 'name']);
        }

        return view('properties::user.index', compact('properties', 'statuses', 'agents', 'categories', 'buildings'));
    }

    public function create()
    {
        $maxGalleryImages = PropertySetting::get('max_gallery_images', 10);
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();

        // Fetch Agents (Initial load, maybe limit or just pass empty if using search)
        // For now, let's pass all if not too many, or just rely on search.
        // But the view expects $agents to check if the section should be shown.
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $agents = User::role($agentRoles)->get();

        return view('properties::user.create', compact('maxGalleryImages', 'statuses', 'agents'));
    }

    public function store(Request $request)
    {
        // Convert Jalali Date to Gregorian before validation
        if ($request->has('delivery_date') && !empty($request->delivery_date)) {
            try {
                $date = Jalalian::fromFormat('Y/m/d', $request->delivery_date)->toCarbon();
                $request->merge(['delivery_date' => $date->format('Y-m-d')]);
            } catch (\Exception $e) {
                $request->merge(['delivery_date' => null]);
            }
        }

        if ($request->has('registered_at') && !empty($request->registered_at)) {
            try {
                $date = Jalalian::fromFormat('Y/m/d', $request->registered_at)->toCarbon();
                $request->merge(['registered_at' => $date->format('Y-m-d')]);
            } catch (\Exception $e) {
                $request->merge(['registered_at' => null]);
            }
        } else {
            // Default to today if not provided
            $request->merge(['registered_at' => now()->format('Y-m-d')]);
        }

        // Sanitize prices (remove commas)
        $priceFields = ['price', 'min_price', 'deposit_price', 'rent_price', 'advance_price'];
        foreach ($priceFields as $field) {
            if ($request->has($field) && !is_null($request->input($field))) {
                $request->merge([
                    $field => str_replace(',', '', $request->input($field))
                ]);
            }
        }

        $maxSize = PropertySetting::get('max_file_size', 10240);
        $allowedTypes = PropertySetting::get('allowed_file_types', 'jpeg,png,jpg,gif');
        $allowedTypes = str_replace(' ', '', $allowedTypes);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'listing_type' => 'required|in:sale,presale,rent',
            'property_type' => 'required|in:apartment,villa,land,office',
            'document_type' => 'nullable|string',
            'building_id' => 'nullable|integer',
            'registered_at' => 'nullable|date',
            'status_id' => 'nullable|exists:property_statuses,id',
            'publication_status' => 'required|in:draft,published',
            'confidential_notes' => 'nullable|string',
            'usage_type' => 'nullable|required_if:property_type,land|in:residential,industrial,commercial,agricultural',
            'delivery_date' => 'nullable|required_if:listing_type,presale|date',
            'code' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:property_categories,id',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'cover_image' => "required|image|mimes:{$allowedTypes}|max:{$maxSize}",
            'gallery_images.*' => "nullable|image|mimes:{$allowedTypes}|max:{$maxSize}",
            'is_special' => 'nullable|boolean',
            'agent_id' => 'nullable|exists:users,id',

            // قیمت‌ها
            'price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'deposit_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'advance_price' => 'nullable|numeric|min:0',

            // ویژگی‌ها و امکانات (استاندارد)
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'features.*' => 'exists:property_attributes,id',

            // متای سفارشی (شامل جزئیات و امکانات سفارشی)
            'meta' => 'nullable|array',
            'meta.features' => 'nullable|array',
            'meta.features.*' => 'string',
            'meta.details' => 'nullable|array',
        ]);

        // Handle Property Code
        if ($request->input('code_type') === 'auto' || empty($data['code'])) {
            $data['code'] = $this->generateUniquePropertyCode($data['category_id'] ?? null);
        } else {
            $prefix = $this->getPropertyCodePrefix($data['category_id'] ?? null);
            $data['code'] = $prefix . $data['code'];

            if (Property::withTrashed()->where('code', $data['code'])->exists()) {
                $errorMsg = 'کد ملک وارد شده (با احتساب پیش‌وند) تکراری است.';
                if ($request->wantsJson()) {
                    return response()->json(['errors' => ['code' => [$errorMsg]]], 422);
                }
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        // Handle Cover Image Upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            if ($file->isValid()) {
                try {
                    $path = $this->uploadFile($file, 'properties/covers');
                    $data['cover_image'] = $path;
                } catch (\Exception $e) {
                    Log::error('Cover Image Store Failed: ' . $e->getMessage());
                    $errorMsg = 'خطا در ذخیره تصویر شاخص: ' . $e->getMessage();
                    if ($request->wantsJson()) {
                        return response()->json(['message' => $errorMsg], 500);
                    }
                    return back()->withInput()->with('error', $errorMsg);
                }
            } else {
                $errorMsg = 'فایل تصویر شاخص نامعتبر است (کد خطا: ' . $file->getError() . ')';
                if ($request->wantsJson()) {
                    return response()->json(['errors' => ['cover_image' => [$errorMsg]]], 422);
                }
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        // Handle Video Upload
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file->isValid()) {
                try {
                    $path = $this->uploadFile($file, 'properties/videos');
                    $data['video'] = $path;
                } catch (\Exception $e) {
                    Log::error('Video Store Failed: ' . $e->getMessage());
                }
            }
        }

        // Set created_by to current user
        $data['created_by'] = auth()->id();

        // Handle agent_id logic
        $user = auth()->user();
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $isAgent = $user->hasAnyRole($agentRoles);
        $isAdmin = $user->hasRole(['super-admin', 'admin']);

        if ($isAdmin || !$isAgent) {
            $data['agent_id'] = $request->input('agent_id') ?: $user->id;
        } else {
            $data['agent_id'] = $user->id;
        }

        // Set Default Status if not provided
        if (empty($data['status_id'])) {
            $defaultStatus = PropertyStatus::where('is_default', true)->first();
            if ($defaultStatus) {
                $data['status_id'] = $defaultStatus->id;
            } else {
                // Fallback to first active status if no default is set
                $firstStatus = PropertyStatus::where('is_active', true)->orderBy('sort_order')->first();
                $data['status_id'] = $firstStatus?->id;
            }
        }

        // Prepare Meta Data
        $meta = $request->input('meta', []);

        // Handle Special Property
        if ($request->has('is_special')) {
            $meta['is_special'] = true;
        }

        // Handle Features in Meta (for quick access if needed, though we use attribute values mostly)
        // But here we want to store CUSTOM features in meta['features']
        // The form sends meta[features][] for custom features.

        $data['meta'] = $meta;

        $property = null;
        $retryCount = 0;
        $maxRetries = 3;

        while ($retryCount < $maxRetries) {
            try {
                $property = Property::create($data);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] == 1062 && strpos($e->getMessage(), 'properties_code_unique') !== false) {
                    if ($request->input('code_type') === 'auto' || empty($request->input('code'))) {
                        $data['code'] = $this->generateUniquePropertyCode($data['category_id'] ?? null);
                        $retryCount++;
                        continue;
                    } else {
                        $errorMsg = 'کد ملک وارد شده تکراری است.';
                        if ($request->wantsJson()) {
                            return response()->json(['errors' => ['code' => [$errorMsg]]], 422);
                        }
                        return back()->withInput()->with('error', $errorMsg);
                    }
                }
                throw $e;
            }
        }

        if (!$property) {
            $errorMsg = 'خطا در ثبت ملک (کد تکراری). لطفا مجددا تلاش کنید.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMsg], 500);
            }
            return back()->withInput()->with('error', $errorMsg);
        }

        // Handle Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                if ($image->isValid()) {
                    try {
                        $path = $this->uploadFile($image, 'properties/gallery');
                        PropertyImage::create([
                            'property_id' => $property->id,
                            'path' => $path,
                            'sort_order' => $index,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Gallery Image Store Failed: ' . $e->getMessage());
                    }
                }
            }
        }

        // Save Standard Attributes (Details)
        if ($request->has('attributes')) {
            foreach ($request->input('attributes') as $attributeId => $value) {
                if (!empty($value)) {
                    PropertyAttributeValue::create([
                        'property_id' => $property->id,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }
        }

        // Save Standard Features (as Attribute Values with value '1')
        if ($request->has('features')) {
            foreach ($request->input('features') as $featureId) {
                PropertyAttributeValue::create([
                    'property_id' => $property->id,
                    'attribute_id' => $featureId,
                    'value' => '1',
                ]);
            }
        }

        $redirectUrl = route('user.properties.pricing', $property);
        $successMsg = 'مشخصات اولیه ثبت شد. لطفا قیمت‌گذاری را انجام دهید.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'redirect_url' => $redirectUrl
            ]);
        }

        return redirect($redirectUrl)->with('success', $successMsg);
    }

    public function pricing(Property $property)
    {
        // Check visibility
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $currency = PropertySetting::get('currency', 'toman');
        return view('properties::user.pricing', compact('property', 'currency'));
    }

    public function updatePricing(Request $request, Property $property)
    {
        // Check permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $request->merge([
            'price' => str_replace(',', '', $request->input('price')),
            'min_price' => str_replace(',', '', $request->input('min_price')),
            'deposit_price' => str_replace(',', '', $request->input('deposit_price')),
            'rent_price' => str_replace(',', '', $request->input('rent_price')),
            'advance_price' => str_replace(',', '', $request->input('advance_price')),
            'is_convertible' => $request->has('is_convertible') ? 1 : 0,
        ]);

        $rules = [
            'is_convertible' => 'boolean',
            'convertible_with' => 'nullable|string|max:255',
        ];

        if ($property->listing_type === 'sale') {
            $rules = array_merge($rules, [
                'price' => 'required|numeric|min:0',
                'min_price' => 'nullable|numeric|min:0|lte:price',
            ]);
        } elseif ($property->listing_type === 'rent') {
            $rules = array_merge($rules, [
                'deposit_price' => 'required|numeric|min:0',
                'rent_price' => 'required|numeric|min:0',
            ]);
        } elseif ($property->listing_type === 'presale') {
            $rules = array_merge($rules, [
                'advance_price' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'min_price' => 'nullable|numeric|min:0|lte:price',
            ]);
        }

        $data = $request->validate($rules);
        $property->update($data);

        // Redirect to Details Step
        return redirect()->route('user.properties.details', $property)->with('success', 'قیمت‌گذاری ثبت شد. لطفا اطلاعات تکمیلی را وارد کنید.');
    }

    public function details(Property $property)
    {
        // Check visibility
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $propertyAttributes = PropertyAttribute::where('section', 'details')->where('is_active', true)->orderBy('sort_order')->get();

        $customDetails = [];
        if (isset($property->meta['details']) && is_array($property->meta['details'])) {
            foreach ($property->meta['details'] as $key => $value) {
                $customDetails[] = ['key' => $key, 'value' => $value];
            }
        }

        return view('properties::user.details', compact('property', 'propertyAttributes', 'customDetails'));
    }

    public function updateDetails(Request $request, Property $property)
    {
        // Check permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $data = $request->validate([
            'attributes' => 'array',
            'attributes.*' => 'nullable|string|max:255',
            'meta' => 'array',
            'meta.*.key' => 'required_with:meta.*.value|string|max:255',
            'meta.*.value' => 'nullable|string|max:255',
        ]);

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeId => $value) {
                if (!empty($value)) {
                    PropertyAttributeValue::updateOrCreate(
                        ['property_id' => $property->id, 'attribute_id' => $attributeId],
                        ['value' => $value]
                    );
                } else {
                    PropertyAttributeValue::where('property_id', $property->id)
                        ->where('attribute_id', $attributeId)
                        ->delete();
                }
            }
        }

        $meta = $property->meta ?? [];
        $meta['details'] = [];

        if (isset($data['meta'])) {
            foreach ($data['meta'] as $item) {
                if (!empty($item['key'])) {
                    $meta['details'][$item['key']] = $item['value'];
                }
            }
        }

        $property->meta = $meta;
        $property->save();

        return redirect()->route('user.properties.features', $property)->with('success', 'اطلاعات تکمیلی ثبت شد. لطفا امکانات را انتخاب کنید.');
    }

    public function features(Property $property)
    {
        // Check visibility
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $propertyAttributes = PropertyAttribute::where('section', 'features')->where('is_active', true)->orderBy('sort_order')->get();

        $customFeatures = [];
        if (isset($property->meta['features']) && is_array($property->meta['features'])) {
            foreach ($property->meta['features'] as $feature) {
                $customFeatures[] = ['value' => $feature];
            }
        }

        return view('properties::user.features', compact('property', 'propertyAttributes', 'customFeatures'));
    }

    public function updateFeatures(Request $request, Property $property)
    {
        // Check permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        $data = $request->validate([
            'attributes' => 'array',
            'attributes.*' => 'exists:property_attributes,id',
            'meta' => 'array',
            'meta.*.value' => 'nullable|string|max:255',
        ]);

        $featureAttributeIds = PropertyAttribute::where('section', 'features')->pluck('id');

        PropertyAttributeValue::where('property_id', $property->id)
            ->whereIn('attribute_id', $featureAttributeIds)
            ->delete();

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeId) {
                PropertyAttributeValue::create([
                    'property_id' => $property->id,
                    'attribute_id' => $attributeId,
                    'value' => '1',
                ]);
            }
        }

        $meta = $property->meta ?? [];
        $meta['features'] = [];

        if (isset($data['meta'])) {
            $meta['features'] = collect($data['meta'])->pluck('value')->filter()->values()->all();
        }

        $property->meta = $meta;
        $property->save();

        return redirect()->route('user.properties.index')->with('success', 'ملک با موفقیت ثبت نهایی شد.');
    }

    public function edit(Property $property)
    {
        // Check visibility/permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        // Eager load building
        $property->load('building');

        $maxGalleryImages = PropertySetting::get('max_gallery_images', 10);
        $owners = PropertyOwner::latest()->get();
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();

        if ($property->delivery_date) {
            $property->delivery_date_jalali = Jalalian::fromCarbon($property->delivery_date)->format('Y/m/d');
        }

        if ($property->registered_at) {
            $property->registered_at_jalali = Jalalian::fromCarbon($property->registered_at)->format('Y/m/d');
        }

        $customDetails = [];
        if (isset($property->meta['details']) && is_array($property->meta['details'])) {
            foreach ($property->meta['details'] as $key => $value) {
                $customDetails[] = ['key' => $key, 'value' => $value];
            }
        }

        $customFeatures = [];
        if (isset($property->meta['features']) && is_array($property->meta['features'])) {
            foreach ($property->meta['features'] as $feature) {
                $customFeatures[] = ['value' => $feature];
            }
        }

        // Fetch Agents
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $agents = User::role($agentRoles)->get();

        return view('properties::user.edit', compact('property', 'maxGalleryImages', 'customDetails', 'customFeatures', 'owners', 'statuses', 'agents'));
    }

    public function update(Request $request, Property $property)
    {
        // Check permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        if ($request->has('delivery_date') && !empty($request->delivery_date)) {
            try {
                $date = Jalalian::fromFormat('Y/m/d', $request->delivery_date)->toCarbon();
                $request->merge(['delivery_date' => $date->format('Y-m-d')]);
            } catch (\Exception $e) {
                $request->merge(['delivery_date' => null]);
            }
        }

        if ($request->has('registered_at') && !empty($request->registered_at)) {
            try {
                $date = Jalalian::fromFormat('Y/m/d', $request->registered_at)->toCarbon();
                $request->merge(['registered_at' => $date->format('Y-m-d')]);
            } catch (\Exception $e) {
                $request->merge(['registered_at' => null]);
            }
        }

        $maxSize = PropertySetting::get('max_file_size', 10240);
        $allowedTypes = PropertySetting::get('allowed_file_types', 'jpeg,png,jpg,gif');
        $allowedTypes = str_replace(' ', '', $allowedTypes);

        // Sanitize prices
        $priceFields = ['price', 'min_price', 'deposit_price', 'rent_price', 'advance_price'];
        foreach ($priceFields as $field) {
            if ($request->has($field) && !is_null($request->input($field))) {
                $request->merge([
                    $field => str_replace(',', '', $request->input($field))
                ]);
            }
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'listing_type' => 'required|in:sale,presale,rent',
            'property_type' => 'required|in:apartment,villa,land,office',
            'document_type' => 'nullable|string',
            'building_id' => 'nullable|integer',
            'registered_at' => 'nullable|date',
            'status_id' => 'nullable|exists:property_statuses,id',
            'publication_status' => 'required|in:draft,published',
            'confidential_notes' => 'nullable|string',
            'usage_type' => 'nullable|required_if:property_type,land|in:residential,industrial,commercial,agricultural',
            'delivery_date' => 'nullable|required_if:listing_type,presale|date',
            'code' => 'nullable|string|max:255|unique:properties,code,' . $property->id,
            'category_id' => 'nullable|exists:property_categories,id',
            'owner_id' => 'nullable|exists:property_owners,id',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'cover_image' => "nullable|image|mimes:{$allowedTypes}|max:{$maxSize}",
            'gallery_images.*' => "nullable|image|mimes:{$allowedTypes}|max:{$maxSize}",
            'is_special' => 'nullable|boolean',
            'agent_id' => 'nullable|exists:users,id',

            // Pricing
            'price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'deposit_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'advance_price' => 'nullable|numeric|min:0',
            'is_convertible' => 'nullable|boolean',
            'convertible_with' => 'nullable|string|max:255',

            // Attributes
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',

            // Features (System)
            'features' => 'nullable|array',
            'features.*' => 'exists:property_attributes,id',

            // Meta (Custom Details & Features)
            'meta' => 'nullable|array',
            'meta.features' => 'nullable|array',
            'meta.features.*' => 'string',
            'meta.details' => 'nullable|array',
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            if ($file->isValid()) {
                try {
                    if ($property->cover_image) {
                        Storage::disk('public')->delete($property->cover_image);
                    }
                    $path = $this->uploadFile($file, 'properties/covers');
                    $data['cover_image'] = $path;
                } catch (\Exception $e) {
                    Log::error('Cover Image Update Failed: ' . $e->getMessage());
                    return back()->withInput()->with('error', 'خطا در آپلود تصویر جدید: ' . $e->getMessage());
                }
            }
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file->isValid()) {
                try {
                    if ($property->video) {
                        Storage::disk('public')->delete($property->video);
                    }
                    $path = $this->uploadFile($file, 'properties/videos');
                    $data['video'] = $path;
                } catch (\Exception $e) {
                    Log::error('Video Update Failed: ' . $e->getMessage());
                }
            }
        }

        // Handle Special Property & Meta
        $meta = $property->meta ?? [];

        // Update is_special
        if ($request->has('is_special')) {
            $meta['is_special'] = true;
        } else {
            $meta['is_special'] = false;
        }

        // Update Custom Details
        if (isset($data['meta']['details'])) {
            $meta['details'] = []; // Reset to overwrite or merge carefully
            foreach ($data['meta']['details'] as $item) {
                if (!empty($item['key'])) {
                    $meta['details'][$item['key']] = $item['value'];
                }
            }
        }

        // Update Custom Features
        if (isset($data['meta']['features'])) {
            $meta['features'] = []; // Reset
            foreach ($data['meta']['features'] as $item) {
                if (!empty($item['value'])) {
                    $meta['features'][] = $item['value'];
                }
            }
        }

        $property->meta = $meta;
        $property->save();

        // Enforce agent logic on update as well
        $user = auth()->user();
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $isAgent = $user->hasAnyRole($agentRoles);
        $isAdmin = $user->hasRole(['super-admin', 'admin']);

        if (!$isAdmin && $isAgent) {
            unset($data['agent_id']);
        }

        $property->update($data);

        // Update Attributes (Details)
        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeId => $value) {
                if (!empty($value)) {
                    PropertyAttributeValue::updateOrCreate(
                        ['property_id' => $property->id, 'attribute_id' => $attributeId],
                        ['value' => $value]
                    );
                } else {
                    PropertyAttributeValue::where('property_id', $property->id)
                        ->where('attribute_id', $attributeId)
                        ->delete();
                }
            }
        }

        // Update Features (System)
        // First, remove all existing system features for this property
        $featureAttributeIds = PropertyAttribute::where('section', 'features')->pluck('id');
        PropertyAttributeValue::where('property_id', $property->id)
            ->whereIn('attribute_id', $featureAttributeIds)
            ->delete();

        // Then add selected ones
        if (isset($data['features'])) {
            foreach ($data['features'] as $featureId) {
                PropertyAttributeValue::create([
                    'property_id' => $property->id,
                    'attribute_id' => $featureId,
                    'value' => '1',
                ]);
            }
        }

        if ($request->hasFile('gallery_images')) {
            $currentCount = $property->images()->count();
            $maxGalleryImages = PropertySetting::get('max_gallery_images', 10);

            if ($currentCount + count($request->file('gallery_images')) > $maxGalleryImages) {
                return back()->with('error', "تعداد تصاویر گالری نمی‌تواند بیشتر از {$maxGalleryImages} باشد.");
            }

            foreach ($request->file('gallery_images') as $index => $image) {
                if ($image->isValid()) {
                    try {
                        $path = $this->uploadFile($image, 'properties/gallery');
                        PropertyImage::create([
                            'property_id' => $property->id,
                            'path' => $path,
                            'sort_order' => $currentCount + $index,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Gallery Image Update Failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return back()->with('success', 'مشخصات ملک با موفقیت ویرایش شد.');
    }

    public function destroy(Property $property)
    {
        // Check permission
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.delete.all') &&
            !($user->can('properties.delete') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        if ($property->cover_image) {
            Storage::disk('public')->delete($property->cover_image);
        }
        if ($property->video) {
            Storage::disk('public')->delete($property->video);
        }
        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $property->delete();
        return redirect()->route('user.properties.index')->with('success', 'ملک حذف شد.');
    }

    public function destroyImage(PropertyImage $image)
    {
        // Check permission via property relation
        $user = auth()->user();
        $property = $image->property;
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['success' => true]);
    }

    public function storeOwner(Request $request)
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

        return response()->json([
            'success' => true,
            'owner' => $owner
        ]);
    }

    public function searchAgents(Request $request)
    {
        $query = $request->get('q');
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);

        if (empty($agentRoles)) {
            return response()->json([]);
        }

        $agents = User::role($agentRoles)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'mobile', 'email']);

        return response()->json($agents);
    }

    private function getPropertyCodePrefix($categoryId = null): string
    {
        $useCategorySlug = PropertySetting::get('property_code_use_category_slug', 0);
        $prefix = PropertySetting::get('property_code_prefix', 'P');
        $separator = PropertySetting::get('property_code_separator', '-');
        $includeYear = PropertySetting::get('property_code_include_year', 1);

        if ($useCategorySlug && $categoryId) {
            $category = PropertyCategory::find($categoryId);
            if ($category && !empty($category->slug)) {
                $prefix = $category->slug;
            }
        }

        $code = '';

        if ($includeYear) {
            $year = Jalalian::now()->getYear();
            $code .= $year . $separator;
        }

        if (!empty($prefix)) {
            $code .= $prefix . $separator;
        }

        return $code;
    }

    private function generateUniquePropertyCode($categoryId = null): string
    {
        $prefixPart = $this->getPropertyCodePrefix($categoryId);

        $lastProperty = Property::withTrashed()
            ->where('code', 'like', "{$prefixPart}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProperty) {
            $lastCode = $lastProperty->code;
            $numberPart = str_replace($prefixPart, '', $lastCode);

            if (is_numeric($numberPart)) {
                $newNumber = intval($numberPart) + 1;
            } else {
                $newNumber = 1001;
            }
        } else {
            $newNumber = 1001;
        }

        $newCode = $prefixPart . $newNumber;

        while (Property::withTrashed()->where('code', $newCode)->exists()) {
            $newNumber++;
            $newCode = $prefixPart . $newNumber;
        }

        return $newCode;
    }

    private function uploadFile($file, $directory)
    {
        $sourcePath = $file->getRealPath();
        if (empty($sourcePath)) {
            $sourcePath = $file->getPathname();
        }

        if (empty($sourcePath) || !file_exists($sourcePath)) {
            throw new \Exception('فایل آپلود شده در مسیر موقت یافت نشد.');
        }

        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(40) . '.' . $extension;
        $path = $directory . '/' . $fileName;

        Storage::disk('public')->put($path, file_get_contents($sourcePath));

        return $path;
    }
}
