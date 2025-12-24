<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientStatus; // Import ClientStatus

class ClientProfileService
{
    /**
     * Create or update a client for online booking.
     *
     * $clientInput keys:
     * - full_name (required for create)
     * - phone (recommended)
     * - email, national_code (optional)
     * - meta (array) additional profile fields (stored in clients.meta)
     *
     * NOTE: This method follows the Clients module username strategy settings as much as possible.
     */
    public function resolveOrCreateClient(array $clientInput, ?int $existingClientId = null, ?int $creatorUserId = null): Client
    {
        $client = null;

        if ($existingClientId) {
            $client = Client::query()->find($existingClientId);
        }

        // Try to find by username/phone if exists
        if (!$client) {
            $username = $clientInput['username'] ?? null;
            if ($username) {
                $client = Client::query()->where('username', $username)->first();
            }
        }

        if (!$client) {
            $phoneDigits = preg_replace('/\D+/', '', (string) ($clientInput['phone'] ?? ''));
            if ($phoneDigits) {
                // In many projects username_strategy=mobile, so phone can be username
                $client = Client::query()->where('phone', $phoneDigits)->orWhere('username', $phoneDigits)->first();
            }
        }

        $payload = [];

        foreach (['full_name', 'email', 'phone', 'national_code'] as $k) {
            if (array_key_exists($k, $clientInput) && $clientInput[$k] !== null && $clientInput[$k] !== '') {
                if ($k === 'phone') {
                    $payload[$k] = preg_replace('/\D+/', '', (string) $clientInput[$k]);
                } else {
                    $payload[$k] = $clientInput[$k];
                }
            }
        }

        $meta = $clientInput['meta'] ?? ($clientInput['profile_data_json'] ?? null);
        if (is_array($meta)) {
            $payload['meta'] = array_merge($client?->meta ?? [], $meta);
        }

        if ($client && $client->exists) {
            $client->fill($payload);
            $client->save();

            return $client;
        }

        // Create new client
        if (empty($payload['full_name'])) {
            throw new \InvalidArgumentException('full_name is required to create client');
        }

        $username = $clientInput['username'] ?? null;
        if (!$username) {
            $username = $this->generateUsername($payload);
        }

        $createPayload = array_merge($payload, [
            'username' => $username,
        ]);

        if ($creatorUserId) {
            $createPayload['created_by'] = $creatorUserId;
        }

        // Set default status for new clients
        $defaultStatus = ClientStatus::query()->where('key', 'new')->first();
        if ($defaultStatus) {
            $createPayload['status_id'] = $defaultStatus->id;
        }


        if (!empty($clientInput['password'])) {
            $createPayload['password'] = Hash::make((string) $clientInput['password']);
        }

        return Client::query()->create($createPayload);
    }

    public function generateUsername(array $input): string
    {
        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        $prefix = ClientSetting::getValue('username_prefix', 'clt');

        $existsInClients = fn(string $u) => DB::table('clients')->where('username', $u)->exists();

        $candidate = null;

        switch ($strategy) {
            case 'email':
                $candidate = (string) ($input['email'] ?? '');
                break;

            case 'national_code':
                $candidate = (string) ($input['national_code'] ?? '');
                break;

            case 'mobile':
                $digits = preg_replace('/\D+/', '', (string) ($input['phone'] ?? ''));
                $candidate = $digits ?: null;
                break;

            case 'prefix_increment':
                $last = DB::table('clients')
                    ->where('username', 'like', "{$prefix}-%")
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(username, '-', -1) AS UNSIGNED)) as mx")
                    ->value('mx');
                $next = (int) $last + 1;
                $candidate = sprintf('%s-%04d', $prefix, $next);
                break;

            case 'name_increment':
                $base = Str::slug((string) ($input['full_name'] ?? 'user'));
                $candidate = $this->incrementUsernameBase($base ?: 'user', $existsInClients);
                break;

            case 'email_local':
            default:
                $local = (string) Str::before((string) ($input['email'] ?? ''), '@');
                $base  = Str::slug($local ?: (string) ($input['full_name'] ?? 'user')) ?: 'user';
                $candidate = $this->incrementUsernameBase($base, $existsInClients);
                break;
        }

        // For strict strategies, if candidate empty, fallback to prefix_increment
        if (in_array($strategy, ['email', 'mobile', 'national_code'], true)) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                $candidate = $this->incrementUsernameBase($prefix, $existsInClients);
            }
            // Ensure unique
            if ($existsInClients($candidate)) {
                $candidate = $this->incrementUsernameBase($candidate, $existsInClients);
            }
            return $candidate;
        }

        if ($candidate === null || $candidate === '') {
            $candidate = $this->incrementUsernameBase($prefix, $existsInClients);
        }

        if ($existsInClients($candidate)) {
            $candidate = $this->incrementUsernameBase($candidate, $existsInClients);
        }

        return (string) $candidate;
    }

    protected function incrementUsernameBase(string $base, \Closure $exists): string
    {
        $base = trim($base) ?: 'user';
        if (!$exists($base)) return $base;

        $i = 1;
        while ($exists($base . $i)) $i++;
        return $base . $i;
    }
}
