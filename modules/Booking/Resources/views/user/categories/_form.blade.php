@php
    $editing = isset($category);
    $inputClass = 'mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/60 px-3 py-2
    text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:border-indigo-400
    dark:focus:ring-indigo-500/30 transition';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    $errorClass = 'text-xs text-rose-600 dark:text-rose-400 mt-1';
@endphp

<div class="space-y-4">
    <div>
        <label class="{{ $labelClass }}">نام</label>
        <input name="name" type="text" value="{{ old('name', $category->name ?? '') }}" class="{{ $inputClass }}"
               required>
        @error('name')
        <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">وضعیت</label>
        <select name="status" class="{{ $inputClass }}">
            <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE }}" @selected(old('status',
                $category->status ?? \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE) ===
                \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE)>
                فعال
            </option>
            <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE }}" @selected(old('status',
                $category->status ?? '') === \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE)>
                غیرفعال
            </option>
        </select>
        @error('status')
        <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="pt-2">
        <button
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
            {{ $editing ? 'بروزرسانی' : 'ایجاد' }}
        </button>
        <a class="mr-2 inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
           href="{{ route('user.booking.categories.index') }}">
            بازگشت
        </a>
    </div>
</div>
