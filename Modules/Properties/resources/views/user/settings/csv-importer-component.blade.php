<div>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">ایمپورت املاک از CSV</h3>
        </div>

        {{-- پیام‌های موفقیت و خطا --}}
        @if($importFinished)
            @if($failedRows > 0)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                    ایمپورت با {{ $importCount }} موفقیت و {{ $failedRows }} خطا انجام شد.
                </div>
            @else
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    تعداد {{ $importCount }} ملک با موفقیت کامل ایمپورت شد.
                </div>
            @endif
        @endif

        @if($failedRows > 0 && !empty($importErrors))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-800">
                <h4 class="text-sm font-bold text-red-800 dark:text-red-300 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    خطاهای رخ داده در حین ایمپورت ({{ $failedRows }} مورد):
                </h4>
                <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-1 max-h-40 overflow-y-auto">
                    @foreach($importErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!$isParsed)
            <div class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center transition-colors"
                     x-data="{ isDropping: false, isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress"
                     :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': isDropping }">

                    <input type="file" wire:model="file" class="hidden" id="file-upload" accept=".csv">

                    <label for="file-upload" class="cursor-pointer block w-full h-full"
                           @dragover.prevent="isDropping = true"
                           @dragleave.prevent="isDropping = false"
                           @drop.prevent="isDropping = false">
                        <div class="space-y-4">
                            <div class="w-16 h-16 mx-auto bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-bold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">فایل CSV را انتخاب کنید</span>
                                <span class="block mt-1">یا فایل را اینجا رها کنید</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-500">فرمت مجاز: CSV (حداکثر ۱۰ مگابایت)</p>
                        </div>
                    </label>

                    <!-- Upload Progress Bar -->
                    <div x-show="isUploading" class="mt-6 max-w-xs mx-auto">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>در حال آپلود...</span>
                            <span x-text="`${progress}%`"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
                <x-input-error for="file" />
            </div>
        @else
            <div class="space-y-8">

                {{-- وضعیت ایمپورت (در حال پردازش) --}}
                <div wire:loading wire:target="import" class="w-full">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center dark:bg-blue-900/20 dark:border-blue-800">
                        <div class="animate-spin w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                        <h4 class="text-lg font-bold text-blue-800 dark:text-blue-300 mb-2">در حال پردازش فایل...</h4>
                        <p class="text-sm text-blue-600 dark:text-blue-400">لطفاً تا پایان عملیات صبر کنید و صفحه را نبندید.</p>
                        <p class="text-xs text-blue-500 mt-2">این عملیات ممکن است بسته به حجم فایل چند دقیقه طول بکشد.</p>
                    </div>
                </div>

                <div wire:loading.remove wire:target="import">
                    <!-- Preview Section -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                پیش‌نمایش داده‌ها (۵ ردیف اول)
                            </h4>
                            <span class="text-xs text-gray-500 bg-white dark:bg-gray-700 px-2 py-1 rounded border border-gray-200 dark:border-gray-600">
                                مجموع ردیف‌ها: {{ number_format($totalRows) }}
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    @foreach($headers as $header)
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">{{ $header }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($data as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        @foreach($row as $cell)
                                            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 border-l border-gray-100 dark:border-gray-800 last:border-0">{{ Str::limit($cell, 30) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mapping Section -->
                    <div class="mt-8">
                        <h4 class="text-md font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            تطبیق ستون‌ها
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($fields as $fieldKey => $fieldLabel)
                                <div class="bg-gray-50 dark:bg-gray-900/30 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors" x-data="{ source: @entangle('mapping.'.$fieldKey.'.source') }">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        {{ $fieldLabel }}
                                        @if(in_array($fieldKey, ['title', 'listing_type', 'property_type']))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    {{-- انتخاب منبع --}}
                                    <div class="flex rounded-md shadow-sm mb-2">
                                        <button type="button" @click="source = 'none'" :class="source === 'none' ? 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-white' : 'bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-400'" class="flex-1 px-2 py-1 text-[10px] border border-gray-300 dark:border-gray-600 rounded-r-md focus:outline-none transition-colors">خالی</button>
                                        <button type="button" @click="source = 'csv'" :class="source === 'csv' ? 'bg-indigo-100 text-indigo-700 border-indigo-300 dark:bg-indigo-900/50 dark:text-indigo-300 dark:border-indigo-700' : 'bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-400 border-y border-gray-300 dark:border-gray-600'" class="flex-1 px-2 py-1 text-[10px] focus:outline-none transition-colors">CSV</button>
                                        <button type="button" @click="source = 'fixed'" :class="source === 'fixed' ? 'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-900/50 dark:text-emerald-300 dark:border-emerald-700' : 'bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-600'" class="flex-1 px-2 py-1 text-[10px] rounded-l-md focus:outline-none transition-colors">ثابت</button>
                                    </div>

                                    {{-- ورودی بر اساس منبع --}}
                                    <div x-show="source === 'csv'">
                                        <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                            <option value="">-- انتخاب ستون --</option>
                                            @foreach($headers as $header)
                                                <option value="{{ $header }}">{{ $header }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div x-show="source === 'fixed'">
                                        @if($fieldKey === 'status_id')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب وضعیت --</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status['id'] }}">{{ $status['name'] ?? $status['label'] ?? $status['title'] ?? 'وضعیت ' . $status['id'] }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldKey === 'category_name')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب دسته‌بندی --</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldKey === 'building_name')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب ساختمان --</option>
                                                @foreach($buildings as $building)
                                                    <option value="{{ $building['id'] }}">{{ $building['name'] }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldKey === 'agent_id')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب مشاور --</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldKey === 'listing_type')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب --</option>
                                                <option value="sale">فروش</option>
                                                <option value="rent">اجاره</option>
                                                <option value="presale">پیش‌فروش</option>
                                            </select>
                                        @elseif($fieldKey === 'property_type')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="">-- انتخاب --</option>
                                                <option value="apartment">آپارتمان</option>
                                                <option value="villa">ویلا</option>
                                                <option value="land">زمین</option>
                                                <option value="office">اداری/تجاری</option>
                                            </select>
                                        @elseif($fieldKey === 'is_special')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="0">خیر</option>
                                                <option value="1">بله</option>
                                            </select>
                                        @elseif($fieldKey === 'publication_status')
                                            <select wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                                <option value="draft">پیش‌نویس</option>
                                                <option value="published">منتشر شده</option>
                                            </select>
                                        @elseif(in_array($fieldKey, ['delivery_date', 'registered_at']))
                                            <input type="text" wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm date-picker" placeholder="YYYY/MM/DD"
                                                   x-init="
                                                       if(typeof jalaliDatepicker !== 'undefined') {
                                                           jalaliDatepicker.startWatch({
                                                               time: false,
                                                               hasSecond: false
                                                           });
                                                       }
                                                   "
                                                   data-jdp>
                                        @else
                                            <input type="text" wire:model="mapping.{{ $fieldKey }}.value" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="مقدار ثابت را وارد کنید">
                                        @endif
                                    </div>

                                    @if($fieldKey === 'title')
                                        <x-input-error for="mapping.title" class="mt-1" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Options Section -->
                    <div class="mt-8 bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <div class="flex items-center h-5">
                                <input type="checkbox" wire:model="autoGenerateCode" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">تولید خودکار کد ملک</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">در صورتی که کد ملک در فایل خالی باشد یا تکراری باشد، سیستم به صورت خودکار کد جدید تولید می‌کند.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="$set('isParsed', false)" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                            انصراف و بازگشت
                        </button>

                        <button wire:click="import" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            شروع عملیات ایمپورت
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
