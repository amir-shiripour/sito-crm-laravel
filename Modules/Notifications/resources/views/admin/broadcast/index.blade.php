@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;
@endphp

@section('title', 'تاریخچه اطلاعیه‌های همگانی')

@section('content')
<div class="w-full mx-auto px-4 py-8 space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </span>
                اطلاعیه‌ها و پیام‌های همگانی
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                تاریخچه اعلان‌های صادر شده توسط مدیران سیستم و ابزار ارسال پیام گروهی
            </p>
        </div>

        <div>
            <a href="{{ route('admin.notifications.broadcast.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>ارسال اطلاعیه جدید</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- جدول تاریخچه --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/30 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <th class="py-3.5 px-6">عنوان و پیام</th>
                        <th class="py-3.5 px-6 text-center">مخاطبان هدف</th>
                        <th class="py-3.5 px-6 text-center">کانال‌ها</th>
                        <th class="py-3.5 px-6 text-center">تعداد گیرندگان</th>
                        <th class="py-3.5 px-6 text-center">فرستنده</th>
                        <th class="py-3.5 px-6 text-center">تاریخ ارسال</th>
                        <th class="py-3.5 px-6 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                    @forelse($broadcasts as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/30 transition-colors">
                            <td class="py-4 px-6 max-w-sm">
                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>{{ $item->title }}</span>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full font-bold
                                        {{ $item->priority === 'urgent' ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400' : ($item->priority === 'high' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300') }}">
                                        {{ $item->priority === 'urgent' ? 'فوری' : ($item->priority === 'high' ? 'مهم' : 'عادی') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $item->message }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                    {{ $item->target_type === 'all' ? 'همه کاربران' : ($item->target_type === 'role' ? 'نقش‌های سازمانی' : 'کاربران انتخابی') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @foreach($item->channels ?? [] as $ch)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $ch === 'database' ? 'پنل' : ($ch === 'sms' ? 'پیامک' : 'ایمیل') }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-gray-700 dark:text-gray-300">
                                {{ $item->recipients_count }} نفر
                            </td>
                            <td class="py-4 px-6 text-center text-xs text-gray-600 dark:text-gray-400">
                                {{ $item->creator?->name ?? 'سیستم' }}
                            </td>
                            <td class="py-4 px-6 text-center text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->sent_at ? Jalalian::fromCarbon($item->sent_at)->format('H:i - Y/m/d') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form action="{{ route('admin.notifications.broadcast.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('آیا از حذف این لاگ اطمینان دارید؟');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                تاکنون هیچ اطلاعیه همگانی ارسال نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($broadcasts->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $broadcasts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
