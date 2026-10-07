<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalConfig;
use Modules\Properties\Entities\PropertySetting;

use Morilog\Jalali\Jalalian;

class RentalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * بررسی دسترسی کاربر به این ملک
     */
    protected function checkAccess(Property $property)
    {
        $user = auth()->user();
        if ($user->hasRole(['super-admin', 'admin']) || $user->can('properties.manage') || $user->can('properties.rental.manage')) {
            return true;
        }

        // اگر سازنده ملک است یا مشاور منتسب است یا میزبان ملک است
        $isCreator = $property->created_by === $user->id;
        $isAgent = $property->agent_id === $user->id;
        $isHost = $property->host && $property->host->user_id === $user->id;

        if (!$isCreator && !$isAgent && !$isHost) {
            abort(403, 'شما دسترسی لازم برای ویرایش این اقامتگاه را ندارید.');
        }

        // اگر کاربر میزبان این اقامتگاه است، وضعیت حسابش بررسی شود
        if ($isHost && $property->host && $property->host->status !== 'active') {
            abort(403, 'حساب میزبانی شما هنوز فعال نشده است. تنظیمات قیمت‌گذاری و اقامتگاه پس از تأیید حساب توسط مدیریت امکان‌پذیر خواهد بود.');
        }

        return true;
    }

    /**
     * صفحه تنظیمات اقامتگاه (ظرفیت، قیمت شبانه، قوانین و امکانات)
     */
    public function config(Request $request, Property $property)
    {
        $this->checkAccess($property);

        $currency = PropertySetting::get('currency', 'toman');
        $rentalConfig = $property->rentalConfig ?? new PropertyRentalConfig([
            'property_id' => $property->id,
            'base_guests' => 2,
            'max_guests' => 4,
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'min_stay_nights' => 1,
            'weekend_days' => ['4', '5'],
            'instant_booking' => false,
        ]);

        // نقشه روزهای هفته (۰: شنبه تا ۶: جمعه)
        $weekDays = [
            '0' => 'شنبه',
            '1' => 'یک‌شنبه',
            '2' => 'دوشنبه',
            '3' => 'سه‌شنبه',
            '4' => 'چهارشنبه',
            '5' => 'پنج‌شنبه',
            '6' => 'جمعه',
        ];

        // قیمت‌های ویژه تاریخ‌های خاص برای این اقامتگاه
        $specialPrices = $property->seasonalPrices()->orderBy('start_date')->get()->map(function ($p) {
            $isSingle = $p->start_date->format('Y-m-d') === $p->end_date->format('Y-m-d');
            return [
                'id' => $p->id,
                'title' => $p->title ?? '',
                'type' => $isSingle ? 'single' : 'range',
                'start_date' => Jalalian::fromCarbon($p->start_date)->format('Y/m/d'),
                'end_date' => Jalalian::fromCarbon($p->end_date)->format('Y/m/d'),
                'price' => number_format((float) $p->price_per_night),
            ];
        });

        // قوانین پیش‌فرض اقامتگاه
        $defaultRules = [
            'no_smoking' => 'استعمال دخانیات ممنوع',
            'no_pets' => 'ورود حیوانات خانگی ممنوع',
            'no_party' => 'برگزاری جشن و مهمانی ممنوع',
            'national_card_required' => 'ارائه کارت ملی هوشمند الزامی است',
            'quiet_hours' => 'رعایت آرامش و سکوت بعد از ساعت ۱۲ شب',
            'couple_rules' => 'پذیرش گروه‌های مجردی با هماهنگی قبلی',
        ];

        $isWizard = $request->query('wizard') == '1' || $request->query('from') === 'create';

        // بررسی وجود نسخه ویرایش در حال بررسی (Staging Revision)
        $pendingRevision = $property->pendingRevision;
        if ($pendingRevision && $pendingRevision->type === 'rental_config_update' && is_array($pendingRevision->new_data)) {
            $stagedData = $pendingRevision->new_data;

            // ساخت یک نمونه شبیه‌سازی‌شده از تنظیمات بر پایه اطلاعات در حال بررسی
            $mergedAttributes = array_merge($rentalConfig->toArray(), $stagedData);
            $rentalConfig = new PropertyRentalConfig($mergedAttributes);
            $rentalConfig->id = $property->rentalConfig?->id;
            $rentalConfig->property_id = $property->id;

            // اگر نرخ‌های ویژه تقویم نیز در بازبینی در انتظار ثبت شده بودند
            if (isset($stagedData['special_prices']) && is_array($stagedData['special_prices'])) {
                $specialPrices = collect($stagedData['special_prices'])->map(function ($p, $idx) {
                    $isSingle = ($p['type'] ?? 'single') === 'single' || empty($p['end_date']) || $p['start_date'] === $p['end_date'];
                    return [
                        'id' => $p['id'] ?? null,
                        'title' => $p['title'] ?? '',
                        'type' => $isSingle ? 'single' : 'range',
                        'start_date' => $p['start_date'] ?? '',
                        'end_date' => $p['end_date'] ?? ($p['start_date'] ?? ''),
                        'price' => number_format((float) str_replace(',', '', (string)($p['price'] ?? 0))),
                    ];
                });
            }
        }

        // تفکیک قوانین پیش‌فرض و قوانین سفارشی کاربر
        $allRules = $rentalConfig->house_rules ?? [];
        $defaultRuleKeys = array_keys($defaultRules);
        $selectedDefaultRules = array_values(array_intersect($allRules, $defaultRuleKeys));
        $existingCustomRules = array_values(array_diff($allRules, $defaultRuleKeys));

        return view('properties::user.rental.config', compact(
            'property',
            'rentalConfig',
            'currency',
            'defaultRules',
            'selectedDefaultRules',
            'existingCustomRules',
            'weekDays',
            'specialPrices',
            'isWizard',
            'pendingRevision'
        ));
    }

    /**
     * به‌روزرسانی تنظیمات اقامتگاه
     */
    public function updateConfig(Request $request, Property $property)
    {
        $this->checkAccess($property);

        // تمیز کردن مقادیر عددی قیمت
        $priceFields = ['price_per_night', 'price_weekend', 'price_holiday', 'extra_guest_fee', 'cleaning_fee'];
        foreach ($priceFields as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                $request->merge([
                    $field => (is_null($val) || $val === '') ? null : str_replace(',', '', $val)
                ]);
            }
        }

        $validated = $request->validate([
            'base_guests' => 'required|integer|min:1|max:50',
            'max_guests' => 'required|integer|min:1|max:100|gte:base_guests',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:0,1,2,3,4,5,6',
            'price_per_night' => 'required|numeric|min:0',
            'price_weekend' => 'nullable|numeric|min:0',
            'price_holiday' => 'nullable|numeric|min:0',
            'extra_guest_fee' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|string',
            'check_out_time' => 'nullable|string',
            'min_stay_nights' => 'required|integer|min:1|max:30',
            'instant_booking' => 'nullable|boolean',
            'house_rules' => 'nullable|array',
            'custom_rules' => 'nullable|array',
            'custom_rules.*' => 'nullable|string|max:255',
            'special_prices' => 'nullable|array',
        ]);

        $validated['instant_booking'] = $request->has('instant_booking');
        $validated['weekend_days'] = $request->input('weekend_days', ['4', '5']);

        // ادغام قوانین پیش‌فرض تیک‌خورده با قوانین سفارشی ثبت‌شده
        $defaultSelected = (array) $request->input('house_rules', []);
        $customRules = array_filter(array_map('trim', (array) $request->input('custom_rules', [])));
        $validated['house_rules'] = array_values(array_unique(array_merge($defaultSelected, $customRules)));
        unset($validated['custom_rules']);

        $specialPricesData = $request->input('special_prices', []);
        unset($validated['special_prices']);

        $user = auth()->user();
        $canManagePublication = \Modules\Properties\Services\PropertyRevisionService::canManagePublication($user);

        // واکشی یا ایجاد نمونه تنظیمات برای بررسی getDirty
        $config = PropertyRentalConfig::firstOrNew(['property_id' => $property->id]);

        if (!$config->exists) {
            $config->base_guests = 2;
            $config->max_guests = 4;
            $config->min_stay_nights = 1;
            $config->check_in_time = '14:00:00';
            $config->check_out_time = '12:00:00';
            $config->weekend_days = ['4', '5'];
            $config->instant_booking = false;
            $config->save();
        }

        // یکسان‌سازی فرمت زمان برای جلوگیری از تفاوت کاذب با دیتابیس (14:00 vs 14:00:00)
        if (isset($validated['check_in_time'])) {
            $inTime = trim((string)$validated['check_in_time']);
            $existingIn = $config->check_in_time ? substr((string)$config->check_in_time, 0, 5) : '14:00';
            if (substr($inTime, 0, 5) === $existingIn) {
                $validated['check_in_time'] = $config->check_in_time;
            } else {
                $validated['check_in_time'] = substr($inTime, 0, 5) . ':00';
            }
        }
        if (isset($validated['check_out_time'])) {
            $outTime = trim((string)$validated['check_out_time']);
            $existingOut = $config->check_out_time ? substr((string)$config->check_out_time, 0, 5) : '12:00';
            if (substr($outTime, 0, 5) === $existingOut) {
                $validated['check_out_time'] = $config->check_out_time;
            } else {
                $validated['check_out_time'] = substr($outTime, 0, 5) . ':00';
            }
        }

        // ثبت مقادیر واقعی دیتابیس قبل از اعمال تغییرات
        $originalAttributes = $config->getAttributes();
        foreach (['weekend_days', 'house_rules'] as $jsonField) {
            if (isset($originalAttributes[$jsonField]) && is_string($originalAttributes[$jsonField])) {
                $decoded = json_decode($originalAttributes[$jsonField], true);
                if (is_array($decoded)) {
                    $originalAttributes[$jsonField] = $decoded;
                }
            }
        }

        $config->fill($validated);
        $dirtyConfig = $config->getDirty();

        $hasSpecialPrices = !empty($specialPricesData) && count($specialPricesData) > 0;

        // بررسی اینکه آیا ملک در حال حاضر زنده و تاییدشده است و کاربر اجازه انتشار مستقیم ندارد
        $isLiveProperty = ($property->approval_status === 'approved' || $property->publication_status === 'published');
        $mustStageForReview = (!$canManagePublication && $isLiveProperty);

        // ثبت گزارش بازبینی در صورتی که فیلدی تغییر کرده باشد یا تاریخ‌های خاص ثبت شده باشد
        if (!empty($dirtyConfig) || $hasSpecialPrices) {
            $revisionService = app(\Modules\Properties\Services\PropertyRevisionService::class);
            $revision = $revisionService->recordRentalConfigChanges(
                $property,
                $config,
                $dirtyConfig,
                $user,
                [
                    'special_prices_count' => is_array($specialPricesData) ? count($specialPricesData) : 0,
                    'special_prices' => $specialPricesData,
                ],
                $originalAttributes
            );

            if ($revision) {
                $property->approval_status = 'pending_review';
            }
        }

        if ($mustStageForReview) {
            // در صورتی که ملک زنده است و کاربر میزبان است:
            // تغییرات در PropertyRevision ذخیره شده است. مقادیر زنده و تقویم عمومی تا زمان تأیید مدیر تغییر نمی‌کنند.
            $property->save(); // جهت ذخیره approval_status = 'pending_review'
        } else {
            // برای مدیران یا در زمان ثبت اولیه اقامتگاه (پیش از اولین تأیید)، تغییرات مستقیماً در دیتابیس زنده ثبت می‌شود:
            $config->save();

            // مدیریت و ذخیره‌سازی تاریخ‌های خاص (تکی و چندتایی)
            $property->seasonalPrices()->delete();
            if (is_array($specialPricesData)) {
                foreach ($specialPricesData as $item) {
                    if (empty($item['start_date']) || empty($item['price'])) {
                        continue;
                    }
                    $cleanPrice = (float) str_replace(',', '', $item['price']);
                    if ($cleanPrice <= 0) {
                        continue;
                    }

                    try {
                        $startDateCarbon = Jalalian::fromFormat('Y/m/d', trim($item['start_date']))->toCarbon();
                        $type = $item['type'] ?? 'single';
                        if ($type === 'single' || empty($item['end_date'])) {
                            $endDateCarbon = $startDateCarbon;
                        } else {
                            $endDateCarbon = Jalalian::fromFormat('Y/m/d', trim($item['end_date']))->toCarbon();
                        }

                        if ($startDateCarbon->gt($endDateCarbon)) {
                            [$startDateCarbon, $endDateCarbon] = [$endDateCarbon, $startDateCarbon];
                        }

                        $property->seasonalPrices()->create([
                            'title' => !empty($item['title']) ? trim($item['title']) : null,
                            'start_date' => $startDateCarbon->format('Y-m-d'),
                            'end_date' => $endDateCarbon->format('Y-m-d'),
                            'price_per_night' => $cleanPrice,
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Error saving rental special price: ' . $e->getMessage());
                    }
                }
            }

            // هماهنگ‌سازی قیمت پایه با فیلد price جدول properties جهت نمایش در لیست‌ها
            $property->price = $validated['price_per_night'];
            $property->save();
        }

        $isWizard = $request->input('wizard') == '1' || $request->query('wizard') == '1';

        if ($isWizard) {
            $successMsg = (!$canManagePublication && !empty($dirtyConfig))
                ? 'تنظیمات و نرخ‌های اقامتگاه ثبت شد. لطفاً در مرحله بعد اطلاعات تکمیلی و مشخصات اتاق‌ها را تکمیل نمایید.'
                : 'تنظیمات اقامتگاه با موفقیت ذخیره شد. لطفاً اطلاعات تکمیلی و مشخصات اتاق‌ها را تکمیل نمایید.';

            return redirect()->route('user.properties.details', $property)->with('success', $successMsg);
        }

        $successMsg = (!$canManagePublication && !empty($dirtyConfig))
            ? 'تغییرات نرخ و تنظیمات اقامتگاه ثبت شد و پس از بررسی و تایید مدیریت در سایت فعال خواهد شد.'
            : 'تنظیمات و نرخ‌های اقامتگاه با موفقیت به‌روزرسانی شد.';

        return redirect()->route('user.properties.rental.config', $property)->with('success', $successMsg);
    }

    /**
     * تایید یا رد اقامتگاه و آخرین نسخه ویرایشی توسط مدیر پلتفرم
     */
    public function reviewStatus(Request $request, Property $property)
    {
        $user = auth()->user();
        if (!\Modules\Properties\Services\PropertyRevisionService::canManagePublication($user)) {
            abort(403);
        }

        $request->validate([
            'approval_status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|required_if:approval_status,rejected|string|max:500',
        ]);

        $revisionService = app(\Modules\Properties\Services\PropertyRevisionService::class);
        $pendingRevision = $property->pendingRevision;

        if ($request->approval_status === 'approved') {
            if ($pendingRevision) {
                $revisionService->approveRevision($pendingRevision, $user);
            } else {
                $property->update([
                    'approval_status' => 'approved',
                    'rejection_reason' => null,
                ]);
            }
            $statusText = 'تأیید شد';
        } else {
            if ($pendingRevision) {
                $revisionService->rejectRevision($pendingRevision, $user, $request->rejection_reason);
            } else {
                $property->update([
                    'approval_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);
            }
            $statusText = 'رد شد';
        }

        return back()->with('success', "وضعیت اقامتگاه با موفقیت به «{$statusText}» تغییر یافت.");
    }

    /**
     * دریافت اطلاعات تغییرات در انتظار بررسی جهت نمایش در پنجره مقایسه (Diff)
     */
    public function getPendingRevision(Request $request, Property $property)
    {
        $user = auth()->user();
        if (!\Modules\Properties\Services\PropertyRevisionService::canManagePublication($user)) {
            abort(403);
        }

        $revision = $property->pendingRevision()->with('user')->first();

        // اگر بازبینی در انتظار موجود نبود و پارامتر latest درخواست شده بود
        if (!$revision && $request->query('latest') == '1') {
            $revision = $property->latestRevision()->with('user')->first();
        }

        if (!$revision) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ ویرایشی برای این اقامتگاه یافت نشد.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'revision' => [
                'id' => $revision->id,
                'status' => $revision->status,
                'user_name' => $revision->user?->name ?? 'کاربر/میزبان',
                'created_at_jalali' => \Morilog\Jalali\Jalalian::fromCarbon($revision->created_at)->format('Y/m/d H:i'),
                'type' => $revision->type,
                'changes_summary' => $revision->changes_summary,
            ]
        ]);
    }
}
