@php
    $isReceived = $cheque->type === 'received';

    // Define status properties (color, icon, text)
    $statusInfo = match($cheque->status) {
        'passed' => [
            'color' => 'green',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'text' => 'وصول شده'
        ],
        'returned' => [
            'color' => 'red',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>',
            'text' => 'برگشت خورده'
        ],
        'cancelled' => [
            'color' => 'orange',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'text' => 'باطل شده'
        ],
        'issued' => [
            'color' => 'gray',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'text' => 'صادر شده'
        ],
        default => [ // 'registered' and any other status
            'color' => 'gray',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>',
            'text' => 'ثبت شده'
        ],
    };
@endphp

{{-- Cheque Card Container --}}
<div class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">

    {{-- Status Color Bar --}}
    <div class="h-2 bg-{{ $statusInfo['color'] }}-500"></div>

    <div class="flex-grow p-5">
        <div class="relative z-20">
            {{-- Header Section --}}
            <header class="flex items-start justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">{{ $cheque->bank_name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">شعبه {{ $cheque->branch_name }}</p>
                </div>
                {{-- Status Badge --}}
                <div class="inline-flex items-center gap-x-1.5 rounded-full bg-{{ $statusInfo['color'] }}-100 px-2 py-1 text-xs font-medium text-{{ $statusInfo['color'] }}-700 dark:bg-{{ $statusInfo['color'] }}-800/50 dark:text-{{ $statusInfo['color'] }}-300">
                    {!! $statusInfo['icon'] !!}
                    <span>{{ $statusInfo['text'] }}</span>
                </div>
            </header>

            {{-- Amount Section --}}
            <div class="my-6 text-center">
                <p class="text-4xl font-extrabold tracking-tight dir-ltr {{ $isReceived ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                    {{ number_format($cheque->amount) }}
                    <span class="text-lg font-medium text-gray-500 dark:text-gray-400">ریال</span>
                </p>
            </div>

            {{-- Details Section --}}
            <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div class="col-span-2 sm:col-span-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400">تاریخ سررسید</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ jdate($cheque->due_date)->format('Y/m/d') }}</p>
                </div>
                <div class="col-span-2 sm:col-span-1 text-left sm:text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400">شماره چک</p>
                    <p class="font-mono font-semibold tracking-wider text-gray-700 dark:text-gray-200">{{ $cheque->cheque_number }}</p>
                </div>
                @if($cheque->client)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">مشتری</p>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $cheque->client->full_name }}</p>
                    </div>
                @endif
            </div>

            {{-- Sayyad ID --}}
            @if($cheque->sayyad_id)
                <div class="mt-6 border-t border-dashed border-gray-300 pt-3 dark:border-gray-600">
                    <p class="text-center font-mono text-sm tracking-widest text-gray-500 dark:text-gray-400" dir="ltr">{{ $cheque->sayyad_id }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions Footer --}}
    <footer class="flex items-center justify-end gap-2 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
        @if(!$cheque->isReconciled())
            <a href="{{ route('admin.accounting.cheques.reconcile.form', $cheque) }}" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                تعیین وضعیت
            </a>
            <a href="{{ route('admin.accounting.cheques.edit', $cheque) }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                ویرایش
            </a>
        @else
            <form action="{{ route('admin.accounting.cheques.cancel-reconcile', $cheque) }}" method="POST" onsubmit="return confirm('آیا از لغو وصول این چک اطمینان دارید؟');">
                @csrf
                @method('PUT')
                <button type="submit" class="rounded-md bg-orange-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    لغو وصول
                </button>
            </form>
        @endif
    </footer>
</div>
