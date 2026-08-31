@extends('layouts.user')

@section('content')
    @php
        $serviceLabel = config('booking.labels.service', 'سرویس');
    @endphp
    <div class="space-y-6 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    ویرایش {{ $serviceLabel }}: {{ $service->name }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">تغییر اطلاعات، سیاست‌ها و فرم اختصاصی {{ $serviceLabel }}.</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 shadow-sm transition-all"
               href="{{ session('services_index_url', route('user.booking.services.index')) }}">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به لیست
            </a>
        </div>

        <form method="POST" action="{{ route('user.booking.services.update', $service) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('booking::user.services._form', ['service' => $service])
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ session('services_index_url', route('user.booking.services.index')) }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                    انصراف
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره تغییرات {{ $serviceLabel }}
                </button>
            </div>
        </form>
    </div>
@endsection