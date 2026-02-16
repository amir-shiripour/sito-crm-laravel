<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Services\GapGPTService;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyAttribute;

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
                - deposit_price: مبلغ رهن.
                - rent_price: مبلغ اجاره.
                - advance_price: مبلغ پیش‌پرداخت (پیش‌فروش).

            15. details: آبجکتی که کلید آن ID ویژگی و مقدار آن مقدار استخراج شده است.
                لیست ویژگی‌ها (ID:Name): [$detailAttributesList]
                مثال: {\"5\": \"2\", \"8\": \"طبقه 3\"} (اگر ویژگی پیدا نشد، ننویس).

            16. features: آرایه‌ای از ID امکانات موجود در متن.
                لیست امکانات (ID:Name): [$featureAttributesList]
                مثال: [1, 4, 12]

            نکات:
            - قیمت‌ها را به عدد کامل تبدیل کن (مثلاً '2 میلیارد' -> 2000000000).
            - اگر کلماتی مثل 'اجاره'، 'رهن' دیدی، listing_type را rent بگذار.
            ";

            $response = $this->gapGPT->ask($prompt, "تو یک دستیار هوشمند املاک هستی.");

            if (!$response) {
                return response()->json(['error' => 'پاسخی از سرویس هوش مصنوعی دریافت نشد.'], 500);
            }

            $jsonStr = $this->cleanJson($response);
            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'فرمت پاسخ دریافتی معتبر نیست.',
                    'raw_response' => $response
                ], 500);
            }

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
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
