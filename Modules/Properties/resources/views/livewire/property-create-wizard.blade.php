<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    {{-- Stepper --}}
    <div class="mb-8">
        <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 dark:bg-gray-700 -z-10"></div>

            @foreach([1 => 'مشخصات ملک', 2 => 'قیمت و جزئیات', 3 => 'امکانات و مدیا'] as $index => $label)
                <div class="flex flex-col items-center bg-white dark:bg-gray-800 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mb-1 transition-colors
                        {{ $step >= $index ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                        {{ $index }}
                    </div>
                    <span class="text-xs {{ $step >= $index ? 'text-indigo-600 font-bold' : 'text-gray-500' }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step 1: مشخصات ملک --}}
    @if($step === 1)
        <div class="space-y-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">مشخصات پایه ملک</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- عنوان فایل --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان فایل *</label>
                    <input type="text" wire:model.defer="property.title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    @error('property.title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- نوع فایل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع فایل *</label>
                    <select wire:model.defer="property.listing_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">انتخاب کنید</option>
                        @foreach($listingTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('property.listing_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- نوع ملک --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع ملک *</label>
                    <select wire:model.defer="property.property_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">انتخاب کنید</option>
                        @foreach($propertyTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('property.property_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- دسته‌بندی شخصی --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی شخصی</label>
                    <select wire:model.defer="property.category_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">بدون دسته‌بندی</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('property.category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- کد ملک --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">کد ملک (اختیاری)</label>
                    <input type="text" wire:model.defer="property.code" placeholder="در صورت خالی بودن، اتوماتیک تولید می‌شود" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    @error('property.code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- آدرس --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس</label>
                    <textarea wire:model.defer="property.address" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    @error('property.address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- عکس شاخص --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عکس شاخص</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                @if($cover_image_file)
                                    <p class="text-sm text-green-500">عکس انتخاب شد: {{ $cover_image_file->getClientOriginalName() }}</p>
                                @else
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">برای آپلود کلیک کنید</span> یا فایل را اینجا رها کنید</p>
                                @endif
                            </div>
                            <input id="dropzone-file" type="file" wire:model="cover_image_file" class="hidden" />
                        </label>
                    </div>
                    @error('cover_image_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- توضیحات --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">توضیحات تکمیلی</label>
                    <textarea wire:model.defer="property.description" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    @error('property.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    @endif

    {{-- دکمه‌های ناوبری --}}
    <div class="flex justify-between mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
        @if($step > 1)
            <button wire:click="previousStep" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                مرحله قبل
            </button>
        @else
            <div></div> {{-- Spacer --}}
        @endif

        @if($step < 3)
            <button wire:click="nextStep" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                مرحله بعد
            </button>
        @else
            <button wire:click="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                ثبت نهایی ملک
            </button>
        @endif
    </div>
</div>
