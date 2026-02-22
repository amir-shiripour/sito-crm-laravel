@php
    $isMyProperty = ($property->created_by === auth()->id() || $property->agent_id === auth()->id());
    $rowClass = $isMyProperty
        ? 'bg-indigo-50/60 dark:bg-indigo-900/10 hover:bg-indigo-100 dark:hover:bg-indigo-900/20'
        : 'hover:bg-gray-50 dark:hover:bg-gray-700/20';

    $canEdit = auth()->user()->hasRole('super-admin') ||
               auth()->user()->can('properties.edit.all') ||
               (auth()->user()->can('properties.edit') && ($isMyProperty || auth()->user()->can('properties.manage')));

    $canDelete = auth()->user()->hasRole('super-admin') ||
                 auth()->user()->can('properties.delete.all') ||
                 (auth()->user()->can('properties.delete') && ($isMyProperty || auth()->user()->can('properties.manage')));
@endphp

<tr class="group {{ $rowClass }} transition-colors duration-150">

    {{-- Title and Image --}}
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600">
                @if($property->cover_image)
                    <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover" alt="{{ $property->title }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                @endif
            </div>
            <div class="flex flex-col max-w-[130px]">
                <span class="font-bold text-gray-900 dark:text-white truncate block" title="{{ $property->title }}">{{ $property->title }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">Code: {{ $property->code }}</span>
            </div>
        </div>
    </td>

    {{-- Price --}}
    <td class="px-6 py-4">
        <div class="flex flex-col gap-1">
            @if($property->listing_type == 'sale' || $property->listing_type == 'presale')
                <span class="text-gray-900 dark:text-gray-100 font-medium">
                    {{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'توافقی' }}
                </span>
                @if($property->min_price > 0)
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        (کف: {{ number_format($property->min_price) }} تومان)
                    </span>
                @endif
            @elseif($property->listing_type == 'rent')
                <div class="text-xs text-gray-500">
                    <div>رهن: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $property->deposit_price > 0 ? number_format($property->deposit_price) : 'توافقی' }}</span></div>
                    <div>اجاره: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $property->rent_price > 0 ? number_format($property->rent_price) : 'توافقی' }}</span></div>
                </div>
            @endif
        </div>
    </td>

    {{-- Listing Type --}}
    <td class="px-6 py-4 hidden sm:table-cell">
        @php
            $typeClass = match($property->listing_type) {
                'sale' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
                'rent' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                'presale' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
                default => 'bg-gray-50 text-gray-700 border-gray-100'
            };
            $typeLabel = match($property->listing_type) {
                'sale' => 'فروش',
                'rent' => 'رهن و اجاره',
                'presale' => 'پیش‌فروش',
                default => $property->listing_type
            };
        @endphp
        <span class="{{ $badgeClass }} border {{ $typeClass }}">
            {{ $typeLabel }}
        </span>
    </td>

    {{-- Status --}}
    <td class="px-6 py-4">
        @if($property->status)
            <span class="{{ $badgeClass }}"
                  style="background-color: {{ $property->status->color }}15; color: {{ $property->status->color }}; border: 1px solid {{ $property->status->color }}30;">
                {{ $property->status->label ?? $property->status->name }}
            </span>
        @else
            <span class="text-xs text-gray-400">—</span>
        @endif
    </td>

    {{-- Category --}}
    <td class="px-6 py-4 hidden md:table-cell">
        @if($property->category)
            <span class="{{ $badgeClass }}" style="background-color: {{ $property->category->color }}15; color: {{ $property->category->color }}; border: 1px solid {{ $property->category->color }}30;">
                {{ $property->category->name }}
            </span>
        @else
            <span class="text-xs text-gray-400">—</span>
        @endif
    </td>

    {{-- Agent --}}
    @if($canManageProperties)
        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden lg:table-cell">
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center text-[10px] text-purple-600 dark:text-purple-300">
                    {{ mb_substr(optional($property->agent)->name ?? '?', 0, 1) }}
                </div>
                <span class="text-xs">{{ optional($property->agent)->name ?? 'نامشخص' }}</span>
            </div>
        </td>
    @endif

    {{-- Creator --}}
    @if($canManageProperties)
        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden lg:table-cell">
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] text-gray-500">
                    {{ mb_substr(optional($property->creator)->name ?? '?', 0, 1) }}
                </div>
                <span class="text-xs">{{ optional($property->creator)->name ?? 'نامشخص' }}</span>
            </div>
        </td>
    @endif

    {{-- Actions --}}
    <td class="px-6 py-4">
        <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
            @if($isTrash)
                {{-- Trash Actions --}}
                @if($canDelete)
                    <form action="{{ route('user.properties.restore', $property->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 transition-colors" title="بازیابی">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </form>
                    <form action="{{ route('user.properties.force-delete', $property->id) }}" method="POST" onsubmit="return confirm('آیا از حذف کامل و غیرقابل بازگشت این ملک اطمینان دارید؟');" class="inline-block">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors" title="حذف کامل">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                @endif
            @else
                {{-- Normal Actions --}}
                <a href="{{ route('properties.show', $property->slug) }}" target="_blank" class="p-2 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 transition-colors" title="مشاهده در سایت">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </a>
                @if($canEdit)
                    <a href="{{ route('user.properties.edit', $property) }}" class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors" title="ویرایش">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                @endif
                @if($canDelete)
                    <form action="{{ route('user.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('آیا از انتقال این ملک به سطل زباله اطمینان دارید؟');" class="inline-block">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors" title="حذف">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </td>
</tr>
