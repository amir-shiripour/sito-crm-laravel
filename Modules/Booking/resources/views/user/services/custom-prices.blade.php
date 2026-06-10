@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .cp-root { direction: rtl; }

        .cp-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.05);
        }
        .dark .cp-card { background: #1e293b; border-color: #334155; }

        .tab-rail { display:flex; gap:4px; overflow-x:auto; padding-bottom:2px; scrollbar-width:none; }
        .tab-rail::-webkit-scrollbar { display:none; }

        .tab-pill {
            display: inline-flex; align-items: center; gap: 0;
            border-radius: 10px; border: 1.5px solid transparent;
            overflow: hidden; transition: all .15s; white-space: nowrap; flex-shrink: 0;
        }
        .tab-pill.active {background: linear-gradient(135deg,#4f46e5,#6366f1); border-color: rgba(99,102,241,.3); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
        .tab-pill.inactive { background: #f8fafc; border-color: #e2e8f0; }
        .dark .tab-pill.inactive { background: #1e293b; border-color: #334155; }
        .tab-pill.inactive:hover { background: #f1f5f9; border-color: #cbd5e1; }
        .dark .tab-pill.inactive:hover { background: #273548; border-color: #475569; }

        .tab-pill-label { padding: 8px 14px; font-size: .8125rem; font-weight: 600; cursor: pointer; background: transparent; border: none; transition: color .15s; }
        .tab-pill.active .tab-pill-label   { color: #fff; }
        .tab-pill.inactive .tab-pill-label { color: #64748b; }
        .dark .tab-pill.inactive .tab-pill-label { color: #94a3b8; }

        .tab-pill-close {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; margin-right: 4px;
            border-radius: 6px; font-size: 11px; cursor: pointer; border: none; background: transparent; transition: background .12s, color .12s;
        }
        .tab-pill.active .tab-pill-close        { color: rgba(255,255,255,.6); }
        .tab-pill.active .tab-pill-close:hover  { background: rgba(255,255,255,.18); color: #fff; }
        .tab-pill.inactive .tab-pill-close      { color: #94a3b8; }
        .tab-pill.inactive .tab-pill-close:hover { background: #fee2e2; color: #ef4444; }

        .cp-input {
            width: 100%; padding: 9px 13px;
            border-radius: 10px; border: 1.5px solid #e2e8f0;
            background: #f8fafc; color: #0f172a; font-size: .875rem;
            transition: border-color .15s, box-shadow .15s, background .15s; outline: none;
        }
        .cp-input::placeholder { color: #94a3b8; }
        .cp-input:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .dark .cp-input { border-color: #334155; background: #0f172a; color: #f1f5f9; }
        .dark .cp-input:focus { border-color: #818cf8; box-shadow: 0 0 0 3px rgba(129,140,248,.12); }

        .cp-select {
            width: 100%; padding: 9px 36px 9px 13px;
            border-radius: 10px; border: 1.5px solid #e2e8f0;
            background: #f8fafc; color: #0f172a; font-size: .875rem;
            transition: border-color .15s, box-shadow .15s; outline: none;
            cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: left 12px center;
        }
        .cp-select:focus { border-color: #6366f1; background-color: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .dark .cp-select { border-color: #334155; background-color: #0f172a; color: #f1f5f9; }

        .section-card { border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; transition: border-color .15s; }
        .section-card:hover { border-color: #c7d2fe; }
        .dark .section-card { border-color: #334155; }
        .dark .section-card:hover { border-color: rgba(99,102,241,.4); }

        .section-header {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 11px 15px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;
        }
        .dark .section-header { background: #0f172a; border-color: #334155; }

        .brand-row {
            display: grid; grid-template-columns: 1fr 0.52fr auto;
            gap: 8px; align-items: center;
            padding: 9px 14px; border-bottom: 1px solid #f1f5f9; transition: background .12s;
        }
        .brand-row:last-of-type { border-bottom: none; }
        .brand-row:hover { background: #fafbff; }
        .dark .brand-row { border-color: #1e293b; }
        .dark .brand-row:hover { background: rgba(15,23,42,.5); }

        .price-wrap { position: relative; }
        .price-wrap::after { content:'تومان'; position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:.7rem; color:#94a3b8; pointer-events:none; }
        .price-input { padding-left: 48px !important; }

        .btn { display:inline-flex; align-items:center; gap:6px; border-radius:10px; font-size:.8125rem; font-weight:600; cursor:pointer; transition:all .15s; border:1.5px solid transparent; padding:8px 16px; }
        .btn-indigo { background:linear-gradient(135deg,#4f46e5,#6366f1); color:#fff; box-shadow:0 2px 8px rgba(99,102,241,.3); }
        .btn-indigo:hover { background:linear-gradient(135deg,#4338ca,#4f46e5); box-shadow:0 4px 12px rgba(99,102,241,.4); transform:translateY(-1px); }
        .btn-emerald { background:linear-gradient(135deg,#059669,#10b981); color:#fff; box-shadow:0 2px 8px rgba(16,185,129,.25); }
        .btn-emerald:hover { background:linear-gradient(135deg,#047857,#059669); box-shadow:0 4px 12px rgba(16,185,129,.35); transform:translateY(-1px); }
        .btn-amber { background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; box-shadow:0 2px 8px rgba(245,158,11,.22); }
        .btn-amber:hover { background:linear-gradient(135deg,#b45309,#d97706); transform:translateY(-1px); }
        .btn-ghost { background:#f8fafc; color:#64748b; border-color:#e2e8f0; }
        .btn-ghost:hover { background:#f1f5f9; color:#475569; }
        .dark .btn-ghost { background:#1e293b; color:#94a3b8; border-color:#334155; }
        .btn-ghost-green { background:rgba(209,250,229,.7); color:#059669; border-color:rgba(167,243,208,.8); padding:6px 12px; }
        .btn-ghost-green:hover { background:rgba(167,243,208,.9); color:#047857; }
        .dark .btn-ghost-green { background:rgba(6,78,59,.25); color:#34d399; border-color:rgba(6,78,59,.5); }
        .btn-ghost-indigo { background:rgba(238,242,255,.8); color:#4f46e5; border-color:rgba(199,210,254,.8); padding:5px 10px; font-size:.75rem; }
        .btn-ghost-indigo:hover { background:rgba(224,231,255,.9); color:#4338ca; }
        .dark .btn-ghost-indigo { background:rgba(49,46,129,.2); color:#818cf8; border-color:rgba(49,46,129,.4); }
        .btn-del { width:28px; height:28px; padding:0; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:rgba(254,226,226,.6); color:#ef4444; border:1px solid rgba(252,165,165,.5); cursor:pointer; transition:all .15s; font-size:11px; }
        .btn-del:hover { background:#ef4444; color:#fff; border-color:#ef4444; }
        .dark .btn-del { background:rgba(127,29,29,.2); border-color:rgba(239,68,68,.2); }

        .type-badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:20px; font-size:.68rem; font-weight:600; background:rgba(238,242,255,.9); color:#4f46e5; border:1px solid rgba(199,210,254,.7); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .dark .type-badge { background:rgba(49,46,129,.25); color:#818cf8; border-color:rgba(49,46,129,.4); }

        .sec-label { font-size:.68rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; }
        .divider { height:1px; background:linear-gradient(90deg,transparent,#e2e8f0 20%,#e2e8f0 80%,transparent); margin:4px 0; }
        .dark .divider { background:linear-gradient(90deg,transparent,#334155 20%,#334155 80%,transparent); }

        .empty-box { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:36px 24px; border:2px dashed #e2e8f0; border-radius:14px; text-align:center; }
        .dark .empty-box { border-color:#334155; }

        .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.4); backdrop-filter:blur(4px); z-index:100; display:flex; align-items:center; justify-content:center; padding:16px; }
        .modal-box { background:#fff; border-radius:20px; padding:28px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.15),0 4px 16px rgba(0,0,0,.08); animation:fadeUp .18s ease; }
        .dark .modal-box { background:#1e293b; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .anim-fade { animation:fadeUp .2s ease forwards; }

        .sc-thin::-webkit-scrollbar{width:4px;height:4px}
        .sc-thin::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:2px}
        .dark .sc-thin::-webkit-scrollbar-thumb{background:#475569}
    </style>

    <div
        x-data="customPricesApp(@js($customPrices['tabs'] ?? []))"
        x-cloak
        class="cp-root max-w-4xl mx-auto py-8 px-4 space-y-5"
    >

        {{-- ══ HEADER ══ --}}
        <div class="cp-card px-6 py-5 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl shrink-0 flex items-center justify-center"
                     style="background:linear-gradient(135deg,#4f46e5,#6366f1);box-shadow:0 4px 14px rgba(99,102,241,.3);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-gray-900 dark:text-white">قیمت‌گذاری پیشرفته</h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $service->name }} — تب، عنوان، برند و قیمت</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="addTab()" class="btn btn-emerald">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    تب جدید
                </button>
                <button type="button" @click="$refs.form.submit()" class="btn btn-indigo">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    ذخیره
                </button>
            </div>
        </div>

        <form x-ref="form" method="POST"
              action="{{ route('user.booking.services.custom-prices.update', $service->id) }}"
              class="space-y-4">
            @csrf

            {{-- ══ TAB BAR ══ --}}
            <div class="cp-card px-4 py-3">
                <div class="tab-rail sc-thin">
                    <template x-for="(tab, tIdx) in tabs" :key="tIdx">
                        <div :class="activeTab === tIdx ? 'tab-pill active' : 'tab-pill inactive'">
                            <button type="button" @click="activeTab = tIdx" class="tab-pill-label"
                                    x-text="tab.title || 'تب بدون نام'"></button>
                            <button type="button" @click.stop="removeTab(tIdx)" class="tab-pill-close">✕</button>
                        </div>
                    </template>
                    <template x-if="tabs.length === 0">
                        <span class="text-xs text-gray-400 dark:text-gray-600 self-center px-1 py-2">هنوز تبی نساخته‌ای</span>
                    </template>
                </div>
            </div>

            {{-- ══ EMPTY STATE ══ --}}
            <template x-if="tabs.length === 0">
                <div class="empty-box anim-fade">
                    <div class="text-3xl">🗂</div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-500">هنوز هیچ تبی وجود ندارد</p>
                    <button type="button" @click="addTab()" class="btn btn-emerald mt-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        اولین تب را بساز
                    </button>
                </div>
            </template>

            {{-- ══ TAB CONTENT ══ --}}
            <template x-for="(tab, tIdx) in tabs" :key="tIdx">
                <div x-show="activeTab === tIdx" class="cp-card p-6 space-y-5 anim-fade">

                    {{-- Tab title --}}
                    <div class="space-y-1.5">
                        <label class="sec-label">عنوان تب</label>
                        <input type="text"
                               x-model="tab.title"
                               :name="`tabs[${tIdx}][title]`"
                               placeholder="مثلاً: ایمپلنت، کامپوزیت، ارتودنسی…"
                               class="cp-input" style="font-size:.9375rem;font-weight:600;"/>
                    </div>

                    <div class="divider"></div>

                    {{-- Sections --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="sec-label">عنوان‌ها و برندها</span>
                                <span class="text-[11px] text-gray-400 bg-gray-100 dark:bg-gray-700 dark:text-gray-500 px-2 py-0.5 rounded-full"
                                      x-text="`${tab.sections.length} عنوان`"></span>
                            </div>
                            <button type="button" @click="openSectionModal(tIdx)"
                                    class="btn btn-amber" style="padding:6px 13px;font-size:.78rem;">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                افزودن عنوان
                            </button>
                        </div>

                        <template x-if="tab.sections.length === 0">
                            <div class="empty-box" style="padding:24px;">
                                <p class="text-xs text-gray-400">هنوز عنوانی اضافه نشده</p>
                            </div>
                        </template>

                        <template x-for="(section, sIdx) in tab.sections" :key="sIdx">
                            <div class="section-card anim-fade">

                                {{-- Hidden inputs --}}
                                <input type="hidden" :name="`tabs[${tIdx}][sections][${sIdx}][title]`" :value="section.title"/>
                                <input type="hidden" :name="`tabs[${tIdx}][sections][${sIdx}][type]`" :value="section.type"/>

                                {{-- Section header --}}
                                <div class="section-header">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate"
                                              x-text="section.title || 'عنوان بدون نام'"></span>
                                        <span class="type-badge" x-text="section.type"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500"
                                              x-text="`${section.brands.length} برند`"></span>
                                        <button type="button" @click="openSectionModal(tIdx, sIdx)" class="btn btn-ghost-indigo">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                                            ویرایش
                                        </button>
                                        <button type="button" @click="removeSection(tIdx, sIdx)" class="btn-del">✕</button>
                                    </div>
                                </div>

                                {{-- BRAND LIST --}}
                                <div class="bg-white dark:bg-gray-900/20">

                                    <template x-if="section.brands.length > 0">
                                        <div class="brand-row" style="padding-top:6px;padding-bottom:6px;border-bottom:1.5px solid #f1f5f9;">
                                            <span class="sec-label">نام برند / گزینه</span>
                                            <span class="sec-label">قیمت (تومان)</span>
                                            <span></span>
                                        </div>
                                    </template>

                                    <template x-for="(brand, bIdx) in section.brands" :key="bIdx">
                                        <div class="brand-row">
                                            <input type="text"
                                                   x-model="brand.name"
                                                   :name="`tabs[${tIdx}][sections][${sIdx}][brands][${bIdx}][name]`"
                                                   placeholder="نام برند یا گزینه"
                                                   class="cp-input"/>
                                            <div class="price-wrap">
                                                <input type="number"
                                                       x-model="brand.price"
                                                       :name="`tabs[${tIdx}][sections][${sIdx}][brands][${bIdx}][price]`"
                                                       placeholder="۰"
                                                       step="1000"
                                                       min="0"
                                                       class="cp-input price-input"/>
                                            </div>
                                            <button type="button" @click="removeBrand(tIdx, sIdx, bIdx)" class="btn-del">✕</button>
                                        </div>
                                    </template>

                                    <div class="px-3 py-2.5">
                                        <button type="button" @click="addBrand(tIdx, sIdx)"
                                                class="btn btn-ghost-green" style="font-size:.78rem;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            افزودن برند / گزینه
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>
            </template>

        </form>

        {{-- ══ SECTION MODAL ══ --}}
        <template x-if="showModal">
            <div class="modal-backdrop" @click.self="showModal = false">
                <div class="modal-box">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white"
                            x-text="modalSectionIdx !== null ? 'ویرایش عنوان' : 'عنوان جدید'"></h2>
                        <button type="button" @click="showModal = false"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm transition-colors">✕</button>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="sec-label">عنوان</label>
                            <input type="text" x-model="modalTitle" placeholder="مثلاً: متال سرامیک…"
                                   class="cp-input" @keydown.enter.prevent="saveSectionModal()"/>
                        </div>
                        <div class="space-y-1.5">
                            <label class="sec-label">نوع / روش محاسبه</label>
                            <select x-model="modalType" class="cp-select">
                                <option value="" disabled>انتخاب کنید…</option>
                                <template x-for="opt in typeOptions" :key="opt">
                                    <option :value="opt" x-text="opt"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 mt-6">
                        <button type="button" @click="showModal = false" class="btn btn-ghost">انصراف</button>
                        <button type="button" @click="saveSectionModal()"
                                :disabled="!modalTitle || !modalType"
                                :class="(!modalTitle || !modalType) ? 'opacity-50 cursor-not-allowed' : ''"
                                class="btn btn-indigo">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span x-text="modalSectionIdx !== null ? 'ذخیره' : 'افزودن'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
        function customPricesApp(initialTabs) {
            return {
                tabs: Array.isArray(initialTabs)
                    ? initialTabs.map(t => ({
                        ...t,
                        sections: Array.isArray(t.sections)
                            ? t.sections.map(s => ({
                                ...s,
                                brands: Array.isArray(s.brands)
                                    ? s.brands.map(b => ({ name: b.name ?? '', price: b.price ?? '' }))
                                    : []
                            }))
                            : []
                    }))
                    : [],

                activeTab: 0,
                showModal: false,
                modalTabIdx: null,
                modalSectionIdx: null,
                modalTitle: '',
                modalType: '',

                typeOptions: [
                    'چند گزینه‌ای به ازای دندان',
                    'دو گزینه‌ای دارد یا ندارد',
                    'به ازای هر واحد',
                    'به ازای هر دندان',
                    'به ازای هر فک',
                    'به ازای کوادران',
                    'چند گزینه‌ای',
                    'به ازای واحد دندان‌دار',
                ],

                addTab() {
                    this.tabs.push({ title: '', sections: [] });
                    this.activeTab = this.tabs.length - 1;
                },
                removeTab(idx) {
                    this.tabs.splice(idx, 1);
                    if (this.activeTab >= this.tabs.length) this.activeTab = Math.max(0, this.tabs.length - 1);
                },

                openSectionModal(tabIdx, sectionIdx = null) {
                    this.modalTabIdx     = tabIdx;
                    this.modalSectionIdx = sectionIdx;
                    if (sectionIdx !== null) {
                        const s = this.tabs[tabIdx].sections[sectionIdx];
                        this.modalTitle = s.title;
                        this.modalType  = s.type;
                    } else {
                        this.modalTitle = '';
                        this.modalType  = '';
                    }
                    this.showModal = true;
                },

                saveSectionModal() {
                    if (!this.modalTitle || !this.modalType) return;
                    const tab = this.tabs[this.modalTabIdx];

                    if (this.modalSectionIdx !== null) {
                        tab.sections[this.modalSectionIdx].title = this.modalTitle;
                        tab.sections[this.modalSectionIdx].type  = this.modalType;
                    } else {
                        let defaultBrands = [];
                        switch (this.modalType) {
                            case 'دو گزینه‌ای دارد یا ندارد':
                                defaultBrands = [{name:'دارد',price:''},{name:'ندارد',price:''}]; break;
                            case 'به ازای هر واحد':
                                defaultBrands = [{name:'۱',price:''}]; break;
                            case 'به ازای هر دندان':
                                defaultBrands = Array.from({length:7},(_,i)=>({name:String(i+1),price:''})); break;
                            case 'به ازای هر فک':
                                defaultBrands = [
                                    {name:'نیم فک بالا',price:''},{name:'نیم فک پایین',price:''},{name:'هر دو فک',price:''},
                                ]; break;
                            case 'به ازای کوادران':
                                defaultBrands = [
                                    {name:'بالا راست',price:''},{name:'بالا چپ',price:''},
                                    {name:'پایین راست',price:''},{name:'پایین چپ',price:''},
                                ]; break;
                            default:
                                defaultBrands = [{name:'',price:''}];
                        }
                        tab.sections.push({ title: this.modalTitle, type: this.modalType, brands: defaultBrands });
                    }
                    this.showModal = false;
                },

                removeSection(tIdx, sIdx)
                {
                    this.tabs[tIdx].sections.splice(sIdx, 1);
                },
                addBrand(tIdx, sIdx)
                {
                    this.tabs[tIdx].sections[sIdx].brands.push({ name:'', price:'' });
                },
                removeBrand(tIdx, sIdx, bIdx)
                {
                    this.tabs[tIdx].sections[sIdx].brands.splice(bIdx, 1);
                },
            };
        }
    </script>
@endsection
