<?php

namespace Modules\Properties\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyAttribute;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\PropertyBuilding;
use App\Services\GapGPTService;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        // Start with the visibleToUser scope
        $query = Property::visibleToUser()->with(['status', 'attributeValues', 'category', 'building'])->latest();

        // Check if user has permission to see all properties and requested to do so
        $user = auth()->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('properties.view.all'))) {
            if ($request->has('show_all') && $request->show_all == '1') {
                // Show all: No additional restriction needed as visibleToUser() returns all for admins.
            } else {
                // Default for admins/managers: Restrict to their own properties (Mine only)
                // We need to explicitly apply this restriction because visibleToUser() returns ALL for admins.
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            }
        } else {
            // Guest user or restricted user (Agent)
            // visibleToUser() handles the restriction for Agents.
            // For Guests, we need to ensure only published properties are shown.
            if (!$user) {
                 $query->where('publication_status', 'published');
            }
        }

        $this->applyFilters($query, $request);

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

        // Fetch Categories and Buildings for filter
        $categories = PropertyCategory::all();
        $buildings = PropertyBuilding::latest()->get();

        return view('properties::index', compact('properties', 'showFeatures', 'filterableAttributes', 'features', 'categories', 'buildings'));
    }

    public function map(Request $request)
    {
        // Start with the visibleToUser scope
        $query = Property::with(['status', 'attributeValues', 'category', 'building'])->latest();

        // Check if user has permission to see all properties and requested to do so
        $user = auth()->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('properties.view.all'))) {
            if ($request->has('show_all') && $request->show_all == '1') {
                // Show all
            } else {
                // Default for admins/managers: Restrict to their own properties (Mine only)
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            }
        } else {
            // Guest user or restricted user
            if ($user) {
                // User CANNOT see all (e.g. Agent). Always restrict.
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            } else {
                // Guest user
                $query->where('publication_status', 'published');
            }
        }

        // Only properties with coordinates
        $query->whereNotNull('latitude')->whereNotNull('longitude');

        $this->applyFilters($query, $request);

        $properties = $query->get(); // Get all for map (or limit if too many)

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

        // Office Location
        $officeLocation = [
            'lat' => PropertySetting::get('office_location_lat'),
            'lng' => PropertySetting::get('office_location_lng'),
            'title' => PropertySetting::get('office_location_title', 'دفتر مرکزی'),
        ];

        return view('properties::map', compact('properties', 'filterableAttributes', 'features', 'officeLocation'));
    }

    private function applyFilters($query, Request $request)
    {
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

        // 9. Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 10. Building
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
    }

    public function show($slug)
    {
        Log::info("Show Property Slug: " . $slug);

        if (strlen($slug) > 15) {
            $identifier = substr($slug, 15);
        } else {
            $parts = explode('-', $slug);
            $identifier = end($parts);
        }

        // Added 'building' to eager loading
        $property = Property::with(['status', 'creator', 'attributeValues.attribute', 'images', 'owner', 'category', 'building'])
            ->where(function($query) use ($identifier) {
                $query->where('code', $identifier)
                      ->orWhere('id', $identifier);
            })
            ->first();

        if (!$property) {
            abort(404);
        }

        // Check visibility
        $user = auth()->user();
        if ($user) {
             if (!$user->hasRole('super-admin') && !$user->can('properties.view.all')) {
                 if ($property->created_by !== $user->id && $property->agent_id !== $user->id) {
                     abort(403);
                 }
             }
        } else {
            if ($property->publication_status !== 'published') {
                abort(404);
            }
        }

        // Fetch Visibility Settings
        $visibilitySettings = [
            'owner_info' => json_decode(PropertySetting::get('visibility_owner_info', '[]'), true),
            'confidential_notes' => json_decode(PropertySetting::get('visibility_confidential_notes', '[]'), true),
            'price_info' => json_decode(PropertySetting::get('visibility_price_info', '[]'), true),
            'map_info' => json_decode(PropertySetting::get('visibility_map_info', '[]'), true),
        ];

        return view('properties::show', compact('property', 'visibilitySettings'));
    }

    public function aiSearch(Request $request, GapGPTService $gapGPT)
    {
        try {
            if (!PropertySetting::get('ai_property_search', 0)) {
                return response()->json(['error' => 'قابلیت جستجوی هوشمند غیرفعال است.'], 403);
            }

            $request->validate([
                'query' => 'required|string|min:3',
            ]);

            $query = $request->input('query');
            Log::info("Public AI Search Started for query: " . $query);

            // Fetch dynamic data for the prompt
            $categories = PropertyCategory::pluck('name', 'id')->toArray();
            $categoriesList = implode(', ', array_map(fn($k, $v) => "$k:$v", array_keys($categories), $categories));

            $detailAttributes = PropertyAttribute::where('section', 'details')->where('is_active', true)->get(['id', 'name', 'type']);
            $detailAttributesList = $detailAttributes->map(fn($a) => "{$a->id}:{$a->name}:{$a->type}")->implode('; ');

            $featureAttributes = PropertyAttribute::where('section', 'features')->where('is_active', true)->get(['id', 'name']);
            $featureAttributesList = $featureAttributes->map(fn($a) => "{$a->id}:{$a->name}")->implode('; ');

            $prompt = "
            You are an intelligent real estate search assistant. A user has provided a text description of a property they are looking for in Persian. Your task is to extract all possible search parameters from this text and return them as a valid JSON object.

            User's query:
            \"$query\"

            Please extract the following fields. The output must be ONLY a valid JSON object, with no extra text or markdown formatting.

            1.  `search`: Specific keywords ONLY. This should include locations (e.g., \"سعادت آباد\", \"فرشته\"), building names, or unique features. **Do NOT put generic terms like 'آپارتمان' or 'فروشی' in this field if you have already identified `property_type` or `listing_type`.**
            2.  `listing_type`: The type of listing. Possible values: `sale`, `rent`, `presale`. If not specified, leave it null.
            3.  `property_type`: The type of property. Possible values: `apartment`, `villa`, `land`, `office`.
            4.  `category_id`: If the user mentions a category, provide its ID from this list (ID:Name): [$categoriesList].
            5.  `prices`: An object containing price information in Toman. Convert all values to numbers.
                -   `price_min`: Minimum total price (for sale/presale).
                -   `price_max`: Maximum total price (for sale/presale).
                -   `deposit_min`: Minimum deposit/mortgage price (for rent).
                -   `deposit_max`: Maximum deposit/mortgage price (for rent).
                -   `rent_min`: Minimum monthly rent.
                -   `rent_max`: Maximum monthly rent.
            6.  `details`: An object where the key is the attribute ID and the value is the desired value. Extract values for the following attributes if mentioned.
                List of available detail attributes (ID:Name:Type): [$detailAttributesList]
                - For numeric types (like area, bedrooms, age), the value should be an object like `{\"min\": 100, \"max\": 120}` or `{\"min\": 3}`.
                - For boolean types, use `true` or `false`.
                - For select types, use the string value mentioned.
                Example: `{\"5\": {\"min\": 2, \"max\": 3}, \"8\": \"شمالی\"}` (e.g., 5 is bedrooms, 8 is direction).
            7.  `features`: An array of IDs for required features from the list below.
                List of available features (ID:Name): [$featureAttributesList]
                Example: `[1, 4, 12]`

            Important Notes:
            - Convert prices like '2.5 میلیارد' to `2500000000`.
            - If a range is mentioned (e.g., \"بین 100 تا 120 متر\"), extract `min` and `max`. If a single value is given (e.g., \"حدود 100 متر\"), you can set `min` to that value.
            - **CRITICAL LOGIC FOR AMBIGUOUS PRICES:**
                - If the user mentions a price but **does not** specify 'فروش', 'خرید', 'اجاره', or 'رهن', you must consider both possibilities.
                - **Example:** For a query 'آپارتمان از 30 میلیون', the user's intent is unclear. It could be a sale OR a rent.
                - In this case, populate **both** the sale and rent price fields. Set `prices.price_min` to `30000000` AND `prices.deposit_min` to `30000000`.
                - If the user explicitly says 'فروش' or 'خرید', only populate the `price_min`/`price_max` fields.
                - If the user explicitly says 'رهن' or 'اجاره', only populate the `deposit_min`/`deposit_max` and `rent_min`/`rent_max` fields.
            - The final output must be only the JSON object. Do not include any explanation.
            ";

            $response = $gapGPT->ask($prompt, "You are an expert real estate search query parser.", null, 2000);

            if (!$response) {
                Log::error("AI Search Failed: No response from service.");
                return response()->json(['error' => 'پاسخی از سرویس هوش مصنوعی دریافت نشد.'], 500);
            }

            $jsonStr = $this->cleanJson($response);
            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("AI Search JSON Error: " . json_last_error_msg() . " | Raw: " . substr($response, 0, 100));
                return response()->json(['error' => 'پاسخ نامعتبر از سرویس هوش مصنوعی.', 'raw' => $response], 500);
            }

            Log::info("AI Search Parsed Data: " . json_encode($data, JSON_UNESCAPED_UNICODE));

            $queryParams = [];
            if (!empty($data['search'])) $queryParams['search'] = $data['search'];
            if (!empty($data['listing_type'])) $queryParams['listing_type'] = $data['listing_type'];
            if (!empty($data['property_type'])) $queryParams['property_type'] = $data['property_type'];
            if (!empty($data['category_id'])) $queryParams['category_id'] = $data['category_id'];

            // Flatten prices, details, and features for query string
            if (!empty($data['prices'])) {
                $priceMapping = [
                    'price_min' => 'min_price',
                    'price_max' => 'max_price',
                    'deposit_min' => 'min_deposit_price',
                    'deposit_max' => 'max_deposit_price',
                    'rent_min' => 'min_rent_price',
                    'rent_max' => 'max_rent_price',
                ];
                foreach ($data['prices'] as $key => $value) {
                    if (!empty($value) && isset($priceMapping[$key])) {
                        $queryParams[$priceMapping[$key]] = $value;
                    }
                }
            }
            if (!empty($data['details'])) {
                $queryParams['details'] = $data['details'];
            }
            if (!empty($data['features'])) {
                $queryParams['features'] = $data['features'];
            }

            // Add a flag to indicate this is an AI search
            $queryParams['ai_search'] = 1;

            $redirectUrl = route('properties.index', $queryParams);

            return response()->json([
                'data' => $data,
                'redirect_url' => $redirectUrl
            ]);

        } catch (\Exception $e) {
            Log::error("AI Search Exception: " . $e->getMessage() . ' (SQL: ' . (method_exists($e, 'getSql') ? $e->getSql() : 'N/A') . ')');
            return response()->json(['error' => 'خطای سرور: ' . $e->getMessage()], 500);
        }
    }

    private function cleanJson($string)
    {
        $string = preg_replace('/^```json\s*/i', '', $string);
        $string = preg_replace('/^```\s*/i', '', $string);
        $string = preg_replace('/\s*```$/', '', $string);
        return trim($string);
    }
}
