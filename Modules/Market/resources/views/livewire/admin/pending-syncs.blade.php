<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">تایید تغییرات اطلاعات مشتریان</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تغییرات ثبت شده در فرم تسویه حساب که نیاز به تایید شما دارند.</p>
            </div>
            @if($pendingLogs->isNotEmpty())
                <div class="flex items-center gap-2">
                    <button wire:click="approveAll" class="px-4 py-2 text-xs font-bold text-white bg-green-600 rounded-lg hover:bg-green-700">تایید همه</button>
                </div>
            @endif
        </div>

        @if($pendingLogs->isEmpty())
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                هیچ تغییری در انتظار تایید نیست.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="p-4">
                                <input type="checkbox" wire:model="selectedLogs" value="{{ $pendingLogs->pluck('id')->implode(',') }}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            </th>
                            <th scope="col" class="px-6 py-3">مشتری</th>
                            <th scope="col" class="px-6 py-3">فیلد</th>
                            <th scope="col" class="px-6 py-3">مقدار فعلی</th>
                            <th scope="col" class="px-6 py-3">مقدار جدید (از سفارش)</th>
                            <th scope="col" class="px-6 py-3">تاریخ</th>
                            <th scope="col" class="px-6 py-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingLogs as $log)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="w-4 p-4">
                                    <input type="checkbox" wire:model="selectedLogs" value="{{ $log->id }}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $log->client->full_name }}
                                    <span class="block text-xs text-gray-500">{{ $log->order->id }}#</span>
                                </td>
                                <td class="px-6 py-4">{{ $log->field_key }}</td>
                                <td class="px-6 py-4 text-gray-400">{{ $log->old_value ?: '-' }}</td>
                                <td class="px-6 py-4 font-bold text-green-500">{{ $log->new_value }}</td>
                                <td class="px-6 py-4">{{ verta($log->created_at)->formatDifference() }}</td>
                                <td class="px-6 py-4 flex items-center gap-2">
                                    <button wire:click="approve({{ $log->id }})" class="text-green-500 hover:text-green-700">تایید</button>
                                    <button wire:click="reject({{ $log->id }})" class="text-red-500 hover:text-red-700">رد</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(count($selectedLogs) > 0)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ count($selectedLogs) }} مورد انتخاب شده:</span>
                    <button wire:click="approveSelected" class="px-3 py-1.5 text-xs font-bold text-white bg-green-600 rounded-md hover:bg-green-700">تایید انتخاب شده‌ها</button>
                    <button wire:click="rejectSelected" class="px-3 py-1.5 text-xs font-bold text-white bg-red-600 rounded-md hover:bg-red-700">رد انتخاب شده‌ها</button>
                </div>
            @endif

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $pendingLogs->links() }}
            </div>
        @endif
    </div>
</div>
