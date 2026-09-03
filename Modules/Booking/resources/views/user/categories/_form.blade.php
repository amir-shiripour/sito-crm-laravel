@php
    $editing = isset($category) && $category->exists;
    $serviceLabel = config('booking.labels.service', 'سرویس');
    $servicesLabel = config('booking.labels.services', 'سرویس‌ها');

    $inputClass = 'w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900/40 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition-all focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-gray-300 dark:hover:border-gray-600';
    $selectClass = $inputClass . ' appearance-none cursor-pointer pr-10';
    $labelClass = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-2';
    $helpClass = 'text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-start gap-1.5';
    $errorClass = 'text-xs text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1';
@endphp

<div class="space-y-6">
    <!-- Main Info Box -->
    <div class="p-6 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            اطلاعات دسته‌بندی
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="col-span-1 md:col-span-2">
                <label class="{{ $labelClass }}">
                    <span>نام دسته‌بندی</span>
                    <span class="text-rose-500">*</span>
                </label>
                <input name="name" type="text" value="{{ old('name', $category->name ?? '') }}" class="{{ $inputClass }}" required placeholder="مثال: خدمات دندانپزشکی، مشاوره یا زیبایی">
                @error('name')
                    <div class="{{ $errorClass }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="{{ $labelClass }}">
                    <span>وضعیت دسته‌بندی</span>
                </label>
                <div class="relative">
                    <select name="status" class="{{ $selectClass }}">
                        <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE }}" @selected(old('status', $category->status ?? \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE) === \Modules\Booking\Entities\BookingCategory::STATUS_ACTIVE)>
                            فعال
                        </option>
                        <option value="{{ \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE }}" @selected(old('status', $category->status ?? '') === \Modules\Booking\Entities\BookingCategory::STATUS_INACTIVE)>
                            غیرفعال
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('status')
                    <div class="{{ $errorClass }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Sort Order -->
            <div>
                <label class="{{ $labelClass }}">
                    <span>ترتیب نمایش</span>
                </label>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="{{ $inputClass }}" placeholder="0">
                <div class="{{ $helpClass }}">
                    <span>اولویت نمایش دسته‌ها در لیست‌ها و فرم رزرو (اعداد کوچکتر زودتر نمایش داده می‌شوند).</span>
                </div>
                @error('sort_order')
                    <div class="{{ $errorClass }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>
</div>
