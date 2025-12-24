@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        {{-- Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">روزهای خاص / استثناهای سرویس</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    سرویس: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $service->name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('user.booking.services.edit', $service) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    تنظیمات سرویس
                </a>
                <a href="{{ route('user.booking.services.availability.edit', $service) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-900/50 transition">
                    برنامه هفتگی
                </a>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        {{-- توضیح --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-sm text-gray-700 dark:text-gray-300 space-y-1">
            <p>در این بخش می‌توانید برای تاریخ‌های خاص، برنامه زمانی سرویس را تغییر دهید یا آن روز را به‌طور کامل تعطیل
                کنید.</p>
            <p>تاریخ در فرانت شمسی است (jalaliDatepicker) ولی در دیتابیس به‌صورت تاریخ استاندارد ذخیره می‌شود.</p>
        </div>

        {{-- فرم ایجاد / بروزرسانی استثنا --}}
        <form method="POST" action="{{ route('user.booking.services.exceptions.store', $service) }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4"
              x-data="exceptionForm()">
            @csrf

            @php
                $inputClass = 'w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3
                py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                transition';
                $labelClass = 'block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1';
                $selectClass = $inputClass . ' appearance-none cursor-pointer';
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- تاریخ --}}
                <div>
                    <label class="{{ $labelClass }}">تاریخ (شمسی)</label>
                    <input type="text" name="local_date" class="{{ $inputClass }}" placeholder="مثلاً 1403/10/01" data-jdp
                           value="{{ old('local_date') }}">
                    @error('local_date')<div class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        jalaliDatepicker مقدار معادل میلادی (Y-m-d) را در value فیلد قرار می‌دهد.
                    </p>
                </div>

                {{-- تعطیل --}}
                <div>
                    <label class="{{ $labelClass }}">این روز کاملاً تعطیل باشد؟</label>
                    <select name="is_closed" class="{{ $selectClass }}" x-model="isClosed">
                        <option value="0" @selected(old('is_closed')==='0' )>خیر</option>
                        <option value="1" @selected(old('is_closed')==='1' )>بله، رزرو ممکن نباشد</option>
                    </select>
                    @error('is_closed')<div class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ظرفیت روز --}}
                <div>
                    <label class="{{ $labelClass }}">ظرفیت روزانه (اختیاری)</label>
                    <input type="number" name="override_capacity_per_day" class="{{ $inputClass }}" min="0"
                           placeholder="مثلاً 10" value="{{ old('override_capacity_per_day') }}">
                    @error('override_capacity_per_day')<div class="text-rose-600 dark:text-rose-400 text-xs mt-1">
                        {{ $message }}</div>@enderror
                </div>
            </div>

            {{-- پنجره‌های کاری در آن روز --}}
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4" x-show="isClosed == 0">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">ساعات کاری در این روز (می‌تواند با
                        برنامه عادی فرق کند)</h2>
                    <button type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-900/50 transition"
                            @click="addWindow()">
                        + افزودن بازه کاری
                    </button>
                </div>

                <div class="space-y-2" x-show="workWindows.length > 0">
                    <template x-for="(w, index) in workWindows" :key="'w-'+index">
                        <div
                            class="flex flex-col sm:flex-row gap-2 items-center bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="flex-1">
                                <input type="time" class="{{ $inputClass }}" x-model="w.start_local"
                                       :name="`override_work_windows[${index}][start_local]`">
                            </div>
                            <span class="text-xs text-gray-400">تا</span>
                            <div class="flex-1">
                                <input type="time" class="{{ $inputClass }}" x-model="w.end_local"
                                       :name="`override_work_windows[${index}][end_local]`">
                            </div>
                            <button type="button" class="text-xs text-rose-600 dark:text-rose-300 hover:underline"
                                    @click="removeWindow(index)">
                                حذف
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                    اگر هیچ بازه‌ای ثبت نشود، از برنامه عادی سرویس / Global استفاده می‌شود.
                </p>
            </div>

            {{-- استراحت‌ها --}}
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4" x-show="isClosed == 0">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">استراحت‌ها در این روز</h2>
                    <button type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 transition"
                            @click="addBreak()">
                        + افزودن استراحت
                    </button>
                </div>

                <div class="space-y-2" x-show="breaks.length > 0">
                    <template x-for="(br, index) in breaks" :key="'b-'+index">
                        <div
                            class="flex flex-col sm:flex-row gap-2 items-center bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="flex-1">
                                <input type="time" class="{{ $inputClass }}" x-model="br.start_local"
                                       :name="`override_breaks[${index}][start_local]`">
                            </div>
                            <span class="text-xs text-gray-400">تا</span>
                            <div class="flex-1">
                                <input type="time" class="{{ $inputClass }}" x-model="br.end_local"
                                       :name="`override_breaks[${index}][end_local]`">
                            </div>
                            <button type="button" class="text-xs text-rose-600 dark:text-rose-300 hover:underline"
                                    @click="removeBreak(index)">
                                حذف
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                    مثال: 12:30 تا 13:00 برای ناهار. می‌توانید چند استراحت در این روز ثبت کنید.
                </p>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    اگر این روز «تعطیل» نباشد و همه فیلدها را خالی بگذارید، استثنا حذف شده و از برنامه عادی استفاده می‌شود.
                </p>
                <button
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ذخیره استثنا
                </button>
            </div>
        </form>

        {{-- لیست استثناهای ثبت‌شده --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">لیست استثناهای این سرویس</h2>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-right">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300">تاریخ</th>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300">بازه‌های کاری</th>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300">استراحت‌ها</th>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300">ظرفیت روزانه</th>
                            <th class="p-2 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($exceptions as $ex)
                            @php
                                $windows = is_array($ex->override_work_windows_json) ? $ex->override_work_windows_json : [];
                                $breaks = is_array($ex->override_breaks_json) ? $ex->override_breaks_json : [];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="p-2 text-gray-800 dark:text-gray-200">{{ $ex->local_date }}</td>
                                <td class="p-2">
                                    @if($ex->is_closed)
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200 text-[11px]">تعطیل</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 text-[11px]">باز
                                    با تنظیمات خاص</span>
                                    @endif
                                </td>
                                <td class="p-2 text-gray-700 dark:text-gray-200">
                                    @if(count($windows))
                                        @foreach($windows as $w)
                                            <div>{{ $w['start_local'] ?? '?' }} تا {{ $w['end_local'] ?? '?' }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">از برنامه عادی</span>
                                    @endif
                                </td>
                                <td class="p-2 text-gray-700 dark:text-gray-200">
                                    @if(count($breaks))
                                        @foreach($breaks as $b)
                                            <div>{{ $b['start_local'] ?? '?' }} تا {{ $b['end_local'] ?? '?' }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">بدون استراحت خاص</span>
                                    @endif
                                </td>
                                <td class="p-2 text-gray-800 dark:text-gray-100">
                                    {{ $ex->override_capacity_per_day ?? '---' }}
                                </td>
                                <td class="p-2 text-left">
                                    <form method="POST"
                                          action="{{ route('user.booking.services.exceptions.destroy', [$service, $ex]) }}"
                                          onsubmit="return confirm('استثنای این تاریخ حذف شود؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="px-3 py-1 text-[11px] rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-900/50 transition">
                                            حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-3 text-center text-gray-500 dark:text-gray-400">
                                    هنوز استثنایی برای این سرویس ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                {{ $exceptions->links() }}
            </div>
        </div>
    </div>

    <script>
        function exceptionForm() {
            return {
                isClosed: '{{ old('
        is_closed ', '
        0 ') }}',
                workWindows: [],
                breaks: [],
                addWindow() {
                    this.workWindows.push({
                        start_local: '',
                        end_local: ''
                    });
                },
                removeWindow(i) {
                    this.workWindows.splice(i, 1);
                },
                addBreak() {
                    this.breaks.push({
                        start_local: '',
                        end_local: ''
                    });
                },
                removeBreak(i) {
                    this.breaks.splice(i, 1);
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.jalaliDatepicker) {
                jalaliDatepicker.startWatch();
            }
        });
    </script>
@endsection
