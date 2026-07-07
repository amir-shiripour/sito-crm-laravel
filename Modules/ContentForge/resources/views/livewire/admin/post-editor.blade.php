<div class="p-6 max-w-7xl mx-auto space-y-8" x-data="{ activeTab: 'content' }">
    {{-- Scripts for Tiptap --}}
    @once
        <script type="module">
            import { Editor } from 'https://esm.sh/@tiptap/core';
            import StarterKit from 'https://esm.sh/@tiptap/starter-kit';
            import Link from 'https://esm.sh/@tiptap/extension-link';

            window.Tiptap = { Editor, StarterKit, Link };
            window.dispatchEvent(new CustomEvent('tiptap-loaded'));
        </script>
        <style>
            .ProseMirror { min-height: 400px; outline: none; padding: 1.5rem; }
            .ProseMirror p.is-editor-empty:first-child::before {
                content: attr(data-placeholder);
                float: right;
                color: #adb5bd;
                pointer-events: none;
                height: 0;
            }
        </style>
    @endonce

    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                {{ $isEdit ? 'ویرایش ' . ($type === 'post' ? 'نوشته وبلاگ' : 'برگه') : 'ایجاد ' . ($type === 'post' ? 'نوشته جدید' : 'برگه جدید') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">طراحی و انتشار صفحات وب با سئو و هوش مصنوعی</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.content.' . ($type === 'post' ? 'posts' : 'pages') . '.index') }}" class="px-4 py-2 border rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                انصراف
            </a>
            <button wire:click="save" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all">
                ذخیره اطلاعات
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('ai_error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl text-sm font-semibold">
            {{ session('ai_error') }}
        </div>
    @endif

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Main Editor Section (3 columns) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Tabs --}}
            <div class="flex border-b border-gray-100 dark:border-gray-700 gap-6">
                <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-sm transition-colors">محتوای اصلی</button>
                <button @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-sm transition-colors">تنظیمات سئو (SEO)</button>
                <button @click="activeTab = 'gallery'" :class="activeTab === 'gallery' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-sm transition-colors">گالری تصاویر</button>
                @if($isEdit)
                    <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2' : 'text-gray-500 hover:text-indigo-600'" class="pb-3 text-sm transition-colors">تاریخچه تغییرات</button>
                @endif
            </div>

            {{-- Tab Contents --}}
            <div x-show="activeTab === 'content'" class="space-y-6">
                {{-- Title Field --}}
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">عنوان برگه/نوشته</label>
                    <input type="text" wire:model.blur="title" placeholder="عنوان را بنویسید..." class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-bold text-lg focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                    @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Excerpt Field --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">خلاصه / چکیده کوتاه</label>
                        @if($aiAvailable)
                            <button type="button" wire:click="aiGenerateExcerpt" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                تولید خودکار با AI
                            </button>
                        @endif
                    </div>
                    <textarea wire:model.live="excerpt" rows="3" placeholder="توضیح کوتاهی درباره این نوشته برای نمایش در لیست‌ها..." class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500"></textarea>
                    @error('excerpt') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Body Tiptap Editor --}}
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">متن محتوا</label>
                    
                    {{-- Tiptap Alpine Wrapper --}}
                    <div x-data="{
                        htmlContent: @entangle('bodyHtml').live,
                        editor: null,
                        initEditor() {
                            if (!window.Tiptap) {
                                window.addEventListener('tiptap-loaded', () => this.initEditor(), { once: true });
                                return;
                            }
                            this.editor = new window.Tiptap.Editor({
                                element: this.$refs.editorContainer,
                                extensions: [
                                    window.Tiptap.StarterKit,
                                    window.Tiptap.Link.configure({ openOnClick: false }),
                                ],
                                content: this.htmlContent,
                                onUpdate: ({ editor }) => {
                                    this.htmlContent = editor.getHTML();
                                }
                            });

                            this.$watch('htmlContent', value => {
                                if (value !== this.editor.getHTML()) {
                                    this.editor.commands.setContent(value, false);
                                }
                            });
                        }
                    }" x-init="initEditor()" class="border rounded-2xl bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
                        
                        {{-- Editor toolbar --}}
                        <div class="border-b bg-gray-50 dark:bg-gray-900/50 p-2 flex flex-wrap items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            {{-- toolbar items can go here. For simplicity, Tiptap's standard bubble/focus commands will be available --}}
                            <span class="text-xs px-2 text-gray-400">تایپ کنید و برای ویرایش متن را انتخاب کنید</span>
                        </div>

                        {{-- ProseMirror container --}}
                        <div x-ref="editorContainer" class="prose dark:prose-invert max-w-none text-right"></div>
                    </div>
                </div>
            </div>

            {{-- SEO Tab --}}
            <div x-show="activeTab === 'seo'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">عنوان سئو (Meta Title)</label>
                        <input type="text" wire:model.live="seoTitle" placeholder="پیش‌فرض: همان عنوان اصلی" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300 font-mono">آدرس یکتا (Slug / URL)</label>
                        <input type="text" wire:model.blur="slug" placeholder="slug-name-here" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono text-left focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                        @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">توضیحات سئو (Meta Description)</label>
                        @if($aiAvailable)
                            <button type="button" wire:click="aiGenerateSeoDescription" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                تولید با AI
                            </button>
                        @endif
                    </div>
                    <textarea wire:model.live="seoDescription" rows="3" placeholder="حداکثر ۱۶۰ کاراکتر..." class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">کلمات کلیدی (کاما جداکننده)</label>
                            @if($aiAvailable)
                                <button type="button" wire:click="aiSuggestTags" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">پیشنهاد کلمات کلیدی با AI</button>
                            @endif
                        </div>
                        <input type="text" wire:model.live="tagsInput" placeholder="آموزش سئو, تولید محتوا, وبلاگ" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">آدرس کانونی (Canonical URL)</label>
                        <input type="text" wire:model.live="canonicalUrl" placeholder="https://example.com/custom-url" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-left font-mono focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Gallery Tab --}}
            <div x-show="activeTab === 'gallery'" class="space-y-6">
                <div class="p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl flex flex-col items-center justify-center gap-4 bg-gray-50/50 dark:bg-gray-900/10">
                    <div class="text-gray-400">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500 block">افزودن تصویر جدید به گالری تصاویر این پست</span>
                        <span class="text-xs text-gray-400 block mt-1">فرمت‌های مجاز: JPG, PNG, WEBP (حداکثر ۵ مگابایت)</span>
                    </div>
                    <label class="px-5 py-2.5 bg-white dark:bg-gray-800 hover:bg-gray-50 border rounded-xl text-xs font-bold cursor-pointer transition-colors shadow-sm">
                        <span>انتخاب تصویر</span>
                        <input type="file" wire:model="galleryImageFile" class="hidden">
                    </label>
                </div>

                @if(session()->has('gallery_success'))
                    <span class="text-xs text-emerald-600 font-bold block">{{ session('gallery_success') }}</span>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($gallery as $idx => $img)
                        <div class="relative group rounded-xl overflow-hidden border">
                            <img src="{{ asset('storage/' . $img) }}" class="w-full h-32 object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" wire:click="removeGalleryImage({{ $idx }})" class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Revisions Tab --}}
            @if($isEdit)
                <div x-show="activeTab === 'history'" class="space-y-6">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">تاریخچه نسخه‌های ذخیره شده</h3>
                    <div class="space-y-4">
                        @forelse($revisions as $rev)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl flex items-center justify-between text-sm">
                                <div>
                                    <span class="font-bold block">ویرایش شده توسط: {{ $rev->user->name }}</span>
                                    <span class="text-xs text-gray-400 font-mono mt-0.5 block">{{ $rev->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <span class="text-xs text-indigo-600 font-bold">نسخه قبلی ذخیره شد</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-6">هیچ تاریخچه ذخیره‌ای برای این نوشته یافت نشد.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Meta Widgets (1 column) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Publish Box --}}
            <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white border-b pb-2">تنظیمات انتشار</h3>

                {{-- Status --}}
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">وضعیت نوشته</label>
                    <select wire:model.live="status" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                        <option value="draft">پیش‌نویس</option>
                        <option value="published">منتشر شده</option>
                        <option value="scheduled">زمان‌بندی انتشار</option>
                        <option value="archived">بایگانی شده</option>
                    </select>
                </div>

                {{-- Scheduled Date --}}
                @if($status === 'scheduled')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">تاریخ و زمان انتشار</label>
                        <input type="text" wire:model.live="scheduledAt" placeholder="2026-07-06 14:00" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                @endif

                {{-- Visibility --}}
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">نوع دسترسی (دیدرس)</label>
                    <select wire:model.live="visibility" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                        <option value="public">عمومی (همه)</option>
                        <option value="private">خصوصی (فقط کاربران سیستم)</option>
                        <option value="password">محافظت با رمز عبور</option>
                    </select>
                </div>

                {{-- Password visibility field --}}
                @if($visibility === 'password')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">رمز عبور صفحه</label>
                        <input type="text" wire:model.live="password" placeholder="رمز عبور..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                        @error('password') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                {{-- Entity --}}
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">موجودیت مرتبط (Multi-Tenant)</label>
                    <select wire:model.live="entityId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category (Only if post type is 'post') --}}
                @if($type === 'post')
                    <div class="space-y-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">دسته‌بندی وبلاگ</label>
                        <select wire:model.live="categoryId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                            <option value="">فاقد دسته‌بندی</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Toggle settings --}}
                <div class="space-y-3 pt-2">
                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-bold cursor-pointer">
                        <input type="checkbox" wire:model.live="featured" class="w-4 h-4 text-indigo-600 border-gray-200 rounded focus:ring-indigo-500">
                        مطلب ویژه (Featured)
                    </label>

                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-bold cursor-pointer">
                        <input type="checkbox" wire:model.live="allowComments" class="w-4 h-4 text-indigo-600 border-gray-200 rounded focus:ring-indigo-500">
                        امکان ارسال دیدگاه کاربر
                    </label>
                </div>
            </div>

            {{-- Cover Image Box --}}
            <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white border-b pb-2">تصویر کاور</h3>
                
                @if($coverImage)
                    <div class="relative group rounded-xl overflow-hidden border">
                        <img src="{{ asset('storage/' . $coverImage) }}" class="w-full h-36 object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button type="button" wire:click="$set('coverImage', null)" class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                حذف تصویر
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl flex flex-col items-center justify-center text-center gap-2 bg-gray-50/50 dark:bg-gray-900/10">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs text-gray-400">تصویری آپلود نشده است</span>
                        <label class="mt-2 px-4 py-1.5 bg-white dark:bg-gray-800 border rounded-lg text-xs font-bold cursor-pointer shadow-sm">
                            آپلود تصویر کاور
                            <input type="file" wire:model="coverImageFile" class="hidden">
                        </label>
                    </div>
                @endif
                @if(session()->has('cover_success'))
                    <span class="text-xs text-emerald-600 font-bold block mt-1">{{ session('cover_success') }}</span>
                @endif
            </div>

            {{-- AI Side panel (If AI available) --}}
            @if($aiAvailable)
                <div class="p-6 bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/50 rounded-2xl shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-purple-900 dark:text-purple-300 border-b border-purple-100 dark:border-purple-900/30 pb-2">دستیار هوش مصنوعی</h3>
                    
                    <div class="space-y-1">
                        <label class="text-xs text-purple-700 dark:text-purple-400">موضوع تولید محتوا</label>
                        <input type="text" wire:model.live="aiTopic" placeholder="مثلاً: تکنیک‌های سئو ۲۰۲۶..." class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-800 rounded-lg text-xs focus:outline-none">
                        @error('aiTopic') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="button" wire:click="aiSuggestTitle" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                        پیشنهاد عنوان مقاله با AI
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
