<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        $installedFlag = storage_path('app/installed.flag');

        if (!file_exists($installedFlag)) {
            return redirect()->route('install.step1');
        }

        try {
            if (!Schema::hasTable('settings')) {
                return redirect()->route('install.step1');
            }
        } catch (\Throwable $e) {
            return redirect()->route('install.step1');
        }

        return $next($request);
    }
}

