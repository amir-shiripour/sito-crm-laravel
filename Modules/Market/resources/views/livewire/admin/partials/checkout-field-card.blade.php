<div class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl space-y-4 bg-white dark:bg-gray-800/50" x-data="{ open: false }">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $field['label'] }}</span>
            @if($field['is_system'] ?? false)
                <span class="px-2 py-0.5 text-xs font-mono text-cyan-700 bg-cyan-100 rounded-full dark:bg-cyan-900 dark:text-cyan-300">سیستمی</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <button @click="open = !open" type="button" class="text-gray-400 hover:text-indigo-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
            <button wire:click="removeField({{ $index }})" type="button" class="text-gray-400 hover:text-red-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-collapse class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">لیبل (عنوان نمایشی)</label>
                <input type="text" wire:model.defer="schema.fields.{{ $index }}.label" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">متن جایگزین (Placeholder)</label>
                <input type="text" wire:model.defer="schema.fields.{{ $index }}.placeholder" class="{{ $inputClass }}">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">گروه</label>
                <select wire:model.defer="schema.fields.{{ $index }}.group" class="{{ $inputClass }}">
                    @foreach($schema['groups'] as $group)
                        <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">عرض فیلد</label>
                <select wire:model.defer="schema.fields.{{ $index }}.width" class="{{ $inputClass }}">
                    <option value="full">کامل</option>
                    <option value="1/2">نصف</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">منبع داده (Source)</label>
                <select wire:model.defer="schema.fields.{{ $index }}.source" class="{{ $inputClass }}">
                    <option value="meta">ذخیره در متای سفارش</option>
                    <optgroup label="فیلدهای کلاینت">
                        @foreach($clientFormFields as $clientField)
                            <option value="client.{{ $clientField['id'] }}">{{ $clientField['label'] }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">نوع همگام‌سازی (Sync)</label>
                <select wire:model.defer="schema.fields.{{ $index }}.sync" class="{{ $inputClass }}">
                    <option value="">بدون سینک</option>
                    <option value="fill_if_empty">فقط اگر خالی بود پر کن</option>
                    <option value="always_update">همیشه آپدیت کن</option>
                </select>
            </div>
        </div>
        <label class="flex items-center gap-3 pt-2">
            <input type="checkbox" wire:model="schema.fields.{{ $index }}.required" class="{{ $checkboxClass }}">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">این فیلد الزامی است</span>
        </label>

        @if($field['required'] ?? false)
            <div class="p-3 bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700 rounded-xl space-y-2 mt-2">
                <span class="block text-xs font-bold text-gray-600 dark:text-gray-400">الزامی برای روش‌های پرداخت خاص (در صورت عدم انتخاب، برای همه روش‌ها الزامی است):</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($activePaymentMethods as $methodKey => $methodTitle)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" 
                                   value="{{ $methodKey }}" 
                                   wire:model.defer="schema.fields.{{ $index }}.required_payment_methods" 
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer">
                            <span class="text-xs text-gray-700 dark:text-gray-300">{{ $methodTitle }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
