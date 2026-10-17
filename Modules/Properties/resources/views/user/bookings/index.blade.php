@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('layouts.user')

@php
    $title = $canManageAll ? 'مدیریت جامع رزرواسیون اقامتگاه‌ها' : 'مدیریت رزرواسیون‌های من';
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans" x-data="{
        advancedOpen: {{ $activeFiltersCount > 0 ? 'true' : 'false' }},
        showCancelModal: false,
        cancelActionUrl: '',
        cancelBookingCode: '',
        cancelReason: '',
        openCancel(url, code) {
            this.cancelActionUrl = url;
            this.cancelBookingCode = code;
            this.cancelReason = '';
            this.showCancelModal = true;
        },
        showRejectModal: false,
        rejectActionUrl: '',
        rejectBookingCode: '',
        rejectReason: '',
        openReject(url, code) {
            this.rejectActionUrl = url;
            this.rejectBookingCode = code;
            this.rejectReason = '';
            this.showRejectModal = true;
        }
    }">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('user.properties.index') }}" class="hover:text-indigo-600 transition">املاک و اقامتگاه‌ها</a>
                <span>&bull;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">رزرواسیون اقامتگاه</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                    <span>{{ $title }}</span>
                </h1>
                @if($canManageAll)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/40">
                        مدیریت پلتفرم
                    </span>
                @elseif($host)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/40">
                        پنل میزبان: {{ $host->display_name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($host)
                <a href="{{ route('user.properties.hosts.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2 -2m0 0l7 -7l7 7m2 2l-2 2m-2 -2v10a1 1 0 0 1 -1 1h-3m-6 0a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-6"/></svg>
                    <span>داشبورد میزبان</span>
                </a>
            @endif
            <a href="{{ route('user.properties.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>لیست اقامتگاه‌ها</span>
            </a>
        </div>
    </div>

    {{-- نوار ابزار اصلی و کنترل‌های فیلتر --}}
    <div class="bg-white dark:bg-gray-800/95 backdrop-blur-md rounded-3xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
        
        {{-- ردیف اول: جستجوی سریع + دکمه فیلترهای پیشرفته + پاک‌سازی --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('user.properties.bookings.index') }}" class="relative flex-1">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('host_id') && request('host_id') !== 'all')
                    <input type="hidden" name="host_id" value="{{ request('host_id') }}">
                @endif
                @if(request('property_id') && request('property_id') !== 'all')
                    <input type="hidden" name="property_id" value="{{ request('property_id') }}">
                @endif
                @if(request('payment_status') && request('payment_status') !== 'all')
                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                @endif
                @if(request('date_from'))
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                @endif
                @if(request('date_to'))
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                @endif

                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجوی سریع کد رزرو، نام مهمان، شماره تماس، عنوان یا کد ملک..."
                           class="w-full rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 pr-11 pl-10 py-2.5 text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans shadow-xs">
                    <div class="absolute right-3.5 top-3 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('user.properties.bookings.index', request()->except('search')) }}"
                           class="absolute left-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-0.5"
                           title="پاک کردن متن جستجو">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                {{-- دکمه تاگل فیلترهای پیشرفته --}}
                <button type="button"
                        @click="advancedOpen = !advancedOpen"
                        :class="advancedOpen ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 border-indigo-600' : 'bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800'"
                        class="px-4 py-2.5 rounded-2xl border text-xs font-black transition-all flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>فیلترهای پیشرفته</span>
                    @if($activeFiltersCount > 0)
                        <span class="w-5 h-5 rounded-full text-[10px] font-black flex items-center justify-center transition"
                              :class="advancedOpen ? 'bg-white text-indigo-700' : 'bg-indigo-600 text-white dark:bg-indigo-500'">
                            {{ CalendarUtils::convertNumbers($activeFiltersCount) }}
                        </span>
                    @endif
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="advancedOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                @if($activeFiltersCount > 0)
                    <a href="{{ route('user.properties.bookings.index') }}"
                       class="px-3.5 py-2.5 rounded-2xl border border-red-200/80 dark:border-red-900/60 bg-red-50/70 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/60 text-xs font-black transition flex items-center gap-1.5 shrink-0"
                       title="پاک‌سازی تمام فیلترها">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="hidden sm:inline">پاکسازی فیلترها</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- پنل بازشونده فیلترهای پیشرفته و سلکتورها --}}
        <div x-show="advancedOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="pt-4 border-t border-gray-100 dark:border-gray-700/80">
            
            <form method="GET" action="{{ route('user.properties.bookings.index') }}" id="advancedFilterForm" class="space-y-4">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- ۱. سلکتور پیشرفته میزبان (مخصوص ادمین‌ها) --}}
                    @if($canManageAll && $hostsList->isNotEmpty())
                        @php
                            $currentHost = $selectedHostId !== 'all' ? $hostsList->firstWhere('id', $selectedHostId) : null;
                        @endphp
                        <div class="space-y-1.5" x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $selectedHostId }}',
                                selectedName: '{{ $currentHost ? $currentHost->display_name : 'همه میزبان‌ها' }}',
                                selectedPhone: '{{ $currentHost ? $currentHost->phone : '' }}',
                                hosts: {{ Js::from($hostsList->map(fn($h) => [
                                    'id' => (string)$h->id,
                                    'name' => $h->display_name,
                                    'phone' => $h->phone ?? '',
                                    'count' => $h->rental_bookings_count ?? 0,
                                    'avatar' => $h->avatar ? asset('storage/' . $h->avatar) : null
                                ])) }},
                                get filteredHosts() {
                                    if (!this.search.trim()) return this.hosts;
                                    const q = this.search.toLowerCase();
                                    return this.hosts.filter(h => h.name.toLowerCase().includes(q) || h.phone.includes(q));
                                },
                                selectHost(id, name, phone) {
                                    this.selectedId = id;
                                    this.selectedName = name;
                                    this.selectedPhone = phone;
                                    this.open = false;
                                    $refs.hostHiddenInput.value = id;
                                },
                                resetHost() {
                                    this.selectHost('all', 'همه میزبان‌ها', '');
                                }
                            }">
                            <label class="block text-[11px] font-black text-gray-600 dark:text-gray-300">میزبان اقامتگاه:</label>
                            <input type="hidden" name="host_id" :value="selectedId" x-ref="hostHiddenInput">

                            <div class="relative">
                                <button type="button"
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-2 rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all text-right shadow-xs">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <span class="truncate font-black" x-text="selectedName"></span>
                                        <span x-show="selectedPhone" class="text-[10px] text-gray-400 dir-ltr" x-text="selectedPhone"></span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- پاپ‌آپ دراپ‌داون جستجودار میزبان --}}
                                <div x-cloak x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute right-0 left-0 mt-2 z-50 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-2 space-y-2">
                                    
                                    {{-- اینپوت فیلتر زنده داخل دراپ‌داون --}}
                                    <div class="relative">
                                        <input type="text"
                                               x-model="search"
                                               placeholder="جستجوی نام یا تلفن میزبان..."
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 pr-8 pl-3 py-1.5 text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 transition-all font-sans">
                                        <div class="absolute right-2.5 top-2 text-gray-400 pointer-events-none">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </div>

                                    <div class="max-h-52 overflow-y-auto space-y-1 scrollbar-thin">
                                        <div @click="resetHost()"
                                             class="flex items-center justify-between p-2 rounded-xl text-xs font-bold cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300"
                                             :class="selectedId === 'all' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-black' : ''">
                                            <span>همه میزبان‌ها</span>
                                            <svg x-show="selectedId === 'all'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>

                                        <template x-for="h in filteredHosts" :key="h.id">
                                            <div @click="selectHost(h.id, h.name, h.phone)"
                                                 class="flex items-center justify-between p-2 rounded-xl text-xs font-bold cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200"
                                                 :class="selectedId === h.id ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-black' : ''">
                                                <div class="flex items-center gap-2 truncate">
                                                    <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[10px] font-black text-gray-600 dark:text-gray-300 shrink-0" x-text="h.name.charAt(0)"></span>
                                                    <span class="truncate" x-text="h.name"></span>
                                                    <span class="text-[10px] text-gray-400 dir-ltr" x-text="h.phone"></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-500 font-normal" x-text="h.count + ' رزرو'"></span>
                                                    <svg x-show="selectedId === h.id" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="filteredHosts.length === 0" class="py-4 text-center text-xs text-gray-400">
                                            میزبانی با این مشخصات پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ۲. سلکتور پیشرفته اقامتگاه --}}
                    @php
                        $currentProperty = $selectedPropertyId !== 'all' ? $propertiesList->firstWhere('id', $selectedPropertyId) : null;
                    @endphp
                    <div class="space-y-1.5" x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $selectedPropertyId }}',
                            selectedTitle: '{{ $currentProperty ? $currentProperty->title : 'همه اقامتگاه‌ها' }}',
                            properties: {{ Js::from($propertiesList->map(fn($p) => [
                                'id' => (string)$p->id,
                                'title' => $p->title,
                                'code' => $p->code ?? '',
                                'city' => $p->city ?? ''
                            ])) }},
                            get filteredProps() {
                                if (!this.search.trim()) return this.properties;
                                const q = this.search.toLowerCase();
                                return this.properties.filter(p => p.title.toLowerCase().includes(q) || p.code.toLowerCase().includes(q) || p.city.toLowerCase().includes(q));
                            },
                            selectProp(id, title) {
                                this.selectedId = id;
                                this.selectedTitle = title;
                                this.open = false;
                                $refs.propHiddenInput.value = id;
                            },
                            resetProp() {
                                this.selectProp('all', 'همه اقامتگاه‌ها');
                            }
                        }">
                        <label class="block text-[11px] font-black text-gray-600 dark:text-gray-300">اقامتگاه خاص:</label>
                        <input type="hidden" name="property_id" :value="selectedId" x-ref="propHiddenInput">

                        <div class="relative">
                            <button type="button"
                                    @click="open = !open"
                                    class="w-full flex items-center justify-between gap-2 rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all text-right shadow-xs">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    </div>
                                    <span class="truncate font-black" x-text="selectedTitle"></span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            {{-- پاپ‌آپ دراپ‌داون اقامتگاه --}}
                            <div x-cloak x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute right-0 left-0 mt-2 z-50 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-2 space-y-2">
                                
                                <div class="relative">
                                    <input type="text"
                                           x-model="search"
                                           placeholder="جستجوی عنوان، کد یا شهر ملک..."
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 pr-8 pl-3 py-1.5 text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 transition-all font-sans">
                                    <div class="absolute right-2.5 top-2 text-gray-400 pointer-events-none">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                </div>

                                <div class="max-h-52 overflow-y-auto space-y-1 scrollbar-thin">
                                    <div @click="resetProp()"
                                         class="flex items-center justify-between p-2 rounded-xl text-xs font-bold cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300"
                                         :class="selectedId === 'all' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-black' : ''">
                                        <span>همه اقامتگاه‌ها</span>
                                        <svg x-show="selectedId === 'all'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>

                                    <template x-for="p in filteredProps" :key="p.id">
                                        <div @click="selectProp(p.id, p.title)"
                                             class="flex items-center justify-between p-2 rounded-xl text-xs font-bold cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200"
                                             :class="selectedId === p.id ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-black' : ''">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="truncate" x-text="p.title"></span>
                                                <span x-show="p.city" class="text-[10px] text-gray-400" x-text="'(' + p.city + ')'"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-500 font-normal" x-text="p.code"></span>
                                                <svg x-show="selectedId === p.id" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="filteredProps.length === 0" class="py-4 text-center text-xs text-gray-400">
                                        اقامتگاهی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ۳. سلکتور وضعیت پرداخت مالی --}}
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-gray-600 dark:text-gray-300">وضعیت پرداخت مالی:</label>
                        <select name="payment_status"
                                class="w-full rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans shadow-xs">
                            <option value="all" {{ $selectedPaymentStatus === 'all' ? 'selected' : '' }}>همه وضعیت‌های پرداخت</option>
                            <option value="paid" {{ $selectedPaymentStatus === 'paid' ? 'selected' : '' }}>پرداخت شده (کامل)</option>
                            <option value="partially_paid" {{ $selectedPaymentStatus === 'partially_paid' ? 'selected' : '' }}>پرداخت بیعانه / جزئی</option>
                            <option value="unpaid" {{ $selectedPaymentStatus === 'unpaid' ? 'selected' : '' }}>در انتظار پرداخت</option>
                            <option value="refunded" {{ $selectedPaymentStatus === 'refunded' ? 'selected' : '' }}>استرداد شده</option>
                        </select>
                    </div>

                    {{-- ۴. فیلتر بازه تاریخ ورود مسافر --}}
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-gray-600 dark:text-gray-300">بازه ورود مسافر (از / تا):</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <input type="text"
                                       name="date_from"
                                       value="{{ $selectedDateFrom }}"
                                       placeholder="از تاریخ (مثلاً 1403/05/01)"
                                       class="w-full rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 px-3 py-2.5 text-[11px] font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans shadow-xs">
                            </div>
                            <div class="relative">
                                <input type="text"
                                       name="date_to"
                                       value="{{ $selectedDateTo }}"
                                       placeholder="تا تاریخ"
                                       class="w-full rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 px-3 py-2.5 text-[11px] font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans shadow-xs">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- دکمه‌های کنترل و اعمال فیلتر پیشرفته --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                    <a href="{{ route('user.properties.bookings.index') }}"
                       class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold transition">
                        ریست پیش‌فرض
                    </a>
                    <button type="submit"
                            class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs transition shadow-md shadow-indigo-600/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>اعمال فیلترها</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- تگ‌های فیلترهای فعال (Active Filter Pills) --}}
        @if($activeFiltersCount > 0)
            <div class="flex items-center gap-2 flex-wrap pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                <span class="text-[11px] font-black text-gray-400">فیلترهای فعال:</span>

                @if($selectedHostId !== 'all' && ($currentHost = $hostsList->firstWhere('id', $selectedHostId)))
                    <a href="{{ route('user.properties.bookings.index', request()->except('host_id')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/50 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>میزبان: {{ $currentHost->display_name }}</span>
                        <svg class="w-3.5 h-3.5 text-indigo-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif

                @if($selectedPropertyId !== 'all' && ($currentProp = $propertiesList->firstWhere('id', $selectedPropertyId)))
                    <a href="{{ route('user.properties.bookings.index', request()->except('property_id')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>ملک: {{ $currentProp->title }}</span>
                        <svg class="w-3.5 h-3.5 text-emerald-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif

                @if($selectedPaymentStatus !== 'all')
                    @php
                        $payLabels = [
                            'paid' => 'پرداخت کامل',
                            'partially_paid' => 'بیعانه / جزئی',
                            'unpaid' => 'در انتظار پرداخت',
                            'refunded' => 'استرداد شده',
                        ];
                    @endphp
                    <a href="{{ route('user.properties.bookings.index', request()->except('payment_status')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/50 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>پرداخت: {{ $payLabels[$selectedPaymentStatus] ?? $selectedPaymentStatus }}</span>
                        <svg class="w-3.5 h-3.5 text-blue-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif

                @if(!empty($selectedDateFrom))
                    <a href="{{ route('user.properties.bookings.index', request()->except('date_from')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>از: {{ CalendarUtils::convertNumbers($selectedDateFrom) }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif

                @if(!empty($selectedDateTo))
                    <a href="{{ route('user.properties.bookings.index', request()->except('date_to')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>تا: {{ CalendarUtils::convertNumbers($selectedDateTo) }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif

                @if(request('search'))
                    <a href="{{ route('user.properties.bookings.index', request()->except('search')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition group">
                        <span>جستجو: "{{ request('search') }}"</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        @endif

        {{-- تب‌های افقی وضعیت رزرو با شمارنده --}}
        <div class="flex items-center gap-2 overflow-x-auto w-full pt-2 border-t border-gray-100 dark:border-gray-700/60 scrollbar-none">
            <a href="{{ route('user.properties.bookings.index', request()->except('status')) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ !$status || $status === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                همه ({{ CalendarUtils::convertNumbers($counts['all'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'pending_host'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'pending_host' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                در انتظار تأیید ({{ CalendarUtils::convertNumbers($counts['pending_host'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'awaiting_payment'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'awaiting_payment' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                در انتظار پرداخت ({{ CalendarUtils::convertNumbers($counts['awaiting_payment'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'confirmed'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                قطعی ({{ CalendarUtils::convertNumbers($counts['confirmed'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'checked_in'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'checked_in' ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                مهمان مستقر ({{ CalendarUtils::convertNumbers($counts['checked_in'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'completed' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                تکمیل شده ({{ CalendarUtils::convertNumbers($counts['completed'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'cancelled' ? 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                لغو شده ({{ CalendarUtils::convertNumbers($counts['cancelled'] ?? 0) }})
            </a>
        </div>

    </div>

    {{-- جدول رزروها --}}
    @if($bookings->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ رزروی یافت نشد</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">هیچ رکوردی منطبق با جستجو یا فیلترهای انتخابی در پایگاه داده ثبت نشده است.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs font-bold">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/50 text-gray-400 border-b border-gray-100 dark:border-gray-700/80">
                        <tr>
                            <th class="py-4 px-5">کد رزرو</th>
                            <th class="py-4 px-5">اقامتگاه</th>
                            <th class="py-4 px-5">مهمان (کلاینت)</th>
                            <th class="py-4 px-5">میزبان</th>
                            <th class="py-4 px-5">بازه اقامت</th>
                            <th class="py-4 px-5">مبلغ کل</th>
                            <th class="py-4 px-5">وضعیت</th>
                            <th class="py-4 px-5 text-center">اقدامات هوشمند</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($bookings as $booking)
                            @php
                                $badge = $booking->status_badge;
                                $payBadge = $booking->payment_status_badge;
                                $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                                $canOperate = $canManageAll || ($host && $booking->host_id === $host->id);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                                <td class="py-4 px-5">
                                    <a href="{{ route('user.properties.bookings.show', $booking->id) }}" class="font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $booking->booking_code }}
                                    </a>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $propCover }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-black line-clamp-1 max-w-[180px]">
                                                {{ $booking->property?->title ?? 'اقامتگاه' }}
                                            </div>
                                            <div class="text-[10px] text-gray-400">کد ملک: {{ $booking->property?->code ?? '---' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-gray-900 dark:text-white font-black">{{ $booking->guest_full_name }}</div>
                                        <div class="text-gray-500 font-bold dir-ltr inline-block text-right text-[11px]">{{ $booking->guest_phone }}</div>
                                        @if($booking->client)
                                            <span class="inline-block text-[10px] text-emerald-600 font-bold">عضو پورتال کلاینت</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-gray-900 dark:text-white font-black">{{ $booking->host?->display_name ?? '---' }}</div>
                                        <div class="text-gray-400 text-[11px] dir-ltr inline-block text-right">{{ $booking->host?->phone ?? '---' }}</div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5 text-[11px]">
                                        <div class="text-gray-900 dark:text-white">
                                            {{ CalendarUtils::convertNumbers($booking->check_in) }} تا {{ CalendarUtils::convertNumbers($booking->check_out) }}
                                        </div>
                                        <div class="text-indigo-600 dark:text-indigo-400 font-black text-[10px]">
                                            {{ CalendarUtils::convertNumbers($booking->nights_count) }} شب &bull; {{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-black text-gray-900 dark:text-white">
                                            {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                            <span class="text-[10px] font-normal text-gray-500">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $payBadge['class'] }}">
                                            {{ $payBadge['label'] }}
                                        </span>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        {{-- مشاهده پرونده --}}
                                        <a href="{{ route('user.properties.bookings.show', $booking->id) }}" class="p-1.5 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="مشاهده جزئیات">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-12 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        @if($canOperate)
                                            {{-- وضعیت نیازمند تأیید میزبان --}}
                                            @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST)
                                                <form method="POST" action="{{ route('user.properties.bookings.approve', $booking->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('آیا درخواست رزرو {{ $booking->booking_code }} را تأیید می‌فرمایید؟')"
                                                            class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[11px] transition shadow-xs" title="تأیید درخواست رزرو">
                                                        تأیید
                                                    </button>
                                                </form>
                                                <button type="button"
                                                        @click="openReject('{{ route('user.properties.bookings.reject', $booking->id) }}', '{{ $booking->booking_code }}')"
                                                        class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/40 dark:hover:bg-red-900/50 dark:text-red-300 font-bold text-[11px] transition" title="رد درخواست رزرو">
                                                    رد
                                                </button>
                                            @endif

                                            {{-- ثبت ورود مهمان پس از قطعی شدن --}}
                                            @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED)
                                                <form method="POST" action="{{ route('user.properties.bookings.check-in', $booking->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('آیا ورود مهمان برای رزرو {{ $booking->booking_code }} انجام شده است؟')"
                                                            class="px-2.5 py-1 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-black text-[11px] transition shadow-xs" title="ثبت ورود مهمان">
                                                        ثبت ورود
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- تکمیل اقامت و تحویل پس از استقرار --}}
                                            @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN)
                                                <form method="POST" action="{{ route('user.properties.bookings.complete', $booking->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('آیا اقامت مهمان برای رزرو {{ $booking->booking_code }} پایان یافته و واحد تحویل گرفته شد؟')"
                                                            class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] transition shadow-xs" title="تکمیل اقامت و تحویل">
                                                        تکمیل اقامت
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- امکان لغو رزرو برای مراحل پس از بررسی اولیه --}}
                                            @if(!in_array($booking->status, [
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST,
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED,
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
                                                \Modules\Properties\Entities\PropertyRentalBooking::STATUS_REJECTED
                                            ]))
                                                <button type="button"
                                                        @click="openCancel('{{ route('user.properties.bookings.cancel', $booking->id) }}', '{{ $booking->booking_code }}')"
                                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition" title="لغو رزرو">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700/80">
                {{ $bookings->links() }}
            </div>
        </div>
    @endif

    {{-- مدال رد درخواست رزرو --}}
    <div x-cloak x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form :action="rejectActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400 flex items-center gap-2">
                            <span>رد درخواست رزرو:</span>
                            <span x-text="rejectBookingCode"></span>
                        </h3>
                        <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 font-bold leading-relaxed">
                        لطفاً علت رد این درخواست رزرو را جهت اطلاع مسافر ثبت فرمایید:
                    </p>

                    <div>
                        <textarea name="rejection_reason" x-model="rejectReason" required rows="3" placeholder="دلیل رد درخواست رزرو..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white resize-none font-sans"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showRejectModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            انصراف
                        </button>
                        <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md shadow-red-600/30">
                            تأیید و رد درخواست
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مدال لغو رزرو --}}
    <div x-cloak x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form :action="cancelActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400 flex items-center gap-2">
                            <span>لغو رزرو:</span>
                            <span x-text="cancelBookingCode"></span>
                        </h3>
                        <button type="button" @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 font-bold leading-relaxed">
                        با لغو این رزرو، تاریخ‌های مربوطه در تقویم اقامتگاه آزاد خواهند شد. لطفاً علت لغو را درج فرمایید:
                    </p>

                    <div>
                        <textarea name="cancellation_reason" x-model="cancelReason" required rows="3" placeholder="علت لغو رزرو..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white resize-none font-sans"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showCancelModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            انصراف
                        </button>
                        <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md shadow-red-600/30">
                            تأیید لغو رزرو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
