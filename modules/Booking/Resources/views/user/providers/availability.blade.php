@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">برنامه زمانی ارائه‌دهنده</h1>
                <p class="text-sm text-gray-500 mt-1">
                    کاربر: <span class="font-semibold">{{ $provider->name }}</span>
                    @if($provider->email)
                        <span class="mx-1 text-gray-400">•</span>
                        <span class="text-xs">{{ $provider->email }}</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('user.booking.providers.index') }}"
                   class="px-3 py-1.5 text-sm text-blue-600 hover:underline">
                    بازگشت به لیست ارائه‌دهندگان
                </a>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Hint --}}
        <div class="bg-white rounded border p-4 text-sm text-gray-600 space-y-1">
            <p>در این بخش می‌توانید برنامه زمانی اختصاصی این ارائه‌دهنده را برای هر روز هفته تنظیم کنید.</p>
            <p>اگر برای یک روز هیچ مقداری ثبت نکنید، آن روز از Global و برنامه زمانی سرویس پیروی می‌کند.</p>
            <p>استراحت‌ها به‌صورت چند بازه شروع / پایان برای هر روز قابل ثبت هستند.</p>
        </div>

        <form method="POST"
              action="{{ route('user.booking.providers.availability.update', $provider) }}"
              class="bg-white rounded border p-4 space-y-6">
            @csrf

            @php
                $dayNames = [
                    0 => 'شنبه',
                    1 => 'یکشنبه',
                    2 => 'دوشنبه',
                    3 => 'سه‌شنبه',
                    4 => 'چهارشنبه',
                    5 => 'پنج‌شنبه',
                    6 => 'جمعه',
                ];
            @endphp

            <div class="space-y-4">
                @for($d = 0; $d <= 6; $d++)
                    @php
                        /** @var \Modules\Booking\Entities\BookingAvailabilityRule|null $r */
                        $r = $rules[$d] ?? null;

                        $isClosed = old('rules.'.$d.'.is_closed',
                            $r ? ( ($r->is_closed ?? false) ? '1' : '0') : '0'
                        );

                        $start    = old('rules.'.$d.'.work_start_local', $r?->work_start_local);
                        $end      = old('rules.'.$d.'.work_end_local', $r?->work_end_local);

                        $dur      = old('rules.'.$d.'.slot_duration_minutes', $r?->slot_duration_minutes);
                        $capSlot  = old('rules.'.$d.'.capacity_per_slot', $r?->capacity_per_slot);
                        $capDay   = old('rules.'.$d.'.capacity_per_day', $r?->capacity_per_day);

                        $breaksArray = old('rules.'.$d.'.breaks');
                        if (is_null($breaksArray) && is_array($r?->breaks_json)) {
                            $breaksArray = $r->breaks_json;
                        }
                    @endphp

                    <div class="border rounded-lg p-4"
                         x-data="availabilityDay(@json($breaksArray ?? []))">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm">{{ $dayNames[$d] ?? ('روز '.$d) }}</h3>
                            <span class="text-xs text-gray-400">
                            در صورت خالی بودن فیلدها، این روز از تنظیمات بالادستی پیروی خواهد کرد.
                        </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- تعطیل --}}
                            <div>
                                <label class="block text-xs mb-1">روز تعطیل؟</label>
                                <select name="rules[{{ $d }}][is_closed]" class="w-full border rounded p-2 text-sm">
                                    <option value="0" @selected($isClosed == '0')>خیر</option>
                                    <option value="1" @selected($isClosed == '1')>بله (کامل تعطیل)</option>
                                </select>
                            </div>

                            {{-- شروع کار --}}
                            <div>
                                <label class="block text-xs mb-1">شروع کار (ساعت)</label>
                                <input type="time"
                                       name="rules[{{ $d }}][work_start_local]"
                                       class="w-full border rounded p-2 text-sm"
                                       value="{{ $start }}">
                            </div>

                            {{-- پایان کار --}}
                            <div>
                                <label class="block text-xs mb-1">پایان کار (ساعت)</label>
                                <input type="time"
                                       name="rules[{{ $d }}][work_end_local]"
                                       class="w-full border rounded p-2 text-sm"
                                       value="{{ $end }}">
                            </div>

                            {{-- اسلات --}}
                            <div>
                                <label class="block text-xs mb-1">مدت هر اسلات (دقیقه)</label>
                                <input type="number"
                                       name="rules[{{ $d }}][slot_duration_minutes]"
                                       class="w-full border rounded p-2 text-sm"
                                       min="5" step="5"
                                       placeholder="مثلاً 30"
                                       value="{{ $dur }}">
                            </div>

                            {{-- ظرفیت اسلات --}}
                            <div>
                                <label class="block text-xs mb-1">ظرفیت هر اسلات</label>
                                <input type="number"
                                       name="rules[{{ $d }}][capacity_per_slot]"
                                       class="w-full border rounded p-2 text-sm"
                                       min="1"
                                       placeholder="مثلاً 1"
                                       value="{{ $capSlot }}">
                            </div>

                            {{-- ظرفیت روز --}}
                            <div>
                                <label class="block text-xs mb-1">حداکثر ظرفیت روزانه (اختیاری)</label>
                                <input type="number"
                                       name="rules[{{ $d }}][capacity_per_day]"
                                       class="w-full border rounded p-2 text-sm"
                                       min="0"
                                       placeholder="مثلاً 20"
                                       value="{{ $capDay }}">
                            </div>
                        </div>

                        {{-- استراحت‌ها --}}
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold">
                                    استراحت‌ها (بازه‌های شروع / پایان)
                                </label>
                                <button type="button"
                                        class="px-2 py-1 text-xs rounded bg-gray-100 hover:bg-gray-200"
                                        @click="addBreak()">
                                    + افزودن استراحت
                                </button>
                            </div>

                            <div class="space-y-2" x-show="breaks.length > 0">
                                <template x-for="(br, index) in breaks" :key="index">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1">
                                            <input type="time"
                                                   class="w-full border rounded p-2 text-xs"
                                                   x-model="br.start_local"
                                                   :name="`rules[{{ $d }}][breaks][${index}][start_local]`">
                                        </div>
                                        <div class="flex-1">
                                            <input type="time"
                                                   class="w-full border rounded p-2 text-xs"
                                                   x-model="br.end_local"
                                                   :name="`rules[{{ $d }}][breaks][${index}][end_local]`">
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
                                مثال: 12:30 تا 13:00 برای ناهار. می‌توانید چند استراحت در روز ثبت کنید.
                            </p>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="pt-4 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    اگر همه مقادیر یک روز را خالی بگذارید، آن روز به‌طور خودکار از تنظیمات سراسری / سرویس پیروی می‌کند.
                </p>
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    ذخیره برنامه زمانی ارائه‌دهنده
                </button>
            </div>
        </form>
    </div>

    <script>
        function availabilityDay(initialBreaks) {
            return {
                breaks: Array.isArray(initialBreaks)
                    ? initialBreaks.map(b => ({
                        start_local: b.start_local || '',
                        end_local: b.end_local || '',
                    }))
                    : [],
                addBreak() {
                    this.breaks.push({ start_local: '', end_local: '' });
                },
                removeBreak(index) {
                    this.breaks.splice(index, 1);
                },
            };
        }
    </script>
@endsection
