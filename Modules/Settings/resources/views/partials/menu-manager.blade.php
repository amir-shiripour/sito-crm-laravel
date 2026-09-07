{{-- Modules/Settings/resources/views/partials/menu-manager.blade.php --}}

<div x-data="menuManagerApp()" x-init="init()" class="space-y-6 relative">

    {{-- سیستم Toast حرفه‌ای بدون نیاز به alert --}}
    <div class="fixed bottom-6 left-6 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full">
        <template x-for="t in toasts" :key="t.id">
            <div x-show="t.show"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 class="pointer-events-auto p-4 rounded-2xl shadow-xl border flex items-center gap-3 backdrop-blur-md"
                 :class="{
                     'bg-emerald-50/95 border-emerald-200 text-emerald-800 dark:bg-emerald-950/90 dark:border-emerald-800 dark:text-emerald-200': t.type === 'success',
                     'bg-red-50/95 border-red-200 text-red-800 dark:bg-red-950/90 dark:border-red-800 dark:text-red-200': t.type === 'error',
                     'bg-amber-50/95 border-amber-200 text-amber-800 dark:bg-amber-950/90 dark:border-amber-800 dark:text-amber-200': t.type === 'warning',
                     'bg-indigo-50/95 border-indigo-200 text-indigo-800 dark:bg-indigo-950/90 dark:border-indigo-800 dark:text-indigo-200': t.type === 'info'
                 }">
                <div class="shrink-0">
                    <template x-if="t.type === 'success'">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                    <template x-if="t.type === 'error'">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                    <template x-if="t.type === 'warning'">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                    <template x-if="t.type === 'info'">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1 text-xs font-semibold leading-relaxed" x-text="t.message"></div>
                <button type="button" @click="t.show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- مودال دیالوگ تأیید استاندارد مدرن (جایگزین confirm) --}}
    <div x-show="confirmDialog.show" class="fixed inset-0 z-[9998] overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="confirmDialog.show = false"></div>
            <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 p-6 text-right shadow-2xl transition-all border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                         :class="{
                             'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': confirmDialog.variant === 'danger',
                             'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': confirmDialog.variant === 'warning',
                             'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400': confirmDialog.variant === 'primary'
                         }">
                        <template x-if="confirmDialog.variant === 'danger'">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </template>
                        <template x-if="confirmDialog.variant === 'warning'">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="confirmDialog.variant === 'primary'">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="confirmDialog.title"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed" x-text="confirmDialog.description"></p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button" @click="confirmDialog.show = false"
                            class="px-4 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-semibold transition-colors">
                        انصراف
                    </button>
                    <button type="button" @click="executeConfirmAction()"
                            class="px-5 py-2 rounded-xl text-white text-xs font-bold shadow-md transition-all flex items-center gap-1.5"
                            :class="{
                                'bg-red-600 hover:bg-red-700 shadow-red-500/20': confirmDialog.variant === 'danger',
                                'bg-amber-600 hover:bg-amber-700 shadow-amber-500/20': confirmDialog.variant === 'warning',
                                'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/20': confirmDialog.variant === 'primary'
                            }">
                        <span x-text="confirmDialog.confirmText || 'تأیید'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- بنرهای تنظیمات و سوئیچ‌های اصلی منو --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- بنر Master Switch: سوئیچ بین منوی پیش‌فرض هسته و منوی سفارشی --}}
        <div class="rounded-2xl p-5 border transition-all duration-300 shadow-sm flex flex-col justify-between"
             :class="isCustomMenuEnabled
                ? 'bg-gradient-to-r from-emerald-500/10 via-indigo-500/5 to-transparent border-emerald-500/30 dark:border-emerald-500/20'
                : 'bg-gray-50 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700'">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-all"
                        :class="isCustomMenuEnabled
                            ? 'bg-emerald-600 text-white shadow-emerald-500/20'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">منبع فعال منوی کاربری:</h3>
                        <div class="mt-1">
                            <template x-if="isCustomMenuEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    منوی شخصی‌سازی شده (فعال)
                                </span>
                            </template>
                            <template x-if="!isCustomMenuEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    منوی پیش‌فرض هسته سیستم
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- دکمه سوئیچ --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="toggleMasterStatus()" :disabled="isTogglingStatus"
                            class="relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            :class="isCustomMenuEnabled ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-gray-600'">
                        <span
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                            :class="isCustomMenuEnabled ? '-translate-x-6' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                اعمال چیدمان، عناوین و دسترسی‌های سفارشی طراحی‌شده در این صفحه روی منوی کاربران.
            </p>
        </div>

        {{-- بنر سوئیچ حالت منوی دو مرحله‌ای (Drilldown) --}}
        <div class="rounded-2xl p-5 border transition-all duration-300 shadow-sm flex flex-col justify-between"
             :class="isTwoStepEnabled
                ? 'bg-gradient-to-r from-indigo-500/10 via-purple-500/5 to-transparent border-indigo-500/30 dark:border-indigo-500/20'
                : 'bg-gray-50 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700'">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-all"
                        :class="isTwoStepEnabled
                            ? 'bg-indigo-600 text-white shadow-indigo-500/20'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h8m-8 6h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">منوی دو مرحله‌ای (Drilldown):</h3>
                        <div class="mt-1">
                            <template x-if="isTwoStepEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                    حالت مرحله‌ای (فعال)
                                </span>
                            </template>
                            <template x-if="!isTwoStepEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    حالت یکپارچه / آکاردئونی
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- دکمه سوئیچ منوی دو مرحله‌ای --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="toggleTwoStepStatus()" :disabled="isTogglingTwoStep"
                            class="relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            :class="isTwoStepEnabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                        <span
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                            :class="isTwoStepEnabled ? '-translate-x-6' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                با انتخاب هر گروه، منو وارد مرحله بعد شده و فقط زیرگروه‌های آن با دکمه بازگشت نمایش داده می‌شوند تا سایر آیتم‌ها خلوت شوند.
            </p>
        </div>

        {{-- بنر سوئیچ نمایش شمارنده آیتم گروه‌ها --}}
        <div class="rounded-2xl p-5 border transition-all duration-300 shadow-sm flex flex-col justify-between"
             :class="isGroupCounterEnabled
                ? 'bg-gradient-to-r from-sky-500/10 via-blue-500/5 to-transparent border-sky-500/30 dark:border-sky-500/20'
                : 'bg-gray-50 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700'">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-all"
                        :class="isGroupCounterEnabled
                            ? 'bg-sky-600 text-white shadow-sky-500/20'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">شمارنده آیتم گروه‌ها:</h3>
                        <div class="mt-1">
                            <template x-if="isGroupCounterEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                    نمایش شمارنده (فعال)
                                </span>
                            </template>
                            <template x-if="!isGroupCounterEnabled">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    مخفی / غیرفعال
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- دکمه سوئیچ شمارنده --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="toggleGroupCounterStatus()" :disabled="isTogglingGroupCounter"
                            class="relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-50"
                            :class="isGroupCounterEnabled ? 'bg-sky-600' : 'bg-gray-300 dark:bg-gray-600'">
                        <span
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                            :class="isGroupCounterEnabled ? '-translate-x-6' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                نمایش یا مخفی‌سازی نشانگر تعداد زیرآیتم‌ها (Badge شمارنده) در کنار نام گروه‌ها در سایدبار پنل کاربری (/user).
            </p>
        </div>
    </div>

    {{-- هدر تب مدیریت منو و کنترل فیلتر/Scope مدرن و پیشرفته --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-5">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shadow-sm shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white flex items-center gap-2.5 flex-wrap">
                        <span>مرکز کنترل و شخصی‌سازی منوی کاربری</span>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-semibold border border-emerald-200/60 dark:border-emerald-800/40">
                            هوشمند و لایه‌ای (Cascading)
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        مدیریت بصری ساختار منو با Drag & Drop، امکان سفارشی‌سازی عمومی یا اختصاصی برای نقش‌ها و کاربران با قابلیت بازگشت آنی
                    </p>
                </div>
            </div>

            {{-- دکمه‌های عملیات اصلی هدر --}}
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- دکمه افزودن گروه جدید --}}
                <button type="button" @click="openGroupModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100/70 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    گروه جدید
                </button>

                {{-- دکمه ذخیره هوشمند تغییرات با نمایش تعداد آیتم‌های تغییر یافته --}}
                <button type="button" @click="saveAll()" :disabled="isSaving"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all disabled:opacity-50"
                        :class="dirtyCount > 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700'">
                    <svg x-show="!isSaving && dirtyCount === 0" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="!isSaving && dirtyCount > 0" class="w-4 h-4 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span x-text="isSaving ? 'در حال ذخیره...' : (dirtyCount > 0 ? 'ذخیره ' + dirtyCount + ' تغییر' : 'ذخیره تغییرات')"></span>
                </button>
            </div>
        </div>

        {{-- انتخابگر اسکوپ (Scope Switcher) کارتی مدرن و تفکیک‌شده --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
            {{-- کارت اسکوپ: تنظیمات عمومی --}}
            <button type="button" @click="setScope('global')"
                    class="p-3.5 rounded-2xl border text-right transition-all flex items-center justify-between"
                    :class="scope === 'global'
                        ? 'bg-indigo-50/70 dark:bg-indigo-900/30 border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-500/20'
                        : 'bg-gray-50/70 dark:bg-gray-800/50 border-gray-200/80 dark:border-gray-700/80 hover:bg-gray-100/60 dark:hover:bg-gray-700/50'">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                         :class="scope === 'global' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        🌍
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white">تنظیمات عمومی (Global)</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">اعمال برای همه کاربران</div>
                    </div>
                </div>
                <template x-if="scope === 'global'">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                </template>
            </button>

            {{-- کارت اسکوپ: بر اساس نقش --}}
            <button type="button" @click="setScope('role')"
                    class="p-3.5 rounded-2xl border text-right transition-all flex items-center justify-between"
                    :class="scope === 'role'
                        ? 'bg-indigo-50/70 dark:bg-indigo-900/30 border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-500/20'
                        : 'bg-gray-50/70 dark:bg-gray-800/50 border-gray-200/80 dark:border-gray-700/80 hover:bg-gray-100/60 dark:hover:bg-gray-700/50'">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                         :class="scope === 'role' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        👥
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white">بر اساس نقش کاربری</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">شخصی‌سازی برای نقش مشخص</div>
                    </div>
                </div>
                <template x-if="scope === 'role'">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                </template>
            </button>

            {{-- کارت اسکوپ: کاربر خاص --}}
            <button type="button" @click="setScope('user')"
                    class="p-3.5 rounded-2xl border text-right transition-all flex items-center justify-between"
                    :class="scope === 'user'
                        ? 'bg-indigo-50/70 dark:bg-indigo-900/30 border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-500/20'
                        : 'bg-gray-50/70 dark:bg-gray-800/50 border-gray-200/80 dark:border-gray-700/80 hover:bg-gray-100/60 dark:hover:bg-gray-700/50'">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                         :class="scope === 'user' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        👤
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white">کاربر خاص</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">اولویت بالاتر از نقش و عمومی</div>
                    </div>
                </div>
                <template x-if="scope === 'user'">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                </template>
            </button>
        </div>

        {{-- انتخابگر اختصاصی زیرمجموعه اسکوپ (نقش یا جستجوی کاربر با Combobox) --}}
        <div x-show="scope !== 'global'" x-cloak class="p-4 rounded-2xl bg-gray-50/90 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-3">
            {{-- انتخاب نقش --}}
            <div x-show="scope === 'role'" class="flex flex-col sm:flex-row sm:items-center gap-3">
                <label class="text-xs font-bold text-gray-700 dark:text-gray-300 shrink-0">
                    انتخاب نقش کاربری هدف:
                </label>
                <div class="relative flex-1 max-w-sm">
                    <select x-model="selectedRoleId" @change="onRoleChanged()"
                            class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm">
                        <option value="">-- لطفاً یک نقش را انتخاب کنید --</option>
                        <template x-for="r in roles" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </div>
                <span class="text-[11px] text-gray-400 dark:text-gray-500">
                    با انتخاب نقش، تنظیمات اختصاصی آن بارگذاری شده و بخش‌های بدون تغییر از عمومی ارث‌بری می‌کنند.
                </span>
            </div>

            {{-- جستجو و انتخاب کاربر (Combobox مدرن) --}}
            <div x-show="scope === 'user'" class="space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 shrink-0">
                        انتخاب کاربر هدف:
                    </label>
                    <div class="relative flex-1 max-w-md" @click.away="showUserDropdown = false">
                        <div class="relative">
                            <input type="text"
                                   x-model="userSearchQuery"
                                   @focus="showUserDropdown = true; if(userSearchResults.length === 0) searchUsersLive('')"
                                   @input.debounce.300ms="searchUsersLive(userSearchQuery)"
                                   placeholder="جستجو بر اساس نام یا ایمیل کاربر..."
                                   class="w-full rounded-xl border-gray-200 bg-white pl-8 pr-9 py-2 text-xs text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm">
                            <div class="absolute right-3 top-2.5 text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <template x-if="selectedUserObj">
                                <button type="button" @click="clearSelectedUser()" class="absolute left-3 top-2.5 text-gray-400 hover:text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                        </div>

                        {{-- دراپ‌داون نتایج جستجو --}}
                        <div x-show="showUserDropdown"
                             class="absolute right-0 left-0 mt-1.5 max-h-56 overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl z-50 p-1 space-y-1">
                            <template x-if="isSearchingUsers">
                                <div class="py-3 text-center text-xs text-gray-400 flex items-center justify-center gap-2">
                                    <svg class="w-3.5 h-3.5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                    در حال جستجو...
                                </div>
                            </template>
                            <template x-if="!isSearchingUsers && userSearchResults.length === 0">
                                <div class="py-3 text-center text-xs text-gray-400">کاربری یافت نشد.</div>
                            </template>
                            <template x-for="u in userSearchResults" :key="u.id">
                                <button type="button" @click="selectUserFromDropdown(u)"
                                        class="w-full text-right px-3 py-2 rounded-xl text-xs flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                        :class="selectedUserId == u.id ? 'bg-indigo-50/80 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-gray-700 dark:text-gray-200'">
                                    <div>
                                        <div x-text="u.name" class="font-medium"></div>
                                        <div x-text="u.email" class="text-[10px] text-gray-400"></div>
                                    </div>
                                    <template x-if="selectedUserId == u.id">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- نوار وضعیت ارث‌بری و دکمه‌های بازنشانی اختصاصی (Inheritance Status Bar) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80">
            <div class="flex items-center gap-2.5 text-xs text-gray-600 dark:text-gray-300">
                <span class="w-2 h-2 rounded-full"
                      :class="scope === 'global' ? 'bg-indigo-500' : (scopeId ? 'bg-emerald-500' : 'bg-amber-500 animate-ping')"></span>
                <span class="font-bold" x-text="getScopeDescriptionText()"></span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                {{-- دکمه بازگشت به تنظیمات عمومی (فقط در نقش و کاربر فعال است) --}}
                <template x-if="scope !== 'global'">
                    <button type="button" @click="confirmInheritGlobalAll()" :disabled="!scopeId"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-xs font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        <span>بازگردانی به تنظیمات عمومی (Global)</span>
                    </button>
                </template>

                {{-- دکمه بازنشانی به پیش‌فرض کارخانه سیستم --}}
                <button type="button" @click="confirmResetAll()" :disabled="scope !== 'global' && !scopeId"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>بازنشانی به پیش‌فرض سیستم</span>
                </button>
            </div>
        </div>
    </div>

    {{-- وضعیت لودینگ --}}
    <div x-show="isLoading" class="p-12 text-center text-gray-500 dark:text-gray-400">
        <div
            class="inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
            <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-sm font-medium">در حال واکشی اطلاعات و پیکربندی منوها...</span>
        </div>
    </div>

    {{-- بدنه اصلی: ساختار آیتم‌ها و ویرایشگر زنده --}}
    <div x-show="!isLoading" class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-cloak>

        {{-- ستون راست: لیست گروه‌ها و آیتم‌ها (با Drag & Drop بین گروه‌ها و داخل گروه‌ها) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    ساختار گروه‌ها و آیتم‌ها (گروه‌ها و آیتم‌ها را برای جابجایی بکشید و رها کنید)
                </span>
                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium"
                      x-text="groups.length + ' گروه • ' + items.length + ' آیتم'"></span>
            </div>

            {{-- کانتینر Sortable برای جابجایی خود گروه‌ها --}}
            <div id="sortable-groups-container" class="space-y-3">
                <template x-for="(group, gIdx) in getOrderedGroups()" :key="group.key">
                    <div :data-group-key="group.key"
                         class="group-card bg-white dark:bg-gray-800 rounded-2xl border transition-all shadow-sm overflow-hidden"
                         :class="{
                             'border-indigo-500 ring-2 ring-indigo-500/20': selectedGroup && selectedGroup.key === group.key,
                             'border-gray-200 dark:border-gray-700': !(selectedGroup && selectedGroup.key === group.key),
                             'opacity-60': group.hidden
                         }">

                        {{-- هدر گروه (با امکان کلیک برای ویرایش گروه و هندل درگ گروه) --}}
                        <div
                            class="px-4 py-3 bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between cursor-pointer select-none"
                            @click="selectGroup(group)">
                            <div class="flex items-center gap-3">
                                {{-- دستگیره جابجایی گروه --}}
                                <div
                                    class="group-drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-indigo-600 p-1"
                                    title="جابجایی ترتیب این گروه">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2">
                                        <circle cx="9" cy="6" r="1" fill="currentColor"/>
                                        <circle cx="15" cy="6" r="1" fill="currentColor"/>
                                        <circle cx="9" cy="12" r="1" fill="currentColor"/>
                                        <circle cx="15" cy="12" r="1" fill="currentColor"/>
                                        <circle cx="9" cy="18" r="1" fill="currentColor"/>
                                        <circle cx="15" cy="18" r="1" fill="currentColor"/>
                                    </svg>
                                </div>

                                {{-- آیکون گروه --}}
                                <div
                                    class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0 [&>svg]:w-4 [&>svg]:h-4"
                                    x-html="group.icon || group.default_icon">
                                </div>

                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white"
                                        x-text="group.title"></h3>
                                    <span
                                        class="text-[11px] px-2 py-0.2 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                        x-text="getItemsForGroup(group.key).length + ' آیتم'"></span>
                                    <template x-if="group.is_custom">
                                        <span
                                            class="text-[10px] px-2 py-0.2 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 font-medium">سفارشی</span>
                                    </template>
                                    <template x-if="group.hidden">
                                        <span
                                            class="text-[10px] px-2 py-0.2 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 font-medium">پنهان</span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-2" @click.stop>
                                {{-- دکمه ویرایش تنظیمات این گروه --}}
                                <button type="button" @click="selectGroup(group)"
                                        class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-indigo-900/30 text-gray-600 hover:text-indigo-600 dark:text-gray-300 transition-colors">
                                    ویرایش گروه
                                </button>

                                {{-- تاگل پنهان‌سازی گروه --}}
                                <button type="button" @click="toggleGroupHidden(group)"
                                        :class="group.hidden ? 'text-red-500' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                        :title="group.hidden ? 'آشکارسازی گروه' : 'پنهان‌سازی گروه'">
                                    <svg x-show="!group.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="group.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>

                                <template x-if="group.is_custom">
                                    <button type="button" @click="deleteCustomGroup(group.id)"
                                            class="text-red-500 hover:text-red-700 p-1" title="حذف گروه سفارشی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- لیست آیتم‌های داخل گروه (Drag & Drop Container) --}}
                        <div :id="'group-container-' + group.key"
                             class="p-2 space-y-1.5 sortable-group-items min-h-[46px]">
                            <template x-for="(item, iIdx) in getItemsForGroup(group.key)" :key="item.menu_key">
                                <div :data-key="item.menu_key"
                                     @click.stop="selectItem(item)"
                                     :class="{
                                         'ring-2 ring-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/30': selectedItem && selectedItem.menu_key === item.menu_key,
                                         'opacity-50 line-through bg-gray-50/50 dark:bg-gray-900/20': item.hidden,
                                         'bg-white dark:bg-gray-800/80': !(selectedItem && selectedItem.menu_key === item.menu_key) && !item.hidden
                                     }"
                                     class="group flex items-center justify-between p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 cursor-pointer transition-all">

                                    <div class="flex items-center gap-3">
                                        {{-- هندل درگ آیتم --}}
                                        <div
                                            class="item-drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 dark:text-gray-500 p-0.5">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2">
                                                <circle cx="9" cy="6" r="1" fill="currentColor"/>
                                                <circle cx="15" cy="6" r="1" fill="currentColor"/>
                                                <circle cx="9" cy="12" r="1" fill="currentColor"/>
                                                <circle cx="15" cy="12" r="1" fill="currentColor"/>
                                                <circle cx="9" cy="18" r="1" fill="currentColor"/>
                                                <circle cx="15" cy="18" r="1" fill="currentColor"/>
                                            </svg>
                                        </div>

                                        {{-- آیکون آیتم --}}
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 shrink-0 [&>svg]:w-4 [&>svg]:h-4"
                                            x-html="item.icon || item.default_icon">
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white"
                                                      x-text="item.title"></span>

                                                {{-- نشانگرهای سه‌حالته وضوح ارث‌بری --}}
                                                <template x-if="scope !== 'global' && item.has_local_override">
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800"
                                                          title="این آیتم دارای تنظیم اختصاصی در این بخش است">سفارشی اختصاصی</span>
                                                </template>
                                                <template x-if="scope !== 'global' && !item.has_local_override && item.has_global_override">
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-medium"
                                                          title="این آیتم تغییرات عمومی سراسری سیستم را دریافت می‌کند">ارث‌بری از عمومی</span>
                                                </template>
                                                <template x-if="scope === 'global' && item.has_global_override">
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-medium">سفارشی عمومی</span>
                                                </template>
                                                <template x-if="item._dirty">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold animate-pulse">ذخیره نشده</span>
                                                </template>
                                                <template x-if="item.hidden">
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 font-medium">پنهان</span>
                                                </template>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                                <span x-text="'ماژول: ' + item.module_name"></span>
                                                <span>•</span>
                                                <span x-text="'موقعیت: ' + item.position"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- دکمه‌های کنترل سریع --}}
                                    <div class="flex items-center gap-1.5">
                                        {{-- دکمه بازگشت آیتم به تنظیمات عمومی (فقط در صورت داشتن override در نقش/کاربر) --}}
                                        <template x-if="scope !== 'global' && item.has_local_override">
                                            <button type="button" @click.stop="inheritGlobalSingleItem(item)"
                                                    class="p-1.5 rounded-lg text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                                    title="بازگشت این آیتم به تنظیمات عمومی (Global)">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                            </button>
                                        </template>

                                        {{-- تاگل وضعیت مخفی --}}
                                        <button type="button" @click.stop="toggleItemHidden(item)"
                                                :class="item.hidden ? 'text-red-500 hover:text-red-700' : 'text-gray-400 hover:text-indigo-600'"
                                                class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                :title="item.hidden ? 'آشکارسازی آیتم' : 'پنهان‌سازی آیتم'">
                                            <svg x-show="!item.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="item.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                            </svg>
                                        </button>

                                        {{-- دکمه بازنشانی به پیش‌فرض هسته --}}
                                        <template x-if="item.is_customized">
                                            <button type="button" @click.stop="resetSingleItem(item)"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                                    title="بازنشانی این آیتم به پیش‌فرض سیستم">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9"/>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="getItemsForGroup(group.key).length === 0">
                                <div
                                    class="py-4 text-center text-xs text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                    برای افزودن آیتم به این گروه، آیتمی را به اینجا بکشید و رها کنید.
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ستون چپ: پنل ویرایشگر زنده و پیش‌نمایش دقیق سایدبار --}}
        <div class="lg:col-span-5 space-y-4">
            <div class="sticky top-6 space-y-4">

                {{-- کارت ویرایشگر هوشمند (برای آیتم یا گروه) --}}
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div
                        class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white"
                                x-text="editMode === 'group' ? 'ویرایشگر گروه منو' : 'ویرایشگر آیتم منو'"></h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedItem && editMode === 'item'">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono"
                                      x-text="selectedItem.menu_key"></span>
                            </template>
                            <template x-if="selectedGroup && editMode === 'group'">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono"
                                      x-text="'گروه: ' + selectedGroup.key"></span>
                            </template>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- حالت خالی (Empty State) وقتی هیچ آیتمی یا گروهی انتخاب نشده --}}
                        <template x-if="editMode === 'none' || (!selectedItem && !selectedGroup)">
                            <div class="py-12 px-4 text-center space-y-3">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 mx-auto flex items-center justify-center">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">یک آیتم یا گروه را انتخاب کنید</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto leading-relaxed">
                                    برای ویرایش عنوان، آیکون، جایگاه و سطح دسترسی، از ستون راست روی هر گروه یا آیتم کلیک کنید.
                                </p>
                            </div>
                        </template>

                        {{-- ۱. حالت ویرایش گروه --}}
                        <template x-if="editMode === 'group' && selectedGroup">
                            <div class="space-y-4">
                                {{-- بنر وضعیت ارث‌بری گروه --}}
                                <template x-if="scope !== 'global' && !selectedGroup.has_local_override">
                                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 flex items-start gap-2.5 text-xs text-emerald-800 dark:text-emerald-300">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="leading-relaxed">
                                            این گروه در حال حاضر تنظیم اختصاصی در این بخش ندارد و از <span class="font-bold">تنظیمات عمومی (Global)</span> ارث‌بری می‌کند. با اعمال تغییرات، یک نسخه اختصاصی برای آن ثبت خواهد شد.
                                        </div>
                                    </div>
                                </template>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان گروه در
                                            سایدبار</label>
                                        <span class="text-[11px] text-gray-400"
                                              x-text="'پیش‌فرض: ' + selectedGroup.default_title"></span>
                                    </div>
                                    <input type="text" x-model="selectedGroup.title"
                                           @input="markGroupModified(selectedGroup)"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ترتیب
                                            جایگاه گروه (Position)</label>
                                        <input type="number" x-model.number="selectedGroup.position"
                                               @input="markGroupModified(selectedGroup)"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت
                                            نمایش کل گروه</label>
                                        <select :value="selectedGroup.hidden ? 'true' : 'false'"
                                                @change="selectedGroup.hidden = ($event.target.value === 'true'); markGroupModified(selectedGroup)"
                                                class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            <option value="false">آشکار (فعال)</option>
                                            <option value="true">مخفی (غیرفعال)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">کد آیکون گروه
                                            (SVG)</label>
                                        <button type="button"
                                                @click="selectedGroup.icon = selectedGroup.default_icon; markGroupModified(selectedGroup)"
                                                class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">
                                            بازگردانی آیکون اصلی
                                        </button>
                                    </div>
                                    <textarea rows="3" x-model="selectedGroup.icon"
                                              @input="markGroupModified(selectedGroup)"
                                              placeholder="<svg ...>...</svg>"
                                              class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-xs font-mono text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                                </div>

                                <div
                                    class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                                    <template x-if="scope !== 'global' && selectedGroup.has_local_override">
                                        <button type="button" @click="inheritGlobalGroup(selectedGroup)"
                                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                            بازگشت به تنظیمات عمومی
                                        </button>
                                    </template>
                                    <template x-if="scope === 'global' || !selectedGroup.has_local_override">
                                        <span class="text-[11px] text-gray-400">تغییرات گروه آماده ذخیره است</span>
                                    </template>
                                    <button type="button" @click="saveAll()" :disabled="isSaving"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                                        <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span x-text="isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات گروه'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- ۲. حالت ویرایش آیتم --}}
                        <template x-if="editMode === 'item' && selectedItem">
                            <div class="space-y-4">
                                {{-- بنر وضعیت ارث‌بری آیتم منو --}}
                                <template x-if="scope !== 'global' && !selectedItem.has_local_override">
                                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 flex items-start gap-2.5 text-xs text-emerald-800 dark:text-emerald-300">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="leading-relaxed">
                                            این آیتم در حال حاضر از <span class="font-bold">تنظیمات عمومی (Global)</span> ارث‌بری می‌کند. با ویرایش هر فیلد، یک سفارشی‌سازی اختصاصی برای این نقش یا کاربر ثبت می‌شود.
                                        </div>
                                    </div>
                                </template>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان آیتم در
                                            منو</label>
                                        <span class="text-[11px] text-gray-400"
                                              x-text="'پیش‌فرض: ' + selectedItem.default_title"></span>
                                    </div>
                                    <input type="text" x-model="selectedItem.title"
                                           @input="markItemModified(selectedItem)"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گروه‌بندی
                                        والد</label>
                                    <select x-model="selectedItem.group" @change="markItemModified(selectedItem)"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <template x-for="g in groups" :key="g.key">
                                            <option :value="g.key" x-text="g.title"
                                                    :selected="selectedItem.group === g.key"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ترتیب
                                            جایگاه (Position)</label>
                                        <input type="number" x-model.number="selectedItem.position"
                                               @input="markItemModified(selectedItem)"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت
                                            نمایش</label>
                                        <select x-model="selectedItem.hidden" @change="markItemModified(selectedItem)"
                                                class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            <option :value="false">آشکار (فعال)</option>
                                            <option :value="true">مخفی (غیرفعال)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">کد آیکون
                                            (SVG)</label>
                                        <button type="button"
                                                @click="selectedItem.icon = selectedItem.default_icon; markItemModified(selectedItem)"
                                                class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">
                                            بازگردانی آیکون اصلی
                                        </button>
                                    </div>
                                    <textarea rows="3" x-model="selectedItem.icon"
                                              @input="markItemModified(selectedItem)"
                                              placeholder="<svg ...>...</svg>"
                                              class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-xs font-mono text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                                </div>

                                {{-- محدودیت دسترسی در سطح UI --}}
                                <div
                                    class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-3">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدودیت
                                        نمایش (Visibility)</label>
                                    <div class="flex items-center gap-4 text-xs">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="all" x-model="selectedItem.visibility_type"
                                                   @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>همه کاربران</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="roles" x-model="selectedItem.visibility_type"
                                                   @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>نقش‌های خاص</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="users" x-model="selectedItem.visibility_type"
                                                   @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>کاربران خاص</span>
                                        </label>
                                    </div>

                                    <div x-show="selectedItem.visibility_type === 'roles'"
                                         class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-[11px] text-gray-500 block mb-1">انتخاب نقش‌های مجاز:</span>
                                        <div class="grid grid-cols-2 gap-1.5 max-h-32 overflow-y-auto">
                                            <template x-for="r in roles" :key="r.id">
                                                <label
                                                    class="inline-flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" :value="r.name"
                                                           x-model="selectedItem.allowed_roles"
                                                           @change="markItemModified(selectedItem)"
                                                           class="rounded text-indigo-600">
                                                    <span x-text="r.name"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="selectedItem.visibility_type === 'users'"
                                         class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-[11px] text-gray-500 block mb-1">انتخاب کاربران مجاز:</span>
                                        <div class="grid grid-cols-1 gap-1.5 max-h-32 overflow-y-auto">
                                            <template x-for="u in users" :key="u.id">
                                                <label
                                                    class="inline-flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" :value="u.id"
                                                           x-model="selectedItem.allowed_users"
                                                           @change="markItemModified(selectedItem)"
                                                           class="rounded text-indigo-600">
                                                    <span x-text="u.name + ' (' + u.email + ')'"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                                    <template x-if="scope !== 'global' && selectedItem.has_local_override">
                                        <button type="button" @click="inheritGlobalSingleItem(selectedItem)"
                                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                            بازگشت به تنظیمات عمومی
                                        </button>
                                    </template>
                                    <template x-if="scope === 'global' || !selectedItem.has_local_override">
                                        <span class="text-[11px] text-gray-400">تغییرات آیتم آماده ذخیره است</span>
                                    </template>
                                    <button type="button" @click="saveAll()" :disabled="isSaving"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                                        <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span x-text="isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات آیتم'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- پیش‌نمایش زنده در لحظه سایدبار (دقیقاً ۱:۱ منطبق بر ساختار و استایل واقعی sidebar-nav.blade.php) --}}
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl p-4 shadow-md border border-gray-200 dark:border-gray-800">
                    <div
                        class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            پیش‌نمایش زنده و واقعی سایدبار
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-semibold"
                              x-text="getLivePreviewScopeBadge()"></span>
                    </div>

                    {{-- بدنه سایدبار پیش‌نمایش --}}
                    <div class="space-y-1.5 max-h-96 overflow-y-auto custom-scrollbar p-1 text-sm font-medium">
                        <template x-for="block in getOrderedSidebarBlocks()" :key="'block-' + block.key">
                            <div>
                                {{-- ۱. بلاک پیشخوان --}}
                                <template x-if="block.type === 'dashboard'">
                                    <div
                                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 relative overflow-hidden">
                                        <span
                                            class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-indigo-600 rounded-l-full"></span>
                                        <span class="w-5 h-5 shrink-0 [&>svg]:w-5 [&>svg]:h-5"
                                              x-html="block.item.icon || block.item.default_icon"></span>
                                        <span class="truncate" x-text="block.item.title"></span>
                                    </div>
                                </template>

                                {{-- ۲. بلاک آیتم‌های تکی --}}
                                <template x-if="block.type === 'single_items'">
                                    <div class="space-y-1.5">
                                        <template x-for="sItem in block.items" :key="'prev-sitem-' + sItem.menu_key">
                                            <div
                                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 transition-colors">
                                                <span class="w-5 h-5 shrink-0 [&>svg]:w-5 [&>svg]:h-5"
                                                      x-html="sItem.icon || sItem.default_icon"></span>
                                                <span class="truncate text-xs" x-text="sItem.title"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- ۳. بلاک گروه‌ها (شامل ماژول‌ها، مشتریان، تنظیمات و گروه‌های سفارشی) --}}
                                <template x-if="block.type === 'group'">
                                    <div class="mt-1" x-data="{ open: true }">
                                        <button type="button" @click="open = !open"
                                                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <span
                                                    class="w-5 h-5 shrink-0 text-indigo-600 dark:text-indigo-400 [&>svg]:w-5 [&>svg]:h-5"
                                                    x-html="block.icon || block.default_icon || (block.items && block.items[0] ? (block.items[0].icon || block.items[0].default_icon) : '')"></span>
                                                <span class="truncate font-semibold text-start text-xs"
                                                      x-text="block.title"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <template x-if="isGroupCounterEnabled && block.items">
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700"
                                                          x-text="block.items.length"></span>
                                                </template>
                                                <svg :class="open ? 'rotate-90 text-indigo-500' : '-rotate-90'"
                                                     class="w-4 h-4 transition-transform text-gray-400 shrink-0" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15 19l-7-7 7-7"/>
                                                </svg>
                                            </div>
                                        </button>
                                        <div x-show="open"
                                             class="mt-1 space-y-1 relative before:absolute before:right-5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
                                            <template x-for="gItem in block.items"
                                                      :key="'prev-gitem-' + gItem.menu_key">
                                                <div
                                                    class="flex items-center pr-10 pl-3 py-2 text-xs rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 relative">
                                                    <span
                                                        class="absolute right-[18px] top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                                    <span class="truncate" x-text="gItem.title"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال ساخت / ویرایش گروه جدید --}}
    <div x-show="showGroupModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                 @click="showGroupModal = false"></div>

            <div
                class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-right shadow-2xl transition-all border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ایجاد گروه سفارشی جدید در منو
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">عنوان گروه</label>
                        <input type="text" x-model="groupForm.title" placeholder="مثال: خدمات آنلاین"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">شناسه یکتا (Slug
                            انگلیسی)</label>
                        <input type="text" x-model="groupForm.group_key" placeholder="مثال: online_services"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">ترتیب چیدمان
                            (Position)</label>
                        <input type="number" x-model.number="groupForm.position"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showGroupModal = false"
                                class="px-4 py-2 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 text-xs font-semibold">
                            انصراف
                        </button>
                        <button type="button" @click="submitGroupForm()"
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                            ایجاد گروه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- بارگذاری کتابخانه SortableJS در صورت لزوم و اسکریپت Alpine --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    function menuManagerApp() {
        return {
            scope: 'global',
            scopeId: null,
            selectedRoleId: '',
            selectedUserId: '',
            selectedUserObj: null,
            userSearchQuery: '',
            userSearchResults: [],
            showUserDropdown: false,
            isSearchingUsers: false,
            dirtyCount: 0,

            // Toast system state
            toasts: [],
            toastCounter: 0,

            // Confirmation Dialog state
            confirmDialog: {
                show: false,
                title: '',
                description: '',
                confirmText: 'تأیید',
                variant: 'danger', // danger | warning | primary
                onConfirm: null,
            },

            isCustomMenuEnabled: false,
            isTwoStepEnabled: false,
            isGroupCounterEnabled: true,
            isTogglingStatus: false,
            isTogglingTwoStep: false,
            isTogglingGroupCounter: false,
            isLoading: false,
            isSaving: false,
            editMode: 'none', // 'none', 'item', 'group'
            items: [],
            groups: [],
            customGroups: [],
            roles: [],
            users: [],
            selectedItem: null,
            selectedGroup: null,
            showGroupModal: false,
            groupForm: {
                id: null,
                title: '',
                group_key: '',
                position: 99
            },
            groupSortableInstance: null,
            itemSortableInstances: [],

            init() {
                this.loadData();
            },

            // ================= Toast Methods =================
            showToast(message, type = 'success', duration = 4000) {
                const id = ++this.toastCounter;
                const toast = { id, message, type, show: true };
                this.toasts.push(toast);

                setTimeout(() => {
                    const found = this.toasts.find(t => t.id === id);
                    if (found) found.show = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }, duration);
            },

            // ================= Confirm Dialog Methods =================
            showConfirm(title, description, onConfirm, variant = 'danger', confirmText = 'تأیید') {
                this.confirmDialog = {
                    show: true,
                    title,
                    description,
                    variant,
                    confirmText,
                    onConfirm
                };
            },

            executeConfirmAction() {
                if (typeof this.confirmDialog.onConfirm === 'function') {
                    const fn = this.confirmDialog.onConfirm;
                    this.confirmDialog.show = false;
                    fn();
                } else {
                    this.confirmDialog.show = false;
                }
            },

            // ================= Master Toggles =================
            async toggleMasterStatus() {
                this.isTogglingStatus = true;
                const targetStatus = !this.isCustomMenuEnabled;

                try {
                    const res = await fetch('{{ route("settings.menu-manager.toggle-status") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enabled: targetStatus })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.isCustomMenuEnabled = data.is_custom_menu_enabled;
                        this.showToast(data.is_custom_menu_enabled
                            ? 'سیستم منوی سفارشی فعال شد و اولویت یافت.'
                            : 'منوی سیستم به پیش‌فرض هسته بازگشت.', 'success');
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت منو.', 'error');
                    }
                } catch (err) {
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                } finally {
                    this.isTogglingStatus = false;
                }
            },

            async toggleTwoStepStatus() {
                this.isTogglingTwoStep = true;
                const targetStatus = !this.isTwoStepEnabled;

                try {
                    const res = await fetch('{{ route("settings.menu-manager.toggle-two-step") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enabled: targetStatus })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.isTwoStepEnabled = data.is_two_step_enabled;
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت منوی دو مرحله‌ای.', 'error');
                    }
                } catch (err) {
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                } finally {
                    this.isTogglingTwoStep = false;
                }
            },

            async toggleGroupCounterStatus() {
                this.isTogglingGroupCounter = true;
                const targetStatus = !this.isGroupCounterEnabled;

                try {
                    const res = await fetch('{{ route("settings.menu-manager.toggle-group-counter") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enabled: targetStatus })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.isGroupCounterEnabled = data.is_group_counter_enabled;
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت شمارنده گروه‌ها.', 'error');
                    }
                } catch (err) {
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                } finally {
                    this.isTogglingGroupCounter = false;
                }
            },

            // ================= Scope & User Combobox =================
            setScope(newScope) {
                if (this.scope === newScope) return;
                this.scope = newScope;
                this.editMode = 'none';
                this.selectedItem = null;
                this.selectedGroup = null;

                if (newScope === 'global') {
                    this.scopeId = null;
                    this.selectedRoleId = '';
                    this.selectedUserId = '';
                    this.selectedUserObj = null;
                    this.userSearchQuery = '';
                    this.loadData();
                } else if (newScope === 'role') {
                    this.scopeId = this.selectedRoleId || null;
                    this.loadData();
                } else if (newScope === 'user') {
                    this.scopeId = this.selectedUserId || null;
                    this.loadData();
                }
            },

            onRoleChanged() {
                this.scopeId = this.selectedRoleId || null;
                this.loadData();
            },

            async searchUsersLive(query) {
                this.isSearchingUsers = true;
                try {
                    const res = await fetch('{{ route("settings.menu-manager.search-users") }}?q=' + encodeURIComponent(query || ''));
                    const data = await res.json();
                    if (data.success) {
                        this.userSearchResults = data.users || [];
                    }
                } catch (err) {
                    console.error('Error searching users:', err);
                } finally {
                    this.isSearchingUsers = false;
                }
            },

            selectUserFromDropdown(user) {
                this.selectedUserId = user.id;
                this.selectedUserObj = user;
                this.userSearchQuery = user.name + ' (' + user.email + ')';
                this.showUserDropdown = false;
                this.scopeId = user.id;
                this.loadData();
            },

            clearSelectedUser() {
                this.selectedUserId = '';
                this.selectedUserObj = null;
                this.userSearchQuery = '';
                this.scopeId = null;
                this.loadData();
            },

            getScopeDescriptionText() {
                if (this.scope === 'global') {
                    return 'در حال تنظیم پیکربندی عمومی منو (پایه اصلی سیستم)';
                }
                if (this.scope === 'role') {
                    if (!this.selectedRoleId) {
                        return 'لطفاً ابتدا نقش مورد نظر را از کادر بالا انتخاب فرمایید.';
                    }
                    const r = this.roles.find(x => x.id == this.selectedRoleId);
                    const roleName = r ? r.name : this.selectedRoleId;
                    return 'در حال شخصی‌سازی برای نقش: «' + roleName + '» (موارد دست‌نخورده از تنظیمات عمومی ارث می‌برند)';
                }
                if (this.scope === 'user') {
                    if (!this.selectedUserId) {
                        return 'لطفاً ابتدا کاربر مورد نظر را جستجو و انتخاب نمایید.';
                    }
                    const u = this.selectedUserObj;
                    const userName = u ? u.name : ('شناسه ' + this.selectedUserId);
                    return 'در حال شخصی‌سازی برای کاربر: «' + userName + '» (اولویت مطلق)';
                }
                return '';
            },

            getLivePreviewScopeBadge() {
                if (this.scope === 'global') return 'پیش‌نمایش عمومی (Global)';
                if (this.scope === 'role') {
                    const r = this.roles.find(x => x.id == this.selectedRoleId);
                    return r ? ('نقش: ' + r.name) : 'پیش‌نمایش نقش (انتخاب نشده)';
                }
                if (this.scope === 'user') {
                    const u = this.selectedUserObj;
                    return u ? ('کاربر: ' + u.name) : 'پیش‌نمایش کاربر (انتخاب نشده)';
                }
                return 'Live Sidebar';
            },

            // ================= Load Data =================
            async loadData() {
                this.isLoading = true;
                this.destroySortables();
                this.dirtyCount = 0;

                const prevGroupKey = this.selectedGroup ? this.selectedGroup.key : null;
                const prevItemKey = this.selectedItem ? this.selectedItem.menu_key : null;
                const prevEditMode = this.editMode;

                let url = '{{ route("settings.menu-manager.items") }}?scope=' + this.scope;
                if (this.scope === 'role' && this.selectedRoleId) {
                    url += '&scope_id=' + this.selectedRoleId;
                } else if (this.scope === 'user' && this.selectedUserId) {
                    url += '&scope_id=' + this.selectedUserId;
                }

                try {
                    const res = await fetch(url, { credentials: 'same-origin' });
                    const data = await res.json();

                    if (data.success) {
                        this.isCustomMenuEnabled = !!data.is_custom_menu_enabled;
                        this.isTwoStepEnabled = !!data.is_two_step_enabled;
                        this.isGroupCounterEnabled = data.is_group_counter_enabled !== undefined ? !!data.is_group_counter_enabled : true;
                        this.items = (data.items || []).map(i => ({ ...i, _dirty: false }));
                        this.groups = (data.groups || []).map(g => ({ ...g, _dirty: false }));
                        this.customGroups = data.custom_groups || [];
                        this.roles = data.roles || [];
                        this.users = data.users || [];

                        // حفظ کاربر انتخاب شده در کامبوباکس در صورت وجود
                        if (this.scope === 'user' && this.selectedUserId && !this.selectedUserObj) {
                            const foundUser = this.users.find(u => u.id == this.selectedUserId);
                            if (foundUser) {
                                this.selectedUserObj = foundUser;
                                this.userSearchQuery = foundUser.name + ' (' + foundUser.email + ')';
                            }
                        }

                        // بازگردانی انتخاب قبلی در صورت معتبر بودن
                        if (prevEditMode === 'group' && prevGroupKey) {
                            this.selectedGroup = this.groups.find(g => g.key === prevGroupKey) || null;
                            this.selectedItem = null;
                            this.editMode = this.selectedGroup ? 'group' : 'none';
                        } else if (prevEditMode === 'item' && prevItemKey) {
                            this.selectedItem = this.items.find(i => i.menu_key === prevItemKey) || null;
                            this.selectedGroup = null;
                            this.editMode = this.selectedItem ? 'item' : 'none';
                        } else {
                            // در لود اول پنل را در حالت Empty State قرار می‌دهیم تا کاربر سردرگم نشود
                            this.selectedItem = null;
                            this.selectedGroup = null;
                            this.editMode = 'none';
                        }

                        this.$nextTick(() => {
                            this.initSortables();
                        });
                    } else {
                        this.showToast(data.message || 'خطا در بارگذاری منوها.', 'error');
                    }
                } catch (err) {
                    console.error('Error loading menu manager data:', err);
                    this.showToast('خطا در ارتباط با سرور هنگام واکشی اطلاعات.', 'error');
                } finally {
                    this.isLoading = false;
                }
            },

            // ================= Selection & Modifications =================
            selectItem(item) {
                const originalItem = this.items.find(i => i.menu_key === item.menu_key);
                this.selectedItem = originalItem || item;
                this.selectedGroup = null;
                this.editMode = 'item';
            },

            selectGroup(group) {
                const originalGroup = this.groups.find(g => g.key === group.key);
                this.selectedGroup = originalGroup || group;
                this.selectedItem = null;
                this.editMode = 'group';
            },

            markItemModified(item) {
                if (!item._dirty) {
                    item._dirty = true;
                    this.recalcDirtyCount();
                }
                item.is_customized = true;
                if (this.scope !== 'global') {
                    item.has_local_override = true;
                    item.is_inherited = false;
                }
            },

            markGroupModified(group) {
                if (!group._dirty) {
                    group._dirty = true;
                    this.recalcDirtyCount();
                }
                group.is_customized = true;
                if (this.scope !== 'global') {
                    group.has_local_override = true;
                    group.is_inherited = false;
                }
            },

            recalcDirtyCount() {
                const itemDirty = this.items.filter(i => i._dirty).length;
                const groupDirty = this.groups.filter(g => g._dirty).length;
                this.dirtyCount = itemDirty + groupDirty;
            },

            toggleItemHidden(item) {
                const originalItem = this.items.find(i => i.menu_key === item.menu_key);
                const target = originalItem || item;
                target.hidden = !target.hidden;
                this.markItemModified(target);
            },

            toggleGroupHidden(group) {
                const originalGroup = this.groups.find(g => g.key === group.key);
                const target = originalGroup || group;
                target.hidden = !target.hidden;
                this.markGroupModified(target);
            },

            getItemsForGroup(groupKey) {
                return this.items
                    .filter(item => (item.group || 'single') === groupKey)
                    .sort((a, b) => (a.position || 99) - (b.position || 99));
            },

            getOrderedGroups() {
                return this.groups
                    .slice()
                    .sort((a, b) => (a.position || 99) - (b.position || 99));
            },

            getOrderedSidebarBlocks() {
                const blocks = [];
                const sortedGroups = this.getOrderedGroups();

                sortedGroups.forEach(g => {
                    if (g.hidden) return;

                    if (g.key === 'dashboard') {
                        const dashItem = this.items.find(i => i.group === 'dashboard' && !i.hidden);
                        if (dashItem) {
                            blocks.push({
                                type: 'dashboard',
                                key: 'dashboard',
                                item: dashItem,
                                position: g.position || 1
                            });
                        }
                        return;
                    }

                    if (g.key === 'single') {
                        const singleItems = this.items
                            .filter(i => (i.group === 'single' || !i.group) && !i.hidden)
                            .sort((a, b) => (a.position || 99) - (b.position || 99));
                        if (singleItems.length > 0) {
                            blocks.push({
                                type: 'single_items',
                                key: 'single',
                                items: singleItems,
                                position: g.position || 20
                            });
                        }
                        return;
                    }

                    const gItems = this.getItemsForGroup(g.key).filter(i => !i.hidden);
                    if (gItems.length > 0) {
                        blocks.push({
                            type: 'group',
                            key: g.key,
                            title: g.title,
                            icon: g.icon || g.default_icon,
                            items: gItems,
                            position: g.position || 99
                        });
                    }
                });

                return blocks;
            },

            // ================= Sortables =================
            initSortables() {
                if (typeof window.Sortable === 'undefined') return;

                const groupsContainer = document.getElementById('sortable-groups-container');
                if (groupsContainer) {
                    this.groupSortableInstance = new window.Sortable(groupsContainer, {
                        animation: 250,
                        handle: '.group-drag-handle',
                        draggable: '.group-card',
                        ghostClass: 'bg-indigo-50/70',
                        onEnd: (evt) => {
                            this.handleGroupSortEnd(evt);
                        }
                    });
                }

                const itemContainers = document.querySelectorAll('.sortable-group-items');
                itemContainers.forEach(el => {
                    const instance = new window.Sortable(el, {
                        group: 'menu-manager-shared-items',
                        animation: 200,
                        handle: '.item-drag-handle',
                        ghostClass: 'bg-indigo-100/50',
                        onEnd: (evt) => {
                            this.handleItemSortEnd(evt);
                        }
                    });
                    this.itemSortableInstances.push(instance);
                });
            },

            destroySortables() {
                if (this.groupSortableInstance) {
                    this.groupSortableInstance.destroy();
                    this.groupSortableInstance = null;
                }
                this.itemSortableInstances.forEach(i => i.destroy());
                this.itemSortableInstances = [];
            },

            handleGroupSortEnd(evt) {
                const container = evt.to;
                const groupCards = container.querySelectorAll('.group-card');

                Array.from(groupCards).forEach((card, index) => {
                    const gKey = card.getAttribute('data-group-key');
                    const grp = this.groups.find(g => g.key === gKey);
                    if (grp) {
                        grp.position = (index + 1) * 10;
                        this.markGroupModified(grp);
                    }
                });
            },

            handleItemSortEnd(evt) {
                const itemKey = evt.item.getAttribute('data-key');
                const targetGroupId = evt.to.id.replace('group-container-', '');

                const movedItem = this.items.find(i => i.menu_key === itemKey);
                if (movedItem) {
                    movedItem.group = targetGroupId;
                    this.markItemModified(movedItem);
                }

                const childElements = evt.to.querySelectorAll('[data-key]');
                Array.from(childElements).forEach((el, index) => {
                    const key = el.getAttribute('data-key');
                    const itm = this.items.find(i => i.menu_key === key);
                    if (itm) {
                        itm.position = (index + 1) * 5;
                        this.markItemModified(itm);
                    }
                });
            },

            // ================= Save & Reset Actions =================
            async saveAll() {
                if (this.scope === 'role' && !this.selectedRoleId) {
                    this.showToast('لطفاً ابتدا نقش مورد نظر را انتخاب فرمایید.', 'warning');
                    return;
                }
                if (this.scope === 'user' && !this.selectedUserId) {
                    this.showToast('لطفاً ابتدا کاربر مورد نظر را انتخاب فرمایید.', 'warning');
                    return;
                }

                this.isSaving = true;
                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                try {
                    const res = await fetch('{{ route("settings.menu-manager.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            scope: this.scope,
                            scope_id: sId,
                            items: this.items,
                            groups: this.groups
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.showToast(data.message || 'تنظیمات و چیدمان منوها با موفقیت ذخیره شد.', 'success');
                        this.dirtyCount = 0;
                        this.items.forEach(i => i._dirty = false);
                        this.groups.forEach(g => g._dirty = false);
                        this.loadData();
                    } else {
                        this.showToast('خطا در ذخیره‌سازی: ' + (data.message || 'نامشخص'), 'error');
                    }
                } catch (e) {
                    this.showToast('خطا در ارسال اطلاعات به سرور.', 'error');
                } finally {
                    this.isSaving = false;
                }
            },

            // بازگشت آیتم تکی به تنظیمات عمومی (Global)
            inheritGlobalSingleItem(item) {
                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                if (!sId) {
                    this.showToast('شناسه نقش یا کاربر یافت نشد.', 'error');
                    return;
                }

                this.showConfirm(
                    'بازگردانی آیتم به تنظیمات عمومی',
                    `آیا مطمئن هستید که می‌خواهید شخصی‌سازی آیتم «${item.title}» حذف شده و تنظیمات آن از عمومی (Global) ارث‌بری شود؟`,
                    async () => {
                        try {
                            const res = await fetch('{{ route("settings.menu-manager.inherit-global") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    scope: this.scope,
                                    scope_id: sId,
                                    menu_key: item.menu_key
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast(data.message, 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در ارث‌بری از عمومی.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                        }
                    },
                    'primary',
                    'بازگردانی به عمومی'
                );
            },

            // بازگشت گروه تکی به تنظیمات عمومی (Global)
            inheritGlobalGroup(group) {
                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                if (!sId) return;

                this.showConfirm(
                    'بازگردانی گروه به تنظیمات عمومی',
                    `آیا مطمئن هستید که می‌خواهید شخصی‌سازی گروه «${group.title}» حذف شده و از تنظیمات عمومی ارث‌بری نماید؟`,
                    async () => {
                        try {
                            const res = await fetch('{{ route("settings.menu-manager.inherit-global") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    scope: this.scope,
                                    scope_id: sId,
                                    menu_key: 'group:' + group.key
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast(data.message, 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در بازگردانی گروه.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در ارتباط با سرور.', 'error');
                        }
                    },
                    'primary',
                    'بازگردانی به عمومی'
                );
            },

            // بازگردانی کامل کل بخش به تنظیمات عمومی (Inherit All from Global)
            confirmInheritGlobalAll() {
                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                if (!sId) {
                    this.showToast('لطفاً ابتدا نقش یا کاربر مورد نظر را انتخاب کنید.', 'warning');
                    return;
                }

                const scopeLabel = this.scope === 'role' ? 'این نقش' : 'این کاربر';

                this.showConfirm(
                    'بازگردانی تمامی موارد به تنظیمات عمومی',
                    `تمامی تغییرات و تنظیمات اختصاصی ${scopeLabel} حذف خواهند شد و منو دقیقاً مطابق با تنظیمات عمومی (Global) سیستم نمایش داده می‌شود. آیا ادامه می‌دهید؟`,
                    async () => {
                        try {
                            const res = await fetch('{{ route("settings.menu-manager.inherit-global") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    scope: this.scope,
                                    scope_id: sId
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast(data.message, 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در بازنشانی به عمومی.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در ارتباط با سرور.', 'error');
                        }
                    },
                    'primary',
                    'بله، بازگشت به عمومی'
                );
            },

            // بازنشانی آیتم تکی به پیش‌فرض کارخانه
            resetSingleItem(item) {
                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                this.showConfirm(
                    'بازنشانی آیتم به پیش‌فرض سیستم',
                    `آیا از بازنشانی آیتم «${item.title}» به مقادیر اولیه و پیش‌فرض هسته سیستم اطمینان دارید؟`,
                    async () => {
                        try {
                            const res = await fetch('{{ route("settings.menu-manager.reset") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    scope: this.scope,
                                    scope_id: sId,
                                    menu_key: item.menu_key
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast(data.message, 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در بازنشانی آیتم.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در ارتباط با سرور.', 'error');
                        }
                    },
                    'danger',
                    'بازنشانی به پیش‌فرض'
                );
            },

            // بازنشانی کامل بخش به پیش‌فرض کارخانه سیستم
            confirmResetAll() {
                if (this.scope !== 'global') {
                    if (this.scope === 'role' && !this.selectedRoleId) {
                        this.showToast('لطفاً ابتدا نقش مورد نظر را انتخاب فرمایید.', 'warning');
                        return;
                    }
                    if (this.scope === 'user' && !this.selectedUserId) {
                        this.showToast('لطفاً ابتدا کاربر مورد نظر را انتخاب فرمایید.', 'warning');
                        return;
                    }
                }

                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                this.showConfirm(
                    'بازنشانی کامل به پیش‌فرض سیستم',
                    'آیا مطمئن هستید که می‌خواهید تمامی تنظیمات و چیدمان‌های انجام شده در این بخش را به حالت خام و اولیه سیستم بازگردانید؟ این عملیات غیرقابل بازگشت است.',
                    async () => {
                        try {
                            const res = await fetch('{{ route("settings.menu-manager.reset") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    scope: this.scope,
                                    scope_id: sId
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast(data.message, 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در بازنشانی.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در ارتباط با سرور.', 'error');
                        }
                    },
                    'danger',
                    'بله، بازنشانی به پیش‌فرض'
                );
            },

            // ================= Custom Groups CRUD =================
            openGroupModal() {
                this.groupForm = {
                    id: null,
                    title: '',
                    group_key: '',
                    position: 99
                };
                this.showGroupModal = true;
            },

            async submitGroupForm() {
                if (!this.groupForm.title || !this.groupForm.group_key) {
                    this.showToast('لطفاً عنوان و شناسه گروه را وارد کنید.', 'warning');
                    return;
                }

                let sId = null;
                if (this.scope === 'role') sId = this.selectedRoleId;
                if (this.scope === 'user') sId = this.selectedUserId;

                try {
                    const res = await fetch('{{ route("settings.menu-manager.groups.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ...this.groupForm,
                            scope: this.scope,
                            scope_id: sId
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.showGroupModal = false;
                        this.showToast('گروه جدید با موفقیت ایجاد شد.', 'success');
                        this.loadData();
                    } else {
                        this.showToast(data.message || 'خطا در ایجاد گروه.', 'error');
                    }
                } catch (e) {
                    this.showToast('خطا در ارسال اطلاعات به سرور.', 'error');
                }
            },

            deleteCustomGroup(groupId) {
                this.showConfirm(
                    'حذف گروه سفارشی',
                    'آیا از حذف این گروه سفارشی اطمینان دارید؟ آیتم‌های داخل آن به حالت تکی بازمی‌گردند.',
                    async () => {
                        try {
                            const url = '{{ url("settings/menu-manager/groups") }}/' + groupId;
                            const res = await fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showToast('گروه سفارشی با موفقیت حذف شد.', 'success');
                                this.loadData();
                            } else {
                                this.showToast(data.message || 'خطا در حذف گروه.', 'error');
                            }
                        } catch (e) {
                            this.showToast('خطا در ارتباط با سرور.', 'error');
                        }
                    },
                    'danger',
                    'حذف گروه'
                );
            }
        }
    }
</script>
