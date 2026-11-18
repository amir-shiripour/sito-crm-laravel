<?php

namespace Modules\FieldService\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotTechnicianOnly
{
    /**
     * Prevent standalone technicians from accessing admin-only FieldService routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (TechnicianCustomFieldsHelper::isTechnicianOnly(Auth::user())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'دسترسی به این بخش مجاز نیست.'], 403);
            }

            return redirect()->route('user.fieldservice.dashboard');
        }

        return $next($request);
    }
}
