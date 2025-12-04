{{-- modules/ClientCalls/resources/views/user/calls/index.blade.php --}}
@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $title = 'تماس‌های ' . ($client->full_name ?: $client->username);

    // نقشه وضعیت‌ها برای استفاده هم در فیلد انتخاب و هم در نمایش
    $statuses = [
        'planned'   => 'برنامه‌ریزی شده',
        'done'      => 'انجام شده',
        'failed'    => 'ناموفق',
        'canceled'  => 'لغو شده',
    ];
@endphp

{{-- اسکریپت و استایل جلالی دیت‌پیکر (اگه تعریف شده) --}}
@includeIf('partials.jalali-date-picker')

@section('content')
    <div class="space-y-4">

        {{-- هدر --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    تاریخچه تماس با {{ $client->full_name ?: $client->username }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت و مشاهده تاریخچه تماس‌های ثبت شده برای این {{ config('clients.labels.singular', 'مشتری') }}.
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('user.clients.show', $client) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>بازگشت به پروفایل</span>
                </a>

                @can('client-calls.create')
                    <a href="{{ route('user.clients.calls.create', $client) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ثبت تماس جدید</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- ایجاد سریع تماس --}}
        @can('client-calls.create')
            @php
                $quickDefaultDate = old(
                    'call_date_jalali',
                    Jalalian::fromDateTime(now())->format('Y/m/d')
                );
                $quickStatus = old('status', 'done');
            @endphp

            <div
                x-data="{ openQuick: {{ $errors->has('call_date_jalali') || $errors->has('call_time') || $errors->has('reason') || $errors->has('result') || $errors->has('status') ? 'true' : 'false' }} }"
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                <button type="button"
                        @click="openQuick = !openQuick"
                        class="flex items-center justify-between w-full text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span>ایجاد سریع تماس جدید</span>
                    <svg class="w-4 h-4 transform transition-transform"
                         :class="openQuick ? 'rotate-90' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7h-2v10h2m0-4h-2"/>
                    </svg>
                </button>

                <div x-show="openQuick" x-cloak class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                    <form method="POST" action="{{ route('user.clients.calls.store', $client) }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- تاریخ تماس (شمسی) --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    تاریخ تماس <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="call_date_jalali"
                                    data-jdp-only-date
                                    value="{{ $quickDefaultDate }}"
                                    required
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 placeholder-gray-400
                                           focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20
                                           dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                >
                                @error('call_date_jalali')
                                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- زمان تماس --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    زمان تماس <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="call_time"
                                    data-jdp-only-time
                                    value="{{ old('call_time') }}"
                                    required
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 placeholder-gray-400
                                       focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20
                                       dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                />

                                @error('call_time')
                                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- وضعیت تماس --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    وضعیت تماس <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="status"
                                    required
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                           focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20
                                           dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                >
                                    @foreach($statuses as $key => $label)
                                        <option
                                            value="{{ $key }}" @selected($quickStatus === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- علت تماس --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    علت تماس <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="reason"
                                    value="{{ old('reason') }}"
                                    required
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 placeholder-gray-400
                                           focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20
                                           dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                >
                                @error('reason')
                                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- نتیجه تماس --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    نتیجه تماس <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="result"
                                    value="{{ old('result') }}"
                                    required
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 placeholder-gray-400
                                           focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20
                                           dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                >
                                @error('result')
                                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700 shadow-sm shadow-emerald-500/30">
                                ذخیره سریع تماس
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- جدول تماس‌ها --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">زمان</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">علت تماس</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نتیجه</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ثبت‌کننده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">وضعیت /
                            عملیات
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($calls as $call)
                        @php
                            $statusKey = $call->status ?? 'unknown';

                            switch ($statusKey) {
                                case 'planned':
                                    $statusLabel = 'برنامه‌ریزی شده';
                                    $statusBadgeClass = 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700';
                                    break;
                                case 'done':
                                    $statusLabel = 'انجام شده';
                                    $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700';
                                    break;
                                case 'failed':
                                    $statusLabel = 'ناموفق';
                                    $statusBadgeClass = 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/40 dark:text-red-200 dark:border-red-700';
                                    break;
                                case 'canceled':
                                    $statusLabel = 'لغو شده';
                                    $statusBadgeClass = 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600';
                                    break;
                                default:
                                    $statusLabel = $statusKey ?: 'نامشخص';
                                    $statusBadgeClass = 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600';
                                    break;
                            }
                        @endphp

                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            {{-- ID --}}
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 font-mono text-xs">
                                {{ $call->id }}
                            </td>

                            {{-- تاریخ (شمسی) --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">
                                @if($call->call_date)
                                    <span class="dir-ltr">
                                        {{ Jalalian::fromDateTime($call->call_date)->format('Y/m/d') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- زمان --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 dir-ltr">
                                {{ $call->call_time ? $call->call_time->format('H:i') : '—' }}
                            </td>


                            {{-- علت تماس --}}
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-100">
                                {{ $call->reason ?: '—' }}
                            </td>

                            {{-- نتیجه --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">
                                {{ $call->result ?: '—' }}
                            </td>

                            {{-- ثبت‌کننده --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs">
                                    {{ optional($call->user)->name ?: '—' }}
                                </span>
                            </td>

                            {{-- وضعیت / عملیات --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                        {{ $statusLabel }}
                                    </span>

                                    <div
                                        class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                        @can('client-calls.edit')
                                            <a href="{{ route('user.clients.calls.edit', [$client, $call]) }}"
                                               class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                                               title="ویرایش تماس">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('client-calls.delete')
                                            <form action="{{ route('user.clients.calls.destroy', [$client, $call]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('آیا از حذف این تماس اطمینان دارید؟');"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        title="حذف تماس">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M18 8a6 6 0 10-12 0v4a6 6 0 0012 0V8z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M13.73 21a2 2 0 01-3.46 0"/>
                                    </svg>
                                    <p class="text-base font-medium">هنوز تماسی ثبت نشده است</p>
                                    <p class="text-sm mt-1">
                                        می‌توانید اولین تماس را برای
                                        این {{ config('clients.labels.singular', 'مشتری') }} ثبت کنید.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                {{ $calls->links() }}
            </div>
        </div>
    </div>
@endsection
