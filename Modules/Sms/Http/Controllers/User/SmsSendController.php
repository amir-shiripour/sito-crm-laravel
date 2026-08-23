<?php

namespace Modules\Sms\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Services\SmsManager;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientStatus;
use Spatie\Permission\Models\Role;

class SmsSendController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        // کاربران و نقش‌ها
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        // مشتریان (در صورت فعال بودن ماژول Clients و داشتن پرمیژن)
        $clients          = collect();
        $clientStatuses   = collect();
        $canTargetClients = false;

        if (class_exists(Client::class) && $user && $user->can('clients.view')) {
            $clients = Client::query()
                ->visibleForUser($user)
                ->with('status')
                ->orderBy('full_name')
                ->get();

            $clientStatuses   = ClientStatus::active()->get();
            $canTargetClients = true;
        }

        $canTargetUsers = true;

        $sampleUser = $users->first(fn($u) => !empty($u->mobile) || !empty($u->phone)) ?? $users->first();
        $sampleClient = $clients->first(fn($c) => !empty($c->phone)) ?? $clients->first();

        $sampleUserData = [
            'name'          => $sampleUser?->name ?? 'علی محمدی',
            'username'      => $sampleUser?->username ?? 'alimohammadi',
            'national_code' => $sampleUser?->national_code ?? '0012345678',
            'phone'         => $sampleUser?->mobile ?? $sampleUser?->phone ?? '09123456789',
            'email'         => $sampleUser?->email ?? 'ali@example.com',
            'role'          => $sampleUser && method_exists($sampleUser, 'roles') && $sampleUser->roles->isNotEmpty()
                ? $sampleUser->roles->pluck('name')->implode(', ')
                : 'مدیر سیستم',
        ];

        $sampleClientStatus = '';
        if ($sampleClient && $sampleClient->relationLoaded('status') && $sampleClient->status) {
            $sampleClientStatus = $sampleClient->status->label ?? $sampleClient->status->key ?? '';
        }

        $sampleClientData = [
            'full_name'     => $sampleClient?->full_name ?? 'رضا رضایی',
            'username'      => $sampleClient?->username ?? 'reza_rezaei',
            'national_code' => $sampleClient?->national_code ?? '0087654321',
            'phone'         => $sampleClient?->phone ?? '09351234567',
            'email'         => $sampleClient?->email ?? 'reza@example.com',
            'status'        => $sampleClientStatus ?: 'مشتری فعال',
            'case_number'   => $sampleClient?->case_number ?? 'CR-10492',
        ];

        return view('sms::user.logs.send', [
            'users'            => $users,
            'roles'            => $roles,
            'clients'          => $clients,
            'clientStatuses'   => $clientStatuses,
            'canTargetUsers'   => $canTargetUsers,
            'canTargetClients' => $canTargetClients,
            'sampleUserData'   => $sampleUserData,
            'sampleClientData' => $sampleClientData,
        ]);
    }

    public function store(Request $request, SmsManager $sms)
    {
        $data = $request->validate([
            'target_type'      => ['required', 'in:users,clients'],
            'recipient_scope'  => ['required', 'in:filters,selected'],
            'type'             => ['required', 'in:manual,scheduled'],
            'scheduled_at'     => ['nullable', 'date'],

            // گیرندگان کاربران
            'user_role_ids'    => ['array'],
            'user_role_ids.*'  => ['string'],
            'user_ids'         => ['array'],
            'user_ids.*'       => ['integer'],

            // گیرندگان مشتریان
            'client_status_ids'   => ['array'],
            'client_status_ids.*' => ['string'],
            'client_ids'          => ['array'],
            'client_ids.*'        => ['integer'],

            // متن / پترن
            'pattern'          => ['nullable', 'string', 'max:191'],
            'body'             => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $baseOptions = [
            'type'       => $data['type'],
            'created_by' => optional($user)->id,
        ];

        if ($data['type'] === SmsMessage::TYPE_SCHEDULED && $request->filled('scheduled_at')) {
            $baseOptions['scheduled_at'] = $request->date('scheduled_at');
        }

        $patternKey = $data['pattern'] ?? null;
        $body       = (string) ($data['body'] ?? '');
        $scope      = $data['recipient_scope'];

        // ۱) محاسبه لیست گیرندگان
        if ($data['target_type'] === 'users') {
            $recipients  = $this->resolveUserRecipients($data, $scope);
            $relatedType = 'USER';
        } else {
            $recipients  = $this->resolveClientRecipients($data, $scope, $user);
            $relatedType = 'CLIENT';
        }

        if ($recipients->isEmpty()) {
            $msg = $scope === 'filters'
                ? 'هیچ گیرنده‌ای مطابق فیلترهای انتخابی پیدا نشد یا فیلترها خالی هستند.'
                : 'هیچ گیرنده‌ای در لیست انتخاب نشده است.';
            return back()
                ->withErrors(['recipients' => $msg])
                ->withInput();
        }

        $messagesCount = 0;

        foreach ($recipients as $target) {
            $phone = $target->phone ?? $target->mobile ?? null;
            if (! $phone) {
                continue;
            }

            $options = $baseOptions + [
                    'related_type' => $relatedType,
                    'related_id'   => $target->id,
                    'meta'         => [
                        'target_type'      => $relatedType,
                        'target_id'        => $target->id,
                        'recipient_scope'  => $scope,
                    ],
                ];

            if ($patternKey) {
                $tokens = $data['target_type'] === 'users'
                    ? $this->buildUserTokens($target)
                    : $this->buildClientTokens($target);

                $sms->sendPattern($phone, $patternKey, $tokens, $options);
            } else {
                $personalizedBody = $this->replaceTextVariables($body, $target, $data['target_type']);
                $sms->sendText($phone, $personalizedBody, $options);
            }

            $messagesCount++;
        }

        return redirect()
            ->route('user.sms.logs.index')
            ->with('status', "پیامک برای {$messagesCount} گیرنده در صف ارسال قرار گرفت.");
    }

    /**
     * جایگزینی متغیرهای داینامیک در متن پیامک عادی
     */
    public function replaceTextVariables(string $text, $target, string $targetType): string
    {
        if (trim($text) === '') {
            return '';
        }

        if ($targetType === 'users') {
            $name     = (string) ($target->name ?? '');
            $username = (string) ($target->username ?? $target->name ?? '');
            $national = (string) ($target->national_code ?? '');
            $phone    = (string) ($target->mobile ?? $target->phone ?? '');
            $email    = (string) ($target->email ?? '');

            $roles = '';
            if (method_exists($target, 'roles')) {
                $roles = method_exists($target, 'relationLoaded') && $target->relationLoaded('roles')
                    ? $target->roles->pluck('name')->implode(', ')
                    : $target->roles()->pluck('name')->implode(', ');
            }

            $map = [
                '{0}'             => $name,
                '{1}'             => $username,
                '{2}'             => $national,
                '{3}'             => $phone,
                '{4}'             => $email,
                '{5}'             => $roles,
                '{name}'          => $name,
                '{full_name}'     => $name,
                '{نام}'           => $name,
                '{نام_خانوادگی}'  => $name,
                '{نام_کامل}'      => $name,
                '{username}'      => $username,
                '{نام_کاربری}'    => $username,
                '{national_code}' => $national,
                '{کد_ملی}'        => $national,
                '{phone}'         => $phone,
                '{mobile}'        => $phone,
                '{موبایل}'        => $phone,
                '{تلفن}'          => $phone,
                '{شماره_موبایل}'  => $phone,
                '{email}'         => $email,
                '{ایمیل}'         => $email,
                '{roles}'         => $roles,
                '{role}'          => $roles,
                '{نقش}'           => $roles,
            ];
        } else {
            $name       = (string) ($target->full_name ?? '');
            $username   = (string) ($target->username ?? '');
            $national   = (string) ($target->national_code ?? '');
            $phone      = (string) ($target->phone ?? $target->mobile ?? '');
            $email      = (string) ($target->email ?? '');
            $caseNumber = (string) ($target->case_number ?? '');

            $statusLabel = '';
            if (method_exists($target, 'relationLoaded') && $target->relationLoaded('status') && $target->status) {
                $statusLabel = $target->status->label ?? $target->status->key ?? $target->status->name ?? '';
            } elseif (method_exists($target, 'status') && !empty($target->status_id)) {
                $statusLabel = $target->status?->label ?? $target->status?->key ?? $target->status?->name ?? '';
            } elseif (isset($target->status) && is_object($target->status)) {
                $statusLabel = $target->status->label ?? $target->status->key ?? $target->status->name ?? '';
            } elseif (isset($target->status) && is_string($target->status)) {
                $statusLabel = $target->status;
            }

            $map = [
                '{0}'             => $name,
                '{1}'             => $username,
                '{2}'             => $national,
                '{3}'             => $phone,
                '{4}'             => $email,
                '{5}'             => $statusLabel,
                '{full_name}'     => $name,
                '{name}'          => $name,
                '{نام}'           => $name,
                '{نام_خانوادگی}'  => $name,
                '{نام_کامل}'      => $name,
                '{username}'      => $username,
                '{نام_کاربری}'    => $username,
                '{national_code}' => $national,
                '{کد_ملی}'        => $national,
                '{phone}'         => $phone,
                '{mobile}'        => $phone,
                '{موبایل}'        => $phone,
                '{تلفن}'          => $phone,
                '{شماره_موبایل}'  => $phone,
                '{email}'         => $email,
                '{ایمیل}'         => $email,
                '{status}'        => $statusLabel,
                '{وضعیت}'         => $statusLabel,
                '{case_number}'   => $caseNumber,
                '{شماره_پرونده}'  => $caseNumber,
            ];
        }

        return str_replace(array_keys($map), array_values($map), $text);
    }

    /**
     * محاسبه گیرندگان کاربران بر اساس scope:
     * - filters  → فقط بر اساس نقش‌ها
     * - selected → فقط بر اساس user_ids
     */
    protected function resolveUserRecipients(array $data, string $scope): Collection
    {
        $roleIds = collect($data['user_role_ids'] ?? [])
            ->filter(fn($v) => $v !== '__all__')
            ->map(fn($v) => (int) $v)
            ->all();

        $userIds = collect($data['user_ids'] ?? [])
            ->map(fn($v) => (int) $v)
            ->all();

        $query = User::query()->whereNotNull('mobile');

        if ($scope === 'filters') {
            if (empty($roleIds)) {
                return collect();
            }

            $query->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('id', $roleIds);
            });

            return $query->get();
        }

        // scope = selected
        if (empty($userIds)) {
            return collect();
        }

        return $query->whereIn('id', $userIds)->get();
    }

    /**
     * محاسبه گیرندگان مشتریان بر اساس scope:
     * - filters  → فقط بر اساس status‌ها
     * - selected → فقط بر اساس client_ids
     */
    protected function resolveClientRecipients(array $data, string $scope, ?User $currentUser): Collection
    {
        if (! class_exists(Client::class)) {
            return collect();
        }

        $statusIds = collect($data['client_status_ids'] ?? [])
            ->filter(fn($v) => $v !== '__all__')
            ->map(fn($v) => (int) $v)
            ->all();

        $clientIds = collect($data['client_ids'] ?? [])
            ->map(fn($v) => (int) $v)
            ->all();

        $query = Client::query()
            ->with('status')
            ->whereNotNull('phone');

        if ($currentUser) {
            $query->visibleForUser($currentUser);
        }

        if ($scope === 'filters') {
            if (empty($statusIds)) {
                return collect();
            }

            $query->whereIn('status_id', $statusIds);

            return $query->get();
        }

        // scope = selected
        if (empty($clientIds)) {
            return collect();
        }

        return $query->whereIn('id', $clientIds)->get();
    }

    /**
     * مپینگ اطلاعات کاربر → آرایه ReplaceToken برای لیمو
     *
     * {0} = full name
     * {1} = username
     * {2} = national code
     * {3} = phone
     * {4} = email
     * {5} = roles (comma separated)
     */
    protected function buildUserTokens(User $user): array
    {
        $fullName = $user->name ?? '';
        $username = $user->username ?? '';
        $national = $user->national_code ?? '';
        $phone    = $user->mobile ?? '';
        $email    = $user->email ?? '';

        $roles = method_exists($user, 'roles')
            ? $user->roles->pluck('name')->implode(', ')
            : '';

        return [
            $fullName,
            $username,
            $national,
            $phone,
            $email,
            $roles,
        ];
    }

    /**
     * مپینگ اطلاعات مشتری → آرایه ReplaceToken برای لیمو
     *
     * {0} = full_name
     * {1} = username
     * {2} = national_code
     * {3} = phone
     * {4} = email
     * {5} = status (label/key)
     */
    protected function buildClientTokens(Client $client): array
    {
        $fullName = $client->full_name ?? '';
        $username = $client->username ?? '';
        $national = $client->national_code ?? '';
        $phone    = $client->phone ?? '';
        $email    = $client->email ?? '';

        $statusLabel = '';
        if ($client->relationLoaded('status') && $client->status) {
            $statusLabel = $client->status->label ?? $client->status->key ?? '';
        }

        return [
            $fullName,
            $username,
            $national,
            $phone,
            $email,
            $statusLabel,
        ];
    }
}
