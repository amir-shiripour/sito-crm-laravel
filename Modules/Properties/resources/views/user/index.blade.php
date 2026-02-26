@extends('layouts.user')

@php
    $title = 'لیست املاک';
    $badgeClass = "inline-flex items-center px-2 py-1 rounded-md text-xs font-medium";
    $canManageProperties = auth()->user()->can('properties.manage');
    $aiSearchEnabled = \Modules\Properties\Entities\PropertySetting::get('ai_property_search', 0);
    $isTrash = request('trashed') == '1';
    $isAiSearch = request('ai_search') == '1';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6" x-data="propertyList()"
         @speech-result.window="handleSpeechResult($event.detail)"
         @speech-status.window="isVoiceTyping = $event.detail; if(isVoiceTyping) aiQueryBeforeSpeech = aiQuery || ''">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    {{ $isTrash ? 'سطل زباله املاک' : ($isAiSearch ? 'نتایج جستجوی هوشمند' : 'لیست املاک') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    {{ $isTrash ? 'مشاهده و مدیریت املاک حذف شده' : ($isAiSearch ? 'نتایج یافت‌شده بر اساس تحلیل هوش مصنوعی' : 'مدیریت و مشاهده وضعیت املاک ثبت شده') }}
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                @if(!$isTrash)
                    @if($aiSearchEnabled)
                        <button @click="showAiModal = true"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-bold hover:bg-purple-700 hover:shadow-lg hover:shadow-purple-500/30 transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            جستجوی هوشمند
                        </button>
                    @endif

                    <a href="{{ route('user.properties.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        افزودن ملک جدید
                    </a>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <a href="{{ route('user.properties.index') }}"
                   class="{{ !$isTrash ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    همه املاک
                </a>
                <a href="{{ route('user.properties.index', ['trashed' => 1]) }}"
                   class="{{ $isTrash ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    سطل زباله
                </a>
            </nav>
        </div>

        {{-- Filters --}}
        @if(!$isTrash)
            @if($isAiSearch)
                {{-- AI Search Results Box --}}
                <div class="bg-purple-50 dark:bg-gray-800/50 rounded-2xl border border-purple-200 dark:border-purple-700 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-purple-800 dark:text-purple-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            فیلترهای هوشمند اعمال شده
                        </h3>
                        <a href="{{ route('user.properties.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            حذف فیلتر هوشمند
                        </a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(request()->all() as $key => $value)
                            @if(!in_array($key, ['ai_search', 'page']) && !empty($value))
                                @php
                                    $label = '';
                                    $displayValue = '';
                                    switch ($key) {
                                        case 'search': $label = 'کلمات کلیدی'; $displayValue = $value; break;
                                        case 'listing_type': $label = 'نوع معامله'; $displayValue = match($value) {'sale' => 'فروش', 'rent' => 'رهن/اجاره', 'presale' => 'پیش‌فروش', default => $value}; break;
                                        case 'property_type': $label = 'نوع ملک'; $displayValue = match($value) {'apartment' => 'آپارتمان', 'villa' => 'ویلا', 'land' => 'زمین', 'office' => 'اداری', default => $value}; break;
                                        case 'category_id': $label = 'دسته‌بندی'; $displayValue = $categories->find($value)?->name ?? $value; break;
                                        case 'price_min': $label = 'حداقل قیمت'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'price_max': $label = 'حداکثر قیمت'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'deposit_min': $label = 'حداقل رهن'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'deposit_max': $label = 'حداکثر رهن'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'rent_min': $label = 'حداقل اجاره'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'rent_max': $label = 'حداکثر اجاره'; $displayValue = number_format((float)$value) . ' تومان'; break;
                                        case 'details':
                                            if (is_array($value)) {
                                                $label = 'جزئیات';
                                                $detailsArr = [];
                                                foreach($value as $attrId => $attrVal) {
                                                    $attrName = $propertyAttributes->find($attrId)?->name ?? "ویژگی {$attrId}";
                                                    if(is_array($attrVal)) {
                                                        $min = $attrVal['min'] ?? null;
                                                        $max = $attrVal['max'] ?? null;
                                                        if($min && $max) $detailsArr[] = "{$attrName}: {$min}-{$max}";
                                                        elseif($min) $detailsArr[] = "{$attrName}: از {$min}";
                                                        elseif($max) $detailsArr[] = "{$attrName}: تا {$max}";
                                                    } else {
                                                        $detailsArr[] = "{$attrName}: {$attrVal}";
                                                    }
                                                }
                                                $displayValue = implode('، ', $detailsArr);
                                            }
                                            break;
                                        case 'features':
                                            if (is_array($value)) {
                                                $label = 'امکانات';
                                                $featuresArr = [];
                                                foreach($value as $featureId) {
                                                    $featuresArr[] = $propertyAttributes->find($featureId)?->name ?? "امکان {$featureId}";
                                                }
                                                $displayValue = implode('، ', $featuresArr);
                                            }
                                            break;
                                    }
                                @endphp
                                @if($label && $displayValue)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        <strong>{{ $label }}:</strong> {{ $displayValue }}
                                    </span>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Advanced Filter Form --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            فیلترهای پیشرفته
                        </h2>
                        @if(request()->except('page'))
                            <a href="{{ route('user.properties.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                حذف فیلترها
                            </a>
                        @endif
                    </div>
                    <div class="p-5">
                        <form action="{{ route('user.properties.index') }}" method="GET">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                @include('properties::user.partials.filter-inputs')
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        {{-- Properties Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">ملک</th>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">قیمت</th>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 hidden sm:table-cell">نوع</th>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">وضعیت</th>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 hidden md:table-cell">دسته‌بندی</th>
                            @if($canManageProperties)
                                <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 hidden lg:table-cell">مشاور</th>
                                <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 hidden lg:table-cell">ایجاد کننده</th>
                            @endif
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($properties as $property)
                            @include('properties::user.partials.property-row', ['property' => $property])
                        @empty
                            @include('properties::user.partials.empty-row')
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($properties->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $properties->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

        {{-- AI Search Modal --}}
        @if($aiSearchEnabled)
            @include('properties::user.partials.ai-search-modal')
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ---------- VANILLA JS SPEECH RECOGNITION ----------
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
                            if (isIOS) {
                                transcript = transcript.replace(/ي/g, "ی").replace(/ك/g, "ک");
                            }
                            window.dispatchEvent(new CustomEvent('speech-result', {
                                detail: { transcript: transcript, isFinal: isFinal }
                            }));
                        }
                    };

                    recognition.onerror = function(event) {
                        isRecording = false;
                        window.dispatchEvent(new CustomEvent('speech-status', { detail: false }));
                        console.error('Speech Recognition Error:', event.error);
                        if (event.error !== 'no-speech') {
                            let errorMsg = 'خطا در تشخیص صدا (' + event.error + ').';
                            if (event.error === 'not-allowed') {
                                errorMsg = 'دسترسی میکروفون رد شد.';
                            } else if (event.error === 'service-not-allowed') {
                                errorMsg = 'سرویس صوتی مسدود شد.';
                            }
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: errorMsg } }));
                        }
                    };

                    recognition.onend = function() {
                        isRecording = false;
                        window.dispatchEvent(new CustomEvent('speech-status', { detail: false }));
                    };
                }

                voiceBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (isRecording && recognition) {
                        try { recognition.stop(); } catch(err) {}
                    } else {
                        initRecognition();
                        try {
                            recognition.start();
                        } catch (err) {
                            console.error("Speech Recognition Start Exception", err);
                        }
                    }
                }, false);
            }
        });

        function getVoiceSupportTooltip() {
            if (!window.isSecureContext) {
                return 'برای تایپ صوتی به اتصال امن (HTTPS) نیاز است.';
            }
            if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) {
                return 'مرورگر شما از تایپ صوتی پشتیبانی نمی‌کند.';
            }
            return '';
        }

        function propertyList() {
            return {
                showAiModal: false,
                aiQuery: '',
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
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('{{ route("user.properties.ai.search") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ query: this.aiQuery })
                        });
                        const result = await response.json();
                        if (response.ok && result.redirect_url) {
                            window.location.href = result.redirect_url;
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: result.error || 'خطا در جستجو.' } }));
                            this.isAiSearching = false;
                        }
                    } catch (error) {
                        console.error('AI Search Error:', error);
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطا در ارتباط با سرور.' } }));
                        this.isAiSearching = false;
                    }
                }
            }
        }
    </script>
@endsection
