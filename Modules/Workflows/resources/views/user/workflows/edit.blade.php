@extends('layouts.user')

@section('title', 'ویرایش گردش کار')

@section('content')
    @php($tokenOptions = [
        'client_name' => 'نام مشتری',
        'appointment_date_jalali' => 'تاریخ (شمسی)',
        'appointment_time_jalali' => 'ساعت',
        'appointment_datetime_jalali' => 'تاریخ و ساعت کامل',
        'service_name' => 'نام سرویس',
        'provider_name' => 'نام ارائه‌دهنده',
    ])

    <div class="max-w-5xl mx-auto space-y-8 pb-20">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">ویرایش گردش کار: <span class="text-indigo-600 dark:text-indigo-400">{{ $workflow->name }}</span></h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مدیریت مراحل، اکشن‌ها و تنظیمات فعال‌سازی.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('user.workflows.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                    بازگشت
                </a>
                <form method="post" action="{{ route('user.workflows.destroy', $workflow) }}" onsubmit="return confirm('آیا مطمئن هستید؟ تمام مراحل و اکشن‌ها حذف خواهند شد.');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/30 transition-colors">
                        حذف گردش کار
                    </button>
                </form>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-300 shadow-sm">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Settings --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    تنظیمات عمومی
                </h2>
            </div>
            <div class="p-6">
                @include('workflows::user.workflows._form', [
                    'workflow' => $workflow,
                    'action' => route('user.workflows.update', $workflow),
                    'method' => 'patch'
                ])
            </div>
        </div>

        {{-- Stages Section --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-8 bg-indigo-500 rounded-full"></span>
                    مراحل گردش کار (Stages)
                </h2>
            </div>

            {{-- Add New Stage Form (Collapsible) --}}
            <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        افزودن مرحله جدید
                    </span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <form method="post" action="{{ route('user.workflows.stages.store', $workflow) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-5">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">نام مرحله</label>
                                <input type="text" name="name" placeholder="مثلاً: ارسال پیامک تایید" required
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">ترتیب</label>
                                <input type="number" name="sort_order" min="0" value="{{ $workflow->stages->count() + 1 }}"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="md:col-span-3 flex flex-col justify-center gap-2 pt-1">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="is_initial" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    <span>نقطه شروع (Initial)</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="is_final" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    <span>نقطه پایان (Final)</span>
                                </label>
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    افزودن
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stages List --}}
            <div class="space-y-6">
                @forelse($workflow->stages as $stage)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden transition hover:shadow-md group">
                        {{-- Stage Header --}}
                        <div class="bg-gray-50 dark:bg-gray-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 text-sm font-bold text-gray-600 dark:text-gray-200 shadow-sm">
                                    {{ $stage->sort_order }}
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        {{ $stage->name }}
                                        @if($stage->is_initial)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                شروع
                                            </span>
                                        @endif
                                        @if($stage->is_final)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-500">
                                                پایان
                                            </span>
                                        @endif
                                    </h3>
                                    @if($stage->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $stage->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                {{-- Edit Stage Button (Could be a modal, for now just delete) --}}
                                <form method="post" action="{{ route('user.workflows.stages.destroy', [$workflow, $stage]) }}" onsubmit="return confirm('آیا از حذف این مرحله اطمینان دارید؟ تمام اکشن‌های داخل آن نیز حذف خواهند شد.');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 px-3 py-1.5 rounded-lg transition-colors">
                                        حذف مرحله
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="p-6 bg-white dark:bg-gray-800">
                            {{-- Actions List --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        عملیات‌ها (Actions)
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium dark:bg-gray-700 dark:text-gray-300">{{ $stage->actions->count() }}</span>
                                </div>

                                <div class="space-y-3 pl-0 sm:pl-4 border-l-2 border-gray-100 dark:border-gray-700 ml-2">
                                    @foreach($stage->actions as $action)
                                        <div class="relative pl-4">
                                            {{-- Connector Line --}}
                                            <div class="absolute top-4 left-0 w-4 h-px bg-gray-200 dark:bg-gray-700"></div>

                                            @include('workflows::user.workflows._action-form', ['action' => $action, 'mode' => 'edit'])
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Add Action --}}
                                <div class="mt-6 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700 pl-0 sm:pl-8">
                                    @include('workflows::user.workflows._action-form', ['action' => null, 'mode' => 'create'])
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">هنوز مرحله‌ای تعریف نشده است</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">برای شروع فرآیند، اولین مرحله را با استفاده از دکمه بالا اضافه کنید.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
