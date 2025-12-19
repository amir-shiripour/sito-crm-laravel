<?php

use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,

    // مهم: مسیر پیش‌فرض بعد از لاگین برای کاربران عادی (غیر super-admin)
    'home' => '/user/dashboard',

    'prefix' => '',
    'middleware' => ['web'],
    'auth_middleware' => 'auth',

    'features' => [
        // Features::registration(), // <-- این خط را کامنت کنید تا ثبت‌نام عمومی غیرفعال شود
        Features::resetPasswords(),
        // Features::emailVerification(), // <-- این را هم می‌توانید فعلاً کامنت کنید
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0,
        ]),
    ],

    'pipelines' => [
        'login' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
        ],

        'register' => [
            \Laravel\Fortify\Actions\CreateNewUser::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
            \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
        ],

        'password.request' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'password.reset' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'profile.update' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'password.confirm' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'two-factor.enable' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'two-factor.confirm' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'two-factor.disable' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'two-factor.generate-recovery-codes' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],

        'two-factor.view-recovery-codes' => [
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
        ],
    ],
];

