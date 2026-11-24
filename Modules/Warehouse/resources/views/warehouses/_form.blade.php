@csrf

@php
    $suggestedColors = [
        '#4f46e5' => 'نیلی سازمانی',
        '#0d9488' => 'یشمی زمردی',
        '#2563eb' => 'آبی اقیانوسی',
        '#059669' => 'سبز',
        '#d97706' => 'کهربایی',
        '#dc2626' => 'قرمز اناری',
        '#7c3aed' => 'بنفش سلطنتی',
        '#4b5563' => 'طوسی تیره',
    ];

    $currentColor = old('color', $warehouse->color ?? ($warehouse->type === 'inventory' ? '#4f46e5' : '#0d9488'));
@endphp

<div x-data="{
    selectedColor: '{{ $currentColor }}',
    isActive: {{ old('is_active', $warehouse->is_active ?? true) ? 'true' : 'false' }},
    setColor(c) {
        this.selectedColor = c;
    }
}" class="space-y-6">

    @if($warehouse->exists && $warehouse->is_main)
        <input type="hidden" name="type" value="{{ $warehouse->type }}">
    @elseif($warehouse->exists)
        <input type="hidden" name="type" value="{{ $warehouse->type }}">
    @else
        <input type="hidden" name="type" value="{{ $type ?? 'inventory' }}">
    @endif

    {{-- Main Form Card (Fully Harmonized with CRM Dark & Light Theme) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-8 space-y-6 shadow-xs">

        {{-- Section Title --}}
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-200 dark:border-gray-700/80">
            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" :style="'background-color: ' + selectedColor"></span>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                مشخصات و اطلاعات انبار
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                    نام انبار <span class="text-rose-500 font-bold">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $warehouse->name) }}"
                       placeholder="مثال: انبارک غرب تهران یا سوله قطعات"
                       required
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- Code --}}
            <div>
                <label for="code" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                    کد انبار
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       value="{{ old('code', $warehouse->code) }}"
                       placeholder="مثال: WH-INV-02"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Parent Warehouse (only for sub-warehouses) --}}
            @if(!$warehouse->exists || !$warehouse->is_main)
                <div class="md:col-span-2">
                    <label for="parent_id" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                        انبار والد
                    </label>
                    <select name="parent_id"
                            id="parent_id"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">بدون انبار والد</option>
                        @foreach($parentWarehouses as $pWarehouse)
                            <option value="{{ $pWarehouse->id }}"
                                    class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                    {{ (string)old('parent_id', $warehouse->parent_id) === (string)$pWarehouse->id ? 'selected' : '' }}>
                                {{ $pWarehouse->name }} ({{ $pWarehouse->code }}) {{ $pWarehouse->is_main ? '— انبار مرکزی' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Advanced Multi-Select User Picker with Search (Zero Auto-load) --}}
            <div x-data="{
                searchQuery: '',
                searching: false,
                dropdownOpen: false,
                searchResults: [],
                searchTimeout: null,
                selectedUsers: {{ Js::from($currentManagers ?? []) }},

                isUserSelected(id) {
                    return this.selectedUsers.some(u => String(u.id) === String(id));
                },

                selectUser(user) {
                    if (!this.isUserSelected(user.id)) {
                        this.selectedUsers.push(user);
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.dropdownOpen = false;
                },

                removeUser(id) {
                    this.selectedUsers = this.selectedUsers.filter(u => String(u.id) !== String(id));
                },

                searchUsersDebounced() {
                    clearTimeout(this.searchTimeout);
                    const q = (this.searchQuery || '').trim();
                    if (!q || q.length === 0) {
                        this.searchResults = [];
                        this.searching = false;
                        return;
                    }
                    this.searching = true;
                    this.searchTimeout = setTimeout(async () => {
                        try {
                            const res = await fetch(`{{ route('admin.warehouse.users.search') }}?q=${encodeURIComponent(q)}`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.searchResults = data.results || [];
                            }
                        } catch (e) {
                            console.error('User search error:', e);
                            this.searchResults = [];
                        } finally {
                            this.searching = false;
                        }
                    }, 300);
                }
            }" class="md:col-span-2 space-y-2.5">

                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                    مسئول انبار / امین اموال
                </label>

                {{-- Hidden Inputs for Form Submission --}}
                <template x-for="u in selectedUsers" :key="u.id">
                    <input type="hidden" name="manager_ids[]" :value="u.id">
                </template>

                {{-- Selected Users Badges --}}
                <div x-show="selectedUsers.length > 0" class="flex flex-wrap items-center gap-2 pb-1" x-cloak>
                    <template x-for="u in selectedUsers" :key="u.id">
                        <div class="inline-flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-800 dark:text-gray-200 shadow-2xs transition-all">
                            {{-- Avatar or Initial --}}
                            <template x-if="u.avatar">
                                <img :src="u.avatar" :alt="u.name" class="w-6 h-6 rounded-full object-cover">
                            </template>
                            <template x-if="!u.avatar">
                                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[11px] font-black shrink-0"
                                      x-text="u.initial || (u.name ? u.name.charAt(0) : '؟')"></span>
                            </template>

                            <div class="flex flex-col text-right">
                                <span x-text="u.name" class="leading-tight"></span>
                                <span x-show="u.mobile || u.email" x-text="u.mobile || u.email" class="text-[10px] text-gray-400 font-sans font-normal dir-ltr leading-tight"></span>
                            </div>

                            <button type="button"
                                    @click="removeUser(u.id)"
                                    class="p-1 rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors mr-1"
                                    title="حذف مسئول">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Search Input with Dropdown (Zero Auto-load) --}}
                <div class="relative" @click.outside="dropdownOpen = false">
                    <div class="relative">
                        <input type="text"
                               x-model="searchQuery"
                               @focus="if (searchQuery.trim().length > 0) dropdownOpen = true"
                               @input="dropdownOpen = true; searchUsersDebounced()"
                               placeholder="نام، شماره موبایل یا ایمیل کاربر را برای افزودن جستجو کنید..."
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pr-10 pl-10 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans"
                               autocomplete="off">

                        {{-- Search Icon on Right --}}
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </div>

                        {{-- Loading Spinner on Left --}}
                        <div x-show="searching" class="absolute inset-y-0 left-0 pl-3.5 flex items-center" x-cloak>
                            <svg class="animate-spin h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Dropdown Search Results --}}
                    <div x-show="dropdownOpen && searchQuery.trim().length > 0"
                         x-transition
                         x-cloak
                         class="absolute right-0 left-0 top-full mt-1.5 z-50 max-h-60 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700/80">
                        <template x-for="u in searchResults" :key="u.id">
                            <div class="w-full px-4 py-2.5 text-xs flex items-center justify-between gap-3 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <template x-if="u.avatar">
                                        <img :src="u.avatar" :alt="u.name" class="w-7 h-7 rounded-full object-cover">
                                    </template>
                                    <template x-if="!u.avatar">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0"
                                             x-text="u.initial"></div>
                                    </template>
                                    <div class="min-w-0 text-right">
                                        <div class="font-bold text-gray-900 dark:text-white truncate" x-text="u.name"></div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-400 font-sans truncate"
                                             x-text="[u.email, u.mobile].filter(Boolean).join(' • ')"></div>
                                    </div>
                                </div>

                                <div>
                                    <template x-if="isUserSelected(u.id)">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/50">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            انتخاب شده
                                        </span>
                                    </template>
                                    <template x-if="!isUserSelected(u.id)">
                                        <button type="button"
                                                @click="selectUser(u)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/70 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 transition-colors">
                                            + افزودن
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div x-show="!searching && searchResults.length === 0" class="p-4 text-center text-xs text-gray-400 dark:text-gray-400">
                            هیچ کاربری با عبارت «<span x-text="searchQuery" class="font-bold text-gray-600 dark:text-gray-300"></span>» یافت نشد.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                    تلفن تماس
                </label>
                <input type="text"
                       name="phone"
                       id="phone"
                       value="{{ old('phone', $warehouse->phone) }}"
                       placeholder="مثال: 02188888888"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Location --}}
            <div>
                <label for="location" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                    نشانی و موقعیت فیزیکی
                </label>
                <input type="text"
                       name="location"
                       id="location"
                       value="{{ old('location', $warehouse->location) }}"
                       placeholder="مثال: طبقه همکف، سوله غربی، قفسه شماره ۳"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>
        </div>

        {{-- Color Selection --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700/80 space-y-3">
            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                رنگ سازمانی و شناسه بصری انبار
            </label>

            <div class="flex items-center gap-2 flex-wrap">
                @foreach($suggestedColors as $hex => $label)
                    <button type="button"
                            @click="setColor('{{ $hex }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition-all"
                            :class="selectedColor.toLowerCase() === '{{ strtolower($hex) }}'
                                ? 'border-2 border-indigo-600 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-950/70 dark:text-indigo-200 shadow-xs'
                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60'">
                        <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $hex }}"></span>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-3 pt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">یا انتخاب رنگ دلخواه:</span>
                <input type="color"
                       x-model="selectedColor"
                       class="w-10 h-10 rounded-xl cursor-pointer border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
                <input type="text"
                       name="color"
                       x-model="selectedColor"
                       placeholder="#4f46e5"
                       class="w-28 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs font-sans text-center dir-ltr focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>
        </div>

        {{-- Description --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700/80">
            <label for="description" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                توضیحات تکمیلی
            </label>
            <textarea name="description"
                      id="description"
                      rows="3"
                      placeholder="یادداشت‌های فنی، دسترسی و شرایط نگهداری اقلام..."
                      class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">{{ old('description', $warehouse->description) }}</textarea>
        </div>

        {{-- Active Status Toggle --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700/80 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-900 dark:text-white">وضعیت فعالیت انبار</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       x-model="isActive"
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
            </label>
        </div>
    </div>

    {{-- Bottom Action Buttons (Standard Panel Buttons) --}}
    <div class="flex items-center justify-between gap-3 pt-2">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
            انصراف
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $warehouse->exists ? 'ذخیره تغییرات' : 'ثبت و ایجاد انبار' }}
        </button>
    </div>
</div>
