<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (!file_exists(storage_path('app/installed.flag'))) {
            return redirect()->route('install.step1');
        }

        return $next($request);
    }
}

