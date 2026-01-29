@php
    // استایل‌های لوکال (برای اطمینان از اعمال شدن حتی اگر از والد ارث‌بری نشود)
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-visible transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

<form action="{{ route('user.properties.update', $property) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- فیلدهای مخفی نقشه --}}
    <input type="hidden" name="latitude" x-model="lat">
    <input type="hidden" name="longitude" x-model="lng">

    <div class="grid grid-cols-12 gap-6">

        {{-- ستون چپ (مدیا و تنظیمات جانبی) --}}
        <div class="col-span-12 lg:col-span-4 space-y-6 order-2 lg:order-1">

            {{-- تصویر شاخص --}}
            <div class="{{ $cardClass }} p-5">
                <label class="{{ $labelClass }}">تصویر شاخص</label>
                <div class="relative w-full aspect-[4/3] rounded-xl border-2 border-dashed flex flex-col items-center justify-center transition-all overflow-hidden group"
                     :class="coverPreview ? 'border-indigo-500 bg-white dark:bg-gray-800' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 hover:border-indigo-400'"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleCoverDrop($event)">

                    {{-- حالت پیش‌فرض (نمایش عکس فعلی یا پلیس‌هلدر) --}}
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

                    {{-- حالت پیش‌نمایش جدید --}}
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

                {{-- تصاویر موجود در دیتابیس --}}
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

                {{-- تصاویر جدید --}}
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
                            @foreach($statuses as $status)
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
                        <label class="{{ $labelClass }}">یادداشت محرمانه</label>
                        <textarea name="confidential_notes" rows="3" class="{{ $inputClass }} resize-none" placeholder="یادداشت خصوصی...">{{ old('confidential_notes', $property->confidential_notes) }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- ستون راست (اطلاعات اصلی) --}}
        <div class="col-span-12 lg:col-span-8 space-y-6 order-1 lg:order-2">

            {{-- کارت اطلاعات پایه --}}
            <div class="{{ $cardClass }} p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">اطلاعات پایه ملک</h2>
                </div>

                <div class="space-y-6">
                    {{-- ردیف اول --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="{{ $labelClass }}">عنوان ملک <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $property->title) }}" class="{{ $inputClass }}" required>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">نوع فایل</label>
                            <select name="listing_type" x-model="listingType" class="{{ $selectClass }}">
                                <option value="sale">فروش</option>
                                <option value="presale">پیش‌فروش</option>
                                <option value="rent">رهن و اجاره</option>
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">نوع ملک</label>
                            <select name="property_type" x-model="propertyType" class="{{ $selectClass }}">
                                <option value="apartment">خانه و آپارتمان</option>
                                <option value="villa">ویلا</option>
                                <option value="land">زمین و باغ</option>
                                <option value="office">اداری و تجاری</option>
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">نوع سند</label>
                            <select name="document_type" class="{{ $selectClass }}">
                                <option value="">انتخاب کنید</option>
                                @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ $property->document_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">ساختمان / برج</label>
                            <select name="building_id" class="{{ $selectClass }}">
                                <option value="">انتخاب کنید (اختیاری)</option>
                                {{-- اگر ساختمان انتخاب شده وجود دارد --}}
                                @if($property->building_id)
                                    <option value="{{ $property->building_id }}" selected>ساختمان فعلی</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- فیلدهای شرطی --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50"
                         x-show="propertyType === 'land' || listingType === 'presale'" x-transition>
                        <div x-show="propertyType === 'land'">
                            <label class="{{ $labelClass }}">نوع کاربری</label>
                            <select name="usage_type" class="{{ $selectClass }}">
                                <option value="">انتخاب کنید...</option>
                                <option value="residential" {{ $property->usage_type == 'residential' ? 'selected' : '' }}>مسکونی</option>
                                <option value="industrial" {{ $property->usage_type == 'industrial' ? 'selected' : '' }}>صنعتی</option>
                                <option value="commercial" {{ $property->usage_type == 'commercial' ? 'selected' : '' }}>اداری / تجاری</option>
                                <option value="agricultural" {{ $property->usage_type == 'agricultural' ? 'selected' : '' }}>کشاورزی</option>
                            </select>
                        </div>
                        <div x-show="listingType === 'presale'">
                            <label class="{{ $labelClass }}">تاریخ تحویل</label>
                            <input type="text" name="delivery_date" data-jdp value="{{ $property->delivery_date_jalali }}" class="{{ $inputClass }} text-center">
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">توضیحات تکمیلی</label>
                        <textarea name="description" rows="4" class="{{ $inputClass }} resize-none leading-relaxed">{{ old('description', $property->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">کد ملک</label>
                            <input type="text" name="code" value="{{ old('code', $property->code) }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">دسته‌بندی شخصی</label>
                            <select name="category_id" class="{{ $selectClass }}">
                                <option value="">بدون دسته‌بندی</option>
                                @foreach(\Modules\Properties\Entities\PropertyCategory::where('user_id', auth()->id())->get() as $category)
                                    <option value="{{ $category->id }}" {{ $property->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت مالک --}}
            <div class="{{ $cardClass }} p-6 overflow-visible">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">اطلاعات مالک</h2>
                </div>

                <div class="relative">
                    {{-- نمایش مالک فعلی (اگر وجود دارد) --}}
                    @if($property->owner)
                        <div class="flex items-center justify-between p-3 mb-4 rounded-xl bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-sm dark:bg-indigo-800 dark:text-indigo-200">
                                    {{ mb_substr($property->owner->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-indigo-900 dark:text-indigo-200">{{ $property->owner->first_name . ' ' . $property->owner->last_name }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 dir-ltr text-right">{{ $property->owner->phone }}</div>
                                </div>
                            </div>
                            <span class="text-xs bg-white dark:bg-gray-800 px-2 py-1 rounded text-gray-500">مالک فعلی</span>
                        </div>
                    @endif

                    <label class="{{ $labelClass }}">تغییر مالک (جستجو)</label>
                    <div class="flex gap-2">
                        <input type="hidden" name="owner_id" x-model="selectedOwner">

                        <div class="relative flex-1 group">
                            <input type="text"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="searchOwners()"
                                   @focus="if(searchQuery.length >= 2) showResults = true"
                                   @click.outside="showResults = false"
                                   class="{{ $inputClass }} pr-10"
                                   placeholder="جستجوی مالک جدید...">

                            {{-- لودینگ --}}
                            <div x-show="isSearching" class="absolute left-3 top-2.5">
                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>

                            {{-- نتایج جستجو --}}
                            <div x-show="showResults && searchResults.length > 0"
                                 class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                <ul>
                                    <template x-for="owner in searchResults" :key="owner.id">
                                        <li @click="selectOwner(owner)" class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700/50 last:border-0 transition-colors">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-bold" x-text="owner.first_name + ' ' + owner.last_name"></span>
                                                <span class="text-xs text-gray-500 dir-ltr" x-text="owner.phone"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <button type="button" @click="showOwnerModal = true" class="px-4 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-xl hover:bg-indigo-100 transition-colors border border-indigo-100 dark:border-indigo-800">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- کارت نقشه --}}
            <div class="{{ $cardClass }} p-6">
                <div class="flex items-center justify-between gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">موقعیت مکانی</h2>
                    </div>
                    <button type="button" @click="getCurrentLocation" class="text-xs flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        لوکیشن من
                    </button>
                </div>

                <div class="space-y-4">
                    <div id="map" class="w-full h-80 rounded-2xl z-0 border border-gray-200 dark:border-gray-600 shadow-inner"></div>

                    <div>
                        <label class="{{ $labelClass }}">آدرس دقیق</label>
                        <div class="relative">
                            <textarea name="address" x-model="address" rows="2" class="{{ $inputClass }} resize-none text-right pr-10 leading-relaxed" placeholder="آدرس به صورت خودکار از روی نقشه دریافت می‌شود..."></textarea>
                            <div class="absolute right-3 top-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- دکمه ذخیره اصلی --}}
    <div class="fixed bottom-0 left-0 right-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 px-4 py-4 lg:hidden">
        <button type="submit" class="w-full px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg hover:bg-indigo-700 transition-all">
            ذخیره تغییرات
        </button>
    </div>

    {{-- دکمه ذخیره دسکتاپ --}}
    <div class="hidden lg:flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
            ذخیره تغییرات
        </button>
    </div>

</form>
