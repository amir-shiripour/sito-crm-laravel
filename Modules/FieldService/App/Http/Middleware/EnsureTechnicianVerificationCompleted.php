<?php

namespace Modules\FieldService\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnicianVerificationCompleted
{
    /**
     * Restrict unapproved technicians to /user/profile until their initial verification is completed and approved.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('user/profile*')
            || $request->is('user/contracts/*/print')
            || $request->is('user/contracts/*/pdf')
            || $request->is('logout')
            || $request->is('livewire*')
            || $request->is('sanctum/*')
        ) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user && TechnicianCustomFieldsHelper::isRestrictedUnapprovedTechnician($user)) {
            $tech = \Modules\FieldService\App\Models\Technician::where('user_id', $user->id)->first();
            $hasPendingContract = $tech && $tech->hasPendingContract();

            $warningMessage = $hasPendingContract
                ? 'برای استفاده از امکانات سامانه، ابتدا باید قرارداد همکاری خود را در بخش قراردادها مطالعه و امضا نمایید.'
                : 'تا زمان تکمیل اطلاعات احراز هویت و تایید نهایی، دسترسی شما به این بخش محدود شده است.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message'  => $warningMessage,
                    'redirect' => url('/user/profile'),
                ], 403);
            }

            return redirect('/user/profile')->with(
                'warning',
                $warningMessage
            );
        }

        return $next($request);
    }
}
