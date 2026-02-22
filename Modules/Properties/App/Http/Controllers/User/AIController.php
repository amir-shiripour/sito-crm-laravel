<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Services\GapGPTService;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyAttribute;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $gapGPT;

    public function __construct(GapGPTService $gapGPT)
    {
        $this->gapGPT = $gapGPT;
    }

    public function completeProperty(Request $request)
    {
        try {
            // بررسی فعال بودن قابلیت
            if (!PropertySetting::get('ai_property_completion', 0)) {
                return response()->json(['error' => 'قابلیت تکمیل هوشمند در تنظیمات غیرفعال است.'], 403);
            }

            $request->validate([
                'description' => 'required|string|min:10',
            ]);

            $description = $request->description;

            Log::info("AI Completion Started for description: " . substr($description, 0, 50) . "...");

            // دریافت دسته‌بندی‌ها
            $categories = PropertyCategory::pluck('name', 'id')->toArray();

            // دریافت انواع سند
            $documentTypes = Property::DOCUMENT_TYPES;
            $documentTypesStr = implode(', ', array_map(
                fn($k, $v) => "$k ($v)",
                array_keys($documentTypes),
                $documentTypes
            ));

            // دریافت ویژگی‌های جزئیات (Details)
            $detailAttributes = PropertyAttribute::where('section', 'details')->where('is_active', true)->get(['id', 'name']);
            $detailAttributesList = $detailAttributes->map(fn($a) => "{$a->id}:{$a->name}")->implode(', ');

            // دریافت امکانات (Features)
            $featureAttributes = PropertyAttribute::where('section', 'features')->where('is_active', true)->get(['id', 'name']);
            $featureAttributesList = $featureAttributes->map(fn($a) => "{$a->id}:{$a->name}")->implode(', ');

            $prompt = "
            من یک متن توضیحات ملک دارم و می‌خواهم تمام اطلاعات ممکن را از آن استخراج کنی.
            توضیحات:
            \"$description\"

            لطفاً خروجی را فقط و فقط به صورت یک آبجکت JSON معتبر برگردان (بدون هیچ متن اضافه).
            فیلدها:

            1. title: عنوان جذاب (فارسی).
            2. listing_type: نوع معامله (sale, rent, presale).
            3. property_type: نوع ملک (apartment, villa, land, office).
            4. document_type: نوع سند [$documentTypesStr].
            5. usage_type: کاربری (residential, commercial, industrial, agricultural).
            6. delivery_date: تاریخ تحویل (YYYY/MM/DD).
            7. description: توضیحات تکمیلی مرتب شده.
            8. code: کد ملک.
            9. is_special: ویژه بودن (true/false).
            10. confidential_notes: یادداشت محرمانه.
            11. owner_name: نام مالک.
            12. building_name: نام ساختمان.
            13. address: آدرس دقیق.

            14. prices: آبجکتی شامل قیمت‌ها (اعداد به تومان):
                - price: قیمت کل (فروش/پیش‌فروش).
                - min_price: حداقل قیمت.
                - deposit_price: مبلغ رهن (ودیعه).
                - rent_price: مبلغ اجاره ماهانه.
                - advance_price: مبلغ پیش‌پرداخت (پیش‌فروش).

            15. details: آبجکتی که کلید آن ID ویژگی و مقدار آن مقدار استخراج شده است.
                لیست ویژگی‌های موجود (ID:Name): [$detailAttributesList]
                مثال: {\"5\": \"2\", \"8\": \"طبقه 3\"}

            16. custom_details: آبجکتی شامل ویژگی‌هایی که در لیست بالا نبودند (کلید: نام ویژگی، مقدار: مقدار ویژگی).
                مثال: {\"تعداد خواب\": \"3\", \"جهت ساختمان\": \"جنوبی\"}

            17. features: آرایه‌ای از ID امکانات موجود در متن.
                لیست امکانات موجود (ID:Name): [$featureAttributesList]
                مثال: [1, 4, 12]

            18. custom_features: آرایه‌ای از نام امکاناتی که در لیست بالا نبودند.
                مثال: [\"استخر\", \"روف گاردن\", \"مستر\"]

            نکات مهم:
            - قیمت‌ها را به عدد کامل تبدیل کن (مثلاً '2 میلیارد' -> 2000000000).
            - اگر کلماتی مثل 'اجاره'، 'رهن' دیدی، listing_type را rent بگذار.
            - اگر listing_type برابر rent است:
                - قیمت‌های کلان (رهن/ودیعه) را در deposit_price قرار بده.
                - قیمت‌های ماهانه (اجاره) را در rent_price قرار بده.
                - فیلد price را 0 بگذار.
            - فقط از ID های موجود در لیست details و features استفاده کن. اگر موردی در لیست نبود، حتماً در custom_details یا custom_features قرار بده.
            - اگر قیمت به صورت '2 میلیارد و 300' بود، آن را به عدد کامل '2300000000' تبدیل کن.
            - اگر امکاناتی مثل 'استخر'، 'سونا'، 'جکوزی' یا هر امکانات رفاهی دیگری در متن بود که در لیست features نبود، حتماً نام آن را در آرایه custom_features قرار بده.
            ";

            // استفاده از تایم‌اوت پیش‌فرض سرویس (که از تنظیمات خوانده می‌شود)
            // اما توکن را بالا نگه می‌داریم چون پاسخ طولانی است
            $response = $this->gapGPT->ask($prompt, "تو یک دستیار هوشمند املاک هستی.", null, 4000);

            if (!$response) {
                Log::error("AI Completion Failed: No response received.");
                return response()->json(['error' => 'پاسخی از سرویس هوش مصنوعی دریافت نشد. لطفاً لاگ‌ها را بررسی کنید.'], 500);
            }

            $jsonStr = $this->cleanJson($response);
            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("AI Completion JSON Error: " . json_last_error_msg() . " | Raw: " . substr($response, 0, 100));
                return response()->json([
                    'error' => 'فرمت پاسخ دریافتی معتبر نیست.',
                    'raw_response' => $response
                ], 500);
            }

            Log::info("AI Completion Successful. Data: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            Log::error("AI Completion Exception: " . $e->getMessage());
            return response()->json([
                'error' => 'خطای سرور: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchProperty(Request $request)
    {
        try {
            if (!PropertySetting::get('ai_property_search', 0)) {
                return response()->json(['error' => 'قابلیت جستجوی هوشمند غیرفعال است.'], 403);
            }

            $request->validate([
                'query' => 'required|string|min:3',
            ]);

            $query = $request->input('query');
            Log::info("AI Search Started for query: " . $query);

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

            $response = $this->gapGPT->ask($prompt, "You are an expert real estate search query parser.", null, 2000);

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
                foreach ($data['prices'] as $key => $value) {
                    if(!empty($value)) $queryParams[$key] = $value;
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

            $redirectUrl = route('user.properties.index', $queryParams);

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
