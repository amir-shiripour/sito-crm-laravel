<?php

namespace Modules\Clients\Middleware;

use Closure;
use Nwidart\Modules\Facades\Module;

class EnsureClientsModuleEnabled
{
    public function handle($request, Closure $next)
    {
        if (! Module::has('Clients') || ! Module::isEnabled('Clients')) {
            abort(404);
        }

        return $next($request);
    }
}
