@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $actionInstance = $action ?? null;
    $cfg = $isEdit ? ($actionInstance->config ?? []) : [];
    $formAction = $isEdit
        ? route('user.workflows.actions.update', [$workflow, $stage, $actionInstance])
        : route('user.workflows.actions.store', [$workflow, $stage]);
@endphp

<div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4"
     x-data="{
         actionType: '{{ $isEdit ? $actionInstance->action_type : \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}',
         assigneeTarget: '{{ $cfg['assignee_target'] ?? 'CURRENT_USER' }}'
     }">

    <form method="post" action="{{ $formAction }}" class="space-y-4">
        @csrf
        @if($isEdit)
            @method('patch')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">نوع اکشن</label>
                <select name="action_type" x-model="actionType"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
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
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">ترتیب</label>
                <input type="number" name="sort_order" value="{{ $isEdit ? $actionInstance->sort_order : 0 }}" min="0"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
            </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700 border-dashed">

        {{-- SMS Fields --}}
        <div class="space-y-4" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">الگوی پیامک (Pattern)</label>
                    <input type="text" name="config[pattern_key]" value="{{ $cfg['pattern_key'] ?? '' }}" placeholder="مثال: 234"
                           :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">هدف ارسال</label>
                    <select name="config[target]"
                            :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="APPOINTMENT_CLIENT" @selected(($cfg['target'] ?? '') === 'APPOINTMENT_CLIENT')>مشتری نوبت</option>
                        <option value="CUSTOM_PHONE" @selected(($cfg['target'] ?? '') === 'CUSTOM_PHONE')>شماره دلخواه</option>
                    </select>
                    <input type="text" name="config[phone]" value="{{ $cfg['phone'] ?? '' }}" placeholder="شماره دلخواه"
                           :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                           class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاخیر ارسال (دقیقه)</label>
                    <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}"
                           :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">نسبت به زمان شروع نوبت. منفی برای قبل از نوبت.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">متن پیام (اگر الگو ندارید)</label>
                    <textarea name="config[message]" rows="3"
                              :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white"
                              placeholder="سلام {client_name} عزیز ...">{{ $cfg['message'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مقادیر الگو / پارامترها (ترتیبی)</label>
                    <div class="grid grid-cols-2 gap-2 p-2 border border-gray-200 dark:border-gray-700 rounded-md">
                        @foreach($tokenOptions as $tokenKey => $tokenLabel)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="config[params][]" value="{{ $tokenKey }}"
                                       :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                       @checked(in_array($tokenKey, $cfg['params'] ?? [])) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                                {{ $tokenLabel }}
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">ترتیب انتخاب‌شده معادل {0}، {1} و ... در الگو است.</p>
                </div>
            </div>
        </div>

        {{-- Task/FollowUp Fields --}}
        <div class="space-y-4" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">عنوان</label>
                    <input type="text" name="config[title]" value="{{ $cfg['title'] ?? '' }}"
                           :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">منتسب به</label>
                    <select name="config[assignee_target]" x-model="assigneeTarget"
                            :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="CURRENT_USER">کاربر فعلی</option>
                        <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                        <option value="SPECIFIC_USER">کاربر خاص</option>
                    </select>
                    <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                        <select name="config[assignee_id]"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="">انتخاب کاربر...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاخیر ایجاد (روز)</label>
                    <input type="number" name="config[offset_days]" value="{{ $cfg['offset_days'] ?? '0' }}"
                           :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">نسبت به زمان شروع نوبت.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">اولویت</label>
                    <select name="config[priority]"
                            :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="LOW" @selected(($cfg['priority'] ?? '') === 'LOW')>کم</option>
                        <option value="MEDIUM" @selected(($cfg['priority'] ?? 'MEDIUM') === 'MEDIUM')>معمولی</option>
                        <option value="HIGH" @selected(($cfg['priority'] ?? '') === 'HIGH')>زیاد</option>
                        <option value="CRITICAL" @selected(($cfg['priority'] ?? '') === 'CRITICAL')>بحرانی</option>
                    </select>
                </div>
                 <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</label>
                    <select name="config[status]"
                            :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="TODO" @selected(($cfg['status'] ?? 'TODO') === 'TODO')>در صف انجام</option>
                        <option value="IN_PROGRESS" @selected(($cfg['status'] ?? '') === 'IN_PROGRESS')>در حال انجام</option>
                        <option value="DONE" @selected(($cfg['status'] ?? '') === 'DONE')>انجام شده</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">توضیحات</label>
                <textarea name="config[description]" rows="3"
                          :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">{{ $cfg['description'] ?? '' }}</textarea>
            </div>
        </div>

        {{-- Reminder Fields --}}
        <div class="space-y-4" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">منتسب به</label>
                    <select name="config[assignee_target]" x-model="assigneeTarget"
                            :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="CURRENT_USER">کاربر فعلی</option>
                        <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                        <option value="SPECIFIC_USER">کاربر خاص</option>
                    </select>
                    <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                        <select name="config[assignee_id]"
                                :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="">انتخاب کاربر...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاخیر یادآوری (دقیقه)</label>
                    <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}"
                           :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">نسبت به زمان شروع نوبت. منفی برای قبل از نوبت.</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">پیام یادآوری</label>
                <textarea name="config[message]" rows="3"
                          :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_REMINDER }}'"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">{{ $cfg['message'] ?? '' }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-800">
                {{ $isEdit ? 'ذخیره اکشن' : 'افزودن اکشن' }}
            </button>
            @if($isEdit)
                <button type="submit" form="delete-action-{{ $actionInstance->id }}"
                        class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        onclick="return confirm('آیا از حذف این اکشن اطمینان دارید؟');">
                    حذف
                </button>
            @endif
        </div>
    </form>

    @if($isEdit)
        <form id="delete-action-{{ $actionInstance->id }}" method="post" action="{{ route('user.workflows.actions.destroy', [$workflow, $stage, $actionInstance]) }}" class="hidden">
            @csrf
            @method('delete')
        </form>
    @endif
</div>
