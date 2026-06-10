@extends('layouts.user')

@php
    $title = 'مدیریت سفارشات فروشگاه';
@endphp

@section('content')
    <div class="space-y-6 text-right" dir="rtl">
        
        {{-- Header & Create Button --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت سفارشات فروشگاه</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">پیگیری، تحلیل مالی، ثبت دستی و مدیریت کامل سفارشات خریداران.</p>
            </div>
            <div>
                <a href="{{ route('user.market.orders.create') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-md transition-all hover:shadow-lg text-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    ثبت سفارش دستی جدید
                </a>
            </div>
        </div>

        {{-- Statistics Section --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Revenue Card --}}
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 p-5 rounded-3xl border border-emerald-100 dark:border-emerald-900/30 flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">مجموع درآمد تایید شده</span>
                    <h3 class="text-2xl font-black text-emerald-900 dark:text-emerald-300">{{ number_format($stats['total_revenue']) }} <span class="text-xs font-normal">ریال</span></h3>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm text-emerald-600 dark:text-emerald-400">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Total Count Card --}}
            <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/20 dark:to-blue-950/20 p-5 rounded-3xl border border-indigo-100 dark:border-indigo-900/30 flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">کل سفارشات ثبت شده</span>
                    <h3 class="text-2xl font-black text-indigo-900 dark:text-indigo-300">{{ number_format($stats['total_count']) }} <span class="text-xs font-normal">سفارش</span></h3>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm text-indigo-600 dark:text-indigo-400">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
            </div>

            {{-- Paid Orders Card --}}
            <div class="bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-950/20 dark:to-sky-950/20 p-5 rounded-3xl border border-blue-100 dark:border-blue-900/30 flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">سفارشات پرداخت شده</span>
                    <h3 class="text-2xl font-black text-blue-900 dark:text-blue-300">{{ number_format($stats['paid_count']) }} <span class="text-xs font-normal">سفارش</span></h3>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm text-blue-600 dark:text-blue-400">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Unpaid Orders Card --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 p-5 rounded-3xl border border-amber-100 dark:border-amber-900/30 flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">در انتظار پرداخت</span>
                    <h3 class="text-2xl font-black text-amber-900 dark:text-amber-300">{{ number_format($stats['unpaid_count']) }} <span class="text-xs font-normal">سفارش</span></h3>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm text-amber-600 dark:text-amber-400">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Advanced Filters Bar --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200">
            <form action="{{ route('user.market.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                {{-- Search Box --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">جستجو در سفارشات</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="شماره سفارش یا نام مشتری..." 
                               class="w-full pl-3 pr-9 py-2.5 rounded-xl text-xs bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Payment Status Filter --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">وضعیت پرداخت</label>
                    <select name="payment_status" class="w-full px-3 py-2.5 rounded-xl text-xs bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>در انتظار پرداخت</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>پرداخت شده</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>پرداخت ناموفق</option>
                    </select>
                </div>

                {{-- Delivery Status Filter --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">وضعیت سفارش</label>
                    <select name="market_order_status_id" class="w-full px-3 py-2.5 rounded-xl text-xs bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                        <option value="">همه وضعیت‌ها</option>
                        @php
                            $isAdmin = auth()->user() && auth()->user()->hasRole(['super-admin', 'admin']);
                            $filterStatusesQuery = \Modules\Market\App\Models\MarketOrderStatus::where('is_active', true);
                            if (!$isAdmin) {
                                $filterStatusesQuery->where('show_to_client', true);
                            }
                            $filterStatuses = $filterStatusesQuery->orderBy('sort_order', 'asc')->get();
                        @endphp
                        @foreach($filterStatuses as $s)
                            <option value="{{ $s->id }}" {{ request('market_order_status_id') == $s->id ? 'selected' : '' }}>
                                {{ $isAdmin ? $s->admin_label : $s->client_label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm hover:shadow-md transition-all">
                        اعمال فیلتر
                    </button>
                    @if(request()->anyFilled(['search', 'payment_status', 'market_order_status_id']))
                        <a href="{{ route('user.market.orders.index') }}" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-xl text-xs transition-colors">
                            حذف فیلترها
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs">
                        <tr>
                            <th class="px-6 py-4 font-bold">شماره سفارش</th>
                            <th class="px-6 py-4 font-bold">خریدار</th>
                            <th class="px-6 py-4 font-bold">مبلغ فاکتور</th>
                            <th class="px-6 py-4 font-bold">وضعیت پرداخت</th>
                            <th class="px-6 py-4 font-bold">وضعیت سفارش</th>
                            <th class="px-6 py-4 font-bold">تاریخ ثبت</th>
                            <th class="px-6 py-4 font-bold text-left pl-8">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($orders as $order)
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-all duration-150">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/30 px-2.5 py-1.5 rounded-xl border border-indigo-100 dark:border-indigo-900/20">
                                        #ORD-{{ $order->id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ optional($order->client)->full_name ?: 'کاربر ناشناس' }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ optional($order->client)->phone ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-gray-900 dark:text-white">{{ number_format($order->grand_total) }}</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">ریال</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->payment_status === 'paid')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 text-xs font-bold border border-emerald-100 dark:border-emerald-900/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                                            پرداخت شده
                                        </span>
                                    @elseif($order->payment_status === 'failed')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 text-xs font-bold border border-rose-100 dark:border-rose-900/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 dark:bg-rose-400"></span>
                                            پرداخت ناموفق
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 text-xs font-bold border border-amber-100 dark:border-amber-900/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                                            در انتظار پرداخت
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $isAdmin = auth()->user() && auth()->user()->hasRole(['super-admin', 'admin']);
                                        $status = $isAdmin ? $order->status : $order->client_status;
                                        if ($status) {
                                            $label = $isAdmin ? $status->admin_label : $status->client_label;
                                            $class = $status->color_class;
                                        } else {
                                            $label = 'نامشخص';
                                            $class = 'bg-gray-50 text-gray-700 border border-gray-100';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold border {{ str_replace('bg-', 'border-', $class) }} {{ $class }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ \Morilog\Jalali\Jalalian::fromDateTime($order->created_at)->format('Y/m/d') }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ \Morilog\Jalali\Jalalian::fromDateTime($order->created_at)->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- View Details --}}
                                        <a href="{{ route('user.market.orders.show', $order) }}" class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/30 transition-all" title="مشاهده جزئیات">
                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        {{-- Edit Order --}}
                                        <a href="{{ route('user.market.orders.edit', $order) }}" class="p-2 rounded-xl text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30 transition-all" title="ویرایش سفارش">
                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        {{-- Delete Order --}}
                                        <form action="{{ route('user.market.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('آیا از حذف این سفارش و بازگشت اقلام به انبار اطمینان دارید؟');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30 transition-all" title="حذف سفارش">
                                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-bold">هیچ سفارشی ثبت نشده است</p>
                                        <p class="text-xs mt-1 text-gray-400">می‌توانید با دکمه بالا یک سفارش دستی جدید ایجاد کنید.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
