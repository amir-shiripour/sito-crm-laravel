<div>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">ایمپورت املاک از CSV</h3>
{{--            <a href="{{ route('properties.user.settings.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">--}}
{{--                بازگشت به تنظیمات--}}
{{--            </a>--}}
        </div>

        <x-action-message on="message" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('message') }}
        </x-action-message>

        @if(!$isParsed)
            <div class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center"
                     x-data="{ isDropping: false, isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress"
                     :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': isDropping }">

                    <input type="file" wire:model="file" class="hidden" id="file-upload" accept=".csv">

                    <label for="file-upload" class="cursor-pointer"
                           @dragover.prevent="isDropping = true"
                           @dragleave.prevent="isDropping = false"
                           @drop.prevent="isDropping = false">
                        <div class="space-y-2">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium text-indigo-600 hover:text-indigo-500">فایل CSV را انتخاب کنید</span>
                                یا اینجا رها کنید
                            </div>
                            <p class="text-xs text-gray-500">CSV تا ۱۰ مگابایت</p>
                        </div>
                    </label>

                    <!-- Progress Bar -->
                    <div x-show="isUploading" class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-indigo-600 h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
                        </div>
                        <div class="text-xs text-center mt-1 text-gray-500" x-text="`${progress}%`"></div>
                    </div>
                </div>
                <x-input-error for="file" />
            </div>
        @else
            <div class="space-y-6">
                <!-- Preview -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">پیش‌نمایش داده‌ها (۵ ردیف اول)</h4>
                    <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach($headers as $header)
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $header }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($data as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($cell, 20) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mapping -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($fields as $fieldKey => $fieldLabel)
                        <div>
                            <x-label :value="$fieldLabel" class="mb-1" />
                            <select wire:model="mapping.{{ $fieldKey }}" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">-- انتخاب ستون --</option>
                                @foreach($headers as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                            @if($fieldKey === 'title')
                                <x-input-error for="mapping.title" />
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 space-x-reverse pt-4 border-t dark:border-gray-700">
                    <x-secondary-button wire:click="$set('isParsed', false)" wire:loading.attr="disabled">
                        انصراف و انتخاب فایل دیگر
                    </x-secondary-button>

                    <x-button wire:click="import" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700">
                        <span wire:loading.remove wire:target="import">شروع ایمپورت</span>
                        <span wire:loading wire:target="import">در حال پردازش...</span>
                    </x-button>
                </div>
            </div>
        @endif
    </div>
</div>
