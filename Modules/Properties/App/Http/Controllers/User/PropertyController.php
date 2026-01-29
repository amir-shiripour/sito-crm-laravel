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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with('status', 'creator')->latest()->paginate(10);
        return view('properties::user.index', compact('properties'));
    }

    public function create()
    {
        $maxGalleryImages = PropertySetting::get('max_gallery_images', 10);
        $statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get();
        return view('properties::user.create', compact('maxGalleryImages', 'statuses'));
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
        ]);

        // Handle Property Code
        if ($request->input('code_type') === 'auto' || empty($data['code'])) {
            $data['code'] = $this->generateUniquePropertyCode();
        } else {
            $prefix = $this->getPropertyCodePrefix();
            $data['code'] = $prefix . $data['code'];

            if (Property::where('code', $data['code'])->exists()) {
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

        $data['created_by'] = auth()->id();

        $property = Property::create($data);

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
        $currency = PropertySetting::get('currency', 'toman');
        return view('properties::user.pricing', compact('property', 'currency'));
    }

    public function updatePricing(Request $request, Property $property)
    {
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

        return view('properties::user.edit', compact('property', 'maxGalleryImages', 'customDetails', 'customFeatures', 'owners', 'statuses'));
    }

    public function update(Request $request, Property $property)
    {
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

        $property->update($data);

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
        if ($image->property->created_by !== auth()->id()) {
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

    private function getPropertyCodePrefix(): string
    {
        $prefix = PropertySetting::get('property_code_prefix', 'P');
        $separator = PropertySetting::get('property_code_separator', '-');
        $includeYear = PropertySetting::get('property_code_include_year', 1);

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

    private function generateUniquePropertyCode(): string
    {
        $prefixPart = $this->getPropertyCodePrefix();

        $lastProperty = Property::where('code', 'like', "{$prefixPart}%")
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

        return $prefixPart . $newNumber;
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
