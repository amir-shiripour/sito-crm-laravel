<div x-data="importTasksJsonManager()"
     x-show="open"
     x-cloak
     @open-import-tasks-json-modal.window="openModal()"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="import-json-modal-title" role="dialog" aria-modal="true">

    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="close()"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Dialog --}}
        <div
            class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-gray-700/60"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- Modal Header --}}
            <div class="p-6 sm:p-7 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span
                        class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white" id="import-json-modal-title">
                            درون‌ریزی کارها و فازها از فایل JSON
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            افزودن مستقیم ساختار فازها، گروه‌ها و کارهای چک‌لیست از فایل JSON به این پروژه.
                        </p>
                    </div>
                </div>

                <button type="button" @click="close()"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 sm:p-7 space-y-5 max-h-[65vh] overflow-y-auto">

                {{-- Action / Quick Download Sample --}}
                <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs text-gray-700 dark:text-gray-300">
                            ساختار استاندارد JSON شامل فازها، گروه‌ها و کارهای زیرمجموعه است.
                        </span>
                    </div>

                    <button type="button" @click="downloadSampleJson()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 text-xs font-bold transition-all shadow-2xs shrink-0 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>دانلود نمونه فایل</span>
                    </button>
                </div>

                {{-- Input Method Tabs --}}
                <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <button type="button" @click="inputTab = 'file'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            :class="inputTab === 'file' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                        بارگذاری فایل JSON
                    </button>
                    <button type="button" @click="inputTab = 'text'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            :class="inputTab === 'text' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                        درج مستقیم کد JSON
                    </button>
                </div>

                {{-- Tab 1: File Upload Dropzone --}}
                <div x-show="inputTab === 'file'" class="space-y-3">
                    <label class="relative flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed transition-all cursor-pointer group bg-gray-50/70 dark:bg-gray-900/40"
                           :class="isDragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600'"
                           @dragover.prevent="isDragging = true"
                           @dragleave.prevent="isDragging = false"
                           @drop.prevent="isDragging = false; handleDrop($event)">
                        <input type="file" accept=".json,application/json" @change="handleFileInput($event)" class="sr-only">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition-transform shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    کلیک کنید یا فایل JSON را به این بخش بکشید
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-400 mt-0.5">
                                    فرمت معتبر: فایل با پسوند .json حداکثر ۱۰ مگابایت
                                </p>
                            </div>
                            <template x-if="fileName">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800/40 mt-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="fileName"></span>
                                </div>
                            </template>
                        </div>
                    </label>
                </div>

                {{-- Tab 2: Direct JSON Textarea --}}
                <div x-show="inputTab === 'text'" class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        محتوای JSON را اینجا قرار دهید:
                    </label>
                    <textarea x-model="jsonText"
                              @input="parseJsonText()"
                              rows="6"
                              placeholder='{"structure": {"phases": [{"name": "فاز ۱", "tasks": [{"title": "گروه ۱", "items": [{"title": "کار اول"}]}]}]}}'
                              class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 p-3.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all font-sans leading-relaxed text-left dir-ltr"></textarea>
                </div>

                {{-- Mode Selector (Append vs Replace) --}}
                <div class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 space-y-2.5">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        نحوه اعمال داده‌های فایل در پروژه:
                    </label>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <button type="button" @click="importMode = 'append'"
                                class="py-2.5 px-3 rounded-xl font-bold transition-all text-center border cursor-pointer flex items-center justify-center gap-1.5"
                                :class="importMode === 'append' ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'">
                            <span>افزودن به کارهای فعلی (پیشنهادی)</span>
                        </button>
                        <button type="button" @click="importMode = 'replace'"
                                class="py-2.5 px-3 rounded-xl font-bold transition-all text-center border cursor-pointer flex items-center justify-center gap-1.5"
                                :class="importMode === 'replace' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'">
                            <span>جایگزینی کامل کارهای پروژه</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1" x-text="importMode === 'append' ? 'کارهای جدید به فازها و کارهای قبلی پروژه اضافه خواهند شد.' : 'توجه: با انتخاب جایگزینی، تمامی فازها و کارهای قبلی این پروژه حذف شده و ساختار جدید درج خواهد شد.'"></p>
                </div>

                {{-- Live Preview / Feedback Card --}}
                <template x-if="preview">
                    <div class="p-4 rounded-2xl border transition-all"
                         :class="preview.valid ? 'bg-emerald-50/60 text-emerald-800 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-300 dark:border-emerald-800/40' : 'bg-rose-50/60 text-rose-800 border-rose-200 dark:bg-rose-950/20 dark:text-rose-300 dark:border-rose-800/40'">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold" x-text="preview.message"></span>
                            <template x-if="preview.valid">
                                <div class="flex items-center gap-2 text-xs font-bold">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40" x-text="preview.phasesCount + ' فاز'"></span>
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300" x-text="preview.tasksCount + ' گروه'"></span>
                                    <span class="px-2 py-0.5 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300" x-text="preview.itemsCount + ' کار'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            {{-- Modal Footer --}}
            <div
                class="p-6 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between gap-3">
                <button type="button" @click="close()"
                        class="px-5 py-2.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all cursor-pointer">
                    انصراف
                </button>

                <button type="button" @click="submitImport()"
                        :disabled="submitting || !parsedStructure"
                        class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 cursor-pointer">
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'در حال اعمال ساختار...' : 'اعمال و افزودن کارها به پروژه'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function importTasksJsonManager() {
        return {
            open: false,
            inputTab: 'file',
            isDragging: false,
            fileName: '',
            jsonText: '',
            importMode: 'append',
            submitting: false,
            parsedStructure: null,
            preview: null,

            openModal() {
                this.open = true;
                this.fileName = '';
                this.jsonText = '';
                this.parsedStructure = null;
                this.preview = null;
                this.submitting = false;
            },

            close() {
                this.open = false;
            },

            handleFileInput(e) {
                const file = e.target.files ? e.target.files[0] : null;
                if (file) this.readFile(file);
            },

            handleDrop(e) {
                const file = e.dataTransfer.files ? e.dataTransfer.files[0] : null;
                if (file) this.readFile(file);
            },

            readFile(file) {
                if (!file.name.endsWith('.json') && file.type !== 'application/json') {
                    this.preview = { valid: false, message: 'لطفاً یک فایل با پسوند .json انتخاب نمایید.' };
                    return;
                }
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.jsonText = e.target.result;
                    this.parseJsonText();
                };
                reader.readAsText(file);
            },

            parseJsonText() {
                if (!this.jsonText.trim()) {
                    this.parsedStructure = null;
                    this.preview = null;
                    return;
                }

                try {
                    let clean = this.jsonText.replace(/^\uFEFF/, '').trim();
                    const data = JSON.parse(clean);

                    let phases = [];
                    let unphased = [];

                    if (data.structure && typeof data.structure === 'object') {
                        phases = data.structure.phases || data.structure['فازها'] || [];
                        unphased = data.structure.unphased_tasks || data.structure['گروه‌ها'] || data.structure['tasks'] || [];
                    } else if (data.phases || data['فازها']) {
                        phases = data.phases || data['فازها'] || [];
                        unphased = data.unphased_tasks || data.tasks || data['گروه‌ها'] || [];
                    } else if (Array.isArray(data)) {
                        const isPhaseList = data.some(item => item && (item.tasks || item.phases || item['فاز']));
                        if (isPhaseList) {
                            phases = data;
                        } else {
                            unphased = data;
                        }
                    } else if (data.tasks || data['گروه‌ها']) {
                        unphased = data.tasks || data['گروه‌ها'] || [];
                    }

                    let tasksCount = 0;
                    let itemsCount = 0;

                    phases.forEach(p => {
                        const pTasks = p.tasks || p['گروه‌ها'] || p.items || [];
                        tasksCount += pTasks.length;
                        pTasks.forEach(t => {
                            const tItems = t.items || t.checklist || t['کارها'] || [];
                            itemsCount += tItems.length;
                        });
                    });

                    unphased.forEach(t => {
                        tasksCount++;
                        const tItems = t.items || t.checklist || t['کارها'] || [];
                        itemsCount += tItems.length;
                    });

                    if (phases.length === 0 && tasksCount === 0) {
                        this.parsedStructure = null;
                        this.preview = { valid: false, message: 'هیچ فاز یا گروه کاری در این فایل شناسایی نشد.' };
                        return;
                    }

                    this.parsedStructure = data;
                    this.preview = {
                        valid: true,
                        message: 'ساختار فایل با موفقیت شناسایی و تایید شد.',
                        phasesCount: phases.length,
                        tasksCount: tasksCount,
                        itemsCount: itemsCount,
                    };
                } catch (err) {
                    this.parsedStructure = null;
                    this.preview = { valid: false, message: 'خطا در خواندن فایل JSON: فرمت فایل نامعتبر است.' };
                }
            },

            async submitImport() {
                if (!this.parsedStructure || this.submitting) return;

                if (this.importMode === 'replace') {
                    if (!confirm('⚠️ آیا اطمینان دارید؟ تمام فازها و کارهای فعلی این پروژه حذف شده و اطلاعات جدید جایگزین خواهند شد.')) {
                        return;
                    }
                }

                this.submitting = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const res = await fetch(`{{ route('projects.projects.tasks.importJson', $project) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            structure: this.parsedStructure,
                            import_mode: this.importMode,
                        })
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'خطا در درون‌ریزی ساختار کارها');
                    }

                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { type: 'success', text: data.message || 'کارها با موفقیت به پروژه اضافه شدند.' }
                    }));

                    this.close();
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } catch (err) {
                    console.error('Import error:', err);
                    alert(err.message || 'خطا در ذخیره‌سازی اطلاعات');
                    this.submitting = false;
                }
            },

            downloadSampleJson() {
                const sample = {
                    version: "1.0",
                    title: "نمونه ساختار پروژه",
                    structure: {
                        phases: [
                            {
                                name: "فاز ۱: تحقیقات و برنامه‌ریزی",
                                color: "#6366f1",
                                description: "بررسی نیازها و مستندسازی نیازمندی‌ها",
                                tasks: [
                                    {
                                        title: "جلسه بریف اولیه و استخراج RFP",
                                        description: "هماهنگی جلسات با کارفرما",
                                        manager_id: "",
                                        due_date: "1404/01/20",
                                        items: [
                                            { title: "تنظیم پرسشنامه نیازمندی‌ها", description: "", assigned_to: "", due_date: "1404/01/15" },
                                            { title: "تکمیل و تأیید سند بریف", description: "", assigned_to: "", due_date: "1404/01/20" }
                                        ]
                                    }
                                ]
                            },
                            {
                                name: "فاز ۲: طراحی و توسعه",
                                color: "#8b5cf6",
                                description: "طراحی رابط کاربری و پیاده‌سازی بک‌اند",
                                tasks: [
                                    {
                                        title: "طراحی پروتوتایپ صفحات",
                                        description: "طراحی ساختار صفحات اصلی",
                                        manager_id: "",
                                        due_date: "1404/02/10",
                                        items: [
                                            { title: "طراحی وایرفریم‌ها", description: "", assigned_to: "", due_date: "1404/01/28" },
                                            { title: "تأیید طرح از سمت کارفرما", description: "", assigned_to: "", due_date: "1404/02/10" }
                                        ]
                                    }
                                ]
                            }
                        ],
                        unphased_tasks: [
                            {
                                title: "مدیریت ارتباطات و پشتیبانی",
                                description: "جلسات هفتگی هماهنگی",
                                manager_id: "",
                                due_date: "",
                                items: [
                                    { title: "ارسال گزارش پیشرفت هفتگی", description: "", assigned_to: "", due_date: "" }
                                ]
                            }
                        ]
                    }
                };

                const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(sample, null, 2));
                const downloadAnchor = document.createElement('a');
                downloadAnchor.setAttribute("href", dataStr);
                downloadAnchor.setAttribute("download", "project-tasks-sample.json");
                document.body.appendChild(downloadAnchor);
                downloadAnchor.click();
                downloadAnchor.remove();
            }
        };
    }
</script>
