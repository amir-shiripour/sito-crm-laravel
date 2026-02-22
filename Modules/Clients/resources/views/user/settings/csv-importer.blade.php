@php
    $title = 'ایمپورت مشتریان';
@endphp

<div class="w-full max-w-5xl mx-auto"
     @if($importing && !$isFinished) wire:poll.750ms="processChunk" @endif>

    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">ایمپورت مشتریان از CSV</h3>
                @if($isParsed)
                    <button wire:click="resetState" class="text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        انصراف و شروع مجدد
                    </button>
                @endif
            </div>

            {{-- Finished State --}}
            @if($isFinished)
                <div class="text-center space-y-4 py-8">
                    <div class="w-24 h-24 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-green-600 dark:text-green-400">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-800 dark:text-white">عملیات ایمپورت کامل شد</h4>
                    <p class="text-base text-gray-600 dark:text-gray-400">
                        {{ $importCount }} مشتری جدید ایجاد و {{ $updateCount }} مشتری موجود آپدیت شد.
                    </p>
                    @if(count($importErrors) > 0)
                        <div class="pt-4 max-w-lg mx-auto">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-800 text-right">
                                <h4 class="text-sm font-bold text-red-800 dark:text-red-300 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    خطاها ({{ count($importErrors) }} مورد)
                                </h4>
                                <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-1 max-h-40 overflow-y-auto">
                                    @foreach($importErrors as $error)
                                        <li>ردیف {{ $error['row'] }}: {{ $error['error'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <button wire:click="resetState" class="mt-6 px-8 py-3 text-base font-bold text-white bg-indigo-600 rounded-xl shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        ایمپورت فایل جدید
                    </button>
                </div>

            {{-- Progress State --}}
            @elseif($importing)
                <div class="text-center space-y-6 py-8">
                    <div class="relative w-32 h-32 mx-auto">
                        <svg class="w-full h-full" viewBox="0 0 36 36"><path class="text-gray-200 dark:text-gray-700" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" /><path class="text-indigo-600" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" stroke-dasharray="{{ ($totalRows > 0 ? ($processedRows / $totalRows) * 100 : 0) }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" /></svg>
                        <div class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-gray-800 dark:text-gray-200">
                            {{ number_format($totalRows > 0 ? ($processedRows / $totalRows) * 100 : 0, 0) }}%
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 dark:text-white">در حال پردازش فایل...</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">لطفاً تا پایان عملیات صبر کنید. این فرآیند ممکن است چند دقیقه طول بکشد.</p>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ number_format($processedRows) }} / {{ number_format($totalRows) }} ردیف پردازش شد</p>
                </div>

            {{-- Upload & Mapping State --}}
            @elseif(!$isParsed)
                <div class="space-y-4" x-data="{ isDropping: false, isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center transition-colors"
                         :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': isDropping }">
                        <input type="file" wire:model="file" class="hidden" id="file-upload" accept=".csv,.txt">
                        <label for="file-upload" class="cursor-pointer block w-full h-full"
                               @dragover.prevent="isDropping = true" @dragleave.prevent="isDropping = false" @drop.prevent="isDropping = false">
                            <div class="space-y-4">
                                <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700/50 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-bold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">فایل CSV را انتخاب کنید</span>
                                    <span class="block mt-1">یا فایل را اینجا رها کنید</span>
                                </div>
                                <p class="text-xs text-gray-500">فرمت مجاز: CSV, TXT (حداکثر ۱۰ مگابایت)</p>
                            </div>
                        </label>
                        <div x-show="isUploading" class="mt-6 max-w-xs mx-auto">
                            <div class="flex justify-between text-xs text-gray-500 mb-1"><span>در حال آپلود...</span><span x-text="`${progress}%`"></span></div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden"><div class="bg-indigo-600 h-2 rounded-full" :style="`width: ${progress}%`"></div></div>
                        </div>
                    </div>
                    @error('file') <div class="text-red-500 text-sm mt-2">{{ $message }}</div> @enderror
                </div>
            @else
                <div class="space-y-8">
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                پیش‌نمایش داده‌ها (۵ ردیف اول)
                            </h4>
                            <span class="text-xs text-gray-500 bg-white dark:bg-gray-700 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-600">مجموع ردیف‌ها: {{ number_format($totalRows) }}</span>
                        </div>
                        <div class="overflow-x-auto"><table class="min-w-full text-right">
                            <thead class="bg-gray-50 dark:bg-gray-800/50"><tr class="border-b border-gray-200 dark:border-gray-700">
                                @foreach($csvHeaders as $header)
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">{{ $header }}</th>
                                @endforeach
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($previewData as $row)
                                @if($row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"><td class="p-0">
                                    <div class="flex divide-x divide-gray-200 dark:divide-gray-700">
                                    @foreach($row as $cell)
                                        <div class="px-4 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 flex-1 w-48">{{ Str::limit($cell, 30) }}</div>
                                    @endforeach
                                    </div>
                                </td></tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table></div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-md font-bold text-gray-900 dark:text-white mb-4">تطبیق ستون‌ها</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($csvHeaders as $index => $header)
                                <div class="bg-gray-50 dark:bg-gray-900/30 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $header }}</label>
                                    <select wire:model="fieldMapping.{{ $index }}" class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        <option value="">-- نادیده گرفتن --</option>
                                        @foreach($formFields as $fieldId => $fieldLabel)
                                            <option value="{{ $fieldId }}">{{ $fieldLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <div class="flex items-center h-5"><input type="checkbox" wire:model.live="hasHeaders" id="hasHeaders" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"></div>
                            <div class="flex flex-col"><span class="text-sm font-bold text-gray-900 dark:text-white">فایل دارای ردیف هدر است</span><span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">با فعال بودن این گزینه، ردیف اول فایل شما به عنوان عناوین ستون‌ها در نظر گرفته شده و در فرآیند ایمپورت پردازش نخواهد شد.</span></div>
                        </label>
                        <hr class="border-gray-200 dark:border-gray-700">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <div class="flex items-center h-5"><input type="checkbox" wire:model="updateExisting" id="updateExisting" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"></div>
                            <div class="flex flex-col"><span class="text-sm font-bold text-gray-900 dark:text-white">آپدیت مشتریان موجود</span><span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">اگر مشتری با موبایل، ایمیل، کد ملی یا شماره پرونده یکسان در سیستم وجود داشته باشد، اطلاعات آن آپدیت می‌شود.</span></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="startImport" class="w-full sm:w-auto px-8 py-3 text-base font-bold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            شروع عملیات ایمپورت ({{ number_format($totalRows) }} ردیف)
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
