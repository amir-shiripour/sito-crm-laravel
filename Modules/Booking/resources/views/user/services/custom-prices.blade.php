@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .cp-root { direction: rtl; }
        .cp-page-bg { background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(99,102,241,.08) 0%, transparent 70%); }
        .cp-card {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(226,232,240,.8);
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 8px 32px rgba(0,0,0,.06);
        }
        .dark .cp-card { background: rgba(17,24,39,.75); border-color: rgba(55,65,81,.6); box-shadow: 0 1px 3px rgba(0,0,0,.3), 0 8px 32px rgba(0,0,0,.25); }
        .cp-tab-pill { position: relative; display: flex; align-items: center; gap: 4px; background: transparent; border-radius: 10px; transition: all .18s ease; }
        .cp-tab-btn { padding: 7px 16px; border-radius: 9px; font-size: .8125rem; font-weight: 500; letter-spacing: -.01em; transition: all .18s ease; white-space: nowrap; border: 1px solid transparent; cursor: pointer; }
        .cp-tab-btn.active { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #fff; border-color: rgba(99,102,241,.4); box-shadow: 0 2px 10px rgba(99,102,241,.35); }
        .dark .cp-tab-btn.active { background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); box-shadow: 0 2px 10px rgba(99,102,241,.4); }
        .cp-tab-btn.inactive { background: rgba(248,250,252,1); color: #64748b; border-color: rgba(226,232,240,1); }
        .cp-tab-btn.inactive:hover { background: rgba(241,245,249,1); color: #475569; border-color: rgba(203,213,225,1); }
        .dark .cp-tab-btn.inactive { background: rgba(30,41,59,.6); color: #94a3b8; border-color: rgba(51,65,85,.8); }
        .dark .cp-tab-btn.inactive:hover { background: rgba(30,41,59,.9); color: #cbd5e1; }
        .cp-tab-close { width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px; transition: all .15s; cursor: pointer; }
        .cp-tab-close:hover { background: rgba(239,68,68,.1); color: #ef4444; }
        .cp-input { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid rgba(226,232,240,1); background: rgba(248,250,252,1); color: #0f172a; font-size: .875rem; transition: border-color .15s, box-shadow .15s, background .15s; outline: none; }
        .cp-input::placeholder { color: #94a3b8; }
        .cp-input:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .dark .cp-input { border-color: rgba(51,65,85,.9); background: rgba(15,23,42,.6); color: #f1f5f9; }
        .dark .cp-input:focus { border-color: #818cf8; background: rgba(15,23,42,.9); box-shadow: 0 0 0 3px rgba(129,140,248,.15); }
        .cp-brand-row { display: grid; grid-template-columns: 1fr 0.6fr auto; gap: 10px; align-items: center; padding: 12px 14px; border-radius: 12px; background: rgba(248,250,252,.7); border: 1px solid rgba(226,232,240,.8); transition: border-color .15s, box-shadow .15s; }
        .cp-brand-row:hover { border-color: rgba(199,210,254,.8); box-shadow: 0 2px 8px rgba(99,102,241,.06); }
        .dark .cp-brand-row { background: rgba(15,23,42,.4); border-color: rgba(51,65,85,.7); }
        .dark .cp-brand-row:hover { border-color: rgba(99,102,241,.4); }
        .cp-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 10px; font-size: .8125rem; font-weight: 600; cursor: pointer; transition: all .18s ease; border: 1px solid transparent; letter-spacing: -.01em; }
        .cp-btn-emerald { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,.25); }
        .cp-btn-emerald:hover { background: linear-gradient(135deg, #047857 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16,185,129,.35); transform: translateY(-1px); }
        .cp-btn-indigo { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
        .cp-btn-indigo:hover { background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%); box-shadow: 0 4px 14px rgba(99,102,241,.4); transform: translateY(-1px); }
        .cp-btn-amber { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #fff; box-shadow: 0 2px 8px rgba(245,158,11,.25); }
        .cp-btn-amber:hover { background: linear-gradient(135deg, #b45309 0%, #d97706 100%); box-shadow: 0 4px 14px rgba(245,158,11,.35); transform: translateY(-1px); }
        .cp-btn-ghost-emerald { background: rgba(209,250,229,.7); color: #059669; border-color: rgba(167,243,208,.8); font-weight: 500; padding: 7px 14px; }
        .cp-btn-ghost-emerald:hover { background: rgba(167,243,208,.8); color: #047857; border-color: rgba(110,231,183,.9); }
        .dark .cp-btn-ghost-emerald { background: rgba(6,78,59,.25); color: #34d399; border-color: rgba(6,78,59,.5); }
        .dark .cp-btn-ghost-emerald:hover { background: rgba(6,78,59,.4); }
        .cp-btn-ghost-indigo { background: rgba(238,242,255,.7); color: #4f46e5; border-color: rgba(199,210,254,.8); font-weight: 500; padding: 7px 14px; }
        .cp-btn-ghost-indigo:hover { background: rgba(224,231,255,.8); color: #4338ca; border-color: rgba(165,180,252,.9); }
        .dark .cp-btn-ghost-indigo { background: rgba(49,46,129,.2); color: #818cf8; border-color: rgba(49,46,129,.4); }
        .cp-btn-delete { width: 32px; height: 32px; padding: 0; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(254,226,226,.6); color: #ef4444; border: 1px solid rgba(252,165,165,.5); font-size: 13px; cursor: pointer; transition: all .15s; font-family: sans-serif; }
        .cp-btn-delete:hover { background: #ef4444; color: #fff; border-color: #ef4444; box-shadow: 0 2px 8px rgba(239,68,68,.3); }
        .dark .cp-btn-delete { background: rgba(127,29,29,.2); border-color: rgba(239,68,68,.2); }
        .dark .cp-btn-delete:hover { background: #ef4444; border-color: #ef4444; }
        .cp-title-input-wrap { position: relative; }
        .cp-title-input-wrap::before { content: ''; position: absolute; inset: 0; border-radius: 11px; pointer-events: none; background: linear-gradient(90deg,rgba(99,102,241,.06) 0%,transparent 60%); }
        .cp-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 56px 24px; border-radius: 16px; border: 2px dashed rgba(203,213,225,.8); color: #94a3b8; }
        .dark .cp-empty { border-color: rgba(51,65,85,.7); color: #475569; }
        .cp-empty-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(248,250,252,1); border: 1.5px solid rgba(226,232,240,1); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .dark .cp-empty-icon { background: rgba(30,41,59,.6); border-color: rgba(51,65,85,.8); }
        .cp-section-label { font-size: .7rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
        .cp-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(226,232,240,.8) 20%, rgba(226,232,240,.8) 80%, transparent); margin: 4px 0; }
        .dark .cp-divider { background: linear-gradient(90deg, transparent, rgba(51,65,85,.6) 20%, rgba(51,65,85,.6) 80%, transparent); }
        @keyframes cpFadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .cp-fade-up { animation: cpFadeUp .22s ease forwards; }
        .cp-price-wrap { position: relative; }
        .cp-price-wrap::after { content: 'تومان'; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: .7rem; color: #94a3b8; pointer-events: none; }
        .cp-price-input { padding-left: 52px !important; }

        /* Section card */
        .cp-section-card { background: rgba(248,250,252,.5); border: 1.5px solid rgba(226,232,240,.9); border-radius: 14px; padding: 16px; }
        .dark .cp-section-card { background: rgba(15,23,42,.35); border-color: rgba(51,65,85,.7); }
        .cp-type-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(238,242,255,.9); color: #4f46e5; border: 1px solid rgba(199,210,254,.7); }
        .dark .cp-type-badge { background: rgba(49,46,129,.2); color: #818cf8; border-color: rgba(49,46,129,.4); }

        /* Modal */
        .cp-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(4px); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .cp-modal { background: #fff; border-radius: 20px; padding: 28px; width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08); animation: cpFadeUp .2s ease; }
        .dark .cp-modal { background: #1e293b; }
        .cp-select { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid rgba(226,232,240,1); background: rgba(248,250,252,1); color: #0f172a; font-size: .875rem; transition: border-color .15s, box-shadow .15s; outline: none; appearance: none; cursor: pointer; }
        .cp-select:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .dark .cp-select { border-color: rgba(51,65,85,.9); background: rgba(15,23,42,.6); color: #f1f5f9; }
        .dark .cp-select:focus { border-color: #818cf8; background: rgba(15,23,42,.9); }
        .cp-select-wrap { position: relative; }
        .cp-select-wrap::after { content: '▾'; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: .75rem; }
    </style>

    <div
        x-data="customPricesApp(@js($customPrices['tabs'] ?? []))"
        x-cloak
        class="cp-root cp-page-bg max-w-4xl mx-auto py-8 px-4"
    >
        {{-- ══════════════ HEADER ══════════════ --}}
        <div class="cp-card rounded-2xl px-6 py-5 mb-6 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0"
                     style="background: linear-gradient(135deg,#4f46e5,#6366f1); box-shadow: 0 4px 14px rgba(99,102,241,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-semibold text-slate-900 dark:text-white leading-tight">تنظیمات قیمت سفارشی</h1>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">تب بساز، عنوان اضافه کن، برند و قیمت تعیین کن</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="addTab()" class="cp-btn cp-btn-emerald">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    تب جدید
                </button>
                <button type="button" @click="$refs.form.submit()" class="cp-btn cp-btn-indigo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    ذخیره تنظیمات
                </button>
            </div>
        </div>

        <form x-ref="form" method="POST" action="{{ route('user.booking.services.custom-prices.update', $service->id) }}" class="space-y-5">
            @csrf

            {{-- ══════════════ TAB BAR ══════════════ --}}
            <div class="cp-card rounded-2xl px-4 py-3">
                <div class="flex gap-2 overflow-x-auto scrollbar-none pb-1">
                    <template x-for="(tab, tIndex) in tabs" :key="tIndex">
                        <div class="cp-tab-pill shrink-0">
                            <button type="button" @click="activeTab = tIndex" class="cp-tab-btn"
                                    :class="activeTab === tIndex ? 'active' : 'inactive'"
                                    x-text="tab.title || 'تب بدون عنوان'"></button>
                            <button type="button" @click="removeTab(tIndex)" class="cp-tab-close" title="حذف تب">✕</button>
                        </div>
                    </template>
                    <template x-if="tabs.length === 0">
                        <span class="text-xs text-slate-400 dark:text-slate-600 py-2 px-1 self-center">
                            هنوز تبی نساخته‌ای — دکمه «تب جدید» را بزن
                        </span>
                    </template>
                </div>
            </div>

            {{-- ══════════════ EMPTY STATE ══════════════ --}}
            <template x-if="tabs.length === 0">
                <div class="cp-empty cp-fade-up">
                    <div class="cp-empty-icon">🗂</div>
                    <div class="text-sm font-medium text-slate-500 dark:text-slate-600">هنوز هیچ تبی ساخته نشده</div>
                    <button type="button" @click="addTab()" class="cp-btn cp-btn-emerald mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        اولین تب را بساز
                    </button>
                </div>
            </template>

            {{-- ══════════════ TAB CONTENT ══════════════ --}}
            <template x-for="(tab, tIndex) in tabs" :key="tIndex">
                <div x-show="activeTab === tIndex" class="cp-card rounded-2xl p-6 space-y-5 cp-fade-up">

                    {{-- Tab title --}}
                    <div class="space-y-1.5">
                        <label class="cp-section-label">عنوان تب</label>
                        <div class="cp-title-input-wrap">
                            <input type="text" x-model="tab.title" :name="`tabs[${tIndex}][title]`"
                                   placeholder="مثلاً: ایمپلنت، کامپوزیت، لمینت…" class="cp-input"/>
                        </div>
                    </div>

                    <div class="cp-divider"></div>

                    {{-- Sections --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="cp-section-label">عنوان‌ها و برندها</label>
                            <button type="button" @click="openSectionModal(tIndex)" class="cp-btn cp-btn-amber" style="padding:7px 14px; font-size:.8rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                افزودن عنوان
                            </button>
                        </div>

                        {{-- Empty sections --}}
                        <template x-if="tab.sections.length === 0">
                            <div class="cp-empty" style="padding:32px 24px;">
                                <div style="font-size:1.5rem;">📋</div>
                                <div class="text-xs text-slate-400 dark:text-slate-600">هنوز عنوانی اضافه نشده</div>
                            </div>
                        </template>

                        {{-- Section cards --}}
                        <template x-for="(section, sIndex) in tab.sections" :key="sIndex">
                            <div class="cp-section-card cp-fade-up space-y-4">

                                {{-- Section header --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="section.title || 'عنوان بدون نام'"></span>
                                        <span class="cp-type-badge" x-text="section.type"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="openSectionModal(tIndex, sIndex)" class="cp-btn cp-btn-ghost-indigo" style="padding:5px 12px; font-size:.75rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                                            ویرایش
                                        </button>
                                        <button type="button" @click="removeSection(tIndex, sIndex)" class="cp-btn-delete" title="حذف عنوان">✕</button>
                                    </div>
                                </div>

                                {{-- Hidden inputs for section --}}
                                <input type="hidden" :name="`tabs[${tIndex}][sections][${sIndex}][title]`" :value="section.title"/>
                                <input type="hidden" :name="`tabs[${tIndex}][sections][${sIndex}][type]`" :value="section.type"/>

                                <div class="cp-divider"></div>

                                {{-- Brands --}}
                                <div class="space-y-2">
                                    <template x-if="section.brands.length > 0">
                                        <div style="display: grid; grid-template-columns: 1fr 0.6fr auto; gap: 10px; padding: 0 4px;">
                                            <span class="text-xs text-slate-400 dark:text-slate-600">نام برند</span>
                                            <span class="text-xs text-slate-400 dark:text-slate-600">قیمت</span>
                                            <span class="w-8"></span>
                                        </div>
                                    </template>

                                    <template x-for="(brand, bIndex) in section.brands" :key="bIndex">
                                        <div class="cp-brand-row cp-fade-up">
                                            <input type="text" x-model="brand.name"
                                                   :name="`tabs[${tIndex}][sections][${sIndex}][brands][${bIndex}][name]`"
                                                   placeholder="نام برند" class="cp-input"/>
                                            <div class="cp-price-wrap">
                                                <input type="number" x-model="brand.price"
                                                       :name="`tabs[${tIndex}][sections][${sIndex}][brands][${bIndex}][price]`"
                                                       placeholder="۰" class="cp-input cp-price-input"/>
                                            </div>
                                            <button type="button" @click="removeBrand(tIndex, sIndex, bIndex)" class="cp-btn-delete" title="حذف برند">✕</button>
                                        </div>
                                    </template>

                                    <button type="button" @click="addBrand(tIndex, sIndex)" class="cp-btn cp-btn-ghost-emerald">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        افزودن برند
                                    </button>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>
            </template>
        </form>

        {{-- ══════════════ SECTION MODAL ══════════════ --}}
        <template x-if="showSectionModal">
            <div class="cp-modal-backdrop" @click.self="showSectionModal = false">
                <div class="cp-modal">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white"
                            x-text="modalSectionIndex !== null ? 'ویرایش عنوان' : 'افزودن عنوان جدید'"></h2>
                        <button type="button" @click="showSectionModal = false"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-sm">✕</button>
                    </div>

                    <div class="space-y-4">
                        {{-- Title --}}
                        <div class="space-y-1.5">
                            <label class="cp-section-label">عنوان</label>
                            <input type="text" x-model="modalTitle" placeholder="عنوان را وارد کنید" class="cp-input"
                                   @keydown.enter.prevent="saveSectionModal()"/>
                        </div>

                        {{-- Type --}}
                        <div class="space-y-1.5">
                            <label class="cp-section-label">نوع</label>
                            <div class="cp-select-wrap">
                                <select x-model="modalType" class="cp-select">
                                    <option value="" disabled>نوع را انتخاب کنید</option>
                                    <template x-for="opt in typeOptions" :key="opt">
                                        <option :value="opt" x-text="opt"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-7 justify-end">
                        <button type="button" @click="showSectionModal = false"
                                class="cp-btn" style="background:rgba(241,245,249,1);color:#64748b;border:1px solid rgba(226,232,240,1);">
                            انصراف
                        </button>
                        <button type="button" @click="saveSectionModal()"
                                :disabled="!modalTitle || !modalType"
                                class="cp-btn cp-btn-indigo"
                                :style="(!modalTitle || !modalType) ? 'opacity:.5;cursor:not-allowed;transform:none' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span x-text="modalSectionIndex !== null ? 'ذخیره تغییرات' : 'افزودن'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
        function customPricesApp(initialTabs) {
            return {
                tabs: Array.isArray(initialTabs) ? initialTabs.map(t => ({
                    ...t,
                    sections: Array.isArray(t.sections) ? t.sections : []
                })) : [],
                activeTab: 0,

                // Modal state
                showSectionModal: false,
                modalTabIndex: null,
                modalSectionIndex: null,
                modalTitle: '',
                modalType: '',

                typeOptions: [
                    'چند گزینه ای به ازای دندان',
                    'دو گزینه ای دارد یا ندارد',
                    'به ازای هر واحد',
                    'به ازای هردندان',
                    'به ازای هر فک',
                    'به ازای کوادران',
                    'چند گزینه ای',
                    'به ازای واحد دندان دار',
                ],

                addTab() {
                    this.tabs.push({ title: '', sections: [] });
                    this.activeTab = this.tabs.length - 1;
                },

                removeTab(index) {
                    this.tabs.splice(index, 1);
                    if (this.activeTab >= this.tabs.length) this.activeTab = this.tabs.length - 1;
                    if (this.activeTab < 0) this.activeTab = 0;
                },

                openSectionModal(tabIndex, sectionIndex = null) {
                    this.modalTabIndex = tabIndex;
                    this.modalSectionIndex = sectionIndex;
                    if (sectionIndex !== null) {
                        const s = this.tabs[tabIndex].sections[sectionIndex];
                        this.modalTitle = s.title;
                        this.modalType  = s.type;
                    } else {
                        this.modalTitle = '';
                        this.modalType  = '';
                    }
                    this.showSectionModal = true;
                },

                saveSectionModal() {
                    if (!this.modalTitle || !this.modalType) return;
                    const tab = this.tabs[this.modalTabIndex];
                    if (this.modalSectionIndex !== null) {
                        tab.sections[this.modalSectionIndex].title = this.modalTitle;
                        tab.sections[this.modalSectionIndex].type  = this.modalType;
                    } else {
                        tab.sections.push({ title: this.modalTitle, type: this.modalType, brands: [] });
                    }
                    this.showSectionModal = false;
                },

                removeSection(tabIndex, sectionIndex) {
                    this.tabs[tabIndex].sections.splice(sectionIndex, 1);
                },

                addBrand(tabIndex, sectionIndex) {
                    this.tabs[tabIndex].sections[sectionIndex].brands.push({ name: '', price: '' });
                },

                removeBrand(tabIndex, sectionIndex, brandIndex) {
                    this.tabs[tabIndex].sections[sectionIndex].brands.splice(brandIndex, 1);
                },
            }
        }
    </script>
@endsection

