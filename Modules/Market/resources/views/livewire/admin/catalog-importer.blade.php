<div class="max-w-6xl mx-auto my-6 px-4">
    {{-- هدر صفحه --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">وارد کردن گروهی کاتالوگ محصولات (CSV)</h1>
            <p class="text-sm text-gray-550 dark:text-gray-400 mt-1">با استفاده از فایل CSV می‌توانید محصولات کاتالوگ را به صورت یکجا ثبت یا ویرایش کنید.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="downloadTemplate" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold shadow-sm transition-all">
                <svg class="w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                دانلود فایل نمونه استاندارد
            </button>
            <a href="{{ route('user.market.master-products.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-all">
                بازگشت به کاتالوگ
            </a>
        </div>
    </div>

    {{-- کارت اصلی ایمپورت --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden p-6 md:p-8">
        
        {{-- استپ‌های پیشرفت کار --}}
        <div class="flex items-center justify-center gap-4 mb-8 max-w-2xl mx-auto text-sm font-bold text-gray-450 dark:text-gray-500">
            <div class="flex items-center gap-2.5 {{ !$isParsed && !$importing && !$isFinished ? 'text-fuchsia-600 dark:text-fuchsia-400' : 'text-emerald-500' }}">
                <span class="w-7 h-7 flex items-center justify-center rounded-full border-2 {{ !$isParsed && !$importing && !$isFinished ? 'border-fuchsia-600 dark:border-fuchsia-400 font-black' : 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/20 font-black' }}">۱</span>
                آپلود فایل
            </div>
            <div class="w-16 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex items-center gap-2.5 {{ $isParsed && !$importing && !$isFinished ? 'text-fuchsia-600 dark:text-fuchsia-400' : ($isFinished || $importing ? 'text-emerald-500' : '') }}">
                <span class="w-7 h-7 flex items-center justify-center rounded-full border-2 {{ $isParsed && !$importing && !$isFinished ? 'border-fuchsia-600 dark:border-fuchsia-400 font-black' : ($isFinished || $importing ? 'border-emerald-500 bg-emerald-50 font-black' : 'border-gray-200 dark:border-gray-700') }}">۲</span>
                نگاشت ستون‌ها
            </div>
            <div class="w-16 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex items-center gap-2.5 {{ $importing ? 'text-fuchsia-600 dark:text-fuchsia-400' : ($isFinished ? 'text-emerald-500' : '') }}">
                <span class="w-7 h-7 flex items-center justify-center rounded-full border-2 {{ $importing ? 'border-fuchsia-600 dark:border-fuchsia-400 font-black' : ($isFinished ? 'border-emerald-500 bg-emerald-50 font-black' : 'border-gray-200 dark:border-gray-700') }}">۳</span>
                پردازش داده‌ها
            </div>
        </div>

        {{-- مرحله اول: آپلود فایل --}}
        @if(!$isParsed && !$importing && !$isFinished)
            <div class="flex flex-col items-center justify-center py-10">
                <div class="w-full max-w-lg"
                     x-data="{ isDragging: false }"
                     x-on:dragover.prevent="isDragging = true"
                     x-on:dragleave.prevent="isDragging = false"
                     x-on:drop.prevent="isDragging = false; $wire.upload('file', $event.dataTransfer.files[0])">
                    
                    <label class="flex flex-col items-center justify-center w-full h-72 border-2 border-dashed rounded-3xl cursor-pointer transition-all duration-300 relative overflow-hidden"
                           :class="isDragging ? 'border-fuchsia-500 bg-fuchsia-50/30 dark:bg-fuchsia-950/10' : 'border-gray-300 hover:border-fuchsia-400 dark:border-gray-700 dark:hover:border-gray-600 bg-gray-50/50 dark:bg-gray-900/30'">
                        
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                            <div class="p-5 bg-fuchsia-50 dark:bg-fuchsia-950/20 rounded-2xl mb-4 text-fuchsia-600 dark:text-fuchsia-400 transition-transform">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-base font-bold text-gray-800 dark:text-gray-200">فایل CSV خود را اینجا رها کنید یا کلیک کنید</p>
                            <p class="text-xs text-gray-400 mt-2">فرمت‌های مجاز: CSV, TXT (حداکثر ۱۰ مگابایت)</p>
                        </div>
                        
                        <input type="file" wire:model="file" class="hidden" accept=".csv,.txt" />
                    </label>
                </div>

                {{-- راهنمای کوتاه قالب فایل --}}
                <div class="w-full max-w-2xl mt-10 bg-gray-50 dark:bg-gray-900/40 rounded-2xl p-6 border border-gray-150 dark:border-gray-700/80 text-sm leading-relaxed text-gray-650 dark:text-gray-350">
                    <h3 class="font-black text-fuchsia-700 dark:text-fuchsia-400 mb-3 flex items-center gap-1.5 text-base">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        راهنمای ساختار فایل CSV کاتالوگ:
                    </h3>
                    <ul class="list-disc pr-5 space-y-2">
                        <li><strong>برند و دسته‌بندی خودکار:</strong> اگر برند یا دسته‌بندی وارد شده در سیستم وجود نداشته باشد، سیستم به صورت هوشمند آن را ایجاد می‌کند.</li>
                        <li><strong>مسیر دسته‌بندی درختی:</strong> برای ایجاد ساختار درختی دسته‌بندی‌ها، از علامت <code class="bg-white dark:bg-gray-800/80 px-1.5 py-0.5 rounded font-bold">&gt;</code> استفاده کنید. مثال: <code class="bg-white dark:bg-gray-800/80 px-1.5 py-0.5 rounded">کالای دیجیتال &gt; موبایل &gt; لوازم جانبی</code></li>
                        <li><strong>بروزرسانی محصولات موجود:</strong> سیستم برای شناسایی محصولات ثبت شده، کدهای CRM، بارکد و یا GTIN را جستجو می‌کند.</li>
                    </ul>
                </div>
            </div>
        @endif

        {{-- مرحله دوم: نگاشت ستون‌ها --}}
        @if($isParsed && !$importing && !$isFinished)
            <div>
                <h3 class="text-base font-black text-gray-850 dark:text-gray-100 mb-4">نگاشت ستون‌های CSV به فیلدهای محصول کاتالوگ</h3>
                
                {{-- چک‌باکس‌های تنظیمات --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 p-5 bg-gray-50 dark:bg-gray-900/30 rounded-2xl border border-gray-150 dark:border-gray-700/80">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="updateExisting" class="w-5 h-5 text-fuchsia-600 border-gray-300 rounded focus:ring-fuchsia-500 dark:bg-gray-850 dark:border-gray-750">
                        <div>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">به‌روزرسانی محصولات</span>
                            <p class="text-xs text-gray-400 mt-1">در صورت انطباق کدهای CRM، بارکد یا GTIN، محصول بروزرسانی می‌شود.</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="hasHeaders" class="w-5 h-5 text-fuchsia-600 border-gray-300 rounded focus:ring-fuchsia-500 dark:bg-gray-850 dark:border-gray-750">
                        <div>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">فایل حاوی سطر هدر است</span>
                            <p class="text-xs text-gray-400 mt-1">در صورت غیرفعال بودن، ردیف اول فایل نیز به عنوان محصول ایمپورت می‌شود.</p>
                        </div>
                    </label>
                    <div>
                        <label class="block text-sm font-bold text-gray-750 dark:text-gray-300 mb-2">وضعیت پیش‌فرض محصولات جدید</label>
                        <select wire:model="defaultStatus" class="w-full h-11 px-3 text-sm bg-white dark:bg-gray-800 border border-gray-250 dark:border-gray-700 rounded-xl focus:border-fuchsia-500 focus:ring-1 focus:ring-fuchsia-500 transition-all outline-none">
                            <option value="active">فعال (Active)</option>
                            <option value="draft">پیش‌نویس (Draft)</option>
                            <option value="archived">آرشیو شده (Archived)</option>
                        </select>
                    </div>
                </div>

                {{-- جدول نگاشت فیلدها --}}
                <div class="border border-gray-200 dark:border-gray-750 rounded-2xl overflow-hidden mb-6 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-750 text-right text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 font-bold text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-5 py-4">نام ستون در فایل شما</th>
                                <th class="px-5 py-4 w-72">نگاشت به فیلد کاتالوگ</th>
                                <th class="px-5 py-4">پیش‌نمایش داده‌های فایل شما (سطرهای اول)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-750 text-gray-800 dark:text-gray-200">
                            @foreach($csvHeaders as $index => $header)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10">
                                    <td class="px-5 py-4 font-bold text-gray-900 dark:text-white text-sm">
                                        {{ $header }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <select wire:model="fieldMapping.{{ $index }}" class="w-full h-10 px-3 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-fuchsia-500 focus:ring-1 focus:ring-fuchsia-500 transition-all outline-none">
                                            <option value="">-- عدم وارد کردن این ستون --</option>
                                            @foreach($availableFields as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-5 py-4 text-gray-400 dark:text-gray-400 text-xs overflow-hidden max-w-md truncate">
                                        @php
                                            $previews = [];
                                            foreach($previewData as $row) {
                                                if (isset($row[$index]) && trim($row[$index]) !== '') {
                                                    $previews[] = trim($row[$index]);
                                                }
                                            }
                                            echo !empty($previews) ? implode(' | ', array_slice($previews, 0, 3)) : '-';
                                        @endphp
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- دکمه‌های کنترل مرحله دوم --}}
                <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-5">
                    <button wire:click="resetState" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 text-gray-650 dark:bg-gray-900 dark:hover:bg-gray-750 dark:text-gray-300 rounded-xl text-sm font-bold transition-all">
                        انصراف و آپلود مجدد
                    </button>
                    <button wire:click="startImport" class="px-7 py-3 bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-fuchsia-500/20 active:scale-95 transition-all">
                        شروع پردازش و وارد کردن داده‌ها
                    </button>
                </div>
            </div>
        @endif

        {{-- مرحله سوم: در حال پردازش (توسط Polling) --}}
        @if($importing && !$isFinished)
            <div class="py-12 flex flex-col items-center justify-center text-center" wire:poll.500ms="processChunk">
                <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
                    {{-- انیمیشن لودینگ چرخشی --}}
                    <div class="absolute inset-0 border-4 border-fuchsia-100 dark:border-fuchsia-950/40 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-fuchsia-600 dark:border-fuchsia-400 rounded-full border-t-transparent animate-spin"></div>
                    <svg class="w-8 h-8 text-fuchsia-600 dark:text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>

                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">درحال پردازش و وارد کردن کاتالوگ...</h3>
                <p class="text-sm text-gray-550 dark:text-gray-400 mb-6">سرور در حال خواندن ردیف‌های فایل CSV و همگام‌سازی با دیتابیس است. لطفاً پنجره را نبندید.</p>

                {{-- نوار پیشرفت کار --}}
                <div class="w-full max-w-md bg-gray-100 dark:bg-gray-950 h-3 rounded-full overflow-hidden mb-4 p-0.5 border border-gray-200/50 dark:border-gray-700/50">
                    @php
                        $percentage = $totalRows > 0 ? min(100, round(($processedRows / $totalRows) * 100)) : 0;
                    @endphp
                    <div class="bg-fuchsia-600 dark:bg-fuchsia-400 h-full rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                </div>

                <div class="flex items-center gap-6 text-sm font-bold text-gray-650 dark:text-gray-400">
                    <div>پیشرفت: {{ $processedRows }} از {{ $totalRows }} ردیف ({{ $percentage }}%)</div>
                    <div class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                    <div class="text-emerald-600 dark:text-emerald-450">ایجاد: {{ $importCount }}</div>
                    <div class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                    <div class="text-blue-600 dark:text-blue-450">بروزرسانی: {{ $updateCount }}</div>
                    @if(count($importErrors) > 0)
                        <div class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                        <div class="text-red-500">خطا: {{ count($importErrors) }}</div>
                    @endif
                </div>
            </div>
        @endif

        {{-- مرحله چهارم: اتمام پردازش و نمایش خلاصه گزارش --}}
        @if($isFinished)
            <div>
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="p-5 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl mb-4 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">عملیات ایمپورت با موفقیت خاتمه یافت!</h3>
                    <p class="text-sm text-gray-550 dark:text-gray-400 mt-1">کلیه سطرهای معتبر فایل CSV پردازش و در دیتابیس کاتالوگ اعمال شدند.</p>
                </div>

                {{-- کارت گزارش آماری --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 text-center">
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1">کل ردیف‌های پردازش‌شده</span>
                        <span class="text-xl font-black text-gray-800 dark:text-white">{{ $processedRows }}</span>
                    </div>
                    <div class="bg-emerald-50/10 dark:bg-emerald-950/10 p-4 rounded-2xl border border-emerald-100/50 dark:border-emerald-900/20 text-center">
                        <span class="block text-xs font-bold text-emerald-600 dark:text-emerald-500 mb-1">محصولات ایجاد شده</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $importCount }}</span>
                    </div>
                    <div class="bg-blue-50/10 dark:bg-blue-950/10 p-4 rounded-2xl border border-blue-100/50 dark:border-blue-900/20 text-center">
                        <span class="block text-xs font-bold text-blue-600 dark:text-blue-500 mb-1">محصولات بروزرسانی شده</span>
                        <span class="text-xl font-black text-blue-600 dark:text-blue-400">{{ $updateCount }}</span>
                    </div>
                    <div class="bg-red-50/10 dark:bg-red-950/10 p-4 rounded-2xl border border-red-100/50 dark:border-red-900/20 text-center">
                        <span class="block text-xs font-bold text-red-500 dark:text-red-400 mb-1">تعداد موارد رد شده (خطا)</span>
                        <span class="text-xl font-black text-red-500">{{ count($importErrors) }}</span>
                    </div>
                </div>

                {{-- جدول خطاها در صورت وجود --}}
                @if(count($importErrors) > 0)
                    <div class="mb-8">
                        <h4 class="text-sm font-black text-red-500 dark:text-red-400 mb-3 flex items-center gap-1.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            لیست جزئیات خطاهای ردیابی شده حین ایمپورت:
                        </h4>
                        <div class="border border-red-100 dark:border-red-900/30 rounded-2xl overflow-hidden max-h-60 overflow-y-auto shadow-sm">
                            <table class="min-w-full divide-y divide-red-100 dark:divide-red-900/20 text-right text-sm">
                                <thead class="bg-red-50/50 dark:bg-red-950/10 font-bold text-red-700 dark:text-red-400">
                                    <tr>
                                        <th class="px-5 py-3 w-28">ردیف CSV</th>
                                        <th class="px-5 py-3">توضیح خطا و علت رد شدن سطر</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-red-50/50 dark:divide-red-950/20 text-gray-700 dark:text-gray-450 bg-white dark:bg-gray-800">
                                    @foreach($importErrors as $err)
                                        <tr class="hover:bg-red-50/10 transition-colors">
                                            <td class="px-5 py-3 font-bold text-red-650">
                                                {{ $err['row'] }}
                                            </td>
                                            <td class="px-5 py-3">
                                                {{ $err['error'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- دکمه شروع مجدد --}}
                <div class="flex items-center justify-end border-t border-gray-100 dark:border-gray-750 pt-5">
                    <button wire:click="resetState" class="px-6 py-3 bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-fuchsia-500/20 active:scale-95 transition-all">
                        وارد کردن فایل دیگر (جدید)
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>
