<div class="space-y-8">
    {{-- نمایش کلید تازه ساخته شده --}}
    @if(session('new_api_key'))
        <div class="bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-200 dark:border-indigo-800 p-6 rounded-2xl animate-in fade-in zoom-in duration-300">
            <div class="flex items-center gap-3 mb-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">کلید دسترسی جدید با موفقیت ایجاد شد</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                توجه: این کلید به دلایل امنیتی فقط <strong>یک‌بار</strong> نمایش داده می‌شود. لطفاً آن را کپی کرده و در محل امنی ذخیره کنید. در صورت گم شدن، باید کلید را حذف و کلید جدید بسازید.
            </p>
            <div class="flex items-center gap-3">
                <input type="text" readonly value="{{ session('new_api_key') }}" id="new-api-key-input"
                       class="flex-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-4 py-3 rounded-xl text-sm font-mono text-gray-800 dark:text-gray-100 text-left dir-ltr focus:outline-none">
                <button type="button" onclick="copyNewApiKey()"
                        class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all flex items-center gap-2" id="copy-btn">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="copy-icon-svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                    <span id="copy-btn-text">کپی کلید</span>
                </button>
            </div>
        </div>

        <script>
            function copyNewApiKey() {
                const input = document.getElementById('new-api-key-input');
                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value);

                const btnText = document.getElementById('copy-btn-text');
                const btn = document.getElementById('copy-btn');
                btnText.textContent = 'کپی شد!';
                btn.classList.replace('bg-indigo-600', 'bg-emerald-600');
                btn.classList.replace('hover:bg-indigo-700', 'hover:bg-emerald-700');

                setTimeout(() => {
                    btnText.textContent = 'کپی کلید';
                    btn.classList.replace('bg-emerald-600', 'bg-indigo-600');
                    btn.classList.replace('hover:bg-emerald-700', 'hover:bg-indigo-700');
                }, 2000);
            }
        </script>
    @endif

    {{-- کارت اصلی: لیست کلیدها --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت کلیدهای API</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">کلیدهای دسترسی جهت خواندن اطلاعات املاک توسط وب‌سایت‌های خارجی</p>
            </div>
        </div>

        <div class="p-6">
            @if($apiKeys->isEmpty())
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">هنوز هیچ کلید API فعالی ساخته نشده است.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                            <tr>
                                <th scope="col" class="px-6 py-4 rounded-r-xl">نام کلید</th>
                                <th scope="col" class="px-6 py-4">ماژول</th>
                                <th scope="col" class="px-6 py-4">محدودیت نرخ (ساعت)</th>
                                <th scope="col" class="px-6 py-4">انقضا</th>
                                <th scope="col" class="px-6 py-4">تعداد درخواست</th>
                                <th scope="col" class="px-6 py-4">وضعیت</th>
                                <th scope="col" class="px-6 py-4 rounded-l-xl">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($apiKeys as $key)
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <th scope="row" class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                        <div class="flex flex-col gap-1">
                                            <span>{{ $key->name }}</span>
                                            <span class="text-xs font-mono text-gray-400 dir-ltr text-right">
                                                crm_key_...{{ substr($key->key, -8) }}
                                            </span>
                                        </div>
                                    </th>
                                    <td class="px-6 py-4 text-xs font-semibold">
                                        @if($key->module === 'booking')
                                            <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-900/10 text-purple-700 dark:text-purple-400 rounded-lg">
                                                نوبت‌دهی (Booking)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 rounded-lg">
                                                املاک (Properties)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($key->rate_limit_per_hour)
                                            <span class="px-2.5 py-1 bg-yellow-50 dark:bg-yellow-900/10 text-yellow-700 dark:text-yellow-400 rounded-lg text-xs font-semibold">
                                                {{ $key->rate_limit_per_hour }} درخواست
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-lg text-xs font-semibold">
                                                بدون محدودیت
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        @if($key->expires_at)
                                            <span class="{{ $key->expires_at->isPast() ? 'text-red-500' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $key->expires_at->format('Y-m-d') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">نامحدود</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-gray-700 dark:text-gray-300">
                                        {{ number_format($key->usage_count) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('settings.api-keys.toggle', $key->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1.5 focus:outline-none">
                                                @if($key->is_active)
                                                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                    <span class="text-emerald-700 dark:text-emerald-400 text-xs font-bold">فعال</span>
                                                @else
                                                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                                                    <span class="text-red-700 dark:text-red-400 text-xs font-bold">غیرفعال</span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            {{-- دکمه پیش‌نمایش خروجی --}}
                                            <a href="{{ route('settings.api-keys.preview', $key->id) }}" target="_blank"
                                               class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 flex items-center justify-center transition-colors"
                                               title="پیش‌نمایش خروجی JSON">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            {{-- دکمه مستندات --}}
                                            <a href="{{ route('settings.api-keys.docs', $key->docs_token) }}" target="_blank"
                                               class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 flex items-center justify-center transition-colors"
                                               title="مستندات کلید">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </a>
                                            {{-- دکمه حذف --}}
                                            <form action="{{ route('settings.api-keys.destroy', $key->id) }}" method="POST"
                                                  onsubmit="return confirm('آیا از حذف این کلید مطمئن هستید؟ با حذف این کلید، تمام دسترسی‌های متصل به آن قطع خواهند شد.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors"
                                                        title="حذف کلید">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- کارت ۲: ایجاد کلید جدید --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">ایجاد کلید API جدید</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ساخت توکن دسترسی اختصاصی با فیلترها و محدودیت‌های دلخواه</p>
            </div>
        </div>

        <form action="{{ route('settings.api-keys.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="key_name" class="{{ $labelClass }}">نام کلید (مثال: سایت وردپرس اصلی)</label>
                    <input type="text" class="{{ $inputClass }}" id="key_name" name="name" required placeholder="مثال: سایت وردپرس">
                </div>

                <div>
                    <label for="key_module" class="{{ $labelClass }}">انتخاب ماژول</label>
                    <select name="module" id="key_module" class="{{ $inputClass }}" onchange="toggleModuleFilters(this.value)">
                        @if($isPropertiesActive)
                            <option value="properties" selected>املاک (Properties)</option>
                        @endif
                        @if($isBookingActive)
                            <option value="booking" {{ !$isPropertiesActive ? 'selected' : '' }}>نوبت‌دهی (Booking)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="rate_limit_per_hour" class="{{ $labelClass }}">محدودیت درخواست در ساعت</label>
                    <input type="number" class="{{ $inputClass }}" id="rate_limit_per_hour" name="rate_limit_per_hour" placeholder="خالی برای بدون محدودیت">
                </div>

                <div>
                    <label for="expires_at" class="{{ $labelClass }}">تاریخ انقضا</label>
                    <input type="date" class="{{ $inputClass }}" id="expires_at" name="expires_at">
                </div>
            </div>

            {{-- فیلترهای ماژول املاک --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6" id="properties-filters-section">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">فیلترهای پیش‌فرض خروجی (ماژول املاک)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="pub_status" class="{{ $labelClass }}">وضعیت انتشار ملک</label>
                        <select name="filters[publication_status]" id="pub_status" class="{{ $inputClass }}">
                            <option value="published" selected>فقط املاک منتشر شده</option>
                            <option value="draft">فقط پیش‌نویس‌ها</option>
                            <option value="all">همه املاک</option>
                        </select>
                    </div>

                    <div>
                        <label for="require_show_crm" class="{{ $labelClass }}">نمایش در سایت فعال باشد؟</label>
                        <select name="filters[require_show_in_crm]" id="require_show_crm" class="{{ $inputClass }}">
                            <option value="1" selected>بله (فقط املاکی که گزینه نمایش در سایت دارند)</option>
                            <option value="0">خیر (همه املاک بدون در نظر گرفتن این گزینه)</option>
                        </select>
                    </div>

                    <div>
                        <label for="per_page_max" class="{{ $labelClass }}">حداکثر تعداد در هر صفحه</label>
                        <input type="number" class="{{ $inputClass }}" id="per_page_max" name="filters[per_page_max]" value="100" min="1" max="500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="order_by" class="{{ $labelClass }}">مرتب‌سازی بر اساس</label>
                        <select name="filters[order_by]" id="order_by" class="{{ $inputClass }}">
                            <option value="created_at" selected>تاریخ ثبت</option>
                            <option value="updated_at">آخرین بروزرسانی</option>
                            <option value="price">قیمت ملک</option>
                            <option value="area">متراژ ملک</option>
                        </select>
                    </div>

                    <div>
                        <label for="order_direction" class="{{ $labelClass }}">جهت مرتب‌سازی</label>
                        <select name="filters[order_direction]" id="order_direction" class="{{ $inputClass }}">
                            <option value="desc" selected>نزولی (جدیدترین / بالاترین)</option>
                            <option value="asc">صعودی (قدیمی‌ترین / پایین‌ترین)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    {{-- نوع معامله --}}
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800">
                        <label class="{{ $labelClass }}">نوع معامله مجاز</label>
                        <div class="space-y-2 mt-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[listing_types][]" value="sale" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>فروش</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[listing_types][]" value="rent" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>اجاره / رهن</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[listing_types][]" value="presale" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>پیش‌فروش</span>
                            </label>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-3">انتخاب نکنید تا همه موارد مجاز باشند.</p>
                    </div>

                    {{-- نوع ملک --}}
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800">
                        <label class="{{ $labelClass }}">نوع ملک مجاز</label>
                        <div class="space-y-2 mt-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[property_types][]" value="apartment" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>آپارتمان</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[property_types][]" value="villa" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>ویلا</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[property_types][]" value="land" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>زمین / کلنگی</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="filters[property_types][]" value="office" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>اداری / تجاری</span>
                            </label>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-3">انتخاب نکنید تا همه موارد مجاز باشند.</p>
                    </div>

                    {{-- وضعیت‌های مجاز ملک --}}
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800">
                        <label class="{{ $labelClass }}">وضعیت‌های مجاز ملک</label>
                        <div class="space-y-2 mt-2 max-h-[120px] overflow-y-auto pr-1">
                            @foreach($propertyStatuses as $status)
                                <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="filters[status_ids][]" value="{{ $status->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span style="color: {{ $status->color }}">{{ $status->label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-400 mt-3">انتخاب نکنید تا همه وضعیت‌ها مجاز باشند.</p>
                    </div>
                </div>

                {{-- دسترسی اطلاعات حساس املاک --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">دسترسی و حریم خصوصی داده‌ها</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50/30 dark:bg-gray-950/10 rounded-xl border border-gray-100 dark:border-gray-800 flex items-start gap-3">
                            <input type="checkbox" name="permissions[include_owner]" id="perm_include_owner" value="1"
                                   class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <label for="perm_include_owner" class="text-xs font-bold text-gray-900 dark:text-white cursor-pointer select-none">
                                    ارسال اطلاعات تماس مالکین در خروجی API
                                </label>
                                <p class="text-[10px] text-gray-400 mt-1">با فعال کردن این گزینه، اطلاعات تماس مالکین (شامل نام و تلفن) در پاسخ‌های API ارسال خواهد شد. (پیش‌فرض: غیرفعال)</p>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50/30 dark:bg-gray-950/10 rounded-xl border border-gray-100 dark:border-gray-800 flex items-start gap-3">
                            <input type="checkbox" name="permissions[include_confidential_notes]" id="perm_include_notes" value="1"
                                   class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <label for="perm_include_notes" class="text-xs font-bold text-gray-900 dark:text-white cursor-pointer select-none">
                                    ارسال یادداشت‌های محرمانه ملک در خروجی API
                                </label>
                                <p class="text-[10px] text-gray-400 mt-1">با فعال کردن این گزینه، متن فیلد یادداشت‌های محرمانه املاک در خروجی API ارسال خواهد شد. (پیش‌فرض: غیرفعال)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- فیلترهای ماژول نوبت‌دهی --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6" id="booking-filters-section" style="display: none;">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">فیلترهای پیش‌فرض خروجی (ماژول نوبت‌دهی)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="service_status" class="{{ $labelClass }}">وضعیت سرویس‌ها</label>
                        <select name="filters[service_status]" id="service_status" class="{{ $inputClass }}">
                            <option value="active" selected>فقط سرویس‌های فعال (Active)</option>
                            <option value="inactive">فقط سرویس‌های غیرفعال (Inactive)</option>
                            <option value="all">همه سرویس‌ها</option>
                        </select>
                    </div>

                    <div>
                        <label for="b_per_page_max" class="{{ $labelClass }}">حداکثر تعداد در هر صفحه</label>
                        <input type="number" class="{{ $inputClass }}" id="b_per_page_max" name="filters[per_page_max]" value="100" min="1" max="500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="b_order_by" class="{{ $labelClass }}">مرتب‌سازی بر اساس</label>
                        <select name="filters[order_by]" id="b_order_by" class="{{ $inputClass }}">
                            <option value="created_at" selected>تاریخ ثبت</option>
                            <option value="updated_at">آخرین بروزرسانی</option>
                            <option value="name">نام سرویس</option>
                            <option value="base_price">قیمت پایه</option>
                        </select>
                    </div>

                    <div>
                        <label for="b_order_direction" class="{{ $labelClass }}">جهت مرتب‌سازی</label>
                        <select name="filters[order_direction]" id="b_order_direction" class="{{ $inputClass }}">
                            <option value="desc" selected>نزولی (جدیدترین / بالاترین)</option>
                            <option value="asc">صعودی (قدیمی‌ترین / پایین‌ترین)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mt-6">
                    {{-- دسته‌بندی‌های مجاز سرویس --}}
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-800">
                        <label class="{{ $labelClass }}">دسته‌بندی‌های مجاز سرویس نوبت‌دهی</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2 max-h-[150px] overflow-y-auto pr-1">
                            @foreach($bookingCategories as $bCat)
                                <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="filters[category_ids][]" value="{{ $bCat->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $bCat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-400 mt-3">انتخاب نکنید تا سرویس‌های همه دسته‌بندی‌ها مجاز باشند.</p>
                    </div>
                </div>

                {{-- دسترسی اطلاعات حساس نوبت‌دهی --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">دسترسی و حریم خصوصی داده‌ها (نوبت‌دهی)</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="p-4 bg-gray-50/30 dark:bg-gray-950/10 rounded-xl border border-gray-100 dark:border-gray-800 flex items-start gap-3">
                            <input type="checkbox" name="permissions[include_providers]" id="perm_include_providers" value="1" checked
                                   class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <label for="perm_include_providers" class="text-xs font-bold text-gray-900 dark:text-white cursor-pointer select-none">
                                    ارسال اطلاعات پزشکان / ارائه‌دهندگان سرویس نوبت‌دهی
                                </label>
                                <p class="text-[10px] text-gray-400 mt-1">با فعال کردن این گزینه، مشخصات پزشکان و ارائه‌دهندگان مرتبط با هر سرویس نوبت‌دهی در پاسخ‌های API ارسال خواهد شد. (پیش‌فرض: فعال)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    ایجاد کلید API جدید
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleModuleFilters(module) {
            const propSec = document.getElementById('properties-filters-section');
            const bookingSec = document.getElementById('booking-filters-section');
            
            if (module === 'booking') {
                if (propSec) propSec.style.display = 'none';
                if (bookingSec) bookingSec.style.display = 'block';
            } else {
                if (propSec) propSec.style.display = 'block';
                if (bookingSec) bookingSec.style.display = 'none';
            }
        }
        
        // Trigger onload to set initial state
        document.addEventListener('DOMContentLoaded', function() {
            const moduleSelect = document.getElementById('key_module');
            if (moduleSelect) {
                toggleModuleFilters(moduleSelect.value);
            }
        });
    </script>
</div>
