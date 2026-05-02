{{-- resources/views/admin/roles/create.blade.php --}}
@extends('layouts.user')

@php
    $title = 'ایجاد نقش جدید';

    // 🚨 هشدار معماری: این خط باید در کنترلر انجام شود و $widgetGroups به ویو پاس داده شود.
    $widgetGroups = collect($widgets ?? [])->groupBy('group');
    $oldWidgets = old('widgets', []);

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </span>
                    ایجاد نقش جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تعریف نقش جدید، تعیین ویجت‌های داشبورد و سطح دسترسی‌ها</p>
            </div>

            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به لیست
            </a>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6 pb-20" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            {{-- بخش اول: اطلاعات اصلی نقش --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        مشخصات اصلی نقش
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">نام فارسی نقش <span class="text-red-500">*</span></label>
                        <input type="text" name="display_name" value="{{ old('display_name') }}" class="{{ $inputClass }}" required placeholder="مثلاً: مدیر فروش">
                        @error('display_name')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">آیدی لاتین (Slug)</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="{{ $inputClass }} dir-ltr text-left font-mono" placeholder="مثلاً: sales-manager">
                        <p class="text-[10px] text-gray-400 mt-1.5">اختیاری؛ در صورت خالی بودن از روی نام فارسی تولید می‌شود.</p>
                        @error('name')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- بخش دوم: ویجت‌های داشبورد --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        ویجت‌های داشبورد
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-4">ویجت‌هایی که این نقش در پیشخوان خود مشاهده می‌کند.</p>
                </div>

                <div class="p-6 space-y-6">
                    @forelse($widgetGroups as $groupLabel => $groupWidgets)
                        <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700/50">
                            <div class="mb-4 flex items-center gap-2">
                                <div class="w-1 h-4 bg-amber-400 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $groupLabel }}</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($groupWidgets as $widget)
                                    @php
                                        $checked = is_array($oldWidgets) && array_key_exists($widget['key'], $oldWidgets);
                                    @endphp
                                    <label class="flex items-start gap-3 p-3 border rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors group">
                                        <input type="checkbox" name="widgets[{{ $widget['key'] }}]" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" @checked($checked)>
                                        <div class="flex-1 space-y-1">
                                            <div class="font-bold text-sm text-gray-800 dark:text-gray-200 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $widget['label'] ?? $widget['key'] }}
                                            </div>
                                            @if(!empty($widget['description'] ?? null))
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                                    {{ $widget['description'] }}
                                                </div>
                                            @endif
                                            @if(!empty($widget['permission']))
                                                <div class="text-[10px] bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 px-2 py-0.5 rounded inline-block mt-1">
                                                    نیاز به مجوز: <span class="font-mono dir-ltr">{{ $widget['permission'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 dark:text-gray-500 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <p class="text-sm">هیچ ویجتی در سیستم ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- بخش سوم: مجوزها (Permissions) --}}
            <div class="{{ $cardClass }}"
                 x-data="permissionForm({
                selected: @js(old('permissions', [])),
                groups: @js($permissionGroups)
             })">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            سطوح دسترسی و مجوزها
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-4">تعیین دسترسی‌های این نقش در بخش‌های مختلف سیستم</p>
                    </div>

                    {{-- سرچ مجوزها --}}
                    <div class="relative w-full sm:w-72">
                        <input type="text" x-model="query" placeholder="جستجوی مجوز..." class="w-full h-10 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 pr-10 pl-4 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:text-white placeholder-gray-400">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <template x-for="(group, key) in groups" :key="key">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm" x-show="visibleCount(key) > 0">

                            {{-- هدر آکاردئون --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                                <button type="button" class="flex items-center gap-2 text-gray-900 dark:text-white font-bold text-sm outline-none group" x-on:click="toggle(key)">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-transform" :class="open[key] ? 'rotate-90 text-indigo-500' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    <span x-text="group.title"></span>
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 text-[10px] font-mono">
                                    <span x-text="visibleCount(key)"></span> / <span x-text="group.items.length"></span>
                                </span>
                                </button>

                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" class="px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-medium dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition-colors" x-on:click="selectAll(key)">
                                        انتخاب همه
                                    </button>
                                    <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-xs font-medium dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors" x-on:click="unselectAll(key)">
                                        حذف همه
                                    </button>
                                </div>
                            </div>

                            {{-- محتوای آکاردئون --}}
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-show="open[key]" x-collapse>
                                <template x-for="perm in filtered(key)" :key="perm.name">
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 bg-white hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-700 cursor-pointer group transition-colors shadow-sm hover:shadow">
                                        <input type="checkbox" name="permissions[]" :value="perm.name" :checked="selected.includes(perm.name)" @change="toggleOne(perm.name, $event.target.checked)" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                        <div class="flex flex-col flex-1 min-w-0">
                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors truncate" x-text="perm.label"></span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono dir-ltr text-left mt-1 truncate" x-text="perm.name"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- در صورت خالی بودن نتایج جستجو --}}
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700" x-show="Object.keys(groups).reduce((acc, key) => acc + visibleCount(key), 0) === 0" style="display: none;">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">هیچ مجوزی مطابق با عبارت «<span x-text="query" class="font-bold"></span>» یافت نشد.</p>
                    </div>
                </div>
            </div>

            {{-- دکمه‌های عملیات --}}
            <div class="flex items-center justify-between sticky bottom-6 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ایجاد نقش جدید</span>
                    <span x-show="isSubmitting">در حال ذخیره...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>

        </form>
    </div>

    {{-- اسکریپت Alpine برای فرم مجوزها --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('permissionForm', ({selected = [], groups = {}}) => ({
                query: '',
                selected,
                groups,
                open: Object.fromEntries(Object.keys(groups).map(k => [k, true])),

                filtered(key) {
                    const q = this.query.trim().toLowerCase();
                    const items = this.groups[key].items;
                    if (!q) return items;
                    return items.filter(i => (i.label + ' ' + i.name).toLowerCase().includes(q));
                },

                visibleCount(key) {
                    return this.filtered(key).length;
                },

                toggle(key) {
                    this.open[key] = !this.open[key];
                },

                toggleOne(name, checked) {
                    if (checked && !this.selected.includes(name)) this.selected.push(name);
                    if (!checked) this.selected = this.selected.filter(n => n !== name);
                },

                selectAll(key) {
                    const names = this.filtered(key).map(i => i.name);
                    this.selected = Array.from(new Set([...this.selected, ...names]));
                    // همگام‌سازی بصری DOM
                    this.$nextTick(() => names.forEach(n => {
                        const el = this.$root.querySelector(`input[type=checkbox][value="${CSS.escape(n)}"]`);
                        if (el) el.checked = true;
                    }));
                },

                unselectAll(key) {
                    const names = this.filtered(key).map(i => i.name);
                    this.selected = this.selected.filter(n => !names.includes(n));
                    this.$nextTick(() => names.forEach(n => {
                        const el = this.$root.querySelector(`input[type=checkbox][value="${CSS.escape(n)}"]`);
                        if (el) el.checked = false;
                    }));
                }
            }));
        });
    </script>
@endsection
