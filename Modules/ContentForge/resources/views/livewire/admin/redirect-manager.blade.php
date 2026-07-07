<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت ریدایرکت‌های URL</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت آدرس‌های ارجاع داده شده جهت جلوگیری از بروز خطای ۴۰۴ صفحات قدیمی</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2">
                {{ $editingRedirectId ? 'ویرایش ریدایرکت' : 'ایجاد ریدایرکت جدید' }}
            </h3>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">آدرس مبدا (From URL)</label>
                <input type="text" wire:model.live="fromUrl" placeholder="/old-article-url" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none focus:border-indigo-500">
                @error('fromUrl') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">آدرس مقصد (To URL)</label>
                <input type="text" wire:model.live="toUrl" placeholder="/new-article-url" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none focus:border-indigo-500">
                @error('toUrl') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">نوع ریدایرکت</label>
                    <select wire:model.live="type" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                        <option value="301">301 (دائمی)</option>
                        <option value="302">302 (موقت)</option>
                    </select>
                    @error('type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">موجودیت مرتبط</label>
                    <select wire:model.live="entityId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                        <option value="">همه</option>
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" wire:click="save" class="flex-grow px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                    {{ $editingRedirectId ? 'ویرایش ریدایرکت' : 'ایجاد ریدایرکت' }}
                </button>
                @if($editingRedirectId)
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
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو در ریدایرکت‌ها..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
            </div>

            <div class="border rounded-xl overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <tr class="border-b">
                            <th class="p-3">آدرس مبدا (From)</th>
                            <th class="p-3">آدرس مقصد (To)</th>
                            <th class="p-3">نوع ریدایرکت</th>
                            <th class="p-3 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        @forelse($redirects as $red)
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $red->from_url }}</td>
                                <td class="p-3 font-mono text-xs text-indigo-600">{{ $red->to_url }}</td>
                                <td class="p-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $red->type === '301' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/20' }}">
                                        {{ $red->type === '301' ? '301 (دائمی)' : '302 (موقت)' }}
                                    </span>
                                </td>
                                <td class="p-3 text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="edit({{ $red->id }})" class="p-1 hover:bg-gray-100 rounded text-gray-500" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button onclick="confirm('آیا حذف شود؟') || event.stopImmediatePropagation()" wire:click="delete({{ $red->id }})" class="p-1 hover:bg-red-50 rounded text-gray-500 hover:text-red-600" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-400">هیچ ریدایرکتی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($redirects->hasPages())
                <div>{{ $redirects->links() }}</div>
            @endif
        </div>
    </div>
</div>
