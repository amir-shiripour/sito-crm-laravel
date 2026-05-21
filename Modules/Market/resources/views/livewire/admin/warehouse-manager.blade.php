@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">مدیریت انبارها</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ایجاد، ویرایش و مشاهده لیست انبارهای سیستم</p>
        </div>
        <button wire:click="create()" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            ایجاد انبار جدید
        </button>
    </div>

    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="relative w-full max-w-md">
                <input type="text" class="{{ $inputClass }} pl-10" placeholder="جستجو در نام، کد یا فروشنده..." wire:model.live.debounce.300ms="search">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">نام</th>
                        <th scope="col" class="px-6 py-3">کد</th>
                        @if($isMultiVendor)
                            <th scope="col" class="px-6 py-3">مالک (فروشنده)</th>
                        @endif
                        <th scope="col" class="px-6 py-3">وضعیت</th>
                        <th scope="col" class="px-6 py-3 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $warehouse->name }}
                            </th>
                            <td class="px-6 py-4 font-mono">{{ $warehouse->code }}</td>
                            @if($isMultiVendor)
                                <td class="px-6 py-4">{{ $warehouse->vendor->store_name ?? 'انبار مرکزی' }}</td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $warehouse->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $warehouse->is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2 space-x-reverse">
                                <a href="{{ route('user.market.warehouse-stock.index', $warehouse->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">موجودی</a>
                                <button wire:click="edit({{ $warehouse->id }})" class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">ویرایش</button>
                                <button wire:click="delete({{ $warehouse->id }})" wire:confirm="آیا از حذف این انبار مطمئن هستید؟" class="font-medium text-red-600 dark:text-red-500 hover:underline">حذف</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isMultiVendor ? 5 : 4 }}" class="text-center py-10 text-gray-500 dark:text-gray-400">
                                هیچ انباری یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-900/80 z-40" x-on:click="$dispatch('close-modal')"></div>
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md m-4" @click.away="$dispatch('close-modal')">
                <form wire:submit.prevent="store">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $warehouseId ? 'ویرایش انبار' : 'ایجاد انبار جدید' }}</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($isMultiVendor)
                            <div>
                                <label for="vendor_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">مالک انبار (فروشنده)</label>
                                <select id="vendor_id" class="{{ $inputClass }}" wire:model="vendor_id">
                                    <option value="">انبار مرکزی (متعلق به سیستم)</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->store_name }}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نام انبار</label>
                            <input type="text" id="name" class="{{ $inputClass }}" wire:model="name">
                            @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">کد انبار (منحصر به فرد)</label>
                            <input type="text" id="code" class="{{ $inputClass }} font-mono" wire:model="code">
                            @error('code') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <label class="flex items-center gap-3 pt-2">
                            <input type="checkbox" id="is_active" class="{{ $checkboxClass }}" wire:model="is_active">
                            <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">انبار فعال باشد</label>
                        </label>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-b-2xl flex items-center justify-end gap-4">
                        <button type="button" x-on:click="$dispatch('close-modal')" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center gap-2">
                            <span wire:loading.remove wire:target="store">ذخیره</span>
                            <span wire:loading wire:target="store">در حال پردازش...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
