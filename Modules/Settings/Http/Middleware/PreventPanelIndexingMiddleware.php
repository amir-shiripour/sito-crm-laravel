<?php

namespace Modules\Settings\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventPanelIndexingMiddleware
{
    /**
     * Handle an incoming request and attach X-Robots-Tag if indexing is blocked.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if panel indexing is blocked
        if (function_exists('is_panel_indexing_blocked') && is_panel_indexing_blocked()) {
            if ($this->isPanelRequest($request)) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
                return $response;
            }
        }

        // Check if public site indexing is blocked
        if (function_exists('is_public_indexing_blocked') && is_public_indexing_blocked()) {
            if (!$this->isPanelRequest($request)) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
                return $response;
            }
        }

        return $response;
    }

    /**
     * Determine whether the given request is targeting the admin, user panel, or auth routes.
     */
    protected function isPanelRequest(Request $request): bool
    {
        if ($request->is('admin', 'admin/*', 'user', 'user/*', 'login', 'register', 'password/*', 'two-factor-challenge')) {
            return true;
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        if ($routeName && (str_starts_with($routeName, 'admin.') || str_starts_with($routeName, 'user.') || str_starts_with($routeName, 'login') || str_starts_with($routeName, 'register'))) {
            return true;
        }

        return false;
    }
}
