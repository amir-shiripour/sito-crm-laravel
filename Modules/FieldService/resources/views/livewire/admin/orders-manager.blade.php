<div class="space-y-6 font-sans">
    {{-- Header Banner & Stats --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        📋
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 dark:text-white">مدیریت سفارش‌ها و ماموریت‌های فنی</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">پایش زنده چرخه اعزام، ثبت سفارشات تلفنی، رادار هوشمند و فاکتورهای امانی</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-200 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>ثبت سفارش جدید (سفارش تلفنی)</span>
                </button>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">کل سفارش‌ها</div>
                    <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ number_format($totalOrdersCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    📦
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">در حال جستجو و رادار</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($searchingCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    📡
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">ماموریت‌های در جریان</div>
                    <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ number_format($inProgressCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                    🔧
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">انجام‌شده و تسویه</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($completedCount) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✅
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-80 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی شماره سفارش، نام مشتری، تلفن، شهر..." class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">وضعیت:</span>
                <select wire:model.live="statusFilter" class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="searching">رادار جستجو</option>
                    <option value="dispatched">اعزام شده به تکنسین</option>
                    <option value="accepted">تایید تکنسین</option>
                    <option value="en_route">در مسیر</option>
                    <option value="arrived">حضور در محل</option>
                    <option value="in_progress">در حال عیب‌یابی / تعمیر</option>
                    <option value="invoice_pending">در انتظار پرداخت فاکتور</option>
                    <option value="completed">پایان یافته</option>
                    <option value="cancelled">لغو شده</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">فوریت:</span>
                <select wire:model.live="priorityFilter" class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all">همه</option>
                    <option value="normal">عادی</option>
                    <option value="emergency">🚨 اعزام فوری اضطراری</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-750 border-b border-slate-200/70 dark:border-slate-700/60 text-slate-500 dark:text-slate-400 font-bold">
                        <th class="py-3.5 px-4">کد و زمان سفارش</th>
                        <th class="py-3.5 px-4">مشتری (CRM Client)</th>
                        <th class="py-3.5 px-4">دستگاه و رسته</th>
                        <th class="py-3.5 px-4">موقعیت و شهر</th>
                        <th class="py-3.5 px-4">تکنسین مامور</th>
                        <th class="py-3.5 px-4">وضعیت</th>
                        <th class="py-3.5 px-4">مبلغ فاکتور</th>
                        <th class="py-3.5 px-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-750/60 transition-colors">
                        {{-- Order Code & Time --}}
                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                            <div class="flex items-center gap-1.5">
                                <span class="font-sans tracking-wide text-indigo-600 dark:text-indigo-400 font-black">{{ $ord->order_number }}</span>
                                @if($ord->is_emergency)
                                <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 text-[10px] font-black animate-pulse">فوری</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ function_exists('jdate') ? jdate($ord->created_at)->format('Y/m/d H:i') : $ord->created_at->format('Y-m-d H:i') }}
                            </div>
                        </td>

                        {{-- Client CRM --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                @if($ord->client)
                                <a href="{{ route('user.clients.show', $ord->client_id) }}" target="_blank" class="hover:text-indigo-600 hover:underline">
                                    {{ $ord->customer_name }}
                                </a>
                                @else
                                <span>{{ $ord->customer_name }}</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 dir-ltr text-right mt-0.5">{{ $ord->customer_phone }}</div>
                        </td>

                        {{-- Appliance & Category --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <span>{{ $ord->appliance_type?->icon() }}</span>
                                <span>{{ $ord->appliance_type?->label() }}</span>
                                @if($ord->appliance_brand_model)
                                <span class="text-slate-400 font-normal">({{ $ord->appliance_brand_model }})</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5" title="{{ $ord->problem_description }}">
                                {{ $ord->problem_description }}
                            </div>
                        </td>

                        {{-- Location --}}
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-700 dark:text-slate-300">{{ $ord->province }}، {{ $ord->city }}</div>
                            <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">شعاع: {{ $ord->search_radius_km }} کیلومتر</div>
                        </td>

                        {{-- Assigned Tech --}}
                        <td class="py-3.5 px-4">
                            @if($ord->technician)
                            <div class="font-bold text-indigo-600 dark:text-indigo-400">{{ $ord->technician->full_name }}</div>
                            <div class="text-[10px] text-slate-400 dir-ltr text-right">{{ $ord->technician->mobile }}</div>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                <span>در جستجوی تکنسین</span>
                            </span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $ord->status->badgeClass() }}">
                                {{ $ord->status->label() }}
                            </span>
                        </td>

                        {{-- Invoice / Amount --}}
                        <td class="py-3.5 px-4">
                            @if($ord->invoice)
                            <div class="font-black text-slate-900 dark:text-white">{{ number_format($ord->invoice->total_amount / 10) }} تومان</div>
                            <span class="text-[10px] px-2 py-0.5 rounded-md mt-0.5 inline-block {{ $ord->invoice->payment_status->badgeClass() }}">
                                {{ $ord->invoice->payment_status->label() }}
                            </span>
                            @else
                            <span class="text-slate-400 text-[11px]">فاکتور صادر نشده</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- View Details --}}
                                <button type="button" wire:click="openOrderDetail({{ $ord->id }})" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition" title="مشاهده جزئیات سفارش">
                                    👁️
                                </button>

                                {{-- Re-dispatch Radar --}}
                                @if(in_array($ord->status->value, ['searching', 'dispatched']))
                                <button type="button" wire:click="reDispatch({{ $ord->id }})" class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 transition" title="ارسال مجدد در رادار">
                                    📡
                                </button>
                                @endif

                                {{-- Cancel --}}
                                @if($ord->status->value !== 'cancelled' && $ord->status->value !== 'completed')
                                <button type="button" wire:click="cancelOrder({{ $ord->id }})" wire:confirm="آیا از لغو این سفارش اطمینان دارید؟" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition" title="لغو سفارش">
                                    ✕
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center text-slate-400 space-y-2">
                            <div class="text-3xl">📋</div>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">هیچ سفارشی مطابق جستجو یافت نشد</div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">می‌توانید با کلیک بر روی دکمه «ثبت سفارش جدید»، سفارش تلفنی مشتری را وارد کنید.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-700/60">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: Operator New Order Creation (Phone Orders)                         --}}
    {{-- ========================================================================= --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl border border-slate-200 dark:border-slate-700 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        📞
                    </div>
                    <div>
                        <h3 class="font-black text-base text-slate-900 dark:text-white">ثبت سفارش تلفنی جدید توسط اپراتور</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">ثبت درخواست مشتری تماس‌گیرنده و اعزام هوشمند تکنسین در استان</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">✕</button>
            </div>

            <div class="space-y-4 max-h-[75vh] overflow-y-auto pr-1">
                {{-- Client CRM Selector --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>👤</span>
                            <span>انتخاب یا ایجاد مشتری در CRM *</span>
                        </label>
                        <button type="button" wire:click="$toggle('isCreatingNewClient')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $isCreatingNewClient ? 'یا انتخاب مشتری موجود' : '+ ثبت سریع مشتری جدید' }}
                        </button>
                    </div>

                    @if($isCreatingNewClient)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">نام و نام خانوادگی مشتری</label>
                            <input type="text" wire:model="newClientName" placeholder="مثال: رضا احمدی" class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            @error('newClientName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">شماره همراه مشتری</label>
                            <input type="text" wire:model="newClientPhone" placeholder="0911xxxxxxx" dir="ltr" class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            @error('newClientPhone') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @else
                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model.live.debounce.300ms="clientSearch" placeholder="نام یا شماره موبایل مشتری CRM را جستجو کنید..." class="flex-1 h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            @if($selectedClientId)
                            <button type="button" wire:click="clearClientSelection" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-xs font-bold rounded-xl">حذف انتخاب</button>
                            @endif
                        </div>
                        @error('clientSearch') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror

                        @if($clientSuggestions->isNotEmpty())
                        <div class="absolute z-20 top-11 right-0 left-0 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-600">
                            @foreach($clientSuggestions as $suggestion)
                            <button type="button" wire:click="selectClient({{ $suggestion->id }})" class="w-full p-2.5 text-right hover:bg-slate-50 dark:hover:bg-slate-600/50 flex items-center justify-between text-xs transition">
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $suggestion->full_name }}</span>
                                <span class="text-slate-400 dir-ltr">{{ $suggestion->phone ?? 'بدون شماره' }}</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Service & Appliance --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رسته تخصصی خدمت *</label>
                        <select wire:model.live="category_id" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">برند و مدل دستگاه</label>
                        <input type="text" wire:model="appliance_brand_model" placeholder="مثال: پکیج دیواری بوتان مدل پارما" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شرح عیب یا مشکل اعلامی توسط کارفرما *</label>
                    <textarea wire:model="problem_description" rows="2" placeholder="توضیح کوتاه درباره خرابی یا خدمات درخواستی..." class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100"></textarea>
                    @error('problem_description') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                </div>

                {{-- Location & Province/City --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">استان *</label>
                        <select wire:model.live="province" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            @foreach($provinces as $prov)
                            <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شهر *</label>
                        <select wire:model="city" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            @foreach($cities as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">آدرس دقیق محل اعزام *</label>
                    <input type="text" wire:model="address" placeholder="بابل، خیابان شریعتی، کوچه بهار، پلاک..." class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                    @error('address') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                </div>

                {{-- Priority & B2B Options --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">فوریت سفارش</label>
                        <select wire:model="priority" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            <option value="normal">عادی (طی روز جاری)</option>
                            <option value="emergency">🚨 اعزام فوری اضطراری (زیر ۴۵ دقیقه)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اتصال به مجتمع (B2B)</label>
                        <select wire:model="building_id" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            <option value="">بدون اتصال به مجتمع</option>
                            @foreach($buildings as $bld)
                            <option value="{{ $bld->id }}">{{ $bld->name }} ({{ $bld->city }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">فروشگاه همکار معرف (پورسانت ۵٪)</label>
                        <select wire:model="vendor_id" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            <option value="">بدون معرف فروشگاهی</option>
                            @foreach($vendors as $vnd)
                            <option value="{{ $vnd->id }}">{{ $vnd->store_name }} ({{ $vnd->referral_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dispatch Mode --}}
                <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 space-y-3">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">شیوه اعزام تکنسین *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2.5 {{ $dispatchMode === 'radar' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200' }}">
                            <input type="radio" wire:model.live="dispatchMode" value="radar" class="hidden">
                            <span class="text-base">📡</span>
                            <div>
                                <div class="text-xs font-bold">رادار اعزام خودکار</div>
                                <div class="text-[10px] opacity-80">ارسال هوشمند به نزدیک‌ترین تکنسین‌های آزاد</div>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2.5 {{ $dispatchMode === 'manual' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200' }}">
                            <input type="radio" wire:model.live="dispatchMode" value="manual" class="hidden">
                            <span class="text-base">👤</span>
                            <div>
                                <div class="text-xs font-bold">تخصیص مستقیم دستی</div>
                                <div class="text-[10px] opacity-80">انتخاب تکنسین خاص برای این سفارش</div>
                            </div>
                        </label>
                    </div>

                    @if($dispatchMode === 'manual')
                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">انتخاب تکنسین برای تخصیص مستقیم:</label>
                        <select wire:model="manualTechnicianId" class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            <option value="">-- انتخاب تکنسین --</option>
                            @foreach($allTechs as $tech)
                            <option value="{{ $tech->id }}">
                                {{ $tech->full_name }} ({{ $tech->city }}) - {{ $tech->is_online ? 'آنلاین ✅' : 'آفلاین' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                <button type="button" wire:click="$set('showCreateModal', false)" class="w-1/3 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    انصراف
                </button>
                <button type="button" wire:click="saveNewOrder" class="w-2/3 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/20 transition active:scale-95">
                    ثبت نهایی سفارش و شروع اعزام
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- DRAWER: Order Details (Slide-over drawer from right)                       --}}
    {{-- ========================================================================= --}}
    @if($showDetailDrawer && $viewingOrder)
    <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex justify-end">
        <div class="w-full max-w-xl bg-white dark:bg-slate-800 h-full shadow-2xl border-r border-slate-200 dark:border-slate-700 flex flex-col justify-between overflow-hidden animate-slide-left">
            {{-- Drawer Header --}}
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-black text-slate-900 dark:text-white">سفارش #{{ $viewingOrder->order_number }}</h2>
                        <span class="px-2.5 py-0.5 rounded-xl text-xs font-bold {{ $viewingOrder->status->badgeClass() }}">
                            {{ $viewingOrder->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        ثبت شده در: {{ function_exists('jdate') ? jdate($viewingOrder->created_at)->format('Y/m/d H:i') : $viewingOrder->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>

                <button type="button" wire:click="closeDetailDrawer" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">✕</button>
            </div>

            {{-- Drawer Content --}}
            <div class="p-6 overflow-y-auto space-y-5 flex-1 text-xs">
                {{-- Client Details Card --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/70 dark:border-slate-700/50 space-y-2.5">
                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center justify-between">
                        <span>👤 اطلاعات مشتری</span>
                        @if($viewingOrder->client_id)
                        <a href="{{ route('user.clients.show', $viewingOrder->client_id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline text-[11px]">
                            پروفایل کلاینت CRM ←
                        </a>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-slate-600 dark:text-slate-300">
                        <div>نام: <span class="font-bold text-slate-900 dark:text-white">{{ $viewingOrder->customer_name }}</span></div>
                        <div class="dir-ltr text-right">تلفن: <a href="tel:{{ $viewingOrder->customer_phone }}" class="text-indigo-600 font-bold hover:underline">{{ $viewingOrder->customer_phone }}</a></div>
                    </div>
                    <div class="text-slate-600 dark:text-slate-300">
                        📍 آدرس: {{ $viewingOrder->province }}، {{ $viewingOrder->city }} - {{ $viewingOrder->address }}
                    </div>
                </div>

                {{-- Appliance & Problem Card --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/70 dark:border-slate-700/50 space-y-2.5">
                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center justify-between">
                        <span>🛠️ مشخصات دستگاه و عیب</span>
                        <span class="text-[11px] font-semibold text-indigo-600">{{ $viewingOrder->appliance_type?->label() }}</span>
                    </div>
                    @if($viewingOrder->appliance_brand_model)
                    <div class="text-slate-600 dark:text-slate-300">برند و مدل: <span class="font-bold text-slate-900 dark:text-white">{{ $viewingOrder->appliance_brand_model }}</span></div>
                    @endif
                    <div class="p-3 rounded-xl bg-white dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 leading-relaxed">
                        {{ $viewingOrder->problem_description }}
                    </div>
                </div>

                {{-- Assigned Technician Card --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/70 dark:border-slate-700/50 space-y-2.5">
                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center justify-between">
                        <span>🔧 تکنسین مامور</span>
                        @if($viewingOrder->technician)
                        <span class="text-emerald-600 font-bold">تخصیص‌یافته</span>
                        @else
                        <span class="text-amber-600 font-bold">بدون تکنسین</span>
                        @endif
                    </div>
                    @if($viewingOrder->technician)
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $viewingOrder->technician->full_name }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $viewingOrder->technician->city }} • امتیاز: {{ $viewingOrder->technician->rating_avg }}★</div>
                        </div>
                        <a href="tel:{{ $viewingOrder->technician->mobile }}" class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 font-bold">
                            تماس با تکنسین
                        </a>
                    </div>
                    @else
                    <div class="space-y-2">
                        <p class="text-slate-400 text-[11px]">این سفارش هنوز به تکنسین متصل نشده است. می‌توانید مجدداً در رادار منتشر کنید یا دستی تخصیص دهید.</p>
                        <button type="button" wire:click="reDispatch({{ $viewingOrder->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold">
                            📡 انتشار مجدد در رادار
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Digital Invoice & Financial Breakdown --}}
                @if($viewingOrder->invoice)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/70 dark:border-slate-700/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 dark:text-slate-200">💳 فاکتور تفکیکی دیجیتال</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $viewingOrder->invoice->payment_status->badgeClass() }}">
                            {{ $viewingOrder->invoice->payment_status->label() }}
                        </span>
                    </div>

                    <div class="space-y-1.5 text-slate-600 dark:text-slate-300 divide-y divide-slate-100 dark:divide-slate-700/40">
                        @foreach($viewingOrder->invoice->items as $it)
                        <div class="flex items-center justify-between pt-1">
                            <span>{{ $it->title }} ({{ $it->quantity }}x)</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ number_format($it->total_price / 10) }} تومان</span>
                        </div>
                        @endforeach
                        <div class="flex items-center justify-between pt-1">
                            <span>هزینه ایاب و ذهاب:</span>
                            <span>{{ number_format($viewingOrder->invoice->travel_fee / 10) }} تومان</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 text-sm font-black text-slate-900 dark:text-white">
                            <span>مبلغ کل:</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($viewingOrder->invoice->total_amount / 10) }} تومان</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Change Order Status Quick Control --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/70 dark:border-slate-700/50 space-y-2">
                    <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs">تغییر وضعیت مستقیم سفارش:</label>
                    <select wire:change="updateOrderStatus({{ $viewingOrder->id }}, $event.target.value)" class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                        @foreach(\Modules\FieldService\App\Enums\OrderStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ $viewingOrder->status === $st ? 'selected' : '' }}>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                <button type="button" wire:click="closeDetailDrawer" class="flex-1 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs">
                    بستن
                </button>
                @if($viewingOrder->status->value !== 'cancelled' && $viewingOrder->status->value !== 'completed')
                <button type="button" wire:click="cancelOrder({{ $viewingOrder->id }})" wire:confirm="آیا از لغو این سفارش اطمینان دارید؟" class="py-2.5 px-4 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400 font-bold text-xs">
                    لغو سفارش
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
