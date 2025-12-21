@extends('layouts.user')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";

    $inputClass = "w-full h-10 rounded-xl border-gray-200 bg-gray-50 px-3 text-sm text-gray-900
                   focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                   transition-all
                   dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-100 dark:focus:bg-gray-700 dark:focus:border-indigo-400 dark:placeholder-gray-400
                   text-center dir-ltr disabled:opacity-50 disabled:cursor-not-allowed";

    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 text-center";

    $dayNames = [
        0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه',
        3 => 'سه‌شنبه', 4 => 'چهارشنبه', 5 => 'پنج‌شنبه', 6 => 'جمعه',
    ];
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    برنامه زمانی ارائه‌دهنده
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $provider->name }}</span>
                    @if($provider->email)
                        <svg class="w-3 h-3 text-gray-300 hidden sm:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">{{ $provider->email }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('user.booking.providers.index') }}"
                   class="flex-1 sm:flex-none justify-center px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    بازگشت
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- اینکلود دیت پیکر برای انتخاب ساعت --}}
        @includeIf('partials.jalali-date-picker')

        {{-- راهنما --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <p>در این بخش می‌توانید برنامه زمانی اختصاصی این ارائه‌دهنده را برای هر روز هفته تنظیم کنید.</p>
                <p class="text-xs opacity-80">اگر برای یک روز هیچ مقداری ثبت نکنید، آن روز از <span class="font-bold border-b border-blue-300 dark:border-blue-500">برنامه زمانی سراسری و سرویس</span> پیروی می‌کند.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('user.booking.providers.availability.update', $provider) }}" class="space-y-6 pb-24">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                @for($d = 0; $d <= 6; $d++)
                    @php
                        $r = $rules[$d] ?? null;
                        $isClosedValue = old('rules.'.$d.'.is_closed', $r ? (($r->is_closed ?? false) ? '1' : '0') : '0');

                        $start    = old('rules.'.$d.'.work_start_local', $r?->work_start_local);
                        $end      = old('rules.'.$d.'.work_end_local', $r?->work_end_local);
                        $dur      = old('rules.'.$d.'.slot_duration_minutes', $r?->slot_duration_minutes);
                        $capSlot  = old('rules.'.$d.'.capacity_per_slot', $r?->capacity_per_slot);
                        $capDay   = old('rules.'.$d.'.capacity_per_day', $r?->capacity_per_day);

                        $breaksArray = old('rules.'.$d.'.breaks');
                        if (is_null($breaksArray) && is_array($r?->breaks_json)) {
                            $breaksArray = $r->breaks_json;
                        }

                        $startValue = $start ? substr((string) $start, 0, 5) : null;
                        $endValue = $end ? substr((string) $end, 0, 5) : null;

                        if (is_array($breaksArray)) {
                            foreach ($breaksArray as $breakIndex => $breakItem) {
                                if (! is_array($breakItem)) {
                                    continue;
                                }

                                if (array_key_exists('start_local', $breakItem)) {
                                    $breaksArray[$breakIndex]['start_local'] = $breakItem['start_local']
                                        ? substr((string) $breakItem['start_local'], 0, 5)
                                        : null;
                                }

                                if (array_key_exists('end_local', $breakItem)) {
                                    $breaksArray[$breakIndex]['end_local'] = $breakItem['end_local']
                                        ? substr((string) $breakItem['end_local'], 0, 5)
                                        : null;
                                }
                            }
                        }
                    @endphp

                    <div class="{{ $cardClass }}"
                         x-data="availabilityDay(@json($breaksArray ?? []), '{{ $isClosedValue }}')">

                        {{-- هدر کارت روز --}}
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 flex items-center justify-center rounded-xl font-bold text-sm transition-colors
                                            {{ $isClosedValue == '1'
                                                ? 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                                                : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400' }}">
                                    {{ $d + 1 }}
                                </span>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $dayNames[$d] ?? ('روز '.$d) }}</h3>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">تنظیم ساعات کاری</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="hidden sm:block text-xs font-medium px-2 py-1 rounded-lg transition-colors"
                                     :class="isClosed == '0' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                                    <span x-text="isClosed == '0' ? 'فعال' : 'تعطیل'"></span>
                                </div>

                                <div class="relative">
                                    <input type="hidden" name="rules[{{ $d }}][weekday]" value="{{ $d }}">
                                    <select name="rules[{{ $d }}][is_closed]"
                                            x-model="isClosed"
                                            class="h-9 pl-3 pr-8 rounded-xl border-gray-300 bg-white text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 cursor-pointer shadow-sm appearance-none hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                        <option value="0">باز (فعال)</option>
                                        <option value="1">تعطیل</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2 text-gray-500 dark:text-gray-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- محتوای کارت --}}
                        <div x-show="isClosed == '0'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="p-4 sm:p-6 space-y-6">

                            {{-- ردیف تنظیمات اصلی --}}
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">شروع کار</label>
                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_start_local]"
                                           class="{{ $inputClass }}" value="{{ $startValue }}" placeholder="08:00">
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">پایان کار</label>
                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_end_local]"
                                           class="{{ $inputClass }}" value="{{ $endValue }}" placeholder="17:00">
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">مدت اسلات (دقیقه)</label>
                                    <input type="number" name="rules[{{ $d }}][slot_duration_minutes]"
                                           class="{{ $inputClass }}" min="5" step="5" placeholder="30" value="{{ $dur }}">
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">ظرفیت اسلات</label>
                                    <input type="number" name="rules[{{ $d }}][capacity_per_slot]"
                                           class="{{ $inputClass }}" min="1" placeholder="1" value="{{ $capSlot }}">
                                </div>

                                <div class="col-span-2 md:col-span-1">
                                    <label class="{{ $labelClass }}">ظرفیت روز (اختیاری)</label>
                                    <input type="number" name="rules[{{ $d }}][capacity_per_day]"
                                           class="{{ $inputClass }}" min="0" placeholder="20" value="{{ $capDay }}">
                                </div>
                            </div>

                            {{-- بخش استراحت‌ها --}}
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-5 mt-2">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                        <div class="p-1 rounded bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        زمان‌های استراحت (Break Times)
                                    </label>
                                    <button type="button" @click="addBreak()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold hover:bg-indigo-100 transition-colors border border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-800 dark:hover:bg-indigo-900/40">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        افزودن
                                    </button>
                                </div>

                                <div class="space-y-3" x-show="breaks.length > 0" x-transition>
                                    <template x-for="(br, index) in breaks" :key="index">
                                        <div class="flex flex-wrap items-center gap-3 bg-gray-50 dark:bg-gray-700/30 p-3 rounded-xl border border-gray-100 dark:border-gray-700 animate-in fade-in slide-in-from-right-2">
                                            <div class="flex-1 min-w-[100px] flex items-center gap-2">
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">شروع:</span>
                                                <input type="text" data-jdp-only-time
                                                       class="{{ $inputClass }} !bg-white dark:!bg-gray-800 h-9"
                                                       x-model="br.start_local"
                                                       :name="`rules[{{ $d }}][breaks][${index}][start_local]`"
                                                       placeholder="--:--">
                                            </div>

                                            <div class="flex-1 min-w-[100px] flex items-center gap-2">
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">پایان:</span>
                                                <input type="text" data-jdp-only-time
                                                       class="{{ $inputClass }} !bg-white dark:!bg-gray-800 h-9"
                                                       x-model="br.end_local"
                                                       :name="`rules[{{ $d }}][breaks][${index}][end_local]`"
                                                       placeholder="--:--">
                                            </div>

                                            <button type="button" @click="removeBreak(index)"
                                                    class="p-2 text-red-500 bg-white dark:bg-gray-800 hover:bg-red-50 hover:text-red-700 border border-gray-200 dark:border-gray-600 rounded-lg transition-colors dark:hover:bg-red-900/20 dark:hover:text-red-300 dark:hover:border-red-900/30 shadow-sm"
                                                    title="حذف بازه">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="breaks.length === 0" class="text-center py-4 text-xs text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/30 dark:bg-gray-800/30">
                                    هنوز زمان استراحتی برای این روز تعریف نشده است.
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="fixed bottom-6 left-0 right-0 z-40 flex justify-center pointer-events-none px-4">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl pointer-events-auto max-w-sm w-full flex justify-between items-center gap-4 animate-in slide-in-from-bottom-6">
                    <span class="text-xs text-gray-500 dark:text-gray-400 mr-2 hidden sm:inline">تغییرات را ذخیره کنید</span>
                    <button type="submit"
                            class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره برنامه زمانی ارائه‌دهنده
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function availabilityDay(initialBreaks, initialClosed) {
            return {
                isClosed: initialClosed,
                breaks: Array.isArray(initialBreaks) ? initialBreaks.map(b => ({
                    start_local: b.start_local || '',
                    end_local: b.end_local || '',
                })) : [],

                addBreak() {
                    this.breaks.push({ start_local: '', end_local: '' });
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', hasSecond: false });
                        }
                    });
                },

                removeBreak(index) {
                    this.breaks.splice(index, 1);
                },
            };
        }
    </script>
@endsection
