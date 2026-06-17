@php
    $action = $action ?? '';
    // Load existing triggers or default to one empty trigger
    $triggers = old('triggers', isset($workflow) ? $workflow->triggers->toArray() : []);
    if (empty($triggers)) {
        $triggers = [['type' => '', 'config' => []]];
    }
@endphp

<style>
    /* Premium Glassmorphism and Custom Micro-Animations */
    .premium-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(229, 231, 235, 0.8);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark .premium-card {
        background: rgba(26, 32, 44, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(75, 85, 99, 0.35);
    }
    .premium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 24px -10px rgba(99, 102, 241, 0.12);
        border-color: rgba(99, 102, 241, 0.35);
    }
    .trigger-item {
        animation: slideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .event-group-toggle svg.rotate-180 {
        transform: rotate(180deg);
    }
</style>

<form method="post" action="{{ $action }}" class="space-y-8">
    @csrf
    @if(($method ?? '') === 'patch')
        @method('patch')
    @endif

    <!-- بخش اطلاعات پایه -->
    <div class="premium-card shadow-sm rounded-2xl p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            اطلاعات کلی گردش کار
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- نام -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">نام گردش کار <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="wf-name" value="{{ old('name', $workflow->name ?? '') }}"
                       class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 transition-all py-3 px-4 shadow-sm"
                       placeholder="مثال: ارسال پیامک تایید نوبت" required>
                <p class="text-xs text-gray-400">یک نام گویا برای این فرآیند انتخاب کنید.</p>
            </div>

            <!-- کلید (پیشرفته) -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    شناسه سیستمی (Key)
                    <span class="text-xs font-normal text-gray-400">(تولید خودکار فینگلیش)</span>
                </label>
                <input type="text" name="key" id="wf-key" value="{{ old('key', $workflow->key ?? '') }}"
                       class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 transition-all py-3 px-4 shadow-sm font-mono text-left" dir="ltr"
                       required>
                <p class="text-xs text-gray-400">شناسه یکتا برای سیستم (انگلیسی، حروف و اعداد و خط تیره).</p>
            </div>

            <!-- توضیحات -->
            <div class="col-span-1 md:col-span-2 space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">توضیحات (اختیاری)</label>
                <textarea name="description" rows="2.5"
                          class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 transition-all py-3 px-4 shadow-sm"
                          placeholder="یادداشتی برای مدیران سیستم...">{{ old('description', $workflow->description ?? '') }}</textarea>
            </div>

            <!-- وضعیت فعال -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">وضعیت گردش کار</span>
                        <span class="text-xs text-gray-400 mt-0.5">در صورت غیرفعال بودن، این گردش کار به هیچ وجه اجرا نخواهد شد.</span>
                    </div>
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $workflow->is_active ?? true)) class="sr-only peer">
                            <div class="w-12 h-6 bg-gray-200 dark:bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:bg-gray-300 dark:border-gray-600 peer-checked:bg-emerald-500 transition-colors"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- بخش شرایط شروع -->
    <div class="premium-card shadow-sm rounded-2xl p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-2 h-full bg-indigo-500"></div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    شرایط شروع (Triggers)
                </h3>
                <p class="mt-1 text-xs text-gray-400">مشخص کنید این گردش کار تحت چه فیلترها و شرایطی باید فعال و شروع شود.</p>
            </div>

            <button type="button" id="add-trigger-btn"
                    class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                افزودن شرط شروع جدید
            </button>
        </div>

        <div id="triggers-container" class="space-y-6">
            @foreach($triggers as $index => $trigger)
                @php
                    $triggerType = $trigger['type'] ?? '';
                    $tConfig = $trigger['config'] ?? [];
                @endphp
                <div class="trigger-item group relative bg-gray-50/50 dark:bg-gray-900/10 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-all hover:border-indigo-300 dark:hover:border-indigo-800">

                    <!-- دکمه حذف -->
                    <button type="button" class="remove-trigger absolute top-4 left-4 text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20" title="حذف شرط">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <!-- انتخاب نوع شرط -->
                        <div class="lg:col-span-4 space-y-2">
                            <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">نوع شرط شروع</label>
                            <select name="triggers[{{ $index }}][type]" class="trigger-type-select block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 shadow-sm text-sm">
                                <option value="">انتخاب کنید...</option>
                                <optgroup label="بر اساس رویداد (آنی)">
                                    <option value="EVENT" @selected($triggerType === 'EVENT')>وقتی رویدادی رخ می‌دهد (Event)</option>
                                </optgroup>
                                <optgroup label="بر اساس زمان (یادآوری نوبت)">
                                    <option value="APPOINTMENT_REMINDER" @selected($triggerType === 'APPOINTMENT_REMINDER')>یادآوری نوبت‌دهی (قبل/بعد از نوبت)</option>
                                </optgroup>
                                <optgroup label="بر اساس زمان‌بندی (دوره‌ای)">
                                    <option value="SCHEDULE" @selected($triggerType === 'SCHEDULE')>زمان‌بندی دوره‌ای خاص (Cron Job)</option>
                                </optgroup>
                            </select>
                            <p class="text-xs text-gray-400">محرک اصلی اجرای گردش کار را تعیین کنید.</p>
                        </div>

                        <!-- تنظیمات مربوطه -->
                        <div class="lg:col-span-8 border-t lg:border-t-0 lg:border-r border-gray-200 dark:border-gray-800 pt-6 lg:pt-0 lg:pr-6">

                            <!-- راهنما -->
                            <div class="trigger-config config-empty text-sm text-gray-400 italic flex items-center justify-center min-h-[100px]">
                                لطفا ابتدا یک نوع شرط را در سمت راست انتخاب کنید.
                            </div>

                            <!-- 1. EVENT CONFIG -->
                            <div class="trigger-config config-EVENT hidden space-y-6">
                                <!-- رویدادهای محرک (دسته‌بندی شده) -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">رویدادهای محرک <span class="text-red-500">*</span></label>
                                    @php
                                        $selectedEvents = (array)($tConfig['event_key'] ?? []);
                                    @endphp

                                    <div class="space-y-3">
                                        <!-- دسته‌بندی نوبت‌دهی -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900">
                                            <button type="button" class="event-group-toggle w-full flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                                <span>رویدادهای نوبت‌دهی (Appointment Events)</span>
                                                <svg class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div class="event-group-content p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @php
                                                    $aptEvents = [
                                                        'appointment_created' => 'ایجاد نوبت جدید',
                                                        'appointment_created_online' => 'رزرو آنلاین نوبت (توسط بیمار)',
                                                        'appointment_created_operator' => 'ثبت نوبت توسط اپراتور',
                                                        'appointment_canceled' => 'لغو نوبت',
                                                        'appointment_no_show' => 'عدم حضور بیمار (No-Show)'
                                                    ];
                                                    if (isset($triggerOptions['APPOINTMENT'])) {
                                                        foreach($triggerOptions['APPOINTMENT'] as $k => $lbl) {
                                                            if (!str_starts_with($k, 'appointment_reminder_') && !isset($aptEvents[$k])) {
                                                                $aptEvents[$k] = $lbl;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @foreach($aptEvents as $k => $label)
                                                    <label class="flex items-center gap-3 p-2 bg-gray-50/50 dark:bg-gray-800/30 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-all select-none">
                                                        <input type="checkbox" name="triggers[{{ $index }}][config][event_key][]" value="{{ $k }}" @checked(in_array($k, $selectedEvents))
                                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 event-checkbox h-4.5 w-4.5">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- دسته‌بندی صورت وضعیت مالی -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900">
                                            <button type="button" class="event-group-toggle w-full flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                                <span>رویدادهای صورت وضعیت مالی (Statements)</span>
                                                <svg class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div class="event-group-content p-4 grid grid-cols-1 md:grid-cols-2 gap-3 hidden">
                                                @php
                                                    $stEvents = [
                                                        'statement_created' => 'ایجاد صورت وضعیت جدید',
                                                        'statement_status_changed' => 'تغییر وضعیت صورت وضعیت',
                                                        'statement_approved' => 'تایید صورت وضعیت',
                                                        'statement_completed' => 'تکمیل صورت وضعیت'
                                                    ];
                                                @endphp
                                                @foreach($stEvents as $k => $label)
                                                    <label class="flex items-center gap-3 p-2 bg-gray-50/50 dark:bg-gray-800/30 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-all select-none">
                                                        <input type="checkbox" name="triggers[{{ $index }}][config][event_key][]" value="{{ $k }}" @checked(in_array($k, $selectedEvents))
                                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 event-checkbox h-4.5 w-4.5">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- دسته‌بندی طرح درمان -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900">
                                            <button type="button" class="event-group-toggle w-full flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                                <span>رویدادهای طرح درمان (Treatment Plans)</span>
                                                <svg class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div class="event-group-content p-4 grid grid-cols-1 md:grid-cols-2 gap-3 hidden">
                                                @php
                                                    $tpEvents = [
                                                        'treatment_plan_draft' => 'طرح درمان: پیش‌نویس',
                                                        'treatment_plan_active' => 'طرح درمان: فعال شده',
                                                        'treatment_plan_completed' => 'طرح درمان: تکمیل شده'
                                                    ];
                                                @endphp
                                                @foreach($tpEvents as $k => $label)
                                                    <label class="flex items-center gap-3 p-2 bg-gray-50/50 dark:bg-gray-800/30 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-all select-none">
                                                        <input type="checkbox" name="triggers[{{ $index }}][config][event_key][]" value="{{ $k }}" @checked(in_array($k, $selectedEvents))
                                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 event-checkbox h-4.5 w-4.5">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- فیلتر خدمات (شامل / شامل نشود) -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">فیلتر خدمات/سرویس‌ها</label>
                                        <select name="triggers[{{ $index }}][config][service_operator]" class="operator-select text-xs font-bold rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 py-1 px-2.5">
                                            <option value="IN" @selected(($tConfig['service_operator'] ?? 'IN') === 'IN')>شامل موارد زیر باشد</option>
                                            <option value="NOT_IN" @selected(($tConfig['service_operator'] ?? 'IN') === 'NOT_IN')>شامل موارد زیر نباشد (به جز...)</option>
                                        </select>
                                    </div>
                                    @php
                                        $selectedServices = (array)($tConfig['service_ids'] ?? (isset($tConfig['service_id']) ? [$tConfig['service_id']] : []));
                                    @endphp
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-3.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                                        @foreach($services as $service)
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none">
                                                <input type="checkbox" name="triggers[{{ $index }}][config][service_ids][]" value="{{ $service->id }}" @checked(in_array($service->id, $selectedServices))
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 service-checkbox h-4 w-4">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $service->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- فیلتر پزشکان (شامل / شامل نشود) -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">فیلتر پزشک/ارائه‌دهنده</label>
                                        <select name="triggers[{{ $index }}][config][provider_operator]" class="operator-select text-xs font-bold rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 py-1 px-2.5">
                                            <option value="IN" @selected(($tConfig['provider_operator'] ?? 'IN') === 'IN')>شامل موارد زیر باشد</option>
                                            <option value="NOT_IN" @selected(($tConfig['provider_operator'] ?? 'IN') === 'NOT_IN')>شامل موارد زیر نباشد (به جز...)</option>
                                        </select>
                                    </div>
                                    @php
                                        $selectedProviders = (array)($tConfig['provider_ids'] ?? (isset($tConfig['provider_id']) ? [$tConfig['provider_id']] : []));
                                    @endphp
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 max-h-40 overflow-y-auto p-3.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                                        @foreach($users as $user)
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none">
                                                <input type="checkbox" name="triggers[{{ $index }}][config][provider_ids][]" value="{{ $user->id }}" @checked(in_array($user->id, $selectedProviders))
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 provider-checkbox h-4 w-4">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 2. APPOINTMENT_REMINDER CONFIG -->
                            <div class="trigger-config config-APPOINTMENT_REMINDER hidden space-y-6">
                                <!-- زمان دقیق ارسال یادآوری و ساعت اجرا -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">فاصله زمانی از نوبت</label>
                                        @php
                                            $rawOffset = (int)($tConfig['offset_minutes'] ?? -60);
                                            $direction = $rawOffset < 0 ? -1 : 1;
                                            $absMinutes = abs($rawOffset);

                                            $val = $absMinutes;
                                            $unit = 1;

                                            if ($absMinutes % 1440 === 0) {
                                                $val = $absMinutes / 1440;
                                                $unit = 1440;
                                            } elseif ($absMinutes % 60 === 0) {
                                                $val = $absMinutes / 60;
                                                $unit = 60;
                                            }
                                        @endphp
                                        <div class="flex rounded-lg shadow-sm border border-gray-300 dark:border-gray-700 overflow-hidden">
                                            <input type="number" value="{{ $val }}" min="1" class="offset-val-input block w-20 border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 text-center focus:ring-0 focus:outline-none text-sm font-semibold">
                                            <select class="offset-unit-select block border-r border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 focus:ring-0 focus:outline-none border-gray-300 dark:border-gray-700 text-xs font-bold">
                                                <option value="1" @selected($unit === 1)>دقیقه</option>
                                                <option value="60" @selected($unit === 60)>ساعت</option>
                                                <option value="1440" @selected($unit === 1440)>روز</option>
                                            </select>
                                            <select class="offset-dir-select block border-r border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 focus:ring-0 focus:outline-none border-gray-300 dark:border-gray-700 text-xs font-bold">
                                                <option value="-1" @selected($direction === -1)>قبل از شروع</option>
                                                <option value="1" @selected($direction === 1)>بعد از شروع</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="triggers[{{ $index }}][config][offset_minutes]" value="{{ $rawOffset }}" class="real-offset-input">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">ساعت اجرای مشخص روز (اختیاری)</label>
                                        <input type="time" name="triggers[{{ $index }}][config][run_at_time]" value="{{ $tConfig['run_at_time'] ?? '' }}"
                                               class="run-at-time-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent text-gray-900 dark:text-white py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold">
                                        <p class="text-[10px] text-gray-400 leading-normal">برای مثال: «ارسال ۳ روز قبل، رأس ساعت ۰۸:۰۰ صبح». در صورت خالی بودن، به صورت آنی در همان دقیقه محاسبه شده ارسال می‌شود.</p>
                                    </div>
                                </div>

                                <!-- وضعیت‌های نوبت (لیست کامل ۹ وضعیت) -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">وضعیت‌های نوبت مجاز</label>
                                    @php
                                        $selectedStatuses = (array)($tConfig['statuses'] ?? [$tConfig['status'] ?? 'CONFIRMED']);
                                    @endphp
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 bg-white dark:bg-gray-900 p-3 rounded-xl border border-gray-200 dark:border-gray-800">
                                        @php
                                            $appointmentStatuses = [
                                                'CONFIRMED' => 'تایید شده (Confirmed)',
                                                'PENDING' => 'در انتظار تایید (Pending)',
                                                'PENDING_PAYMENT' => 'در انتظار پرداخت',
                                                'DRAFT' => 'پیش‌نویس (Draft)',
                                                'DONE' => 'انجام شده (Done)',
                                                'RESCHEDULED' => 'تغییر زمان داده شده',
                                                'CANCELED_BY_CLIENT' => 'لغو توسط بیمار',
                                                'CANCELED_BY_ADMIN' => 'لغو توسط سیستم/پزشک',
                                                'NO_SHOW' => 'عدم حضور بیمار (No-Show)'
                                            ];
                                        @endphp
                                        @foreach($appointmentStatuses as $k => $label)
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none">
                                                <input type="checkbox" name="triggers[{{ $index }}][config][statuses][]" value="{{ $k }}" @checked(in_array($k, $selectedStatuses))
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 status-checkbox h-4 w-4">
                                                <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- فیلتر خدمات (شامل / شامل نشود) -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">فیلتر خدمات/سرویس‌ها</label>
                                        <select name="triggers[{{ $index }}][config][service_operator]" class="operator-select text-xs font-bold rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 py-1 px-2.5">
                                            <option value="IN" @selected(($tConfig['service_operator'] ?? 'IN') === 'IN')>شامل موارد زیر باشد</option>
                                            <option value="NOT_IN" @selected(($tConfig['service_operator'] ?? 'IN') === 'NOT_IN')>شامل موارد زیر نباشد (به جز...)</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 max-h-40 overflow-y-auto p-3.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                                        @foreach($services as $service)
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none">
                                                <input type="checkbox" name="triggers[{{ $index }}][config][service_ids][]" value="{{ $service->id }}" @checked(in_array($service->id, $selectedServices))
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 service-checkbox h-4 w-4">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $service->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- فیلتر پزشکان (شامل / شامل نشود) -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">فیلتر پزشک/ارائه‌دهنده</label>
                                        <select name="triggers[{{ $index }}][config][provider_operator]" class="operator-select text-xs font-bold rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 py-1 px-2.5">
                                            <option value="IN" @selected(($tConfig['provider_operator'] ?? 'IN') === 'IN')>شامل موارد زیر باشد</option>
                                            <option value="NOT_IN" @selected(($tConfig['provider_operator'] ?? 'IN') === 'NOT_IN')>شامل موارد زیر نباشد (به جز...)</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 max-h-40 overflow-y-auto p-3.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                                        @foreach($users as $user)
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none">
                                                <input type="checkbox" name="triggers[{{ $index }}][config][provider_ids][]" value="{{ $user->id }}" @checked(in_array($user->id, $selectedProviders))
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 provider-checkbox h-4 w-4">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 3. SCHEDULE CONFIG -->
                            <div class="trigger-config config-SCHEDULE hidden space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">الگوهای آماده زمان‌بندی</label>
                                        <select class="cron-preset-select block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="">-- انتخاب الگوی پیش‌فرض --</option>
                                            <option value="0 8 * * *">هر روز ساعت ۸:۰۰ صبح</option>
                                            <option value="0 12 * * *">هر روز ساعت ۱۲:۰۰ ظهر</option>
                                            <option value="0 18 * * *">هر روز ساعت ۶:۰۰ عصر</option>
                                            <option value="0 8 * * 6">شنبه‌ها ساعت ۸:۰۰ صبح</option>
                                            <option value="0 0 * * *">هر شب ساعت ۱۲:۰۰ بامداد</option>
                                            <option value="custom">تنظیم دستی فرمت کرون</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">کد زمان‌بندی (Cron Expression)</label>
                                        <input type="text" name="triggers[{{ $index }}][config][cron]" value="{{ $tConfig['cron'] ?? '0 8 * * *' }}"
                                               class="cron-input block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-mono text-left py-2.5 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm" dir="ltr"
                                               placeholder="* * * * *">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">فرمت کرون‌جاب لینوکسی استاندارد شامل ۵ فیلد: (دقیقه ساعت روز ماه هفته)</p>
                            </div>

                            <!-- توضیح زبانی روان (Dynamic NLP Preview Card) -->
                            <div class="nlp-preview-card mt-5 p-4 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/50 dark:border-indigo-900/20 rounded-xl flex items-start gap-2.5">
                                <div class="text-indigo-600 dark:text-indigo-400 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400">خلاصه عملکرد این شرط:</span>
                                    <p class="nlp-text text-sm text-indigo-950 dark:text-indigo-200 leading-relaxed font-medium">لطفاً نوع شرط را انتخاب کنید...</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- دکمه‌های عملیات -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
        @if(($method ?? '') === 'patch')
            <a href="{{ route('user.workflows.index') }}"
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                انصراف
            </a>
        @endif

        <button type="submit"
                class="inline-flex justify-center px-8 py-3 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all hover:shadow-lg hover:shadow-indigo-500/10">
            {{ ($method ?? '') === 'patch' ? 'ذخیره تغییرات عمومی' : 'ایجاد گردش کار جدید' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Transliterate Persian text to Finglish for system Key ---
        function transliteratePersianToEnglish(text) {
            const map = {
                'آ': 'a', 'ا': 'a', 'ب': 'b', 'پ': 'p', 'ت': 't', 'ث': 's', 'ج': 'j', 'چ': 'ch',
                'ح': 'h', 'خ': 'kh', 'د': 'd', 'ذ': 'z', 'ر': 'r', 'ز': 'z', 'ژ': 'zh', 'س': 's',
                'ش': 'sh', 'ص': 's', 'ض': 'z', 'ط': 't', 'ظ': 'z', 'ع': 'a', 'غ': 'gh', 'ف': 'f',
                'ق': 'gh', 'ک': 'k', 'گ': 'g', 'ل': 'l', 'م': 'm', 'ن': 'n', 'و': 'v', 'ه': 'h',
                'ی': 'y', 'ئ': 'e', 'ء': 'a', 'ی': 'y', 'ک': 'k', 'ه': 'h',
                ' ': '_', '-': '_', '_': '_'
            };

            return text.split('').map(char => {
                return map[char] || (/[a-zA-Z0-9]/.test(char) ? char.toLowerCase() : '');
            }).join('')
            .replace(/_+/g, '_')
            .replace(/^_+|_+$/g, '');
        }

        const nameInput = document.getElementById('wf-name');
        const keyInput = document.getElementById('wf-key');

        if (nameInput && keyInput) {
            nameInput.addEventListener('input', function() {
                let generatedSlug = transliteratePersianToEnglish(nameInput.value);

                if (keyInput.value === '' || keyInput.dataset.auto === 'true') {
                    keyInput.value = generatedSlug;
                    keyInput.dataset.auto = 'true';
                }
            });

            keyInput.addEventListener('input', function() {
                keyInput.dataset.auto = 'false';
            });

            if (keyInput.value === '') keyInput.dataset.auto = 'true';
        }

        // --- Event groups accordion logic ---
        const container = document.getElementById('triggers-container');
        const addBtn = document.getElementById('add-trigger-btn');

        function initAccordions(element) {
            element.querySelectorAll('.event-group-toggle').forEach(btn => {
                // Remove existing click listener to avoid duplicate bindings on cloning
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);

                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const content = newBtn.nextElementSibling;
                    const icon = newBtn.querySelector('svg');

                    if (content.classList.contains('hidden')) {
                        content.classList.remove('hidden');
                        icon.classList.add('rotate-180');
                    } else {
                        content.classList.add('hidden');
                        icon.classList.remove('rotate-180');
                    }
                });
            });
        }
        initAccordions(document);

        // --- Mappings for human translation ---
        const eventLabels = {
            'appointment_created': 'ایجاد نوبت جدید',
            'appointment_created_online': 'رزرو آنلاین نوبت بیمار',
            'appointment_created_operator': 'ثبت نوبت توسط اپراتور',
            'appointment_canceled': 'لغو نوبت',
            'appointment_no_show': 'عدم حضور بیمار (No-Show)',
            'statement_created': 'ایجاد صورت وضعیت مالی',
            'statement_status_changed': 'تغییر وضعیت صورت وضعیت',
            'statement_approved': 'تایید صورت وضعیت مالی',
            'statement_completed': 'تکمیل صورت وضعیت مالی',
            'treatment_plan_draft': 'طرح درمان در وضعیت پیش‌نویس',
            'treatment_plan_active': 'طرح درمان در وضعیت فعال',
            'treatment_plan_completed': 'طرح درمان در وضعیت تکمیل شده'
        };

        const statusLabels = {
            'CONFIRMED': 'تایید شده',
            'PENDING': 'در انتظار تایید',
            'PENDING_PAYMENT': 'در انتظار پرداخت',
            'DRAFT': 'پیش‌نویس',
            'DONE': 'انجام شده',
            'RESCHEDULED': 'تغییر زمان داده شده',
            'CANCELED_BY_CLIENT': 'لغو شده توسط بیمار',
            'CANCELED_BY_ADMIN': 'لغو شده توسط ادمین',
            'NO_SHOW': 'عدم حضور بیمار'
        };

        function updateNlpText(item) {
            const typeSelect = item.querySelector('.trigger-type-select');
            const type = typeSelect.value;
            const nlpTextEl = item.querySelector('.nlp-text');

            if (!type) {
                nlpTextEl.textContent = 'لطفاً نوع شرط را انتخاب کنید...';
                return;
            }

            if (type === 'EVENT') {
                // Read checked events
                const checkedEvents = Array.from(item.querySelectorAll('.event-checkbox:checked')).map(el => {
                    return eventLabels[el.value] || el.value;
                });
                // Read checked services
                const checkedServices = Array.from(item.querySelectorAll('.service-checkbox:checked')).map(el => {
                    const label = el.nextElementSibling ? el.nextElementSibling.textContent.trim() : el.value;
                    return `«${label}»`;
                });
                const serviceOp = item.querySelector('[name*="[service_operator]"]').value;

                // Read checked providers
                const checkedProviders = Array.from(item.querySelectorAll('.provider-checkbox:checked')).map(el => {
                    const label = el.nextElementSibling ? el.nextElementSibling.textContent.trim() : el.value;
                    return `«${label}»`;
                });
                const providerOp = item.querySelector('[name*="[provider_operator]"]').value;

                let text = 'وقتی ';
                if (checkedEvents.length > 0) {
                    text += 'رویداد ' + checkedEvents.join(' یا ') + ' رخ دهد';
                } else {
                    text += 'هر کدام از رویدادها رخ دهد';
                }

                if (checkedServices.length > 0) {
                    if (serviceOp === 'IN') {
                        text += ' و خدمتِ نوبت مربوط به ' + checkedServices.join(' یا ') + ' باشد';
                    } else {
                        text += ' و خدمتِ نوبت مربوط به هر کدام از خدمات باشد، به جز ' + checkedServices.join(' و ');
                    }
                }

                if (checkedProviders.length > 0) {
                    if (providerOp === 'IN') {
                        text += ' و پزشکِ نوبت مربوط به ' + checkedProviders.join(' یا ') + ' باشد';
                    } else {
                        text += ' و پزشکِ نوبت مربوط به هر پزشکی باشد، به جز ' + checkedProviders.join(' و ');
                    }
                }

                text += '، این گردش کار به طور آنی اجرا خواهد شد.';
                nlpTextEl.textContent = text;
            } else if (type === 'APPOINTMENT_REMINDER') {
                const val = parseInt(item.querySelector('.offset-val-input').value) || 0;
                const unitText = item.querySelector('.offset-unit-select').options[item.querySelector('.offset-unit-select').selectedIndex].text;
                const dirText = item.querySelector('.offset-dir-select').options[item.querySelector('.offset-dir-select').selectedIndex].text;
                const runAtTime = item.querySelector('.run-at-time-input').value;

                const checkedStatuses = Array.from(item.querySelectorAll('.status-checkbox:checked')).map(el => {
                    return statusLabels[el.value] || el.value;
                });
                const checkedServices = Array.from(item.querySelectorAll('.service-checkbox:checked')).map(el => {
                    return `«${el.nextElementSibling.textContent.trim()}»`;
                });
                const serviceOp = item.querySelector('[name*="[service_operator]"]').value;

                const checkedProviders = Array.from(item.querySelectorAll('.provider-checkbox:checked')).map(el => {
                    return `«${el.nextElementSibling.textContent.trim()}»`;
                });
                const providerOp = item.querySelector('[name*="[provider_operator]"]').value;

                let text = `دقیقاً ${val} ${unitText} ${dirText}`;
                if (runAtTime) {
                    text += ` رأس ساعت ${runAtTime} صبح/عصر`;
                }

                if (checkedStatuses.length > 0) {
                    text += ` برای نوبت‌هایی با وضعیت ${checkedStatuses.join(' یا ')}`;
                } else {
                    text += ' برای نوبت‌ها با هر وضعیتی';
                }

                if (checkedServices.length > 0) {
                    if (serviceOp === 'IN') {
                        text += ` مربوط به خدمت(های) ${checkedServices.join(' یا ')}`;
                    } else {
                        text += ` مربوط به تمام خدمات به جز ${checkedServices.join(' و ')}`;
                    }
                }
                if (checkedProviders.length > 0) {
                    if (providerOp === 'IN') {
                        text += ` برای پزشک(ها) ${checkedProviders.join(' یا ')}`;
                    } else {
                        text += ` برای تمام پزشکان به جز ${checkedProviders.join(' و ')}`;
                    }
                }

                text += '، این فرآیند زمان‌بندی شده اجرا می‌شود.';
                nlpTextEl.textContent = text;
            } else if (type === 'SCHEDULE') {
                const cronVal = item.querySelector('.cron-input').value;
                const presetSelect = item.querySelector('.cron-preset-select');
                let presetText = presetSelect.selectedIndex > 0 && presetSelect.value !== 'custom'
                    ? presetSelect.options[presetSelect.selectedIndex].text
                    : '';

                if (presetText) {
                    nlpTextEl.textContent = `این گردش کار به صورت زمان‌بندی شده و منظم در ${presetText} (با کد کرون: ${cronVal}) اجرا خواهد شد.`;
                } else {
                    nlpTextEl.textContent = `این گردش کار طبق زمان‌بندی سفارشی با کد کرون [ ${cronVal} ] به طور خودکار اجرا خواهد شد.`;
                }
            }
        }

        function handleTypeChange(select) {
            const item = select.closest('.trigger-item');
            const type = select.value;

            // Hide configs
            item.querySelectorAll('.trigger-config').forEach(el => el.classList.add('hidden'));

            // Disable all inner fields
            item.querySelectorAll('.trigger-config input, .trigger-config select').forEach(input => {
                input.disabled = true;
            });

            if (type) {
                const configDiv = item.querySelector(`.config-${type}`);
                if (configDiv) {
                    configDiv.classList.remove('hidden');
                    configDiv.querySelectorAll('input, select').forEach(input => {
                        input.disabled = false;
                    });
                }
                item.querySelector('.config-empty').classList.add('hidden');
            } else {
                item.querySelector('.config-empty').classList.remove('hidden');
            }

            updateNlpText(item);
        }

        function calculateOffset(item) {
            const val = parseInt(item.querySelector('.offset-val-input').value) || 0;
            const unit = parseInt(item.querySelector('.offset-unit-select').value) || 1;
            const dir = parseInt(item.querySelector('.offset-dir-select').value) || -1;

            const finalOffset = val * unit * dir;
            item.querySelector('.real-offset-input').value = finalOffset;

            updateNlpText(item);
        }

        // Initialize all trigger views on page load
        container.querySelectorAll('.trigger-item').forEach(item => {
            const typeSelect = item.querySelector('.trigger-type-select');
            handleTypeChange(typeSelect);

            const cronInput = item.querySelector('.cron-input');
            if (cronInput) {
                const presetSelect = item.querySelector('.cron-preset-select');
                let matched = false;
                Array.from(presetSelect.options).forEach(opt => {
                    if (opt.value === cronInput.value) {
                        presetSelect.value = opt.value;
                        matched = true;
                    }
                });
                if (!matched && cronInput.value !== '') {
                    presetSelect.value = 'custom';
                }
            }
        });

        // Event delegation
        container.addEventListener('change', function(e) {
            const item = e.target.closest('.trigger-item');
            if (!item) return;

            if (e.target.classList.contains('trigger-type-select')) {
                handleTypeChange(e.target);
            }

            if (e.target.classList.contains('offset-val-input') || e.target.classList.contains('offset-unit-select') || e.target.classList.contains('offset-dir-select')) {
                calculateOffset(item);
            }

            if (e.target.classList.contains('event-checkbox') || e.target.classList.contains('service-checkbox') || e.target.classList.contains('provider-checkbox') || e.target.classList.contains('status-checkbox') || e.target.classList.contains('operator-select') || e.target.classList.contains('run-at-time-input')) {
                updateNlpText(item);
            }

            if (e.target.classList.contains('cron-preset-select')) {
                const val = e.target.value;
                const cronInput = item.querySelector('.cron-input');
                if (val && val !== 'custom') {
                    cronInput.value = val;
                    cronInput.readOnly = true;
                    cronInput.classList.add('bg-gray-100', 'dark:bg-gray-800');
                } else if (val === 'custom') {
                    cronInput.readOnly = false;
                    cronInput.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                }
                updateNlpText(item);
            }
        });

        container.addEventListener('input', function(e) {
            const item = e.target.closest('.trigger-item');
            if (!item) return;

            if (e.target.classList.contains('offset-val-input')) {
                calculateOffset(item);
            }

            if (e.target.classList.contains('cron-input')) {
                updateNlpText(item);
            }
        });

        // Add Trigger
        addBtn.addEventListener('click', function() {
            const index = container.children.length;
            const firstItem = container.querySelector('.trigger-item');

            let newItem;
            if (firstItem) {
                newItem = firstItem.cloneNode(true);
            } else {
                location.reload();
                return;
            }

            // Reset inputs and update array index names
            newItem.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
                const name = cb.getAttribute('name');
                if (name) {
                    cb.setAttribute('name', name.replace(/triggers\[\d+\]/, `triggers[${index}]`));
                }
            });

            newItem.querySelectorAll('select, input[type="text"], input[type="number"], input[type="hidden"], input[type="time"]').forEach(input => {
                if (input.tagName === 'SELECT') {
                    if (input.classList.contains('operator-select')) {
                        input.value = 'IN';
                    } else {
                        input.selectedIndex = 0;
                    }
                } else if (input.classList.contains('cron-input')) {
                    input.value = '0 8 * * *';
                    input.readOnly = false;
                    input.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                } else if (input.classList.contains('real-offset-input')) {
                    input.value = '-60';
                } else if (input.classList.contains('offset-val-input')) {
                    input.value = '1';
                } else {
                    input.value = '';
                }
                input.disabled = true;

                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/triggers\[\d+\]/, `triggers[${index}]`));
                }
            });

            // Enable select type again
            const typeSelect = newItem.querySelector('.trigger-type-select');
            typeSelect.disabled = false;

            // Remove trigger button ensure
            let removeBtn = newItem.querySelector('.remove-trigger');
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-trigger absolute top-4 left-4 text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20';
                removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
                newItem.appendChild(removeBtn);
            }

            container.appendChild(newItem);
            initAccordions(newItem);
            handleTypeChange(typeSelect);
        });

        // Remove Trigger delegation
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-trigger');
            if (btn) {
                if (container.children.length > 1) {
                    btn.closest('.trigger-item').remove();
                } else {
                    alert('حداقل یک شرط برای شروع گردش کار لازم است.');
                }
            }
        });
    });
</script>
