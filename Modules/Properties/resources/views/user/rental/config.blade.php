@extends('layouts.user')

@php
    $title = 'مشخصات و قیمت‌گذاری اقامتگاه روزانه';
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 font-sans placeholder-gray-400 dark:placeholder-gray-600";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer";
    $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';
    $isAdmin = auth()->user()->hasRole(['super-admin', 'admin']) || auth()->user()->can('properties.manage');
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6" x-data="rentalConfigForm()">

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-500/20 dark:text-teal-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </span>
                تنظیمات اقامتگاه: {{ $property->title }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-10">ظرفیت مهمانان، قیمت‌های شبانه، شرایط اقامت و امکانات</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('user.properties.rental.calendar', $property) }}" class="px-4 py-2 rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 text-xs font-bold border border-teal-200 dark:border-teal-800/40 hover:bg-teal-100 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                تقویم و مسدودسازی
            </a>
            <a href="{{ route('user.properties.index') }}" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 transition">
                بازگشت
            </a>
        </div>
    </div>

    {{-- وضعیت بررسی ادمین (در صورت نیاز) --}}
    @if($isAdmin)
        <div class="{{ $cardClass }} p-5 bg-gradient-to-r from-amber-50/50 to-white dark:from-gray-800 dark:to-gray-800 border-amber-200 dark:border-amber-900/30">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">وضعیت تایید و انتشار پلتفرم</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">وضعیت فعلی: 
                        <strong class="font-sans text-gray-900 dark:text-white">
                            {{ match($property->approval_status) { 'approved' => 'تأیید شده و منتشر', 'pending_review' => 'در انتظار بررسی مدیریت', 'rejected' => 'رد شده', default => $property->approval_status } }}
                        </strong>
                    </span>
                </div>

                <form action="{{ route('user.properties.rental.review-status', $property) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @if($property->approval_status !== 'approved')
                        <input type="hidden" name="approval_status" value="approved">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                            تأیید اقامتگاه
                        </button>
                    @else
                        <input type="hidden" name="approval_status" value="rejected">
                        <input type="hidden" name="rejection_reason" value="نیاز به بازنگری تصاویر یا اطلاعات">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm">
                            رد و عدم انتشار
                        </button>
                    @endif
                </form>
            </div>
        </div>
    @endif

    <form action="{{ route('user.properties.rental.config.update', $property) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- کارت ۱: ظرفیت، خواب و سرویس --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                ظرفیت پذیرش و تعداد تخت‌ها
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                <div>
                    <label class="{{ $labelClass }}">ظرفیت استاندارد (پایه) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="base_guests" value="{{ old('base_guests', $rentalConfig->base_guests ?? 2) }}" min="1" max="50" required class="{{ $inputClass }} text-center">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400">نفر</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">حداکثر ظرفیت با نفر اضافه <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="max_guests" value="{{ old('max_guests', $rentalConfig->max_guests ?? 4) }}" min="1" max="100" required class="{{ $inputClass }} text-center">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400">نفر</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">تعداد اتاق خواب <span class="text-red-500">*</span></label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms', $rentalConfig->bedrooms ?? 1) }}" min="0" max="20" required class="{{ $inputClass }} text-center">
                </div>

                <div>
                    <label class="{{ $labelClass }}">تخت دو نفره</label>
                    <input type="number" name="double_beds" value="{{ old('double_beds', $rentalConfig->double_beds ?? 1) }}" min="0" max="20" class="{{ $inputClass }} text-center">
                </div>

                <div>
                    <label class="{{ $labelClass }}">تخت یک نفره</label>
                    <input type="number" name="single_beds" value="{{ old('single_beds', $rentalConfig->single_beds ?? 0) }}" min="0" max="20" class="{{ $inputClass }} text-center">
                </div>

                <div>
                    <label class="{{ $labelClass }}">سرویس بهداشتی و حمام <span class="text-red-500">*</span></label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms', $rentalConfig->bathrooms ?? 1) }}" min="1" max="10" required class="{{ $inputClass }} text-center">
                </div>
            </div>
        </div>

        {{-- کارت ۲: نرخ‌ها و شرایط مالی --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                قیمت‌گذاری شبانه (به {{ $currencyLabel }})
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="{{ $labelClass }}">قیمت شب‌های عادی (شنبه تا سه‌شنبه) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="price_per_night" x-model="pricePerNight" @input="formatPrice('pricePerNight')" required class="{{ $inputClass }} text-left dir-ltr pl-14">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">قیمت آخر هفته (چهارشنبه و پنجشنبه)</label>
                    <div class="relative">
                        <input type="text" name="price_weekend" x-model="priceWeekend" @input="formatPrice('priceWeekend')" class="{{ $inputClass }} text-left dir-ltr pl-14">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">قیمت ایام پیک و تعطیلات رسمی</label>
                    <div class="relative">
                        <input type="text" name="price_holiday" x-model="priceHoliday" @input="formatPrice('priceHoliday')" class="{{ $inputClass }} text-left dir-ltr pl-14">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">هزینه نفر اضافه (هر شب به ازای هر نفر)</label>
                    <div class="relative">
                        <input type="text" name="extra_guest_fee" x-model="extraGuestFee" @input="formatPrice('extraGuestFee')" class="{{ $inputClass }} text-left dir-ltr pl-14">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">هزینه ثابت نظافت (اختیاری)</label>
                    <div class="relative">
                        <input type="text" name="cleaning_fee" x-model="cleaningFee" @input="formatPrice('cleaningFee')" class="{{ $inputClass }} text-left dir-ltr pl-14">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">حداقل مدت اقامت</label>
                    <div class="relative">
                        <input type="number" name="min_stay_nights" value="{{ old('min_stay_nights', $rentalConfig->min_stay_nights ?? 1) }}" min="1" max="30" class="{{ $inputClass }} text-center">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400">شب</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- کارت ۳: زمان ورود و خروج و رزرو فوری --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                ساعت تحویل و تحویل‌گیری
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="{{ $labelClass }}">ساعت تحویل اقامتگاه (ورود مهمان)</label>
                    <input type="time" name="check_in_time" value="{{ old('check_in_time', substr($rentalConfig->check_in_time ?? '14:00', 0, 5)) }}" class="{{ $inputClass }} text-center dir-ltr">
                </div>

                <div>
                    <label class="{{ $labelClass }}">ساعت تخلیه اقامتگاه (خروج مهمان)</label>
                    <input type="time" name="check_out_time" value="{{ old('check_out_time', substr($rentalConfig->check_out_time ?? '12:00', 0, 5)) }}" class="{{ $inputClass }} text-center dir-ltr">
                </div>

                <div class="md:col-span-2 pt-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer">
                        <input type="checkbox" name="instant_booking" value="1" {{ ($rentalConfig->instant_booking ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">رزرو قطعی آنی (Instant Booking)</span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">در صورت تایید، مهمان می‌تواند بدون نیاز به تایید تلفنی مستقیم رزرو را ثبت کند.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- کارت ۴: امکانات اقامتگاه --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                امکانات و ویژگی‌های اقامتگاه
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3.5">
                @php
                    $selectedAmenities = $rentalConfig->rental_amenities ?? [];
                @endphp
                @foreach($defaultAmenities as $key => $title)
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-100 dark:border-gray-700/70 bg-gray-50/60 dark:bg-gray-900/20 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition">
                        <input type="checkbox" name="rental_amenities[]" value="{{ $key }}" {{ in_array($key, $selectedAmenities) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 font-sans">{{ $title }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- کارت ۵: قوانین خانه --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                مقررات و قوانین ورود به اقامتگاه
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @php
                    $selectedRules = $rentalConfig->house_rules ?? [];
                @endphp
                @foreach($defaultRules as $rKey => $rTitle)
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-100 dark:border-gray-700/70 bg-gray-50/60 dark:bg-gray-900/20 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition">
                        <input type="checkbox" name="house_rules[]" value="{{ $rKey }}" {{ in_array($rKey, $selectedRules) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 font-sans">{{ $rTitle }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- دکمه ذخیره --}}
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('user.properties.rental.calendar', $property) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 transition">
                رفتن به تقویم روزها
            </a>

            <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                ذخیره تنظیمات اقامتگاه
            </button>
        </div>
    </form>
</div>

<script>
    function rentalConfigForm() {
        return {
            pricePerNight: '{{ old('price_per_night', $rentalConfig->price_per_night ? number_format($rentalConfig->price_per_night) : '') }}',
            priceWeekend: '{{ old('price_weekend', $rentalConfig->price_weekend ? number_format($rentalConfig->price_weekend) : '') }}',
            priceHoliday: '{{ old('price_holiday', $rentalConfig->price_holiday ? number_format($rentalConfig->price_holiday) : '') }}',
            extraGuestFee: '{{ old('extra_guest_fee', $rentalConfig->extra_guest_fee ? number_format($rentalConfig->extra_guest_fee) : '') }}',
            cleaningFee: '{{ old('cleaning_fee', $rentalConfig->cleaning_fee ? number_format($rentalConfig->cleaning_fee) : '') }}',

            formatPrice(field) {
                let val = String(this[field]).replace(/,/g, '').replace(/[^\d]/g, '');
                if (val !== '') {
                    this[field] = parseInt(val).toLocaleString('en-US');
                } else {
                    this[field] = '';
                }
            }
        }
    }
</script>
@endsection
