@extends('layouts.user')

@section('title', 'درون‌ریزی اکسل خروج و مصرف کالا')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.consumptions.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">خروج و مصرف کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">درون‌ریزی اکسل</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                درون‌ریزی گروهی خروج و ثبت مصرف کالا از اکسل
            </h1>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    @if(session('import_errors') && is_array(session('import_errors')))
        <div class="bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-4 space-y-1.5 text-xs text-amber-800 dark:text-amber-200">
            <div class="font-bold">جزئیات ردیف‌های خطادار:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 shadow-xs space-y-5">
        <div class="rounded-xl bg-indigo-50/70 dark:bg-indigo-500/10 border border-indigo-200/70 dark:border-indigo-500/30 p-4 text-xs text-slate-700 dark:text-slate-200 space-y-2">
            <p class="font-bold text-indigo-700 dark:text-indigo-300">ساختار استاندارد ستون‌های اکسل (.xlsx):</p>
            <p>
                تاریخ | کد کالا | نام کالا | تعداد کالا | نام بیمار | شماره پرونده | نام انبار یا انبارک | توضیحات
            </p>
            <p class="text-[11px] text-slate-600 dark:text-slate-300">
                سیستم هر دو عنوان ستون «نام انبار» و «نام انبارک» را خوانده و هم از انبار مرکزی و هم از انبارک‌ها موجودی را به‌صورت خودکار کسر می‌کند.
            </p>
        </div>

        <form action="{{ route('admin.warehouse.consumptions.import.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-2">
                    انتخاب فایل اکسل (.xlsx) <span class="text-rose-500">*</span>
                </label>
                <input type="file"
                       name="excel_file"
                       accept=".xlsx"
                       required
                       class="block w-full text-xs text-slate-700 dark:text-slate-200 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 border border-gray-200 dark:border-gray-700 rounded-xl p-2 bg-gray-50 dark:bg-gray-900/50 font-sans">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('admin.warehouse.consumptions.index') }}"
                   class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-slate-700 dark:text-slate-200">
                    بازگشت
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                    آپلود و ثبت خروج کالاها
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
