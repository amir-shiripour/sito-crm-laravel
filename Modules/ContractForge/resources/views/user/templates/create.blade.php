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
                                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="مثال: قرارداد درمان اقساطی">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">موجودیت مرتبط</label>
                                <select name="entity_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($entityTypes as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">استایل CSS سفارشی (اختیاری)</label>
                            <textarea name="css_style" rows="2" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder=".contract-container { font-family: iransans; }"></textarea>
                        </div>
                    </div>

                    <!-- Block builder -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">بلوک‌های سند قرارداد</h2>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="addBlock('header')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    + سربرگ
                                </button>
                                <button type="button" onclick="addBlock('text')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    + متن قرارداد
                                </button>
                                <button type="button" onclick="addBlock('table')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    + جدول اطلاعاتی
                                </button>
                                <button type="button" onclick="addBlock('page_break')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    + شکست صفحه
                                </button>
                                <button type="button" onclick="addBlock('footer')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    + پاورقی
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
                        
                        <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                            @foreach($tokens as $key => $lbl)
                                <div onclick="copyToken('{{ $key }}')" class="group flex items-center justify-between p-2.5 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 hover:border-indigo-200 dark:hover:border-indigo-800 cursor-pointer transition-all duration-200">
                                    <div>
                                        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                            {{ $lbl }}
                                        </div>
                                        <div class="text-[10px] font-mono text-gray-400 mt-0.5">
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

        function addBlock(type, data = {}) {
            blockCounter++;
            const container = document.getElementById('blocksContainer');
            let contentHtml = '';

            if (type === 'header') {
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">عنوان سربرگ</label>
                        <input type="text" name="blocks[${blockCounter}][title]" value="${data.title || ''}" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: قرارداد درمان عمومی">
                    </div>
                `;
            } else if (type === 'text') {
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">محتوای متنی قرارداد (امکان استفاده از توکن‌ها)</label>
                        <textarea name="blocks[${blockCounter}][content]" rows="4" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="متن قرارداد خود را در این بخش بنویسید...">${data.content || ''}</textarea>
                    </div>
                `;
            } else if (type === 'table') {
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
                contentHtml = `
                    <div class="py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-550" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <span>شکست صفحه (شروع صفحه جدید هنگام چاپ قرارداد)</span>
                        <input type="hidden" name="blocks[${blockCounter}][content]" value="page_break">
                    </div>
                `;
            } else if (type === 'footer') {
                contentHtml = `
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">پاورقی (بخش امضا و توضیحات نهایی)</label>
                        <textarea name="blocks[${blockCounter}][content]" rows="2" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 focus:outline-none placeholder-gray-400 dark:placeholder-gray-500 transition-colors" placeholder="مثال: مهر و امضای کلینیک / امضای بیمار">${data.content || ''}</textarea>
                    </div>
                `;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'block-wrapper p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative group transition-all duration-200';
            wrapper.id = `block_wrap_${blockCounter}`;
            wrapper.innerHTML = `
                <input type="hidden" name="blocks[${blockCounter}][type]" value="${type}">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                        ${type === 'header' ? 'سربرگ' : type === 'text' ? 'متن قرارداد' : type === 'table' ? 'جدول اطلاعاتی' : type === 'page_break' ? 'شکست صفحه' : 'پاورقی'}
                    </span>
                    <div class="flex gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                        <button type="button" onclick="moveUp('${wrapper.id}')" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            ▲
                        </button>
                        <button type="button" onclick="moveDown('${wrapper.id}')" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            ▼
                        </button>
                        <button type="button" onclick="removeBlock('${wrapper.id}')" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors">
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
                
                showToast('توکن ' + tokenText + ' در محل قرار گرفت و کپی شد.');
            } else {
                showToast('توکن ' + tokenText + ' کپی شد. در متن مورد نظر Paste کنید.');
            }
            
            navigator.clipboard.writeText(tokenText);
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-5 py-3 rounded-xl shadow-2xl text-sm font-medium z-50 transition-all duration-300 transform translate-y-0 opacity-100';
            toast.innerText = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Initialize with default blocks
        window.addEventListener('DOMContentLoaded', () => {
            addBlock('header', { title: 'سند قرارداد طرح درمان' });
            addBlock('text', { content: "قرارداد حاضر بین کلینیک دندانپزشکی و جناب آقای/سرکار خانم {patient_name} با شرایط ذیل منعقد گردید.\n\nموضوع قرارداد: انجام امور درمانی به شرح جدول خدمات زیر." });
            addBlock('table', { content: 'plan_items_table' });
            addBlock('text', { content: "شرایط پرداخت مبالغ بر اساس روش {installment_option_title} بوده و مبالغ پیش‌پرداخت معادل {installment_down_payment} و الباقی طی اقساط زیر تادیه می‌گردد." });
            addBlock('table', { content: 'installment_breakdown_table' });
            addBlock('footer', { content: 'مهر و امضای کلینیک                   امضای بیمار / ولی بیمار' });
        });
    </script>
@endsection
