@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-lg">
                    {{ $contract->contract_number }}
                </span>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $contract->title }}</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">صادر شده برای: {{ $contract->client->full_name ?? ($contract->contractable->patient_name ?? 'بیمار') }}</p>
            </div>
            
            <div class="flex flex-wrap gap-2 justify-end">
                <a href="{{ route('user.contracts.print', $contract->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-250 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    نسخه چاپی
                </a>
                <a href="{{ route('user.contracts.pdf', $contract->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    دانلود PDF
                </a>
                
                @if($contract->status !== 'signed' && $contract->status !== 'cancelled')
                    <form action="{{ route('user.contracts.sign', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                            ثبت امضا
                        </button>
                    </form>
                    
                    <form action="{{ route('user.contracts.cancel', $contract->id) }}" method="POST" class="inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-medium hover:bg-rose-700 transition-all duration-200">
                            لغو قرارداد
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl border border-rose-200 dark:border-rose-800 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left: Contract Content -->
            <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-8 min-h-[600px] relative overflow-hidden">
                <!-- Watermark for cancelled or draft status -->
                @if($contract->status === 'cancelled')
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04] select-none rotate-45">
                        <span class="text-9xl font-black text-rose-600 tracking-widest">لغو شده</span>
                    </div>
                @elseif($contract->status === 'draft')
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04] select-none rotate-45">
                        <span class="text-9xl font-black text-gray-500 tracking-widest">پیش نویس</span>
                    </div>
                @endif

                <div class="prose max-w-none dark:prose-invert">
                    {!! $contract->rendered_body !!}
                </div>
            </div>

            <!-- Right: Meta Panel -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">مشخصات سند</h2>

                    <div class="space-y-3 divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-500">وضعیت فعلی:</span>
                            <span class="font-bold">
                                @if($contract->status === 'signed')
                                    <span class="text-emerald-600 dark:text-emerald-450">امضا شده</span>
                                @elseif($contract->status === 'cancelled')
                                    <span class="text-rose-600">لغو شده</span>
                                @elseif($contract->status === 'active')
                                    <span class="text-indigo-600">در انتظار امضا</span>
                                @else
                                    <span class="text-amber-600">پیش‌نویس</span>
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-500">موجودیت مرتبط:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                @if($contract->contractable_type === 'Modules\Booking\App\Models\TreatmentPlan')
                                    طرح درمان #{{ $contract->contractable_id }}
                                @else
                                    {{ class_basename($contract->contractable_type) }} #{{ $contract->contractable_id }}
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-500">تاریخ صدور:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $contract->created_at->format('Y/m/d H:i') }}</span>
                        </div>

                        @if($contract->signed_at)
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-500">تاریخ امضا:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-mono">{{ $contract->signed_at->format('Y/m/d H:i') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-500">کاربر صادرکننده:</span>
                            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $contract->user->name ?? '-' }}</span>
                        </div>
                    </div>
                    
                    @if($contract->contractable_type === 'Modules\Booking\App\Models\TreatmentPlan')
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('user.booking.cure.index') }}?plan_id={{ $contract->contractable_id }}" class="w-full text-center inline-block px-4 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                                مشاهده طرح درمان مرتبط
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
