<x-app-layout>
    {{-- هدر صفحه --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('مدیریت کاربران') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                {{-- کانتینر اصلی --}}
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <!-- نمایش پیام موفقیت (در صورت وجود) -->
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <h1 class="mt-2 text-2xl font-medium text-gray-900">
                        لیست کاربران سیستم
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        در این بخش می‌توانید لیست تمام کاربران ثبت‌شده در CRM را مشاهده و مدیریت کنید.
                    </p>
                </div>

                {{-- جدول نمایش کاربران --}}
                <div class="bg-gray-200 bg-opacity-25 p-6 lg:p-8">
                    <div class="overflow-x-auto rounded-lg shadow-md">
                        <table class="w-full whitespace-no-wrap">
                            <thead>
                            <tr class="text-xs font-semibold tracking-wide text-right text-gray-500 uppercase border-b bg-gray-50">
                                <th class="px-4 py-3">نام</th>
                                <th class="px-4 py-3">ایمیل</th>
                                <th class="px-4 py-3">موبایل</th>
                                <th class="px-4 py-3">نقش</th>
                                <th class="px-4 py-3">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                            @forelse ($users as $user)
                                <tr class="text-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center text-sm">
                                            <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                                {{-- نمایش عکس پروفایل --}}
                                                <img class="object-cover w-full h-full rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                                                <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                                            </div>
                                            <div>
                                                <p class="font-semibold">{{ $user->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $user->mobile ?? '-' }} {{-- نمایش خط تیره اگر موبایل نداشت --}}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{-- نمایش نقش کاربر --}}
                                        @if ($user->roles->isNotEmpty())
                                            <span class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">
                                                    {{ $user->roles->first()->name }}
                                                </span>
                                        @else
                                            <span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full">
                                                    -
                                                </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{-- دکمه ویرایش --}}
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                            ویرایش
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                                        هیچ کاربری یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- لینک‌های صفحه‌بندی --}}
                    <div class="mt-8">
                        {{ $users->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

