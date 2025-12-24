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

    <div class="max-w-5xl mx-auto space-y-8 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">ویرایش گردش کار: <span class="text-indigo-600 dark:text-indigo-400">{{ $workflow->name }}</span></h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مدیریت مراحل، اکشن‌ها و تنظیمات فعال‌سازی.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('user.workflows.index') }}"
                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    بازگشت
                </a>
                <form method="post" action="{{ route('user.workflows.destroy', $workflow) }}" onsubmit="return confirm('آیا مطمئن هستید؟ تمام مراحل و اکشن‌ها حذف خواهند شد.');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/30">
                        حذف گردش کار
                    </button>
                </form>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Settings --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">تنظیمات عمومی</h2>
            </div>
            <div class="p-6">
                @include('workflows::user.workflows._form', [
                    'workflow' => $workflow,
                    'action' => route('user.workflows.update', $workflow),
                    'method' => 'patch'
                ])
            </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700 border-dashed">

        {{-- Stages Section --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">مراحل گردش کار (Stages)</h2>
            </div>

            {{-- Add New Stage Form --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">افزودن مرحله جدید</h3>
                <form method="post" action="{{ route('user.workflows.stages.store', $workflow) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <input type="text" name="name" placeholder="نام مرحله (مثلاً: ارسال پیامک تایید)" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <input type="number" name="sort_order" min="0" value="0" placeholder="ترتیب"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-3 flex items-center gap-4 pt-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="is_initial" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <span>نقطه شروع</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="is_final" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <span>نقطه پایان</span>
                            </label>
                        </div>
                        <div class="md:col-span-2 text-left">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                افزودن
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Stages List --}}
            <div class="space-y-6">
                @forelse($workflow->stages as $stage)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden transition hover:shadow-md">
                        {{-- Stage Header --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 text-sm font-bold text-gray-600 dark:text-gray-200 shadow-sm">
                                    {{ $stage->sort_order }}
                                </span>
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">{{ $stage->name }}</h3>

                                <div class="flex gap-2">
                                    @if($stage->is_initial)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            شروع
                                        </span>
                                    @endif
                                    @if($stage->is_final)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            پایان
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <form method="post" action="{{ route('user.workflows.stages.destroy', [$workflow, $stage]) }}" onsubmit="return confirm('آیا از حذف این مرحله اطمینان دارید؟ تمام اکشن‌های داخل آن نیز حذف خواهند شد.');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                                        حذف مرحله
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="p-5 space-y-6">
                            {{-- Actions List --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">اکشن‌ها (عملیات)</h4>
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium dark:bg-gray-700 dark:text-gray-300">{{ $stage->actions->count() }}</span>
                                </div>

                                <div class="space-y-3">
                                    @foreach($stage->actions as $action)
                                        @include('workflows::user.workflows._action-form', ['action' => $action, 'mode' => 'edit'])
                                    @endforeach
                                </div>

                                {{-- Add Action --}}
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-medium text-gray-500 mb-3 uppercase tracking-wider">افزودن اکشن جدید</p>
                                    @include('workflows::user.workflows._action-form', ['action' => null, 'mode' => 'create'])
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">هیچ مرحله‌ای تعریف نشده است</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">برای شروع، یک مرحله جدید اضافه کنید.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
