@php
    // استایل‌های لوکال
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-visible transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

<div class="space-y-6">

    {{-- تصویر شاخص --}}
    <div class="{{ $cardClass }} p-5">
        <label class="{{ $labelClass }}">تصویر شاخص</label>
        <div class="relative w-full aspect-[4/3] rounded-xl border-2 border-dashed flex flex-col items-center justify-center transition-all overflow-hidden group"
             :class="coverPreview ? 'border-indigo-500 bg-white dark:bg-gray-800' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 hover:border-indigo-400'"
             @dragover.prevent="dragOver = true"
             @dragleave.prevent="dragOver = false"
             @drop.prevent="handleCoverDrop($event)">

            <template x-if="!coverPreview">
                <div class="w-full h-full relative group/img">
                    @if($property->cover_image)
                        <img src="{{ asset('storage/' . $property->cover_image) }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover/img:scale-105"
                             onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover/img:opacity-100 transition-opacity flex flex-col items-center justify-center text-white">
                            <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span class="text-xs font-bold">تغییر تصویر</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-gray-400">
                            <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="text-xs">بارگذاری تصویر جدید</span>
                        </div>
                    @endif
                </div>
            </template>

            <template x-if="coverPreview">
                <div class="absolute inset-0 w-full h-full">
                    <img :src="coverPreview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <button type="button" @click="removeCover" class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 shadow-lg transition-transform hover:scale-110 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="absolute bottom-2 right-2 bg-indigo-600 text-white text-[10px] px-2 py-1 rounded shadow">جدید</div>
                </div>
            </template>

            <input type="file" name="cover_image" id="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-0" @change="handleCoverSelect" accept="image/*">
        </div>
    </div>

    {{-- گالری تصاویر --}}
    <div class="{{ $cardClass }} p-5">
        <label class="{{ $labelClass }}">گالری تصاویر</label>

        @if($property->images->count() > 0)
            <div class="mb-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">تصاویر فعلی:</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($property->images as $image)
                        <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group" id="image-{{ $image->id }}">
                            <img src="{{ asset('storage/' . $image->path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.src='https://via.placeholder.com/150'">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <button type="button" @click="deleteImage({{ $image->id }})" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 hover:scale-110" title="حذف تصویر">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-2" x-show="galleryPreviews.length > 0">تصاویر جدید برای آپلود:</p>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="(img, index) in galleryPreviews" :key="index">
                    <div class="relative aspect-square rounded-lg overflow-hidden border border-indigo-200 dark:border-indigo-800 group">
                        <img :src="img" class="w-full h-full object-cover">
                        <button type="button" @click="removeGalleryImage(index)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>

                <label class="aspect-square border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors text-gray-400 hover:text-indigo-500">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="text-[10px]">افزودن</span>
                    <input type="file" name="gallery_images[]" multiple class="hidden" @change="handleGallerySelect" accept="image/*">
                </label>
            </div>
        </div>
    </div>

    {{-- ویدیو --}}
    <div class="{{ $cardClass }} p-5">
        <label class="{{ $labelClass }}">ویدیو ملک</label>
        <div class="relative w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-2 flex flex-col items-center justify-center hover:border-indigo-400 transition-colors overflow-hidden bg-gray-50 dark:bg-gray-900/30">

            <template x-if="!videoPreview">
                <div class="text-center w-full h-full flex flex-col items-center justify-center">
                    @if($property->video)
                        <div class="relative w-full h-full group">
                            <video src="{{ asset('storage/' . $property->video) }}" class="w-full h-full object-cover rounded-lg"></video>
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <p class="text-white text-xs">برای تغییر کلیک کنید</p>
                            </div>
                        </div>
                    @else
                        <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <p class="text-[10px] text-gray-500">انتخاب ویدیو</p>
                    @endif
                </div>
            </template>

            <template x-if="videoPreview">
                <div class="w-full h-full relative group">
                    <video :src="videoPreview" controls class="w-full h-full object-cover rounded-lg"></video>
                    <button type="button" @click="removeVideo" class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 shadow-sm z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </template>

            <input type="file" name="video" id="video" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleVideoSelect" accept="video/*" x-show="!videoPreview || (videoPreview && !'{{ $property->video }}')">
        </div>
    </div>

    {{-- تنظیمات انتشار --}}
    <div class="{{ $cardClass }} p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            تنظیمات انتشار
        </h3>
        <div class="space-y-4">
            <div>
                <label class="{{ $labelClass }}">تاریخ ثبت</label>
                <input type="text" name="registered_at" data-jdp value="{{ old('registered_at', $property->registered_at_jalali ?? \Morilog\Jalali\Jalalian::now()->format('Y/m/d')) }}" class="{{ $inputClass }} text-center">
            </div>

            <div>
                <label class="{{ $labelClass }}">وضعیت ملک</label>
                <select name="status_id" class="{{ $selectClass }}">
                    @foreach(\Modules\Properties\Entities\PropertyStatus::where('is_active', true)->orderBy('sort_order')->get() as $status)
                        <option value="{{ $status->id }}" {{ $property->status_id == $status->id ? 'selected' : '' }}>{{ $status->label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">وضعیت انتشار</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="publication_status" value="published" class="peer sr-only" {{ $property->publication_status == 'published' ? 'checked' : '' }}>
                        <div class="text-center py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 peer-checked:bg-emerald-50 peer-checked:text-emerald-600 peer-checked:border-emerald-200 dark:peer-checked:bg-emerald-900/20 dark:peer-checked:border-emerald-800 transition-all text-xs font-bold">
                            منتشر شده
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="publication_status" value="draft" class="peer sr-only" {{ $property->publication_status == 'draft' ? 'checked' : '' }}>
                        <div class="text-center py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 peer-checked:bg-amber-50 peer-checked:text-amber-600 peer-checked:border-amber-200 dark:peer-checked:bg-amber-900/20 dark:peer-checked:border-amber-800 transition-all text-xs font-bold">
                            پیش‌نویس
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_special" value="1" {{ isset($property->meta['is_special']) && $property->meta['is_special'] ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">آگهی ویژه / فوری</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-7">با فعال کردن این گزینه، ملک با نشان ویژه نمایش داده می‌شود.</p>
            </div>

            <div>
                <label class="{{ $labelClass }}">یادداشت محرمانه</label>
                <textarea name="confidential_notes" x-model="confidentialNotes" rows="3" class="{{ $inputClass }} resize-none" placeholder="یادداشت خصوصی...">{{ old('confidential_notes', $property->confidential_notes) }}</textarea>
            </div>
        </div>
    </div>

</div>
