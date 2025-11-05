<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('مدیریت ماژول‌ها') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-gray-700 dark:text-green-300" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-gray-700 dark:text-red-300" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">
                                    نام ماژول
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    توضیحات
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    وضعیت دیتابیس
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    وضعیت بارگذاری
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            {{-- $dbModules توسط کنترلر ارسال شده و فقط شامل ماژول‌های غیراصلی است --}}
                            @forelse ($dbModules as $module)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $module->name }}
                                    </th>
                                    <td class="py-4 px-6">
                                        {{ $module->description }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($module->active)
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-green-200 dark:text-green-900">فعال</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        {{-- وضعیت واقعی از پکیج nwidart خوانده می‌شود --}}
                                        @php
                                            $isLoaded = $packageModulesStatus[$module->slug]['active'] ?? false;
                                        @endphp
                                        @if ($isLoaded)
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-900">بارگذاری شده</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-gray-600 dark:text-gray-300">بارگذاری نشده</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        {{-- اصلاح شد: اکشن فرم به POST تغییر کرد --}}
                                        <form action="{{ route('admin.modules.toggle') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $module->slug }}">
                                            @if ($isLoaded)
                                                <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">غیرفعال سازی</button>
                                            @else
                                                <button type="submit" class="font-medium text-green-600 dark:text-green-500 hover:underline">فعال سازی</button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="5" class="py-4 px-6 text-center">
                                        {{-- اصلاح شد: پیام واضح‌تر شده است --}}
                                        هیچ ماژول *اختیاری* (قابل فعال‌سازی) یافت نشد. ماژول‌های هسته در این لیست نمایش داده نمی‌شوند.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

