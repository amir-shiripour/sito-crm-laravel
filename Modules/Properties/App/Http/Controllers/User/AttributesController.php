<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\PropertyAttribute;

class AttributesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:properties.attributes.view|properties.attributes.manage')->only('index');
        $this->middleware('permission:properties.attributes.create|properties.attributes.manage')->only(['store', 'loadDefaults']);
        $this->middleware('permission:properties.attributes.edit|properties.attributes.manage')->only('update');
        $this->middleware('permission:properties.attributes.delete|properties.attributes.manage')->only('destroy');
    }

    public function index()
    {
        $detailsAttributes = PropertyAttribute::where('section', 'details')->orderBy('sort_order')->get();
        $featuresAttributes = PropertyAttribute::where('section', 'features')->orderBy('sort_order')->get();

        return view('properties::user.settings.attributes', compact('detailsAttributes', 'featuresAttributes'));
    }

    public function store(Request $request)
    {
        Log::info('--- AttributesController Store Debug ---');
        Log::info('Request Data:', $request->all());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'section' => 'required|in:details,features',
            'icon' => 'nullable|string|max:100',
            'options' => 'nullable|string',
            'is_filterable' => 'nullable|boolean',
            'is_range_filter' => 'nullable|boolean',
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        $data['sort_order'] = PropertyAttribute::where('section', $data['section'])->max('sort_order') + 1;

        // Checkbox handling
        $data['is_filterable'] = $request->has('is_filterable');
        $data['is_range_filter'] = $request->has('is_range_filter');
        $data['is_active'] = true;

        Log::info('Data to Create:', $data);

        $attr = PropertyAttribute::create($data);

        Log::info('Created Attribute:', $attr->toArray());

        return back()->with('success', 'ویژگی با موفقیت اضافه شد.')->with('active_tab', $data['section']);
    }

    public function update(Request $request, PropertyAttribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,checkbox',
            'icon' => 'nullable|string|max:100',
            'options' => 'nullable|string',
            'is_filterable' => 'nullable|boolean',
            'is_range_filter' => 'nullable|boolean',
        ]);

        if ($data['type'] === 'select' && !empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        } else {
            $data['options'] = null;
        }

        // Checkbox handling
        $data['is_filterable'] = $request->has('is_filterable');
        $data['is_range_filter'] = $request->has('is_range_filter');

        $attribute->update($data);

        return back()->with('success', 'ویژگی با موفقیت ویرایش شد.')->with('active_tab', $attribute->section);
    }

    public function destroy(PropertyAttribute $attribute)
    {
        $section = $attribute->section;
        $attribute->delete();
        return back()->with('success', 'ویژگی حذف شد.')->with('active_tab', $section);
    }

    public function loadDefaults(Request $request)
    {
        $section = $request->input('section', 'details');

        $defaults = [
            'details' => [
                ['name' => 'متراژ اقامتگاه (زیربنا)', 'type' => 'number', 'icon' => '📐', 'options' => null, 'is_filterable' => true, 'is_range_filter' => true],
                ['name' => 'متراژ کل زمین / محوطه', 'type' => 'number', 'icon' => '🏡', 'options' => null, 'is_filterable' => true, 'is_range_filter' => true],
                ['name' => 'متراژ', 'type' => 'number', 'icon' => '📏', 'options' => null, 'is_filterable' => true, 'is_range_filter' => true],
                ['name' => 'تعداد اتاق خواب', 'type' => 'number', 'icon' => '🚪', 'options' => null, 'is_filterable' => true, 'is_range_filter' => true],
                ['name' => 'تعداد حمام', 'type' => 'number', 'icon' => '🚿', 'options' => null, 'is_filterable' => false, 'is_range_filter' => false],
                ['name' => 'تعداد سرویس بهداشتی', 'type' => 'number', 'icon' => '🚽', 'options' => null, 'is_filterable' => false, 'is_range_filter' => false],
                ['name' => 'تعداد تخت دو نفره', 'type' => 'number', 'icon' => '🛏️', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'تعداد تخت یک نفره', 'type' => 'number', 'icon' => '🛋️', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'سال ساخت', 'type' => 'number', 'icon' => '🗓️', 'options' => null, 'is_filterable' => true, 'is_range_filter' => true],
                ['name' => 'طبقه', 'type' => 'number', 'icon' => '🏢', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'تعداد کل طبقات', 'type' => 'number', 'icon' => '🏬', 'options' => null, 'is_filterable' => false, 'is_range_filter' => false],
                ['name' => 'تعداد واحد در هر طبقه', 'type' => 'number', 'icon' => '🔢', 'options' => null, 'is_filterable' => false, 'is_range_filter' => false],
                ['name' => 'موقعیت و جهت ملک', 'type' => 'select', 'icon' => '🧭', 'options' => ['شمالی', 'جنوبی', 'شرقی', 'غربی', 'دو کله', 'دو نبش'], 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'نوع کفپوش', 'type' => 'select', 'icon' => '🪵', 'options' => ['سرامیک', 'پارکت', 'لمینت', 'سنگ', 'موزاییک'], 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'سیستم سرمایش', 'type' => 'select', 'icon' => '❄️', 'options' => ['کولر آبی', 'کولر گازی / اسپلیت', 'داکت اسپلیت', 'چیلر', 'فن کوئل'], 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'سیستم گرمایش', 'type' => 'select', 'icon' => '🔥', 'options' => ['پکیج و رادیاتور', 'بخاری گازی', 'گرمایش از کف', 'شومینه', 'موتورخانه مرکزی'], 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'تأمین آب گرم', 'type' => 'select', 'icon' => '♨️', 'options' => ['پکیج', 'آبگرمکن', 'موتورخانه'], 'is_filterable' => true, 'is_range_filter' => false],
            ],
            'features' => [
                ['name' => 'استخر سرپوشیده آبگرم', 'type' => 'checkbox', 'icon' => '🏊', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'استخر روباز', 'type' => 'checkbox', 'icon' => '🏊‍♂️', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'جکوزی', 'type' => 'checkbox', 'icon' => '🛁', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'سونا', 'type' => 'checkbox', 'icon' => '🧖', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'میز بیلیارد', 'type' => 'checkbox', 'icon' => '🎱', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'فوتبال دستی', 'type' => 'checkbox', 'icon' => '⚽', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'اینترنت پرسرعت Wi-Fi', 'type' => 'checkbox', 'icon' => '📶', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'پارکینگ اختصاصی', 'type' => 'checkbox', 'icon' => '🚗', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'آسانسور', 'type' => 'checkbox', 'icon' => '🛗', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'انباری', 'type' => 'checkbox', 'icon' => '📦', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'بالکن / تراس', 'type' => 'checkbox', 'icon' => '🌅', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'حیاط و فضای سبز', 'type' => 'checkbox', 'icon' => '🌳', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'روف گاردن', 'type' => 'checkbox', 'icon' => '🌿', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'باربیکیو / کباب‌پز', 'type' => 'checkbox', 'icon' => '🥩', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'چشم‌انداز دریا / جنگل / کوهستان', 'type' => 'checkbox', 'icon' => '🌲', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'تجهیزات کامل آشپزخانه', 'type' => 'checkbox', 'icon' => '🍳', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'ماشین لباسشویی', 'type' => 'checkbox', 'icon' => '🧺', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'تلویزیون و سیستم صوتی', 'type' => 'checkbox', 'icon' => '📺', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'درب ضد سرقت و ریموت‌دار', 'type' => 'checkbox', 'icon' => '🔐', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
                ['name' => 'دوربین مداربسته و نگهبانی', 'type' => 'checkbox', 'icon' => '🛡️', 'options' => null, 'is_filterable' => true, 'is_range_filter' => false],
            ],
        ];

        $targetSections = ($section === 'all') ? ['details', 'features'] : (in_array($section, ['details', 'features']) ? [$section] : ['details']);
        $addedCount = 0;
        $updatedCount = 0;

        foreach ($targetSections as $sec) {
            $items = $defaults[$sec] ?? [];
            $maxSort = PropertyAttribute::where('section', $sec)->max('sort_order') ?? 0;

            foreach ($items as $item) {
                $existing = PropertyAttribute::where('name', $item['name'])->where('section', $sec)->first();
                if ($existing) {
                    if (empty($existing->icon) && !empty($item['icon'])) {
                        $existing->update(['icon' => $item['icon']]);
                        $updatedCount++;
                    }
                } else {
                    $maxSort++;
                    PropertyAttribute::create([
                        'name' => $item['name'],
                        'type' => $item['type'],
                        'section' => $sec,
                        'icon' => $item['icon'] ?? null,
                        'options' => $item['options'],
                        'sort_order' => $maxSort,
                        'is_active' => true,
                        'is_filterable' => $item['is_filterable'],
                        'is_range_filter' => $item['is_range_filter'],
                    ]);
                    $addedCount++;
                }
            }
        }

        $activeTab = in_array($section, ['details', 'features']) ? $section : 'details';
        $messages = [];
        if ($addedCount > 0) {
            $messages[] = "{$addedCount} ویژگی جدید اضافه شد";
        }
        if ($updatedCount > 0) {
            $messages[] = "آیکون {$updatedCount} ویژگی به‌روزرسانی شد";
        }
        $message = !empty($messages) 
            ? 'الگوی پیش‌فرض با موفقیت اعمال شد (' . implode(' و ', $messages) . ').'
            : 'همه موارد الگوی پیش‌فرض و آیکون‌ها از قبل در سیستم موجود هستند.';

        return back()->with('success', $message)->with('active_tab', $activeTab);
    }
}
