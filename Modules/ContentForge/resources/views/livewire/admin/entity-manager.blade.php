<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت موجودیت‌های محتوا</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت موجودیت‌های مستقل برای کسب‌وکارهای چندگانه (CRM Multi-Entity)</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2">
                {{ $editingEntityId ? 'ویرایش موجودیت' : 'ایجاد موجودیت جدید' }}
            </h3>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نام موجودیت</label>
                <input type="text" wire:model.live="name" placeholder="مثلاً: هلدینگ صبا" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نامک آدرس (URL Slug)</label>
                <input type="text" wire:model.live="slug" placeholder="saba-holding" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none focus:border-indigo-500">
                @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">ماژول مبدا</label>
                    <input type="text" wire:model.live="moduleSource" placeholder="Market" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                    @error('moduleSource') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">شناسه مرجع</label>
                    <input type="number" wire:model.live="entityReferenceId" placeholder="1" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                    @error('entityReferenceId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">قالب اختصاصی (Theme Key)</label>
                <input type="text" wire:model.live="themeKey" placeholder="saba-theme" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none">
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-bold cursor-pointer">
                    <input type="checkbox" wire:model.live="isActive" class="w-4 h-4 text-indigo-600 border-gray-200 rounded">
                    موجودیت فعال باشد
                </label>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" wire:click="save" class="flex-grow px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                    {{ $editingEntityId ? 'ویرایش موجودیت' : 'ایجاد موجودیت' }}
                </button>
                @if($editingEntityId)
                    <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold transition-all">
                        انصراف
                    </button>
                @endif
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div class="relative w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
            </div>

            <div class="border rounded-xl overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <tr class="border-b">
                            <th class="p-3">نام</th>
                            <th class="p-3">نامک (Slug)</th>
                            <th class="p-3">قالب</th>
                            <th class="p-3">وضعیت</th>
                            <th class="p-3 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        @forelse($entities as $ent)
                            <tr class="hover:bg-gray-50/50 {{ $ent->is_default ? 'bg-indigo-50/20 dark:bg-indigo-950/10' : '' }}">
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold">{{ $ent->name }}</span>
                                        @if($ent->is_default)
                                            <span class="text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400 px-1.5 py-0.5 rounded font-bold">پیش‌فرض</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 block font-mono">{{ $ent->module_source ? "ماژول: {$ent->module_source} (#{$ent->entity_reference_id})" : 'عمومی' }}</span>
                                </td>
                                <td class="p-3 font-mono text-xs text-gray-400">{{ $ent->slug }}</td>
                                <td class="p-3 font-mono text-xs">{{ $ent->theme_key ?? 'content (پیش‌فرض)' }}</td>
                                <td class="p-3">
                                    @if($ent->is_active)
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded-full">فعال</span>
                                    @else
                                        <span class="text-xs text-gray-400 font-bold bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded-full">غیرفعال</span>
                                    @endif
                                </td>
                                <td class="p-3 text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(!$ent->is_default)
                                            <button wire:click="makeDefault({{ $ent->id }})" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline px-2 py-1 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 rounded" title="انتخاب به عنوان پیش‌فرض">
                                                انتخاب پیش‌فرض
                                            </button>
                                        @endif
                                        <button wire:click="edit({{ $ent->id }})" class="p-1 hover:bg-gray-100 rounded text-gray-500" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if(!$ent->is_default)
                                            <button onclick="confirm('آیا حذف شود؟') || event.stopImmediatePropagation()" wire:click="delete({{ $ent->id }})" class="p-1 hover:bg-red-50 rounded text-gray-500 hover:text-red-600" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400">هیچ موجودیتی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($entities->hasPages())
                <div>{{ $entities->links() }}</div>
            @endif
        </div>
    </div>
</div>
