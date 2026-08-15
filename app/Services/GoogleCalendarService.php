<?php

namespace App\Services;

use App\Models\GoogleCalendarToken;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Entities\Setting;

class GoogleCalendarService
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 15,
            'http_errors' => false,
        ]);
    }

    /**
     * دریافت Client ID (اول از .env و سپس از دیتابیس بدون رخداد خطای سیستم)
     */
    public function getClientId(): ?string
    {
        $fromEnv = config('google_calendar.client_id');
        if (!empty($fromEnv)) {
            return trim($fromEnv);
        }

        if (class_exists(Setting::class)) {
            try {
                $fromDb = Setting::where('key', 'google_client_id')->value('value');
                if (!empty($fromDb)) {
                    return trim($fromDb);
                }
            } catch (\Throwable $e) {
                // نادیده گرفتن خطاهای احتمالی دیتابیس
            }
        }

        return null;
    }

    /**
     * دریافت Client Secret (اول از .env و سپس از دیتابیس)
     */
    public function getClientSecret(): ?string
    {
        $fromEnv = config('google_calendar.client_secret');
        if (!empty($fromEnv)) {
            return trim($fromEnv);
        }

        if (class_exists(Setting::class)) {
            try {
                $fromDb = Setting::where('key', 'google_client_secret')->value('value');
                if (!empty($fromDb)) {
                    return trim($fromDb);
                }
            } catch (\Throwable $e) {
                // نادیده گرفتن خطاهای احتمالی دیتابیس
            }
        }

        return null;
    }

    /**
     * دریافت آدرس Redirect URI
     */
    public function getRedirectUri(): string
    {
        $redirect = config('google_calendar.redirect_uri');
        if (!empty($redirect)) {
            return $redirect;
        }

        return url('/settings/google-calendar/callback');
    }

    /**
     * آیا تنظیمات Client ID & Secret انجام شده است؟
     */
    public function isConfigured(): bool
    {
        return !empty($this->getClientId()) && !empty($this->getClientSecret());
    }

    /**
     * تولید آدرس Redirect OAuth برای لایت باکس یا صفحه گوگل
     */
    public function getAuthUrl(string $state = ''): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $params = [
            'client_id'     => $this->getClientId(),
            'redirect_uri'  => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope'         => implode(' ', config('google_calendar.scopes', [
                'https://www.googleapis.com/auth/calendar.readonly',
                'https://www.googleapis.com/auth/userinfo.email',
            ])),
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * تبادل Code با Access Token و Refresh Token و ذخیره آن
     */
    public function handleCallback(string $code, ?int $userId = null): ?GoogleCalendarToken
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->httpClient->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code'          => $code,
                    'client_id'     => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'redirect_uri'  => $this->getRedirectUri(),
                    'grant_type'    => 'authorization_code',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::error('Google OAuth token exchange failed: ' . $response->getBody());
                return null;
            }

            $data = json_decode((string) $response->getBody(), true);
            $accessToken  = $data['access_token'] ?? null;
            $refreshToken = $data['refresh_token'] ?? null;
            $expiresIn    = (int) ($data['expires_in'] ?? 3600);

            if (!$accessToken) {
                return null;
            }

            // دریافت ایمیل حساب متصل
            $email = $this->fetchUserEmail($accessToken);

            // غیرفعال کردن توکن‌های قبلی
            GoogleCalendarToken::where('is_active', true)->update(['is_active' => false]);

            $tokenRecord = GoogleCalendarToken::create([
                'email'            => $email ?? 'حساب گوگل',
                'access_token'     => $accessToken,
                'refresh_token'    => $refreshToken,
                'token_expires_at' => now()->addSeconds($expiresIn - 60),
                'calendar_ids'     => ['primary'],
                'is_active'        => true,
                'connected_by'     => $userId ?? auth()->id(),
            ]);

            return $tokenRecord;
        } catch (\Throwable $e) {
            Log::error('Google OAuth handleCallback error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تجدید خودکار Access Token انقضا یافته
     */
    public function refreshTokenIfExpired(GoogleCalendarToken $token): bool
    {
        if (!$token->token_expires_at || $token->token_expires_at->isFuture()) {
            return true;
        }

        if (empty($token->refresh_token)) {
            return false;
        }

        try {
            $response = $this->httpClient->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id'     => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'refresh_token' => $token->refresh_token,
                    'grant_type'    => 'refresh_token',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::error('Google OAuth token refresh failed: ' . $response->getBody());
                return false;
            }

            $data = json_decode((string) $response->getBody(), true);
            $newAccessToken = $data['access_token'] ?? null;
            $expiresIn     = (int) ($data['expires_in'] ?? 3600);

            if ($newAccessToken) {
                $token->access_token = $newAccessToken;
                $token->token_expires_at = now()->addSeconds($expiresIn - 60);
                $token->save();
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Google OAuth refresh error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت لیست تقویم‌های کاربر از حساب گوگل
     */
    public function listCalendars(GoogleCalendarToken $token): array
    {
        if (!$this->refreshTokenIfExpired($token)) {
            return [];
        }

        try {
            $response = $this->httpClient->get('https://www.googleapis.com/calendar/v3/users/me/calendarList', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Accept'        => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $data = json_decode((string) $response->getBody(), true);
            $items = $data['items'] ?? [];

            $result = [];
            foreach ($items as $item) {
                $result[] = [
                    'id'          => $item['id'] ?? '',
                    'summary'     => $item['summary'] ?? ($item['id'] ?? ''),
                    'description' => $item['description'] ?? '',
                    'primary'     => !empty($item['primary']),
                    'bg_color'    => $item['backgroundColor'] ?? '#0d9488',
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Google Calendar listCalendars error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * دریافت رویدادها از تقویم‌های گوگل با قابلیت Cache به مدت ۱۵ دقیقه
     */
    public function getEvents(GoogleCalendarToken $token, Carbon $from, Carbon $to): Collection
    {
        if (!$this->refreshTokenIfExpired($token)) {
            return collect();
        }

        $calendarIds = $token->calendar_ids ?: ['primary'];
        $cacheKey = 'gcal_events_' . $token->id . '_' . md5(implode(',', $calendarIds) . '_' . $from->format('Ymd') . '_' . $to->format('Ymd'));

        return Cache::remember($cacheKey, 900, function () use ($token, $calendarIds, $from, $to) {
            $allEvents = collect();

            foreach ($calendarIds as $calId) {
                try {
                    $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calId) . '/events';
                    $response = $this->httpClient->get($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token->access_token,
                            'Accept'        => 'application/json',
                        ],
                        'query' => [
                            'timeMin'      => $from->copy()->startOfDay()->toRfc3339String(),
                            'timeMax'      => $to->copy()->endOfDay()->toRfc3339String(),
                            'singleEvents' => 'true',
                            'orderBy'      => 'startTime',
                            'maxResults'   => 250,
                        ],
                    ]);

                    if ($response->getStatusCode() !== 200) {
                        continue;
                    }

                    $data = json_decode((string) $response->getBody(), true);
                    $items = $data['items'] ?? [];

                    foreach ($items as $item) {
                        $start = $item['start']['dateTime'] ?? $item['start']['date'] ?? null;
                        $end   = $item['end']['dateTime'] ?? $item['end']['date'] ?? null;

                        if (!$start) continue;

                        $dt = Carbon::parse($start);
                        $allEvents->push([
                            'id'          => $item['id'] ?? uniqid('gcal_'),
                            'summary'     => $item['summary'] ?? 'بدون عنوان',
                            'description' => $item['description'] ?? '',
                            'location'    => $item['location'] ?? '',
                            'start'       => $dt,
                            'html_link'   => $item['htmlLink'] ?? '#',
                            'calendar_id' => $calId,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Google Calendar fetch events error for cal [{$calId}]: " . $e->getMessage());
                }
            }

            return $allEvents;
        });
    }

    /**
     * قطع اتصال حساب گوگل فعلی
     */
    public function disconnect(?GoogleCalendarToken $token = null): bool
    {
        try {
            if (!$token) {
                $token = GoogleCalendarToken::where('is_active', true)->latest()->first();
            }

            if ($token) {
                $token->update(['is_active' => false]);
            }

            // پاکسازی تمام کش‌های رویداد گوگل
            Cache::flush();

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Calendar disconnect error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت ایمیل کاربر از Google UserInfo API
     */
    protected function fetchUserEmail(string $accessToken): ?string
    {
        try {
            $response = $this->httpClient->get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['email'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::error('Google fetchUserEmail error: ' . $e->getMessage());
        }

        return null;
    }
}
