<div x-data="{
        isOpen: @entangle('isOpen'),
        selectedProvince: @entangle('province'),
        selectedCity: @entangle('city'),
        allData: @js($allData)
     }"
     @keydown.escape.window="isOpen = false">

    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-md overflow-y-auto font-sans">

        <div @click.outside="isOpen = false"
             class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden relative my-auto">

            {{-- Close Button --}}
            <button type="button" @click="$wire.closeModal()"
                    class="absolute left-4 top-4 w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition z-20"
                    title="بستن">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center gap-3.5">
                    <div class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600/15 border border-indigo-500/30 text-indigo-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-white">پروفایل و احراز هویت تکنسین</h3>
                        <p class="text-xs text-slate-300 mt-0.5">تکمیل و به‌روزرسانی اطلاعات هویتی و مدارک تعریف‌شده در سیستم</p>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if($errorMessage || $successMessage)
                <div class="px-6 pt-5">
                    @if($errorMessage)
                        <div class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-bold flex items-center gap-2.5">
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif
                    @if($successMessage)
                        <div class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2.5">
                            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $successMessage }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Form Body --}}
            <form wire:submit="saveProfile" class="px-6 py-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">نام <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model="firstName"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white font-sans focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @error('firstName') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">نام خانوادگی <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model="lastName"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white font-sans focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @error('lastName') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if($profileFields->isNotEmpty())
                    <div class="pt-3 border-t border-slate-800/80">
                        <h4 class="text-xs font-bold text-indigo-300 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            اطلاعات تکمیلی و احراز هویت
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach($profileFields as $field)
                                @include('fieldservice::livewire.technician.partials.custom-field-input', [
                                    'field'         => $field,
                                    'customFiles'   => $customFiles,
                                    'existingFiles' => $existingFiles,
                                    'idPrefix'      => 'prof',
                                ])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 text-center">
                        در حال حاضر هیچ فیلد اختصاصی با گزینه «احراز هویت و دریافت در پروفایل» برای نقش تکنسین تعریف نشده است.
                    </div>
                @endif

                {{-- Technical Categories & Skills --}}
                @if($categories->isNotEmpty())
                    <div class="pt-3 border-t border-slate-800/80">
                        <div class="flex items-center justify-between mb-2.5">
                            <h4 class="text-xs font-bold text-indigo-300 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                تخصص‌ها و حوزه‌های کاری
                            </h4>
                            <span class="text-[11px] text-slate-300">رسته‌های قابل دریافت در رادار</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach($categories as $cat)
                                @php
                                    $isSelected = in_array($cat->id, $selectedCategories);
                                @endphp
                                <label class="relative flex items-center gap-2.5 p-3 rounded-2xl cursor-pointer border transition-all {{ $isSelected ? 'bg-indigo-950/40 border-indigo-500/60 shadow-sm shadow-indigo-600/20 text-white' : 'bg-slate-950/70 border-slate-800 text-slate-300 hover:border-slate-700 hover:bg-slate-950' }}">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat->id }}" class="sr-only">
                                    <div class="w-5 h-5 rounded-lg flex items-center justify-center border shrink-0 transition-colors {{ $isSelected ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-700 bg-slate-900 text-transparent' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-base shrink-0">{{ $cat->icon }}</span>
                                        <span class="text-xs font-bold truncate">{{ $cat->name }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-800/80">
                    <button type="button" @click="$wire.closeModal()"
                            class="h-11 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition">
                        بستن
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="h-11 px-6 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 text-white font-bold text-xs rounded-2xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="saveProfile">ذخیره تغییرات پروفایل</span>
                        <span wire:loading wire:target="saveProfile">در حال ذخیره...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
