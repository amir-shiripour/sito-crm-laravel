<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                {{ $type === 'post' ? 'مدیریت نوشته‌های وبلاگ' : 'مدیریت برگه‌های ایستا' }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $type === 'post' ? 'لیست تمام نوشته‌ها و پیش‌نویس‌های وبلاگ شما' : 'لیست برگه‌های ثابت و ایستای سایت شما' }}
            </p>
        </div>
        <div>
            <a href="{{ route('user.content.' . ($type === 'post' ? 'posts' : 'pages') . '.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ $type === 'post' ? 'نوشته جدید' : 'برگه جدید' }}
            </a>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="p-4 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Search Input --}}
            <div class="relative w-full md:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی عنوان یا نامک..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            {{-- Status Filter --}}
            <select wire:model.live="statusFilter" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                <option value="all">همه وضعیت‌ها</option>
                <option value="published">منتشر شده</option>
                <option value="draft">پیش‌نویس</option>
                <option value="scheduled">زمان‌بندی شده</option>
                <option value="archived">بایگانی شده</option>
            </select>

            {{-- Entity Filter --}}
            <select wire:model.live="entityFilter" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-500">
                <option value="">همه موجودیت‌ها</option>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Content Table --}}
    <div class="bg-white dark:bg-gray-800 border rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="p-4">عنوان</th>
                        <th class="p-4">موجودیت</th>
                        <th class="p-4">دسته‌بندی</th>
                        <th class="p-4">نویسنده</th>
                        <th class="p-4">وضعیت</th>
                        <th class="p-4">بازدید / دیدگاه</th>
                        <th class="p-4">تاریخ انتشار</th>
                        <th class="p-4 text-left">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($posts as $p)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-all text-sm text-gray-700 dark:text-gray-200">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if($p->cover_image)
                                        <img src="{{ asset('storage/' . $p->cover_image) }}" class="w-10 h-10 object-cover rounded-lg">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-bold line-clamp-1">{{ $p->title }}</h4>
                                        <span class="text-xs text-gray-400 font-mono">{{ $p->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">{{ $p->entity->name }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $p->category->name ?? 'ندارد' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-semibold">{{ $p->author->name ?? 'مدیر' }}</span>
                            </td>
                            <td class="p-4">
                                @php
                                    $statusColors = [
                                        'published' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400',
                                        'draft' => 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        'scheduled' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400',
                                        'archived' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400',
                                    ];
                                    $statusLabels = [
                                        'published' => 'منتشر شده',
                                        'draft' => 'پیش‌نویس',
                                        'scheduled' => 'زمان‌بندی شده',
                                        'archived' => 'بایگانی شده',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$p->status->value] ?? '' }}">
                                    {{ $statusLabels[$p->status->value] ?? $p->status->value }}
                                </span>
                            </td>
                            <td class="p-4 text-xs font-semibold">
                                <span>{{ $p->view_count }} بازدید</span>
                                <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                                <span>{{ $p->comment_count }} نظر</span>
                            </td>
                            <td class="p-4 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $p->published_at?->format('Y-m-d H:i') ?? 'بازه پیش‌نویس' }}
                            </td>
                            <td class="p-4 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Preview link --}}
                                    @if($p->status->value === 'published')
                                        @php
                                            $previewUrl = $p->entity->is_default 
                                                ? url('/' . $p->slug) 
                                                : url('/' . $p->entity->slug . '/' . $p->slug);
                                        @endphp
                                        <a href="{{ $previewUrl }}" target="_blank" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition-colors" title="مشاهده فرانت">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @endif
                                    {{-- Edit link --}}
                                    <a href="{{ route('user.content.' . ($type === 'post' ? 'posts' : 'pages') . '.edit', $p) }}" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition-colors" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    {{-- Delete button --}}
                                    <button onclick="confirm('آیا از حذف این آیتم اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="delete({{ $p->id }})" class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg text-gray-500 dark:text-gray-400 hover:text-red-600 transition-colors" title="حذف">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2M5 13V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>هیچ موردی با فیلترهای انتخابی یافت نشد.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="p-4 border-t bg-gray-50 dark:bg-gray-900/50">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
