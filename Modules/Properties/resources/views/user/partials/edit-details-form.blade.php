<form action="{{ route('user.properties.update', $property) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="latitude" x-model="lat">
    <input type="hidden" name="longitude" x-model="lng">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Images & File Info --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Cover Image --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">عکس شاخص</label>
                <div
                    class="relative w-full h-64 border-2 border-dashed rounded-xl flex flex-col items-center justify-center transition-colors overflow-hidden"
                    :class="coverPreview ? 'border-indigo-500' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400'"
                >
                    <template x-if="!coverPreview">
                        <div class="text-center p-4 pointer-events-none w-full h-full flex flex-col items-center justify-center">
                            @if($property->cover_image)
                                <img src="{{ asset('storage/' . $property->cover_image) }}"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'; this.parentElement.classList.add('bg-gray-100');">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                    <p class="text-white text-sm font-medium">برای تغییر کلیک کنید</p>
                                </div>
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">آپلود عکس جدید</p>
                            @endif
                        </div>
                    </template>

                    <template x-if="coverPreview">
                        <div class="absolute inset-0 w-full h-full">
                            <img :src="coverPreview" class="w-full h-full object-cover">
                            <button type="button" @click="removeCover" class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    <input type="file" name="cover_image" id="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-0" @change="handleCoverSelect" accept="image/*">
                </div>
            </div>

            {{-- Gallery Images --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">گالری تصاویر</label>

                {{-- Existing Images --}}
                @if($property->images->count() > 0)
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        @foreach($property->images as $image)
                            <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group" id="image-{{ $image->id }}">
                                <img src="{{ asset('storage/' . $image->path) }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=Error';">
                                <button type="button" @click="deleteImage({{ $image->id }})" class="absolute top-1 right-1 bg-red-500/80 text-white p-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- New Images Upload --}}
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <template x-for="(img, index) in galleryPreviews" :key="index">
                        <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group">
                            <img :src="img" class="w-full h-full object-cover">
                            <button type="button" @click="removeGalleryImage(index)" class="absolute top-1 right-1 bg-red-500/80 text-white p-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    <label class="aspect-square border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <input type="file" name="gallery_images[]" multiple class="hidden" @change="handleGallerySelect" accept="image/*">
                    </label>
                </div>
                <p class="text-xs text-gray-500">افزودن تصاویر جدید</p>
            </div>

            {{-- Video Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ویدیو ملک</label>
                <div class="relative w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center hover:border-indigo-400 transition-colors">

                    <template x-if="!videoPreview">
                        <div class="text-center pointer-events-none w-full">
                            @if($property->video)
                                <video src="{{ asset('storage/' . $property->video) }}" controls class="w-full rounded-lg max-h-48 mb-2"></video>
                                <p class="text-xs text-gray-500">برای تغییر ویدیو کلیک کنید</p>
                            @else
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <p class="mt-1 text-xs text-gray-500">انتخاب ویدیو جدید</p>
                            @endif
                        </div>
                    </template>

                    <template x-if="videoPreview">
                        <div class="w-full relative">
                            <video :src="videoPreview" controls class="w-full rounded-lg max-h-48"></video>
                            <button type="button" @click="removeVideo" class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    <input type="file" name="video" id="video" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleVideoSelect" accept="video/*" x-show="!videoPreview || (videoPreview && !'{{ $property->video }}')">
                </div>
            </div>

            {{-- File Information (Moved Here) --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">اطلاعات فایل</h3>
                <div class="space-y-4">
                    <div>
                        <label for="registered_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ ثبت ملک</label>
                        <input type="text" id="registered_at" name="registered_at" data-jdp value="{{ old('registered_at', $property->registered_at_jalali ?? \Morilog\Jalali\Jalalian::now()->format('Y/m/d')) }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت ملک</label>
                        <select id="status_id" name="status_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">انتخاب کنید</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ $property->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="publication_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت انتشار</label>
                        <select id="publication_status" name="publication_status" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="published" {{ $property->publication_status == 'published' ? 'selected' : '' }}>منتشر شده</option>
                            <option value="draft" {{ $property->publication_status == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        </select>
                    </div>

                    <div>
                        <label for="confidential_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">یادداشت‌های محرمانه</label>
                        <textarea id="confidential_notes" name="confidential_notes" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="این یادداشت‌ها فقط برای شما قابل مشاهده است...">{{ old('confidential_notes', $property->confidential_notes) }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Form Fields --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان ملک</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $property->title) }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div>
                    <label for="listing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع فایل</label>
                    <select id="listing_type" name="listing_type" x-model="listingType" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="sale">فروش</option>
                        <option value="presale">پیش‌فروش</option>
                        <option value="rent">رهن و اجاره</option>
                    </select>
                </div>

                <div>
                    <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع ملک</label>
                    <select id="property_type" name="property_type" x-model="propertyType" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="apartment">خانه و آپارتمان</option>
                        <option value="villa">ویلا</option>
                        <option value="land">زمین و باغ</option>
                        <option value="office">اداری و تجاری</option>
                    </select>
                </div>

                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع سند</label>
                    <select id="document_type" name="document_type" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">انتخاب کنید</option>
                        @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                            <option value="{{ $key }}" {{ $property->document_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="building_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ساختمان / برج</label>
                    <select id="building_id" name="building_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">انتخاب کنید</option>
                        {{-- Options will be loaded dynamically later --}}
                        @if($property->building_id)
                            <option value="{{ $property->building_id }}" selected>ساختمان فعلی ({{ $property->building_id }})</option>
                        @endif
                    </select>
                </div>

                {{-- Conditional Fields --}}
                <div x-show="propertyType === 'land'" x-transition class="md:col-span-2">
                    <label for="usage_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع کاربری</label>
                    <select id="usage_type" name="usage_type" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">انتخاب کنید</option>
                        <option value="residential" {{ $property->usage_type == 'residential' ? 'selected' : '' }}>مسکونی</option>
                        <option value="industrial" {{ $property->usage_type == 'industrial' ? 'selected' : '' }}>صنعتی</option>
                        <option value="commercial" {{ $property->usage_type == 'commercial' ? 'selected' : '' }}>اداری و تجاری</option>
                        <option value="agricultural" {{ $property->usage_type == 'agricultural' ? 'selected' : '' }}>کشاورزی</option>
                    </select>
                </div>

                <div x-show="listingType === 'presale'" x-transition class="md:col-span-2">
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ تحویل (حدودی)</label>
                    <input type="text" id="delivery_date" name="delivery_date" data-jdp value="{{ old('delivery_date', $property->delivery_date_jalali ?? '') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">توضیحات</label>
                <textarea id="description" name="description" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $property->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">کد ملک</label>
                    <input type="text" name="code" value="{{ old('code', $property->code) }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی شخصی</label>
                    <select id="category_id" name="category_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">بدون دسته‌بندی</option>
                        @foreach(\Modules\Properties\Entities\PropertyCategory::where('user_id', auth()->id())->get() as $category)
                            <option value="{{ $category->id }}" {{ $property->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Owner Selection with Search --}}
                <div class="md:col-span-2">
                    <label for="owner_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مالک</label>
                    <div class="flex gap-2 relative">
                        <input type="hidden" name="owner_id" x-model="selectedOwnerId">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="owner_search"
                                x-model="searchQuery"
                                @input.debounce.300ms="searchOwners()"
                                @focus="if(searchQuery.length >= 2) showResults = true"
                                @click.away="showResults = false"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="جستجو بر اساس نام یا شماره تماس..."
                                autocomplete="off"
                            >
                            <div x-show="isSearching" class="absolute left-3 top-3">
                                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- Search Results Dropdown --}}
                            <div x-show="showResults && searchResults.length > 0" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                <ul>
                                    <template x-for="owner in searchResults" :key="owner.id">
                                        <li @click="selectOwner(owner)" class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                            <div class="font-medium" x-text="owner.first_name + ' ' + owner.last_name"></div>
                                            <div class="text-xs text-gray-500" x-text="owner.phone"></div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div x-show="showResults && searchResults.length === 0 && !isSearching" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                                موردی یافت نشد.
                            </div>
                        </div>

                        <button type="button" @click="showOwnerModal = true" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Map & Address --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">موقعیت روی نقشه</label>
                    <button type="button" @click="getCurrentLocation" class="text-xs flex items-center gap-1 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        دریافت موقعیت فعلی
                    </button>
                </div>

                <div id="map" class="w-full h-64 rounded-xl z-0 border border-gray-300 dark:border-gray-700"></div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس دقیق</label>
                    <textarea id="address" name="address" x-model="address" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="آدرس به صورت خودکار از نقشه دریافت می‌شود..."></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    ذخیره تغییرات
                </button>
            </div>
        </div>
    </div>
</form>
