<div class="space-y-4">
    <div class="p-4 border dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800/50">
        <h4 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-3">افزودن فیلد سفارشی</h4>
        <div class="grid grid-cols-2 gap-2">
            <button wire:click="addField('text')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">متن ساده</button>
            <button wire:click="addField('email')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">ایمیل</button>
            <button wire:click="addField('number')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">عدد</button>
            <button wire:click="addField('textarea')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">متن طولانی</button>
            <button wire:click="addField('select')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">لیست کشویی</button>
            <button wire:click="addField('checkbox')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">چک‌باکس</button>
            <button wire:click="addField('select-province-city')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">استان/شهر</button>
            <button wire:click="addField('jalali-date')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">تاریخ شمسی</button>
            <button wire:click="addField('postal-code')" type="button" class="p-2 text-xs font-bold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">کد پستی</button>
        </div>
    </div>

    <div class="p-4 border dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800/50">
        <h4 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-3">افزودن فیلد سیستمی</h4>
        <div class="space-y-2">
            @php
                $usedSystemFields = collect($schema['fields'] ?? [])->where('is_system', true)->pluck('id')->all();
            @endphp
            @foreach($systemFields as $id => $field)
                <button wire:click="addSystemField('{{ $id }}')" type="button"
                        class="w-full p-2 text-xs font-bold text-right rounded-lg transition
                               {{ in_array($id, $usedSystemFields)
                                   ? 'bg-gray-200 text-gray-400 dark:bg-gray-800 dark:text-gray-500 cursor-not-allowed'
                                   : 'bg-cyan-50 text-cyan-800 hover:bg-cyan-100 dark:bg-cyan-900/50 dark:text-cyan-300 dark:hover:bg-cyan-900' }}"
                        {{ in_array($id, $usedSystemFields) ? 'disabled' : '' }}>
                    {{ $field['label'] }}
                </button>
            @endforeach
        </div>
    </div>
    <div class="p-4 border dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800/50">
        <button wire:click="addGroup" type="button" class="w-full p-3 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
            افزودن گروه جدید
        </button>
    </div>
</div>
