@extends('layouts.user')

@section('title', 'تنظیمات و ترجیحات اعلان‌ها')

@section('content')
<div class="w-full mx-auto px-4 py-8 space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                تنظیمات و ترجیحات اعلان‌ها
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                مدیریت نحوه دریافت اعلان‌ها از کانال‌های مختلف (درون‌برنامه‌ای، پیامک، ایمیل) و زمان‌بندی ساعات سکوت
            </p>
        </div>

        <div>
            <a href="{{ route('user.notifications.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>بازگشت به اعلان‌ها</span>
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

    <form action="{{ route('user.notifications.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- بخش ۱: تنظیمات عمومی و ساعات سکوت --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    تنظیمات عمومی و ساعات سکوت (Do Not Disturb)
                </h3>
            </div>

            <div class="p-6 space-y-6">
                {{-- فعال‌سازی صدای اعلان --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">پخش صدای اعلان در مرورگر</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">در صورت دریافت اعلان جدید درون‌برنامه‌ای، صدای ملایم پخش شود.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sound_enabled" value="1" class="sr-only peer" {{ $userSetting->sound_enabled ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                {{-- ساعات سکوت --}}
                <div x-data="{ enabled: {{ $userSetting->quiet_hours_enabled ? 'true' : 'false' }} }" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white">فعال‌سازی ساعات سکوت شبانه</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">در این بازه زمانی پیامک‌های اطلاع‌رسانی ارسال نخواهند شد (مگر اعلان‌های فوق‌اضطراری).</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="quiet_hours_enabled" value="1" x-model="enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div x-show="enabled" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">ساعت شروع سکوت</label>
                            <input type="time" name="quiet_hours_start" value="{{ substr($userSetting->quiet_hours_start ?? '22:00', 0, 5) }}"
                                   class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">ساعت پایان سکوت</label>
                            <input type="time" name="quiet_hours_end" value="{{ substr($userSetting->quiet_hours_end ?? '08:00', 0, 5) }}"
                                   class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- بخش ۲: ماتریس کانال‌های اعلان به تفکیک موضوع --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    کانال‌های دریافت به تفکیک موضوع
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/20 text-xs font-bold text-gray-500 dark:text-gray-400">
                            <th class="py-3.5 px-6">موضوع / دسته‌بندی</th>
                            <th class="py-3.5 px-6 text-center">اعلان درون‌برنامه‌ای (پنل)</th>
                            <th class="py-3.5 px-6 text-center">پیامک (SMS)</th>
                            <th class="py-3.5 px-6 text-center">ایمیل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @foreach($categories as $catKey => $cat)
                            @php
                                $userPref = $userPreferences[$catKey] ?? null;
                                $dbChecked = $userPref ? $userPref->channel_database : in_array('database', $defaultChannels[$catKey] ?? ['database']);
                                $smsChecked = $userPref ? $userPref->channel_sms : in_array('sms', $defaultChannels[$catKey] ?? []);
                                $mailChecked = $userPref ? $userPref->channel_mail : in_array('mail', $defaultChannels[$catKey] ?? []);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/30 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $cat['label'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $cat['description'] }}</div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <input type="checkbox" name="preferences[{{ $catKey }}][database]" value="1"
                                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500"
                                           {{ $dbChecked ? 'checked' : '' }}>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <input type="checkbox" name="preferences[{{ $catKey }}][sms]" value="1"
                                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500"
                                           {{ $smsChecked ? 'checked' : '' }}>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <input type="checkbox" name="preferences[{{ $catKey }}][mail]" value="1"
                                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500"
                                           {{ $mailChecked ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>ذخیره تغییرات ترجیحات</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
