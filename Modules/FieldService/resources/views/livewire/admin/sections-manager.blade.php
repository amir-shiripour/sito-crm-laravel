<div class="space-y-6 font-sans">

    {{-- Header & KPIs --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0 border border-indigo-500/20 shadow-sm">
                    🧩
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">مدیریت بخش‌ها و تایپ‌های خدمات</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">تعریف بخش‌های اصلی ارائه خدمات (مانند تعمیر، نصب، گارانتی، سرویس دوره‌ای و ...)</p>
                </div>
            </div>

            <button type="button" wire:click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/25 transition active:scale-[0.98] shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>تعریف بخش جدید</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/60">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">کل بخش‌های ثبت‌شده</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    🧩
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">بخش‌های فعال</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($activeCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✅
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">بخش‌های غیرفعال</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalCount - $activeCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    ⏸️
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('message'))
    <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="relative w-full md:flex-1">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="جستجو در عنوان بخش، نامک یا توضیحات..."
                   class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex items-center gap-1.5 w-full md:w-auto">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-medium shrink-0">وضعیت:</span>
            <select wire:model.live="statusFilter"
                    class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer w-full sm:w-44">
                <option value="all">همه وضعیت‌ها</option>
                <option value="active">فقط بخش‌های فعال</option>
                <option value="inactive">فقط بخش‌های غیرفعال</option>
            </select>
        </div>
    </div>

    {{-- Sections Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/70 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">ترتیب</th>
                        <th class="py-3.5 px-4">عنوان بخش و آیکون</th>
                        <th class="py-3.5 px-4 text-center">تعداد دسته‌ها</th>
                        <th class="py-3.5 px-4 text-center">وضعیت</th>
                        <th class="py-3.5 px-4 text-left">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-800 dark:text-slate-200">
                    @forelse($sections as $section)
                    <tr wire:key="section-row-{{ $section->id }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-700/60 font-black text-slate-700 dark:text-slate-300 font-sans">
                                {{ $section->sort_order }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-100 dark:border-indigo-500/30 flex items-center justify-center text-lg shrink-0 shadow-sm">
                                    {{ $section->icon ?: '🧩' }}
                                </span>
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ $section->name }}
                                    </div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 truncate max-w-md" title="{{ $section->description }}">
                                        <span class="font-sans font-medium text-slate-500 dark:text-slate-400">{{ $section->slug }}</span>
                                        @if($section->description)
                                            <span class="text-slate-400 mx-1">•</span>
                                            <span>{{ Str::limit($section->description, 60) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-xl bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-500/20">
                                {{ number_format($section->groups_count) }} دسته
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <button type="button" wire:click="toggleStatus({{ $section->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition cursor-pointer {{ $section->is_active ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-200/80 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-300/70' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $section->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $section->is_active ? 'فعال' : 'غیرفعال' }}</span>
                            </button>
                        </td>

                        <td class="py-3.5 px-4 text-left">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" wire:click="openEditModal({{ $section->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold transition">
                                    ویرایش
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $section->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 font-bold transition">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 px-4 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 flex items-center justify-center text-2xl mb-3">
                                    🧩
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">هنوز هیچ بخشی تعریف نشده است</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                    برای دسته‌بندی خدمات (مثلاً تعمیر، نصب، گارانتی) اولین بخش را تعریف کنید.
                                </p>
                                <button type="button" wire:click="openCreateModal"
                                        class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                    + تعریف بخش جدید
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sections->hasPages())
        <div class="p-4 border-t border-slate-200/80 dark:border-slate-700/60">
            {{ $sections->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div @click.outside="$wire.closeModal()"
             class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-2xl overflow-hidden my-auto font-sans">

            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xl flex items-center justify-center">
                        {{ $icon ?: '🧩' }}
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">
                            {{ $editingSectionId ? 'ویرایش بخش خدمات' : 'تعریف بخش جدید' }}
                        </h3>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">بخش یا تایپ کلی برای سازماندهی دسته‌ها و خدمات</p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white transition flex items-center justify-center">
                    ✕
                </button>
            </div>

            <form wire:submit.prevent="saveSection" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            عنوان بخش <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="name" placeholder="مثال: تعمیر، نصب، گارانتی"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            آیکون / اموجی
                        </label>
                        <input type="text" wire:model="icon" placeholder="🧩" maxlength="10"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-center text-base text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            شناسه انگلیسی (Slug) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="slug" placeholder="repair" dir="ltr"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left">
                        @error('slug') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            اولویت مرتب‌سازی
                        </label>
                        <input type="number" wire:model="sortOrder" min="0"
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center font-sans">
                        @error('sortOrder') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                        <input type="checkbox" wire:model="isActive" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">این بخش فعال باشد</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        توضیحات
                    </label>
                    <textarea wire:model="description" rows="2" placeholder="توضیح مختصر درباره این بخش..."
                              class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeModal"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                        انصراف
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 disabled:opacity-75 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition">
                        {{ $editingSectionId ? 'ذخیره تغییرات' : 'افزودن بخش' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-2xl text-center space-y-4 font-sans">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center text-2xl">
                ⚠️
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">تایید حذف بخش</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                    آیا از حذف بخش <span class="font-bold text-slate-900 dark:text-white">«{{ $deletingSectionName }}»</span> اطمینان دارید؟
                </p>
                @if($deletingSectionGroupsCount > 0)
                <div class="mt-3 p-3 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-700 dark:text-rose-300 text-xs text-right leading-relaxed">
                    ⛔ امکان حذف وجود ندارد: تعداد {{ number_format($deletingSectionGroupsCount) }} دسته زیرمجموعه این بخش هستند. ابتدا دسته‌ها را منتقل یا حذف کنید، یا این بخش را غیرفعال نمایید.
                </div>
                @endif
            </div>

            <div class="pt-3 flex items-center justify-center gap-3">
                <button type="button" wire:click="cancelDelete"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                    انصراف
                </button>
                @if($deletingSectionGroupsCount === 0)
                <button type="button" wire:click="deleteSection"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-lg shadow-rose-600/30 transition">
                    بله، حذف شود
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
