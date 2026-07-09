<div class="p-6 max-w-7xl mx-auto space-y-8" x-data="{ activeTab: 'content' }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-xs font-bold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></span>
                {{ $type === 'post' ? 'نوشته وبلاگ' : 'برگه ایستا' }}
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                {{ $isEdit ? 'ویرایش نوشته' : 'ایجاد نوشته جدید' }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت همه‌جانبه، سئو پیشرفته و تولید محتوا با هوش مصنوعی</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.content.' . ($type === 'post' ? 'posts' : 'pages') . '.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-650 rounded-xl text-xs font-bold transition-colors">
                انصراف
            </a>
            <button wire:click="save" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/25 transition-all">
                ذخیره نهایی نوشته
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('ai_error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl text-xs font-semibold">
            {{ session('ai_error') }}
        </div>
    @endif

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Main Editor Section (3 columns) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Tabs --}}
            <div class="flex border-b border-gray-100 dark:border-gray-700 gap-6">
                <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-xs transition-colors">محتوای اصلی</button>
                <button @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-xs transition-colors">تنظیمات سئو (SEO)</button>
                <button @click="activeTab = 'gallery'" :class="activeTab === 'gallery' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-xs transition-colors">گالری تصاویر</button>
                @if($isEdit)
                    <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-xs transition-colors">تاریخچه تغییرات</button>
                @endif
            </div>

            {{-- Tab Content: Main Content --}}
            <div x-show="activeTab === 'content'" class="space-y-6">
                {{-- Title & Auto-slug helper with Alpine --}}
                <div x-data="{
                    autoSlug: {{ $isEdit ? 'false' : 'true' }},
                    title: @entangle('title').live,
                    slug: @entangle('slug').live,
                    generateSlug() {
                        if (this.title) {
                            this.slug = this.title
                                .toLowerCase()
                                .replace(/[^a-z0-9\u0600-\u06FF\s-]/g, '')
                                .trim()
                                .replace(/\s+/g, '-')
                                .replace(/-+/g, '-');
                        }
                    }
                }" x-init="
                    $watch('title', value => { if(autoSlug) generateSlug() });
                    $watch('autoSlug', value => { if(value) generateSlug() });
                " class="space-y-4">
                    {{-- Title input --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان اصلی</label>
                        <input type="text" x-model="title" placeholder="عنوان نوشته یا برگه را بنویسید..." class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-bold text-lg focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm transition-all">
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- URL preview box --}}
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400">آدرس نهایی صفحه:</span>
                            <span class="font-mono text-xs text-indigo-600 dark:text-indigo-400 dir-ltr text-left">
                                {{ url('/') }}/<span class="font-bold" x-text="slug || 'slug-name'"></span>
                            </span>
                        </div>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" x-model="autoSlug" class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-800">
                            <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">تولید خودکار آدرس</span>
                        </label>
                    </div>
                </div>

                {{-- Excerpt Field --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">خلاصه / چکیده کوتاه</label>
                        @if($aiAvailable)
                            <button type="button" wire:click="aiGenerateExcerpt" class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                تولید خودکار با AI
                            </button>
                        @endif
                    </div>
                    <textarea wire:model.live="excerpt" rows="3" placeholder="توضیح کوتاهی درباره این نوشته برای نمایش در لیست‌ها..." class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm transition-all"></textarea>
                    @error('excerpt') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Body content (Quill editor) --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300">متن اصلی محتوا</label>
                    <div
                        wire:ignore
                        x-data="{
                            content: @entangle('bodyHtml').live,
                            initQuill() {
                                let quill = new Quill(this.$refs.editor, {
                                    theme: 'snow',
                                    placeholder: 'متن نوشته را به صورت کامل و شکیل بنویسید...',
                                    modules: {
                                        toolbar: [
                                            [{ 'header': [1, 2, 3, false] }],
                                            ['bold', 'italic', 'underline', 'strike'],
                                            [{ 'color': [] }, { 'background': [] }],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            [{ 'align': [] }, { 'direction': 'rtl' }],
                                            ['link', 'image', 'video'],
                                            ['clean']
                                        ]
                                    }
                                });
                                quill.on('text-change', () => {
                                    this.content = quill.root.innerHTML;
                                });
                                quill.root.innerHTML = this.content || '';

                                this.$watch('content', value => {
                                    if (value !== quill.root.innerHTML) {
                                        quill.root.innerHTML = value || '';
                                    }
                                });
                            }
                        }"
                        x-init="initQuill()"
                        class="custom-quill-wrapper border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-800 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 shadow-sm"
                    >
                        <div x-ref="editor" class="focus:outline-none min-h-[400px] text-right font-bold text-gray-800 dark:text-gray-200"></div>
                    </div>
                    @error('bodyHtml') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Tab Content: SEO --}}
            <div x-show="activeTab === 'seo'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">عنوان سئو (Meta Title)</label>
                        <input type="text" wire:model.live="seoTitle" placeholder="پیش‌فرض: همان عنوان اصلی" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300 font-mono">نامک دستی آدرس (Slug)</label>
                        <input type="text" wire:model.blur="slug" placeholder="custom-slug" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono text-left focus:outline-none">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">توضیحات سئو (Meta Description)</label>
                        @if($aiAvailable)
                            <button type="button" wire:click="aiGenerateSeoDescription" class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline">تولید با AI</button>
                        @endif
                    </div>
                    <textarea wire:model.live="seoDescription" rows="3" placeholder="توضیحات کوتاه برای نمایش در موتورهای جستجو..." class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">کلمات کلیدی (کاما جداکننده)</label>
                            @if($aiAvailable)
                                <button type="button" wire:click="aiSuggestTags" class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline">پیشنهاد با AI</button>
                            @endif
                        </div>
                        <input type="text" wire:model.live="tagsInput" placeholder="کلمات کلیدی..." class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">آدرس کانونی (Canonical URL)</label>
                        <input type="text" wire:model.live="canonicalUrl" placeholder="https://..." class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-left font-mono focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Tab Content: Gallery --}}
            <div x-show="activeTab === 'gallery'" class="space-y-6">
                <div class="p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl flex flex-col items-center justify-center gap-4 bg-gray-50/50 dark:bg-gray-900/10">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <div class="text-center">
                        <span class="text-xs text-gray-500 block">افزودن تصویر جدید به گالری تصاویر این نوشته</span>
                    </div>
                    <label class="px-4 py-2 bg-white dark:bg-gray-800 border rounded-xl text-xs font-bold cursor-pointer shadow-sm">
                        انتخاب تصویر
                        <input type="file" wire:model="galleryImageFile" class="hidden">
                    </label>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($gallery as $idx => $img)
                        <div class="relative group rounded-xl overflow-hidden border">
                            <img src="{{ asset('storage/' . $img) }}" class="w-full h-32 object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" wire:click="removeGalleryImage({{ $idx }})" class="p-2 bg-red-600 text-white rounded-lg text-xs font-bold">
                                    حذف تصویر
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tab Content: History --}}
            @if($isEdit)
                <div x-show="activeTab === 'history'" class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300">تاریخچه نسخه‌ها</h3>
                    <div class="space-y-3">
                        @forelse($revisions as $rev)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold block">ویرایش کننده: {{ $rev->user->name }}</span>
                                    <span class="text-gray-400 font-mono block mt-0.5">{{ $rev->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">نسخه شماره {{ $loop->remaining + 1 }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-6">هیچ نسخه‌ای ثبت نشده است.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Meta Widgets --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Publish Box --}}
            <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white border-b pb-2">تنظیمات انتشار</h3>

                <div class="space-y-1">
                    <label class="text-xs text-gray-500">وضعیت نوشته</label>
                    <select wire:model.live="status" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs focus:outline-none">
                        <option value="draft">پیش‌نویس</option>
                        <option value="published">منتشر شده</option>
                        <option value="scheduled">زمان‌بندی انتشار</option>
                        <option value="archived">بایگانی شده</option>
                    </select>
                </div>

                @if($status === 'scheduled')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500">تاریخ و زمان انتشار</label>
                        <input type="text" wire:model.live="scheduledAt" placeholder="2026-07-06 14:00" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs font-mono">
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="text-xs text-gray-500">نوع دسترسی</label>
                    <select wire:model.live="visibility" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs focus:outline-none">
                        <option value="public">عمومی</option>
                        <option value="private">خصوصی (کاربران سیستم)</option>
                        <option value="password">رمز عبور صفحه</option>
                    </select>
                </div>

                @if($visibility === 'password')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500">رمز عبور صفحه</label>
                        <input type="text" wire:model.live="password" placeholder="رمز..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs">
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="text-xs text-gray-500">موجودیت مرتبط</label>
                    <select wire:model.live="entityId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs focus:outline-none">
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-gray-500">قالب اختصاصی (Theme Key)</label>
                    <input type="text" wire:model.live="themeKey" placeholder="مثلا: custom-theme" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs font-mono text-left focus:outline-none">
                </div>

                @if($type === 'post')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500">دسته‌بندی وبلاگ</label>
                        <select wire:model.live="categoryId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs focus:outline-none">
                            <option value="">فاقد دسته‌بندی</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="space-y-2 pt-2">
                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-bold cursor-pointer">
                        <input type="checkbox" wire:model.live="featured" class="w-4 h-4 text-indigo-600 border-gray-200 rounded">
                        مطلب ویژه
                    </label>

                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-bold cursor-pointer">
                        <input type="checkbox" wire:model.live="allowComments" class="w-4 h-4 text-indigo-600 border-gray-200 rounded">
                        امکان ارسال دیدگاه
                    </label>
                </div>
            </div>

            {{-- Cover Image Widget --}}
            <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white border-b pb-2">تصویر کاور</h3>
                @if($coverImage)
                    <div class="relative group rounded-xl overflow-hidden border">
                        <img src="{{ asset('storage/' . $coverImage) }}" class="w-full h-36 object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button type="button" wire:click="$set('coverImage', null)" class="p-2 bg-red-600 text-white rounded-lg text-xs font-bold">
                                حذف تصویر
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-6 border-2 border-dashed border-gray-200 dark:border-gray-750 rounded-xl flex flex-col items-center justify-center text-center gap-2 bg-gray-50/50 dark:bg-gray-900/10">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs text-gray-400">عکسی آپلود نشده</span>
                        <label class="mt-2 px-4 py-1.5 bg-white dark:bg-gray-800 border rounded-lg text-[10px] font-bold cursor-pointer shadow-sm">
                            آپلود تصویر کاور
                            <input type="file" wire:model="coverImageFile" class="hidden">
                        </label>
                    </div>
                @endif
            </div>

            {{-- AI Panel --}}
            @if($aiAvailable)
                <div class="p-6 bg-purple-50/50 dark:bg-purple-950/10 border border-purple-100 dark:border-purple-900/40 rounded-2xl space-y-4">
                    <h3 class="text-xs font-bold text-purple-950 dark:text-purple-300 border-b pb-2">دستیار هوش مصنوعی</h3>
                    <div class="space-y-1">
                        <label class="text-[10px] text-purple-700 dark:text-purple-400">موضوع محتوا</label>
                        <input type="text" wire:model.live="aiTopic" placeholder="مثلاً: آموزش اصول فروش..." class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-800 rounded-lg text-xs">
                    </div>
                    <button type="button" wire:click="aiSuggestTitle" class="w-full px-3 py-2 bg-purple-600 hover:bg-purple-750 text-white rounded-lg text-xs font-bold">
                        پیشنهاد عنوان با AI
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
