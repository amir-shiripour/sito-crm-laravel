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

            $prompt = "
            کاربر متنی برای جستجوی ملک وارد کرده است:
            \"$query\"

            لطفاً پارامترهای جستجو را استخراج کن و به صورت JSON برگردان.
            فیلدها:
            - type: نوع ملک (apartment, villa, land, office)
            - min_price: حداقل قیمت (تومان)
            - max_price: حداکثر قیمت (تومان)
            - min_area: حداقل متراژ
            - max_area: حداکثر متراژ
            - bedrooms: تعداد خواب
            - keyword: کلمات کلیدی مهم (مثل منطقه، خیابان و...)

            نکات:
            - اعداد فارسی را به انگلیسی تبدیل کن.
            - قیمت‌ها را به تومان کامل تبدیل کن.
            ";

            $response = $this->gapGPT->ask($prompt, "تو یک دستیار هوشمند جستجوی املاک هستی.");

            if (!$response) {
                return response()->json(['error' => 'خطا در دریافت پاسخ.'], 500);
            }

            $jsonStr = $this->cleanJson($response);
            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'پاسخ نامعتبر.', 'raw' => $response], 500);
            }

            $queryParams = [];
            if (!empty($data['type'])) $queryParams['type'] = $data['type'];
            if (!empty($data['min_price'])) $queryParams['min_price'] = $data['min_price'];
            if (!empty($data['max_price'])) $queryParams['max_price'] = $data['max_price'];
            if (!empty($data['min_area'])) $queryParams['min_area'] = $data['min_area'];
            if (!empty($data['max_area'])) $queryParams['max_area'] = $data['max_area'];
            if (!empty($data['bedrooms'])) $queryParams['bedrooms'] = $data['bedrooms'];
            if (!empty($data['keyword'])) $queryParams['q'] = $data['keyword'];

            $redirectUrl = route('user.properties.index', $queryParams);

            return response()->json([
                'data' => $data,
                'redirect_url' => $redirectUrl
            ]);

        } catch (\Exception $e) {
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
