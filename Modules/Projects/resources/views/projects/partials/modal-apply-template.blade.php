@php
    $availableTemplates = \Modules\Projects\App\Http\Models\ProjectTemplate::with('category')->latest()->get();
@endphp

<div x-data="applyTemplateModalManager()"
     x-show="open"
     x-cloak
     @open-apply-template-modal.window="openModal()"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">

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
            <div class="p-6 sm:p-8 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span
                        class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                            بارگذاری و اعمال الگو در این پروژه
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            یک الگوی آماده را انتخاب کنید تا فازها، گروه‌ها و کارهای آن به پروژه جاری اضافه شوند.
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
            <div class="p-6 sm:p-8 space-y-5 max-h-[60vh] overflow-y-auto">
                @if($availableTemplates->isNotEmpty())
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            الگوی مورد نظر را انتخاب کنید:
                        </label>

                        <div class="space-y-2.5">
                            @foreach($availableTemplates as $t)
                                <label
                                    class="flex items-start justify-between gap-3 p-4 rounded-2xl border transition-all cursor-pointer select-none"
                                    :class="selectedTemplateId == {{ $t->id }}
                                            ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-300 dark:border-indigo-700 shadow-xs'
                                            : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600'">
                                    <div class="flex items-start gap-3 flex-1">
                                        <input type="radio" name="selected_template" value="{{ $t->id }}"
                                               x-model="selectedTemplateId"
                                               class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <strong
                                                    class="text-sm font-bold text-gray-900 dark:text-white">{{ $t->title }}</strong>
                                                @if($t->category)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                          style="background: {{ $t->category->color }}15; color: {{ $t->category->color }}; border-color: {{ $t->category->color }}33;">
                                                        {{ $t->category->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($t->description)
                                                <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $t->description }}</p>
                                            @endif
                                            <div
                                                class="flex items-center gap-2 mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                                <span
                                                    class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 font-bold">{{ $t->phases_count }} فاز</span>
                                                <span
                                                    class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 font-bold">{{ $t->tasks_count }} گروه</span>
                                                <span
                                                    class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 font-bold">{{ $t->items_count }} کار</span>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center space-y-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">هنوز هیچ الگویی تعریف نشده است.</p>
                        <a href="{{ route('projects.templates.create') }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:underline">
                            + ایجاد الگوی جدید در صفحه الگوها
                        </a>
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div
                class="p-6 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between gap-3">
                <button type="button" @click="close()"
                        class="px-5 py-2.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 transition-all">
                    انصراف
                </button>

                @if($availableTemplates->isNotEmpty())
                    <button type="button" @click="applyTemplate({{ $project->id }})"
                            :disabled="!selectedTemplateId || applying"
                            class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                        <svg x-show="applying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="applying ? 'در حال اعمال الگو...' : 'اعمال این الگو در پروژه'"></span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function applyTemplateModalManager() {
            return {
                open: false,
                selectedTemplateId: null,
                applying: false,

                openModal() {
                    this.selectedTemplateId = null;
                    this.applying = false;
                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                async applyTemplate(projectId) {
                    if (!this.selectedTemplateId) return;

                    if (!confirm('آیا از افزودن فازها و کارهای این الگو به این پروژه اطمینان دارید؟')) {
                        return;
                    }

                    this.applying = true;
                    try {
                        const res = await fetch(`/user/projects/templates/${this.selectedTemplateId}/apply/${projectId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'خطا در اعمال الگو');
                    this.applying = false;
                }
            } catch (e) {
                console.error(e);
                alert('خطا در برقراری ارتباط با سرور');
                this.applying = false;
            }
        }
    };
}
</script>
@endpush
