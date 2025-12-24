@extends('layouts.install')

@section('title', 'نصب - مرحله ۳: انتخاب قالب')

@section('content')
    <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden mt-10">
        <h2 class="text-2xl font-bold text-center text-gray-800 py-6 px-6 bg-gray-50 border-b">
            مرحله ۳: انتخاب قالب (تم)
        </h2>

        <!-- نمایش خطاها -->
        @if ($errors->any())
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 m-6" role="alert">
                <p class="font-bold">خطا در پردازش</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('install.processStep3') }}" method="POST" class="px-8 py-6">
            @csrf

            <p class="mb-6 text-gray-600 text-base leading-relaxed">
                لطفا قالب (تم) اصلی وب‌سایت خود را انتخاب کنید. ماژول‌های مورد نیاز این قالب به صورت خودکار فعال خواهند شد و می‌توانید بعداً آن‌ها را از پنل مدیریت تغییر دهید.
            </p>

            <div class="space-y-4">
                @if(isset($themes) && !$themes->isEmpty())
                    @foreach ($themes as $theme)
                        <label for="theme_{{ $theme->id }}"
                               class="flex items-center justify-between p-5 border rounded-lg cursor-pointer transition-all duration-200
                                  hover:bg-indigo-50 hover:border-indigo-300 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400 has-[:checked]:ring-2 has-[:checked]:ring-indigo-200">
                            <div>
                                <span class="text-lg font-semibold text-gray-900">{{ $theme->name }}</span>
                                <p class="text-sm text-gray-600 mt-1">{{ $theme->description }}</p>
                            </div>
                            <input type="radio"
                                   name="theme_id"
                                   id="theme_{{ $theme->id }}"
                                   value="{{ $theme->id }}"
                                   class="form-radio h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                {{ $loop->first ? 'checked' : '' }}>
                        </label>
                    @endforeach
                @else
                    <div class="bg-yellow-100 border-r-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p class="font-bold">هشدار</p>
                        <p>هیچ تمی در دیتابیس یافت نشد. لطفاً مطمئن شوید که `ThemeSeeder` به درستی اجرا شده است.</p>
                    </div>
                @endif
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit"
                        class="bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200
                           hover:bg-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50
                           @if(!isset($themes) || $themes->isEmpty()) opacity-50 cursor-not-allowed @endif"
                        @if(!isset($themes) || $themes->isEmpty()) disabled @endif>
                    اتمام نصب و راه‌اندازی
                    <i class="fas fa-arrow-left mr-2"></i> <!-- (نیاز به FontAwesome در لی‌آوت) -->
                </button>
            </div>
        </form>
    </div>
@endsection

