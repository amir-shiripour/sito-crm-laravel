<?php

namespace Modules\Clients\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Module;

class EnsureClientsModuleEnabled
{
    public function handle(Request $request, Closure $next)
    {
        /*if (! Module::has('Clients') || ! Module::isEnabled('Clients')) {
            abort(404);
        }*/

        $dbModule = Module::where('slug', 'clients')->first();

        // اگر رکورد وجود نداشت یعنی ماژول شناسایی نشده (احتمالا هنوز sync نشده)، 404
        if (! $dbModule) {
            abort(404);
        }

        // اگر نصب نشده یا غیرفعال هست، قطع دسترسی (می‌توان redirect به صفحه modules داد)
        if (! $dbModule->installed || ! $dbModule->active) {
            abort(404);
        }

        return $next($request);
    }
}
