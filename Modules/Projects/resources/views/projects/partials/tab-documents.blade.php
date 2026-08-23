@php
    use Modules\Projects\App\Http\Models\ProjectDocument;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $allowedCategories = $project->allowedDocumentCategoriesFor(auth()->id());
    $allowedExtsArray = ProjectDocument::getAllowedExtensions();
    $acceptAttr = !empty($allowedExtsArray) ? ('.' . implode(',.', $allowedExtsArray)) : '*';
    $maxSizeMb = ProjectDocument::getMaxFileSizeMb();

    $visibleDocuments = $project->documents->filter(function($doc) use ($allowedCategories) {
        if ($doc->uploaded_by === auth()->id()) {
            return true;
        }
        if (empty($doc->category)) {
            return true;
        }
        return in_array($doc->category, $allowedCategories, true);
    });
@endphp

<div class="space-y-6" x-data="{ uploadModal: false, docType: 'file', activeCategory: 'all' }">

    {{-- Top Action Bar --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800/80 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                اسناد و پیوست‌های پروژه
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">فایل‌ها، پیش‌نویس‌ها، مستندات و لینک‌های خارجی مرتبط با این پروژه را
                مدیریت کنید.</p>
        </div>

        @can('uploadDocument', $project)
            <button type="button" @click.stop="uploadModal = true"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                افزودن سند یا لینک جدید
            </button>
        @endcan
    </div>

    {{-- Category Filters Bar --}}
    @if(count($allowedCategories) > 0)
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button type="button" @click="activeCategory = 'all'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shrink-0"
                    :class="activeCategory === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200/80 dark:border-gray-700'">
                همه اسناد ({{ $visibleDocuments->count() }})
            </button>
            @foreach($allowedCategories as $cat)
                @php $catCount = $visibleDocuments->where('category', $cat)->count(); @endphp
                <button type="button" @click="activeCategory = '{{ $cat }}'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shrink-0"
                        :class="activeCategory === '{{ $cat }}' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200/80 dark:border-gray-700'">
                    <span>{{ $cat }}</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ $catCount }})</span>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Documents Grid / List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($visibleDocuments as $doc)
            <div x-show="activeCategory === 'all' || activeCategory === '{{ $doc->category }}'"
                 class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition-all">
                <div class="flex items-start gap-3.5">
                    {{-- Icon --}}
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $doc->type === 'file' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                        @if($doc->type === 'file')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('projects.projects.documents.show', ['project' => $project->id, 'document' => $doc->id]) }}"
                               class="font-bold text-gray-900 dark:text-white text-sm truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                               title="{{ $doc->title }}">
                                {{ $doc->title }}
                            </a>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($doc->category)
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                        {{ $doc->category }}
                                    </span>
                                @endif
                                <span
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $doc->type === 'file' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                                    {{ $doc->type === 'file' ? 'فایل' : 'پیوند' }}
                                </span>
                            </div>
                        </div>

                        @if($doc->description)
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed line-clamp-2">{{ $doc->description }}</p>
                        @endif

                        <div class="flex items-center gap-3 text-[11px] text-gray-400 mt-2">
                            @if($doc->type === 'file')
                                <span class="font-mono">{{ $doc->human_file_size }}</span>
                            @else
                                <span class="truncate font-mono max-w-[200px]" dir="ltr">{{ $doc->link_url }}</span>
                            @endif
                            <span>•</span>
                            <span>{{ $doc->uploader?->name ?? 'کاربر' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div
                    class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100 dark:border-gray-700/40">
                    <span
                        class="text-[11px] text-gray-400">{{ $doc->created_at ? (function_exists('jdate') ? $faNum(jdate($doc->created_at)->format('Y/m/d')) : $doc->created_at->format('Y/m/d')) : '' }}</span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('projects.projects.documents.show', ['project' => $project->id, 'document' => $doc->id]) }}"
                           class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-all flex items-center gap-1.5"
                           title="مشاهده جزئیات سند">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            جزئیات
                        </a>

                        @if($doc->type === 'file' && $doc->file_path)
                            <a href="{{ route('projects.projects.documents.download', ['project' => $project->id, 'document' => $doc->id]) }}"
                               class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                دانلود
                            </a>
                        @elseif($doc->type === 'link' && $doc->link_url)
                            <a href="{{ $doc->link_url }}" target="_blank" rel="noopener noreferrer"
                               class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                پیوند
                            </a>
                        @endif

                        @can('deleteDocument', $project)
                            <form method="POST"
                                  action="{{ route('projects.projects.documents.destroy', ['project' => $project->id, 'document' => $doc->id]) }}"
                                  onsubmit="return confirm('آیا از حذف این سند اطمینان دارید؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer"
                                        title="حذف">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div
                class="col-span-1 md:col-span-2 bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-12 text-center text-gray-400">
                <div
                    class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-700/40 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">هنوز سندی برای این پروژه ثبت نشده
                    است</h4>
                <p class="text-xs text-gray-400 mb-4">می‌توانید فایل‌های قرارداد، خروجی‌ها یا لینک‌های مرجع را آپلود
                    نمایید.</p>
                @can('uploadDocument', $project)
                    <button type="button" @click.stop="uploadModal = true"
                            class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow hover:bg-indigo-700 transition-all cursor-pointer">
                        + ثبت اولین سند
                    </button>
                @endcan
            </div>
        @endforelse
    </div>

    {{-- Upload / Add Document Modal --}}
    <div x-show="uploadModal" class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true"
         style="display: none;">
        {{-- Background overlay --}}
        <div x-show="uploadModal"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="uploadModal = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20" @click.self="uploadModal = false">
            <div class="flex min-h-full items-end justify-center text-center sm:items-center"
                 @click.self="uploadModal = false">
                {{-- Modal panel --}}
                <div x-show="uploadModal"
                     @click.stop
                     @click.away="uploadModal = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700 p-6 space-y-5">

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            ثبت سند یا لینک جدید
                        </h3>
                        <button type="button" @click="uploadModal = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('projects.projects.documents.store', $project) }}" method="POST"
                          enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- Type Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع
                                سند</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex items-center justify-center gap-2 p-3 rounded-2xl border cursor-pointer text-xs font-bold transition-all"
                                    :class="docType === 'file' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                                    <input type="radio" name="type" value="file" x-model="docType" class="sr-only">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    آپلود فایل
                                </label>
                                <label
                                    class="flex items-center justify-center gap-2 p-3 rounded-2xl border cursor-pointer text-xs font-bold transition-all"
                                    :class="docType === 'link' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                                    <input type="radio" name="type" value="link" x-model="docType" class="sr-only">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    پیوند خارجی (URL)
                                </label>
                            </div>
                        </div>

                        {{-- Category Select --}}
                        @if(count($allowedCategories) > 0)
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی
                                    موضوعی سند</label>
                                <select name="category"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                                    <option value="">بدون دسته‌بندی (عمومی)</option>
                                    @foreach($allowedCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Title --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان سند
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required placeholder="مثال: قرارداد رسمی نسخه نهایی"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                        </div>

                        {{-- File Input --}}
                        <div x-show="docType === 'file'" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    انتخاب فایل <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                                    حداکثر حجم: {{ $faNum($maxSizeMb) }} مگابایت
                                </span>
                            </div>
                            <input type="file" name="file" :required="docType === 'file'"
                                   accept="{{ $acceptAttr }}"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">

                            @if(!empty($allowedExtsArray))
                                <div
                                    class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 text-[11px] text-gray-500 dark:text-gray-400 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <span class="font-bold text-gray-700 dark:text-gray-300">فرمت‌های مجاز:</span>
                                        <span
                                            class="font-mono text-[10px] text-gray-600 dark:text-gray-400 dir-ltr inline-block mr-1 leading-normal">{{ implode(', ', $allowedExtsArray) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Link URL Input --}}
                        <div x-show="docType === 'link'">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">آدرس اینترنتی
                                (URL) <span class="text-red-500">*</span></label>
                            <input type="url" name="link_url" :required="docType === 'link'"
                                   placeholder="https://example.com/document" dir="ltr"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white font-mono text-left transition-all">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات
                                اختیاری</label>
                            <textarea name="description" rows="2" placeholder="توضیح کوتاه در مورد این سند یا لینک..."
                                      class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all"></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="uploadModal = false"
                                    class="px-5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 cursor-pointer">
                                انصراف
                            </button>
                            <button type="submit"
                                    class="px-6 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                ثبت سند
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
