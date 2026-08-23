@php
    $currentAttributes = $status ? ($status->getAttributeValue('attributes') ?? []) : [];
    if (is_string($currentAttributes)) {
        $currentAttributes = json_decode($currentAttributes, true) ?? [];
    }
@endphp

<div>
    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های رفتاری و منطقی</label>
    <div class="flex flex-wrap gap-3">
        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="hidden" name="is_queued" value="0">
            <input type="checkbox" name="is_queued" value="1"
                   @if(old('is_queued', $currentAttributes['is_queued'] ?? false)) checked @endif
                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            در صف انتظار
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="hidden" name="is_in_progress" value="0">
            <input type="checkbox" name="is_in_progress" value="1"
                   @if(old('is_in_progress', $currentAttributes['is_in_progress'] ?? false)) checked @endif
                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            در حال انجام / پیشرفت
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="hidden" name="is_completed" value="0">
            <input type="checkbox" name="is_completed" value="1"
                   @if(old('is_completed', $currentAttributes['is_completed'] ?? false)) checked @endif
                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            تکمیل شده (محاسبه ۱۰۰٪)
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="hidden" name="is_canceled" value="0">
            <input type="checkbox" name="is_canceled" value="1"
                   @if(old('is_canceled', $currentAttributes['is_canceled'] ?? false)) checked @endif
                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            لغو شده / متوقف
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="hidden" name="is_delayed" value="0">
            <input type="checkbox" name="is_delayed" value="1"
                   @if(old('is_delayed', $currentAttributes['is_delayed'] ?? false)) checked @endif
                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            تعویق
        </label>
    </div>
</div>
