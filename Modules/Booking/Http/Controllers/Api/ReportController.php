<?php

namespace Modules\Booking\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Entities\BookingSetting;
use App\Models\User;

class ReportController extends Controller
{
    protected function resolveProviderScope(Request $request): ?int
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        if ($this->isAdminUser($user)) {
            return null;
        }

        $settings = BookingSetting::current();
        if ($this->userIsProvider($user, $settings)) {
            return (int) $user->id;
        }

        return null;
    }

    protected function resolveUtcRange(Request $request): array
    {
        // Supports:
        // - from_utc / to_utc (ISO or any date parseable)
        // - or from_local_date / to_local_date in schedule timezone
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        if ($request->query('from_utc') && $request->query('to_utc')) {
            $from = Carbon::parse($request->query('from_utc'), 'UTC');
            $to   = Carbon::parse($request->query('to_utc'), 'UTC');
            return [$from, $to];
        }

        $fromLocal = $request->query('from_local_date');
        $toLocal   = $request->query('to_local_date');

        if ($fromLocal && $toLocal) {
            $from = Carbon::createFromFormat('Y-m-d', $fromLocal, $scheduleTz)->startOfDay()->timezone('UTC');
            $to   = Carbon::createFromFormat('Y-m-d', $toLocal, $scheduleTz)->addDay()->startOfDay()->timezone('UTC');
            return [$from, $to];
        }

        // default last 30 days
        $to = now('UTC')->addDay();
        $from = now('UTC')->subDays(30);
        return [$from, $to];
    }

    public function overview(Request $request)
    {
        [$from, $to] = $this->resolveUtcRange($request);
        $providerId = $this->resolveProviderScope($request);

        $base = Appointment::query()
            ->where('start_at_utc', '>=', $from)
            ->where('start_at_utc', '<', $to);

        if ($providerId) {
            $base->where('provider_user_id', $providerId);
        }

        $total = (clone $base)->count();

        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $byChannel = (clone $base)
            ->selectRaw('created_by_type, COUNT(*) as cnt')
            ->groupBy('created_by_type')
            ->pluck('cnt', 'created_by_type');

        $revenuePaid = BookingPayment::query()
            ->where('status', BookingPayment::STATUS_PAID)
            ->whereHas('appointment', function ($q) use ($from, $to) {
                $q->where('start_at_utc', '>=', $from)
                  ->where('start_at_utc', '<', $to);
            })
            ->sum('amount');

        if ($providerId) {
            $revenuePaid = BookingPayment::query()
                ->where('status', BookingPayment::STATUS_PAID)
                ->whereHas('appointment', function ($q) use ($from, $to, $providerId) {
                    $q->where('start_at_utc', '>=', $from)
                      ->where('start_at_utc', '<', $to)
                      ->where('provider_user_id', $providerId);
                })
                ->sum('amount');
        }

        return response()->json([
            'data' => [
                'range' => ['from_utc' => $from->toIso8601String(), 'to_utc' => $to->toIso8601String()],
                'total_appointments' => $total,
                'by_status' => $byStatus,
                'by_created_by_type' => $byChannel,
                'revenue_paid' => (float) $revenuePaid,
            ],
        ]);
    }

    public function providers(Request $request)
    {
        [$from, $to] = $this->resolveUtcRange($request);
        $providerId = $this->resolveProviderScope($request);

        $rows = Appointment::query()
            ->selectRaw('provider_user_id, COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status LIKE "CANCELED%" THEN 1 ELSE 0 END) as canceled,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as no_show', [
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_NO_SHOW
                ])
            ->where('start_at_utc', '>=', $from)
            ->where('start_at_utc', '<', $to)
            ->when($providerId, fn ($q) => $q->where('provider_user_id', $providerId))
            ->groupBy('provider_user_id')
            ->orderByDesc('total')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function services(Request $request)
    {
        [$from, $to] = $this->resolveUtcRange($request);
        $providerId = $this->resolveProviderScope($request);

        $rows = Appointment::query()
            ->leftJoin('booking_services', 'appointments.service_id', '=', 'booking_services.id')
            ->leftJoin('booking_categories', 'booking_services.category_id', '=', 'booking_categories.id')
            ->selectRaw('appointments.service_id, booking_services.name as service_name, booking_services.category_id, booking_categories.name as category_name, COUNT(*) as total')
            ->where('start_at_utc', '>=', $from)
            ->where('start_at_utc', '<', $to)
            ->when($providerId, fn ($q) => $q->where('appointments.provider_user_id', $providerId))
            ->groupBy('appointments.service_id', 'booking_services.name', 'booking_services.category_id', 'booking_categories.name')
            ->orderByDesc('total')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function finance(Request $request)
    {
        [$from, $to] = $this->resolveUtcRange($request);
        $providerId = $this->resolveProviderScope($request);

        $rows = BookingPayment::query()
            ->selectRaw('status, COUNT(*) as total, SUM(amount) as sum_amount')
            ->whereHas('appointment', function ($q) use ($from, $to, $providerId) {
                $q->where('start_at_utc', '>=', $from)
                  ->where('start_at_utc', '<', $to);

                if ($providerId) {
                    $q->where('provider_user_id', $providerId);
                }
            })
            ->groupBy('status')
            ->get();

        return response()->json(['data' => $rows]);
    }

    protected function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->can('booking.manage') || $user->can('booking.reports.view');
    }

    protected function userIsProvider(?User $user, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        $providerRoleIds = array_map('intval', (array) ($settings->allowed_roles ?? []));
        if (empty($providerRoleIds)) {
            return false;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int) $v)->all();

        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0;
    }
}
