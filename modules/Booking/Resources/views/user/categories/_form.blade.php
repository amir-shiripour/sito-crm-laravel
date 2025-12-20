@php
    $editing = isset($category);
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">نام</label>
        <input name="name" type="text" value="{{ old('name', $category->name ?? '') }}"
               class="mt-1 w-full rounded border-gray-300" required>
        @error('name')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">وضعیت</label>
        <select name="status" class="mt-1 w-full rounded border-gray-300">
            <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE }}"
                @selected(old('status', $category->status ?? \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE) === \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE)>
                فعال
            </option>
            <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE }}"
                @selected(old('status', $category->status ?? '') === \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE)>
                غیرفعال
            </option>
        </select>
        @error('status')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">
            {{ $editing ? 'بروزرسانی' : 'ایجاد' }}
        </button>
        <a class="ml-2 text-gray-600 hover:underline" href="{{ route('user.booking.categories.index') }}">
            بازگشت
        </a>
    </div>
</div>
