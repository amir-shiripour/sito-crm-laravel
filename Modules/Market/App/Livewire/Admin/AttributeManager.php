<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Modules\Market\Entities\MarketAttribute;
use Modules\Market\Entities\MarketAttributeValue;
use Illuminate\Support\Facades\Storage;


class AttributeManager extends Component
{
    use WithFileUploads;

    public $attribute_id;
    public $name = '';
    public $type = 'select'; // select, color, image
    public $unit = ''; // 💡 NEW: واحد اندازه‌گیری

    // مقادیر زیرمجموعه
    public $values = [];

    public $isFormOpen = false;

    // لیست واحدهای اندازه‌گیری استاندارد
    public $unitsList = [
        '' => 'بدون واحد (مثلاً: XL, L)',
        'GB' => 'گیگابایت (GB)',
        'MB' => 'مگابایت (MB)',
        'TB' => 'ترابایت (TB)',
        'cm' => 'سانتی‌متر (cm)',
        'm' => 'متر (m)',
        'mm' => 'میلی‌متر (mm)',
        'g' => 'گرم (g)',
        'kg' => 'کیلوگرم (kg)',
        'ml' => 'میلی‌لیتر (ml)',
        'lit' => 'لیتر (L)',
        'W' => 'وات (W)',
        'V' => 'ولت (V)',
        'inch' => 'اینچ (inch)',
        'mAh' => 'میلی‌آمپر ساعت (mAh)',
        'MHz' => 'مگاهرتز (MHz)',
        'GHz' => 'گیگاهرتز (GHz)',
        'MP' => 'مگاپیکسل (MP)',
        'pcs' => 'عدد',
        'month' => 'ماه',
        'year' => 'سال',
    ];

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        if ($id) {
            $attr = MarketAttribute::with('values')->findOrFail($id);
            $this->attribute_id = $attr->id;
            $this->name = $attr->name;
            $this->type = $attr->type;
            $this->unit = $attr->unit ?? ''; // 💡 Load Unit

            $this->values = [];
            foreach ($attr->values as $val) {
                $this->values[] = [
                    'id' => $val->id,
                    'value' => $val->value,
                    'meta_value' => $val->meta_value,
                    'new_image' => null // محل نگهداری تصویر آپلودی جدید در صورت نیاز
                ];
            }
        } else {
            $this->reset(['attribute_id', 'name', 'type', 'unit', 'values']);
        }
        $this->isFormOpen = true;
    }

    public function quickCreate($preset)
    {
        $this->resetValidation();
        $this->reset(['attribute_id', 'name', 'type', 'unit', 'values']);

        switch ($preset) {

            // ─── رنگ و ظاهر ─────────────────────────────────────────
            case 'color':
                $this->name = 'رنگ';
                $this->type = 'color';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'مشکی',     'meta_value' => '#000000', 'new_image' => null],
                    ['id' => null, 'value' => 'سفید',      'meta_value' => '#ffffff', 'new_image' => null],
                    ['id' => null, 'value' => 'قرمز',      'meta_value' => '#ef4444', 'new_image' => null],
                    ['id' => null, 'value' => 'آبی',       'meta_value' => '#3b82f6', 'new_image' => null],
                    ['id' => null, 'value' => 'سبز',       'meta_value' => '#22c55e', 'new_image' => null],
                    ['id' => null, 'value' => 'زرد',       'meta_value' => '#eab308', 'new_image' => null],
                    ['id' => null, 'value' => 'خاکستری',   'meta_value' => '#6b7280', 'new_image' => null],
                    ['id' => null, 'value' => 'نارنجی',    'meta_value' => '#f97316', 'new_image' => null],
                    ['id' => null, 'value' => 'بنفش',      'meta_value' => '#a855f7', 'new_image' => null],
                    ['id' => null, 'value' => 'صورتی',     'meta_value' => '#ec4899', 'new_image' => null],
                    ['id' => null, 'value' => 'قهوه‌ای',   'meta_value' => '#92400e', 'new_image' => null],
                    ['id' => null, 'value' => 'کرم',       'meta_value' => '#fef3c7', 'new_image' => null],
                    ['id' => null, 'value' => 'نقره‌ای',   'meta_value' => '#cbd5e1', 'new_image' => null],
                    ['id' => null, 'value' => 'طلایی',     'meta_value' => '#f59e0b', 'new_image' => null],
                    ['id' => null, 'value' => 'فیروزه‌ای', 'meta_value' => '#06b6d4', 'new_image' => null],
                    ['id' => null, 'value' => 'زیتونی',    'meta_value' => '#65a30d', 'new_image' => null],
                    ['id' => null, 'value' => 'سرمه‌ای',   'meta_value' => '#1e3a5f', 'new_image' => null],
                ];
                break;

            // ─── دیجیتال / موبایل / لپتاپ ───────────────────────────
            case 'ram':
                $this->name = 'حافظه رم (RAM)';
                $this->type = 'select';
                $this->unit = 'GB';
                $this->values = [
                    ['id' => null, 'value' => '2',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '8',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '12', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '16', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '24', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '32', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '64', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'storage':
                $this->name = 'حافظه داخلی';
                $this->type = 'select';
                $this->unit = 'GB';
                $this->values = [
                    ['id' => null, 'value' => '16',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '32',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '64',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '128',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '256',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '512',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1024', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2048', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'screen_size':
                $this->name = 'اندازه صفحه نمایش';
                $this->type = 'select';
                $this->unit = 'inch';
                $this->values = [
                    ['id' => null, 'value' => '4.7',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5.5',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6.1',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6.4',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6.7',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '13.3', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '14',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '15.6', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '17.3', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'battery':
                $this->name = 'ظرفیت باتری';
                $this->type = 'select';
                $this->unit = 'mAh';
                $this->values = [
                    ['id' => null, 'value' => '2000',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3000',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4000',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4500',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5000',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6000',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10000', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'camera':
                $this->name = 'دوربین اصلی';
                $this->type = 'select';
                $this->unit = 'MP';
                $this->values = [
                    ['id' => null, 'value' => '8',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '12',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '16',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '48',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '64',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '108', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '200', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'sim':
                $this->name = 'تعداد سیم‌کارت';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'تک سیم‌کارت',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'دو سیم‌کارت',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سه سیم‌کارت',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'os':
                $this->name = 'سیستم عامل';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'Android',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'iOS',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'Windows',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'macOS',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'Linux',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'HarmonyOS','meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'network':
                $this->name = 'نسل شبکه';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => '3G',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4G',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5G',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── پوشاک و مد ──────────────────────────────────────────
            case 'size':
                $this->name = 'سایز لباس';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'XS',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'S',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'M',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'L',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'XL',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'XXL', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3XL', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4XL', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'size_eu':
                $this->name = 'سایز اروپایی لباس (EU)';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => '36', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '38', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '40', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '42', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '44', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '46', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '48', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '52', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'shoes':
                $this->name = 'سایز کفش';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => '36', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '37', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '38', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '39', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '40', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '41', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '42', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '43', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '44', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '45', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '46', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'gender':
                $this->name = 'جنسیت';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'مردانه',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'زنانه',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'بچگانه',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'دخترانه',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پسرانه',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'یونیسکس',      'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'material':
                $this->name = 'جنس / متریال';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'پنبه (Cotton)',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پلی‌استر',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نخ',               'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کتان (Linen)',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'ابریشم (Silk)',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'ویسکوز',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چرم طبیعی',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چرم مصنوعی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کشمیر',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'اکریلیک',          'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'دنیم (جین)',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'فلیس',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'فلز (Metal)',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پلاستیک',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چوب',              'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سرامیک',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'آلومینیوم',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'استیل',            'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'sleeve':
                $this->name = 'آستین';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'آستین کوتاه',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'آستین ۳/۴',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'آستین بلند',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'بی‌آستین',        'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── لوازم خانگی / برقی ──────────────────────────────────
            case 'power':
                $this->name = 'توان دستگاه';
                $this->type = 'select';
                $this->unit = 'W';
                $this->values = [
                    ['id' => null, 'value' => '500',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '750',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1000', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1200', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1500', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2000', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2200', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2500', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3000', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'capacity_lit':
                $this->name = 'ظرفیت / حجم';
                $this->type = 'select';
                $this->unit = 'lit';
                $this->values = [
                    ['id' => null, 'value' => '0.5',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1.5',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '7',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '15',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '20',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '30',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50',   'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'energy_grade':
                $this->name = 'رتبه انرژی';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'A+++', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'A++',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'A+',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'A',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'B',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'C',    'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'washing_capacity':
                $this->name = 'ظرفیت لباسشویی';
                $this->type = 'select';
                $this->unit = 'kg';
                $this->values = [
                    ['id' => null, 'value' => '5',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '7',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '8',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '9',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '12',   'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'tv_size':
                $this->name = 'اندازه تلویزیون';
                $this->type = 'select';
                $this->unit = 'inch';
                $this->values = [
                    ['id' => null, 'value' => '24',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '32',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '40',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '43',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '55',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '65',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '75',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '85',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'tv_resolution':
                $this->name = 'رزولوشن تصویر';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'HD (720p)',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'Full HD (1080p)','meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4K UHD',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '8K UHD',         'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── مبلمان / دکور / ساختمان ─────────────────────────────
            case 'dimensions':
                $this->name = 'ابعاد';
                $this->type = 'select';
                $this->unit = 'cm';
                $this->values = [
                    ['id' => null, 'value' => '40×40',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50×50',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '60×40',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '80×60',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '100×80',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '120×80',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '150×100',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '200×150',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'weight':
                $this->name = 'وزن';
                $this->type = 'select';
                $this->unit = 'kg';
                $this->values = [
                    ['id' => null, 'value' => '0.1',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '0.5',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '20',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50',   'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'frame_material':
                $this->name = 'جنس فریم / بدنه';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'چوب MDF',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چوب جامد',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'فلز',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'آلومینیوم',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پلاستیک',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'شیشه',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سنگ طبیعی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سرامیک',          'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'ترکیبی',          'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── غذایی / آشامیدنی / بسته‌بندی ───────────────────────
            case 'package_size':
                $this->name = 'وزن / حجم بسته';
                $this->type = 'select';
                $this->unit = 'g';
                $this->values = [
                    ['id' => null, 'value' => '100',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '200',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '250',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '500',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1000', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2000', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5000', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'package_count':
                $this->name = 'تعداد در بسته';
                $this->type = 'select';
                $this->unit = 'pcs';
                $this->values = [
                    ['id' => null, 'value' => '1',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '6',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '12',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '24',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'flavor':
                $this->name = 'طعم / عطر';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'طبیعی / بدون طعم', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'شکلات',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'وانیل',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'توت‌فرنگی',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موز',               'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'انبه',              'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نعناع',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'لیمو',              'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'هندوانه',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'قهوه',              'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کاراملو',           'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── لوازم آرایشی / بهداشتی / سلامت ─────────────────────
            case 'skin_type':
                $this->name = 'نوع پوست';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'پوست چرب',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پوست خشک',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پوست مختلط',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پوست حساس',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'همه انواع پوست',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'hair_type':
                $this->name = 'نوع مو';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'موی چرب',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موی خشک',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موی معمولی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موی رنگ‌شده',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موی آسیب‌دیده',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'موی فر',          'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'volume_ml':
                $this->name = 'حجم محصول';
                $this->type = 'select';
                $this->unit = 'ml';
                $this->values = [
                    ['id' => null, 'value' => '30',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '50',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '100',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '150',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '200',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '250',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '300',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '500',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '1000', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'sunscreen_spf':
                $this->name = 'SPF ضدآفتاب';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'SPF 15',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'SPF 30',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'SPF 50',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'SPF 60',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'SPF 100', 'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── ورزش / تناسب اندام ──────────────────────────────────
            case 'dumbbell_weight':
                $this->name = 'وزن دمبل / وزنه';
                $this->type = 'select';
                $this->unit = 'kg';
                $this->values = [
                    ['id' => null, 'value' => '1',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '2',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '3',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '4',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '7.5', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '12',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '15',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '20',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'sport_size':
                $this->name = 'سایز تجهیزات ورزشی';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'کودک (Junior)',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کوچک (S)',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'متوسط (M)',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'بزرگ (L)',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'استاندارد',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'حرفه‌ای',          'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── خودرو / موتورسیکلت / لوازم یدکی ────────────────────
            case 'car_brand':
                $this->name = 'برند / مدل خودرو';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'پراید',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پژو ۲۰۶',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پژو ۴۰۵',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سمند',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'دنا',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'رانا',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'تیبا',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کوئیک',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'تارا',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'هایما',           'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چانگان',          'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'جک',              'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'مزدا',            'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'تویوتا',          'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'هیوندای',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کیا',             'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'سایر',            'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'tire_size':
                $this->name = 'سایز لاستیک';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => '155/65R13',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '165/65R13',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '175/65R14',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '185/65R14',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '185/65R15',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '195/65R15',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '205/55R16',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '215/60R17',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '225/65R17',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'oil_viscosity':
                $this->name = 'ویسکوزیته روغن موتور';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => '5W-30',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '5W-40',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '10W-40',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '15W-40',  'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => '20W-50',  'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── کتاب / محصولات آموزشی / دیجیتال ────────────────────
            case 'language':
                $this->name = 'زبان';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'فارسی',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'انگلیسی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'عربی',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'آلمانی',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'فرانسوی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'اسپانیایی',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'ترکی',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'روسی',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'چینی',         'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'ژاپنی',        'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'edition':
                $this->name = 'نوع نسخه / ویرایش';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'نسخه معمولی',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نسخه ویژه',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نسخه جیبی',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نسخه گالینگور',    'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نسخه PDF/دیجیتال', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نسخه صوتی',        'meta_value' => null, 'new_image' => null],
                ];
                break;

            case 'age_range':
                $this->name = 'رده سنی';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'نوزاد (۰-۱ سال)',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نوپا (۱-۳ سال)',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'کودک (۳-۶ سال)',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'دانش‌آموز (۶-۱۲ سال)', 'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'نوجوان (۱۲-۱۸ سال)',   'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'بزرگسال (+۱۸ سال)',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'همه سنین',              'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── ضمانت / گارانتی ─────────────────────────────────────
            case 'warranty':
                $this->name = 'ضمانت / گارانتی';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'بدون گارانتی',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۶ ماهه',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۱ ساله',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۱۸ ماهه',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۲ ساله',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۳ ساله',      'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'گارانتی ۵ ساله',      'meta_value' => null, 'new_image' => null],
                ];
                break;

            // ─── بسته‌بندی / ارسال ───────────────────────────────────
            case 'packaging':
                $this->name = 'نوع بسته‌بندی';
                $this->type = 'select';
                $this->unit = '';
                $this->values = [
                    ['id' => null, 'value' => 'جعبه ساده',          'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'جعبه کادویی',        'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'پاکت نایلونی',       'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'بدون بسته‌بندی',     'meta_value' => null, 'new_image' => null],
                    ['id' => null, 'value' => 'باکس حرفه‌ای',       'meta_value' => null, 'new_image' => null],
                ];
                break;
        }

        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
    }

    public function addValue()
    {
        $this->values[] = [
            'id' => null,
            'value' => '',
            'meta_value' => $this->type === 'color' ? '#000000' : null,
            'new_image' => null
        ];
    }

    public function removeValue($index)
    {
        if (!empty($this->values[$index]['id'])) {
            $valModel = MarketAttributeValue::find($this->values[$index]['id']);

            // اگر از قبل عکسی داشته، پاکش کن
            if ($valModel && ($this->type === 'image' || $this->type === 'color') && Str::startsWith($valModel->meta_value, 'attributes/')) {
                Storage::disk('public')->delete($valModel->meta_value);
            }

            $valModel?->delete();
        }
        unset($this->values[$index]);
        $this->values = array_values($this->values);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:select,color,image', // 💡 Image added
            'unit' => 'nullable|string|max:50',
            'values.*.value' => 'required|string|max:100',
            // اعتبارسنجی تصویر فقط برای ردیف‌هایی که واقعاً تصویر آپلود کرده‌اند
            'values.*.new_image' => 'nullable|image|max:1024', // مکس 1MB
        ], [
            'values.*.value.required' => 'نام مقدار نمی‌تواند خالی باشد.'
        ]);

        $attribute = MarketAttribute::updateOrCreate(
            ['id' => $this->attribute_id],
            [
                'name' => $this->name,
                'type' => $this->type,
                'unit' => empty($this->unit) ? null : $this->unit,
            ]
        );

        // ذخیره مقادیر
        foreach ($this->values as $index => $val) {
            $metaValueToSave = $val['meta_value'] ?? null;

            // اگر کاربر عکس جدیدی آپلود کرده باشد (مخصوص حالت image یا پترن رنگی)
            if (!empty($val['new_image'])) {
                // اگر از قبل عکسی بود پاک کن
                if (!empty($val['meta_value']) && Str::startsWith($val['meta_value'], 'attributes/')) {
                    Storage::disk('public')->delete($val['meta_value']);
                }
                // ذخیره عکس جدید
                $metaValueToSave = $val['new_image']->store('attributes', 'public');
            }

            // پاکسازی meta_value برای حالت select اگر اشتباهاً چیزی مانده بود
            if ($this->type === 'select') {
                $metaValueToSave = null;
            }

            MarketAttributeValue::updateOrCreate(
                ['id' => $val['id']],
                [
                    'attribute_id' => $attribute->id,
                    'value' => $val['value'],
                    'meta_value' => $metaValueToSave
                ]
            );
        }

        $this->dispatch('notify', type: 'success', text: 'ویژگی و مقادیر آن با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function delete($id)
    {
        $attr = MarketAttribute::with('values')->findOrFail($id);

        // حذف فیزیکی تمام عکس‌های مربوط به مقادیر این ویژگی
        foreach ($attr->values as $val) {
            if ($val->meta_value && Str::startsWith($val->meta_value, 'attributes/')) {
                Storage::disk('public')->delete($val->meta_value);
            }
        }

        $attr->delete();
        $this->dispatch('notify', type: 'success', text: 'ویژگی با موفقیت حذف شد.');
    }

    public function render()
    {
        $attributes = MarketAttribute::with('values')->latest()->get();
        return view('market::livewire.admin.attribute-manager', compact('attributes'));
    }
}
