<div x-data="{
    insertModalOpen: false,
    activeTagType: '',
    targetTextareaId: '',
    card_number: '',
    bank_name: '',
    card_holder: '',
    iban_code: '',
    account_holder: '',
    crypto_currency: 'USDT',
    crypto_network: 'TRC20',
    crypto_address: '',
    copy_text: '',
    copy_label: 'کد تخفیف',
    phone_number: '',
    phone_label: '',
    btn_label: '',
    btn_url: '',

    openInsertModal(type, targetId) {
        this.activeTagType = type;
        this.targetTextareaId = targetId;
        this.insertModalOpen = true;
    },

    insertSmartTag() {
        let tag = '';
        if (this.activeTagType === 'bank_card') {
            if (!this.card_number) return;
            tag = '[card number=&quot;' + this.card_number + '&quot; bank=&quot;' + (this.bank_name || 'کارت بانکی') + '&quot; owner=&quot;' + (this.card_holder || '') + '&quot;]';
        } else if (this.activeTagType === 'iban') {
            if (!this.iban_code) return;
            tag = '[iban code=&quot;' + this.iban_code + '&quot; owner=&quot;' + (this.account_holder || '') + '&quot;]';
        } else if (this.activeTagType === 'crypto') {
            if (!this.crypto_address) return;
            tag = '[crypto currency=&quot;' + (this.crypto_currency || 'USDT') + '&quot; network=&quot;' + (this.crypto_network || 'TRC20') + '&quot; address=&quot;' + this.crypto_address + '&quot;]';
        } else if (this.activeTagType === 'copy') {
            if (!this.copy_text) return;
            tag = '[copy text=&quot;' + this.copy_text + '&quot; label=&quot;' + (this.copy_label || 'کپی') + '&quot;]';
        } else if (this.activeTagType === 'phone') {
            if (!this.phone_number) return;
            tag = '[phone number=&quot;' + this.phone_number + '&quot; label=&quot;' + (this.phone_label || ('تماس با ' + this.phone_number)) + '&quot;]';
        } else if (this.activeTagType === 'button') {
            if (!this.btn_url) return;
            tag = '[button url=&quot;' + this.btn_url + '&quot; label=&quot;' + (this.btn_label || 'مشاهده و اقدام') + '&quot;]';
        }

        if (tag) {
            const el = document.getElementById(this.targetTextareaId);
            if (el) {
                const start = el.selectionStart || 0;
                const end = el.selectionEnd || 0;
                const val = el.value || '';
                const newVal = val.substring(0, start) + '\n' + tag + '\n' + val.substring(end);
                el.value = newVal;
                el.dispatchEvent(new Event('input'));
            }
        }
        this.insertModalOpen = false;
        this.card_number = ''; this.bank_name = ''; this.card_holder = '';
        this.iban_code = ''; this.account_holder = ''; this.crypto_address = '';
        this.copy_text = ''; this.phone_number = ''; this.phone_label = '';
        this.btn_label = ''; this.btn_url = '';
    }
}" class="space-y-8 font-iranYekan pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-6 rounded-[2rem] border border-slate-200/80 dark:border-slate-800 shadow-sm sticky top-4 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('user.smartbot.qna') }}" wire:navigate class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $editingQuestionId ? 'ویرایش سوال و پاسخ' : 'افزودن سوال و پاسخ جدید' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">مشخصات سوال، پاسخ و منوهای هوشمند پاسخگو را مدیریت کنید.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('user.smartbot.qna') }}" wire:navigate class="px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl transition-all">
                انصراف
            </a>
            <button
                type="button"
                wire:click="save(false)"
                wire:loading.attr="disabled"
                class="px-4 py-2.5 text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:hover:bg-indigo-500/20 border border-indigo-200/50 dark:border-indigo-500/30 rounded-xl transition-all flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="save">ذخیره موقت</span>
                <span wire:loading wire:target="save">در حال ذخیره...</span>
            </button>
            <button
                type="button"
                wire:click="save(true)"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/25 transition-all active:scale-95 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                <span>ذخیره و خروج</span>
            </button>
        </div>
    </div>

    <!-- Section 1: Question Info -->
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-6 sm:p-8 rounded-[2rem] border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">۱</span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">مشخصات سوال و کلیدواژه‌ها</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">عباراتی که کاربر ممکن است از ربات بپرسد</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2 space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    متن سوال اصلی <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    wire:model="question_text"
                    placeholder="مثلا: شرایط بازگشت کالا چیست؟"
                    class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                />
                @error('question_text') <span class="text-xs text-rose-500 font-bold block">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    کلمات کلیدی مترادف (با ویرگول انگلیسی یا فارسی جدا کنید)
                </label>
                <input
                    type="text"
                    wire:model="keywords"
                    placeholder="مرجوعی، پس دادن، مرجوع کالا، تعویض"
                    class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">دسته‌بندی</label>
                    <input
                        type="text"
                        wire:model="category_field"
                        placeholder="general, sales, support..."
                        class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    />
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">اولویت (عدد بزرگتر = اولویت بالاتر)</label>
                    <input
                        type="number"
                        wire:model="priority"
                        class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    />
                </div>
            </div>

            <div class="md:col-span-2 flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">وضعیت فعال بودن سوال</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">سوالات غیرفعال در پاسخگویی ربات استفاده نمی‌شوند.</p>
                    </div>
                </div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:-translate-x-full rtl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Section 2: Answer Configuration -->
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-6 sm:p-8 rounded-[2rem] border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6 relative overflow-hidden">
        <!-- Global Loading Overlay for section changes -->
        <div wire:loading.flex wire:target="answer_type" class="absolute inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm z-10 flex flex-col items-center justify-center gap-3">
            <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">در حال بارگذاری فرم پاسخ...</span>
        </div>

        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">۲</span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">تنظیم نوع و محتوای پاسخ</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">مشخص کنید ربات چگونه به این سوال پاسخ دهد</p>
            </div>
        </div>

        <!-- Answer Text Base Input -->
        <div x-data="{ expanded: false }" x-effect="document.body.classList.toggle('overflow-hidden', expanded)" class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    متن پاسخ اولیه / توضیحات <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-bold text-slate-400">
                        {{ mb_strlen($answer_text) }} کاراکتر
                    </span>
                    <button
                        type="button"
                        @click="expanded = true"
                        class="inline-flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-2.5 py-1 rounded-lg transition-all"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                        <span>بزرگ‌نمایی ویرایشگر</span>
                    </button>
                </div>
            </div>

            <!-- Quick Insert Toolbar -->
            <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                <span class="text-[10px] text-slate-400 pl-1 border-l border-slate-300 dark:border-slate-700 ml-1 shrink-0">درج سریع در موقعیت کرسر:</span>
                <button type="button" @click="openInsertModal('bank_card', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">
                    💳 کارت بانکی
                </button>
                <button type="button" @click="openInsertModal('iban', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-sky-500 hover:text-sky-600 transition-all flex items-center gap-1 cursor-pointer">
                    🏦 شماره شبا
                </button>
                <button type="button" @click="openInsertModal('crypto', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-purple-500 hover:text-purple-600 transition-all flex items-center gap-1 cursor-pointer">
                    🪙 کیف پول کریپتو
                </button>
                <button type="button" @click="openInsertModal('copy', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center gap-1 cursor-pointer">
                    📋 باکس کپی
                </button>
                <button type="button" @click="openInsertModal('phone', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">
                    📞 دکمه تماس
                </button>
                <button type="button" @click="openInsertModal('button', 'main_answer_textarea')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-amber-500 hover:text-amber-600 transition-all flex items-center gap-1 cursor-pointer">
                    🔗 دکمه لینک
                </button>
            </div>

            <textarea
                id="main_answer_textarea"
                wire:model="answer_text"
                rows="5"
                placeholder="متنی که همزمان یا قبل از ارائه کالاها/منو نمایش داده می‌شود..."
                class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all resize-y custom-scrollbar"
            ></textarea>
            @error('answer_text') <span class="text-xs text-rose-500 font-bold block">{{ $message }}</span> @enderror

            <!-- Teleported Fullscreen Overlay -->
            <template x-teleport="body">
                <div x-show="expanded" class="fixed inset-0 z-[9999] bg-slate-900/80 backdrop-blur-md p-4 sm:p-8 flex items-center justify-center font-iranYekan" x-cloak>
                    <div class="w-full max-w-5xl h-[88vh] bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col gap-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full bg-indigo-500"></span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">ویرایشگر متنی پاسخ اولیه (تمام‌صفحه)</span>
                                <span class="text-xs font-bold text-slate-400 mr-4">{{ mb_strlen($answer_text) }} کاراکتر</span>
                            </div>
                            <button type="button" @click="expanded = false" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all">
                                تأیید و بستن ✕
                            </button>
                        </div>

                        <!-- Fullscreen Toolbar -->
                        <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <span class="text-[10px] text-slate-400 pl-1 border-l border-slate-300 dark:border-slate-700 ml-1 shrink-0">درج سریع المان هوشمند:</span>
                            <button type="button" @click="openInsertModal('bank_card', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">💳 کارت بانکی</button>
                            <button type="button" @click="openInsertModal('iban', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-sky-500 hover:text-sky-600 transition-all flex items-center gap-1 cursor-pointer">🏦 شماره شبا</button>
                            <button type="button" @click="openInsertModal('crypto', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-purple-500 hover:text-purple-600 transition-all flex items-center gap-1 cursor-pointer">🪙 کریپتو</button>
                            <button type="button" @click="openInsertModal('copy', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center gap-1 cursor-pointer">📋 باکس کپی</button>
                            <button type="button" @click="openInsertModal('phone', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">📞 تماس</button>
                            <button type="button" @click="openInsertModal('button', 'main_answer_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-amber-500 hover:text-amber-600 transition-all flex items-center gap-1 cursor-pointer">🔗 لینک</button>
                        </div>

                        <textarea
                            id="main_answer_textarea_fs"
                            wire:model="answer_text"
                            placeholder="متن کامل پاسخ را وارد کنید..."
                            class="w-full flex-1 p-5 border border-slate-200 dark:border-slate-700/80 rounded-2xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-base outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 leading-relaxed resize-none custom-scrollbar"
                        ></textarea>
                    </div>
                </div>
            </template>
        </div>

        <!-- Smart Attachments UI Section (Main Answer) -->
        <div class="p-5 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94a3 3 0 114.243 4.243L8.567 18.312a1.5 1.5 0 11-2.122-2.122l8.834-8.834"/></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">پیوست‌های تعاملی و هوشمند (پاسخ اصلی)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">افزودن کارت بانکی، شماره شبا، کیف پول کریپتو یا دکمه اقدام با کپی آسان</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="addSmartAttachment('bank_card', 'main')" class="px-2.5 py-1.5 text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 rounded-lg hover:bg-emerald-100 transition-all flex items-center gap-1 cursor-pointer">
                        <span>💳 + کارت بانکی</span>
                    </button>
                    <button type="button" wire:click="addSmartAttachment('iban', 'main')" class="px-2.5 py-1.5 text-[11px] font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/60 rounded-lg hover:bg-cyan-100 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🏦 + شماره شبا</span>
                    </button>
                    <button type="button" wire:click="addSmartAttachment('crypto_wallet', 'main')" class="px-2.5 py-1.5 text-[11px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 rounded-lg hover:bg-purple-100 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🪙 + کیف پول تتر/کریپتو</span>
                    </button>
                    <button type="button" wire:click="addSmartAttachment('url_button', 'main')" class="px-2.5 py-1.5 text-[11px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 rounded-lg hover:bg-amber-100 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🔗 + دکمه لینک</span>
                    </button>
                </div>
            </div>

            @if(!empty($smart_attachments))
                <div class="space-y-3 pt-2">
                    @foreach($smart_attachments as $index => $att)
                        <div wire:key="att-main-{{ $index }}" class="p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative group">
                            <button type="button" wire:click="removeSmartAttachment({{ $index }}, 'main')" class="absolute top-3 left-3 text-slate-400 hover:text-rose-500 p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all" title="حذف پیوست">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>

                            @if(($att['type'] ?? '') === 'bank_card')
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">کارت بانکی</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">شماره کارت (۱۶ رقم)</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.card_number" placeholder="6274121776068280" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">نام بانک (اختیاری)</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.bank_name" placeholder="مثلاً: ملی، اقتصاد نوین..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none focus:border-indigo-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">نام صاحب حساب</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.card_holder" placeholder="مثلاً: علی محمدی" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none focus:border-indigo-500" />
                                    </div>
                                </div>
                            @elseif(($att['type'] ?? '') === 'iban')
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 border border-cyan-200/60 dark:border-cyan-800">شماره شبا (IBAN)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">شماره شبا (با IR)</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.iban_code" placeholder="IR700550332144407818976001" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">نام صاحب حساب</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.account_holder" placeholder="مثلاً: علی محمدی" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none focus:border-indigo-500" />
                                    </div>
                                </div>
                            @elseif(($att['type'] ?? '') === 'crypto_wallet')
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">کیف پول دیجیتال</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">نام ارز</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.currency" placeholder="USDT" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white font-bold uppercase outline-none focus:border-indigo-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">شبکه (Network)</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.network" placeholder="TRC20" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white font-bold uppercase outline-none focus:border-indigo-500" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">آدرس کیف پول (Wallet Address)</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.address" placeholder="300 USDT or 0x..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                                    </div>
                                </div>
                            @elseif(($att['type'] ?? '') === 'url_button')
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">دکمه لینک / اقدام</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">عنوان روی دکمه</label>
                                        <input type="text" wire:model="smart_attachments.{{ $index }}.button_label" placeholder="مثلاً: ورود به صف نوبت‌ها" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none focus:border-indigo-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">آدرس اینترنتی (URL)</label>
                                        <input type="url" wire:model="smart_attachments.{{ $index }}.button_url" placeholder="https://..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Answer Type Selection Radio Cards -->
        <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">انتخاب نوع ساختار پاسخ</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Option 1: Text -->
                <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border transition-all duration-200 {{ $answer_type === 'text' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-500/10 shadow-sm' : 'border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 hover:border-slate-300' }}">
                    <input type="radio" wire:model.live="answer_type" value="text" class="sr-only">
                    <div class="flex items-center justify-between w-full mb-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                        </div>
                        <div class="w-4 h-4 rounded-full border border-slate-300 dark:border-slate-600 flex items-center justify-center {{ $answer_type === 'text' ? 'border-indigo-600 bg-indigo-600' : '' }}">
                            @if($answer_type === 'text')
                                <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">پاسخ متنی ساده</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">ارائه فقط متن آماده در پیام</span>
                </label>

                <!-- Option 2: Product List -->
                <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border transition-all duration-200 {{ $answer_type === 'product_list' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-500/10 shadow-sm' : 'border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 hover:border-slate-300' }}">
                    <input type="radio" wire:model.live="answer_type" value="product_list" class="sr-only">
                    <div class="flex items-center justify-between w-full mb-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </div>
                        <div class="w-4 h-4 rounded-full border border-slate-300 dark:border-slate-600 flex items-center justify-center {{ $answer_type === 'product_list' ? 'border-indigo-600 bg-indigo-600' : '' }}">
                            @if($answer_type === 'product_list')
                                <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">لیست کالا و محصولات</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">نمایش اسلایدر یا لیست محصولات</span>
                </label>

                <!-- Option 3: Menu Items -->
                <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border transition-all duration-200 {{ $answer_type === 'menu_items' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-500/10 shadow-sm' : 'border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 hover:border-slate-300' }}">
                    <input type="radio" wire:model.live="answer_type" value="menu_items" class="sr-only">
                    <div class="flex items-center justify-between w-full mb-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        </div>
                        <div class="w-4 h-4 rounded-full border border-slate-300 dark:border-slate-600 flex items-center justify-center {{ $answer_type === 'menu_items' ? 'border-indigo-600 bg-indigo-600' : '' }}">
                            @if($answer_type === 'menu_items')
                                <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">منوی شرطی / دکمه‌ها</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">نمایش دکمه‌های گزینه‌ای چندسطحی</span>
                </label>
            </div>
        </div>

        <!-- Conditional Dynamic View: Product List Picker -->
        @if($answer_type === 'product_list')
            <div class="mt-6 p-6 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">انتخاب محصولات مرتبط پاسخ</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">محصولاتی که می‌خواهید زیر پاسخ نمایش داده شوند را انتخاب کنید.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="show_add_to_cart" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">نمایش دکمه خرید سریع</span>
                        </label>
                    </div>
                </div>

                <!-- Product Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="productSearchQuery"
                        placeholder="جستجو (کد سیستم، عنوان، بارکد...)"
                        class="px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none"
                    />
                    <select wire:model.live="productBrandId" class="px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-200 outline-none">
                        <option value="">همه برندها</option>
                        @foreach($brands as $b)
                            <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="productCategoryId" class="px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-200 outline-none">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="productDisplayCategoryId" class="px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-200 outline-none">
                        <option value="">همه دسته‌های نمایش</option>
                        @foreach($displayCategories as $dc)
                            <option value="{{ $dc['id'] }}">{{ $dc['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Actions -->
                <div class="flex items-center justify-between text-xs pt-2">
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="selectAllFilteredProducts('main')" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                            انتخاب همه نتیجه‌های فیلتر شده
                        </button>
                        <span class="text-slate-300 dark:text-slate-700">|</span>
                        <button type="button" wire:click="deselectAllProducts('main')" class="text-rose-500 font-bold hover:underline">
                            پاک کردن همه انتخاب‌ها
                        </button>
                    </div>
                    <span class="font-bold text-slate-500 dark:text-slate-400">
                        تعداد انتخاب شده: <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ count($selected_product_ids) }}</span>
                    </span>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-1 custom-scrollbar">
                    @forelse($this->filteredProducts as $prod)
                        @php $isSelected = in_array($prod['id'], array_map('intval', $selected_product_ids)); @endphp
                        <label class="flex items-center gap-3 p-3 rounded-xl border transition-all cursor-pointer {{ $isSelected ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-900 hover:border-slate-300' }}">
                            <input
                                type="checkbox"
                                wire:model.live="selected_product_ids"
                                value="{{ $prod['id'] }}"
                                class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $prod['title'] }}</div>
                                <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span>کد: {{ $prod['crm_code'] ?? $prod['id'] }}</span>
                                    @if($prod['brand_name'])
                                        <span>• {{ $prod['brand_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-full py-8 text-center text-xs text-slate-400">محصولی با این مشخصات یافت نشد.</div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- Conditional Dynamic View: Menu Items Builder -->
        @if($answer_type === 'menu_items')
            <div class="mt-6 p-6 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">مدیریت ساختار منوی شرطی (N-سطحی)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">دکمه‌های انتخابی پاسخگو را تعیین کنید.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="openMenuItemDrawer(null, null)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-md transition-all active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>افزودن دکمه اصلی به منو</span>
                    </button>
                </div>

                @if(!$editingAnswerId)
                    <div class="p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>نکته: با کلیک روی افزودن دکمه، اطلاعات پاسخ به‌صورت خودکار ذخیره موقت می‌شود.</span>
                    </div>
                @endif

                <!-- Root Menu Items List -->
                <div class="space-y-3">
                    @forelse($menuItems as $item)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm space-y-3">
                            <!-- Parent Item Row -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                                        {{ $item['sort_order'] }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $item['label'] }}</span>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $item['response_type'] === 'menu_items' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                                {{ $item['response_type'] === 'menu_items' ? 'پوشه زیرمنو' : ($item['response_type'] === 'product_list' ? 'لیست کالاها' : ($item['response_type'] === 'url' ? 'لینک مجزا' : 'پاسخ متنی')) }}
                                            </span>
                                            @if(!$item['is_active'])
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">غیرفعال</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($item['response_type'] === 'menu_items')
                                        <button
                                            type="button"
                                            wire:click="openMenuItemDrawer(null, {{ $item['id'] }})"
                                            class="px-2.5 py-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 rounded-lg transition-all"
                                        >
                                            + زیردکمه
                                        </button>
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="openMenuItemDrawer({{ $item['id'] }}, null)"
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteMenuItem({{ $item['id'] }})"
                                        wire:confirm="آیا از حذف این دکمه و زیرمجموعه‌های آن اطمینان دارید؟"
                                        class="p-1.5 text-slate-500 hover:text-rose-600 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Children Recursive Render -->
                            @if(!empty($item['children']))
                                <div class="pr-6 space-y-2 border-r-2 border-slate-100 dark:border-slate-800 mr-3">
                                    @foreach($item['children'] as $child)
                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">↳ {{ $child['label'] }}</span>
                                                <span class="text-[10px] text-slate-400">({{ $child['response_type'] }})</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    wire:click="openMenuItemDrawer({{ $child['id'] }}, {{ $item['id'] }})"
                                                    class="p-1 text-slate-400 hover:text-indigo-600 transition-colors"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="deleteMenuItem({{ $child['id'] }})"
                                                    wire:confirm="از حذف این زیردکمه اطمینان دارید؟"
                                                    class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
                            هنوز دکمه‌ای برای این پاسخ ثبت نشده است. روی «افزودن دکمه اصلی به منو» کلیک کنید.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <!-- Slide-over / Side Drawer for Menu Item Management -->
    @if($isMenuItemDrawerOpen)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); $cleanup(() => document.body.classList.remove('overflow-hidden'))" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                wire:click="closeMenuItemDrawer()"
            ></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-xl bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col">
                    <!-- Drawer Header -->
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                    {{ $editingMenuItemId ? 'ویرایش دکمه منو' : 'افزودن دکمه منو جدید' }}
                                </h3>
                                @if($this->parentItemLabel)
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">در حال افزودن زیرمجموعه به: {{ $this->parentItemLabel }}</p>
                                @endif
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeMenuItemDrawer()"
                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Drawer Body (Scrollable) -->
                    <div class="flex-1 p-6 space-y-6 overflow-y-auto custom-scrollbar">
                        <!-- Label & Icon & Order -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2 space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">عنوان دکمه (Label) <span class="text-rose-500">*</span></label>
                                <input
                                    type="text"
                                    wire:model="menuItemLabel"
                                    placeholder="مثلا: پیگیری سفارش، تماس با پشتیبانی"
                                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                                @error('menuItemLabel') <span class="text-xs text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">ترتیب نمایش</label>
                                <input
                                    type="number"
                                    wire:model="menuItemSortOrder"
                                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">آیکون (اختیاری - کلاس)</label>
                                <input
                                    type="text"
                                    wire:model="menuItemIcon"
                                    placeholder="heroicon-o-shopping-bag"
                                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Response Type -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">نوع پاسخ هنگام کلیک روی این دکمه</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer text-xs font-bold {{ $menuItemResponseType === 'text' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    <input type="radio" wire:model.live="menuItemResponseType" value="text" class="sr-only">
                                    <span>پاسخ متنی</span>
                                </label>

                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer text-xs font-bold {{ $menuItemResponseType === 'menu_items' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    <input type="radio" wire:model.live="menuItemResponseType" value="menu_items" class="sr-only">
                                    <span>نمایش زیرمنو (N-سطحی)</span>
                                </label>

                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer text-xs font-bold {{ $menuItemResponseType === 'product_list' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    <input type="radio" wire:model.live="menuItemResponseType" value="product_list" class="sr-only">
                                    <span>نمایش لیست محصولات</span>
                                </label>

                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer text-xs font-bold {{ $menuItemResponseType === 'url' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    <input type="radio" wire:model.live="menuItemResponseType" value="url" class="sr-only">
                                    <span>هدایت به لینک (URL)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Response Type Inputs -->
                        @if($menuItemResponseType === 'text')
                            <div x-data="{ expanded: false }" x-effect="document.body.classList.toggle('overflow-hidden', expanded)" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        متن پاسخ دکمه <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[11px] font-bold text-slate-400">
                                            {{ mb_strlen($menuItemResponseText) }} کاراکتر
                                        </span>
                                        <button
                                            type="button"
                                            @click="expanded = true"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-2.5 py-1 rounded-lg transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                                            <span>بزرگ‌نمایی ویرایشگر</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Quick Insert Toolbar (Drawer) -->
                                <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                    <span class="text-[10px] text-slate-400 pl-1 border-l border-slate-300 dark:border-slate-700 ml-1 shrink-0">درج سریع المان هوشمند:</span>
                                    <button type="button" @click="openInsertModal('bank_card', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-emerald-500 hover:text-emerald-600 transition-all cursor-pointer">💳 کارت</button>
                                    <button type="button" @click="openInsertModal('iban', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-sky-500 hover:text-sky-600 transition-all cursor-pointer">🏦 شبا</button>
                                    <button type="button" @click="openInsertModal('crypto', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-purple-500 hover:text-purple-600 transition-all cursor-pointer">🪙 کریپتو</button>
                                    <button type="button" @click="openInsertModal('copy', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer">📋 کپی</button>
                                    <button type="button" @click="openInsertModal('phone', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-emerald-500 hover:text-emerald-600 transition-all cursor-pointer">📞 تماس</button>
                                    <button type="button" @click="openInsertModal('button', 'drawer_menu_item_textarea')" class="px-2 py-0.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded hover:border-amber-500 hover:text-amber-600 transition-all cursor-pointer">🔗 لینک</button>
                                </div>

                                <textarea
                                    id="drawer_menu_item_textarea"
                                    wire:model="menuItemResponseText"
                                    rows="6"
                                    placeholder="متنی که با کلیک کاربر روی این دکمه ارسال می‌شود..."
                                    class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 leading-relaxed resize-y custom-scrollbar"
                                ></textarea>

                                <!-- Teleported Fullscreen Overlay -->
                                <template x-teleport="body">
                                    <div x-show="expanded" class="fixed inset-0 z-[9999] bg-slate-900/80 backdrop-blur-md p-4 sm:p-8 flex items-center justify-center font-iranYekan" x-cloak>
                                        <div class="w-full max-w-5xl h-[88vh] bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col gap-4">
                                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3.5 h-3.5 rounded-full bg-indigo-500"></span>
                                                    <span class="text-sm font-bold text-slate-900 dark:text-white">ویرایشگر متنی پاسخ دکمه (تمام‌صفحه)</span>
                                                    <span class="text-xs font-bold text-slate-400 mr-4">{{ mb_strlen($menuItemResponseText) }} کاراکتر</span>
                                                </div>
                                                <button type="button" @click="expanded = false" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all">
                                                    تأیید و بستن ✕
                                                </button>
                                            </div>

                                            <!-- Fullscreen Toolbar (Drawer) -->
                                            <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                                <span class="text-[10px] text-slate-400 pl-1 border-l border-slate-300 dark:border-slate-700 ml-1 shrink-0">درج سریع المان هوشمند:</span>
                                                <button type="button" @click="openInsertModal('bank_card', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">💳 کارت بانکی</button>
                                                <button type="button" @click="openInsertModal('iban', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-sky-500 hover:text-sky-600 transition-all flex items-center gap-1 cursor-pointer">🏦 شماره شبا</button>
                                                <button type="button" @click="openInsertModal('crypto', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-purple-500 hover:text-purple-600 transition-all flex items-center gap-1 cursor-pointer">🪙 کریپتو</button>
                                                <button type="button" @click="openInsertModal('copy', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center gap-1 cursor-pointer">📋 باکس کپی</button>
                                                <button type="button" @click="openInsertModal('phone', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all flex items-center gap-1 cursor-pointer">📞 تماس</button>
                                                <button type="button" @click="openInsertModal('button', 'drawer_menu_item_textarea_fs')" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-amber-500 hover:text-amber-600 transition-all flex items-center gap-1 cursor-pointer">🔗 لینک</button>
                                            </div>

                                            <textarea
                                                id="drawer_menu_item_textarea_fs"
                                                wire:model="menuItemResponseText"
                                                placeholder="متن کامل پاسخ این دکمه را وارد کنید..."
                                                class="w-full flex-1 p-5 border border-slate-200 dark:border-slate-700/80 rounded-2xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-base outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 leading-relaxed resize-none custom-scrollbar"
                                            ></textarea>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Smart Attachments UI Section (Drawer / Menu Item) -->
                            <div class="p-4 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94a3 3 0 114.243 4.243L8.567 18.312a1.5 1.5 0 11-2.122-2.122l8.834-8.834"/></svg>
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">پیوست‌های هوشمند این دکمه</h4>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <button type="button" wire:click="addSmartAttachment('bank_card', 'drawer')" class="px-2 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 rounded-md hover:bg-emerald-100 transition-all flex items-center gap-1 cursor-pointer">
                                            <span>💳 + کارت بانکی</span>
                                        </button>
                                        <button type="button" wire:click="addSmartAttachment('iban', 'drawer')" class="px-2 py-1 text-[10px] font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/60 rounded-md hover:bg-cyan-100 transition-all flex items-center gap-1 cursor-pointer">
                                            <span>🏦 + شبا</span>
                                        </button>
                                        <button type="button" wire:click="addSmartAttachment('crypto_wallet', 'drawer')" class="px-2 py-1 text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 rounded-md hover:bg-purple-100 transition-all flex items-center gap-1 cursor-pointer">
                                            <span>🪙 + کریپتو</span>
                                        </button>
                                        <button type="button" wire:click="addSmartAttachment('url_button', 'drawer')" class="px-2 py-1 text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 rounded-md hover:bg-amber-100 transition-all flex items-center gap-1 cursor-pointer">
                                            <span>🔗 + لینک</span>
                                        </button>
                                    </div>
                                </div>

                                @if(!empty($menuItemSmartAttachments))
                                    <div class="space-y-2.5 pt-1">
                                        @foreach($menuItemSmartAttachments as $index => $att)
                                            <div wire:key="att-drawer-{{ $index }}" class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative group text-xs">
                                                <button type="button" wire:click="removeSmartAttachment({{ $index }}, 'drawer')" class="absolute top-2.5 left-2.5 text-slate-400 hover:text-rose-500 p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all" title="حذف پیوست">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>

                                                @if(($att['type'] ?? '') === 'bank_card')
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">کارت بانکی</span>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">شماره کارت</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.card_number" placeholder="6274..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">بانک</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.bank_name" placeholder="ملی" class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">صاحب حساب</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.card_holder" placeholder="نام..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none" />
                                                        </div>
                                                    </div>
                                                @elseif(($att['type'] ?? '') === 'iban')
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300">شماره شبا</span>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">شماره شبا</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.iban_code" placeholder="IR70..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">صاحب حساب</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.account_holder" placeholder="نام..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none" />
                                                        </div>
                                                    </div>
                                                @elseif(($att['type'] ?? '') === 'crypto_wallet')
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">کیف پول کریپتو</span>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">ارز/شبکه</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.currency" placeholder="USDT (TRC20)" class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none" />
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">آدرس کیف پول</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.address" placeholder="T9y..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none" />
                                                        </div>
                                                    </div>
                                                @elseif(($att['type'] ?? '') === 'url_button')
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">دکمه لینک</span>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">عنوان دکمه</label>
                                                            <input type="text" wire:model="menuItemSmartAttachments.{{ $index }}.button_label" placeholder="عنوان..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white outline-none" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">لینک (URL)</label>
                                                            <input type="url" wire:model="menuItemSmartAttachments.{{ $index }}.button_url" placeholder="https://..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-white dir-ltr text-right outline-none" />
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @elseif($menuItemResponseType === 'url')
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">آدرس اینترنتی (URL)</label>
                                <input
                                    type="url"
                                    wire:model="menuItemResponseUrl"
                                    placeholder="https://example.com/page"
                                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>
                        @elseif($menuItemResponseType === 'product_list')
                            <div class="space-y-4 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">انتخاب محصولات مربوط به این دکمه</div>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="productSearchQuery"
                                    placeholder="جستجوی سریع محصولات..."
                                    class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white"
                                />
                                <div class="max-h-48 overflow-y-auto space-y-2 custom-scrollbar pr-1">
                                    @foreach($this->filteredProducts as $prod)
                                        @php $isSelected = in_array($prod['id'], array_map('intval', $menuItemResponseEntityIds)); @endphp
                                        <label class="flex items-center gap-2 p-2 rounded-lg border text-xs cursor-pointer {{ $isSelected ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                                            <input
                                                type="checkbox"
                                                wire:model.live="menuItemResponseEntityIds"
                                                value="{{ $prod['id'] }}"
                                                class="rounded border-slate-300 text-indigo-600"
                                            />
                                            <span class="truncate font-bold text-slate-800 dark:text-slate-200">{{ $prod['title'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($menuItemResponseType === 'menu_items')
                            <div class="p-4 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20 text-xs text-amber-700 dark:text-amber-300">
                                این دکمه به عنوان پوشه زیرمنو عمل خواهد کرد. پس از ذخیره می‌توانید زیردکمه‌های آن را به این دکمه اضافه کنید.
                            </div>
                        @endif
                    </div>

                    <!-- Drawer Footer -->
                    <div class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeMenuItemDrawer()"
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl transition-all"
                        >
                            انصراف
                        </button>
                        <button
                            type="button"
                            wire:click="saveMenuItem()"
                            class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-md transition-all active:scale-95"
                        >
                            ذخیره دکمه منو
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Teleported Insert Smart Element Modal -->
    <template x-teleport="body">
        <div x-show="insertModalOpen" class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-md p-4 flex items-center justify-center font-iranYekan" x-cloak>
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col gap-4 text-right dir-rtl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <span x-text="activeTagType === 'bank_card' ? 'افزودن کارت بانکی به متن' : (activeTagType === 'iban' ? 'افزودن شماره شبا به متن' : (activeTagType === 'crypto' ? 'افزودن کیف پول کریپتو به متن' : (activeTagType === 'copy' ? 'افزودن باکس کپی متنی' : (activeTagType === 'phone' ? 'افزودن دکمه تماس' : 'افزودن دکمه لینک'))))"></span>
                    </h3>
                    <button type="button" @click="insertModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
                </div>

                <template x-if="activeTagType === 'bank_card'">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">شماره کارت (۱۶ رقم)</label>
                            <input type="text" x-model="card_number" placeholder="6274121776068280" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">نام بانک (اختیاری)</label>
                            <input type="text" x-model="bank_name" placeholder="مثلاً: ملی، پاسارگاد" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">نام صاحب حساب (اختیاری)</label>
                            <input type="text" x-model="card_holder" placeholder="مثلاً: علی محمدی" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <template x-if="activeTagType === 'iban'">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">شماره شبا (با IR)</label>
                            <input type="text" x-model="iban_code" placeholder="IR700550332144407818976001" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">نام صاحب حساب (اختیاری)</label>
                            <input type="text" x-model="account_holder" placeholder="مثلاً: علی محمدی" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <template x-if="activeTagType === 'crypto'">
                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">نام ارز</label>
                                <input type="text" x-model="crypto_currency" placeholder="USDT" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white uppercase font-bold outline-none focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">شبکه</label>
                                <input type="text" x-model="crypto_network" placeholder="TRC20" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white uppercase font-bold outline-none focus:border-indigo-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">آدرس کیف پول یا مقدار</label>
                            <input type="text" x-model="crypto_address" placeholder="300 USDT or 0x..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <template x-if="activeTagType === 'copy'">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان / برچسب</label>
                            <input type="text" x-model="copy_label" placeholder="مثلاً: کد تخفیف، کد سریال" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">متنی که باید کپی شود</label>
                            <input type="text" x-model="copy_text" placeholder="مثلاً: OFF50" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <template x-if="activeTagType === 'phone'">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">شماره تماس</label>
                            <input type="text" x-model="phone_number" placeholder="09123456789" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان روی دکمه (اختیاری)</label>
                            <input type="text" x-model="phone_label" placeholder="مثلاً: تماس با پشتیبانی" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <template x-if="activeTagType === 'button'">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان روی دکمه</label>
                            <input type="text" x-model="btn_label" placeholder="مثلاً: ورود به صف رزرو" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">آدرس اینترنتی (URL)</label>
                            <input type="url" x-model="btn_url" placeholder="https://example.com" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white dir-ltr text-right outline-none focus:border-indigo-500" />
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="insertModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-xl">انصراف</button>
                    <button type="button" @click="insertSmartTag()" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md">درج در متن</button>
                </div>
            </div>
        </div>
    </template>
</div>
