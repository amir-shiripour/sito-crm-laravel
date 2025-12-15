@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">روزهای خاص / استثناهای سرویس</h1>
                <p class="text-sm text-gray-500 mt-1">
                    سرویس: <span class="font-semibold">{{ $service->name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('user.booking.services.edit', $service) }}"
                   class="text-blue-600 hover:underline">
                    تنظیمات سرویس
                </a>
                <span class="text-gray-400">|</span>
                <a href="{{ route('user.booking.services.availability.edit', $service) }}"
                   class="text-blue-600 hover:underline">
                    برنامه هفتگی
                </a>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- توضیح --}}
        <div class="bg-white rounded border p-4 text-sm text-gray-600 space-y-1">
            <p>در این بخش می‌توانید برای تاریخ‌های خاص، برنامه زمانی سرویس را تغییر دهید یا آن روز را به‌طور کامل تعطیل کنید.</p>
            <p>تاریخ در فرانت شمسی است (jalaliDatepicker) ولی در دیتابیس به‌صورت تاریخ استاندارد ذخیره می‌شود.</p>
        </div>

        {{-- فرم ایجاد / بروزرسانی استثنا --}}
        <form method="POST"
              action="{{ route('user.booking.services.exceptions.store', $service) }}"
              class="bg-white rounded border p-4 space-y-4"
              x-data="exceptionForm()">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- تاریخ --}}
                <div>
                    <label class="block text-xs mb-1">تاریخ (شمسی)</label>
                    <input type="text"
                           name="local_date"
                           class="w-full border rounded p-2 text-sm"
                           placeholder="مثلاً 1403/10/01"
                           data-jdp
                           value="{{ old('local_date') }}">
                    @error('local_date')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    <p class="text-[11px] text-gray-500 mt-1">
                        jalaliDatepicker مقدار معادل میلادی (Y-m-d) را در value فیلد قرار می‌دهد.
                    </p>
                </div>

                {{-- تعطیل --}}
                <div>
                    <label class="block text-xs mb-1">این روز کاملاً تعطیل باشد؟</label>
                    <select name="is_closed"
                            class="w-full border rounded p-2 text-sm"
                            x-model="isClosed">
                        <option value="0" @selected(old('is_closed')==='0')>خیر</option>
                        <option value="1" @selected(old('is_closed')==='1')>بله، رزرو ممکن نباشد</option>
                    </select>
                    @error('is_closed')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- ظرفیت روز --}}
                <div>
                    <label class="block text-xs mb-1">ظرفیت روزانه (اختیاری)</label>
                    <input type="number"
                           name="override_capacity_per_day"
                           class="w-full border rounded p-2 text-sm"
                           min="0"
                           placeholder="مثلاً 10"
                           value="{{ old('override_capacity_per_day') }}">
                    @error('override_capacity_per_day')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- پنجره‌های کاری در آن روز --}}
            <div class="mt-4 border-t pt-4" x-show="isClosed == 0">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold">ساعات کاری در این روز (می‌تواند با برنامه عادی فرق کند)</h2>
                    <button type="button"
                            class="px-2 py-1 text-xs rounded bg-gray-100 hover:bg-gray-200"
                            @click="addWindow()">
                        + افزودن بازه کاری
                    </button>
                </div>

                <div class="space-y-2" x-show="workWindows.length > 0">
                    <template x-for="(w, index) in workWindows" :key="'w-'+index">
                        <div class="flex flex-col sm:flex-row gap-2 items-center">
                            <div class="flex-1">
                                <input type="time"
                                       class="w-full border rounded p-2 text-xs"
                                       x-model="w.start_local"
                                       :name="`override_work_windows[${index}][start_local]`">
                            </div>
                            <span class="text-xs text-gray-400">تا</span>
                            <div class="flex-1">
                                <input type="time"
                                       class="w-full border rounded p-2 text-xs"
                                       x-model="w.end_local"
                                       :name="`override_work_windows[${index}][end_local]`">
                            </div>
                            <button type="button"
                                    class="text-xs text-red-600 hover:underline"
                                    @click="removeWindow(index)">
                                حذف
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-[11px] text-gray-500 mt-1">
                    اگر هیچ بازه‌ای ثبت نشود، از برنامه عادی سرویس / Global استفاده می‌شود.
                </p>
            </div>

            {{-- استراحت‌ها --}}
            <div class="mt-4 border-t pt-4" x-show="isClosed == 0">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold">استراحت‌ها در این روز</h2>
                    <button type="button"
                            class="px-2 py-1 text-xs rounded bg-gray-100 hover:bg-gray-200"
                            @click="addBreak()">
                        + افزودن استراحت
                    </button>
                </div>

                <div class="space-y-2" x-show="breaks.length > 0">
                    <template x-for="(br, index) in breaks" :key="'b-'+index">
                        <div class="flex flex-col sm:flex-row gap-2 items-center">
                            <div class="flex-1">
                                <input type="time"
                                       class="w-full border rounded p-2 text-xs"
                                       x-model="br.start_local"
                                       :name="`override_breaks[${index}][start_local]`">
                            </div>
                            <span class="text-xs text-gray-400">تا</span>
                            <div class="flex-1">
                                <input type="time"
                                       class="w-full border rounded p-2 text-xs"
                                       x-model="br.end_local"
                                       :name="`override_breaks[${index}][end_local]`">
                            </div>
                            <button type="button"
                                    class="text-xs text-red-600 hover:underline"
                                    @click="removeBreak(index)">
                                حذف
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-[11px] text-gray-500 mt-1">
                    مثال: 12:30 تا 13:00 برای ناهار. می‌توانید چند استراحت در این روز ثبت کنید.
                </p>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">
                    اگر این روز «تعطیل» نباشد و همه فیلدها را خالی بگذارید، استثنا حذف شده و از برنامه عادی استفاده می‌شود.
                </p>
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    ذخیره استثنا
                </button>
            </div>
        </form>

        {{-- لیست استثناهای ثبت‌شده --}}
        <div class="bg-white rounded border p-4 space-y-3">
            <h2 class="text-sm font-semibold mb-2">لیست استثناهای این سرویس</h2>

            <div class="border rounded overflow-hidden">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-right">تاریخ</th>
                        <th class="p-2 text-right">وضعیت</th>
                        <th class="p-2 text-right">بازه‌های کاری</th>
                        <th class="p-2 text-right">استراحت‌ها</th>
                        <th class="p-2 text-right">ظرفیت روزانه</th>
                        <th class="p-2 text-right">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($exceptions as $ex)
                        @php
                            $windows = is_array($ex->override_work_windows_json) ? $ex->override_work_windows_json : [];
                            $breaks  = is_array($ex->override_breaks_json) ? $ex->override_breaks_json : [];
                        @endphp
                        <tr class="border-t">
                            <td class="p-2">{{ $ex->local_date }}</td>
                            <td class="p-2">
                                @if($ex->is_closed)
                                    <span class="px-2 py-0.5 rounded bg-red-50 text-red-700 text-[11px]">تعطیل</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[11px]">باز با تنظیمات خاص</span>
                                @endif
                            </td>
                            <td class="p-2">
                                @if(count($windows))
                                    @foreach($windows as $w)
                                        <div>{{ $w['start_local'] ?? '?' }} تا {{ $w['end_local'] ?? '?' }}</div>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">از برنامه عادی</span>
                                @endif
                            </td>
                            <td class="p-2">
                                @if(count($breaks))
                                    @foreach($breaks as $b)
                                        <div>{{ $b['start_local'] ?? '?' }} تا {{ $b['end_local'] ?? '?' }}</div>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">بدون استراحت خاص</span>
                                @endif
                            </td>
                            <td class="p-2">
                                {{ $ex->override_capacity_per_day ?? '---' }}
                            </td>
                            <td class="p-2">
                                <form method="POST"
                                      action="{{ route('user.booking.services.exceptions.destroy', [$service, $ex]) }}"
                                      onsubmit="return confirm('استثنای این تاریخ حذف شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 text-[11px] rounded bg-red-50 text-red-700 hover:bg-red-100">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-3 text-center text-gray-500">
                                هنوز استثنایی برای این سرویس ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $exceptions->links() }}
        </div>
    </div>

    <script>
        function exceptionForm() {
            return {
                isClosed: '{{ old('is_closed', '0') }}',
                workWindows: [],
                breaks: [],
                addWindow() {
                    this.workWindows.push({start_local: '', end_local: ''});
                },
                removeWindow(i) {
                    this.workWindows.splice(i, 1);
                },
                addBreak() {
                    this.breaks.push({start_local: '', end_local: ''});
                },
                removeBreak(i) {
                    this.breaks.splice(i, 1);
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (window.jalaliDatepicker) {
                jalaliDatepicker.startWatch();
            }
        });
    </script>
@endsection
