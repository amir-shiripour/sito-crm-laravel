@php use Morilog\Jalali\Jalalian; @endphp
@extends('layouts.user')

@section('content')
    <div class="space-y-6" dir="rtl">

        {{-- ══ Header & Stats ══ --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">کل طرح‌ها</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totalCount) }}</span>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400">تأیید شده</span>
                    <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($statusCounts['confirmed'] ?? 0) }}</span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-100 dark:border-amber-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-amber-600 dark:text-amber-400">پیش‌نویس</span>
                    <span class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-1">{{ number_format($statusCounts['draft'] ?? 0) }}</span>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400">جمع کل</span>
                    <span class="text-xl font-bold text-indigo-700 dark:text-indigo-300 mt-1">{{ number_format($totalAmount) }}</span>
                </div>
            </div>

            <div class="flex flex-col justify-center items-end gap-3">
                @can('booking.cure.create')
                    <a href="{{ route('user.booking.cure.index') }}"
                       class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        طرح درمان جدید
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ══ Filters ══ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('user.booking.cure.list') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">جستجو</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="نام بیمار..."
                           class="p-2 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</label>
                    <select name="status" class="p-2 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>تأیید شده</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مرتب‌سازی</label>
                    <select name="sort" class="w-full p-2 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="newest"     {{ request('sort', 'newest') === 'newest'     ? 'selected' : '' }}>جدیدترین</option>
                        <option value="oldest"     {{ request('sort') === 'oldest'               ? 'selected' : '' }}>قدیمی‌ترین</option>
                        <option value="total_desc" {{ request('sort') === 'total_desc'           ? 'selected' : '' }}>بیشترین مبلغ</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 font-medium text-sm transition py-2">
                        اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>

        {{-- ══ Plans List ══ --}}
        <div class="space-y-4">
            @forelse($plans as $plan)
                @php
                    $clientName    = $plan->client?->full_name ?? $plan->patient_name ?? 'بدون نام';
                    $createdBy     = $plan->creator?->name ?? 'نامشخص';
                    $itemCount     = is_array($plan->items) ? count($plan->items) : 0;
                    $teethCount    = collect($plan->items ?? [])->sum(fn($i) => count($i['teeth'] ?? []));
                    $currency      = $plan->currency === 'IRR' ? 'ریال' : 'تومان';
                    $createdJalali = Jalalian::fromDateTime($plan->created_at)->format('Y/m/d');

                    $statusMap = [
                        'draft'     => ['label' => 'پیش‌نویس',  'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800'],
                        'confirmed' => ['label' => 'تأیید شده', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800'],
                    ];
                    $statusMeta = $statusMap[$plan->status] ?? ['label' => $plan->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
                    $initial    = mb_substr($clientName, 0, 1);

                    $canEdit = auth()->user()->can('booking.cure.edit') || auth()->user()->can('booking.cure.manage');
                    if ($plan->status === 'confirmed') {
                        $canEdit = $canEdit
                            && ($cureAllowEditConfirmed ?? false)
                            && (auth()->user()->can('booking.cure.edit.confirmed') || auth()->user()->can('booking.cure.manage'));
                    }
                @endphp

                <div x-data="{ expanded: false }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:shadow-md">

                    {{-- Card Header --}}
                    <div @click="expanded = !expanded"
                         class="cursor-pointer px-5 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-black shrink-0
                                        bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900/40 dark:to-violet-900/40
                                        text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50">
                                {{ $initial }}
                            </div>
                            {{-- Client Info --}}
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $clientName }}</h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>شناسه: {{ $plan->id }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    <span>{{ $createdJalali }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    {{-- Creator name --}}
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $createdBy }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="hidden sm:inline-flex px-3 py-1 rounded-full text-[11px] font-bold border {{ $statusMeta['class'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                            <div class="text-left hidden md:block">
                                <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ number_format($plan->total) }}</div>
                                <div class="text-[10px] text-gray-400">{{ $currency }}</div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                 :class="expanded ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Expandable Content --}}
                    <div x-show="expanded"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2">

                        <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 p-5 space-y-5">

                            {{-- Mobile Status / Total --}}
                            <div class="flex md:hidden items-center justify-between">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold border {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                                <div class="text-left">
                                    <span class="text-sm font-black text-emerald-600">{{ number_format($plan->total) }}</span>
                                    <span class="text-[10px] text-gray-400 mr-1">{{ $currency }}</span>
                                </div>
                            </div>

                            {{-- Financial Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">جمع کل</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($plan->subtotal) }}
                                        <span class="text-[10px] text-gray-400 font-normal">{{ $currency }}</span>
                                    </span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">تخفیف</span>
                                    <span class="text-sm font-bold text-rose-600 dark:text-rose-400">
                                        @if($plan->discount_value > 0)
                                            −{{ number_format($plan->discount_value) }}
                                        @else
                                            ۰
                                        @endif
                                    </span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">قابل پرداخت</span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($plan->total) }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">تعداد آیتم‌ها</span>
                                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $itemCount }} آیتم / {{ $teethCount }} دندان</span>
                                </div>
                            </div>

                            {{-- Items Details --}}
                            @if(is_array($plan->items) && count($plan->items) > 0)
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                        <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300">جزئیات آیتم‌های طرح درمان</h4>
                                        <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-medium px-2 py-0.5 rounded-md">
                                            {{ $itemCount }} ردیف خدماتی
                                        </span>
                                    </div>
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @foreach($plan->items as $item)
                                            @php
                                                $serviceName    = $item['service_name'] ?? $item['name'] ?? 'سرویس نامشخص';
                                                $price          = $item['price'] ?? 0;
                                                $qty            = $item['quantity'] ?? 1;
                                                $subtotal       = $item['subtotal'] ?? ($price * $qty);
                                                $rawTeeth       = $item['teeth'] ?? [];
                                                $brands         = $item['brands'] ?? [];
                                                $categoryValue  = $item['category_name'] ?? $item['category'] ?? null;
                                                $guaranteeValue = $item['guarantee'] ?? $item['warranty'] ?? null;
                                            @endphp
                                            <div class="px-4 py-4 flex flex-col gap-3 hover:bg-gray-50/50 dark:hover:bg-gray-900/40 transition">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-50 dark:border-gray-700/30 pb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $serviceName }}</p>
                                                    </div>
                                                    <div class="text-left">
                                                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                                            {{ number_format($subtotal) }}
                                                            <span class="text-[10px] text-gray-400 font-normal mr-0.5">{{ $currency }}</span>
                                                        </span>
                                                        @if($qty > 1)
                                                            <span class="text-[10px] text-gray-400 block sm:inline sm:mr-2">
                                                                ({{ $qty }} واحد × {{ number_format($price) }})
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(!empty($brands) || $categoryValue || $guaranteeValue)
                                                    <div class="flex flex-wrap gap-2 items-center pr-4">
                                                        @foreach($brands as $brand)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 shadow-sm">
                                                                {{ !empty($brand['sectionTitle']) ? $brand['sectionTitle'] : 'گزینه' }}: <span class="font-bold">{{ $brand['name'] ?? 'نامشخص' }}</span>
                                                            </span>
                                                        @endforeach
                                                        @if($categoryValue)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-100 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800 shadow-sm">
                                                                گروه: {{ $categoryValue }}
                                                            </span>
                                                        @endif
                                                        @if($guaranteeValue)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800 shadow-sm">
                                                                ضمانت: {{ $guaranteeValue }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="mt-1 pr-4">
                                                    @if(!empty($rawTeeth) && is_array($rawTeeth))
                                                        <div class="flex flex-wrap gap-1 items-center">
                                                            <span class="text-[11px] text-gray-400 dark:text-gray-500 ml-1.5">موقعیت دندان‌ها:</span>
                                                            @foreach($rawTeeth as $tooth)
                                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px] font-bold border border-gray-200 dark:border-gray-600 shadow-sm">
                                                                    {{ is_array($tooth) ? ($tooth['number'] ?? '-') : $tooth }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-[11px] text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded">
                                                            بدون وابستگی به موقعیت دندان (خدمات عمومی یا کلی)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Notes & Actions --}}
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                                @if($plan->notes)
                                    <div class="flex-1 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $plan->notes }}</span>
                                    </div>
                                @else
                                    <div></div>
                                @endif

                                <div class="flex items-center gap-2 shrink-0">

                                    {{-- View --}}
                                    @canany(['booking.cure.view', 'booking.cure.view.all', 'booking.cure.view.own', 'booking.cure.manage'])
                                        <a href="{{ route('user.booking.cure.show', $plan) }}"
                                           class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-gray-100 text-gray-700
                                                  hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:hover:bg-gray-700 transition font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            مشاهده
                                        </a>
                                    @endcanany

                                    {{-- Edit --}}
                                    @if($canEdit)
                                        <a href="{{ route('user.booking.cure.edit', $plan) }}"
                                           class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-indigo-600 text-white
                                                  hover:bg-indigo-700 transition font-medium shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            ویرایش
                                        </a>
                                    @endif

                                    {{-- Delete --}}
                                    @canany(['booking.cure.delete', 'booking.cure.manage'])
                                        <form method="POST"
                                              action="{{ route('user.booking.cure.destroy', $plan) }}"
                                              onsubmit="return confirm('آیا از حذف این طرح درمان اطمینان دارید؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-rose-50 text-rose-600
                                                           hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/30 transition font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                حذف
                                            </button>
                                        </form>
                                    @endcanany

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm py-16 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-600 dark:text-gray-400 mb-1">هیچ طرح درمانی یافت نشد</h3>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-6">شما هنوز طرح درمانی ثبت نکرده‌اید.</p>
                    @can('booking.cure.create')
                        <a href="{{ route('user.booking.cure.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            ایجاد اولین طرح
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        <div class="flex justify-end">
            {{ $plans->links() }}
        </div>
    </div>
@endsection
