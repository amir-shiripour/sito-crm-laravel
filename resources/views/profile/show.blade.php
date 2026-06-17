@extends('layouts.user')

@php
    $title = __('پروفایل کاربری');
    $user = auth()->user();
    $userRolesDisplay = $user->roles->pluck('display_name')->toArray();
@endphp

@section('content')

    <div class="mx-auto max-w-full space-y-6"
         x-data="{ activeTab: '{{ session('active_tab', 'profile') }}' }">
        <div class="relative dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">

                {{-- Avatar + name --}}
                <div class="flex items-center gap-4">
                    @if(Laravel\Jetstream\Jetstream::managesProfilePhotos() && $user->profile_photo_path)
                        <img class="h-16 w-16 shrink-0 rounded-full object-cover ring-4 ring-white dark:ring-gray-800"
                             src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"/>
                    @else
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full
                        bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300
                        text-2xl font-bold ring-4 ring-white dark:ring-gray-800">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-mono dir-ltr">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $user->email }}</span>
                            </div>
                            @if(count($userRolesDisplay))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border
                                text-xs font-medium bg-emerald-50 text-emerald-700 border-emerald-100
                                dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                {{ implode('، ', $userRolesDisplay) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tab nav --}}
                <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto mt-2 xl:mt-0">
                    <nav class="flex w-full sm:w-auto space-x-3 space-x-reverse overflow-x-auto pb-2 sm:pb-0 hide-scrollbar">

                        <button @click="activeTab = 'profile'"
                                :class="activeTab === 'profile' ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300' : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium
                                transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            اطلاعات کاربری
                        </button>

                        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                            <button @click="activeTab = 'security'"
                                    :class="activeTab === 'security' ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300' : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm
                                    font-medium transition-all whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                امنیت
                            </button>
                        @endif

                        <button @click="activeTab = 'sessions'"
                                :class="activeTab === 'sessions' ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300' : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm
                                font-medium transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            نشست‌ها
                        </button>

                        @if(auth()->user()->canAccessDoctorTab())
                            <button @click="activeTab = 'doctor'"
                                    :class="activeTab === 'doctor' ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300' : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm
                                    font-medium transition-all whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 015 16V5a2 2 0 012-2h10a2 2 0 012 2v11a4 4 0 01-.121 1.804M9 21h6"/>
                                </svg>
                                پروفایل پزشک
                            </button>
                        @endif

                    </nav>
                </div>
            </div>
        </div>
        <div class="p-6 sm:p-8 bg-white dark:bg-gray-800">

            {{-- اطلاعات کاربری --}}
            <div x-show="activeTab === 'profile'" x-cloak>
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    @livewire('profile.update-profile-information-form')
                @endif
            </div>

            {{-- امنیت --}}
            <div x-show="activeTab === 'security'" x-cloak>
                <div class="space-y-10">
                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                        <div>@livewire('profile.update-password-form')</div>
                    @endif
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <x-section-border/>
                        <div>@livewire('profile.two-factor-authentication-form')</div>
                    @endif
                </div>
            </div>

            {{-- نشست‌ها --}}
            <div x-show="activeTab === 'sessions'" x-cloak>
                <div class="space-y-10">
                    <div>@livewire('profile.logout-other-browser-sessions-form')</div>
                    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                        <x-section-border/>
                        <div>@livewire('profile.delete-user-form')</div>
                    @endif
                </div>
            </div>

            @if(auth()->user()->canAccessDoctorTab())
                <template x-if="activeTab === 'doctor'">
                    <div class="space-y-6">

                        {{-- Success message --}}
                        @if(session('success'))
                            <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700
                            dark:bg-green-900/20 dark:border-green-800 dark:text-green-300 px-4 py-3
                            rounded-xl text-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Visibility hint --}}
                        <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border
                        border-blue-100 dark:border-blue-800 rounded-xl text-xs text-blue-700 dark:text-blue-300">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            هر بخشی که چک‌باکس «نمایش عمومی» آن فعال باشد، در صفحه پروفایل عمومی شما نمایش داده می‌شود.
                        </div>

                        <form method="POST"
                              action="{{ route('user.doctor-profile.about.update') }}"
                              class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl overflow-hidden border
                              border-gray-200 dark:border-gray-700"
                              id="doctor-about-form"
                        >
                            @csrf

                            <div class="flex items-center justify-between px-5 py-4 bg-white dark:bg-gray-800
                            border-b border-gray-200 dark:border-gray-700">
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm">
                                    اطلاعات پزشک
                                </h3>
                            </div>

                            <div class="p-5 space-y-6">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs mb-1">شماره نظام پزشکی</label>
                                        <input type="text" name="medical_system_number"
                                               value="{{ old('medical_system_number', $profile->medical_system_number) }}"
                                               class="w-full rounded-xl px-3 py-2
bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-700
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
transition"                                        >
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs mb-1">تخصص</label>
                                        <input type="text" name="specialty"
                                               value="{{ old('specialty', $profile->specialty) }}"
                                               class="w-full rounded-xl px-3 py-2
bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-700
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
transition"                                        >
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs mb-1">نام کلینیک</label>
                                        <input type="text" name="clinic_name"
                                               value="{{ old('clinic_name', $profile->clinic_name) }}"
                                               class="w-full rounded-xl px-3 py-2
bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-700
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
transition"                                        >
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs mb-1">تحصیلات</label>
                                        <input type="text" name="education"
                                               value="{{ old('education', $profile->education) }}"
                                               class="w-full rounded-xl px-3 py-2
bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-700
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
transition"                                        >
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">درباره پزشک</label>
                                    <textarea name="about_me" rows="4"
                                              class="w-full rounded-xl px-3 py-2
bg-white dark:bg-gray-800
border border-gray-200 dark:border-gray-700
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
transition"                                    >
                                        {{ old('about_me', $profile->about_me) }}
                                    </textarea>

                                    <label class="flex items-center gap-2 mt-2">

                                        <input type="checkbox"
                                               {{ $profile->isVisible('about_me') ? 'checked' : '' }}
                                               onchange="toggleVisibility('about_me', this)">
                                        <span class="text-xs">نمایش عمومی</span>
                                    </label>
                                </div>

                                <button type="submit"
                                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                        text-white text-sm rounded-xl">
                                    ذخیره اطلاعات
                                </button>

                            </div>
                        </form>

                        @php
                            $insurances = $profile->insurances;
                            if (is_string($insurances)) $insurances = json_decode($insurances, true);
                            if (!is_array($insurances)) $insurances = [];
                        @endphp

                        <form method="POST"
                              action="{{ route('user.doctor-profile.update.insurance') }}"
                              enctype="multipart/form-data"
                              class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl overflow-hidden border
                              border-gray-200 dark:border-gray-700"
                              x-data="insuranceSelector(@js($insurances))">
                            @csrf

                            {{-- Section header --}}
                            <div class="flex items-center justify-between px-5 py-4 bg-white dark:bg-gray-800
                            border-b border-gray-200 dark:border-gray-700">
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    بیمه‌های طرف قرارداد
                                </h3>
                                <label class="flex items-center gap-2 cursor-pointer select-none group">
                                    <input type="checkbox"
                                           {{ $profile->isVisible('insurances') ? 'checked' : '' }}
                                           onchange="toggleVisibility('insurances', this)">
                                    <span class="text-xs text-gray-500 dark:text-gray-400
                                    group-hover:text-gray-700 dark:group-hover:text-gray-200 transition">
                                        نمایش عمومی
                                    </span>
                                </label>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="flex flex-wrap gap-3 min-h-[2rem]">
                                    <template x-for="(item, index) in selected" :key="index">
                                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-2xl bg-white
                                        dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="w-9 h-9 rounded-lg overflow-hidden border border-gray-200
                                            dark:border-gray-700 bg-gray-100 dark:bg-gray-900 shrink-0 flex
                                            items-center justify-center">
                                                <img x-show="item.preview || item.logo"
                                                     :src="item.preview ? item.preview : `/storage/${item.logo}`"
                                                     class="w-full h-full object-cover">
                                                <span x-show="!item.preview && !item.logo"
                                                      x-text="item.name.charAt(0)"
                                                      class="text-xs font-bold text-gray-500"></span>
                                            </div>
                                            <div>
                                                <p x-text="item.name" class="text-sm font-medium text-gray-800
                                                dark:text-white"></p>
                                                <p class="text-[10px] text-gray-400"
                                                   x-text="item.preview ? 'لوگوی جدید' : (item.logo ? 'ذخیره شده' : 'بدون لوگو')"></p>
                                            </div>
                                            <button type="button" @click="remove(index)"
                                                    class="w-6 h-6 rounded-full bg-red-100 text-red-500
                                                    hover:bg-red-200 transition flex items-center justify-center
                                                    text-xs shrink-0">
                                                ✕
                                            </button>
                                        </div>
                                    </template>
                                    <p x-show="selected.length === 0" class="text-sm text-gray-400
                                    dark:text-gray-500">هنوز بیمه‌ای اضافه نشده.</p>
                                </div>
                                <div class="grid sm:grid-cols-3 gap-3 p-4 bg-white dark:bg-gray-800
                                rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">نام بیمه</label>
                                        <input type="text" x-model="newItem" placeholder="مثال: بیمه ایران"
                                               class="w-full rounded-xl px-3 py-2 border border-gray-200
                                               dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">لوگو </label>
                                        <input type="file" x-ref="logo" accept="image/*"
                                               class="w-full rounded-xl px-3 py-2 border border-gray-200
                                               dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="addNew()"
                                                class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                                                text-white text-sm rounded-xl transition">
                                            افزودن بیمه
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="insurances" :value="selected.length ? JSON.stringify(selected) : ''">

                                <button type="submit"
                                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white
                                        text-sm rounded-xl transition">
                                    ذخیره بیمه‌ها
                                </button>
                            </div>
                        </form>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- PHOTO GALLERY --}}
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl overflow-hidden border
                            border-gray-200 dark:border-gray-700">
                                {{-- Section header --}}
                                <div class="flex items-center justify-between px-5 py-4 bg-white
                                dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center
                                    gap-2 text-sm">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        گالری تصاویر
                                    </h3>
                                    {{-- Visibility saves via the about form on page reload --}}
                                    <form id="gallery-vis-form" method="POST"
                                          action="{{ route('user.doctor-profile.about.update') }}"
                                          class="hidden">
                                        @csrf
                                        <input type="hidden" name="medical_system_number" value="{{ $profile->medical_system_number }}">
                                        <input type="hidden" name="visibility_about"      value="{{ $profile->isVisible('about_me') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_insurances" value="{{ $profile->isVisible('insurances') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_gallery"    id="gallery-vis-input" value="{{ $profile->isVisible('gallery') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_video"      value="{{ $profile->isVisible('video') ? '1' : '' }}">
                                    </form>
                                    <label class="flex items-center gap-2 cursor-pointer select-none group">
                                        <input type="checkbox"
                                               {{ $profile->isVisible('gallery') ? 'checked' : '' }}
                                               onchange="toggleVisibility('gallery', this)">
                                        <span class="text-xs text-gray-500 dark:text-gray-400
                                        group-hover:text-gray-700 transition">نمایش عمومی</span>
                                    </label>
                                </div>

                                <div class="p-5 space-y-3">
                                    {{-- Existing photos --}}
                                    @if($photos->count())
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach($photos as $photo)
                                                <div class="relative group rounded-xl overflow-hidden border
                                                border-gray-200 dark:border-gray-600 aspect-square">
                                                    <img src="{{ asset('storage/'.$photo->file_path) }}"
                                                         class="w-full h-full object-cover">
                                                    <form method="POST"
                                                          action="{{ route('user.doctor-profile.media.delete', $photo->id) }}"
                                                          class="absolute inset-0 flex items-center justify-center
                                                          bg-black/50 opacity-0 group-hover:opacity-100 transition">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="bg-red-500 hover:bg-red-600
                                                                text-white text-xs px-2.5 py-1 rounded-lg">
                                                            حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Upload area --}}
                                    <form method="POST" action="{{ route('user.doctor-profile.photo-upload') }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        @error('photos')<p class="text-red-500 text-xs mb-1">{{ $message }}</p>@enderror
                                        <input id="photos" type="file" name="photos[]" multiple accept="image/*"
                                               class="hidden"
                                               onchange="this.form.submit()">
                                        <label for="photos"
                                               class="flex flex-col items-center justify-center w-full h-28 rounded-xl
                                               border-2 border-dashed border-gray-300 dark:border-gray-600
                                               cursor-pointer hover:border-indigo-400 transition-colors bg-white
                                               dark:bg-gray-900/30">
                                            <svg class="w-7 h-7 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            <span class="text-sm text-gray-400">افزودن تصویر</span>
                                        </label>
                                    </form>
                                    <p class="text-xs text-gray-400 text-center">حداکثر ۱۲ تصویر — هر تصویر تا ۱۰ مگابایت
                                    </p>
                                </div>
                            </div>

                            {{-- VIDEO GALLERY --}}
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl overflow-hidden border
                            border-gray-200 dark:border-gray-700">
                                {{-- Section header --}}
                                <div class="flex items-center justify-between px-5 py-4 bg-white
                                dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center
                                    gap-2 text-sm">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9A2.25 2.25 0 0013.5 5.25h-9A2.25 2.25 0 002.25 7.5v9A2.25 2.25 0 004.5 18.75z"/>
                                        </svg>
                                        گالری تصاویر
                                    </h3>
                                    <form id="video-vis-form" method="POST" action="{{ route('user.doctor-profile.about.update') }}" class="hidden">
                                        @csrf
                                        <input type="hidden" name="medical_system_number" value="{{ $profile->medical_system_number }}">
                                        <input type="hidden" name="visibility_about"      value="{{ $profile->isVisible('about') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_insurances" value="{{ $profile->isVisible('insurances') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_gallery"    value="{{ $profile->isVisible('gallery') ? '1' : '' }}">
                                        <input type="hidden" name="visibility_video"      id="video-vis-input" value="{{ $profile->isVisible('video') ? '1' : '' }}">
                                    </form>
                                    <label class="flex items-center gap-2 cursor-pointer select-none group">
                                        <input type="checkbox"
                                               {{ $profile->isVisible('video') ? 'checked' : '' }}
                                               onchange="toggleVisibility('video', this)">
                                        <span class="text-xs text-gray-500 dark:text-gray-400
                                        group-hover:text-gray-700 transition">نمایش عمومی</span>
                                    </label>
                                </div>

                                <div class="p-5 space-y-3">
                                    {{-- Existing videos --}}
                                    @if($videos->count())
                                        <div class="space-y-2">
                                            @foreach($videos as $video)
                                                <div class="rounded-xl overflow-hidden border border-gray-200
                                                dark:border-gray-600 p-2">
                                                    <video controls class="w-full rounded-lg aspect-video">
                                                        <source src="{{ asset('storage/'.$video->file_path) }}">
                                                    </video>
                                                    <form method="POST"
                                                          action="{{ route('user.doctor-profile.media.delete', $video->id) }}"
                                                          class="mt-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="w-full bg-red-500 hover:bg-red-600
                                                                text-white text-xs py-1.5 rounded-lg transition">
                                                            حذف ویدیو
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Upload area --}}
                                    <form method="POST" action="{{ route('user.doctor-profile.video-upload') }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        @error('videos')<p class="text-red-500 text-xs mb-1">{{ $message }}</p>@enderror
                                        <input id="videos" type="file" name="videos[]" multiple accept="video/*"
                                               class="hidden"
                                               onchange="this.form.submit()">
                                        <label for="videos"
                                               class="flex flex-col items-center justify-center w-full h-28 rounded-xl
                                               border-2 border-dashed border-gray-300 dark:border-gray-600
                                               cursor-pointer hover:border-indigo-400 transition-colors bg-white
                                               dark:bg-gray-900/30">
                                            <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="1.3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9A2.25 2.25 0 0013.5 5.25h-9A2.25 2.25 0 002.25 7.5v9A2.25 2.25 0 004.5 18.75z"/>
                                            </svg>
                                            <span class="text-sm text-gray-400">افزودن ویدیو</span>
                                            <span class="text-xs text-gray-400 mt-0.5">Max 20MB</span>
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            @endif

        </div>
    </div>

    @if(auth()->user()->canAccessDoctorTab())
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('insuranceSelector', (initialInsurances = []) => ({
                    selected: initialInsurances,
                    newItem: '',

                    addNew() {
                        if (this.newItem.trim() === '') return;

                        const fileInput = this.$refs.logo;
                        const file = fileInput.files[0];

                        const newInsurance = {
                            name: this.newItem,
                            logo: null,
                            preview: null
                        };

                        this.selected.push(newInsurance);
                        const index = this.selected.length - 1;

                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                // This embeds the file data safely into the array for form submission
                                this.selected[index].preview = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }

                        this.newItem = '';
                        fileInput.value = '';
                    },

                    remove(index) {
                        this.selected.splice(index, 1);
                    }
                }));
            });
            function insuranceSelector(selectedInsurances = []) {
                return {
                    selected: selectedInsurances ?? [],
                    newItem: '',
                    addNew() {
                        const name = this.newItem.trim();
                        const file = this.$refs.logo.files[0];
                        if (!name) { alert('نام بیمه را وارد کنید'); return; }
                        if (this.selected.some(i => i.name === name)) { alert('این بیمه قبلاً اضافه شده'); return; }

                        const index = this.selected.length;
                        const preview = file ? URL.createObjectURL(file) : null;

                        if (file) {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.name = `logos[${index}]`;
                            input.files = dt.files;
                            input.hidden = true;
                            this.$root.appendChild(input);
                        }

                        this.selected.push({ name, logo: null, preview, file_index: file ? index : null });
                        this.newItem = '';
                        this.$refs.logo.value = '';
                    },
                    remove(index) {
                        if (this.selected[index]?.preview) URL.revokeObjectURL(this.selected[index].preview);
                        this.selected.splice(index, 1);
                    }
                };
            }
            function submitDoctorVisibilityForm() {
                document.getElementById('doctor-about-form').submit();
            }
            function toggleVisibility(key, el) {
                fetch("{{ route('user.doctor-profile.visibility.toggle') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        key: key,
                        value: el.checked
                    }),
                })
                    .then(res => res.json())
                    .then(data => {
                        // optional: UI feedback
                        console.log(data);
                    })
                    .catch(err => {
                        console.error(err);
                        el.checked = !el.checked; // revert if failed
                    });
            }
        </script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

@endsection

