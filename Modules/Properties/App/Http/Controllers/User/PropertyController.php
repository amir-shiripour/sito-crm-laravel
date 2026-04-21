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
        $canViewAll = $user->hasRole('super-admin') || $user->can('properties.view.all') || $user->can('properties.manage');

        if ($canViewAll) {
            if (!$request->has('show_all') || $request->show_all != '1') {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('agent_id', $user->id);
                });
            }
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('agent_id', $user->id);
            });
        }

        // Standard Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('publication_status')) {
            $query->where('publication_status', $request->publication_status);
        }
        if ($request->filled('agent_id') && $canViewAll) {
            $query->where('agent_id', $request->agent_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        // AI Search Filters
        if ($request->has('ai_search')) {
            // ** NEW: OR logic for ambiguous prices **
            if ($request->filled('price_min') && $request->filled('deposit_min')) {
                $query->where(function ($q) use ($request) {
                    $q->where('price', '>=', (float)$request->price_min)
                        ->orWhere('deposit_price', '>=', (float)$request->deposit_min);
                });
            } else {
                if ($request->filled('price_min')) $query->where('price', '>=', (float)$request->price_min);
                if ($request->filled('deposit_min')) $query->where('deposit_price', '>=', (float)$request->deposit_min);
            }

            if ($request->filled('price_max')) $query->where('price', '<=', (float)$request->price_max);
            if ($request->filled('deposit_max')) $query->where('deposit_price', '<=', (float)$request->deposit_max);
            if ($request->filled('rent_min')) $query->where('rent_price', '>=', (float)$request->rent_min);
            if ($request->filled('rent_max')) $query->where('rent_price', '<=', (float)$request->rent_max);

            if ($request->has('details') && is_array($request->details)) {
                foreach ($request->details as $attrId => $value) {
                    $query->whereHas('attributeValues', function ($q) use ($attrId, $value) {
                        $q->where('attribute_id', $attrId);
                        if (is_array($value)) {
                            if (isset($value['min'])) $q->whereRaw('CAST(value AS UNSIGNED) >= ?', [$value['min']]);
                            if (isset($value['max'])) $q->whereRaw('CAST(value AS UNSIGNED) <= ?', [$value['max']]);
                        } else {
                            $q->where('value', $value);
                        }
                    });
                }
            }

            if ($request->has('features') && is_array($request->features)) {
                foreach ($request->features as $featureId) {
                    $query->whereHas('attributeValues', function ($q) use ($featureId) {
                        $q->where('attribute_id', $featureId)->where('value', '1');
                    });
                }
            }
        }

        $properties = $query->latest()->paginate(10)->withQueryString();

        // Data for filters
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();
        $categories = PropertyCategory::where('user_id', $user->id)->get();
        $buildings = PropertyBuilding::latest()->get();
        $propertyAttributes = PropertyAttribute::where('is_active', true)->get()->keyBy('id');

        // Agents list for filter
        $agents = [];
        if ($canViewAll) {
            $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
            if (!empty($agentRoles)) {
                $agents = User::role($agentRoles)->get(['id', 'name']);
            }
        }

        return view('properties::user.index', compact('properties', 'statuses', 'agents', 'categories', 'buildings', 'propertyAttributes'));
    }

    public function create()
    {
        $maxGalleryImages = PropertySetting::get('max_gallery_images', 10);
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $agents = User::role($agentRoles)->get();
        $owners = PropertyOwner::latest()->get(); // اضافه شد جهت نمایش در لیست انتخاب مالک
        return view('properties::user.create', compact('maxGalleryImages', 'statuses', 'agents', 'owners'));
    }

    public function store(Request $request)
    {
        // تبدیل تاریخ جلالی
        if ($request->has('delivery_date') && !empty($request->delivery_date)) {
            try { $request->merge(['delivery_date' => Jalalian::fromFormat('Y/m/d', $request->delivery_date)->toCarbon()->format('Y-m-d')]); } catch (\Exception $e) { $request->merge(['delivery_date' => null]); }
        }
        if ($request->has('registered_at') && !empty($request->registered_at)) {
            try { $request->merge(['registered_at' => Jalalian::fromFormat('Y/m/d', $request->registered_at)->toCarbon()->format('Y-m-d')]); } catch (\Exception $e) { $request->merge(['registered_at' => null]); }
        } else { $request->merge(['registered_at' => now()->format('Y-m-d')]); }

        // اصلاح مبالغ عددی (جلوگیری از خطای SQL 1366)
        $priceFields = ['price', 'min_price', 'deposit_price', 'rent_price', 'advance_price'];
        foreach ($priceFields as $field) {
            if ($request->has($field)) {
                $rawPrice = str_replace(',', '', $request->input($field));
                // اگر مقدار خالی بود، نال قرار می‌دهیم نه رشته خالی
                $request->merge([$field => ($rawPrice === '' ? null : $rawPrice)]);
            }
        }

        $maxSize = PropertySetting::get('max_file_size', 10240);
        $allowedTypes = str_replace(' ', '', PropertySetting::get('allowed_file_types', 'jpeg,png,jpg,gif'));

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'listing_type' => 'required|in:sale,presale,rent',
            'property_type' => 'required|in:apartment,villa,land,office',
            'document_type' => 'nullable|string',
            'building_id' => 'nullable|integer',
            'owner_id' => 'nullable|exists:property_owners,id',
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
            'price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'deposit_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'advance_price' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'features' => 'nullable|array',
            'meta' => 'nullable|array',
        ]);

        // جدا کردن داده‌های رابطه‌ای (مانند متد Update)
        $attributesData = $request->input('attributes', []);
        $featuresData = $request->input('features', []);

        $data = $validated;
        unset($data['attributes'], $data['features']);

        // مدیریت کد ملک
        if ($request->input('code_type') === 'auto' || empty($data['code'])) {
            $data['code'] = $this->generateUniquePropertyCode($data['category_id'] ?? null);
        } else {
            $prefix = $this->getPropertyCodePrefix($data['category_id'] ?? null);
            $data['code'] = $prefix . $data['code'];
            if (Property::withTrashed()->where('code', $data['code'])->exists()) return back()->withInput()->with('error', 'کد ملک تکراری است.');
        }

        // آپلود تصویر شاخص
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->uploadFile($request->file('cover_image'), 'properties/covers');
        }

        // آپلود ویدیو
        if ($request->hasFile('video')) {
            $data['video'] = $this->uploadFile($request->file('video'), 'properties/videos');
        }

        $data['created_by'] = auth()->id();
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        if (auth()->user()->hasRole(['super-admin', 'admin']) || !auth()->user()->hasAnyRole($agentRoles)) {
            $data['agent_id'] = $request->input('agent_id') ?: auth()->id();
        } else { $data['agent_id'] = auth()->id(); }

        if (empty($data['status_id'])) {
            $data['status_id'] = PropertyStatus::where('is_default', true)->first()?->id ?? PropertyStatus::where('is_active', true)->orderBy('sort_order')->first()?->id;
        }

        // پردازش Meta (جلوگیری از [object Object])
        $metaRequest = $request->input('meta', []);
        $processedMeta = ['is_special' => $request->has('is_special')];
        if (isset($metaRequest['details'])) foreach ($metaRequest['details'] as $k => $v) if (!empty($k)) $processedMeta['details'][$k] = $v;
        if (isset($metaRequest['features'])) foreach ($metaRequest['features'] as $f) {
            if (is_array($f) && !empty($f['value'])) $processedMeta['features'][] = $f['value'];
            elseif (is_string($f)) $processedMeta['features'][] = $f;
        }
        $data['meta'] = $processedMeta;

        // ایجاد ملک
        $property = Property::create($data);

        // ذخیره گالری
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $idx => $img) {
                if ($idx < PropertySetting::get('max_gallery_images', 10) && $img->isValid()) {
                    PropertyImage::create(['property_id' => $property->id, 'path' => $this->uploadFile($img, 'properties/gallery'), 'sort_order' => $idx]);
                }
            }
        }

        // ذخیره ویژگی‌های استاندارد
        foreach ($attributesData as $id => $val) {
            if (!empty($val)) PropertyAttributeValue::create(['property_id' => $property->id, 'attribute_id' => $id, 'value' => $val]);
        }

        // ذخیره امکانات رفاهی انتخابی
        foreach ($featuresData as $fid) {
            PropertyAttributeValue::create(['property_id' => $property->id, 'attribute_id' => $fid, 'value' => '1']);
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
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        // اصلاح اعتبارسنجی:
        // attributes.* نباید exists باشد چون مقدار ارسال می شود (مثل 1)، نه ID.
        $data = $request->validate([
            'attributes' => 'nullable|array',
            'meta' => 'nullable|array',
            'meta.*.value' => 'nullable|string|max:255',
        ]);

        // ۱. مدیریت امکانات عمومی (سیستمی)
        // دریافت آی‌دی تمام ویژگی‌هایی که مربوط به بخش features هستند
        $featureAttributeIds = PropertyAttribute::where('section', 'features')->pluck('id')->toArray();

        // حذف مقادیر قبلی فقط برای این بخش
        PropertyAttributeValue::where('property_id', $property->id)
            ->whereIn('attribute_id', $featureAttributeIds)
            ->delete();

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeId => $value) {
                if (!empty($value)) {
                    PropertyAttributeValue::create([
                        'property_id' => $property->id,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }
        }

        // ۲. مدیریت امکانات سفارشی (Meta)
        $meta = $property->meta ?? [];
        $meta['features'] = []; // ریست کردن امکانات سفارشی قبلی

        if (isset($request->meta) && is_array($request->meta)) {
            foreach ($request->meta as $item) {
                if (is_array($item) && !empty($item['value'])) {
                    $meta['features'][] = $item['value'];
                }
            }
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
        // بررسی دسترسی کاربر
        $user = auth()->user();
        $isOwnerOrAgent = $property->created_by === $user->id || $property->agent_id === $user->id;

        if (!$user->hasRole('super-admin') &&
            !$user->can('properties.edit.all') &&
            !($user->can('properties.edit') && ($isOwnerOrAgent || $user->can('properties.manage')))) {
            abort(403);
        }

        // تبدیل تاریخ‌های جلالی به میلادی قبل از اعتبارسنجی
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

        // تمیز کردن فیلدهای قیمت (حذف جداکننده هزارگان)
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
            'price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'deposit_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'advance_price' => 'nullable|numeric|min:0',
            'is_convertible' => 'nullable|boolean',
            'convertible_with' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'features.*' => 'exists:property_attributes,id',
            'meta' => 'nullable|array',
            'meta.features' => 'nullable|array',
            'meta.features.*.value' => 'nullable|string|max:255',
            'meta.details' => 'nullable|array',
            'meta.details.*.key' => 'required_with:meta.details.*.value|string|max:255',
            'meta.details.*.value' => 'nullable|string|max:255',
        ]);

        // آپلود تصویر اصلی
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            if ($file->isValid()) {
                if ($property->cover_image) {
                    Storage::disk('public')->delete($property->cover_image);
                }
                $data['cover_image'] = $this->uploadFile($file, 'properties/covers');
            }
        }

        // آپلود ویدیو
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file->isValid()) {
                if ($property->video) {
                    Storage::disk('public')->delete($property->video);
                }
                $data['video'] = $this->uploadFile($file, 'properties/videos');
            }
        }

        // --- مدیریت داده‌های متا (Meta) ---
        $processedMeta = $property->meta ?? [];
        $processedMeta['is_special'] = $request->has('is_special');

        // ابتدا آرایه‌ها را خالی می‌کنیم تا اگر در درخواست نبودند (حذف کامل)، در دیتابیس هم پاک شوند
        $processedMeta['details'] = [];
        $processedMeta['features'] = [];

        // پردازش جزئیات سفارشی در صورت وجود در درخواست
        if (isset($request->meta['details']) && is_array($request->meta['details'])) {
            foreach ($request->meta['details'] as $item) {
                if (!empty($item['key'])) {
                    $processedMeta['details'][$item['key']] = $item['value'] ?? '';
                }
            }
        }

        // پردازش امکانات سفارشی در صورت وجود در درخواست
        if (isset($request->meta['features']) && is_array($request->meta['features'])) {
            foreach ($request->meta['features'] as $item) {
                if (is_array($item) && !empty($item['value'])) {
                    $processedMeta['features'][] = $item['value'];
                }
            }
        }

        // جایگزینی متای نهایی در آرایه داده‌ها
        $data['meta'] = $processedMeta;

        // محدودیت تغییر مشاور برای غیر ادمین‌ها
        $isAdmin = $user->hasRole(['super-admin', 'admin']);
        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        if (!$isAdmin && $user->hasAnyRole($agentRoles)) {
            unset($data['agent_id']);
        }

        // بروزرسانی مدل ملک
        $property->update($data);

        // بروزرسانی ویژگی‌های سیستمی (Attributes)
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

        // بروزرسانی امکانات سیستمی (System Features)
        $featureIds = PropertyAttribute::where('section', 'features')->pluck('id');
        PropertyAttributeValue::where('property_id', $property->id)->whereIn('attribute_id', $featureIds)->delete();

        if (isset($data['features'])) {
            foreach ($data['features'] as $fId) {
                PropertyAttributeValue::create([
                    'property_id' => $property->id,
                    'attribute_id' => $fId,
                    'value' => '1',
                ]);
            }
        }

        // مدیریت گالری تصاویر
        if ($request->hasFile('gallery_images')) {
            $currentCount = $property->images()->count();
            $maxGallery = PropertySetting::get('max_gallery_images', 10);
            if ($currentCount + count($request->file('gallery_images')) <= $maxGallery) {
                foreach ($request->file('gallery_images') as $idx => $img) {
                    if ($img->isValid()) {
                        PropertyImage::create([
                            'property_id' => $property->id,
                            'path' => $this->uploadFile($img, 'properties/gallery'),
                            'sort_order' => $currentCount + $idx,
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'تغییرات ملک با موفقیت ذخیره شد.');
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
