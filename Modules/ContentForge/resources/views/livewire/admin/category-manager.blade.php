<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت دسته‌بندی‌های وبلاگ</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت ساختار درختی دسته‌بندی نوشته‌ها</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Grid layout (Form on right/left, List on other side) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form (1 column) --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2">
                {{ $editingCategoryId ? 'ویرایش دسته‌بندی' : 'ایجاد دسته‌بندی جدید' }}
            </h3>

            {{-- Entity --}}
            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">موجودیت مرتبط</label>
                <select wire:model.live="entityId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                    @foreach($entities as $ent)
                        <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                    @endforeach
                </select>
                @error('entityId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Parent Category --}}
            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">دسته‌بندی والد</label>
                <select wire:model.live="parentId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                    <option value="">فاقد دسته والد (دسته اصلی)</option>
                    @foreach($allCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('parentId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Name --}}
            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نام دسته‌بندی</label>
                <input type="text" wire:model.live="name" placeholder="مثلاً: اخبار کسب و کار" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Slug --}}
            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نامک (URL)</label>
                <input type="text" wire:model.live="slug" placeholder="business-news" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none focus:border-indigo-500">
                @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Description --}}
            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">توضیحات کوتاه</label>
                <textarea wire:model.live="description" rows="3" placeholder="توضیحات کوتاه..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            {{-- SEO Fields --}}
            <div class="border-t pt-4 space-y-3">
                <h4 class="text-xs font-bold text-gray-400">تنظیمات سئو</h4>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">عنوان سئو</label>
                    <input type="text" wire:model.live="seoTitle" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">توضیحات سئو</label>
                    <textarea wire:model.live="seoDescription" rows="2" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none"></textarea>
                </div>
            </div>

            {{-- Submit actions --}}
            <div class="flex items-center gap-2 pt-2">
                <button type="button" wire:click="save" class="flex-grow px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                    {{ $editingCategoryId ? 'ویرایش دسته' : 'ایجاد دسته' }}
                </button>
                @if($editingCategoryId)
                    <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold transition-all">
                        انصراف
                    </button>
                @endif
            </div>
        </div>

        {{-- Tree List (2 columns) --}}
        <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-md font-bold text-gray-900 dark:text-white">ساختار درختی دسته‌بندی‌ها</h3>
                <div class="text-xs text-gray-400">موجودیت انتخاب شده: {{ $entities->firstWhere('id', $entityId)->name ?? '' }}</div>
            </div>

            <div class="space-y-4">
                @forelse($categories as $cat)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $cat->name }}</span>
                                <span class="text-xs text-gray-400 font-mono ml-2">/{{ $cat->slug }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button wire:click="edit({{ $cat->id }})" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition-colors" title="ویرایش">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="confirm('آیا از حذف این دسته و تمامی زیرمجموعه‌های آن اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="delete({{ $cat->id }})" class="p-1 hover:bg-red-50 dark:hover:bg-red-950/20 rounded text-gray-500 dark:text-gray-400 hover:text-red-600 transition-colors" title="حذف">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Children recursively --}}
                        @if($cat->children->isNotEmpty())
                            <div class="pr-6 border-r border-gray-200 dark:border-gray-700 space-y-2.5 mt-2">
                                @foreach($cat->children as $child)
                                    <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-800 border rounded-lg text-xs">
                                        <div>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $child->name }}</span>
                                            <span class="text-gray-400 font-mono ml-2">/{{ $child->slug }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="edit({{ $child->id }})" class="p-1 hover:bg-gray-100 rounded text-gray-500" title="ویرایش">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button onclick="confirm('حذف شود؟') || event.stopImmediatePropagation()" wire:click="delete({{ $child->id }})" class="p-1 hover:bg-red-50 rounded text-gray-500 hover:text-red-600" title="حذف">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-12">هیچ دسته‌بندی برای این موجودیت ثبت نشده است.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
