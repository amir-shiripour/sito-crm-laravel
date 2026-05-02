@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

<div class="mx-auto pb-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none">

        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $product?->exists ? 'ویرایش کاتالوگ کالا: ' . $title : 'ثبت کاتالوگ اصلی و تنوع‌ها' }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">مشخصات پایه محصول را برای استفاده فروشندگان وارد کنید.</p>
            </div>

            <a href="{{ route('user.market.master-products.index') }}" class="group inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 transition-colors">
                بازگشت
            </a>
        </div>

        {{-- بخش اول: اطلاعات پایه --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="{{ $labelClass }}">نام محصول <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="title" class="{{ $inputClass }}" placeholder="مثلاً: لپ‌تاپ ایسوس مدل X515">
                @error('title') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-2">شناسه هوشمند (Smart SKU)</label>
                <div class="{{ $inputClass }} bg-indigo-50 text-indigo-700 font-mono text-center font-bold dark:bg-indigo-900/20 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800">{{ $crm_code }}</div>
            </div>

            <div>
                <label class="{{ $labelClass }}">برند <span class="text-red-500">*</span></label>
                <select wire:model.live="brand_id" class="{{ $inputClass }}">
                    <option value="">انتخاب برند...</option>
                    @foreach($brands as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                </select>
                @error('brand_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">دسته‌بندی <span class="text-red-500">*</span></label>
                <select wire:model.live="category_id" class="{{ $inputClass }}">
                    <option value="">انتخاب دسته...</option>
                    @foreach($parentCategories as $pCat)
                        <option value="{{ $pCat->id }}" class="font-bold">{{ $pCat->name }}</option>
                        @foreach($pCat->children as $sCat)
                            <option value="{{ $sCat->id }}">&nbsp;&nbsp;↳ {{ $sCat->name }}</option>
                        @endforeach
                    @endforeach
                </select>
                @error('category_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="{{ $labelClass }}">توضیحات معرفی</label>
                <textarea wire:model.defer="description" rows="3" class="{{ $inputClass }}"></textarea>
            </div>

            <div>
                <label class="{{ $labelClass }}">وضعیت انتشار در کاتالوگ <span class="text-red-500">*</span></label>
                <select wire:model.defer="status" class="{{ $inputClass }}">
                    <option value="draft">پیش‌نویس (مخفی از فروشندگان)</option>
                    <option value="active">فعال (قابل انتخاب توسط فروشندگان)</option>
                    <option value="archived">بایگانی شده</option>
                </select>
                @error('status') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- بخش دوم: ویژگی‌های داینامیک دسته --}}
        @if(count($categoryFields) > 0)
            <div class="bg-gray-50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 mb-8 animate-in fade-in">
                <h3 class="text-sm font-bold mb-4 text-indigo-600 dark:text-indigo-400">ویژگی‌های اختصاصی این دسته</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($categoryFields as $field)
                        <div>
                            <label class="{{ $labelClass }}">{{ $field }}</label>
                            <input type="text" wire:model.defer="dynamicAttributes.{{ $field }}" class="{{ $inputClass }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- بخش سوم: گالری و عکس‌ها --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 border-t border-gray-100 dark:border-gray-700 pt-6">
            {{-- عکس اصلی --}}
            <div class="md:col-span-1">
                <label class="{{ $labelClass }}">تصویر اصلی کالا</label>
                <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 overflow-hidden relative transition-colors">
                    @if($main_image)
                        <img src="{{ $main_image->temporaryUrl() }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر عکس</span></div>
                    @elseif($existing_main_image)
                        <img src="{{ Storage::url($existing_main_image) }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر عکس</span></div>
                    @else
                        <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <p class="text-xs text-gray-500">برای آپلود کلیک کنید</p>
                    @endif
                    <input type="file" wire:model="main_image" class="hidden" accept="image/*" />
                </label>
                @error('main_image') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- گالری عکس --}}
            <div class="md:col-span-2">
                <label class="{{ $labelClass }}">گالری تصاویر (چند عکس انتخاب کنید)</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <input type="file" wire:model="gallery_images" multiple class="hidden" accept="image/*" />
                    </label>

                    @foreach($existing_gallery as $idx => $img)
                        <div class="relative w-full h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 group">
                            <img src="{{ Storage::url($img) }}" class="w-full h-full object-cover">
                            <button wire:click.prevent="removeExistingGalleryImage({{ $idx }})" type="button" class="absolute inset-0 bg-red-500/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @endforeach

                    @foreach($gallery_images as $idx => $img)
                        <div class="relative w-full h-24 rounded-xl overflow-hidden border border-indigo-200 dark:border-indigo-700 group">
                            <img src="{{ $img->temporaryUrl() }}" class="w-full h-full object-cover">
                            <button wire:click.prevent="removeNewGalleryImage({{ $idx }})" type="button" class="absolute inset-0 bg-red-500/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                @error('gallery_images.*') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- بخش چهارم: مدیریت تنوع‌ها (Smart Variant Builder) --}}
        <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 p-6 rounded-3xl mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-indigo-200 dark:border-indigo-800">
                <div>
                    <h3 class="text-base font-bold text-indigo-900 dark:text-indigo-300">تعریف مدل‌ها و تنوع‌های محصول</h3>
                    @if(count($variantAxes) > 0)
                        <p class="text-[11px] text-gray-500 mt-1">بر اساس محورهای تنوع دسته ({{ implode('، ', $variantAxes) }})</p>
                    @endif
                </div>
                @if(count($variantAxes) > 0)
                    <button wire:click="addVariant" class="px-4 py-2 bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold shadow-sm hover:bg-indigo-50 transition-colors">+ افزودن ترکیب جدید</button>
                @endif
            </div>

            <div class="space-y-4">
                @if(count($variantAxes) > 0)
                    @foreach($variants as $index => $variant)
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-colors hover:border-indigo-300">
                            <div class="flex flex-col md:flex-row flex-wrap items-end gap-4">
                                {{-- تولید داینامیک اینپوت بر اساس محورهای تنوع دسته --}}
                                @foreach($variantAxes as $axis)
                                    <div class="flex-1 min-w-[140px] w-full">
                                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 mb-1">{{ $axis }}</label>
                                        <input type="text" wire:model.defer="variants.{{ $index }}.values.{{ $axis }}" placeholder="مثلا: مشکی یا 256GB" class="{{ $inputClass }} py-2">
                                    </div>
                                @endforeach

                                <div class="flex items-center gap-4 w-full md:w-auto pt-2 md:pt-0">
                                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <input type="checkbox" wire:model.defer="variants.{{ $index }}.is_active" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">فعال</span>
                                    </label>

                                    @if(count($variants) > 1)
                                        <button wire:click="removeVariant({{ $index }})" class="p-2 bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition-colors" title="حذف این ترکیب">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- 💡 هندل کردن دسته‌بندی‌هایی که تنوع ندارند --}}
                    <div class="bg-white dark:bg-gray-800/50 p-6 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 text-center">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">این دسته دارای محور تنوع (مانند رنگ یا سایز) نیست.</p>
                        <p class="text-xs text-gray-500 mt-1">یک مدل "استاندارد" به صورت خودکار برای قیمت‌گذاری فروشندگان ایجاد خواهد شد.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button wire:click="save" wire:loading.attr="disabled" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 w-full sm:w-auto flex justify-center items-center gap-2">
                <span wire:loading.remove>ذخیره نهایی کاتالوگ</span>
                <span wire:loading.flex class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    درحال پردازش...
                </span>
            </button>
        </div>
    </div>
</div>
