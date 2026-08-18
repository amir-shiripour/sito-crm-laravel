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

                        @php
                            $jsonPath = resource_path('data/iran-provinces-cities.json');
                            $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
                            $allProvinces = array_keys($provincesData);
                        @endphp

                        <form method="POST"
                              action="{{ route('user.doctor-profile.about.update') }}"
                              class="bg-white dark:bg-gray-800/95 rounded-2xl overflow-hidden border border-gray-200/80 dark:border-gray-700/80 shadow-sm transition-all"
                              id="doctor-about-form"
                              @submit="addSpecialty(); addEducation();"
                              x-data="{
                                  province: '{{ old('province', $profile->province ?? '') }}',
                                  city: '{{ old('city', $profile->city ?? '') }}',
                                  provinces: @js($allProvinces),
                                  provincesData: @js($provincesData),
                                  cities: [],
                                  openProvince: false,
                                  openCity: false,
                                  searchProvince: '',
                                  searchCity: '',
                                  specialties: @js(
                                      old('specialty') !== null
                                          ? (is_array(old('specialty')) ? old('specialty') : (json_decode(old('specialty'), true) ?: (old('specialty') ? [old('specialty')] : [])))
                                          : ($profile->specialties_list ?? [])
                                  ),
                                  newSpecialty: '',
                                  educations: @js(
                                      old('education') !== null
                                          ? (is_array(old('education')) ? old('education') : (json_decode(old('education'), true) ?: (old('education') ? [old('education')] : [])))
                                          : ($profile->education_list ?? [])
                                  ),
                                  newEducation: '',
                                  addSpecialty() {
                                      const val = this.newSpecialty.trim();
                                      if (val && !this.specialties.includes(val)) {
                                          this.specialties.push(val);
                                      }
                                      this.newSpecialty = '';
                                  },
                                  removeSpecialty(index) {
                                      this.specialties.splice(index, 1);
                                  },
                                  addEducation() {
                                      const val = this.newEducation.trim();
                                      if (val && !this.educations.includes(val)) {
                                          this.educations.push(val);
                                      }
                                      this.newEducation = '';
                                  },
                                  removeEducation(index) {
                                      this.educations.splice(index, 1);
                                  },
                                  init() {
                                      if (this.province && this.provincesData[this.province]) {
                                          this.cities = this.provincesData[this.province];
                                      }
                                      this.$watch('province', (val) => {
                                          this.cities = (val && this.provincesData[val]) ? this.provincesData[val] : [];
                                          if (val && this.cities && !this.cities.includes(this.city)) {
                                              this.city = '';
                                          }
                                      });
                                  }
                              }"
                        >
                            @csrf

                            {{-- Card Header --}}
                            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-gray-50 to-indigo-50/30 dark:from-gray-800 dark:to-indigo-950/20 border-b border-gray-200/80 dark:border-gray-700/80">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                            اطلاعات ارائه‌دهنده / پزشک
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            مشخصات حرفه‌ای، تخصص، کلینیک و اطلاعات مکانی
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 space-y-8">
                                {{-- SECTION 1: Professional details --}}
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">مشخصات تخصصی و سوابق</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {{-- شماره نظام پزشکی --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                شماره نظام پزشکی
                                            </label>
                                            <div class="relative">
                                                <input type="text" name="medical_system_number"
                                                       value="{{ old('medical_system_number', $profile->medical_system_number) }}"
                                                       placeholder="مثال: ۱۲۳۴۵۶"
                                                       class="w-full rounded-xl px-3.5 py-2.5 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                            </div>
                                            @error('medical_system_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- سابقه کار --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                سابقه کار و تجربه
                                            </label>
                                            <div class="relative flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 focus-within:bg-white dark:focus-within:bg-gray-900 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition overflow-hidden">
                                                <input type="number" name="experience" min="0" max="80"
                                                       value="{{ old('experience', $profile->experience_years ?? $profile->experience) }}"
                                                       placeholder="مثال: ۱۰"
                                                       class="w-full bg-transparent px-3.5 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 border-0 focus:ring-0 focus:outline-none text-sm font-medium">
                                                <div class="px-3.5 py-2.5 bg-gray-100 dark:bg-gray-800/80 border-r border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-500 dark:text-gray-400 select-none shrink-0">
                                                    سال سابقه
                                                </div>
                                            </div>
                                            @error('experience') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- تخصص و حیطه فعالیت (Tag manager) --}}
                                        <div class="md:col-span-2">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    تخصص و حیطه فعالیت
                                                </label>
                                                <span class="text-[11px] font-normal text-gray-400">چند انتخابی / با فشردن Enter یا دکمه افزودن اضافه کنید</span>
                                            </div>

                                            {{-- Hidden input for form submission --}}
                                            <input type="hidden" name="specialty" :value="JSON.stringify(specialties)">

                                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 p-2.5 transition focus-within:bg-white dark:focus-within:bg-gray-900 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                                {{-- Tags list --}}
                                                <div class="flex flex-wrap items-center gap-2 mb-2" x-show="specialties.length > 0">
                                                    <template x-for="(item, index) in specialties" :key="index">
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60 shadow-xs group transition-all">
                                                            <span x-text="item"></span>
                                                            <button type="button" @click="removeSpecialty(index)" class="text-indigo-400 hover:text-red-500 dark:hover:text-red-400 p-0.5 rounded transition-colors" title="حذف">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </div>

                                                {{-- Add input --}}
                                                <div class="flex items-center gap-2">
                                                    <input type="text"
                                                           x-model="newSpecialty"
                                                           @keydown.enter.prevent="addSpecialty()"
                                                           @keydown.comma.prevent="addSpecialty()"
                                                           placeholder="عنوان تخصص را بنویسید (مثال: جراحی فک و صورت، ایمپلنتولوژیست)..."
                                                           class="flex-1 bg-transparent border-0 px-2 py-1 text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 focus:outline-none">
                                                    <button type="button"
                                                            @click="addSpecialty()"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 hover:bg-indigo-200 text-indigo-700 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/70 dark:text-indigo-300 text-xs font-semibold transition-colors shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        <span>افزودن</span>
                                                    </button>
                                                </div>
                                            </div>
                                            @error('specialty') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- تحصیلات و مدارک علمی (Tag manager) --}}
                                        <div class="md:col-span-2">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    تحصیلات و مدارک علمی
                                                </label>
                                                <span class="text-[11px] font-normal text-gray-400">چند انتخابی / با فشردن Enter یا دکمه افزودن اضافه کنید</span>
                                            </div>

                                            {{-- Hidden input for form submission --}}
                                            <input type="hidden" name="education" :value="JSON.stringify(educations)">

                                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 p-2.5 transition focus-within:bg-white dark:focus-within:bg-gray-900 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500">
                                                {{-- Tags list --}}
                                                <div class="flex flex-wrap items-center gap-2 mb-2" x-show="educations.length > 0">
                                                    <template x-for="(item, index) in educations" :key="index">
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 shadow-xs group transition-all">
                                                            <span x-text="item"></span>
                                                            <button type="button" @click="removeEducation(index)" class="text-emerald-400 hover:text-red-500 dark:hover:text-red-400 p-0.5 rounded transition-colors" title="حذف">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </div>

                                                {{-- Add input --}}
                                                <div class="flex items-center gap-2">
                                                    <input type="text"
                                                           x-model="newEducation"
                                                           @keydown.enter.prevent="addEducation()"
                                                           @keydown.comma.prevent="addEducation()"
                                                           placeholder="مدرک تحصیلی یا گواهی علمی را بنویسید (مثال: بورد تخصصی، فلوشیپ لیزر از آلمان)..."
                                                           class="flex-1 bg-transparent border-0 px-2 py-1 text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 focus:outline-none">
                                                    <button type="button"
                                                            @click="addEducation()"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 dark:bg-emerald-900/40 dark:hover:bg-emerald-900/70 dark:text-emerald-300 text-xs font-semibold transition-colors shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        <span>افزودن</span>
                                                    </button>
                                                </div>
                                            </div>
                                            @error('education') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 2: Clinic & Location details --}}
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">اطلاعات کلینیک / مطب و موقعیت مکانی</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {{-- نام کلینیک --}}
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                نام کلینیک / مطب / مرکز درمانی
                                            </label>
                                            <div class="relative">
                                                <input type="text" name="clinic_name"
                                                       value="{{ old('clinic_name', $profile->clinic_name) }}"
                                                       placeholder="مثال: کلینیک تخصصی مهر"
                                                       class="w-full rounded-xl px-3.5 py-2.5 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                            </div>
                                            @error('clinic_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Province Selector --}}
                                        <div class="relative" @click.away="openProvince = false">
                                            <input type="hidden" name="province" :value="province">
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                استان
                                            </label>
                                            <div @click="openProvince = !openProvince"
                                                 class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2.5 text-sm text-gray-900 dark:text-gray-100 cursor-pointer flex justify-between items-center select-none transition focus:bg-white dark:focus:bg-gray-900"
                                                 :class="{'ring-2 ring-indigo-500/20 border-indigo-500 bg-white dark:bg-gray-900': openProvince}">
                                                <span x-text="province || 'انتخاب استان...'" :class="{'text-gray-400 dark:text-gray-500': !province}"></span>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500': openProvince}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                            <div x-show="openProvince" x-transition class="absolute z-50 w-full mt-1.5 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                                <div class="px-3 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                                    <input type="text" x-model="searchProvince" placeholder="جستجوی استان..." class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                </div>
                                                <div class="pt-1">
                                                    <template x-for="p in provinces.filter(item => item.toLowerCase().includes(searchProvince.toLowerCase()))" :key="p">
                                                        <div @click="province = p; openProvince = false; searchProvince = ''"
                                                             class="px-4 py-2 cursor-pointer transition-colors flex items-center justify-between text-xs sm:text-sm group"
                                                             :class="province === p ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                                            <span x-text="p"></span>
                                                            <svg x-show="province === p" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            @error('province') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- City Selector --}}
                                        <div class="relative" @click.away="openCity = false">
                                            <input type="hidden" name="city" :value="city">
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                شهر
                                            </label>
                                            <div @click="province ? openCity = !openCity : null"
                                                 class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3.5 py-2.5 text-sm flex justify-between items-center select-none transition"
                                                 :class="{
                                                     'bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-gray-100 cursor-pointer': province,
                                                     'ring-2 ring-indigo-500/20 border-indigo-500 bg-white dark:bg-gray-900': openCity && province,
                                                     'bg-gray-100 dark:bg-gray-900/30 text-gray-400 dark:text-gray-600 opacity-70 cursor-not-allowed': !province
                                                 }">
                                                <span x-text="province ? (city || 'انتخاب شهر...') : 'ابتدا استان را انتخاب کنید'"
                                                      :class="{'text-gray-400 dark:text-gray-500': !city}"></span>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500': openCity && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                            <div x-show="openCity && province" x-transition class="absolute z-50 w-full mt-1.5 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                                <div class="px-3 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                                    <input type="text" x-model="searchCity" placeholder="جستجوی شهر..." class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                </div>
                                                <div class="pt-1">
                                                    <template x-for="c in cities.filter(item => item.toLowerCase().includes(searchCity.toLowerCase()))" :key="c">
                                                        <div @click="city = c; openCity = false; searchCity = ''"
                                                             class="px-4 py-2 cursor-pointer transition-colors flex items-center justify-between text-xs sm:text-sm group"
                                                             :class="city === c ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                                            <span x-text="c"></span>
                                                            <svg x-show="city === c" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <div x-show="cities.length === 0" class="px-4 py-3 text-xs text-gray-500 text-center">
                                                        شهری یافت نشد
                                                    </div>
                                                </div>
                                            </div>
                                            @error('city') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- نشانی کامل --}}
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                                نشانی دقیق کلینیک / مطب
                                            </label>
                                            <div class="relative">
                                                <input type="text" name="clinic_address"
                                                       value="{{ old('clinic_address', $profile->clinic_address) }}"
                                                       placeholder="مثال: خیابان شریعتی، بالاتر از میرداماد، پلاک ۱۰۰، طبقه ۲"
                                                       class="w-full rounded-xl px-3.5 py-2.5 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                            </div>
                                            @error('clinic_address') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 3: About / Bio --}}
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">درباره پزشک / بیوگرافی</h4>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                            توضیحات و بیوگرافی معرفی
                                        </label>
                                        <textarea name="about_me" rows="4"
                                                  placeholder="توضیحاتی در مورد سوابق، حوزه‌های درمانی، دوره‌های تخصصی و خدمات..."
                                                  class="w-full rounded-xl px-3.5 py-2.5 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm leading-relaxed">{{ old('about_me', $profile->about_me) }}</textarea>
                                        @error('about_me') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                                        <div class="mt-3 flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/60 dark:border-gray-700/60">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">نمایش بیوگرافی در صفحه عمومی پروفایل</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                                <input type="checkbox" name="visibility_about_me" value="1"
                                                       class="sr-only peer"
                                                       {{ $profile->isVisible('about_me') ? 'checked' : '' }}
                                                       onchange="toggleVisibility('about_me', this)">
                                                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit button --}}
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>ذخیره اطلاعات</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @php
                            $stats = $profile->stats;
                        @endphp

                        {{-- CARD: Doctor Stats & Trust Badges --}}
                        <form method="POST"
                              action="{{ route('user.doctor-profile.update.stats') }}"
                              class="bg-white dark:bg-gray-800/95 rounded-2xl overflow-hidden border border-gray-200/80 dark:border-gray-700/80 shadow-sm transition-all"
                              x-data="{
                                  mode: '{{ old('mode', $stats['mode'] ?? 'manual') }}',
                                  visStats: {{ $profile->isVisible('stats') ? 'true' : 'false' }},
                                  visRating: {{ $profile->isVisible('rating') ? 'true' : 'false' }},
                                  visSatisfaction: {{ $profile->isVisible('satisfaction') ? 'true' : 'false' }},
                                  visBookings: {{ $profile->isVisible('successful_bookings') ? 'true' : 'false' }},
                                  visEndorsements: {{ $profile->isVisible('endorsements') ? 'true' : 'false' }},
                              }">
                            @csrf

                            {{-- Card Header --}}
                            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-gray-50 to-amber-50/40 dark:from-gray-800 dark:to-amber-950/20 border-b border-gray-200/80 dark:border-gray-700/80">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                            آمار، امتیاز و اعتبارسنجی ارائه‌دهنده
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            امتیاز نظرات، درصد رضایت، نوبت‌های موفق و تاییدیه همکاران در پروفایل عمومی
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">نمایش کل بخش:</span>
                                    <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                        <input type="checkbox" name="visibility_stats" value="1"
                                               class="sr-only peer"
                                               x-model="visStats">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>
                            </div>

                            <div class="p-6 space-y-6">
                                {{-- MODE SELECTOR --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        منبع و روش محاسبه آمار:
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        {{-- Manual Mode --}}
                                        <label class="relative flex items-center p-3.5 rounded-xl border cursor-pointer transition select-none"
                                               :class="mode === 'manual' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-200 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/40 text-gray-700 dark:text-gray-300'">
                                            <input type="radio" name="mode" value="manual" x-model="mode" class="sr-only">
                                            <div class="flex items-center gap-3">
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                                     :class="mode === 'manual' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-400'">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="mode === 'manual'"></div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-xs">تنظیم دستی (Manual)</div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">وارد کردن مقادیر و آمار به صورت دلخواه توسط پزشک</div>
                                                </div>
                                            </div>
                                        </label>

                                        {{-- Auto Mode --}}
                                        <label class="relative flex items-center p-3.5 rounded-xl border cursor-pointer transition select-none"
                                               :class="mode === 'auto' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 ring-1 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/40 text-gray-700 dark:text-gray-300'">
                                            <input type="radio" name="mode" value="auto" x-model="mode" class="sr-only">
                                            <div class="flex items-center gap-3">
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                                     :class="mode === 'auto' ? 'border-emerald-600 bg-emerald-600' : 'border-gray-400'">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="mode === 'auto'"></div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-xs">محاسبه خودکار و هوشمند (Auto)</div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">محاسبه خودکار از نظرات، امتیازات و نوبت‌های ثبت‌شده</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div x-show="mode === 'auto'" class="mt-2.5 p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-800/40 text-blue-800 dark:text-blue-300 text-xs flex items-start gap-2">
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>در حالت خودکار، مقادیر مستقیماً بر اساس سوابق نوبت‌دهی و بازخوردهای ثبت‌شده محاسبه خواهند شد و مقادیر زیر به عنوان پشتیبان (Fallback) در نظر گرفته می‌شوند.</span>
                                    </div>
                                </div>

                                {{-- 4 STAT ITEMS --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                    {{-- 1. Rating & Reviews --}}
                                    <div class="p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-amber-500">⭐</span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">امتیاز و تعداد نظرات</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                                <input type="checkbox" name="visibility_rating" value="1"
                                                       class="sr-only peer"
                                                       x-model="visRating">
                                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">امتیاز (از ۵)</label>
                                                <input type="number" name="rating" step="0.1" min="1" max="5"
                                                       value="{{ old('rating', $stats['rating'] ?? 4.8) }}"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">تعداد نظرات</label>
                                                <input type="number" name="reviews_count" min="0"
                                                       value="{{ old('reviews_count', $stats['reviews_count'] ?? 0) }}"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. Satisfaction percentage --}}
                                    <div class="p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-emerald-500">👍</span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">درصد رضایت / پیشنهاد کاربران</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                                <input type="checkbox" name="visibility_satisfaction" value="1"
                                                       class="sr-only peer"
                                                       x-model="visSatisfaction">
                                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">درصد پیشنهاد کاربران (۰ تا ۱۰۰)</label>
                                            <div class="relative flex items-center">
                                                <input type="number" name="satisfaction_rate" min="0" max="100"
                                                       value="{{ old('satisfaction_rate', $stats['satisfaction_rate'] ?? 95) }}"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                                <span class="absolute left-3 text-xs text-gray-400 font-bold">٪</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Successful Bookings --}}
                                    <div class="p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sky-500 font-bold">🔵✓</span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تعداد نوبت‌های موفق در سامانه</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                                <input type="checkbox" name="visibility_successful_bookings" value="1"
                                                       class="sr-only peer"
                                                       x-model="visBookings">
                                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">تعداد نوبت‌های موفق</label>
                                                <input type="number" name="successful_bookings_count" min="0"
                                                       value="{{ old('successful_bookings_count', $stats['successful_bookings_count'] ?? 0) }}"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">نام سامانه در متن</label>
                                                <input type="text" name="platform_name" placeholder="پیش‌فرض: {{ config('app.name') ?: 'دکترتو' }}"
                                                       value="{{ old('platform_name', $stats['platform_name'] ?? '') }}"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 4. Doctor endorsements --}}
                                    <div class="p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-amber-500">🏅</span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تاییدیه پزشکان و همکاران</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer" dir="ltr">
                                                <input type="checkbox" name="visibility_endorsements" value="1"
                                                       class="sr-only peer"
                                                       x-model="visEndorsements">
                                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">تعداد پزشکان تایید‌کننده</label>
                                                <input type="number" name="endorsements_count" min="0"
                                                       value="{{ old('endorsements_count', $stats['endorsements_count'] ?? 0) }}"
                                                       placeholder="مثال: ۱۰"
                                                       class="w-full rounded-lg px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit button --}}
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>ذخیره تنظیمات آمار و اعتبارسنجی</span>
                                    </button>
                                </div>
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

