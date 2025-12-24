@extends('layouts.user')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                تنظیمات پیامک
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                تنظیمات درگاه پیامک برای ارسال اعلان‌ها، OTP و پیامک‌های سیستمی.
            </p>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            <form method="POST" action="{{ route('user.sms.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- درایور و شماره ارسال‌کننده --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">
                            درایور
                        </label>
                        <select name="driver"
                                class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="null" @selected(optional($setting)->driver === 'null')>
                                Null (فقط لاگ)
                            </option>
                            <option value="limosms" @selected(optional($setting)->driver === 'limosms')>
                                Limo SMS
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">
                            شماره ارسال کننده (Sender)
                        </label>
                        <input type="text" name="sender"
                               value="{{ old('sender', optional($setting)->sender) }}"
                               class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                </div>

                {{-- API Key و Base URL --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">
                            API Key
                        </label>
                        <input type="text" name="api_key"
                               value="{{ old('api_key', data_get($setting, 'config.api_key')) }}"
                               class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">
                            Base URL
                        </label>
                        <input type="text" name="base_url"
                               value="{{ old('base_url', data_get($setting, 'config.base_url')) }}"
                               class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                </div>

                {{-- پترن OTP مخصوص ورود کلاینت‌ها --}}
                @if($clientsModuleInstalled)
                    <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700 space-y-3">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            پترن OTP برای ورود مشتریان
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">
                                    کد / شناسه پترن OTP (OtpId در لیمو)
                                </label>
                                <input type="text" name="client_otp_pattern"
                                       value="{{ old('client_otp_pattern', $clientOtpPattern) }}"
                                       placeholder="مثلاً 12"
                                       class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                <p class="mt-1 text-[11px] text-gray-400">
                                    این شناسه برای ارسال کد ورود یکبارمصرف (OTP) به مشتریان در صفحه لاگین پرتال استفاده خواهد شد.
                                    ما در زمان پیاده‌سازی لاگین، برای این پترن مقدار <code>{0}</code> را با کد OTP جایگزین می‌کنیم.
                                </p>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <div class="font-semibold mb-1">
                                    مثال تعریف پترن در لیمو:
                                </div>
                                <p class="text-[11px] leading-relaxed">
                                    متن پترن در لیمو:
                                    «کد ورود شما <code>{0}</code> می‌باشد.»<br>
                                    هنگام ورود مشتری، ما یک کد مثل <code>48291</code> تولید می‌کنیم
                                    و با همین پترن برای او ارسال می‌کنیم.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                        ذخیره تنظیمات
                    </button>

                    @if($balance)
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            مانده اعتبار:
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $balance['balance'] ?? 'نامشخص' }}
                            </span>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
