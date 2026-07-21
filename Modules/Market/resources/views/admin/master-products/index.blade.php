@extends('layouts.user')

@php
    $title = 'کاتالوگ محصولات (Master)';
@endphp

@section('content')
    @php
        $canManageCatalog = auth()->user()->hasAnyRole(['super-admin', 'admin']) || 
            (\Modules\Market\Entities\MarketSetting::getValue('vendors.vendor_can_create_catalog', false) && auth()->user()->can('market.master-products.manage'));

        $canDeleteCatalog = auth()->user()->hasAnyRole(['super-admin', 'admin']) || 
            (\Modules\Market\Entities\MarketSetting::getValue('vendors.vendor_can_create_catalog', false) && auth()->user()->can('market.master-products.delete'));
    @endphp
    <div class="space-y-4" x-data="{ selectedIds: [], allChecked: false, bulkAction: '' }">
        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">مدیریت کاتالوگ محصولات</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">محصولات مرجع که فروشندگان می‌توانند روی آن‌ها قیمت‌گذاری کنند.</p>
            </div>
            <div class="flex items-center gap-3">
                @if($canManageCatalog)
                    <a href="{{ route('user.market.master-products.import') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-sm font-medium transition-all shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        ایمپورت CSV کاتالوگ
                    </a>
                    <a href="{{ route('user.market.master-products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        ثبت محصول جدید
                    </a>
                @endif
            </div>
        </div>

        {{-- فیلترهای پیشرفته --}}
        <form method="GET" action="{{ route('user.market.master-products.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $separateCategoryEnabled ? 'lg:grid-cols-5 md:grid-cols-3' : 'md:grid-cols-4' }} gap-4">
                {{-- جستجوی متنی --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">جستجوی کالا</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="عنوان، کد CRM یا بارکد..." class="w-full h-10 pl-3 pr-10 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none text-gray-950 dark:text-white">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- فیلتر برند --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">برند</label>
                    <select name="brand_id" class="w-full h-10 px-3 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none text-gray-950 dark:text-white">
                        <option value="">همه برندها</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فیلتر دسته‌بندی اصلی --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">دسته‌بندی اصلی</label>
                    <select name="category_id" class="w-full h-10 px-3 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none text-gray-950 dark:text-white">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" data-brand-id="{{ $category->brand_id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($separateCategoryEnabled)
                    {{-- فیلتر دسته‌بندی فروشگاه (مجزا) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">دسته‌بندی فروشگاه</label>
                        <select name="display_category_id" class="w-full h-10 px-3 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none text-gray-950 dark:text-white">
                            <option value="">همه دسته‌بندی‌های فروشگاه</option>
                            @foreach($displayCategories as $dCat)
                                <option value="{{ $dCat->id }}" {{ request('display_category_id') == $dCat->id ? 'selected' : '' }}>{{ $dCat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- فیلتر وضعیت --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">وضعیت</label>
                    <select name="status" class="w-full h-10 px-3 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none text-gray-950 dark:text-white">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>آرشیو شده</option>
                    </select>
                </div>
            </div>

            {{-- دکمه‌ها --}}
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                @if(request()->anyFilled(['search', 'category_id', 'display_category_id', 'brand_id', 'status']))
                    <a href="{{ route('user.market.master-products.index') }}" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition-colors">حذف فیلترها</a>
                @endif
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    اعمال فیلتر
                </button>
            </div>
        </form>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const brandSelect = document.querySelector('select[name="brand_id"]');
                const categorySelect = document.querySelector('select[name="category_id"]');
                if (brandSelect && categorySelect) {
                    const originalOptions = Array.from(categorySelect.options);

                    function filterCategories() {
                        const selectedBrandId = brandSelect.value;
                        const currentSelectedValue = categorySelect.value;
                        
                        // Clear options
                        categorySelect.innerHTML = '';
                        
                        // Add back matching options
                        originalOptions.forEach(option => {
                            const optionBrandId = option.getAttribute('data-brand-id');
                            // Show generic options (no brand id or empty value option) or matching brand options
                            if (!selectedBrandId || !optionBrandId || optionBrandId === selectedBrandId || option.value === '') {
                                categorySelect.appendChild(option);
                            }
                        });

                        // Keep previous value if it is still available in the filtered list
                        if (Array.from(categorySelect.options).some(opt => opt.value === currentSelectedValue)) {
                            categorySelect.value = currentSelectedValue;
                        } else {
                            categorySelect.value = '';
                        }
                    }

                    brandSelect.addEventListener('change', filterCategories);
                    // Run once on load in case a brand is already selected from query parameters
                    filterCategories();
                }
            });
        </script>
        @endpush

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" x-model="allChecked" @change="selectedIds = allChecked ? [{{ implode(',', $products->pluck('id')->toArray()) }}] : []" class="rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 dark:focus:ring-offset-gray-900">
                        </th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">کد هوشمند (CRM)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام محصول</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">برند و دسته</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors" :class="selectedIds.includes({{ $product->id }}) ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : ''">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" value="{{ $product->id }}" x-model="selectedIds" class="rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 dark:focus:ring-offset-gray-900">
                            </td>
                            <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $product->crm_code }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $product->title }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <span class="block text-gray-800 dark:text-gray-300">{{ optional($product->brand)->name ?? '-' }}</span>
                                <span>{{ optional($product->category)->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($product->status === 'active')
                                    <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 text-xs font-semibold">فعال</span>
                                @elseif($product->status === 'draft')
                                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-750 dark:text-gray-300 text-xs font-semibold">پیش‌نویس</span>
                                @elseif($product->status === 'archived')
                                    <span class="px-2 py-1 rounded-md bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 text-xs font-semibold">آرشیو شده</span>
                                @else
                                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-semibold">{{ $product->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    @if($canManageCatalog)
                                        <a href="{{ route('user.market.master-products.edit', $product) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                        <form action="{{ route('user.market.master-products.destroy', $product) }}" method="POST" onsubmit="return confirm('حذف شود؟');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-500">هیچ محصولی در کاتالوگ یافت نشد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages()) <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $products->links() }}</div> @endif
        </div>

        {{-- نوتیفیکیشن / کادر شناور عملیات گروهی --}}
        <div x-show="selectedIds.length > 0"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700 px-6 py-4 rounded-3xl shadow-2xl flex flex-col md:flex-row items-center gap-4 transition-all"
             style="display: none;">
             
             <div class="flex items-center gap-2">
                 <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                 <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                     <span x-text="selectedIds.length" class="text-indigo-600 dark:text-indigo-400 font-extrabold mx-0.5"></span> محصول انتخاب شده
                 </span>
             </div>

             <div class="h-4 w-px bg-gray-200 dark:bg-gray-700 hidden md:block"></div>

             <form action="{{ route('user.market.master-products.bulk') }}" method="POST" class="flex flex-wrap items-center gap-3">
                 @csrf
                 {{-- پاس دادن آیدی‌های انتخاب شده به صورت فیلد پنهان --}}
                 <template x-for="id in selectedIds">
                     <input type="hidden" name="product_ids[]" :value="id">
                 </template>

                 <div class="flex items-center gap-2">
                     <select name="action" x-model="bulkAction" class="h-9 px-3 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-gray-800 dark:text-gray-200 font-bold" required>
                         <option value="">-- انتخاب عملیات --</option>
                         <option value="status">تغییر وضعیت به...</option>
                         @if($canDeleteCatalog)
                             <option value="delete">حذف گروهی</option>
                         @endif
                     </select>

                     <select name="status" x-show="bulkAction === 'status'" class="h-9 px-3 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-gray-800 dark:text-gray-200 font-bold" style="display: none;">
                         <option value="active">فعال</option>
                         <option value="draft">پیش‌نویس</option>
                         <option value="archived">آرشیو شده</option>
                     </select>
                 </div>

                 <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all active:scale-95 shadow-md shadow-indigo-500/20">
                     اعمال عملیات
                 </button>
             </form>
        </div>
    </div>
@endsection
