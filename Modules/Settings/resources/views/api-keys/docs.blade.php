<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مستندات API - {{ $apiKey->name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'system-ui', 'sans-serif'],
                        mono: ['Fira Code', 'Courier New', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .code-container {
            background-color: #1e293b;
        }
        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- هدر صفحه -->
    <header class="border-b border-slate-800 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-lg font-bold text-white">مستندات فنی کلید API</h1>
                    <p class="text-xs text-slate-400 mt-0.5">مخصوص توسعه‌دهندگان و اتصال به وب‌سایت‌های خارجی</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">نام کلید:</span>
                <span class="px-3 py-1 bg-indigo-900/40 border border-indigo-800 text-indigo-400 rounded-lg text-xs font-bold">{{ $apiKey->name }}</span>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- سایدبار اطلاعات کلید -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl space-y-6">
                    <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        مشخصات و محدودیت‌های کلید
                    </h2>

                    <!-- فیلد توکن -->
                    <div class="space-y-2">
                        <label class="text-xs text-slate-400 block">توکن اتصال (Bearer Token)</label>
                        <div class="flex items-center gap-2 bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-xs">
                            <input type="text" readonly id="api-key-raw" value="{{ $apiKey->key }}"
                                   class="bg-transparent border-none text-left dir-ltr w-full text-indigo-300 focus:outline-none">
                            <button onclick="copyToClipboard('api-key-raw', 'copy-key-btn')" id="copy-key-btn" class="text-slate-400 hover:text-white transition-colors" title="کپی توکن">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- محدودیت نرخ -->
                    <div class="flex justify-between items-center text-sm py-2 border-b border-slate-800">
                        <span class="text-slate-400">محدودیت درخواست در ساعت:</span>
                        <span class="font-bold text-white">
                            {{ $apiKey->rate_limit_per_hour ? $apiKey->rate_limit_per_hour . ' درخواست' : 'بدون محدودیت' }}
                        </span>
                    </div>

                    <!-- وضعیت کلید -->
                    <div class="flex justify-between items-center text-sm py-2 border-b border-slate-800">
                        <span class="text-slate-400">وضعیت کلید:</span>
                        @if($apiKey->isValid())
                            <span class="px-2 py-0.5 bg-emerald-950 text-emerald-400 border border-emerald-800 text-xs font-bold rounded-md">فعال</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-950 text-red-400 border border-red-800 text-xs font-bold rounded-md">غیرفعال / منقضی</span>
                        @endif
                    </div>

                    <!-- انقضا -->
                    <div class="flex justify-between items-center text-sm py-2 border-b border-slate-800">
                        <span class="text-slate-400">تاریخ انقضا:</span>
                        <span class="font-bold text-white">
                            {{ $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : 'بدون انقضا' }}
                        </span>
                    </div>

                    <!-- فیلترهای از پیش اعمال شده -->
                    <div class="space-y-3 pt-2">
                        <span class="text-xs text-slate-400 block">فیلترهای خروجی اجباری روی این کلید:</span>
                        
                        <ul class="text-xs space-y-2 text-slate-300">
                            <li class="flex items-center justify-between">
                                <span>وضعیت انتشار ملک:</span>
                                <strong class="text-white">
                                    @if(($apiKey->filters['publication_status'] ?? '') == 'published') فقط منتشر شده
                                    @elseif(($apiKey->filters['publication_status'] ?? '') == 'draft') فقط پیش‌نویس
                                    @else همه املاک @endif
                                </strong>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>نمایش در سایت فعال باشد؟</span>
                                <strong class="text-white">
                                    {{ ($apiKey->filters['require_show_in_crm'] ?? true) ? 'بله' : 'خیر' }}
                                </strong>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>حداکثر آیتم در هر صفحه:</span>
                                <strong class="text-white">{{ $apiKey->filters['per_page_max'] ?? 100 }}</strong>
                            </li>
                            @if(!empty($apiKey->filters['listing_types']))
                                <li class="flex flex-col gap-1">
                                    <span>نوع معامله‌های مجاز:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($apiKey->filters['listing_types'] as $t)
                                            <span class="bg-slate-850 px-2 py-0.5 rounded text-[10px] border border-slate-800 text-indigo-300">
                                                {{ $t === 'sale' ? 'فروش' : ($t === 'rent' ? 'اجاره' : 'پیش‌فروش') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                            @if(!empty($apiKey->filters['property_types']))
                                <li class="flex flex-col gap-1">
                                    <span>نوع ملک‌های مجاز:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($apiKey->filters['property_types'] as $t)
                                            <span class="bg-slate-850 px-2 py-0.5 rounded text-[10px] border border-slate-800 text-indigo-300">
                                                {{ $t === 'apartment' ? 'آپارتمان' : ($t === 'villa' ? 'ویلا' : ($t === 'land' ? 'زمین' : 'اداری/تجاری')) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                            @if(!empty($apiKey->filters['status_ids']))
                                <li class="flex flex-col gap-1">
                                    <span>وضعیت‌های مجاز ملک:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($statuses as $status)
                                            @if(in_array($status->id, $apiKey->filters['status_ids']))
                                                <span class="bg-slate-850 px-2 py-0.5 rounded text-[10px] border border-slate-800 text-white" style="border-color: {{ $status->color }}50">
                                                    {{ $status->label }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- دسترسی‌های ویژه حریم خصوصی -->
                    <div class="space-y-3 pt-4 border-t border-slate-850">
                        <span class="text-xs text-slate-400 block">دسترسی به فیلد‌های حساس:</span>
                        <ul class="text-xs space-y-2">
                            <li class="flex items-center justify-between">
                                <span>ارسال اطلاعات تماس مالکین:</span>
                                <strong class="{{ ($apiKey->permissions['include_owner'] ?? false) ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ ($apiKey->permissions['include_owner'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </strong>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>ارسال یادداشت‌های محرمانه:</span>
                                <strong class="{{ ($apiKey->permissions['include_confidential_notes'] ?? false) ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ ($apiKey->permissions['include_confidential_notes'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-amber-950/20 border border-amber-900/30 p-5 rounded-2xl text-xs text-amber-300 leading-relaxed">
                    <strong>توصیه امنیتی:</strong><br>
                    این صفحه به صورت عمومی بدون نیاز به ورود قابل دسترس است تا برنامه‌نویس شما بتواند آن را مطالعه کند. از اشتراک‌گذاری عمومی لینک این صفحه به دلیل وجود کلید اتصال خودداری کنید یا بعد از پایان کار، کلید را غیرفعال کنید.
                </div>
            </div>

            <!-- بخش مستندات متنی -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- راهنمای کلی -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                    <h2 class="text-base font-bold text-white mb-3">شروع کار با وب‌سرویس</h2>
                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        این وب‌سرویس به شما امکان می‌دهد تا املاک ثبت شده در سیستم CRM خود را در قالب ساختار استاندارد JSON در سایت‌های دیگر (مانند وب‌سایت وردپرسی خود) نمایش دهید. کلیه درخواست‌ها باید با هدر احراز هویت یا از طریق پارامتر URL ارسال شوند.
                    </p>
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-850 space-y-2 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 w-24">روش اول (هدر):</span>
                            <span class="font-mono text-indigo-400">Authorization: Bearer <span class="text-white">{{ $apiKey->key }}</span></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 w-24">روش دوم (URL):</span>
                            <span class="font-mono text-indigo-400"><span class="text-slate-450">{{ url('/external-api/properties') }}</span>?api=<span class="text-white">{{ $apiKey->key }}</span></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 w-24">فرمت خروجی:</span>
                            <span class="font-mono text-white">application/json</span>
                        </div>
                    </div>
                </div>

                <!-- متد ۱: دریافت لیست املاک -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-slate-800 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <span class="px-2.5 py-1 bg-emerald-950 border border-emerald-800 text-emerald-400 text-xs font-bold rounded-lg font-mono">GET</span>
                            <h2 class="inline-block text-base font-bold text-white mr-3">لیست املاک</h2>
                        </div>
                        <span class="font-mono text-xs text-indigo-400 bg-slate-950 px-3 py-1.5 rounded-lg select-all">
                            {{ url('/external-api/properties') }}
                        </span>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-sm text-slate-300 leading-relaxed">
                            دریافت لیست املاک ثبت‌شده فیلتر شده بر اساس محدودیت‌های این کلید API. می‌توانید با پارامترهای اختیاری زیر خروجی را دقیق‌تر فیلتر کنید.
                        </p>

                        <!-- جدول پارامترها -->
                        <div>
                            <h3 class="text-xs font-bold text-white mb-3">پارامترهای ارسالی در Query String (اختیاری)</h3>
                            <div class="overflow-x-auto border border-slate-850 rounded-xl">
                                <table class="w-full text-right text-xs">
                                    <thead class="bg-slate-950 text-slate-300 font-bold border-b border-slate-850">
                                        <tr>
                                            <th class="px-4 py-3">نام پارامتر</th>
                                            <th class="px-4 py-3">نوع</th>
                                            <th class="px-4 py-3">توضیحات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-850 text-slate-400">
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">per_page</td>
                                            <td class="px-4 py-3">integer</td>
                                            <td class="px-4 py-3">تعداد آیتم‌ها در صفحه (حداکثر {{ $apiKey->filters['per_page_max'] ?? 100 }})</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">page</td>
                                            <td class="px-4 py-3">integer</td>
                                            <td class="px-4 py-3">شماره صفحه جاری برای صفحه‌بندی</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">search</td>
                                            <td class="px-4 py-3">string</td>
                                            <td class="px-4 py-3">جستجو در عنوان، کد ملک، آدرس و توضیحات ملک</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">listing_type</td>
                                            <td class="px-4 py-3">string</td>
                                            <td class="px-4 py-3">نوع معامله: <code class="text-slate-300">sale</code> (فروش)، <code class="text-slate-300">rent</code> (رهن و اجاره)، <code class="text-slate-300">presale</code> (پیش فروش)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">property_type</td>
                                            <td class="px-4 py-3">string</td>
                                            <td class="px-4 py-3">نوع ملک: <code class="text-slate-300">apartment</code> (آپارتمان)، <code class="text-slate-300">villa</code> (ویلا)، <code class="text-slate-300">land</code> (زمین)، <code class="text-slate-300">office</code> (اداری/تجاری)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">category_id</td>
                                            <td class="px-4 py-3">integer</td>
                                            <td class="px-4 py-3">فیلتر بر اساس شناسه دسته‌بندی ملک</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">status_id</td>
                                            <td class="px-4 py-3">integer</td>
                                            <td class="px-4 py-3">فیلتر بر اساس شناسه وضعیت ملک</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">min_price / max_price</td>
                                            <td class="px-4 py-3">number</td>
                                            <td class="px-4 py-3">حداقل/حداکثر قیمت کل خرید (تومان)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">min_deposit_price / max_deposit_price</td>
                                            <td class="px-4 py-3">number</td>
                                            <td class="px-4 py-3">حداقل/حداکثر مبلغ رهن (تومان)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">min_rent_price / max_rent_price</td>
                                            <td class="px-4 py-3">number</td>
                                            <td class="px-4 py-3">حداقل/حداکثر مبلغ اجاره ماهیانه (تومان)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-indigo-400">min_area / max_area</td>
                                            <td class="px-4 py-3">integer</td>
                                            <td class="px-4 py-3">حداقل/حداکثر متراژ (متر مربع)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- تب نمونه کدها -->
                        <div>
                            <h3 class="text-xs font-bold text-white mb-3">نمونه کدهای فرستادن درخواست (GET LIST)</h3>
                            
                            <!-- دکمه‌های تب -->
                            <div class="flex border-b border-slate-800 gap-2 mb-4">
                                <button onclick="switchTab('tab-curl', 'btn-curl')" id="btn-curl" class="tab-btn px-4 py-2 text-xs font-bold border-b-2 border-indigo-500 text-indigo-400 focus:outline-none">cURL</button>
                                <button onclick="switchTab('tab-js', 'btn-js')" id="btn-js" class="tab-btn px-4 py-2 text-xs font-bold border-b-2 border-transparent text-slate-400 hover:text-white focus:outline-none">JavaScript</button>
                                <button onclick="switchTab('tab-wp', 'btn-wp')" id="btn-wp" class="tab-btn px-4 py-2 text-xs font-bold border-b-2 border-transparent text-slate-400 hover:text-white focus:outline-none">وردپرس (WordPress PHP)</button>
                            </div>

                            <!-- محتوای تب‌ها -->
                            <div class="code-container p-4 rounded-xl border border-slate-800 text-left dir-ltr font-mono text-xs overflow-x-auto relative">
                                <button onclick="copyCodeContent()" class="absolute top-3 right-3 text-slate-400 hover:text-white transition-colors" title="کپی کد">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                </button>
                                
                                <div id="tab-curl" class="tab-content block">
                                    <pre id="code-curl">curl --request GET \
  --url '{{ url('/external-api/properties?per_page=10') }}' \
  --header 'Authorization: Bearer {{ $apiKey->key }}' \
  --header 'Accept: application/json'</pre>
                                </div>

                                <div id="tab-js" class="tab-content hidden">
                                    <pre id="code-js">fetch('{{ url('/external-api/properties?per_page=10') }}', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer {{ $apiKey->key }}',
    'Accept': 'application/json',
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('خطا:', error));</pre>
                                </div>

                                <div id="tab-wp" class="tab-content hidden">
                                    <pre id="code-wp">&lt;?php
/**
 * نمونه کد PHP وردپرس جهت دریافت و ایجاد پست تایپ ملک در وردپرس
 */
$api_url = '{{ url('/external-api/properties') }}';
$api_key = '{{ $apiKey->key }}';

// اضافه کردن توکن به انتهای آدرس جهت دور زدن محدودیت‌های هاست در حذف هدر Authorization
$request_url = add_query_arg('api', $api_key, $api_url);

$response = wp_remote_get($request_url, [
    'headers' => [
        'Accept' => 'application/json',
    ],
    'timeout' => 15
]);

if (is_wp_error($response)) {
    error_log('خطا در فراخوانی وب‌سرویس املاک: ' . $response->get_error_message());
    return;
}

$body = wp_remote_retrieve_body($response);
$result = json_decode($body, true);

if (isset($result['success']) && $result['success'] === true) {
    $properties = $result['data'];
    
    foreach ($properties as $property) {
        // برای مثال ایجاد یک پست تایپ سفارشی در وردپرس
        $post_data = [
            'post_title'    => sanitize_text_field($property['title']),
            'post_content'  => wp_kses_post($property['description'] ?? ''),
            'post_status'   => 'publish',
            'post_type'     => 'properties', // نام پست تایپ ملک در وردپرس شما
            'meta_input'    => [
                '_crm_property_id'    => $property['id'],
                '_property_code'      => $property['code'],
                '_property_price'     => $property['price'],
                '_property_area'      => $property['area'],
                '_property_address'   => $property['address'],
                '_property_slug'      => $property['slug'],
                '_property_crm_url'   => $property['crm_url'],
            ]
        ];

        // چک کردن برای جلوگیری از ایجاد مجدد پست‌های تکراری
        $existing_post = get_posts([
            'post_type'  => 'properties',
            'meta_key'   => '_crm_property_id',
            'meta_value' => $property['id'],
            'fields'     => 'ids'
        ]);

        if (empty($existing_post)) {
            $post_id = wp_insert_post($post_data);
            
            // دانلود تصویر کاور و تنظیم به عنوان تصویر شاخص
            if (!empty($property['cover_image_url'])) {
                // کد مربوط به دانلود تصویر شاخص و الصاق به $post_id
            }
        }
    }
}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- متد ۲: جزئیات یک ملک -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-slate-800 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <span class="px-2.5 py-1 bg-emerald-950 border border-emerald-800 text-emerald-400 text-xs font-bold rounded-lg font-mono">GET</span>
                            <h2 class="inline-block text-base font-bold text-white mr-3">جزئیات کامل یک ملک</h2>
                        </div>
                        <span class="font-mono text-xs text-indigo-400 bg-slate-950 px-3 py-1.5 rounded-lg select-all">
                            {{ url('/external-api/properties/{id_or_code_or_slug}') }}
                        </span>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-sm text-slate-300 leading-relaxed">
                            دریافت مشخصات کامل یک ملک با شناسه عددی (ID)، کد ملک (مثال: P-1001) یا اسلاگ ملک.
                        </p>
                        
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-850 space-y-2 text-xs font-mono">
                            <div>مثال با شناسه: <span class="text-indigo-400">{{ url('/external-api/properties/42') }}</span></div>
                            <div>مثال با کد ملک: <span class="text-indigo-400">{{ url('/external-api/properties/P-1001') }}</span></div>
                            <div>مثال با اسلاگ: <span class="text-indigo-400">{{ url('/external-api/properties/20260621100000-P-1001') }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- ساختار خروجی JSON (Schema) -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl space-y-4">
                    <h2 class="text-base font-bold text-white">نمونه ساختار پاسخ وب‌سرویس (Response JSON Schema)</h2>
                    <p class="text-sm text-slate-300">
                        در صورت ارسال درخواست موفق، داده‌ها در شیء `data` بازگردانده می‌شوند.
                    </p>
                    
                    <div class="code-container p-4 rounded-xl border border-slate-800 text-left dir-ltr font-mono text-xs overflow-x-auto">
                        <pre>{
  "success": true,
  "data": [
    {
      "id": 42,
      "title": "آپارتمان لوکس ۳ خوابه زعفرانیه",
      "code": "P-1001",
      "slug": "20260621123045-P-1001",
      "crm_url": "{{ url('/properties/20260621123045-P-1001') }}",
      "listing_type": "sale",
      "property_type": "apartment",
      "usage_type": "residential",
      "document_type": "tak_barg",
      "document_type_label": "سند تک برگ",
      "publication_status": "published",
      "area": 185,
      "price": "22500000000",
      "min_price": "21000000000",
      "deposit_price": "0",
      "rent_price": "0",
      "advance_price": "0",
      "is_convertible": false,
      "convertible_with": null,
      "address": "تهران، زعفرانیه، خیابان آصف، پلاک ۱۲",
      "latitude": 35.804251,
      "longitude": 51.412458,
      "delivery_date": "2026-09-01",
      "registered_at": "2026-06-21",
      "status": {
        "id": 1,
        "key": "new",
        "label": "جدید",
        "color": "#10b981"
      },
      "category": {
        "id": 3,
        "name": "مسکونی",
        "slug": "residential",
        "color": "#3b82f6"
      },
      "building": {
        "id": 2,
        "name": "برج تندیس زعفرانیه",
        "address": "آصف، کوچه دهم",
        "floors_count": 12,
        "units_count": 24,
        "construction_year": 1402
      },
      "cover_image_url": "{{ url('storage/uploads/properties/cover_1.jpg') }}",
      "images": [
        {
          "url": "{{ url('storage/uploads/properties/gallery_1.jpg') }}",
          "sort_order": 1
        },
        {
          "url": "{{ url('storage/uploads/properties/gallery_2.jpg') }}",
          "sort_order": 2
        }
      ],
      "video": null,
      "attributes": {
        "details": [
          {
            "id": 5,
            "name": "تعداد اتاق خواب",
            "type": "number",
            "value": "3"
          },
          {
            "id": 8,
            "name": "جهت ساختمان",
            "type": "select",
            "value": "شمالی"
          }
        ],
        "features": [
          {
            "id": 12,
            "name": "آسانسور",
            "value": "1"
          },
          {
            "id": 15,
            "name": "پارکینگ سندی",
            "value": "1"
          }
        ]
      },
      "meta": {
        "is_special": true,
        "floor": 4
      },
      "created_at": "2026-06-21T12:30:45+03:30",
      "updated_at": "2026-06-21T12:30:45+03:30"
      @if($apiKey->permissions['include_owner'] ?? false),
      "owner": {
        "first_name": "علی",
        "last_name": "کریمی",
        "phone": "09123456789"
      }
      @endif
      @if($apiKey->permissions['include_confidential_notes'] ?? false),
      "confidential_notes": "مالک فروشنده فوری است و تخفیف پای معامله می‌دهد."
      @endif
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 10,
    "total": 38
  }
}</pre>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="border-t border-slate-800 bg-slate-900/30 py-6 text-center text-xs text-slate-500 mt-12">
        <p>پنل مدیریت ارتباط با مشتریان CRM - بخش توسعه‌دهندگان</p>
    </footer>

    <!-- کدهای کمکی JS تب‌ها و کپی -->
    <script>
        function switchTab(tabId, btnId) {
            // پنهان کردن تمام محتواها
            document.querySelectorAll('.tab-content').forEach(el => el.classList.replace('block', 'hidden'));
            // غیرفعال کردن دکمه‌ها
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.replace('border-indigo-500', 'border-transparent'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.replace('text-indigo-400', 'text-slate-400'));
            
            // نمایش تب فعال
            document.getElementById(tabId).classList.replace('hidden', 'block');
            // فعال کردن دکمه فعال
            const activeBtn = document.getElementById(btnId);
            activeBtn.classList.replace('border-transparent', 'border-indigo-500');
            activeBtn.classList.replace('text-slate-400', 'text-indigo-400');
            activeBtn.classList.add('text-white');
        }

        function copyToClipboard(inputId, buttonId) {
            const copyText = document.getElementById(inputId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            const btn = document.getElementById(buttonId);
            const originalHTML = btn.innerHTML;
            
            btn.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }

        function copyCodeContent() {
            // پیدا کردن تب فعال
            const activeContent = document.querySelector('.tab-content:not(.hidden) pre');
            if (!activeContent) return;

            navigator.clipboard.writeText(activeContent.textContent);
            alert('کد نمونه کپی شد!');
        }
    </script>
</body>
</html>
