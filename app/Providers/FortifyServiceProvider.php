<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\CustomUserField;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse; // اضافه شد
use Illuminate\Support\Facades\Auth; // اضافه شد
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\RegisteredUserController;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify::createUsersUsing(CreateNewUser::class); // We are overriding this
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Allow login with email or mobile
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)
                ->orWhere('mobile', 'like', '%' . $request->email)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Use our custom controller for registration views
        Fortify::registerView(function (Request $request) {
            $view = app(RegisteredUserController::class)->create($request);

            if ($view instanceof \Illuminate\View\View || $view instanceof \Illuminate\Contracts\View\View) {
                $view->with('allCustomFields', CustomUserField::all()->groupBy('role_name'));
            }

            return $view;
        });

        // Use our custom controller for storing new users
        $this->app->singleton(\Laravel\Fortify\Contracts\CreatesNewUsers::class, \App\Actions\Fortify\CreateNewUser::class);

        // =========================================================================
        // تنظیم رفتار سیستم بعد از ثبت‌نام (جلوگیری از ورود خودکار + پیام فارسی)
        // =========================================================================
        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    // از آنجایی که حساب در انتظار تایید است، اگر فورتیفای کاربر را موقتاً لاگین کرد، خارجش می‌کنیم
                    if (Auth::check()) {
                        Auth::logout();
                    }

                    return redirect()->route('login')->with('status', 'درخواست ثبت نام شما با موفقیت ارسال شد و در انتظار بررسی است.');
                }
            };
        });

        // تنظیم رفتار سیستم بعد از ورود موفق
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                        // سوپر ادمین را به ادمین هدایت کن
                        return redirect()->intended('/admin/dashboard');
                    }

                    // کاربران غیر سوپر ادمین نباید به آدرس‌های مدیریت هدایت شوند
                    $intended = redirect()->intended('/user/dashboard')->getTargetUrl();
                    if (str_contains($intended, '/admin/')) {
                        return redirect()->to('/user/dashboard');
                    }

                    return redirect()->intended('/user/dashboard');
                }
            };
        });
    }
}
