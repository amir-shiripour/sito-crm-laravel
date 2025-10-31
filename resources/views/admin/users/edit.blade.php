<x-app-layout>
    {{-- هدر صفحه --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ویرایش کاربر') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                {{-- کانتینر فرم --}}
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <h1 class="mt-2 text-2xl font-medium text-gray-900">
                        اطلاعات کاربر را به‌روزرسانی کنید
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        در این بخش می‌توانید اطلاعات کاربر و نقش دسترسی او را در سیستم CRM تغییر دهید.
                    </p>

                    <!-- نمایش خطاهای اعتبارسنجی (Validation Errors) -->
                    @if ($errors->any())
                        <div class="mb-4 mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">خطا!</strong>
                            <span class="block sm:inline">لطفاً موارد زیر را بررسی کنید:</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- فرم ویرایش --}}
                    {{-- ما از متد POST استفاده می‌کنیم اما با @method('PUT') به لاراول می‌گوییم که این یک درخواست PUT است --}}
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="mt-10">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- فیلد نام --}}
                            <div>
                                <x-label for="name" value="{{ __('نام') }}" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            </div>

                            {{-- فیلد ایمیل --}}
                            <div>
                                <x-label for="email" value="{{ __('ایمیل') }}" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
                            </div>

                            {{-- فیلد شماره موبایل --}}
                            <div>
                                <x-label for="mobile" value="{{ __('شماره موبایل (اختیاری)') }}" />
                                <x-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile', $user->mobile)" autocomplete="tel" />
                            </div>

                            {{-- فیلد نقش (Role) --}}
                            <div>
                                <x-label for="role" value="{{ __('نقش کاربر') }}" />
                                <select name="role" id="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- انتخاب نقش --</option>
                                    @foreach ($roles as $role)
                                        {{--
                                          چک می‌کنیم که آیا کاربر این نقش را دارد یا نه.
                                          $user->roles->first()->id ?? null:
                                          نقش فعلی کاربر را می‌گیرد (اگر نقشی داشته باشد).
                                        --}}
                                        <option value="{{ $role->name }}" {{ (old('role', $user->roles->first()->name ?? null) == $role->name) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- دکمه ذخیره --}}
                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('admin.users.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('لغو') }}
                            </a>

                            <x-button class="ms-4">
                                {{ __('ذخیره تغییرات') }}
                            </x-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

