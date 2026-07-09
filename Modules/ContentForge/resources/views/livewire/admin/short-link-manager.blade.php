<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت لینک‌های کوتاه</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت و شخصی‌سازی کدهای ارجاع دهنده لینک‌های کوتاه مقالات</p>
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
                تعیین کد سفارشی
            </h3>

            @if($editingShortLinkId)
                <div class="space-y-3">
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                        <span class="text-xs text-gray-400 block">نوشته مرتبط:</span>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block mt-1">
                            {{ \Modules\ContentForge\App\Models\ContentShortLink::find($editingShortLinkId)->post->title ?? '' }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">کد اختصاصی (کلمات انگلیسی و اعداد)</label>
                        <input type="text" wire:model.live="customCode" placeholder="مثلاً: crm-intro" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none focus:border-indigo-500">
                        @error('customCode') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" wire:click="save" class="flex-grow px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                            ذخیره کد سفارشی
                        </button>
                        <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold transition-all">
                            انصراف
                        </button>
                    </div>
                </div>
            @else
                <p class="text-xs text-gray-400 py-6 text-center">برای تنظیم کد سفارشی لینک کوتاه، یکی از رکوردهای جدول روبروی خود را به حالت «ویرایش» درآورید.</p>
            @endif
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div class="relative w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو در لینک‌های کوتاه..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
            </div>

            <div class="border rounded-xl overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <tr class="border-b">
                            <th class="p-3">نوشته</th>
                            <th class="p-3">لینک کوتاه سیستمی</th>
                            <th class="p-3">لینک کوتاه سفارشی</th>
                            <th class="p-3 text-center">تعداد کلیک</th>
                            <th class="p-3 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        @forelse($links as $link)
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3">
                                    <span class="font-bold block line-clamp-1">{{ $link->post->title }}</span>
                                    <span class="text-xs text-gray-400 font-mono">{{ $link->post->slug }}</span>
                                </td>
                                <td class="p-3 font-mono text-xs text-indigo-600">
                                    <a href="{{ \Modules\ContentForge\App\Services\ShortLinkService::getFullUrl($link) }}" target="_blank" class="hover:underline">
                                        /{{ \Modules\ContentForge\Entities\ContentSetting::getValue('short_link.prefix', 's') }}/{{ $link->code }}
                                    </a>
                                </td>
                                <td class="p-3 font-mono text-xs text-purple-600">
                                    @if($link->custom_code)
                                        <a href="{{ url('/' . \Modules\ContentForge\Entities\ContentSetting::getValue('short_link.prefix', 's') . '/' . $link->custom_code) }}" target="_blank" class="hover:underline">
                                            /{{ \Modules\ContentForge\Entities\ContentSetting::getValue('short_link.prefix', 's') }}/{{ $link->custom_code }}
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">تنظیم نشده</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center font-bold text-gray-700 dark:text-gray-300">{{ $link->click_count }} کلیک</td>
                                <td class="p-3 text-left">
                                    <button wire:click="edit({{ $link->id }})" class="p-1 hover:bg-gray-100 rounded text-gray-500" title="ویرایش کد سفارشی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400">هیچ لینکی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($links->hasPages())
                <div>{{ $links->links() }}</div>
            @endif
        </div>
    </div>
</div>
