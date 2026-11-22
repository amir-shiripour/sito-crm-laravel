<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\CalendarEventSmsLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use Spatie\Permission\Models\Role;

class CalendarEventSmsService
{
    public function __construct(
        protected CalendarEventService $calendarService
    ) {}

    /**
     * لیست توکن‌های پویای قابل استفاده در متن یا پارامترهای پترن پیامک رویداد
     */
    public function getAvailableTokens(): array
    {
        return [
            ['key' => 'event_title', 'label' => 'عنوان رویداد'],
            ['key' => 'event_date_fa', 'label' => 'تاریخ شمسی رویداد'],
            ['key' => 'event_time', 'label' => 'ساعت رویداد'],
            ['key' => 'event_start_time', 'label' => 'ساعت شروع'],
            ['key' => 'event_end_time', 'label' => 'ساعت پایان'],
            ['key' => 'event_location', 'label' => 'مکان رویداد'],
            ['key' => 'event_description', 'label' => 'توضیحات رویداد'],
            ['key' => 'creator_name', 'label' => 'نام ثبت‌کننده'],
            ['key' => 'recipient_name', 'label' => 'نام گیرنده'],
        ];
    }

    /**
     * دریافت گزینه‌های سبک اولیه فرم تنظیمات پیامک برای مودال تقویم (بدون بارگذاری لیست کامل کاربران و مشتریان)
     */
    public function getFormOptions(User $user, array $selectedUserIds = [], array $selectedClientIds = []): array
    {
        if (!$this->calendarService->isSmsWorkflowAvailable()) {
            return [
                'available'             => false,
                'roles'                 => [],
                'can_target_clients'    => false,
                'client_label_singular' => 'مشتری',
                'client_label_plural'   => 'مشتریان',
                'client_statuses'       => [],
                'templates'             => [],
                'tokens'                => [],
                'selected_users'        => [],
                'selected_clients'      => [],
            ];
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        $roles = [];
        if (class_exists(Role::class)) {
            $rolesQuery = Role::query()->orderBy('name');
            if (!$isSuperAdmin) {
                $rolesQuery->where('name', '!=', 'super-admin');
            }
            $roles = $rolesQuery->get(['id', 'name'])->toArray();
        }

        $selectedUsers = [];
        $cleanUserIds = array_values(array_unique(array_filter(array_map('intval', $selectedUserIds))));
        if (!empty($cleanUserIds)) {
            $uQuery = User::query()->whereIn('id', $cleanUserIds)->select(['id', 'name', 'mobile']);
            if (!$isSuperAdmin) {
                $uQuery->whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'));
            }
            $selectedUsers = $uQuery->get()->map(fn(User $u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'phone' => $this->normalizePhone($u->mobile ?? ''),
            ])->values()->all();
        }

        $canTargetClients = false;
        $clientStatuses = [];
        $selectedClients = [];
        $clientLabelSingular = config('clients.labels.singular', 'مشتری');
        $clientLabelPlural   = config('clients.labels.plural', 'مشتریان');

        if (
            class_exists(\Modules\Clients\Entities\Client::class)
            && Schema::hasTable('clients')
            && $user->can('clients.view')
        ) {
            $canTargetClients = true;

            if (class_exists(\Modules\Clients\Entities\ClientStatus::class) && Schema::hasTable('client_statuses')) {
                $clientStatuses = \Modules\Clients\Entities\ClientStatus::query()
                    ->active()
                    ->get(['id', 'label', 'key'])
                    ->toArray();
            }

            $cleanClientIds = array_values(array_unique(array_filter(array_map('intval', $selectedClientIds))));
            if (!empty($cleanClientIds)) {
                $selectedClients = \Modules\Clients\Entities\Client::query()
                    ->visibleForUser($user)
                    ->whereIn('id', $cleanClientIds)
                    ->select(['id', 'full_name', 'phone', 'national_code', 'case_number'])
                    ->get()
                    ->map(fn($c) => [
                        'id'            => $c->id,
                        'name'          => $c->full_name,
                        'phone'         => $this->normalizePhone($c->phone ?? ''),
                        'national_code' => $c->national_code ?? '',
                        'case_number'   => $c->case_number ?? '',
                    ])
                    ->values()
                    ->all();
            }
        }

        $templates = [];
        if (class_exists(\Modules\Sms\Entities\SmsTemplate::class) && Schema::hasTable('sms_templates')) {
            $templates = \Modules\Sms\Entities\SmsTemplate::query()
                ->active()
                ->latest('id')
                ->get(['id', 'key', 'title', 'body', 'provider_pattern', 'type'])
                ->toArray();
        }

        return [
            'available'             => true,
            'roles'                 => $roles,
            'can_target_clients'    => $canTargetClients,
            'client_label_singular' => $clientLabelSingular,
            'client_label_plural'   => $clientLabelPlural,
            'client_statuses'       => $clientStatuses,
            'templates'             => $templates,
            'tokens'                => $this->getAvailableTokens(),
            'selected_users'        => $selectedUsers,
            'selected_clients'      => $selectedClients,
        ];
    }

    /**
     * جستجوی زنده کاربران یا مشتریان (حداقل ۳ کاراکتر)
     */
    public function searchRecipients(User $user, string $type, string $query): array
    {
        if (!$this->calendarService->isSmsWorkflowAvailable()) {
            return [];
        }

        $rawQuery = trim($query);
        if (mb_strlen($rawQuery) < 3) {
            return [];
        }

        $normQuery = $this->normalizeDigits($rawQuery);
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if ($type === 'users') {
            $uQuery = User::query()
                ->select(['id', 'name', 'mobile'])
                ->where(function ($q) use ($rawQuery, $normQuery) {
                    $q->where('name', 'like', "%{$rawQuery}%")
                      ->orWhere('mobile', 'like', "%{$normQuery}%");
                })
                ->orderBy('name')
                ->limit(20);

            if (!$isSuperAdmin) {
                $uQuery->whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'));
            }

            return $uQuery->get()
                ->map(fn(User $u) => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'phone' => $this->normalizePhone($u->mobile ?? ''),
                ])
                ->values()
                ->all();
        }

        if (
            $type === 'clients'
            && class_exists(\Modules\Clients\Entities\Client::class)
            && Schema::hasTable('clients')
            && $user->can('clients.view')
        ) {
            return \Modules\Clients\Entities\Client::query()
                ->visibleForUser($user)
                ->select(['id', 'full_name', 'phone', 'national_code', 'case_number'])
                ->where(function ($q) use ($rawQuery, $normQuery) {
                    $q->where('full_name', 'like', "%{$rawQuery}%")
                      ->orWhere('phone', 'like', "%{$normQuery}%")
                      ->orWhere('national_code', 'like', "%{$normQuery}%")
                      ->orWhere('case_number', 'like', "%{$normQuery}%");
                })
                ->orderBy('full_name')
                ->limit(20)
                ->get()
                ->map(fn($c) => [
                    'id'            => $c->id,
                    'name'          => $c->full_name,
                    'phone'         => $this->normalizePhone($c->phone ?? ''),
                    'national_code' => $c->national_code ?? '',
                    'case_number'   => $c->case_number ?? '',
                ])
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * نرمال‌سازی و پاک‌سازی ساختار sms_config پیش از ذخیره در دیتابیس
     */
    public function normalizeSmsConfig(?array $raw, ?User $actor = null): ?array
    {
        if (empty($raw) || empty($raw['enabled'])) {
            return null;
        }

        $allDaySendTime = $this->normalizeDigits((string)($raw['all_day_send_time'] ?? '09:00'));
        if (!preg_match('/^\d{1,2}:\d{2}$/', $allDaySendTime)) {
            $allDaySendTime = '09:00';
        } else {
            [$h, $m] = explode(':', $allDaySendTime);
            $h = max(0, min(23, (int)$h));
            $m = max(0, min(59, (int)$m));
            $allDaySendTime = sprintf('%02d:%02d', $h, $m);
        }

        $recipientsRaw = is_array($raw['recipients'] ?? null) ? $raw['recipients'] : [];
        $customPhonesRaw = $recipientsRaw['custom_phones'] ?? [];
        if (is_string($customPhonesRaw)) {
            $customPhonesRaw = preg_split('/[\s,،;\n]+/', $customPhonesRaw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $customPhones = [];
        foreach ((array)$customPhonesRaw as $phone) {
            $norm = $this->normalizePhone((string)$phone);
            if ($norm !== '') {
                $customPhones[] = $norm;
            }
        }
        $customPhones = array_values(array_unique($customPhones));

        $userIds = array_values(array_unique(array_filter(array_map('intval', (array)($recipientsRaw['user_ids'] ?? [])))));
        $roleIds = array_values(array_unique(array_filter(array_map('intval', (array)($recipientsRaw['role_ids'] ?? [])))));

        // جلوگیری از انتخاب نقش یا کاربران سوپرادمین توسط سایر کاربران
        $actorIsSuperAdmin = $actor && method_exists($actor, 'hasRole') && $actor->hasRole('super-admin');
        if ($actor && !$actorIsSuperAdmin) {
            if (!empty($userIds)) {
                $userIds = User::query()
                    ->whereIn('id', $userIds)
                    ->whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'))
                    ->pluck('id')
                    ->map(fn($id) => (int)$id)
                    ->values()
                    ->all();
            }
            if (!empty($roleIds) && class_exists(Role::class)) {
                $roleIds = Role::query()
                    ->whereIn('id', $roleIds)
                    ->where('name', '!=', 'super-admin')
                    ->pluck('id')
                    ->map(fn($id) => (int)$id)
                    ->values()
                    ->all();
            }
        }

        $recipients = [
            'include_creator'   => !empty($recipientsRaw['include_creator']),
            'user_ids'          => $userIds,
            'role_ids'          => $roleIds,
            'client_ids'        => array_values(array_unique(array_filter(array_map('intval', (array)($recipientsRaw['client_ids'] ?? []))))),
            'client_status_ids' => array_values(array_unique(array_filter(array_map('intval', (array)($recipientsRaw['client_status_ids'] ?? []))))),
            'custom_phones'     => $customPhones,
        ];

        $rules = [];
        foreach ((array)($raw['rules'] ?? []) as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $timing = in_array($rule['timing'] ?? '', ['on_create', 'at_start', 'before', 'after'], true)
                ? $rule['timing']
                : 'before';
            $unit = in_array($rule['offset_unit'] ?? '', ['minutes', 'hours', 'days'], true)
                ? $rule['offset_unit']
                : 'minutes';
            $value = max(0, (int)($rule['offset_value'] ?? 30));

            $rules[] = [
                'timing'       => $timing,
                'offset_value' => $timing === 'before' || $timing === 'after' ? $value : 0,
                'offset_unit'  => $unit,
            ];
        }

        if (empty($rules)) {
            $rules[] = [
                'timing'       => 'before',
                'offset_value' => 30,
                'offset_unit'  => 'minutes',
            ];
        }

        $mode = in_array($raw['mode'] ?? '', ['text', 'pattern', 'template'], true)
            ? $raw['mode']
            : 'text';

        $params = [];
        foreach ((array)($raw['params'] ?? []) as $param) {
            if (is_array($param)) {
                $params[] = [
                    'type'  => ($param['type'] ?? 'token') === 'static' ? 'static' : 'token',
                    'value' => (string)($param['value'] ?? ''),
                ];
            } elseif (is_string($param) && $param !== '') {
                $params[] = [
                    'type'  => 'token',
                    'value' => $param,
                ];
            }
        }

        return [
            'enabled'           => true,
            'all_day_send_time' => $allDaySendTime,
            'rules'             => array_values($rules),
            'recipients'        => $recipients,
            'mode'              => $mode,
            'template_id'       => !empty($raw['template_id']) ? (int)$raw['template_id'] : null,
            'pattern_key'       => trim((string)($raw['pattern_key'] ?? '')),
            'params'            => $params,
            'message'           => trim((string)($raw['message'] ?? '')),
        ];
    }

    /**
     * ارسال پیامک‌های فوری هنگام ثبت یا ویرایش رویداد (قوانین on_create)
     */
    public function dispatchOnCreate(CalendarEvent $event): int
    {
        if (!$this->calendarService->isSmsWorkflowAvailable()) {
            return 0;
        }

        $smsConfig = $event->sms_config;
        if (empty($smsConfig) || empty($smsConfig['enabled'])) {
            return 0;
        }

        $occurrenceDate = $event->start_time ? $event->start_time->toDateString() : now()->toDateString();
        $occurrence = [
            'occurrence_date' => $occurrenceDate,
            'start_dt'        => $event->start_time ?? now(),
            'end_dt'          => $event->end_time ?? now()->addHour(),
            'title'           => $event->title,
            'location'        => $event->location ?? '',
            'description'     => $event->description ?? '',
            'is_all_day'      => (bool)$event->is_all_day,
            'sms_config'      => $smsConfig,
        ];

        $sentCount = 0;
        foreach ((array)($smsConfig['rules'] ?? []) as $ruleIndex => $rule) {
            if (($rule['timing'] ?? '') !== 'on_create') {
                continue;
            }
            $sentCount += $this->dispatchRuleForOccurrence($event, $occurrence, $ruleIndex, $smsConfig);
        }

        return $sentCount;
    }

    /**
     * پردازش و ارسال پیامک‌های سررسیدشده برای رویدادهای یک‌باره و تکرارپذیر (اجرا در کرون‌جاب)
     */
    public function dispatchDueReminders(?Carbon $now = null): int
    {
        if (!$this->calendarService->isSmsWorkflowAvailable()) {
            return 0;
        }

        $now = $now ?? now();
        $events = CalendarEvent::query()
            ->with(['creator', 'user', 'exceptions'])
            ->where('status', 'active')
            ->whereNotNull('sms_config')
            ->get();

        $totalDispatched = 0;

        foreach ($events as $event) {
            $parentSmsConfig = $event->sms_config;
            if (empty($parentSmsConfig) || empty($parentSmsConfig['enabled'])) {
                continue;
            }

            // محاسبه پنجره زمانی مورد نیاز بر اساس بیشترین آفست قبل و بعد
            [$maxBeforeMinutes, $maxAfterMinutes] = $this->calculateMaxOffsets($event);
            $windowFrom = $now->copy()->subHours(24)->subMinutes($maxAfterMinutes)->startOfDay();
            $windowTo   = $now->copy()->addMinutes($maxBeforeMinutes)->endOfDay();

            $occurrences = $this->collectOccurrencesForDispatch($event, $windowFrom, $windowTo);

            foreach ($occurrences as $occ) {
                $occConfig = $occ['sms_config'] ?? $parentSmsConfig;
                if (empty($occConfig) || empty($occConfig['enabled'])) {
                    continue;
                }

                $baseTime = $this->resolveOccurrenceBaseTime($occ, $occConfig);

                foreach ((array)($occConfig['rules'] ?? []) as $ruleIndex => $rule) {
                    $timing = $rule['timing'] ?? 'before';
                    if ($timing === 'on_create') {
                        continue;
                    }

                    $signedOffset = $this->ruleToSignedMinutes($rule);
                    $targetSendAt = $baseTime->copy()->addMinutes($signedOffset);

                    // اگر زمان ارسال رسیده باشد و از ۲۴ ساعت گذشته قدیمی‌تر نباشد
                    if ($targetSendAt->lte($now) && $targetSendAt->gte($now->copy()->subHours(24))) {
                        $totalDispatched += $this->dispatchRuleForOccurrence($event, $occ, $ruleIndex, $occConfig);
                    }
                }
            }
        }

        return $totalDispatched;
    }

    /**
     * استخراج لیست مخاطبان یکتا با نرمال‌سازی شماره و حفظ نام گیرنده
     */
    public function resolveRecipients(CalendarEvent $event, array $recipientsConfig): array
    {
        $resolved = [];

        $addRecipient = function (?string $rawPhone, string $name, string $relatedType, ?int $relatedId) use (&$resolved) {
            $phone = $this->normalizePhone((string)$rawPhone);
            if ($phone === '') {
                return;
            }
            if (!isset($resolved[$phone])) {
                $resolved[$phone] = [
                    'phone'        => $phone,
                    'name'         => $name !== '' ? $name : 'کاربر گرامی',
                    'related_type' => $relatedType,
                    'related_id'   => $relatedId,
                ];
            }
        };

        // ۱. سازنده رویداد
        if (!empty($recipientsConfig['include_creator'])) {
            $creator = $event->creator ?? $event->user;
            if ($creator) {
                $addRecipient($creator->mobile ?? $creator->phone, $creator->name ?? '', 'USER', $creator->id);
            }
        }

        // ۲. کاربران خاص
        $userIds = array_filter(array_map('intval', (array)($recipientsConfig['user_ids'] ?? [])));
        if (!empty($userIds)) {
            $users = User::query()->whereIn('id', $userIds)->get(['id', 'name', 'mobile']);
            foreach ($users as $u) {
                $addRecipient($u->mobile ?? $u->phone ?? '', $u->name ?? '', 'USER', $u->id);
            }
        }

        // ۳. نقش‌های کاربری
        $roleIds = array_filter(array_map('intval', (array)($recipientsConfig['role_ids'] ?? [])));
        if (!empty($roleIds) && class_exists(Role::class)) {
            $roleUsers = User::query()
                ->whereHas('roles', fn($q) => $q->whereIn('roles.id', $roleIds))
                ->get(['id', 'name', 'mobile']);
            foreach ($roleUsers as $u) {
                $addRecipient($u->mobile ?? $u->phone ?? '', $u->name ?? '', 'USER', $u->id);
            }
        }

        // ۴. مشتریان خاص و وضعیت پرونده مشتریان
        if (class_exists(\Modules\Clients\Entities\Client::class) && Schema::hasTable('clients')) {
            $clientIds = array_filter(array_map('intval', (array)($recipientsConfig['client_ids'] ?? [])));
            if (!empty($clientIds)) {
                $clients = \Modules\Clients\Entities\Client::query()
                    ->whereIn('id', $clientIds)
                    ->get(['id', 'full_name', 'phone']);
                foreach ($clients as $c) {
                    $addRecipient($c->phone, $c->full_name ?? '', 'CLIENT', $c->id);
                }
            }

            $statusIds = array_filter(array_map('intval', (array)($recipientsConfig['client_status_ids'] ?? [])));
            if (!empty($statusIds)) {
                $statusClients = \Modules\Clients\Entities\Client::query()
                    ->whereIn('status_id', $statusIds)
                    ->get(['id', 'full_name', 'phone']);
                foreach ($statusClients as $c) {
                    $addRecipient($c->phone, $c->full_name ?? '', 'CLIENT', $c->id);
                }
            }
        }

        // ۵. شماره‌های دلخواه
        foreach ((array)($recipientsConfig['custom_phones'] ?? []) as $customPhone) {
            $addRecipient((string)$customPhone, 'مخاطب گرامی', 'CUSTOM', null);
        }

        return array_values($resolved);
    }

    /**
     * ارسال پیامک برای یک نمونه و یک قانون خاص با تضمین عدم ارسال تکراری
     */
    protected function dispatchRuleForOccurrence(
        CalendarEvent $event,
        array $occurrence,
        int $ruleIndex,
        array $smsConfig
    ): int {
        $recipients = $this->resolveRecipients($event, (array)($smsConfig['recipients'] ?? []));
        if (empty($recipients)) {
            return 0;
        }

        $smsManager = app(\Modules\Sms\Services\SmsManager::class);
        $occDate    = $occurrence['occurrence_date'];
        $mode       = $smsConfig['mode'] ?? 'text';
        $patternKey = trim((string)($smsConfig['pattern_key'] ?? ''));
        $messageTpl = (string)($smsConfig['message'] ?? '');

        // اگر حالت الگو انتخاب شده باشد، در صورت داشتن provider_pattern به صورت پترنی ارسال می‌شود
        if ($mode === 'template' && !empty($smsConfig['template_id']) && class_exists(\Modules\Sms\Entities\SmsTemplate::class)) {
            $tpl = \Modules\Sms\Entities\SmsTemplate::find($smsConfig['template_id']);
            if ($tpl) {
                if (!empty($tpl->provider_pattern) && $patternKey === '') {
                    $patternKey = trim((string)$tpl->provider_pattern);
                }
                if ($messageTpl === '') {
                    $messageTpl = (string)$tpl->body;
                }
            }
        }

        $usePattern = ($mode === 'pattern' || ($mode === 'template' && $patternKey !== '')) && $patternKey !== '';
        if (!$usePattern && trim($messageTpl) === '') {
            return 0;
        }

        $dispatched = 0;

        foreach ($recipients as $recipient) {
            $phone = $recipient['phone'];

            $alreadySent = CalendarEventSmsLog::query()
                ->where('calendar_event_id', $event->id)
                ->where('occurrence_date', $occDate)
                ->where('rule_index', $ruleIndex)
                ->where('recipient_phone', $phone)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $tokens = $this->buildOccurrenceTokens($event, $occurrence, $recipient['name']);

            $options = [
                'type'         => \Modules\Sms\Entities\SmsMessage::TYPE_SYSTEM,
                'related_type' => 'CALENDAR_EVENT',
                'related_id'   => $event->id,
                'created_by'   => $event->created_by ?? $event->user_id,
                'meta'         => [
                    'source'            => 'calendar_event',
                    'calendar_event_id' => $event->id,
                    'event_title'       => $occurrence['title'],
                    'occurrence_date'   => $occDate,
                    'rule_index'        => $ruleIndex,
                    'recipient_name'    => $recipient['name'],
                    'recipient_type'    => $recipient['related_type'],
                    'recipient_id'      => $recipient['related_id'],
                ],
            ];

            try {
                if ($usePattern) {
                    $params = $this->resolvePatternParams((array)($smsConfig['params'] ?? []), $tokens);
                    $sms = $smsManager->sendPattern($phone, $patternKey, $params, $options);
                } else {
                    $body = $this->renderText($messageTpl, $tokens);
                    $sms = $smsManager->sendText($phone, $body, $options);
                }

                CalendarEventSmsLog::create([
                    'calendar_event_id' => $event->id,
                    'occurrence_date'   => $occDate,
                    'rule_index'        => $ruleIndex,
                    'recipient_phone'   => $phone,
                    'sms_message_id'    => $sms->id ?? null,
                    'status'            => $sms->status ?? 'dispatched',
                    'sent_at'           => now(),
                ]);

                $dispatched++;
            } catch (\Throwable $e) {
                Log::error('[CalendarEventSmsService] Failed to dispatch event SMS', [
                    'calendar_event_id' => $event->id,
                    'occurrence_date'   => $occDate,
                    'phone'             => $phone,
                    'error'             => $e->getMessage(),
                ]);
            }
        }

        return $dispatched;
    }

    /**
     * جمع‌آوری نمونه‌های رویداد (یک‌باره یا تکرارپذیر) در بازه زمانی
     */
    protected function collectOccurrencesForDispatch(CalendarEvent $event, Carbon $windowFrom, Carbon $windowTo): array
    {
        if (!$event->isRecurring()) {
            $startDt = $event->start_time;
            if (!$startDt || $startDt->lt($windowFrom) || $startDt->gt($windowTo)) {
                return [];
            }

            return [[
                'occurrence_date' => $startDt->toDateString(),
                'start_dt'        => $startDt->copy(),
                'end_dt'          => $event->end_time ? $event->end_time->copy() : $startDt->copy()->addHour(),
                'title'           => $event->title,
                'location'        => $event->location ?? '',
                'description'     => $event->description ?? '',
                'is_all_day'      => (bool)$event->is_all_day,
                'sms_config'      => $event->sms_config,
            ]];
        }

        $items = $this->calendarService->expandOccurrencesInRange($event, $windowFrom, $windowTo, null);

        return $items->map(function (array $item) {
            $startDt = !empty($item['datetime'])
                ? Carbon::parse($item['datetime'])
                : Carbon::parse($item['occurrence_date']);

            return [
                'occurrence_date' => $item['occurrence_date'],
                'start_dt'        => $startDt,
                'end_dt'          => $startDt->copy()->addMinutes((int)($item['duration_minutes'] ?? 60)),
                'title'           => $item['title'] ?? '',
                'location'        => $item['location'] ?? '',
                'description'     => $item['description'] ?? '',
                'is_all_day'      => !empty($item['is_all_day']),
                'sms_config'      => $item['sms_config'] ?? null,
            ];
        })->values()->all();
    }

    /**
     * محاسبه زمان مبنا برای یک نمونه (با لحاظ ساعت تنظیم‌شده در رویداد تمام‌روز)
     */
    protected function resolveOccurrenceBaseTime(array $occurrence, array $smsConfig): Carbon
    {
        $startDt = $occurrence['start_dt']->copy();
        if (!empty($occurrence['is_all_day'])) {
            $sendTime = $smsConfig['all_day_send_time'] ?? '09:00';
            [$h, $m] = array_pad(explode(':', $sendTime), 2, '0');
            $startDt->setTime((int)$h, (int)$m, 0);
        }
        return $startDt;
    }

    protected function calculateMaxOffsets(CalendarEvent $event): array
    {
        $maxBefore = 0;
        $maxAfter  = 0;

        $configs = [$event->sms_config];
        foreach ($event->exceptions ?? [] as $exc) {
            if (!empty($exc->sms_config)) {
                $configs[] = $exc->sms_config;
            }
        }

        foreach ($configs as $cfg) {
            foreach ((array)($cfg['rules'] ?? []) as $rule) {
                $minutes = $this->ruleToSignedMinutes($rule);
                if ($minutes < 0) {
                    $maxBefore = max($maxBefore, abs($minutes));
                } elseif ($minutes > 0) {
                    $maxAfter = max($maxAfter, $minutes);
                }
            }
        }

        return [$maxBefore, $maxAfter];
    }

    protected function ruleToSignedMinutes(array $rule): int
    {
        $timing = $rule['timing'] ?? 'before';
        if ($timing === 'at_start' || $timing === 'on_create') {
            return 0;
        }

        $value = max(0, (int)($rule['offset_value'] ?? 0));
        $unit  = $rule['offset_unit'] ?? 'minutes';

        $minutes = match ($unit) {
            'hours' => $value * 60,
            'days'  => $value * 1440,
            default => $value,
        };

        return $timing === 'before' ? -$minutes : $minutes;
    }

    protected function buildOccurrenceTokens(CalendarEvent $event, array $occurrence, string $recipientName): array
    {
        $startDt = $occurrence['start_dt'];
        $endDt   = $occurrence['end_dt'];
        $isAllDay = !empty($occurrence['is_all_day']);

        $jalaliDate = Jalalian::fromCarbon($startDt)->format('Y/m/d');
        $startStr   = $isAllDay ? 'تمام روز' : $startDt->format('H:i');
        $endStr     = $isAllDay ? 'تمام روز' : $endDt->format('H:i');
        $timeStr    = $isAllDay ? 'تمام روز' : "{$startStr} تا {$endStr}";

        return [
            'event_title'       => (string)($occurrence['title'] ?? $event->title),
            'event_date_fa'     => $jalaliDate,
            'event_time'        => $timeStr,
            'event_start_time'  => $startStr,
            'event_end_time'    => $endStr,
            'event_location'    => (string)($occurrence['location'] ?? ''),
            'event_description' => (string)($occurrence['description'] ?? ''),
            'creator_name'      => (string)($event->creator?->name ?? $event->user?->name ?? ''),
            'recipient_name'    => $recipientName,
        ];
    }

    protected function resolvePatternParams(array $paramConfigs, array $tokens): array
    {
        $resolved = [];
        foreach ($paramConfigs as $item) {
            if (is_array($item)) {
                $type  = $item['type'] ?? 'token';
                $value = (string)($item['value'] ?? '');
                $resolved[] = $type === 'static' ? $value : ($tokens[$value] ?? $value);
            } else {
                $key = (string)$item;
                $resolved[] = $tokens[$key] ?? $key;
            }
        }
        return $resolved;
    }

    protected function renderText(string $template, array $tokens): string
    {
        foreach ($tokens as $key => $val) {
            $template = str_replace('{' . $key . '}', (string)$val, $template);
        }
        return $template;
    }

    public function normalizePhone(string $raw): string
    {
        $phone = $this->normalizeDigits(trim($raw));
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';

        if (str_starts_with($phone, '+98')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '0098')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '98') && strlen($phone) === 12) {
            $phone = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '9') && strlen($phone) === 10) {
            $phone = '0' . $phone;
        }

        if (!preg_match('/^09\d{9}$/', $phone) && !preg_match('/^\d{10,15}$/', $phone)) {
            return '';
        }

        return $phone;
    }

    protected function normalizeDigits(string $string): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabic, $english, str_replace($persian, $english, $string));
    }
}
