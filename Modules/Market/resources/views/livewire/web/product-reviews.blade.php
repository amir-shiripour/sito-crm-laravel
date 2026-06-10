<div id="product-reviews-section" class="scroll-mt-36">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
        دیدگاه کاربران
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        {{-- بخش آمار و ارقام دیدگاه‌ها --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 flex flex-col justify-between">
            <div class="text-center pb-6 border-b border-gray-100 dark:border-gray-800/80">
                <div class="text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-2">
                    {{ number_format($averageRating, 1) }}
                </div>
                <div class="flex items-center justify-center gap-1 text-amber-500 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'fill-current' : 'text-gray-200 dark:text-gray-700' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    براساس {{ $totalReviews }} نظر ثبت شده
                </div>
            </div>

            {{-- نمودار میله‌ای امتیازها --}}
            <div class="space-y-3 pt-6">
                @foreach($stats as $starValue => $data)
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 w-3">
                            {{ $starValue }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-amber-500 fill-current shrink-0" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full transition-all" style="width: {{ $data['percent'] }}%"></div>
                        </div>
                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 w-8 text-left">
                            {{ $data['percent'] }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- بخش لیست نظرات ثبت شده --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 flex flex-col gap-6 justify-between">
            <div class="flex flex-col gap-6">
                @forelse($reviews as $rev)
                    <div class="border-b border-gray-100 dark:border-gray-800/60 pb-6 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 font-bold rounded">
                                    {{ number_format($rev->rating, 1) }}
                                </span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $rev->client->full_name ?? 'خریدار سایت' }}
                                </span>
                                @if($rev->vendorProduct)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/40">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        خریدار کالا
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">
                                {{ \Morilog\Jalali\Jalalian::fromCarbon($rev->created_at)->format('%d %B %Y') }}
                            </span>
                        </div>
                        @if($rev->vendorProduct)
                            @php
                                $variant = $rev->vendorProduct->variant;
                                $rawAttrs = $variant && is_array($variant->variant_attributes) ? $variant->variant_attributes : [];
                                $attributes = [];
                                foreach ($rawAttrs as $key => $val) {
                                    $cleanVal = trim($val);
                                    $cleanKey = trim($key);
                                    if (in_array($cleanVal, ['استاندارد', 'نسخه استاندارد', 'standard', 'default', 'بدون تنوع', 'ساده'])) {
                                        continue;
                                    }
                                    if (in_array($cleanKey, ['name', 'نام']) && $cleanVal === 'استاندارد') {
                                        continue;
                                    }
                                    $dictAttr = $attributeDictionary->firstWhere('name', $key);
                                    $type = $dictAttr ? $dictAttr->type : 'select';
                                    $metaValue = null;
                                    if ($dictAttr) {
                                        $dictVal = $dictAttr->values->firstWhere('value', $val);
                                        if ($dictVal) {
                                            $metaValue = $dictVal->meta_value;
                                        }
                                    }
                                    $attributes[] = [
                                        'key' => $key,
                                        'value' => $val,
                                        'type' => $type,
                                        'meta_value' => $metaValue,
                                    ];
                                }
                                $vendorName = $showVendor ? ($rev->vendorProduct->vendor->store_name ?? null) : null;
                            @endphp
                            @if(!empty($attributes) || $vendorName)
                                <div class="flex flex-wrap items-center gap-3 mt-1.5 mb-2 text-[10px] text-gray-500 dark:text-gray-400 font-semibold">
                                    @if($vendorName)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                            خریداری شده از: <strong class="text-gray-600 dark:text-gray-300 font-bold">{{ $vendorName }}</strong>
                                        </span>
                                    @endif
                                    @if(!empty($attributes) && $vendorName)
                                        <span>•</span>
                                    @endif
                                    @if(!empty($attributes))
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span>تنوع:</span>
                                            @foreach($attributes as $attr)
                                                @if($attr['type'] === 'color')
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 text-[10px] font-bold">
                                                        @if($attr['meta_value'] && str_starts_with($attr['meta_value'], 'attributes/'))
                                                            <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3 h-3 rounded-full object-cover">
                                                        @else
                                                            <span class="w-2.5 h-2.5 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm" style="background-color: {{ $attr['meta_value'] ?? '#ccc' }}"></span>
                                                        @endif
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @elseif($attr['type'] === 'image' && $attr['meta_value'])
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 text-[10px] font-bold">
                                                        <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3 h-3 rounded object-cover">
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 bg-gray-50 dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                                        <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $attr['key'] }}:</span>
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                            {{ $rev->comment }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-xs font-bold">هنوز دیدگاهی برای این محصول ثبت نشده است. اولین نفری باشید که نظر می‌دهد!</p>
                    </div>
                @endforelse
            </div>

            @if($reviews->hasPages())
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- بخش فرم ثبت دیدگاه جدید --}}
    <div class="bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 md:p-8">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">ثبت دیدگاه جدید</h3>
        
        @if (session()->has('message'))
            <div class="mb-5 p-4 text-xs font-bold text-emerald-800 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                {{ session('message') }}
            </div>
        @endif

        @auth('client')
            <form wire:submit.prevent="submitReview" class="space-y-5">
                {{-- امتیازدهی ستاره‌ای --}}
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300">امتیاز شما به این محصول:</label>
                    <div x-data="{ rating: @entangle('rating'), hoverRating: 0 }" class="flex items-center gap-1">
                        <template x-for="star in 5">
                            <button
                                type="button"
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="w-8 h-8 text-gray-200 dark:text-gray-700 transition-colors focus:outline-none cursor-pointer"
                            >
                                <svg
                                    class="w-full h-full fill-current transition-colors"
                                    :class="{'text-amber-500': star <= (hoverRating || rating), 'text-gray-200 dark:text-gray-700': star > (hoverRating || rating)}"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    @error('rating') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- متن دیدگاه --}}
                <div class="flex flex-col gap-2">
                    <label for="comment" class="text-xs font-bold text-gray-700 dark:text-gray-300">متن دیدگاه:</label>
                    <textarea
                        id="comment"
                        wire:model.defer="comment"
                        rows="4"
                        placeholder="تجربه استفاده یا نظرات فنی خود درباره این محصول را بنویسید..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 resize-none leading-loose"
                    ></textarea>
                    @error('comment') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- دیدگاه بر اساس خرید --}}
                @if(!empty($purchasedItems))
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-5 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.live="isPurchaseBased" class="w-5 h-5 rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 cursor-pointer transition-colors">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-850 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">ثبت دیدگاه بر اساس خرید این محصول</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">ثبت تجربه خرید شما به همراه مشخصات تنوع کالا و فروشگاه آن.</span>
                            </div>
                        </label>

                        @if($isPurchaseBased)
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-800/80 animate-in fade-in duration-300">
                                @if(count($purchasedItems) > 1)
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">انتخاب فاکتور خرید:</label>
                                    <select wire:model.live="selectedVendorProductId" class="w-full rounded-xl border border-gray-200 bg-white dark:bg-gray-900 px-4 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                        @foreach($purchasedItems as $item)
                                            @php
                                                $varItem = $product->variants->firstWhere('id', \Modules\Market\Entities\VendorProduct::find($item['vendor_product_id'])->product_variant_id ?? null);
                                                $rawItemAttrs = $varItem && is_array($varItem->variant_attributes) ? $varItem->variant_attributes : [];
                                                $itemAttrs = [];
                                                foreach($rawItemAttrs as $k => $v) {
                                                    $cleanVal = trim($v);
                                                    $cleanKey = trim($k);
                                                    if (in_array($cleanVal, ['استاندارد', 'نسخه استاندارد', 'standard', 'default', 'بدون تنوع', 'ساده'])) {
                                                        continue;
                                                    }
                                                    if (in_array($cleanKey, ['name', 'نام']) && $cleanVal === 'استاندارد') {
                                                        continue;
                                                    }
                                                    $itemAttrs[] = "$k: $v";
                                                }
                                                $itemVarName = implode(', ', $itemAttrs);
                                            @endphp
                                            <option value="{{ $item['vendor_product_id'] }}">
                                                @if($itemVarName)
                                                    {{ $itemVarName }} 
                                                    @if($showVendor) (از فروشگاه: {{ $item['vendor_name'] }}) @endif
                                                @else
                                                    @if($showVendor)
                                                        خرید از فروشگاه: {{ $item['vendor_name'] }}
                                                    @else
                                                        خرید شماره {{ $loop->iteration }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    {{-- نمایش گرافیکی گزینه‌ای که در حال حاضر انتخاب شده است --}}
                                    @php
                                        $selectedItem = collect($purchasedItems)->firstWhere('vendor_product_id', $selectedVendorProductId);
                                    @endphp
                                    @if($selectedItem)
                                        @php
                                            $selectedVar = $product->variants->firstWhere('id', \Modules\Market\Entities\VendorProduct::find($selectedItem['vendor_product_id'])->product_variant_id ?? null);
                                            $selectedRawAttrs = $selectedVar && is_array($selectedVar->variant_attributes) ? $selectedVar->variant_attributes : [];
                                            $selectedAttributes = [];
                                            foreach ($selectedRawAttrs as $key => $val) {
                                                $cleanVal = trim($val);
                                                $cleanKey = trim($key);
                                                if (in_array($cleanVal, ['استاندارد', 'نسخه استاندارد', 'standard', 'default', 'بدون تنوع', 'ساده'])) {
                                                    continue;
                                                }
                                                if (in_array($cleanKey, ['name', 'نام']) && $cleanVal === 'استاندارد') {
                                                    continue;
                                                }
                                                $dictAttr = $attributeDictionary->firstWhere('name', $key);
                                                $type = $dictAttr ? $dictAttr->type : 'select';
                                                $metaValue = null;
                                                if ($dictAttr) {
                                                    $dictVal = $dictAttr->values->firstWhere('value', $val);
                                                    if($dictVal) $metaValue = $dictVal->meta_value;
                                                }
                                                $selectedAttributes[] = [
                                                    'key' => $key,
                                                    'value' => $val,
                                                    'type' => $type,
                                                    'meta_value' => $metaValue,
                                                ];
                                            }
                                        @endphp
                                        @if(!empty($selectedAttributes))
                                            <div class="mt-3 flex flex-wrap items-center gap-2 bg-white dark:bg-gray-950 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500">مشخصات تنوع انتخابی:</span>
                                                @foreach($selectedAttributes as $attr)
                                                    @if($attr['type'] === 'color')
                                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-850 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold">
                                                            @if($attr['meta_value'] && str_starts_with($attr['meta_value'], 'attributes/'))
                                                                <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3.5 h-3.5 rounded-full object-cover">
                                                            @else
                                                                <span class="w-3 h-3 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm" style="background-color: {{ $attr['meta_value'] ?? '#ccc' }}"></span>
                                                            @endif
                                                            {{ $attr['value'] }}
                                                        </span>
                                                    @elseif($attr['type'] === 'image' && $attr['meta_value'])
                                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-850 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold">
                                                            <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3.5 h-3.5 rounded object-cover">
                                                            {{ $attr['value'] }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 bg-gray-50 dark:bg-gray-850 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                                            <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $attr['key'] }}:</span>
                                                            {{ $attr['value'] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    {{-- فقط ۱ محصول خریداری شده وجود دارد --}}
                                    @php
                                        $singleItem = $purchasedItems[0];
                                        $singleVar = $product->variants->firstWhere('id', \Modules\Market\Entities\VendorProduct::find($singleItem['vendor_product_id'])->product_variant_id ?? null);
                                        $singleRawAttrs = $singleVar && is_array($singleVar->variant_attributes) ? $singleVar->variant_attributes : [];
                                        $singleAttributes = [];
                                        foreach ($singleRawAttrs as $key => $val) {
                                            $cleanVal = trim($val);
                                            $cleanKey = trim($key);
                                            if (in_array($cleanVal, ['استاندارد', 'نسخه استاندارد', 'standard', 'default', 'بدون تنوع', 'ساده'])) {
                                                continue;
                                            }
                                            if (in_array($cleanKey, ['name', 'نام']) && $cleanVal === 'استاندارد') {
                                                continue;
                                            }
                                            $dictAttr = $attributeDictionary->firstWhere('name', $key);
                                            $type = $dictAttr ? $dictAttr->type : 'select';
                                            $metaValue = null;
                                            if ($dictAttr) {
                                                $dictVal = $dictAttr->values->firstWhere('value', $val);
                                                if($dictVal) $metaValue = $dictVal->meta_value;
                                            }
                                            $singleAttributes[] = [
                                                'key' => $key,
                                                'value' => $val,
                                                'type' => $type,
                                                'meta_value' => $metaValue,
                                            ];
                                        }
                                    @endphp
                                    <div class="text-[11px] text-gray-650 dark:text-gray-400 leading-relaxed font-semibold flex flex-wrap items-center gap-2">
                                        ثبت دیدگاه برای خرید شما
                                        @if(!empty($singleAttributes))
                                            :
                                            @foreach($singleAttributes as $attr)
                                                @if($attr['type'] === 'color')
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-gray-950 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold">
                                                        @if($attr['meta_value'] && str_starts_with($attr['meta_value'], 'attributes/'))
                                                            <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3.5 h-3.5 rounded-full object-cover">
                                                        @else
                                                            <span class="w-3 h-3 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm" style="background-color: {{ $attr['meta_value'] ?? '#ccc' }}"></span>
                                                        @endif
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @elseif($attr['type'] === 'image' && $attr['meta_value'])
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-gray-950 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold">
                                                        <img src="{{ Storage::url($attr['meta_value']) }}" class="w-3.5 h-3.5 rounded object-cover">
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 bg-white dark:bg-gray-950 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-800 text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                                        <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $attr['key'] }}:</span>
                                                        {{ $attr['value'] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($showVendor)
                                            <span class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-[10px] font-bold text-gray-700 dark:text-gray-300">از فروشگاه: {{ $singleItem['vendor_name'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove>ثبت و ارسال دیدگاه</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            در حال ارسال...
                        </span>
                    </button>
                </div>
            </form>
        @else
            <div class="p-5 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 text-center flex flex-col items-center justify-center gap-3">
                <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                    برای ثبت دیدگاه ابتدا باید وارد حساب کاربری خود شوید.
                </p>
                <a href="{{ route('client.login') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:hover:bg-indigo-950/80 rounded-xl text-xs font-bold border border-indigo-100 dark:border-indigo-900/50 transition-colors">
                    ورود به حساب کاربری
                </a>
            </div>
        @endauth
    </div>
</div>
