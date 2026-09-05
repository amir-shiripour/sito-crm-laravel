<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'install',
        'install/*',
        'settings/payment/verify/*',
        'booking/payment/verify/*',
        'market/checkout/callback',
        'market/checkout/callback/*',
        'client/portal/payment/verify/*',
        'payment/verify/*',
    ];
}
