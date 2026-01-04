@extends('layouts.user')

@section('content')
    @php
        // استایل‌های مشترک
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    @endphp

    <div class="w-full mx-auto px-4 py-8 space-y-6">

        {{-- هدر و دکمه ایجاد --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </span>
                    گزارش پیامک‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    لیست و وضعیت تمامی پیامک‌های ارسال شده (سیستمی، دستی و OTP)
                </p>
            </div>

            @can('sms.messages.send')
                <a href="{{ route('user.sms.send.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>ارسال پیامک جدید</span>
                </a>
            @endcan
        </div>

        {{-- فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                {{-- جستجو --}}
                <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                    <label class="{{ $labelClass }}">جستجو</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="شماره موبایل، متن پیام..."
                               class="{{ $inputClass }} pl-9">
                        <div class="absolute left-3 top-2.5 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                </div>

                {{-- نوع --}}
                <div>
                    <label class="{{ $labelClass }}">نوع ارسال</label>
                    <select name="type" class="{{ $inputClass }} appearance-none">
                        <option value="">همه</option>
                        <option value="system" @selected(request('type') === 'system')>سیستمی (System)</option>
                        <option value="scheduled" @selected(request('type') === 'scheduled')>برنامه‌ریزی شده</option>
                        <option value="manual" @selected(request('type') === 'manual')>دستی (Manual)</option>
                        <option value="otp" @selected(request('type') === 'otp')>رمز یکبار مصرف (OTP)</option>
                    </select>
                </div>

                {{-- وضعیت --}}
                <div>
                    <label class="{{ $labelClass }}">وضعیت</label>
                    <select name="status" class="{{ $inputClass }} appearance-none">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="pending" @selected(request('status') === 'pending')>در صف ارسال</option>
                        <option value="sent" @selected(request('status') === 'sent')>موفق (Sent)</option>
                        <option value="failed" @selected(request('status') === 'failed')>ناموفق (Failed)</option>
                    </select>
                </div>

                {{-- دکمه‌ها --}}
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-sm font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 hover:border-indigo-200 transition-colors dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                        فیلتر
                    </button>
                    @if(request()->anyFilled(['q', 'type', 'status']))
                        <a href="{{ route('user.sms.logs.index') }}"
                           class="px-3 py-2 rounded-xl text-sm font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-red-500 transition-colors dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
                           title="پاک‌سازی فیلترها">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            @if($messages->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">موبایل گیرنده</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">نوع</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">وضعیت</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">درایور</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">پیام / الگو</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">تاریخ</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($messages as $msg)
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- موبایل --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono font-medium text-gray-900 dark:text-gray-100 dir-ltr inline-block">
                                        {{ $msg->to }}
                                    </span>
                                </td>

                                {{-- نوع --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeClass = match($msg->type) {
                                            'otp' => 'text-blue-600 bg-blue-50 dark:text-blue-300 dark:bg-blue-900/30',
                                            'system' => 'text-gray-600 bg-gray-100 dark:text-gray-300 dark:bg-gray-700',
                                            'manual' => 'text-indigo-600 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-900/30',
                                            'scheduled' => 'text-amber-600 bg-amber-50 dark:text-amber-300 dark:bg-amber-900/30',
                                            default => 'text-gray-600 bg-gray-50',
                                        };
                                        $typeLabel = match($msg->type) {
                                            'otp' => 'رمز یکبار مصرف',
                                            'system' => 'سیستمی',
                                            'manual' => 'دستی',
                                            'scheduled' => 'زمان‌بندی',
                                            default => $msg->type,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $typeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                {{-- وضعیت --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($msg->status === 'sent')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            ارسال شد
                                        </span>
                                    @elseif($msg->status === 'failed')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800" title="{{ $msg->error_message ?? '' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            ناموفق
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700/50 dark:text-gray-300 dark:border-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 animate-pulse"></span>
                                            {{ $msg->status }}
                                        </span>
                                    @endif
                                </td>

                                {{-- درایور --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-mono text-xs">{{ $msg->driver ?? '—' }}</span>
                                </td>

                                {{-- پیام --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 max-w-xs">
                                        @if($msg->template_key)
                                            <div class="flex items-center gap-1">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                    Pattern: {{ $msg->template_key }}
                                                </span>
                                            </div>
                                        @endif

                                        @if($msg->message)
                                            <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2 leading-relaxed" title="{{ $msg->message }}">
                                                {{ $msg->message }}
                                            </p>
                                        @else
                                            <span class="text-xs text-gray-400 italic">بدون متن</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- تاریخ --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-end">
                                        <span class="font-bold text-gray-700 dark:text-gray-300 dir-ltr text-xs">
                                            {{ $msg->created_at?->format('Y-m-d') }}
                                        </span>
                                        <span class="text-xs text-gray-400 dir-ltr">
                                            {{ $msg->created_at?->format('H:i') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- صفحه‌بندی --}}
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 dark:bg-gray-800">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">هیچ پیامکی یافت نشد</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        با توجه به فیلترهای اعمال شده، موردی برای نمایش وجود ندارد.
                    </p>
                    <a href="{{ route('user.sms.logs.index') }}" class="mt-4 text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                        پاک‌سازی فیلترها
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
