<?php

namespace Modules\Properties\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyAttribute;
use Modules\Properties\Entities\PropertySetting;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['status', 'attributeValues'])->latest();

        // 1. جستجوی متنی
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 2. نوع معامله
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // 3. نوع ملک
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // 4. نوع سند
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // 5. فیلتر قیمت
        $listingType = $request->get('listing_type');

        if ($listingType === 'rent') {
            if ($request->filled('min_deposit_price')) {
                $query->where('deposit_price', '>=', $request->min_deposit_price);
            }
            if ($request->filled('max_deposit_price')) {
                $query->where('deposit_price', '<=', $request->max_deposit_price);
            }
            if ($request->filled('min_rent_price')) {
                $query->where('rent_price', '>=', $request->min_rent_price);
            }
            if ($request->filled('max_rent_price')) {
                $query->where('rent_price', '<=', $request->max_rent_price);
            }
        } else {
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
        }

        // 6. فیلتر متراژ
        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }
        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        // 7. فیلتر ویژگی‌های داینامیک
        $attributes = PropertyAttribute::where('is_filterable', true)
            ->where('is_active', true)
            ->get();

        foreach ($attributes as $attribute) {
            if ($attribute->is_range_filter && $attribute->type === 'number') {
                $minInput = 'min_attr_' . $attribute->id;
                $maxInput = 'max_attr_' . $attribute->id;

                if ($request->filled($minInput) || $request->filled($maxInput)) {
                    $query->whereHas('attributeValues', function ($q) use ($attribute, $request, $minInput, $maxInput) {
                        $q->where('attribute_id', $attribute->id);
                        if ($request->filled($minInput)) {
                            $q->whereRaw('CAST(value AS DECIMAL) >= ?', [$request->input($minInput)]);
                        }
                        if ($request->filled($maxInput)) {
                            $q->whereRaw('CAST(value AS DECIMAL) <= ?', [$request->input($maxInput)]);
                        }
                    });
                }
            } elseif ($attribute->section === 'features') {
                if ($request->filled('features') && is_array($request->features)) {
                    if (in_array($attribute->id, $request->features)) {
                        $query->whereHas('attributeValues', function ($q) use ($attribute) {
                            $q->where('attribute_id', $attribute->id)
                              ->where('value', '1');
                        });
                    }
                }
            } else {
                $inputName = 'attr_' . $attribute->id;
                if ($request->filled($inputName)) {
                    $value = $request->input($inputName);
                    $query->whereHas('attributeValues', function ($q) use ($attribute, $value) {
                        $q->where('attribute_id', $attribute->id);
                        if ($attribute->type === 'text') {
                            $q->where('value', 'like', "%{$value}%");
                        } else {
                            $q->where('value', $value);
                        }
                    });
                }
            }
        }

        // 8. فیلتر املاک ویژه
        if ($request->filled('special') && $request->special == '1') {
            $query->where('meta->is_special', true);
        }

        $properties = $query->paginate(12)->withQueryString();
        $showFeatures = PropertySetting::get('show_features_in_card', 1);

        $filterableAttributes = PropertyAttribute::where('is_filterable', true)
            ->where('section', 'details')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $features = PropertyAttribute::where('section', 'features')
            ->where('is_active', true)
            ->where('is_filterable', true)
            ->orderBy('sort_order')
            ->get();

        Log::info('Features found for filter: ' . $features->count());

        return view('properties::index', compact('properties', 'showFeatures', 'filterableAttributes', 'features'));
    }

    public function show($slug)
    {
        Log::info("Show Property Slug: " . $slug);

        // The slug format is YmdHis-CODE (e.g., 20260129171118-1404-P-1002)
        // The timestamp is always 14 characters long.
        // So the identifier starts from index 15 (14 chars + 1 dash).

        if (strlen($slug) > 15) {
            $identifier = substr($slug, 15);
        } else {
            // Fallback for old links or weird formats
            $parts = explode('-', $slug);
            $identifier = end($parts);
        }

        Log::info("Extracted Identifier: " . $identifier);

        // Try to find by code first, then by id if code is not found or identifier is numeric
        $property = Property::with(['status', 'creator', 'attributeValues.attribute', 'images', 'owner'])
            ->where(function($query) use ($identifier) {
                $query->where('code', $identifier)
                      ->orWhere('id', $identifier);
            })
            ->first();

        if (!$property) {
            Log::error("Property not found for identifier: " . $identifier);
            abort(404);
        }

        return view('properties::show', compact('property'));
    }
}
