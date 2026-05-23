@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">انبارهای من</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست انبارهای فیزیکی و آنلاین شما</p>
        </div>
    </div>

    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="relative w-full max-w-md">
                <input type="text" class="{{ $inputClass }} pl-10" placeholder="جستجو در نام یا کد انبار..." wire:model.live.debounce.300ms="search">
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
                        <th scope="col" class="px-6 py-3">نوع</th>
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
                            <td class="px-6 py-4">{{ $warehouse->type }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $warehouse->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $warehouse->is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{-- 💡 فروشنده فقط می‌تواند موجودی را ببیند و تعدیل کند --}}
                                <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">مشاهده موجودی</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500 dark:text-gray-400">
                                شما هنوز انباری ثبت نکرده‌اید.
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
</div>
