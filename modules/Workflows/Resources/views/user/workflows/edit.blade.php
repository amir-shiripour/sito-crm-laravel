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

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">ویرایش گردش کار: {{ $workflow->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مدیریت مراحل، اکشن‌ها و تنظیمات فعال‌سازی.</p>
            </div>
            <a href="{{ route('user.workflows.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">بازگشت</a>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl space-y-1">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">اطلاعات کلی</h2>
                <form method="post" action="{{ route('user.workflows.destroy', $workflow) }}" onsubmit="return confirm('حذف گردش کار و تمام مراحل/اکشن‌ها؟');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700">حذف</button>
                </form>
            </div>
            @include('workflows::user.workflows._form', [
                'workflow' => $workflow,
                'action' => route('user.workflows.update', $workflow),
                'method' => 'patch'
            ])
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">افزودن مرحله جدید</h2>
            </div>
            <form method="post" action="{{ route('user.workflows.stages.store', $workflow) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <input type="text" name="name" placeholder="نام مرحله" required
                               class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                      focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                      dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <input type="number" name="sort_order" min="0" value="0" placeholder="ترتیب"
                               class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                      focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                      dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" name="is_initial" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> شروع
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" name="is_final" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> پایان
                        </label>
                    </div>
                </div>
                <textarea name="description" rows="2" placeholder="توضیحات"
                          class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition">ثبت مرحله</button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($workflow->stages as $stage)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-100">
                            <span class="font-semibold">مرحله {{ $stage->sort_order }}</span>
                            <span class="text-gray-400">|</span>
                            <span>{{ $stage->name }}</span>
                            @if($stage->is_initial)
                                <span class="px-2 py-0.5 text-[11px] rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-700/20 dark:text-emerald-300">شروع</span>
                            @endif
                            @if($stage->is_final)
                                <span class="px-2 py-0.5 text-[11px] rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-700/20 dark:text-indigo-200">پایان</span>
                            @endif
                        </div>
                        <form method="post" action="{{ route('user.workflows.stages.destroy', [$workflow, $stage]) }}" onsubmit="return confirm('حذف این مرحله و اکشن‌هایش؟');">
                            @csrf
                            @method('delete')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-700">حذف مرحله</button>
                        </form>
                    </div>

                    <form method="post" action="{{ route('user.workflows.stages.update', [$workflow, $stage]) }}" class="space-y-3">
                        @csrf
                        @method('patch')
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <input type="text" name="name" value="{{ $stage->name }}" required
                                       class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                              focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                              dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <input type="number" name="sort_order" value="{{ $stage->sort_order }}" min="0"
                                       class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                              focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                              dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" name="is_initial" value="1" @checked($stage->is_initial) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> شروع
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" name="is_final" value="1" @checked($stage->is_final) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> پایان
                                </label>
                            </div>
                        </div>
                        <textarea name="description" rows="2"
                                  class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                                         focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                                         dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ $stage->description }}</textarea>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">ذخیره مرحله</button>
                    </form>

                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700 pt-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">اکشن‌ها</h3>
                        </div>

                        @foreach($stage->actions as $action)
                            @include('workflows::user.workflows._action-form', ['action' => $action, 'mode' => 'edit'])
                        @endforeach

                        @include('workflows::user.workflows._action-form', ['action' => null, 'mode' => 'create'])
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500 dark:text-gray-400">مرحله‌ای ثبت نشده است.</div>
            @endforelse
        </div>
    </div>
@endsection
