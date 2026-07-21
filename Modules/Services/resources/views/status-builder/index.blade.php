@extends('layouts.user')
@section('title', 'مدیریت وضعیت‌ها')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
         x-data="statusBuilder()" x-init="init()">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                مدیریت وضعیت‌ها
            </h1>
            <p class="text-sm text-gray-400 hidden sm:block">وضعیت‌های هر بخش را مستقل تنظیم کنید</p>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Tab bar --}}
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800/80 p-1 rounded-2xl w-fit shadow-inner overflow-x-auto">
            @foreach([
                'project' => ['label' => 'پروژه',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                'order' => ['label' => 'سفارش',   'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                'service' => ['label' => 'سرویس',   'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                'invoice' => ['label' => 'فاکتور',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'payment' => ['label' => 'پرداخت',   'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ] as $type => $cfg)
                <button @click="activeTab = '{{ $type }}'"
                        :class="activeTab === '{{ $type }}'
                        ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                    </svg>
                    {{ $cfg['label'] }}
                    <span class="text-xs px-1.5 py-0.5 rounded-md font-mono"
                          :class="activeTab === '{{ $type }}'
                          ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400'
                          : 'bg-gray-200 dark:bg-gray-700 text-gray-500'">
                        {{ count(${ $type}) }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Tab panels --}}
        @foreach([
            'project' => ['statuses' => $project, 'color' => 'purple'],
            'order' => ['statuses' => $order, 'color' => 'orange'],
            'service' => ['statuses' => $service, 'color' => 'blue'],
            'invoice' => ['statuses' => $invoice, 'color' => 'indigo'],
            'payment' => ['statuses' => $payment, 'color' => 'emerald'],
        ] as $type => $cfg)
            @php $statuses = $cfg['statuses']; @endphp

            <div x-show="activeTab === '{{ $type }}'" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-4">

                {{-- Status cards --}}
                @if($statuses->isEmpty())
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-16 text-center">
                        <div
                            class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <p class="text-gray-900 dark:text-white font-bold mb-1">هنوز وضعیتی تعریف نشده</p>
                        <p class="text-sm text-gray-400">اولین وضعیت را از فرم زیر اضافه کنید</p>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        {{-- Table header --}}
                        <div
                            class="px-5 py-3 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 grid grid-cols-12 gap-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <div class="col-span-4">نام وضعیت</div>
                            <div class="col-span-2 text-center">رنگ</div>
                            <div class="col-span-5 text-center">ویژگی‌ها</div>
                            <div class="col-span-1"></div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($statuses as $status)
                                @php
                                    // سوپر ادمین تحت هر شرایطی مجاز به ویرایش و حذف وضعیت ها است
                                    $isSuperAdmin = auth()->user()->hasAnyRole(['super-admin', 'superadmin']);
                                    $canEdit = $isSuperAdmin || (!$status->is_final && !$status->is_readonly);
                                    $canDelete = $isSuperAdmin || (!$status->is_final && !$status->is_readonly && !$status->is_default);
                                @endphp
                                <div class="group px-5 py-3 hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors"
                                     x-data="{ editing: false, name: '{{ addslashes($status->name) }}', color: '{{ $status->color }}' }">

                                    {{-- View mode --}}
                                    <div x-show="!editing" class="grid grid-cols-12 gap-3 items-center">
                                        {{-- Name + badge preview --}}
                                        <div class="col-span-4 flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shadow-sm shrink-0"
                                                  style="background: {{ $status->color }}22; border: 1.5px solid {{ $status->color }}44">
                                                <span class="w-3 h-3 rounded-full" style="background: {{ $status->color }}"></span>
                                            </span>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-1.5">
                                                    {{ $status->name }}
                                                </div>
                                                <div class="text-xs font-mono text-gray-400 mt-0.5">{{ $status->color }}</div>
                                            </div>
                                        </div>

                                        {{-- Color swatch --}}
                                        <div class="col-span-2 flex justify-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                                                  style="background: {{ $status->color }}15; color: {{ $status->color }}; border-color: {{ $status->color }}30">
                                                {{ $status->name }}
                                            </span>
                                        </div>

                                        {{-- Flags --}}
                                        <div class="col-span-5 flex items-center justify-center gap-2 flex-wrap">
                                            @include('services::status-builder.partials.status-badges', ['status' => $status])
                                        </div>

                                        {{-- Actions --}}
                                        <div class="col-span-1 flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            @if($canEdit)
                                                <button @click="editing = true" type="button"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors" title="ویرایش">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($canDelete)
                                                <form method="POST" action="{{ route('services.status-builder.destroy', $status) }}" onsubmit="return confirm('وضعیت «{{ $status->name }}» حذف شود؟')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="حذف">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Edit mode --}}
                                    @if($canEdit)
                                        <div x-show="editing" x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                                            <form method="POST" action="{{ route('services.status-builder.update', $status) }}" class="space-y-4">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="type" value="{{ $type }}">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                    {{-- Name --}}
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">نام وضعیت</label>
                                                        <input type="text" name="name" x-model="name" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                    </div>
                                                    {{-- Color --}}
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">رنگ</label>
                                                        <div class="flex items-center gap-2">
                                                            <input type="color" name="color" x-model="color" class="h-9 w-14 rounded-lg border-gray-200 dark:border-gray-700 cursor-pointer shrink-0">
                                                            <span class="flex-1 px-3 py-2 rounded-xl text-xs font-bold border transition-all"
                                                                  :style="`background: ${color}15; color: ${color}; border-color: ${color}30`" x-text="name || 'پیش‌نمایش'"></span>
                                                        </div>
                                                    </div>
                                                    {{-- Basic Flags --}}
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های عمومی</label>
                                                        <div class="flex flex-wrap gap-3">
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="hidden" name="is_default" value="0">
                                                                <input type="checkbox" name="is_default" value="1" @if($status->is_default) checked @endif class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                پیش‌فرض
                                                            </label>
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="hidden" name="is_final" value="0">
                                                                <input type="checkbox" name="is_final" value="1" @if($status->is_final) checked @endif class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                نهایی
                                                            </label>
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="hidden" name="is_readonly" value="0">
                                                                <input type="checkbox" name="is_readonly" value="1" @if($status->is_readonly) checked @endif class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                فقط‌خواندنی
                                                            </label>
                                                        </div>
                                                    </div>

                                                    {{-- Type-specific Attributes --}}
                                                    <div class="col-span-1 sm:col-span-3">
                                                        @include('services::status-builder.partials.attributes-form', ['type' => $type, 'status' => $status])
                                                    </div>

                                                    {{-- Role / User Access Level Selector (EDIT) --}}
                                                    @php
                                                        $rType = 'all';
                                                        if (!empty($status->allowed_roles)) $rType = 'roles';
                                                        elseif (!empty($status->allowed_users)) $rType = 'users';
                                                    @endphp
                                                    <div class="col-span-1 sm:col-span-3 border-t border-gray-100 dark:border-gray-700/50 pt-4 mt-2" x-data="{ restrictionType: '{{ $rType }}', search: '' }">
                                                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">سطح دسترسی (چه کسانی می‌توانند این وضعیت را اعمال کنند؟)</label>
                                                        <div class="flex gap-4 mb-4">
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="radio" x-model="restrictionType" value="all" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                                همه مجازند
                                                            </label>
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="radio" x-model="restrictionType" value="roles" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                                محدود به نقش‌ها
                                                            </label>
                                                            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="radio" x-model="restrictionType" value="users" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                                محدود به کاربران
                                                            </label>
                                                        </div>

                                                        {{-- Roles Search & List --}}
                                                        <div x-show="restrictionType === 'roles'" x-cloak class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                                                            <input type="text" x-model="search" placeholder="جستجوی نقش..." class="w-full sm:w-1/2 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                            <div class="flex flex-wrap gap-3 max-h-40 overflow-y-auto">
                                                                @foreach($roles->reject(fn($r) => in_array(mb_strtolower($r->name), ['super-admin', 'superadmin'])) as $role)
                                                                    <label x-show="search === '' || '{{ mb_strtolower($role->name) }}'.includes(search.toLowerCase())" class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                        <input type="checkbox" name="allowed_roles[]" value="{{ $role->name }}" :disabled="restrictionType !== 'roles'"
                                                                               @if(in_array($role->name, $status->allowed_roles ?? [])) checked @endif
                                                                               class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                        {{ $role->name }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        {{-- Users Search & List --}}
                                                        <div x-show="restrictionType === 'users'" x-cloak class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                                                            <input type="text" x-model="search" placeholder="جستجوی کاربر..." class="w-full sm:w-1/2 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                            <div class="flex flex-wrap gap-3 max-h-40 overflow-y-auto">
                                                                @foreach($users as $user)
                                                                    <label x-show="search === '' || '{{ mb_strtolower($user->name) }}'.includes(search.toLowerCase())" class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                        <input type="checkbox" name="allowed_users[]" value="{{ $user->id }}" :disabled="restrictionType !== 'users'"
                                                                               @if(in_array($user->id, $status->allowed_users ?? [])) checked @endif
                                                                               class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                        {{ $user->name }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 pt-2">
                                                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        ذخیره
                                                    </button>
                                                    <button type="button" @click="editing = false" class="px-4 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 transition-all">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Add new status --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden" x-data="addForm('{{ $type }}')">
                    {{-- Collapsed trigger --}}
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <span class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-black leading-none">+</span>
                            افزودن وضعیت جدید
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Form --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         class="border-t border-gray-100 dark:border-gray-700 px-5 pb-5 pt-4" x-cloak>
                        <form method="POST" action="{{ route('services.status-builder.store') }}" @submit="onSubmit()" class="space-y-4">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">نام وضعیت <span class="text-red-400">*</span></label>
                                    <input type="text" name="name" x-model="name" required placeholder="مثال: در انتظار بررسی" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                </div>

                                {{-- Color --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">رنگ</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="color" x-model="color" class="h-9 w-14 rounded-lg border-gray-200 dark:border-gray-700 cursor-pointer shrink-0">
                                        <span class="flex-1 px-3 py-2 rounded-xl text-xs font-bold border transition-all"
                                              :style="`background: ${color}15; color: ${color}; border-color: ${color}30`" x-text="name || 'پیش‌نمایش'"></span>
                                    </div>
                                </div>

                                {{-- Basic Flags --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های عمومی</label>
                                    <div class="flex flex-wrap gap-3 pt-1">
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                                            <input type="hidden" name="is_default" value="0">
                                            <input type="checkbox" name="is_default" value="1" class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            پیش‌فرض
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                                            <input type="hidden" name="is_final" value="0">
                                            <input type="checkbox" name="is_final" value="1" class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            نهایی
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                                            <input type="hidden" name="is_readonly" value="0">
                                            <input type="checkbox" name="is_readonly" value="1" class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            فقط‌خواندنی
                                        </label>
                                    </div>
                                </div>

                                {{-- Type-specific Attributes --}}
                                <div class="col-span-1 sm:col-span-3">
                                    @include('services::status-builder.partials.attributes-form', ['type' => $type, 'status' => null])
                                </div>

                                {{-- Role / User Access Level Selector (ADD) --}}
                                <div class="col-span-1 sm:col-span-3 border-t border-gray-100 dark:border-gray-700/50 pt-4 mt-2" x-data="{ restrictionType: 'all', search: '' }">
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">سطح دسترسی (چه کسانی می‌توانند این وضعیت را اعمال کنند؟)</label>
                                    <div class="flex gap-4 mb-4">
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                            <input type="radio" x-model="restrictionType" value="all" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            همه مجازند
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                            <input type="radio" x-model="restrictionType" value="roles" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            محدود به نقش‌ها
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                            <input type="radio" x-model="restrictionType" value="users" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            محدود به کاربران
                                        </label>
                                    </div>

                                    {{-- Roles Search & List --}}
                                    <div x-show="restrictionType === 'roles'" x-cloak class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                                        <input type="text" x-model="search" placeholder="جستجوی نقش..." class="w-full sm:w-1/2 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                        <div class="flex flex-wrap gap-3 max-h-40 overflow-y-auto">
                                            @foreach($roles->reject(fn($r) => in_array(mb_strtolower($r->name), ['super-admin', 'superadmin'])) as $role)
                                                <label x-show="search === '' || '{{ mb_strtolower($role->name) }}'.includes(search.toLowerCase())" class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                    <input type="checkbox" name="allowed_roles[]" value="{{ $role->name }}" :disabled="restrictionType !== 'roles'" class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    {{ $role->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Users Search & List --}}
                                    <div x-show="restrictionType === 'users'" x-cloak class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                                        <input type="text" x-model="search" placeholder="جستجوی کاربر..." class="w-full sm:w-1/2 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                        <div class="flex flex-wrap gap-3 max-h-40 overflow-y-auto">
                                            @foreach($users as $user)
                                                <label x-show="search === '' || '{{ mb_strtolower($user->name) }}'.includes(search.toLowerCase())" class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                                    <input type="checkbox" name="allowed_users[]" value="{{ $user->id }}" :disabled="restrictionType !== 'users'" class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    {{ $user->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="mt-4 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                افزودن وضعیت
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @endforeach

    </div>

    @push('scripts')
        <script>
            function statusBuilder() {
                return {
                    activeTab: '{{ old("type", "service") }}',
                    init() {
                    }
                }
            }

            function addForm(type) {
                return {
                    open: {{ (session('_type') === $type && $errors->any()) || old('type') === $type ? 'true' : 'false' }},
                    name: '{{ old('name') }}',
                    color: '{{ old('color', '#6366f1') }}',
                    onSubmit() {
                    }
                }
            }
        </script>
    @endpush
@endsection
