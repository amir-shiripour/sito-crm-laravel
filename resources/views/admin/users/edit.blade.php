<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ویرایش کاربر: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800">

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-label for="name" value="{{ __('نام') }}" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error for="name" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-label for="email" value="{{ __('ایمیل') }}" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error for="email" class="mt-2" />
                            </div>

                            <!-- Mobile -->
                            <div>
                                <x-label for="mobile" value="{{ __('شماره موبایل') }}" />
                                <x-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile', $user->mobile)" />
                                <x-input-error for="mobile" class="mt-2" />
                            </div>

                            <!-- Role -->
                            <div>
                                <x-label for="role" value="{{ __('نقش') }}" />
                                <select name="role" id="role" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="role" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="col-span-1 md:col-span-2">
                                <hr class="my-4 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">در صورت تمایل به تغییر رمز عبور، فیلدهای زیر را پر کنید.</p>
                            </div>

                            <div>
                                <x-label for="password" value="{{ __('رمز عبور جدید') }}" />
                                <x-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                <x-input-error for="password" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <x-label for="password_confirmation" value="{{ __('تکرار رمز عبور جدید') }}" />
                                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                                <x-input-error for="password_confirmation" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
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

