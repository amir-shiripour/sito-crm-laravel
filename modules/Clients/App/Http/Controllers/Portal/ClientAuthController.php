<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Clients\Entities\Client;

class ClientAuthController extends Controller
{
    public function showLoginForm()
    {
        // تم مشابه بقیه صفحات کاربری، ولی ساده‌تر
        return view('clients::portal.auth.login');
    }

    public function login(Request $request)
    {
        // فعلاً لاگین بر اساس username + password
        // (username با استراتژی‌ای که تنظیم کردی ساخته شده)
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'نام کاربری الزامی است.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('client')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {

            $request->session()->regenerate();

            return redirect()->intended(route('client.dashboard'));
        }

        return back()
            ->withErrors(['username' => 'نام کاربری یا رمز عبور نادرست است.'])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

//        $request->session()->invalidate();
//        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    public function autoLoginFromAdmin(Request $request, Client $client)
    {
        // اختیاری: چک دسترسی ادمین
        // $this->authorize('view', $client);

        Auth::guard('client')->logout();
        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard');
    }
}
