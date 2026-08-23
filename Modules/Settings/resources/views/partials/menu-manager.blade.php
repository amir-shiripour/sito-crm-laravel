{{-- Modules/Settings/resources/views/partials/menu-manager.blade.php --}}

<div x-data="menuManagerApp()" x-init="init()" class="space-y-6">

    {{-- بنر Master Switch: سوئیچ بین منوی پیش‌فرض هسته و منوی سفارشی --}}
    <div class="rounded-2xl p-5 border transition-all duration-300 shadow-sm"
         :class="isCustomMenuEnabled 
            ? 'bg-gradient-to-r from-emerald-500/10 via-indigo-500/5 to-transparent border-emerald-500/30 dark:border-emerald-500/20' 
            : 'bg-gray-50 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700'">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-all"
                     :class="isCustomMenuEnabled 
                        ? 'bg-emerald-600 text-white shadow-emerald-500/20' 
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">منبع فعال منوی کاربری:</h3>
                        <template x-if="isCustomMenuEnabled">
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                منوی شخصی‌سازی شده (فعال)
                            </span>
                        </template>
                        <template x-if="!isCustomMenuEnabled">
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                منوی پیش‌فرض هسته سیستم
                            </span>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        <template x-if="isCustomMenuEnabled">
                            <span>منوهای سایت بر اساس چیدمان، عناوین و دسترسی‌های سفارشی شما نمایش داده می‌شوند. در صورت غیرفعال کردن، منوی پیش‌فرض لود شده و هیچ دیتایی پاک نمی‌شود.</span>
                        </template>
                        <template x-if="!isCustomMenuEnabled">
                            <span>در حال حاضر منوی استاندارد سیستم فعال است. برای اعمال شخصی‌سازی‌ها، کلید مقابل را فعال کنید (تنظیمات شما در پایگاه‌داده محفوظ است).</span>
                        </template>
                    </p>
                </div>
            </div>

            {{-- دکمه سوئیچ جذاب --}}
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" @click="toggleMasterStatus()" :disabled="isTogglingStatus"
                        class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        :class="isCustomMenuEnabled ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                          :class="isCustomMenuEnabled ? '-translate-x-7' : 'translate-x-0'"></span>
                </button>
                <span class="text-xs font-bold" :class="isCustomMenuEnabled ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-500'"
                      x-text="isCustomMenuEnabled ? 'فعال' : 'پیش‌فرض'"></span>
            </div>
        </div>
    </div>

    {{-- هدر تب مدیریت منو و کنترل فیلتر/Scope --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            مرکز کنترل و شخصی‌سازی منوی کاربری
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-normal">هوشمند و غیرمخرب</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            تغییر بصری ترتیب گروه‌ها و آیتم‌ها با کشیدن و رها کردن (Drag & Drop)، سفارشی‌سازی عناوین، آیکون‌ها و دسترسی‌ها
                        </p>
                    </div>
                </div>
            </div>

            {{-- فیلتر اسکوپ و دکمه‌های عملیات --}}
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- انتخاب Scope --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl text-xs font-medium">
                    <button type="button" @click="setScope('global')"
                            :class="scope === 'global' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        تنظیمات عمومی (همه)
                    </button>
                    <button type="button" @click="setScope('role')"
                            :class="scope === 'role' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        بر اساس نقش
                    </button>
                    <button type="button" @click="setScope('user')"
                            :class="scope === 'user' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        کاربر خاص
                    </button>
                </div>

                {{-- انتخاب نقش در صورت انتخاب اسکوپ نقش --}}
                <div x-show="scope === 'role'" class="relative" x-cloak>
                    <select x-model="selectedRoleId" @change="loadData()" class="rounded-xl border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">-- انتخاب نقش --</option>
                        <template x-for="r in roles" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </div>

                {{-- انتخاب کاربر در صورت انتخاب اسکوپ کاربر --}}
                <div x-show="scope === 'user'" class="relative" x-cloak>
                    <select x-model="selectedUserId" @change="loadData()" class="rounded-xl border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">-- انتخاب کاربر --</option>
                        <template x-for="u in users" :key="u.id">
                            <option :value="u.id" x-text="u.name + ' (' + u.email + ')'"></option>
                        </template>
                    </select>
                </div>

                {{-- دکمه افزودن گروه جدید --}}
                <button type="button" @click="openGroupModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 text-xs font-semibold hover:bg-indigo-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    گروه جدید
                </button>

                {{-- دکمه بازنشانی کلی --}}
                <button type="button" @click="confirmResetAll()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    بازنشانی به پیش‌فرض
                </button>

                {{-- دکمه ذخیره تغییرات --}}
                <button type="button" @click="saveAll()" :disabled="isSaving"
                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all disabled:opacity-50">
                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span x-text="isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- وضعیت لودینگ --}}
    <div x-show="isLoading" class="p-12 text-center text-gray-500 dark:text-gray-400">
        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
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
                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium" x-text="groups.length + ' گروه • ' + items.length + ' آیتم'"></span>
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
                        <div class="px-4 py-3 bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between cursor-pointer select-none"
                             @click="selectGroup(group)">
                            <div class="flex items-center gap-3">
                                {{-- دستگیره جابجایی گروه --}}
                                <div class="group-drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-indigo-600 p-1" title="جابجایی ترتیب این گروه">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="6" r="1" fill="currentColor"/><circle cx="15" cy="6" r="1" fill="currentColor"/>
                                        <circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/>
                                        <circle cx="9" cy="18" r="1" fill="currentColor"/><circle cx="15" cy="18" r="1" fill="currentColor"/>
                                    </svg>
                                </div>

                                {{-- آیکون گروه --}}
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0 [&>svg]:w-4 [&>svg]:h-4"
                                     x-html="group.icon || group.default_icon">
                                </div>

                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white" x-text="group.title"></h3>
                                    <span class="text-[11px] px-2 py-0.2 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="getItemsForGroup(group.key).length + ' آیتم'"></span>
                                    <template x-if="group.is_custom">
                                        <span class="text-[10px] px-2 py-0.2 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 font-medium">سفارشی</span>
                                    </template>
                                    <template x-if="group.hidden">
                                        <span class="text-[10px] px-2 py-0.2 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 font-medium">پنهان</span>
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
                                    <svg x-show="!group.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="group.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>

                                <template x-if="group.is_custom">
                                    <button type="button" @click="deleteCustomGroup(group.id)" class="text-red-500 hover:text-red-700 p-1" title="حذف گروه سفارشی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- لیست آیتم‌های داخل گروه (Drag & Drop Container) --}}
                        <div :id="'group-container-' + group.key" class="p-2 space-y-1.5 sortable-group-items min-h-[46px]">
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
                                        <div class="item-drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 dark:text-gray-500 p-0.5">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="9" cy="6" r="1" fill="currentColor"/><circle cx="15" cy="6" r="1" fill="currentColor"/>
                                                <circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/>
                                                <circle cx="9" cy="18" r="1" fill="currentColor"/><circle cx="15" cy="18" r="1" fill="currentColor"/>
                                            </svg>
                                        </div>

                                        {{-- آیکون آیتم --}}
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 shrink-0 [&>svg]:w-4 [&>svg]:h-4"
                                             x-html="item.icon || item.default_icon">
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="item.title"></span>
                                                <template x-if="item.is_customized">
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-medium">سفارشی</span>
                                                </template>
                                                <template x-if="item.hidden">
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 font-medium">پنهان</span>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                                <span x-text="'ماژول: ' + item.module_name"></span>
                                                <span>•</span>
                                                <span x-text="'موقعیت: ' + item.position"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- دکمه‌های کنترل سریع --}}
                                    <div class="flex items-center gap-1.5">
                                        {{-- تاگل وضعیت مخفی --}}
                                        <button type="button" @click.stop="toggleItemHidden(item)"
                                                :class="item.hidden ? 'text-red-500 hover:text-red-700' : 'text-gray-400 hover:text-indigo-600'"
                                                class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                :title="item.hidden ? 'آشکارسازی آیتم' : 'پنهان‌سازی آیتم'">
                                            <svg x-show="!item.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="item.hidden" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                            </svg>
                                        </button>

                                        {{-- دکمه بازنشانی تکی --}}
                                        <template x-if="item.is_customized">
                                            <button type="button" @click.stop="resetSingleItem(item)"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                                    title="بازنشانی این آیتم به پیش‌فرض">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="getItemsForGroup(group.key).length === 0">
                                <div class="py-4 text-center text-xs text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
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
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white"
                                x-text="editMode === 'group' ? 'ویرایشگر گروه منو' : 'ویرایشگر آیتم منو'"></h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedItem && editMode === 'item'">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono" x-text="selectedItem.menu_key"></span>
                            </template>
                            <template x-if="selectedGroup && editMode === 'group'">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono" x-text="'گروه: ' + selectedGroup.key"></span>
                            </template>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- ۱. حالت ویرایش گروه --}}
                        <template x-if="editMode === 'group' && selectedGroup">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان گروه در سایدبار</label>
                                        <span class="text-[11px] text-gray-400" x-text="'پیش‌فرض: ' + selectedGroup.default_title"></span>
                                    </div>
                                    <input type="text" x-model="selectedGroup.title" @input="markGroupModified(selectedGroup)"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ترتیب جایگاه گروه (Position)</label>
                                        <input type="number" x-model.number="selectedGroup.position" @input="markGroupModified(selectedGroup)"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت نمایش کل گروه</label>
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
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">کد آیکون گروه (SVG)</label>
                                        <button type="button" @click="selectedGroup.icon = selectedGroup.default_icon; markGroupModified(selectedGroup)" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">
                                            بازگردانی آیکون اصلی
                                        </button>
                                    </div>
                                    <textarea rows="3" x-model="selectedGroup.icon" @input="markGroupModified(selectedGroup)"
                                              placeholder="<svg ...>...</svg>"
                                              class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-xs font-mono text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                                </div>

                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <span class="text-[11px] text-gray-400">تغییرات گروه آماده ذخیره است</span>
                                    <button type="button" @click="saveAll()" :disabled="isSaving"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                                        <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span x-text="isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات گروه'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- ۲. حالت ویرایش آیتم --}}
                        <template x-if="editMode === 'item' && selectedItem">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان آیتم در منو</label>
                                        <span class="text-[11px] text-gray-400" x-text="'پیش‌فرض: ' + selectedItem.default_title"></span>
                                    </div>
                                    <input type="text" x-model="selectedItem.title" @input="markItemModified(selectedItem)"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گروه‌بندی والد</label>
                                    <select x-model="selectedItem.group" @change="markItemModified(selectedItem)"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <template x-for="g in groups" :key="g.key">
                                            <option :value="g.key" x-text="g.title" :selected="selectedItem.group === g.key"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ترتیب جایگاه (Position)</label>
                                        <input type="number" x-model.number="selectedItem.position" @input="markItemModified(selectedItem)"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت نمایش</label>
                                        <select x-model="selectedItem.hidden" @change="markItemModified(selectedItem)"
                                                class="w-full rounded-xl border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            <option :value="false">آشکار (فعال)</option>
                                            <option :value="true">مخفی (غیرفعال)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">کد آیکون (SVG)</label>
                                        <button type="button" @click="selectedItem.icon = selectedItem.default_icon; markItemModified(selectedItem)" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">
                                            بازگردانی آیکون اصلی
                                        </button>
                                    </div>
                                    <textarea rows="3" x-model="selectedItem.icon" @input="markItemModified(selectedItem)"
                                              placeholder="<svg ...>...</svg>"
                                              class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-xs font-mono text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                                </div>

                                {{-- محدودیت دسترسی در سطح UI --}}
                                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-3">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدودیت نمایش (Visibility)</label>
                                    <div class="flex items-center gap-4 text-xs">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="all" x-model="selectedItem.visibility_type" @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>همه کاربران</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="roles" x-model="selectedItem.visibility_type" @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>نقش‌های خاص</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" value="users" x-model="selectedItem.visibility_type" @change="markItemModified(selectedItem)" class="text-indigo-600">
                                            <span>کاربران خاص</span>
                                        </label>
                                    </div>

                                    <div x-show="selectedItem.visibility_type === 'roles'" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-[11px] text-gray-500 block mb-1">انتخاب نقش‌های مجاز:</span>
                                        <div class="grid grid-cols-2 gap-1.5 max-h-32 overflow-y-auto">
                                            <template x-for="r in roles" :key="r.id">
                                                <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" :value="r.name" x-model="selectedItem.allowed_roles" @change="markItemModified(selectedItem)" class="rounded text-indigo-600">
                                                    <span x-text="r.name"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="selectedItem.visibility_type === 'users'" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-[11px] text-gray-500 block mb-1">انتخاب کاربران مجاز:</span>
                                        <div class="grid grid-cols-1 gap-1.5 max-h-32 overflow-y-auto">
                                            <template x-for="u in users" :key="u.id">
                                                <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" :value="u.id" x-model="selectedItem.allowed_users" @change="markItemModified(selectedItem)" class="rounded text-indigo-600">
                                                    <span x-text="u.name + ' (' + u.email + ')'"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <span class="text-[11px] text-gray-400">تغییرات آیتم آماده ذخیره است</span>
                                    <button type="button" @click="saveAll()" :disabled="isSaving"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                                        <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span x-text="isSaving ? 'در حال ذخیره...' : 'ذخیره تغییرات آیتم'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- پیش‌نمایش زنده در لحظه سایدبار (دقیقاً ۱:۱ منطبق بر ساختار و استایل واقعی sidebar-nav.blade.php) --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 shadow-md border border-gray-200 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            پیش‌نمایش زنده و واقعی سایدبار
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500">Live Sidebar</span>
                    </div>

                    {{-- بدنه سایدبار پیش‌نمایش --}}
                    <div class="space-y-1.5 max-h-96 overflow-y-auto custom-scrollbar p-1 text-sm font-medium">
                        <template x-for="block in getOrderedSidebarBlocks()" :key="'block-' + block.key">
                            <div>
                                {{-- ۱. بلاک پیشخوان --}}
                                <template x-if="block.type === 'dashboard'">
                                    <div class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 relative overflow-hidden">
                                        <span class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-indigo-600 rounded-l-full"></span>
                                        <span class="w-5 h-5 shrink-0 [&>svg]:w-5 [&>svg]:h-5" x-html="block.item.icon || block.item.default_icon"></span>
                                        <span class="truncate" x-text="block.item.title"></span>
                                    </div>
                                </template>

                                {{-- ۲. بلاک آیتم‌های تکی --}}
                                <template x-if="block.type === 'single_items'">
                                    <div class="space-y-1.5">
                                        <template x-for="sItem in block.items" :key="'prev-sitem-' + sItem.menu_key">
                                            <div class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 transition-colors">
                                                <span class="w-5 h-5 shrink-0 [&>svg]:w-5 [&>svg]:h-5" x-html="sItem.icon || sItem.default_icon"></span>
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
                                                <span class="w-5 h-5 shrink-0 text-indigo-600 dark:text-indigo-400 [&>svg]:w-5 [&>svg]:h-5" x-html="block.icon || block.default_icon || (block.items && block.items[0] ? (block.items[0].icon || block.items[0].default_icon) : '')"></span>
                                                <span class="truncate font-semibold text-start text-xs" x-text="block.title"></span>
                                            </div>
                                            <svg :class="open ? 'rotate-90 text-indigo-500' : '-rotate-90'" class="w-4 h-4 transition-transform text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" class="mt-1 space-y-1 relative before:absolute before:right-5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
                                            <template x-for="gItem in block.items" :key="'prev-gitem-' + gItem.menu_key">
                                                <div class="flex items-center pr-10 pl-3 py-2 text-xs rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 relative">
                                                    <span class="absolute right-[18px] top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
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
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showGroupModal = false"></div>

            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-right shadow-2xl transition-all border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">شناسه یکتا (Slug انگلیسی)</label>
                        <input type="text" x-model="groupForm.group_key" placeholder="مثال: online_services"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">ترتیب چیدمان (Position)</label>
                        <input type="number" x-model.number="groupForm.position"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showGroupModal = false" class="px-4 py-2 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 text-xs font-semibold">
                            انصراف
                        </button>
                        <button type="button" @click="submitGroupForm()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
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
        isCustomMenuEnabled: false,
        isTogglingStatus: false,
        isLoading: false,
        isSaving: false,
        editMode: 'item', // 'item' or 'group'
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
                    body: JSON.stringify({
                        enabled: targetStatus
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.isCustomMenuEnabled = data.is_custom_menu_enabled;
                } else {
                    alert(data.message || 'خطا در تغییر وضعیت سیستم منو.');
                }
            } catch (err) {
                alert('خطا در برقراری ارتباط با سرور.');
            } finally {
                this.isTogglingStatus = false;
            }
        },

        setScope(newScope) {
            this.scope = newScope;
            if (newScope === 'global') {
                this.scopeId = null;
                this.selectedRoleId = '';
                this.selectedUserId = '';
                this.loadData();
            }
        },

        async loadData() {
            this.isLoading = true;
            this.destroySortables();

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
                    this.items = data.items || [];
                    this.groups = data.groups || [];
                    this.customGroups = data.custom_groups || [];
                    this.roles = data.roles || [];
                    this.users = data.users || [];

                    if (prevEditMode === 'group' && prevGroupKey) {
                        this.selectedGroup = this.groups.find(g => g.key === prevGroupKey) || (this.groups.length > 0 ? this.groups[0] : null);
                        this.selectedItem = null;
                        this.editMode = 'group';
                    } else if (prevEditMode === 'item' && prevItemKey) {
                        this.selectedItem = this.items.find(i => i.menu_key === prevItemKey) || (this.items.length > 0 ? this.items[0] : null);
                        this.selectedGroup = null;
                        this.editMode = 'item';
                    } else if (this.items.length > 0) {
                        this.selectedItem = this.items[0];
                        this.selectedGroup = null;
                        this.editMode = 'item';
                    }

                    this.$nextTick(() => {
                        this.initSortables();
                    });
                }
            } catch (err) {
                console.error('Error loading menu manager data:', err);
            } finally {
                this.isLoading = false;
            }
        },

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
            item.is_customized = true;
        },

        markGroupModified(group) {
            group.is_customized = true;
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

        initSortables() {
            if (typeof window.Sortable === 'undefined') return;

            // 1. راه‌اندازی Sortable برای خودِ گروه‌ها
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

            // 2. راه‌اندازی Sortable برای آیتم‌های داخل هر گروه
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

            // به روز رسانی گروه آیتم منتقل شده
            const movedItem = this.items.find(i => i.menu_key === itemKey);
            if (movedItem) {
                movedItem.group = targetGroupId;
                this.markItemModified(movedItem);
            }

            // به روز رسانی ترتیب همه آیتم‌های داخل ظرف مقصد
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

        async saveAll() {
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
                    alert('✓ تنظیمات و چیدمان منوها با موفقیت ذخیره شد.');
                    this.loadData();
                } else {
                    alert('خطا در ذخیره‌سازی: ' + (data.message || 'نامشخص'));
                }
            } catch (e) {
                alert('خطا در ارسال اطلاعات به سرور.');
            } finally {
                this.isSaving = false;
            }
        },

        async resetSingleItem(item) {
            if (!confirm('آیا از بازنشانی این آیتم به مقادیر پیش‌فرض هسته مطمئن هستید؟')) return;

            let sId = null;
            if (this.scope === 'role') sId = this.selectedRoleId;
            if (this.scope === 'user') sId = this.selectedUserId;

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
                    this.loadData();
                }
            } catch (e) {
                console.error(e);
            }
        },

        async confirmResetAll() {
            if (!confirm('آیا مطمئن هستید که می‌خواهید تمام تنظیمات منو در این بخش را به حالت پیش‌فرض برگردانید؟')) return;

            let sId = null;
            if (this.scope === 'role') sId = this.selectedRoleId;
            if (this.scope === 'user') sId = this.selectedUserId;

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
                    alert(data.message);
                    this.loadData();
                }
            } catch (e) {
                console.error(e);
            }
        },

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
                alert('لطفاً عنوان و شناسه گروه را وارد کنید.');
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
                    this.loadData();
                } else {
                    alert(data.message || 'خطا در ایجاد گروه.');
                }
            } catch (e) {
                alert('خطا در ارسال اطلاعات به سرور.');
            }
        },

        async deleteCustomGroup(groupId) {
            if (!confirm('آیا از حذف این گروه سفارشی اطمینان دارید؟ آیتم‌های داخل آن به حالت تکی بازمی‌گردند.')) return;

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
                    this.loadData();
                }
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>
