<?php

namespace Modules\Clients\App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Clients\Entities\ClientSetting;

class UsernameGenerator
{
    public static function generate(array $input): string
    {
        $strategy = ClientSetting::getValue('username.strategy', 'email_local');
        $prefix   = ClientSetting::getValue('username.prefix', 'user');

        $candidate = match ($strategy) {
            'email_local'       => self::emailLocal($input['email'] ?? null) ?? $prefix,
            'mobile'            => $input['phone'] ?? $prefix,
            'name_rand'         => Str::slug(($input['name'] ?? $prefix), '_') . '_' . Str::random(4),
            'prefix_incremental'=> $prefix,
            default             => $prefix,
        };

        return self::unique($candidate, $prefix);
    }

    protected static function emailLocal(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) return null;
        return Str::slug(strstr($email, '@', true), '_');
    }

    protected static function unique(string $base, string $prefix): string
    {
        $u = $base;
        $i = 1;
        while (User::where('username', $u)->exists()) {
            $i++;
            $u = $base === $prefix ? "{$prefix}{$i}" : "{$base}_{$i}";
        }
        return $u;
    }
}
