<x-app-layout>
    {{-- هدر صفحه که در لی‌آوت اصلی (app.blade.php) نمایش داده می‌شود --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('مدیریت ماژول‌ها (فیچرها)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <h1 class=" text-2xl font-medium text-gray-900 mb-6">
                        لیست ماژول‌های سیستم
                    </h1>

                    <p class="mb-6 text-gray-600">
                        از این بخش می‌توانید فیچرهای مختلف سیستم را فعال یا غیرفعال کنید. ماژول‌های فعال، قابلیت‌های خود را به سیستم اضافه می‌کنند.
                    </p>

                    <!-- نمایش پیام‌های موفقیت یا خطا -->
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- جدول نمایش ماژول‌ها -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="bg-gray-50 text-right">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">
                                    نام ماژول
                                </th>
                                <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">
                                    توضیحات
                                </th>
                                <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">
                                    وضعیت
                                </th>
                                <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">
                                    عملیات
                                </th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 text-right">
                            @foreach ($modules as $module)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">
                                        {{ $module->name }}
                                        @if($module->slug === 'core')
                                            <span class="text-xs text-red-600">(هسته)</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        {{ $module->description }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        @if ($module->active)
                                            <span class="inline-flex items-center justify-center rounded-full bg-green-100 px-2.5 py-0.5 text-green-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 me-1">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    فعال
                                                </span>
                                        @else
                                            <span class="inline-flex items-center justify-center rounded-full bg-red-100 px-2.5 py-0.5 text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 me-1">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                    </svg>
                                                    غیرفعال
                                                </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{-- ماژول هسته (Core) قابل تغییر نیست --}}
                                        @if ($module->slug !== 'core')
                                            <form action="{{ route('admin.modules.toggle', $module) }}" method="POST">
                                                @csrf
                                                @if ($module->active)
                                                    <button type"submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700 transition">
                                                    غیرفعال کردن
                                                    </button>
                                                @else
                                                    <button type"submit" class="inline-block rounded bg-green-600 px-4 py-2 text-xs font-medium text-white hover:bg-green-700 transition">
                                                    فعال کردن
                                                    </button>
                                                @endif
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500 italic">
                                                    غیرقابل تغییر
                                                </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

