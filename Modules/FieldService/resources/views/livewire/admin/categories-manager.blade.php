<div class="space-y-6 font-sans" x-data>

    {{-- Header Banner & KPIs --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-2xl shrink-0">
                        🛠️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 dark:text-white">مدیریت خدمات و دسته‌بندی‌های فنی</h1>
                            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold border border-indigo-100 dark:border-indigo-500/20">
                                ماژول مدیریت اجی مجی
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                            تعریف حوزه‌های فنی، تعرفه پایه کارشناسی، مدت زمان گارانتی و مدیریت رسته‌های فعال در کل پلتفرم
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-200 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن خدمت جدید</span>
                </button>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">کل خدمات تعریف‌شده</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    📋
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">خدمات فعال ناوگان</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($activeCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✅
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">خدمات غیرفعال</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalCount - $activeCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    ⏸️
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">انواع تجهیزات پایه</div>
                    <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ count($applianceTypes) }} <span class="text-xs font-medium text-slate-500">رسته</span></div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                    🏷️
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('message'))
    <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-3 animate-in fade-in">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-3 animate-in fade-in">
        <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        {{-- Search Input --}}
        <div class="relative w-full md:flex-1">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="جستجو در عنوان خدمت، نامک یا توضیحات..."
                   class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Status Filter --}}
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium shrink-0">وضعیت:</span>
                <select wire:model.live="statusFilter"
                        class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer w-full sm:w-44">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="active">فقط خدمات فعال</option>
                    <option value="inactive">فقط خدمات غیرفعال</option>
                </select>
            </div>

            {{-- Appliance Type Filter --}}
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium shrink-0">رسته دستگاه:</span>
                <select wire:model.live="applianceFilter"
                        class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer w-full sm:w-52">
                    <option value="all">همه دسته‌بندی‌ها</option>
                    @foreach($applianceTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->icon() }} {{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/70 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">ترتیب</th>
                        <th class="py-3.5 px-4">عنوان خدمت و آیکون</th>
                        <th class="py-3.5 px-4">نوع تجهیز پایه</th>
                        <th class="py-3.5 px-4">دستمزد پایه (تومان)</th>
                        <th class="py-3.5 px-4 text-center">گارانتی</th>
                        <th class="py-3.5 px-4 text-center">متخصصین ناوگان</th>
                        <th class="py-3.5 px-4 text-center">سفارش‌ها</th>
                        <th class="py-3.5 px-4 text-center">وضعیت</th>
                        <th class="py-3.5 px-4 text-left">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-800 dark:text-slate-200">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        {{-- Sort Order --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-700/60 font-black text-slate-700 dark:text-slate-300 font-sans">
                                {{ $category->sort_order }}
                            </span>
                        </td>

                        {{-- Category Name & Icon --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-100 dark:border-indigo-500/30 flex items-center justify-center text-lg shrink-0 shadow-sm">
                                    {{ $category->icon ?: '🛠️' }}
                                </span>
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $category->name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 truncate max-w-xs" title="{{ $category->description }}">
                                        <span class="font-sans font-medium text-slate-500 dark:text-slate-400">{{ $category->slug }}</span>
                                        @if($category->description)
                                            <span class="text-slate-400 mx-1">•</span>
                                            <span>{{ Str::limit($category->description, 45) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Appliance Type --}}
                        <td class="py-3.5 px-4">
                            @php
                                $appType = $category->appliance_type instanceof \Modules\FieldService\App\Enums\ApplianceType 
                                    ? $category->appliance_type 
                                    : \Modules\FieldService\App\Enums\ApplianceType::tryFrom((string) $category->appliance_type);
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-medium border border-slate-200/60 dark:border-slate-600/40">
                                <span>{{ $appType ? $appType->icon() : '🛠️' }}</span>
                                <span>{{ $appType ? $appType->label() : ($category->appliance_type ?? '-') }}</span>
                            </span>
                        </td>

                        {{-- Base Labor Fee --}}
                        <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white font-sans text-sm">
                            {{ number_format($category->base_labor_fee / 10) }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400 mr-1">تومان</span>
                        </td>

                        {{-- Warranty Days --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($category->warranty_days > 0)
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-xl text-xs font-bold font-sans bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20">
                                {{ $category->warranty_days }} روز
                            </span>
                            @else
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg text-[11px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/40">
                                بدون گارانتی
                            </span>
                            @endif
                        </td>

                        {{-- Technicians Count --}}
                        <td class="py-3.5 px-4 text-center font-bold">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-500/20 font-sans">
                                <span>👤</span>
                                <span>{{ $category->technicians_count }}</span>
                            </span>
                        </td>

                        {{-- Orders Count --}}
                        <td class="py-3.5 px-4 text-center font-bold">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-sans">
                                <span>📋</span>
                                <span>{{ $category->orders_count }}</span>
                            </span>
                        </td>

                        {{-- Status Toggle --}}
                        <td class="py-3.5 px-4 text-center">
                            <button type="button" wire:click="toggleStatus({{ $category->id }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold transition active:scale-95 border {{ $category->is_active ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                                <span class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-emerald-500 shadow-sm shadow-emerald-500/50' : 'bg-slate-400 dark:bg-slate-500' }}"></span>
                                <span>{{ $category->is_active ? 'فعال' : 'غیرفعال' }}</span>
                            </button>
                        </td>

                        {{-- Actions --}}
                        <td class="py-3.5 px-4 text-left">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" wire:click="openEditModal({{ $category->id }})"
                                        class="p-2 rounded-xl bg-slate-100 hover:bg-indigo-50 dark:bg-slate-700/70 dark:hover:bg-indigo-500/20 text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-300 transition"
                                        title="ویرایش خدمت">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <button type="button" wire:click="confirmDelete({{ $category->id }})"
                                        class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-700/70 dark:hover:bg-rose-500/20 text-slate-600 hover:text-rose-600 dark:text-slate-300 dark:hover:text-rose-400 transition"
                                        title="حذف خدمت">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 px-4 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-400 flex items-center justify-center text-2xl mb-3">
                                    🔍
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">خدمتی با این مشخصات یافت نشد</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                    می‌توانید فیلتر جستجو را پاک کنید یا خدمت فنی جدیدی اضافه فرمایید.
                                </p>
                                <button type="button" wire:click="openCreateModal"
                                        class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                    + تعریف خدمت جدید
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="p-4 border-t border-slate-200/80 dark:border-slate-700/60">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- CREATE / EDIT CATEGORY MODAL                                              --}}
    {{-- ========================================================================= --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="$wire.closeModal()"
             class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-2xl overflow-hidden my-auto font-sans">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xl flex items-center justify-center">
                        {{ $icon ?: '🛠️' }}
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">
                            {{ $editingCategoryId ? 'ویرایش مشخصات خدمت فنی' : 'تعریف خدمت فنی جدید' }}
                        </h3>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">اطلاعات پایه، دستمزد کارشناسی و مدت گارانتی خدمت</p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white transition flex items-center justify-center">
                    ✕
                </button>
            </div>

            {{-- Modal Form Body --}}
            <form wire:submit.prevent="saveCategory" class="p-6 space-y-4">
                
                {{-- Row 1: Name & Icon --}}
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            عنوان خدمت <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="name" placeholder="مثال: کولر گازی و اسپلیت"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            آیکون / اموجی
                        </label>
                        <input type="text" wire:model="icon" placeholder="❄️" maxlength="10"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-center text-base text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Row 2: Slug & Appliance Type --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            شناسه انگلیسی (Slug) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="slug" placeholder="air-conditioner-split" dir="ltr"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left">
                        @error('slug') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            رسته تجهیز پایه <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="applianceType"
                                class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                            @foreach($applianceTypes as $t)
                                <option value="{{ $t->value }}">{{ $t->icon() }} {{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 3: Base Fee & Warranty Days --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            دستمزد پایه / هزینه کارشناسی (تومان)
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="baseLaborFeeToman" placeholder="مثال: 450,000" dir="ltr"
                                   class="w-full h-11 pl-14 pr-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left font-sans">
                            <span class="absolute left-3.5 top-3 text-[11px] font-bold text-slate-400 pointer-events-none">تومان</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            گارانتی (روز) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" wire:model="warrantyDays" min="0" max="3650"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center font-sans">
                        @error('warrantyDays') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 4: Sort Order & Is Active --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-center">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            اولویت مرتب‌سازی در لیست‌ها
                        </label>
                        <input type="number" wire:model="sortOrder" min="0"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center font-sans">
                    </div>

                    <div class="pt-5">
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                            <input type="checkbox" wire:model="isActive" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">این خدمت فعال باشد و در ناوگان نمایش یابد</span>
                        </label>
                    </div>
                </div>

                {{-- Row 5: Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        توضیحات و شرح خدمات تخصصی
                    </label>
                    <textarea wire:model="description" rows="3" placeholder="دامنه خدمات، موارد شامل و نحوه انجام سرویس..."
                              class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeModal"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                        انصراف
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 disabled:opacity-75 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition flex items-center gap-2">
                        <div wire:loading.remove wire:target="saveCategory">
                            <span>{{ $editingCategoryId ? 'ذخیره تغییرات' : 'افزودن خدمت' }}</span>
                        </div>
                        <div wire:loading.flex wire:target="saveCategory" class="items-center gap-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>در حال ذخیره‌سازی...</span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- DELETE CONFIRMATION MODAL                                                 --}}
    {{-- ========================================================================= --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-2xl text-center space-y-4 font-sans">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center text-2xl">
                ⚠️
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">تایید حذف خدمت</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                    آیا از حذف کامل خدمت <span class="font-bold text-slate-900 dark:text-white">«{{ $deletingCategoryName }}»</span> اطمینان دارید؟
                </p>
                @if($deletingCategoryTechniciansCount > 0)
                <div class="mt-3 p-3 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-700 dark:text-amber-300 text-xs text-right leading-relaxed">
                    ⚠️ توجه: {{ $deletingCategoryTechniciansCount }} متخصص این خدمت را در تخصص‌های خود دارند. با حذف خدمت، این تخصص از پرونده آنان نیز برداشته خواهد شد.
                </div>
                @endif
                @if($deletingCategoryOrdersCount > 0)
                <div class="mt-2 p-3 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-700 dark:text-rose-300 text-xs text-right leading-relaxed">
                    ⛔ خطا: تعداد {{ $deletingCategoryOrdersCount }} سفارش قبلاً با این خدمت ثبت شده‌اند. جهت حفظ سوابق، حذف ممکن نیست و باید این خدمت را غیرفعال فرمایید.
                </div>
                @endif
            </div>

            <div class="pt-3 flex items-center justify-center gap-3">
                <button type="button" wire:click="cancelDelete"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                    انصراف
                </button>
                @if($deletingCategoryOrdersCount === 0)
                <button type="button" wire:click="deleteCategory"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-lg shadow-rose-600/30 transition">
                    بله، حذف شود
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
