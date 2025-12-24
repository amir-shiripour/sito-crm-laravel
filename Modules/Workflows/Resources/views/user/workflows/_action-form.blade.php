@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $actionInstance = $action ?? null;
    $cfg = $isEdit ? ($actionInstance->config ?? []) : [];
    $formAction = $isEdit
        ? route('user.workflows.actions.update', [$workflow, $stage, $actionInstance])
        : route('user.workflows.actions.store', [$workflow, $stage]);
@endphp

<form method="post" action="{{ $formAction }}"
      class="border border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-3 space-y-3"
      x-data="{
          actionType: '{{ $isEdit ? $actionInstance->action_type : \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}',
          assigneeTarget: '{{ $cfg['assignee_target'] ?? 'CURRENT_USER' }}'
      }">
    @csrf
    @if($isEdit)
        @method('patch')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="text-xs text-gray-500">نوع اکشن</label>
            <select name="action_type" x-model="actionType" class="w-full rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                @foreach([\Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS => 'ارسال پیامک',
                          \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK => 'ایجاد وظیفه',
                          \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP => 'ایجاد پیگیری',
                          \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER => 'یادآوری',
                          \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION => 'نوتیفیکیشن سیستمی'] as $type => $label)
                    <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500">ترتیب</label>
            <input type="number" name="sort_order" value="{{ $isEdit ? $actionInstance->sort_order : 0 }}" min="0"
                   class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </div>
    </div>

    {{-- SMS Fields --}}
    <div class="space-y-3" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs text-gray-500">الگوی پیامک (pattern)</label>
                <input type="text" name="config[pattern_key]" value="{{ $cfg['pattern_key'] ?? '' }}" placeholder="مثال: 234"
                       class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="text-xs text-gray-500">هدف ارسال</label>
                <select name="config[target]" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="APPOINTMENT_CLIENT" @selected(($cfg['target'] ?? '') === 'APPOINTMENT_CLIENT')>مشتری نوبت</option>
                    <option value="CUSTOM_PHONE" @selected(($cfg['target'] ?? '') === 'CUSTOM_PHONE')>شماره دلخواه</option>
                </select>
                <input type="text" name="config[phone]" value="{{ $cfg['phone'] ?? '' }}" placeholder="شماره دلخواه"
                       class="mt-1 w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="text-xs text-gray-500">تاخیر ارسال (دقیقه)</label>
                <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}"
                       class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <p class="text-[10px] text-gray-400 mt-1">نسبت به زمان شروع نوبت. منفی برای قبل از نوبت.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs text-gray-500">متن پیام (اگر الگو ندارید)</label>
                <textarea name="config[message]" rows="2" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="سلام {client_name} عزیز ...">{{ $cfg['message'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="text-xs text-gray-500">مقادیر الگو / پارامترها (ترتیبی)</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($tokenOptions as $tokenKey => $tokenLabel)
                        <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                            <input type="checkbox" name="config[params][]" value="{{ $tokenKey }}" @checked(in_array($tokenKey, $cfg['params'] ?? [])) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ $tokenLabel }}
                        </label>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-500 mt-1">ترتیب انتخاب‌شده معادل {0}، {1} و ... در الگو است.</p>
            </div>
        </div>
    </div>

    {{-- Task/FollowUp Fields --}}
    <div class="space-y-3" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs text-gray-500">عنوان</label>
                <input type="text" name="config[title]" value="{{ $cfg['title'] ?? '' }}" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="text-xs text-gray-500">منتسب به</label>
                <select name="config[assignee_target]" x-model="assigneeTarget" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="CURRENT_USER">کاربر فعلی</option>
                    <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                    <option value="SPECIFIC_USER">کاربر خاص</option>
                </select>
                <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                    <select name="config[assignee_id]" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">انتخاب کاربر...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500">تاخیر ایجاد (روز)</label>
                <input type="number" name="config[offset_days]" value="{{ $cfg['offset_days'] ?? '0' }}" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <p class="text-[10px] text-gray-400 mt-1">نسبت به زمان شروع نوبت.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
             <div>
                <label class="text-xs text-gray-500">اولویت</label>
                <select name="config[priority]" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="LOW" @selected(($cfg['priority'] ?? '') === 'LOW')>کم</option>
                    <option value="MEDIUM" @selected(($cfg['priority'] ?? 'MEDIUM') === 'MEDIUM')>معمولی</option>
                    <option value="HIGH" @selected(($cfg['priority'] ?? '') === 'HIGH')>زیاد</option>
                    <option value="CRITICAL" @selected(($cfg['priority'] ?? '') === 'CRITICAL')>بحرانی</option>
                </select>
            </div>
             <div>
                <label class="text-xs text-gray-500">وضعیت</label>
                <select name="config[status]" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="TODO" @selected(($cfg['status'] ?? 'TODO') === 'TODO')>در صف انجام</option>
                    <option value="IN_PROGRESS" @selected(($cfg['status'] ?? '') === 'IN_PROGRESS')>در حال انجام</option>
                    <option value="DONE" @selected(($cfg['status'] ?? '') === 'DONE')>انجام شده</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500">توضیحات</label>
            <textarea name="config[description]" rows="2" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ $cfg['description'] ?? '' }}</textarea>
        </div>
    </div>

    {{-- Reminder Fields --}}
    <div class="space-y-3" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs text-gray-500">منتسب به</label>
                <select name="config[assignee_target]" x-model="assigneeTarget" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="CURRENT_USER">کاربر فعلی</option>
                    <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                    <option value="SPECIFIC_USER">کاربر خاص</option>
                </select>
                <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                    <select name="config[assignee_id]" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">انتخاب کاربر...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500">تاخیر یادآوری (دقیقه)</label>
                <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <p class="text-[10px] text-gray-400 mt-1">نسبت به زمان شروع نوبت. منفی برای قبل از نوبت.</p>
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500">پیام یادآوری</label>
            <textarea name="config[message]" rows="2" class="w-full rounded-lg border-gray-200 text-sm px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ $cfg['message'] ?? '' }}</textarea>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
            {{ $isEdit ? 'ذخیره اکشن' : 'افزودن اکشن' }}
        </button>
        @if($isEdit)
            <button type="submit" form="delete-action-{{ $actionInstance->id }}" class="text-xs text-red-600 hover:text-red-700" onclick="return confirm('حذف اکشن؟');">حذف</button>
        @endif
    </div>
</form>

@if($isEdit)
    <form id="delete-action-{{ $actionInstance->id }}" method="post" action="{{ route('user.workflows.actions.destroy', [$workflow, $stage, $actionInstance]) }}" class="hidden">
        @csrf
        @method('delete')
    </form>
@endif
