@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ایجاد قالب جدید</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">طراحی قالب جدید به صورت بلوک‌های داینامیک</p>
            </div>
            <div>
                <a href="{{ route('user.contracts.templates.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    بازگشت به لیست
                </a>
            </div>
        </div>

        <form action="{{ route('user.contracts.templates.store') }}" method="POST" id="templateForm">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel: Template Info & Block Builder -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- General details -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">اطلاعات کلی</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">نام قالب</label>
                                <input type="text" name="name" value="{{ old('name', isset($sourceTemplate) ? ('کپی از ' . $sourceTemplate->name) : '') }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="مثال: قرارداد درمان اقساطی">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">موجودیت مرتبط</label>
                                <select name="entity_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($entityTypes as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('entity_type', isset($sourceTemplate) ? $sourceTemplate->entity_type : '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">استایل CSS سفارشی (اختیاری)</label>
                            <textarea name="css_style" rows="2" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder=".contract-container { font-family: inherit; }">{{ old('css_style', isset($sourceTemplate) ? $sourceTemplate->css_style : '') }}</textarea>
                        </div>
                    </div>

                    <!-- Block builder -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">بلوک‌های سند قرارداد</h2>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" onclick="addBlock('header')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                    + سربرگ
                                </button>
                                <button type="button" onclick="addBlock('heading')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-xs font-semibold text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                                    + زیرعنوان (H2/H3)
                                </button>
                                <button type="button" onclick="addBlock('text')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                    + متن قرارداد
                                </button>
                                <button type="button" onclick="addBlock('table')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                                    + جدول اطلاعاتی
                                </button>
                                <button type="button" onclick="addBlock('page_break')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-xs font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                                    + شکست صفحه
                                </button>
                                <button type="button" onclick="addBlock('footer')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                    + پاورقی و امضا
                                </button>
                            </div>
                        </div>

                        <!-- Drag and drop container -->
                        <div id="blocksContainer" class="space-y-4 min-h-[200px] border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 bg-gray-50/50 dark:bg-gray-900/10">
                            <!-- JS will load blocks here -->
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                                ثبت و ذخیره قالب
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Tokens Reference -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">توکن‌های داینامیک مجاز</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">روی هر توکن کلیک کنید تا در کلیپ‌بورد کپی شود و سپس آن را در بلوک‌های متنی قرارداد بچسبانید (Paste).</p>
                        
                        <div class="space-y-2.5 max-h-[600px] overflow-y-auto pr-1">
                            @foreach($tokens as $key => $lbl)
                                <div onclick="copyToken('{{ $key }}')" class="group flex items-center justify-between p-2.5 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 hover:border-indigo-200 dark:hover:border-indigo-800 cursor-pointer transition-all duration-200">
                                    <div>
                                        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                            {{ $lbl }}
                                        </div>
                                        <div class="text-[11px] font-sans text-gray-400 dark:text-gray-500 mt-0.5" dir="ltr">
                                            {{ '{' . $key . '}' }}
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let blockCounter = 0;

        function getAlignSelectHtml(counter, currentAlign = 'right', allowedAligns = ['right', 'center', 'left', 'justify']) {
            const labels = {
                'right': 'راست‌چین',
                'center': 'وسط‌چین',
                'left': 'چپ‌چین',
                'justify': 'تراز دوطرفه (Justify)'
            };
            let optionsHtml = '';
            allowedAligns.forEach(align => {
                const isSelected = (currentAlign === align) ? 'selected' : '';
                optionsHtml += `<option value="${align}" ${isSelected}>${labels[align] || align}</option>`;
            });

            return `
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">جهت چینش:</label>
                    <select name="blocks[${counter}][align]" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs px-2.5 py-1 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        ${optionsHtml}
                    </select>
                </div>
            `;
        }

        function addBlock(type, data = {}) {
            blockCounter++;
            const container = document.getElementById('blocksContainer');
            let contentHtml = '';
            let typeLabel = '';
            let typeBadgeClass = '';
            let alignHtml = '';

            if (type === 'header') {
                typeLabel = 'سربرگ قرارداد';
                typeBadgeClass = 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300';
                alignHtml = getAlignSelectHtml(blockCounter, data.align || 'center', ['center', 'right', 'left']);
                const showBorder = (data.show_border === undefined || data.show_border === null || data.show_border === '' || data.show_border === '1' || data.show_border === 1 || data.show_border === true);
                contentHtml = `
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">عنوان سربرگ (خط اول)</label>
                                <input type="text" name="blocks[${blockCounter}][title]" value="${data.title || ''}" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: قرارداد درمان عمومی / کلینیک تخصصی">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">خط زیر سربرگ</label>
                                <select name="blocks[${blockCounter}][show_border]" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none">
                                    <option value="1" ${showBorder ? 'selected' : ''}>با خط جداکننده</option>
                                    <option value="0" ${!showBorder ? 'selected' : ''}>بدون خط جداکننده</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">زیرعنوان سربرگ (خط دوم - اختیاری)</label>
                            <input type="text" name="blocks[${blockCounter}][subtitle]" value="${data.subtitle || ''}" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: شماره قرارداد: {contract_number} | تاریخ: {today_jalali}">
                        </div>
                    </div>
                `;
            } else if (type === 'heading') {
                typeLabel = 'زیرعنوان (تیتر بخش)';
                typeBadgeClass = 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300';
                alignHtml = getAlignSelectHtml(blockCounter, data.align || 'right', ['right', 'center', 'left']);
                const currentLevel = data.level || 'h2';
                const showBorder = (data.show_border === undefined || data.show_border === null || data.show_border === '' || data.show_border === '1' || data.show_border === 1 || data.show_border === true);
                contentHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">سطح تیتر</label>
                            <select name="blocks[${blockCounter}][level]" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none">
                                <option value="h2" ${currentLevel === 'h2' ? 'selected' : ''}>H2 - تیتر بخش اصلی</option>
                                <option value="h3" ${currentLevel === 'h3' ? 'selected' : ''}>H3 - تیتر فرعی</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">خط شاخص کنار تیتر</label>
                            <select name="blocks[${blockCounter}][show_border]" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none">
                                <option value="1" ${showBorder ? 'selected' : ''}>با خط کنار تیتر</option>
                                <option value="0" ${!showBorder ? 'selected' : ''}>بدون خط کنار تیتر</option>
                            </select>
                        </div>
                        <div class="md:col-span-6">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">متن زیرعنوان (امکان استفاده از توکن‌ها)</label>
                            <input type="text" name="blocks[${blockCounter}][title]" value="${data.title || ''}" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: ۱. مشخصات و تعهدات بیمار">
                        </div>
                    </div>
                `;
            } else if (type === 'text') {
                typeLabel = 'متن قرارداد';
                typeBadgeClass = 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
                alignHtml = getAlignSelectHtml(blockCounter, data.align || 'justify', ['justify', 'right', 'center', 'left']);
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">محتوای متنی قرارداد (امکان استفاده از توکن‌ها)</label>
                        <textarea name="blocks[${blockCounter}][content]" rows="4" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="متن قرارداد خود را در این بخش بنویسید...">${data.content || ''}</textarea>
                    </div>
                `;
            } else if (type === 'table') {
                typeLabel = 'جدول اطلاعاتی';
                typeBadgeClass = 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300';
                const selected = data.content || '';
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">انتخاب جدول داده‌های داینامیک</label>
                        <select name="blocks[${blockCounter}][content]" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none transition-colors">
                            <option value="plan_items_table" ${selected === 'plan_items_table' ? 'selected' : ''}>جدول خدمات طرح درمان</option>
                            <option value="installment_breakdown_table" ${selected === 'installment_breakdown_table' ? 'selected' : ''}>جدول اقساط و زمان پرداخت</option>
                            <option value="cheques_table" ${selected === 'cheques_table' ? 'selected' : ''}>جدول چک‌های دریافتی</option>
                        </select>
                    </div>
                `;
            } else if (type === 'page_break') {
                typeLabel = 'شکست صفحه';
                typeBadgeClass = 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300';
                contentHtml = `
                    <div class="py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <span>شکست صفحه (شروع صفحه جدید هنگام چاپ قرارداد)</span>
                        <input type="hidden" name="blocks[${blockCounter}][content]" value="page_break">
                    </div>
                `;
            } else if (type === 'footer') {
                typeLabel = 'پاورقی و امضا';
                typeBadgeClass = 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300';
                alignHtml = getAlignSelectHtml(blockCounter, data.align || 'center', ['center', 'right', 'left', 'justify']);
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">پاورقی (بخش امضا و توضیحات نهایی)</label>
                        <textarea name="blocks[${blockCounter}][content]" rows="2" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: مهر و امضای کلینیک                   امضای بیمار / ولی بیمار">${data.content || ''}</textarea>
                    </div>
                `;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'block-wrapper p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative group transition-all duration-200';
            wrapper.id = `block_wrap_${blockCounter}`;
            wrapper.innerHTML = `
                <input type="hidden" name="blocks[${blockCounter}][type]" value="${type}">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold ${typeBadgeClass}">
                            ${typeLabel}
                        </span>
                        ${alignHtml}
                    </div>
                    <div class="flex gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                        <button type="button" onclick="moveUp('${wrapper.id}')" title="انتقال به بالا" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            ▲
                        </button>
                        <button type="button" onclick="moveDown('${wrapper.id}')" title="انتقال به پایین" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            ▼
                        </button>
                        <button type="button" onclick="removeBlock('${wrapper.id}')" title="حذف بلوک" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                ${contentHtml}
            `;

            container.appendChild(wrapper);
        }

        function removeBlock(id) {
            const block = document.getElementById(id);
            if (block) block.remove();
        }

        function moveUp(id) {
            const block = document.getElementById(id);
            const prev = block.previousElementSibling;
            if (prev) {
                block.parentNode.insertBefore(block, prev);
            }
        }

        function moveDown(id) {
            const block = document.getElementById(id);
            const next = block.nextElementSibling;
            if (next) {
                block.parentNode.insertBefore(next, block);
            }
        }

        let lastFocusedElement = null;
        document.addEventListener('focusin', (e) => {
            if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') {
                lastFocusedElement = e.target;
            }
        });

        function copyToken(token) {
            const tokenText = '{' + token + '}';
            
            if (lastFocusedElement && (lastFocusedElement.tagName === 'TEXTAREA' || lastFocusedElement.tagName === 'INPUT')) {
                const start = lastFocusedElement.selectionStart;
                const end = lastFocusedElement.selectionEnd;
                const text = lastFocusedElement.value;
                lastFocusedElement.value = text.substring(0, start) + tokenText + text.substring(end);
                lastFocusedElement.focus();
                lastFocusedElement.selectionStart = lastFocusedElement.selectionEnd = start + tokenText.length;
                
                if (typeof showToast === 'function') {
                    showToast('توکن ' + tokenText + ' در محل قرار گرفت و کپی شد.');
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast('توکن ' + tokenText + ' کپی شد. در متن مورد نظر Paste کنید.');
                }
            }
            
            navigator.clipboard.writeText(tokenText);
        }

        // Initialize blocks
        window.addEventListener('DOMContentLoaded', () => {
            @if(isset($sourceTemplate) && !empty($sourceTemplate->blocks))
                const existingBlocks = @json(array_values($sourceTemplate->blocks ?: []));
                const blocksArray = Array.isArray(existingBlocks)
                    ? existingBlocks
                    : (existingBlocks && typeof existingBlocks === 'object' ? Object.values(existingBlocks) : []);

                if (blocksArray.length > 0) {
                    blocksArray.forEach(block => {
                        if (block && block.type) {
                            addBlock(block.type, block);
                        }
                    });
                }
            @else
                // Default initial blocks
                addBlock('header', { 
                    title: 'قرارداد تسهیلات و خدمات درمانی', 
                    subtitle: 'تاریخ تنظیم قرارداد: {today_jalali} | شماره پرونده: {patient_case_number}',
                    align: 'center',
                    show_border: '1'
                });
                addBlock('heading', { 
                    level: 'h2', 
                    title: 'ماده ۱ - طرفین قرارداد', 
                    align: 'right' 
                });
                addBlock('text', { 
                    content: "این قرارداد فی‌مابین کلینیک {clinic_name} به آدرس {clinic_address} و شماره تماس {clinic_phone} از یک طرف و جناب آقای/سرکار خانم {patient_name} با کدملی {patient_national_code}، شماره تماس {patient_phone} و شماره پرونده {patient_case_number} که در این قرارداد بیمار/زیباجو نامیده می‌شود، با شرایط و مفاد ذیل منعقد گردید.",
                    align: 'justify'
                });
                addBlock('heading', { 
                    level: 'h2', 
                    title: 'ماده ۲ - موضوع قرارداد', 
                    align: 'right' 
                });
                addBlock('text', { 
                    content: "موضوع قرارداد عبارت است از ارائه خدمات درمانی با جزئیات ذیل‌الذکر به شرح جدول زیر:",
                    align: 'justify'
                });
                addBlock('table', { content: 'plan_items_table' });
                addBlock('heading', { 
                    level: 'h2', 
                    title: 'ماده ۳ - مدت قرارداد', 
                    align: 'right' 
                });
                addBlock('text', { 
                    content: "مدت اجرای کلیه تعهدات این قرارداد، {installment_months} ماه از تاریخ امضای آن است.",
                    align: 'justify'
                });
                addBlock('heading', { 
                    level: 'h2', 
                    title: 'ماده ۴ - مبلغ قرارداد و نحوه پرداخت آن', 
                    align: 'right' 
                });
                addBlock('text', { 
                    content: "مجموع مبلغ این قرارداد معادل {plan_final_payable} (به حروف: {plan_final_payable_in_words}) است و نحوه پرداخت به شرح ذیل خواهد بود:\n\n۴-۱- به عنوان پیش‌پرداخت، مبلغ {installment_down_payment} (به حروف {installment_down_payment_in_words}) معادل {installment_down_payment_percent_label} همزمان با امضای قرارداد نقداً پرداخت شد.\n\n۴-۲- باقیمانده مبلغ قرارداد به میزان {installment_remaining_amount} (به حروف {installment_remaining_amount_in_words}) در قالب {total_cheques} فقره چک به شرح جدول زیر تسلیم کلینیک گردید:",
                    align: 'justify'
                });
                addBlock('table', { content: 'cheques_table' });
                addBlock('heading', { 
                    level: 'h2', 
                    title: 'ماده ۵ - تبصره‌ها و شرایط عمومی', 
                    align: 'right' 
                });
                addBlock('text', { 
                    content: "تبصره ۱: این قرارداد مشمول تعدیل یا افزایش قیمت‌ها در طول درمان نبوده و کلینیک کلیه جوانب را در قرارداد لحاظ کرده است.\nتبصره ۲: هرگونه تغییر در طرح درمان بنا به نظر پزشک یا درخواست بیمار، طبق ضوابط کلینیک محاسبه می‌گردد.\nتبصره ۳: مدت اعتبار این فاکتور ۱۲ ماه از تاریخ صدور قرارداد است.",
                    align: 'justify'
                });
                addBlock('footer', { 
                    content: 'امضا و اثر انگشت بیمار/زیباجو                   امضا و مهر مدیر کلینیک',
                    align: 'center'
                });
            @endif
        });
    </script>
@endsection

