@extends('layouts.web')

@section('title', 'لیست املاک')

@push('styles')
    @livewireStyles
@endpush

@section('content')
    <div class="max-w-7xl mx-auto w-full px-6 space-y-8 pb-16 pt-8"
         x-data="propertyList()"
         @speech-result.window="handleSpeechResult($event.detail)"
         @speech-status.window="isVoiceTyping = $event.detail; if(isVoiceTyping) aiQueryBeforeSpeech = aiQuery || ''">

        @php
            $aiSearchEnabled = \Modules\Properties\Entities\PropertySetting::get('ai_property_search', 0);

            // Helper function to decode visibility settings safely outside the loop
            $getSetting = function($key) {
                $val = \Modules\Properties\Entities\PropertySetting::get($key);
                return $val ? json_decode($val, true) : [];
            };

            $priceRoles = $getSetting('visibility_price_info');
            $coverRoles = $getSetting('visibility_cover_image');
            $mapRoles = $getSetting('visibility_map_info');
        @endphp

        {{-- Header Section --}}
        <div class="text-center space-y-4 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-xl mb-4 shadow-indigo-500/30 rotate-3 hover:rotate-0 transition-transform duration-300">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight">لیست <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">املاک</span></h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                ملک مورد نظر خود را با فیلترهای پیشرفته جستجو کنید و جزئیات آن را مشاهده نمایید.
            </p>
        </div>

        {{-- AI Search Trigger --}}
        @if($aiSearchEnabled)
            <div class="bg-purple-50 dark:bg-gray-800/50 rounded-2xl border border-purple-200 dark:border-purple-700 shadow-sm p-5 flex flex-col md:flex-row items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-5 duration-700 delay-50">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-purple-900 dark:text-purple-200">جستجوی هوشمند (AI)</h3>
                        <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">ملک مورد نظر خود را به زبان ساده (صوتی یا متنی) توصیف کنید.</p>
                    </div>
                </div>
                <button @click="showAiModal = true"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-lg shadow-purple-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    شروع جستجو
                </button>
            </div>
        @endif

        {{-- Advanced Search Box --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl p-6 mb-12 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <form action="{{ route('properties.index') }}" method="GET" class="space-y-6">
                {{-- Top Row: Search & Main Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search Input --}}
                    <div class="md:col-span-4 lg:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">جستجو</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="عنوان، کد، آدرس..."
                                   class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 pl-10 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900 shadow-sm">
                            <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Listing Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع معامله</label>
                        <select name="listing_type" id="listing_type" onchange="togglePriceInputs()" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900 shadow-sm cursor-pointer">
                            <option value="">همه موارد</option>
                            <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
                            <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                            <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
                        </select>
                    </div>

                    {{-- Property Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع ملک</label>
                        <select name="property_type" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900 shadow-sm cursor-pointer">
                            <option value="">همه موارد</option>
                            <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                            <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
                            <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
                            <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
                            <option value="store" {{ request('property_type') == 'store' ? 'selected' : '' }}>مغازه</option>
                        </select>
                    </div>

                    {{-- Document Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع سند</label>
                        <select name="document_type" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900 shadow-sm cursor-pointer">
                            <option value="">همه موارد</option>
                            @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ request('document_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dynamic Attributes & Features Filter --}}
                @php
                    $hasAttributes = isset($filterableAttributes) && $filterableAttributes->count() > 0;
                    $hasFeatures = isset($features) && $features->count() > 0;
                @endphp

                @if($hasAttributes || $hasFeatures || isset($categories) || isset($buildings))
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">

                        @if(isset($categories))
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                                <select name="category_id" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                    <option value="">همه</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(isset($buildings))
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ساختمان مجتمع</label>
                                <select name="building_id" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                    <option value="">همه</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Attributes --}}
                        @if($hasAttributes)
                            @foreach($filterableAttributes as $attribute)
                                @if($attribute->is_range_filter && $attribute->type === 'number')
                                    <div class="md:col-span-2 grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل {{ $attribute->name }}</label>
                                            <input type="number" name="min_attr_{{ $attribute->id }}" value="{{ request('min_attr_' . $attribute->id) }}" placeholder="حداقل..."
                                                   class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر {{ $attribute->name }}</label>
                                            <input type="number" name="max_attr_{{ $attribute->id }}" value="{{ request('max_attr_' . $attribute->id) }}" placeholder="حداکثر..."
                                                   class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attribute->name }}</label>
                                        @if($attribute->type === 'select')
                                            <select name="attr_{{ $attribute->id }}" class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                                <option value="">همه</option>
                                                @if(is_array($attribute->options))
                                                    @foreach($attribute->options as $option)
                                                        <option value="{{ $option }}" {{ request('attr_' . $attribute->id) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @else
                                            <input type="text" name="attr_{{ $attribute->id }}" value="{{ request('attr_' . $attribute->id) }}" placeholder="..."
                                                   class="w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        {{-- Features Checkboxes --}}
                        @if($hasFeatures)
                            <div class="md:col-span-4 mt-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">امکانات رفاهی</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    @foreach($features as $feature)
                                        <label class="flex items-center gap-2 p-2.5 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm bg-white dark:bg-gray-900">
                                            <input type="checkbox" name="features[]" value="{{ $feature->id }}" {{ in_array($feature->id, request('features', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $feature->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Bottom Row: Price Range --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{-- Price Range (Sale) --}}
                    <div id="sale_price_inputs" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('listing_type') == 'rent' ? 'hidden' : '' }}">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل قیمت خرید (تومان)</label>
                            <input type="text" name="min_price" value="{{ request('min_price') }}" placeholder="مثلا: 10,000,000" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر قیمت خرید (تومان)</label>
                            <input type="text" name="max_price" value="{{ request('max_price') }}" placeholder="مثلا: 5,000,000,000" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                        </div>
                    </div>

                    {{-- Price Range (Rent) --}}
                    <div id="rent_price_inputs" class="grid grid-cols-1 lg:grid-cols-2 gap-6 {{ request('listing_type') == 'rent' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل رهن</label>
                                <input type="text" name="min_deposit_price" value="{{ request('min_deposit_price') }}" placeholder="تومان" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر رهن</label>
                                <input type="text" name="max_deposit_price" value="{{ request('max_deposit_price') }}" placeholder="تومان" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل اجاره</label>
                                <input type="text" name="min_rent_price" value="{{ request('min_rent_price') }}" placeholder="تومان" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر اجاره</label>
                                <input type="text" name="max_rent_price" value="{{ request('max_rent_price') }}" placeholder="تومان" class="price-input w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 focus:border-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm dir-ltr text-left">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="special" value="1" {{ request('special') ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                            <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-amber-600 transition-colors">فقط آگهی‌های ویژه</span>
                        </label>

                        @auth
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">نمایش همه املاک</span>
                            </label>
                        @endauth
                    </div>

                    <div class="flex w-full sm:w-auto gap-3">
                        <a href="{{ route('properties.index') }}" class="flex-1 sm:flex-none text-center px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                            پاک کردن
                        </a>
                        <button type="submit" class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/50 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            جستجو
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Properties Grid --}}
        @if($properties->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                @foreach($properties as $property)
                    @php
                        $isMyProperty = auth()->check() && ($property->created_by === auth()->id() || $property->agent_id === auth()->id());
                        $cardClass = $isMyProperty
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-500 dark:border-indigo-400 shadow-xl shadow-indigo-200/50 dark:shadow-indigo-900/30 ring-2 ring-indigo-200 dark:ring-indigo-800 ring-offset-2 dark:ring-offset-gray-900'
                            : 'bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none';

                        // بررسی دسترسی به قیمت
                        $canViewPrice = false;
                        if (empty($priceRoles)) $canViewPrice = true;
                        elseif (auth()->check()) {
                            $user = auth()->user();
                            if ($user->id == $property->created_by || $user->id == $property->agent_id || $user->hasRole('super-admin') || $user->hasAnyRole($priceRoles)) $canViewPrice = true;
                        } else {
                            if (in_array('guest', $priceRoles)) $canViewPrice = true;
                        }

                        // بررسی دسترسی به تصویر کاور
                        $canViewCover = false;
                        if (empty($coverRoles)) $canViewCover = true;
                        elseif (auth()->check()) {
                            $user = auth()->user();
                            if ($user->id == $property->created_by || $user->id == $property->agent_id || $user->hasRole('super-admin') || $user->hasAnyRole($coverRoles)) $canViewCover = true;
                        } else {
                            if (in_array('guest', $coverRoles)) $canViewCover = true;
                        }

                        // بررسی دسترسی به نقشه و آدرس دقیق
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
                       class="group relative flex flex-col backdrop-blur-sm rounded-3xl hover:border-indigo-500/50 transition-all duration-300 overflow-hidden hover:-translate-y-2 h-full {{ $cardClass }}">

                        {{-- Image Section --}}
                        <div class="relative h-60 w-full overflow-hidden bg-gray-100 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-800">
                            @if($canViewCover && $property->cover_image)
                                <img src="{{ asset('storage/' . $property->cover_image) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    @if(!$canViewCover)
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span class="text-xs font-bold">تصویر محدود</span>
                                        </div>
                                    @else
                                        <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    @endif
                                </div>
                            @endif

                            @if($isMyProperty)
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                <span class="px-5 py-1.5 rounded-b-xl text-xs font-black bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    آگهی من
                                </span>
                                </div>
                            @endif

                            @if(isset($property->meta['is_special']) && $property->meta['is_special'])
                                <div class="absolute top-4 left-4 z-10">
                                <span class="px-3 py-1.5 rounded-lg text-xs font-black bg-amber-500 text-white shadow-lg shadow-amber-500/30 flex items-center gap-1 animate-pulse">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                    فایل ویژه
                                </span>
                                </div>
                            @endif

                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                @if($property->status)
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-black text-white shadow-lg" style="background-color: {{ $property->status->color }};">
                                    {{ $property->status->label ?? $property->status->name }}
                                </span>
                                @endif
                                @if($property->listing_type)
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-black bg-indigo-600 text-white shadow-lg shadow-indigo-600/30">
                                    {{ match($property->listing_type) { 'sale' => 'فروش', 'rent' => 'اجاره', 'presale' => 'پیش‌فروش', default => $property->listing_type } }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex flex-col gap-1 mb-5">
                                @if($canViewPrice)
                                    @if($property->listing_type == 'rent')
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">رهن:</span>
                                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ $property->deposit_price > 0 ? number_format($property->deposit_price).' تومان' : 'توافقی' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">اجاره:</span>
                                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ $property->rent_price > 0 ? number_format($property->rent_price).' تومان' : 'توافقی' }}</span>
                                        </div>
                                    @else
                                        <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                                            {{ $property->price > 0 ? number_format($property->price) : 'توافقی' }} <span class="text-xs font-normal text-gray-500">{{ $property->price > 0 ? 'تومان' : '' }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm font-black text-indigo-600 dark:text-indigo-400 py-1 bg-indigo-50 dark:bg-indigo-900/20 px-3 rounded-lg w-max">برای قیمت تماس بگیرید</div>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 line-clamp-2 mb-4 leading-tight">
                                {{ $property->title }}
                            </h3>

                            {{-- Specs Grid (Dynamic based on Attributes) --}}
                            @php
                                $showFeaturesInCard = \Modules\Properties\Entities\PropertySetting::get('show_features_in_card', 1);
                            @endphp
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
                                            <span class="font-bold truncate w-full text-center text-indigo-600 dark:text-indigo-400" title="{{ $detail->value }}">{{ $detail->value }}</span>
                                        </div>
                                    @endforeach

                                    {{-- Fill empty slots if less than 2 details --}}
                                    @for($i = $cardDetails->count(); $i < 2; $i++)
                                        <div class="flex flex-col items-center justify-center gap-1 {{ $i == 1 ? 'border-x border-gray-100 dark:border-gray-800' : '' }}">
                                            <span class="text-xs text-gray-400">-</span>
                                            <span class="font-bold">-</span>
                                        </div>
                                    @endfor

                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <span class="text-xs text-gray-400">کد ملک</span>
                                        <span class="font-bold font-mono truncate w-full text-center text-indigo-600 dark:text-indigo-400" title="{{ $property->code ?? '-' }}">{{ $property->code ?? '-' }}</span>
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
                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5 transform transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center w-full overflow-x-auto pb-4">
                {{ $properties->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl p-16 text-center animate-in fade-in zoom-in duration-500 max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 dark:bg-gray-800 mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">ملکی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">با فیلترهای اعمال شده هیچ نتیجه‌ای در سیستم پیدا نشد.</p>
                <a href="{{ route('properties.index') }}" class="inline-block mt-6 px-6 py-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl font-bold hover:bg-indigo-100 transition-colors">پاک کردن تمامی فیلترها</a>
            </div>
        @endif

        {{-- AI Search Modal --}}
        @if($aiSearchEnabled)
            <div x-show="showAiModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] overflow-y-auto"
                 style="display: none;">

                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showAiModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showAiModal"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-gray-100 dark:border-gray-700">

                        <div class="bg-purple-50/50 dark:bg-purple-900/20 px-6 py-5 border-b border-purple-100 dark:border-purple-800/50 flex items-center justify-between">
                            <h3 class="text-xl font-black text-purple-900 dark:text-purple-100 flex items-center gap-2">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                جستجوی هوشمند
                            </h3>
                            <button @click="showAiModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                    مشخصات ملک را به زبان ساده بگویید.
                                </p>
                                <div x-data="{ tooltip: getVoiceSupportTooltip() }">
                                    <button type="button" id="ai-voice-btn" :disabled="!isVoiceTypingSupported"
                                            class="text-xs font-bold flex items-center gap-1.5 px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                            :class="{
                                        'bg-purple-100 text-purple-700 hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:text-purple-700': !isVoiceTyping,
                                        'bg-rose-500 text-white hover:bg-rose-600 animate-pulse shadow-rose-500/30': isVoiceTyping
                                    }">
                                        <svg x-show="!isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14a2 2 0 0 0 2-2V6a2 2 0 0 0-4 0v6a2 2 0 0 0 2 2Zm-2-8a2 2 0 0 1 4 0v6a2 2 0 0 1-4 0V6Zm8 5a1 1 0 0 0-1 1v1a5 5 0 0 1-10 0v-1a1 1 0 1 0-2 0v1a7 7 0 0 0 6 6.92V21a1 1 0 1 0 2 0v-2.08A7 7 0 0 0 20 12v-1a1 1 0 0 0-1-1Z"/></svg>
                                        <svg x-show="isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.793 5.793a1 1 0 0 1 1.414 0L12 10.586l4.793-4.793a1 1 0 1 1 1.414 1.414L13.414 12l4.793 4.793a1 1 0 0 1-1.414 1.414L12 13.414l-4.793 4.793a1 1 0 0 1-1.414-1.414L10.586 12 5.793 7.207a1 1 0 0 1 0-1.414Z"/></svg>
                                        <span x-text="isVoiceTyping ? 'توقف ضبط' : 'تایپ صوتی'"></span>
                                    </button>
                                </div>
                            </div>

                            <textarea x-model="aiQuery" rows="5" class="w-full rounded-2xl border-gray-200 bg-white dark:bg-gray-900 px-4 py-3 text-base text-gray-900 focus:border-purple-50 focus:ring-2 focus:ring-purple-500/20 transition-all dark:border-gray-700 dark:text-white resize-none shadow-inner" placeholder="مثلاً: یک آپارتمان دو خوابه نوساز در سعادت آباد با قیمت حدود ۵ میلیارد تومان که قابل معاوضه باشه..."></textarea>

                            @auth
                                <div class="flex items-center justify-end">
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" x-model="aiShowAll" class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                                        <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-purple-600 transition-colors">جستجو در تمامی فایل‌های سیستم</span>
                                    </label>
                                </div>
                            @endauth
                        </div>

                        <div class="bg-gray-50/80 dark:bg-gray-900/80 px-6 py-5 flex gap-3 border-t border-gray-100 dark:border-gray-800 backdrop-blur-sm">
                            <button type="button" @click="performAiSearch" :disabled="isAiSearching || aiQuery.length < 3"
                                    class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-purple-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!isAiSearching">تحلیل و جستجو</span>
                                <span x-show="isAiSearching" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            هوش مصنوعی در حال تحلیل...
                        </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div> {{-- پایان کامپوننت اصلی x-data --}}

@endsection

@push('scripts')
    <script>
        function togglePriceInputs() {
            const listingType = document.getElementById('listing_type').value;
            const saleInputs = document.getElementById('sale_price_inputs');
            const rentInputs = document.getElementById('rent_price_inputs');

            if (listingType === 'rent') {
                if(saleInputs) saleInputs.classList.add('hidden');
                if(rentInputs) rentInputs.classList.remove('hidden');
            } else {
                if(saleInputs) saleInputs.classList.remove('hidden');
                if(rentInputs) rentInputs.classList.add('hidden');
            }
        }

        function formatNumber(n) {
            let value = n.replace(/\D/g, "");
            return value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        document.addEventListener('DOMContentLoaded', function() {
            togglePriceInputs();
            const priceInputs = document.querySelectorAll('.price-input');
            priceInputs.forEach(input => {
                if (input.value) input.value = formatNumber(input.value);
                input.addEventListener('input', function() { this.value = formatNumber(this.value); });
            });

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    priceInputs.forEach(input => { input.value = input.value.replace(/,/g, ''); });
                });
            }
        });
    </script>

    @if(isset($aiSearchEnabled) && $aiSearchEnabled)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                const voiceBtn = document.getElementById('ai-voice-btn');

                if (SpeechRecognition && voiceBtn) {
                    let recognition = null;
                    let isRecording = false;

                    function initRecognition() {
                        recognition = new SpeechRecognition();
                        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                        recognition.lang = 'fa-IR';
                        recognition.continuous = false;
                        recognition.interimResults = true;

                        recognition.onstart = function() {
                            isRecording = true;
                            window.dispatchEvent(new CustomEvent('speech-status', { detail: true }));
                        };

                        recognition.onresult = function(event) {
                            let result = event.results[0];
                            let transcript = result[0].transcript;
                            let isFinal = result.isFinal;

                            if (transcript) {
                                if (isIOS) transcript = transcript.replace(/ي/g, "ی").replace(/ك/g, "ک");
                                window.dispatchEvent(new CustomEvent('speech-result', { detail: { transcript: transcript, isFinal: isFinal } }));
                            }
                        };

                        recognition.onerror = function(event) {
                            isRecording = false;
                            window.dispatchEvent(new CustomEvent('speech-status', { detail: false }));
                            if (event.error !== 'no-speech') {
                                let errorMsg = 'خطا در تشخیص صدا (' + event.error + ').';
                                if (event.error === 'not-allowed') errorMsg = 'دسترسی میکروفون رد شد.';
                                else if (event.error === 'service-not-allowed') errorMsg = 'سرویس صوتی مسدود شد.';
                                alert(errorMsg);
                            }
                        };

                        recognition.onend = function() {
                            isRecording = false;
                            window.dispatchEvent(new CustomEvent('speech-status', { detail: false }));
                        };
                    }

                    voiceBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (isRecording && recognition) { try { recognition.stop(); } catch(err) {} }
                        else { initRecognition(); try { recognition.start(); } catch (err) { console.error(err); } }
                    }, false);
                }
            });

            function getVoiceSupportTooltip() {
                if (!window.isSecureContext) return 'برای تایپ صوتی به اتصال امن (HTTPS) نیاز است.';
                if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) return 'مرورگر شما پشتیبانی نمی‌کند.';
                return '';
            }

            function propertyList() {
                return {
                    showAiModal: false,
                    aiQuery: '',
                    aiShowAll: {{ request('show_all') == '1' ? 'true' : 'false' }},
                    isAiSearching: false,
                    isVoiceTyping: false,
                    isVoiceTypingSupported: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
                    aiQueryBeforeSpeech: '',

                    handleSpeechResult(detail) {
                        const transcript = detail.transcript;
                        let prefix = this.aiQueryBeforeSpeech ? this.aiQueryBeforeSpeech.trim() + ' ' : '';
                        this.aiQuery = prefix + transcript;
                    },

                    async performAiSearch() {
                        if (this.aiQuery.length < 3) return;
                        this.isAiSearching = true;
                        try {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                            const url = '{{ route("properties.ai.search.public") }}';

                            const response = await fetch(url, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                body: JSON.stringify({ query: this.aiQuery, show_all: this.aiShowAll })
                            });

                            const result = await response.json();

                            if (response.ok && result.redirect_url) {
                                let finalUrl = result.redirect_url;
                                if (this.aiShowAll) {
                                    const urlObj = new URL(finalUrl, window.location.origin);
                                    urlObj.searchParams.set('show_all', '1');
                                    finalUrl = urlObj.toString();
                                }
                                window.location.href = finalUrl;
                            } else {
                                alert(result.error || 'خطا در جستجو.');
                                this.isAiSearching = false;
                            }
                        } catch (error) {
                            alert('خطا در ارتباط با سرور.');
                            this.isAiSearching = false;
                        }
                    }
                }
            }
        </script>
    @endif
@endpush
