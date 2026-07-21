@php
    // واکشی صحیح اطلاعات از ستون جیسون دیتابیس
    $currentAttributes = $status ? ($status->getAttributeValue('attributes') ?? []) : [];
    if (is_string($currentAttributes)) {
        $currentAttributes = json_decode($currentAttributes, true) ?? [];
    }
@endphp

{{-- Invoice Attributes --}}
@if($type === 'invoice')
    <div>
        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های تخصصی فاکتور</label>
        <div class="flex flex-wrap gap-3">
            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="hidden" name="converts_to_invoice" value="0">
                <input type="checkbox" name="converts_to_invoice" value="1"
                       @if(old('converts_to_invoice', $currentAttributes['converts_to_invoice'] ?? false)) checked @endif
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                تبدیل به فاکتور
            </label>
            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="hidden" name="locks_invoice" value="0">
                <input type="checkbox" name="locks_invoice" value="1"
                       @if(old('locks_invoice', $currentAttributes['locks_invoice'] ?? false)) checked @endif
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                قفل ویرایش
            </label>
            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="hidden" name="allows_payment" value="0">
                <input type="checkbox" name="allows_payment" value="1"
                       @if(old('allows_payment', $currentAttributes['allows_payment'] ?? false)) checked @endif
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                اجازه پرداخت
            </label>
        </div>
    </div>
@endif

{{-- Payment Attributes --}}
@if($type === 'payment')
    <div>
        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های تخصصی پرداخت</label>
        <div class="flex flex-wrap gap-3">
            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="hidden" name="is_successful_payment" value="0">
                <input type="checkbox" name="is_successful_payment" value="1"
                       @if(old('is_successful_payment', $currentAttributes['is_successful_payment'] ?? false)) checked @endif
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                پرداخت موفق
            </label>
            <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="hidden" name="is_failed_payment" value="0">
                <input type="checkbox" name="is_failed_payment" value="1"
                       @if(old('is_failed_payment', $currentAttributes['is_failed_payment'] ?? false)) checked @endif
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                پرداخت ناموفق
            </label>
        </div>
    </div>
@endif

{{-- Service Attributes --}}
@if($type === 'service')
    <div>
        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">ویژگی‌های تخصصی سرویس</label>
        <div class="text-xs text-gray-400 dark:text-gray-500">
            در حال حاضر ویژگی تخصصی برای این بخش تعریف نشده است.
        </div>
    </div>
@endif
