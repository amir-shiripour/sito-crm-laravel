<div x-data="roleModalManager()"
     x-show="open"
     x-cloak
     @open-role-create.window="openCreate()"
     @open-role-edit.window="openEdit($event.detail)"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm transition-opacity" @click="close()"></div>

    {{-- Modal Dialog Panel --}}
    <div
        class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 flex flex-col max-h-[90vh] overflow-hidden z-10"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-3"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0">

        {{-- Modal Header --}}
        <div
            class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800 shrink-0 bg-linear-to-r from-indigo-50/50 to-purple-50/50 dark:from-gray-800/60 dark:to-gray-800/30">
            <div class="flex items-center gap-3.5">
                <span
                    class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-500/20"
                    :class="getColorClass(form.color).btn">
                    <template x-if="form.icon === 'crown'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M3 18h18M4 14l3-7 5 5 5-5 3 7H4z"/></svg>
                    </template>
                    <template x-if="form.icon === 'pencil'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </template>
                    <template x-if="form.icon === 'eye'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </template>
                    <template x-if="form.icon === 'code'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </template>
                    <template x-if="form.icon === 'palette'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21a4 4 0 01-4-4 4 4 0 014-4h2a2 2 0 002-2V9a2 2 0 012-2h1a5 5 0 015 5v1a7 7 0 01-7 7H7z"/></svg>
                    </template>
                    <template x-if="form.icon === 'bug'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM5 11l-3 1m19-1l3 1m-4 5l3 2M3 18l3-2m2-5h8m-8 4h8m-6 4h4a3 3 0 003-3v-4H8v4a3 3 0 003 3z"/></svg>
                    </template>
                    <template x-if="form.icon === 'shield'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </template>
                    <template x-if="form.icon === 'briefcase'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </template>
                    <template x-if="form.icon === 'user'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </template>
                    <template x-if="form.icon === 'star'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </template>
                </span>
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white"
                        x-text="isEditing ? 'ویرایش مشخصات و دسترسی‌های نقش «' + form.display_name + '»' : 'تعریف نقش جدید برای پروژه'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                       x-text="isEditing ? 'تغییر نام، آیکون، رنگ و ماتریس اختیارات این نقش در کل سامانه' : 'تنظیم مشخصات و انتخاب دقیق دسترسی‌های مجاز برای این نقش'"></p>
                </div>
            </div>

            <button type="button" @click="close()"
                    class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Scrollable Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            {{-- Error Message Alert --}}
            <div x-show="errorMsg" x-cloak
                 class="rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 p-4 text-sm text-rose-700 dark:text-rose-300 font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMsg"></span>
            </div>

            {{-- SECTION 1: Role Basic Information --}}
            <div
                class="bg-gray-50/70 dark:bg-gray-800/40 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 space-y-4">
                <h4 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    اطلاعات پایه نقش
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Display Name --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            عنوان نمایشی نقش (فارسی) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="form.display_name"
                               placeholder="مثلاً: سرپرست فنی / طراح UI"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                    </div>

                    {{-- Slug / Key (Only when creating) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            شناسه سیستمی (انگلیسی / Slug)
                            <template x-if="isEditing">
                                <span class="text-xs text-gray-400 font-normal">(غیرقابل تغییر)</span>
                            </template>
                        </label>
                        <input type="text" x-model="form.name" :disabled="isEditing"
                               placeholder="مثلاً: tech_lead / designer"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white disabled:opacity-60 disabled:bg-gray-100 dark:disabled:bg-gray-800/60 transition-all dir-ltr text-left">
                    </div>
                </div>

                {{-- Color & Icon Picker Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    {{-- Color Picker --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">رنگ شناسه و
                            نشان:</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-for="c in availableColors" :key="c.key">
                                <button type="button" @click="form.color = c.key"
                                        :title="c.label"
                                        :class="form.color === c.key ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' : 'opacity-70 hover:opacity-100'"
                                        class="w-7 h-7 rounded-full transition-all cursor-pointer shadow-xs flex items-center justify-center text-white text-[10px]"
                                        :style="`background-color: ${c.hex}`">
                                    <template x-if="form.color === c.key">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Icon Picker --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">آیکون
                            اختصاصی:</label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <template x-for="ic in availableIcons" :key="ic.key">
                                <button type="button" @click="form.icon = ic.key"
                                        :title="ic.label"
                                        :class="form.icon === ic.key ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 border border-gray-200 dark:border-gray-700'"
                                        class="w-8 h-8 rounded-xl transition-all cursor-pointer flex items-center justify-center">
                                    <template x-if="ic.key === 'crown'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3 18h18M4 14l3-7 5 5 5-5 3 7H4z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'pencil'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'eye'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'code'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'palette'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M7 21a4 4 0 01-4-4 4 4 0 014-4h2a2 2 0 002-2V9a2 2 0 012-2h1a5 5 0 015 5v1a7 7 0 01-7 7H7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'bug'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM5 11l-3 1m19-1l3 1m-4 5l3 2M3 18l3-2m2-5h8m-8 4h8m-6 4h4a3 3 0 003-3v-4H8v4a3 3 0 003 3z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'shield'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'briefcase'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'user'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="ic.key === 'star'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات و شرح
                        اختیارات نقش:</label>
                    <textarea x-model="form.description" rows="2"
                              placeholder="توضیح کوتاه در مورد مسئولیت‌ها و حوزه فعالیت این نقش..."
                              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all resize-none"></textarea>
                </div>
            </div>

            {{-- SECTION 2: Granular Permissions Matrix --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            ماتریس سطح دسترسی‌ها و اختیارات مجاز
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">برای این نقش مشخص کنید مجاز به انجام چه اقداماتی در محیط
                            پروژه است</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAllPermissions()"
                                class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 transition-all cursor-pointer">
                            انتخاب همه
                        </button>
                        <button type="button" @click="form.permissions = []"
                                class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-200 transition-all cursor-pointer">
                            لغو همه
                        </button>
                    </div>
                </div>

                {{-- Categories Accordion / Panels --}}
                <div class="space-y-4">
                    @foreach($availablePermissions as $catKey => $category)
                        <div
                            class="rounded-2xl border border-gray-200/80 dark:border-gray-700/60 overflow-hidden bg-white dark:bg-gray-800/60 shadow-xs">
                            {{-- Category Header --}}
                            <div
                                class="p-4 bg-gray-50/70 dark:bg-gray-800 flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                        @switch($category['icon'])
                                            @case('folder')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                                @break
                                            @case('template')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                @break
                                            @case('layers')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                @break
                                            @case('check-circle')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @break
                                            @case('chat')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                @break
                                            @case('annotation')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                @break
                                            @case('document')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                @break
                                            @case('clock')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @break
                                        @endswitch
                                    </span>
                                    <span
                                        class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $category['title'] }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="toggleCategoryByKey('{{ $catKey }}', true)"
                                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                        انتخاب این بخش
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <button type="button"
                                            @click="toggleCategoryByKey('{{ $catKey }}', false)"
                                            class="text-[11px] font-bold text-gray-400 hover:text-gray-600 hover:underline cursor-pointer">
                                        لغو
                                    </button>
                                </div>
                            </div>

                            {{-- Category Permission Items List --}}
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($category['items'] as $itemKey => $item)
                                    <label
                                        class="flex items-start justify-between gap-3 p-3 rounded-xl border transition-all duration-200 cursor-pointer select-none"
                                        :class="form.permissions.includes('{{ addslashes($itemKey) }}')
                                                ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800/50 shadow-xs'
                                                : 'bg-gray-50/50 dark:bg-gray-900/30 border-gray-100 dark:border-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800/60'">
                                        <div class="min-w-0 pr-1">
                                            <span class="text-xs font-bold transition-colors"
                                                  :class="form.permissions.includes('{{ addslashes($itemKey) }}') ? 'text-indigo-900 dark:text-indigo-200' : 'text-gray-800 dark:text-gray-200'">{{ $item['label'] }}</span>
                                            <span
                                                class="text-[11px] text-gray-400 block mt-0.5 leading-snug">{{ $item['desc'] }}</span>
                                        </div>

                                        <div
                                            class="relative shrink-0 mt-0.5 w-9 h-5 rounded-full transition-colors duration-200 ease-in-out cursor-pointer"
                                            :class="form.permissions.includes('{{ addslashes($itemKey) }}') ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                                            <input type="checkbox"
                                                   value="{{ $itemKey }}"
                                                   x-model="form.permissions"
                                                   class="sr-only">
                                            <div
                                                class="absolute top-0.5 right-0.5 w-4 h-4 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out"
                                                :class="form.permissions.includes('{{ addslashes($itemKey) }}') ? '-translate-x-4' : 'translate-x-0'"></div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div
            class="flex items-center justify-between p-5 border-t border-gray-100 dark:border-gray-800 shrink-0 bg-gray-50/70 dark:bg-gray-900/60">
            <button type="button" @click="close()"
                    class="px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-100 transition-all cursor-pointer">
                انصراف
            </button>

            <button type="button" @click="save()"
                    :disabled="saving || !form.display_name.trim()"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 cursor-pointer">
                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="saving ? 'در حال ثبت...' : (isEditing ? 'ذخیره تغییرات نقش' : 'ایجاد نقش جدید')"></span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function roleModalManager() {
            const availableCategories = @json($availablePermissions);
            return {
                open: false,
                isEditing: false,
                saving: false,
                roleId: null,
                errorMsg: '',
                availableCategories: availableCategories,
                form: {
                    name: '',
                    display_name: '',
                    description: '',
                    color: 'indigo',
                    icon: 'shield',
                    permissions: [],
                },
        availableColors: [
            {key: 'purple', label: 'بنفش', hex: '#9333ea'},
            {key: 'indigo', label: 'نیلی', hex: '#4f46e5'},
            {key: 'blue', label: 'آبی', hex: '#2563eb'},
            {key: 'emerald', label: 'زمردی', hex: '#059669'},
            {key: 'teal', label: 'کله‌غازی', hex: '#0d9488'},
            {key: 'cyan', label: 'فیروزه‌ای', hex: '#0891b2'},
            {key: 'amber', label: 'کهربایی', hex: '#d97706'},
            {key: 'orange', label: 'نارنجی', hex: '#ea580c'},
            {key: 'rose', label: 'رز', hex: '#e11d48'},
            {key: 'gray', label: 'خاکستری', hex: '#4b5563'},
        ],
        availableIcons: [
            {key: 'crown', label: 'تاج / مدیریت'},
            {key: 'pencil', label: 'مداد / ویرایشگر'},
            {key: 'eye', label: 'چشم / ناظر'},
            {key: 'code', label: 'کد / برنامه‌نویس'},
            {key: 'palette', label: 'پالت / طراح'},
            {key: 'bug', label: 'تست / کنترل کیفیت'},
            {key: 'shield', label: 'سپر / سرپرست'},
            {key: 'briefcase', label: 'کیف / کارشناس'},
            {key: 'user', label: 'کاربر / همکار'},
            {key: 'star', label: 'ستاره / ارشد'},
        ],
        getColorClass(color) {
            const map = {
                purple: {btn: 'bg-purple-600'},
                indigo: {btn: 'bg-indigo-600'},
                blue: {btn: 'bg-blue-600'},
                emerald: {btn: 'bg-emerald-600'},
                teal: {btn: 'bg-teal-600'},
                cyan: {btn: 'bg-cyan-600'},
                amber: {btn: 'bg-amber-600'},
                orange: {btn: 'bg-orange-600'},
                rose: {btn: 'bg-rose-600'},
                gray: {btn: 'bg-gray-600'},
            };
            return map[color] || map.indigo;
        },
        openCreate() {
            this.isEditing = false;
            this.roleId = null;
            this.errorMsg = '';
            this.saving = false;
            const allKeys = [];
            for (const catKey in this.availableCategories) {
                if (this.availableCategories[catKey]?.items) {
                    allKeys.push(...Object.keys(this.availableCategories[catKey].items));
                }
            }
            this.form = {
                name: '',
                display_name: '',
                description: '',
                color: 'indigo',
                icon: 'shield',
                permissions: [...allKeys],
            };
            this.open = true;
        },
        openEdit(role) {
            this.isEditing = true;
            this.roleId = role.id;
            this.errorMsg = '';
            this.saving = false;
            let rolePerms = [];
            if (Array.isArray(role.permissions)) {
                rolePerms = [...role.permissions];
            } else if (typeof role.permissions === 'string') {
                try {
                    const parsed = JSON.parse(role.permissions);
                    if (Array.isArray(parsed)) rolePerms = parsed;
                } catch(e) {}
            }
            this.form = {
                name: role.name || '',
                display_name: role.display_name || '',
                description: role.description || '',
                color: role.color || 'indigo',
                icon: role.icon || 'shield',
                permissions: rolePerms,
            };
            this.open = true;
        },
        close() {
            this.open = false;
        },
        hasPermission(perm) {
            return this.form.permissions.includes(perm);
        },
        togglePermission(perm) {
            if (this.hasPermission(perm)) {
                this.form.permissions = this.form.permissions.filter(p => p !== perm);
            } else {
                this.form.permissions.push(perm);
            }
        },
        toggleCategoryByKey(catKey, state) {
            const cat = this.availableCategories[catKey];
            if (!cat || !cat.items) return;
            const keys = Object.keys(cat.items);
            if (state) {
                keys.forEach(k => {
                    if (!this.form.permissions.includes(k)) this.form.permissions.push(k);
                });
            } else {
                this.form.permissions = this.form.permissions.filter(p => !keys.includes(p));
            }
        },
        selectAllPermissions() {
            const allKeys = [];
            for (const catKey in this.availableCategories) {
                if (this.availableCategories[catKey]?.items) {
                    allKeys.push(...Object.keys(this.availableCategories[catKey].items));
                }
            }
            this.form.permissions = [...allKeys];
        },
        async save() {
            if (!this.form.display_name.trim()) {
                this.errorMsg = 'لطفاً عنوان نمایشی نقش را وارد کنید.';
                return;
            }
            this.saving = true;
            this.errorMsg = '';

            const url = this.isEditing
                ? `/user/projects/roles/${this.roleId}`
                : `/user/projects/roles`;
            const method = this.isEditing ? 'PUT' : 'POST';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value;
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await res.json();
                if (!res.ok) {
                    this.errorMsg = data.message || data.error || 'خطا در ثبت نقش. لطفاً مجدداً تلاش کنید.';
                    this.saving = false;
                    return;
                }

                // Reload back to roles tab
                window.location.href = window.location.pathname + '?active_tab=roles';
            } catch (err) {
                console.error(err);
                this.errorMsg = 'خطای ارتباط با سرور.';
                this.saving = false;
            }
        }
    };
}

async function deleteProjectRole(roleId, roleTitle) {
    if (!confirm(`آیا از حذف نقش «${roleTitle}» اطمینان دارید؟ تمام اعضای دارای این نقش به ناظر تغییر خواهند کرد.`)) {
        return;
    }

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
        const res = await fetch(`/user/projects/roles/${roleId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            }
        });
        const data = await res.json();
        if (res.ok) {
            window.location.href = window.location.pathname + '?active_tab=roles';
        } else {
            alert(data.error || data.message || 'خطا در حذف نقش');
        }
    } catch (err) {
        console.error(err);
        alert('خطای ارتباط با سرور');
    }
}
</script>
@endpush
