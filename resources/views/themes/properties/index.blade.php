@extends('layouts.web')

@section('title', 'املاک و مستغلات')

@section('content')
    <div class="w-full flex-grow flex flex-col">

        {{-- 1. Hero Search Advanced --}}
        <div class="max-w-7xl mx-auto px-6 w-full mb-16">
            <div class="bg-gray-900 dark:bg-gray-950 rounded-[3rem] overflow-hidden relative shadow-2xl">
                {{-- Background Image Placeholder --}}
                <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/30 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/2"></div>

                <div class="relative z-10 p-10 md:p-16 text-center space-y-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-medium backdrop-blur-md">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                        پلتفرم هوشمند جستجوی ملک
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-white leading-tight">
                        خانه رویایی خود را <br class="hidden md:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">هوشمندانه پیدا کنید</span>
                    </h1>

                    {{-- Advanced Search Box --}}
                    <form action="{{ route('properties.index') }}" method="GET" class="max-w-4xl mx-auto bg-white/10 dark:bg-black/30 backdrop-blur-xl p-2 md:p-3 rounded-2xl md:rounded-full border border-white/20 shadow-2xl flex flex-col md:flex-row gap-2">
                        <select name="listing_type" class="h-14 bg-white dark:bg-gray-800 rounded-xl md:rounded-full border-0 px-6 font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 cursor-pointer w-full md:w-auto">
                            <option value="">نوع معامله (همه)</option>
                            <option value="sale">خرید</option>
                            <option value="rent">رهن و اجاره</option>
                            <option value="presale">پیش‌فروش</option>
                        </select>

                        <select name="property_type" class="h-14 bg-white dark:bg-gray-800 rounded-xl md:rounded-full border-0 px-6 font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 cursor-pointer w-full md:w-auto">
                            <option value="">نوع ملک (همه)</option>
                            <option value="apartment">آپارتمان</option>
                            <option value="villa">ویلایی</option>
                            <option value="land">زمین و کلنگی</option>
                            <option value="office">اداری</option>
                        </select>

                        <input type="text" name="search" placeholder="نام محله، شهر یا کد ملک..." class="h-14 flex-1 bg-white dark:bg-gray-800 rounded-xl md:rounded-full border-0 px-6 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">

                        <button type="submit" class="h-14 px-8 rounded-xl md:rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg transition-colors w-full md:w-auto flex items-center justify-center gap-2">
                            جستجو
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 w-full pb-16 space-y-20">

            {{-- 2. Featured Properties (Real Data) --}}
            <div>
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">جدیدترین فایل‌ها</h2>
                        <p class="text-gray-500 dark:text-gray-400">بهترین موقعیت‌های سرمایه‌گذاری و سکونت</p>
                    </div>
                    <a href="{{ route('properties.index') }}" class="hidden md:flex text-sm font-bold text-blue-600 hover:text-blue-700 items-center gap-1 bg-blue-50 dark:bg-blue-900/30 px-4 py-2 rounded-xl transition-colors">مشاهده همه <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        // متغیرها از سمت کنترلر پاس داده شده‌اند، اما به عنوان پشتیبان (Fallback) توابع را اینجا هم داریم
                        $getSetting = function($key) {
                            $val = \Modules\Properties\Entities\PropertySetting::get($key);
                            return $val ? json_decode($val, true) : [];
                        };

                        $priceRoles = $priceRoles ?? $getSetting('visibility_price_info');
                        $coverRoles = $coverRoles ?? $getSetting('visibility_cover_image');
                        $mapRoles = $mapRoles ?? $getSetting('visibility_map_info');
                        $showFeaturesInCard = $showFeaturesInCard ?? \Modules\Properties\Entities\PropertySetting::get('show_features_in_card', 1);
                    @endphp

                    @if(isset($latestProperties) && $latestProperties->count() > 0)
                        @foreach($latestProperties as $property)
                            @php
                                $isMyProperty = auth()->check() && ($property->created_by === auth()->id() || $property->agent_id === auth()->id());
                                $cardClass = $isMyProperty
                                    ? 'bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-500 dark:border-blue-400 shadow-xl shadow-blue-200/50 dark:shadow-blue-900/30'
                                    : 'bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none';

                                // Check Price Visibility
                                $canViewPrice = false;
                                if (empty($priceRoles)) $canViewPrice = true;
                                elseif (auth()->check()) {
                                    $user = auth()->user();
                                    if ($user->id == $property->created_by || $user->id == $property->agent_id || $user->hasRole('super-admin') || $user->hasAnyRole($priceRoles)) $canViewPrice = true;
                                } else {
                                    if (in_array('guest', $priceRoles)) $canViewPrice = true;
                                }

                                // Check Cover Visibility
                                $canViewCover = false;
                                if (empty($coverRoles)) $canViewCover = true;
                                elseif (auth()->check()) {
                                    $user = auth()->user();
                                    if ($user->id == $property->created_by || $user->id == $property->agent_id || $user->hasRole('super-admin') || $user->hasAnyRole($coverRoles)) $canViewCover = true;
                                } else {
                                    if (in_array('guest', $coverRoles)) $canViewCover = true;
                                }

                                // Check Map/Address Visibility
                                $canViewMap = false;
                                if (empty($mapRoles)) $canViewMap = true;
                                elseif (auth()->check()) {
                                    $user = auth()->user();
                                    if ($user->id == $property->created_by || $user->id == $property->agent_id || $user->hasRole('super-admin') || $user->hasAnyRole($mapRoles)) $canViewMap = true;
                                } else {
                                    if (in_array('guest', $mapRoles)) $canViewMap = true;
                                }
                            @endphp

                            <a href="{{ route('properties.show', $property->slug) }}"
                               class="group relative flex flex-col backdrop-blur-sm rounded-3xl overflow-hidden hover:-translate-y-1 hover:border-blue-500/30 transition-all duration-300 h-full {{ $cardClass }}">

                                {{-- Image Area --}}
                                <div class="h-60 w-full overflow-hidden bg-gray-100 dark:bg-gray-800 relative border-b border-gray-100 dark:border-gray-800">
                                    @if($canViewCover && $property->cover_image)
                                        <img src="{{ asset('storage/' . $property->cover_image) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @else
                                        <div class="absolute inset-0 bg-gray-200 dark:bg-gray-700 flex items-center justify-center group-hover:scale-105 transition-transform duration-500">
                                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                        </div>
                                    @endif

                                    {{-- Badges --}}
                                    <div class="absolute top-4 right-4 flex flex-col gap-2">
                                        @if($property->status)
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-lg" style="background-color: {{ $property->status->color }};">
                                            {{ $property->status->label ?? $property->status->name }}
                                        </span>
                                        @endif
                                        @if($property->listing_type)
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-lg">
                                            {{ match($property->listing_type) { 'sale' => 'فروش', 'rent' => 'اجاره', 'presale' => 'پیش‌فروش', default => $property->listing_type } }}
                                        </span>
                                        @endif
                                    </div>
                                    @if(isset($property->meta['is_special']) && $property->meta['is_special'])
                                        <div class="absolute top-4 left-4 z-10">
                                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500 text-white shadow-lg animate-pulse flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg> ویژه
                                        </span>
                                        </div>
                                    @endif
                                    @if($isMyProperty)
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                        <span class="px-5 py-1.5 rounded-b-xl text-xs font-black bg-blue-600 text-white shadow-lg flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            آگهی من
                                        </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Content Area --}}
                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="flex flex-col gap-1 mb-4">
                                        @if($canViewPrice)
                                            @if($property->listing_type == 'rent')
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">رهن: {{ $property->deposit_price > 0 ? number_format($property->deposit_price).' تومان' : 'توافقی' }}</div>
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">اجاره: {{ $property->rent_price > 0 ? number_format($property->rent_price).' تومان' : 'توافقی' }}</div>
                                            @else
                                                <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                                    {{ $property->price > 0 ? number_format($property->price) : 'توافقی' }} <span class="text-sm font-normal text-gray-500">{{ $property->price > 0 ? 'تومان' : '' }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400 py-1 bg-blue-50 dark:bg-blue-900/20 px-3 rounded-lg w-max">برای قیمت تماس بگیرید</div>
                                        @endif
                                    </div>

                                    <h3 class="font-bold text-gray-800 dark:text-gray-200 text-lg mb-4 line-clamp-2">{{ $property->title }}</h3>

                                    {{-- Specs Grid (Dynamic) --}}
                                    @if($showFeaturesInCard)
                                        @php
                                            // فیلتر کردن ۲ ویژگی اول که مقدار دارند
                                            $cardDetails = $property->attributeValues ? $property->attributeValues->filter(function($attr) {
                                                return $attr->attribute && $attr->attribute->section === 'details' && $attr->value !== null && $attr->value !== '';
                                            })->take(2) : collect();
                                        @endphp
                                        <div class="grid grid-cols-3 gap-2 py-4 border-y border-gray-100 dark:border-gray-800 mb-4 text-gray-600 dark:text-gray-400 text-sm">

                                            @foreach($cardDetails as $detail)
                                                <div class="flex flex-col items-center justify-center gap-1 {{ $loop->iteration == 2 ? 'border-x border-gray-100 dark:border-gray-800' : '' }}">
                                                    <span class="text-xs text-gray-400">{{ $detail->attribute->name }}</span>
                                                    <span class="font-bold truncate w-full text-center text-blue-600 dark:text-blue-400" title="{{ $detail->value }}">{{ $detail->value }}</span>
                                                </div>
                                            @endforeach

                                            {{-- پر کردن جای خالی --}}
                                            @for($i = $cardDetails->count(); $i < 2; $i++)
                                                <div class="flex flex-col items-center justify-center gap-1 {{ $i == 1 ? 'border-x border-gray-100 dark:border-gray-800' : '' }}">
                                                    <span class="text-xs text-gray-400">-</span>
                                                    <span class="font-bold">-</span>
                                                </div>
                                            @endfor

                                            <div class="flex flex-col items-center justify-center gap-1">
                                                <span class="text-xs text-gray-400">کد ملک</span>
                                                <span class="font-bold font-mono truncate w-full text-center text-blue-600 dark:text-blue-400" title="{{ $property->code ?? '-' }}">{{ $property->code ?? '-' }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between mt-auto pt-2">
                                        <div class="flex items-center gap-2 text-sm font-medium text-gray-500 line-clamp-1 w-3/4">
                                            <svg class="w-5 h-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            <span>
                                            @if($canViewMap)
                                                    {{ $property->address ?? 'آدرس نامشخص' }}
                                                @else
                                                    محدوده ملک (نیازمند دسترسی)
                                                @endif
                                        </span>
                                        </div>
                                        <button class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors shrink-0">
                                            <svg class="w-5 h-5 transform transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هنوز فایلی ثبت نشده است</h3>
                            <p class="text-sm text-gray-500">در حال حاضر هیچ فایل فعالی در سیستم موجود نیست.</p>
                        </div>
                    @endif
                </div>

                <div class="mt-8 text-center md:hidden">
                    <a href="{{ route('properties.index') }}" class="inline-flex text-sm font-bold text-blue-600 bg-blue-50 px-6 py-3 rounded-xl border border-blue-100">مشاهده همه فایل‌ها</a>
                </div>
            </div>

            {{-- 3. Why Us Section --}}
            <div class="bg-blue-50 dark:bg-gray-900/50 rounded-[3rem] p-10 md:p-16 border border-blue-100 dark:border-gray-800">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white leading-tight">چرا پلتفرم ما را برای معاملات ملکی انتخاب کنید؟</h2>
                        <p class="text-gray-600 dark:text-gray-400 text-lg">ما با تلفیق تکنولوژی و تجربه مشاوران حرفه‌ای، فرآیند خرید، فروش و اجاره ملک را به امن‌ترین و شفاف‌ترین حالت ممکن رسانده‌ایم.</p>

                        <ul class="space-y-4 pt-4">
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">کارشناسی تخصصی قیمت تمامی فایل‌ها</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">مشاوره حقوقی رایگان حین عقد قرارداد</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">احراز هویت هوشمند مالکین و مستاجرین</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Decorative element --}}
                    <div class="relative h-full min-h-[300px] rounded-3xl bg-blue-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden border border-blue-200 dark:border-gray-700">
                        <svg class="w-32 h-32 text-blue-500 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        <div class="absolute bottom-4 left-4 right-4 bg-white/90 dark:bg-gray-900/90 backdrop-blur p-4 rounded-2xl shadow-lg border border-white/50 dark:border-gray-700">
                            <div class="text-sm font-bold dark:text-white mb-1">پشتیبانی و مشاوره تلفنی</div>
                            <div class="text-2xl font-black text-blue-600 dir-ltr text-right">۰۲۱-۱۲۳۴۵۶۷۸</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
