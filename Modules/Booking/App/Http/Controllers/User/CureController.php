<?php

namespace Modules\Booking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\App\Models\TreatmentPlan;
use Modules\Clients\Entities\Client;

class CureController extends Controller
{
    /**
     * Show the treatment plan builder (create new).
     */
    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'name'          => $s->name,
                'base_price'    => (float) $s->base_price,
                'category_id'   => $s->category_id,
                'category_name' => $s->category?->name,
                'custom_prices' => $s->custom_prices ?? [],
            ]);

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs'     => null,
            'isReadOnly' => false,
            'categories' => $categories,
            'clients'    => $clients,
        ]);
    }

    /**
     * List all treatment plans.
     */
    public function list(Request $request)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.manage',
            ]),
            403
        );

        $setting                = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($setting->cure_allow_edit_confirmed ?? false);

        $user  = auth()->user();
        $query = TreatmentPlan::with('client', 'creator')->latest();

        // Scope to own plans if user only has view.own
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage') &&
            $user->can('booking.cure.view.own')
        ) {
            $query->where('user_id', $user->id);
        }

        // Search
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($q2) => $q2->where('full_name', 'like', "%{$search}%"));
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        match ($request->input('sort', 'newest')) {
            'oldest'     => $query->oldest(),
            'total_desc' => $query->orderByDesc('total'),
            default      => $query->latest(),
        };

        $plans = $query->paginate(15)->withQueryString();

        // Stats (scoped same way)
        $statsQuery = TreatmentPlan::query();
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage') &&
            $user->can('booking.cure.view.own')
        ) {
            $statsQuery->where('user_id', $user->id);
        }

        $totalCount   = $statsQuery->count();
        $statusCounts = $statsQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalAmount  = $statsQuery->sum('total');

        return view('booking::user.cure.cure-list', compact(
            'plans',
            'totalCount',
            'statusCounts',
            'totalAmount',
            'cureAllowEditConfirmed'
        ));
    }

    /**
     * Store a new treatment plan.
     */
    public function store(Request $request)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $data = $request->validate([
            'client_id'            => ['required', 'integer', 'exists:clients,id'],
            'patient_name'         => ['nullable', 'string', 'max:255'],
            'status'               => ['required', 'in:draft,confirmed'],
            'notes'                => ['nullable', 'string'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_type'        => ['nullable', 'in:amount,percent'],
            'subtotal'             => ['nullable', 'numeric', 'min:0'],
            'discount_value'       => ['nullable', 'numeric', 'min:0'],
            'total'                => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', 'integer'],
            'items.*.service_name' => ['required', 'string'],
            'items.*.teeth'        => ['nullable', 'array'],
            'items.*.brands'       => ['nullable', 'array'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.subtotal'     => ['nullable', 'numeric', 'min:0'],
        ]);

        // Downgrade to draft if user cannot confirm
        if (
            $data['status'] === 'confirmed' &&
            ! auth()->user()->can('booking.cure.confirm') &&
            ! auth()->user()->can('booking.cure.manage')
        ) {
            $data['status'] = 'draft';
        }

        $setting  = BookingSetting::current();
        $currency = $setting->currency_unit ?? 'IRT';

        $plan = TreatmentPlan::create([
            'user_id'        => auth()->id(),
            'client_id'      => $data['client_id'],
            'patient_name'   => $data['patient_name'] ?? null,
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
            'currency'       => $currency,
            'discount_amount'=> $data['discount_amount'] ?? 0,
            'discount_type'  => $data['discount_type'] ?? 'amount',
            'discount_value' => $data['discount_value'] ?? 0,
            'subtotal'       => $data['subtotal'] ?? 0,
            'total'          => $data['total'] ?? 0,
            'items'          => $data['items'],
        ]);

        $redirect = $data['status'] === 'confirmed'
            ? route('user.booking.cure.list')
            : null;

        return response()->json([
            'success'  => true,
            'message'  => $data['status'] === 'confirmed' ? 'طرح درمان تأیید شد.' : 'پیش‌نویس ذخیره شد.',
            'id'       => $plan->id,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Show a treatment plan (read-only).
     */
    public function show(TreatmentPlan $cure)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage')
        ) {
            abort_unless($cure->user_id === $user->id, 403);
        }

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'name'          => $s->name,
                'base_price'    => (float) $s->base_price,
                'category_id'   => $s->category_id,
                'category_name' => $s->category?->name,
                'custom_prices' => $s->custom_prices ?? [],
            ]);

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        $planJs = [
            'id'              => $cure->id,
            'client'          => $cure->client ? [
                'id'        => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name'    => $cure->patient_name,
            'status'          => $cure->status,
            'notes'           => $cure->notes,
            'discount_amount' => $cure->discount_amount,
            'discount_type'   => $cure->discount_type,
            'items'           => $cure->items ?? [],
        ];

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs'     => $planJs,
            'isReadOnly' => true,
            'categories' => $categories,
            'clients'    => $clients,
        ]);
    }

    /**
     * Show the edit form for a treatment plan.
     */
    public function edit(TreatmentPlan $cure)
    {
        $user                   = auth()->user();
        $setting                = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($setting->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') || $user->can('booking.cure.manage'),
            403
        );

        if ($cure->status === 'confirmed') {
            abort_unless(
                $cureAllowEditConfirmed && (
                    $user->can('booking.cure.edit.confirmed') ||
                    $user->can('booking.cure.manage')
                ),
                403
            );
        }

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'name'          => $s->name,
                'base_price'    => (float) $s->base_price,
                'category_id'   => $s->category_id,
                'category_name' => $s->category?->name,
                'custom_prices' => $s->custom_prices ?? [],
            ]);

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        $planJs = [
            'id'              => $cure->id,
            'client'          => $cure->client ? [
                'id'        => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name'    => $cure->patient_name,
            'status'          => $cure->status,
            'notes'           => $cure->notes,
            'discount_amount' => $cure->discount_amount,
            'discount_type'   => $cure->discount_type,
            'items'           => $cure->items ?? [],
        ];

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs'     => $planJs,
            'isReadOnly' => false,
            'categories' => $categories,
            'clients'    => $clients,
        ]);
    }

    /**
     * Update an existing treatment plan.
     */
    public function update(Request $request, TreatmentPlan $cure)
    {
        $user                   = auth()->user();
        $setting                = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($setting->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') || $user->can('booking.cure.manage'),
            403
        );

        if ($cure->status === 'confirmed') {
            abort_unless(
                $cureAllowEditConfirmed && (
                    $user->can('booking.cure.edit.confirmed') ||
                    $user->can('booking.cure.manage')
                ),
                403
            );
        }

        $data = $request->validate([
            'client_id'            => ['required', 'integer', 'exists:clients,id'],
            'patient_name'         => ['nullable', 'string', 'max:255'],
            'status'               => ['required', 'in:draft,confirmed'],
            'notes'                => ['nullable', 'string'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_type'        => ['nullable', 'in:amount,percent'],
            'subtotal'             => ['nullable', 'numeric', 'min:0'],
            'discount_value'       => ['nullable', 'numeric', 'min:0'],
            'total'                => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', 'integer'],
            'items.*.service_name' => ['required', 'string'],
            'items.*.teeth'        => ['nullable', 'array'],
            'items.*.brands'       => ['nullable', 'array'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.subtotal'     => ['nullable', 'numeric', 'min:0'],
        ]);

        // Keep current status if user cannot confirm
        if (
            $data['status'] === 'confirmed' &&
            ! $user->can('booking.cure.confirm') &&
            ! $user->can('booking.cure.manage')
        ) {
            $data['status'] = $cure->status;
        }

        $cure->update([
            'client_id'      => $data['client_id'],
            'patient_name'   => $data['patient_name'] ?? null,
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
            'discount_amount'=> $data['discount_amount'] ?? 0,
            'discount_type'  => $data['discount_type'] ?? 'amount',
            'discount_value' => $data['discount_value'] ?? 0,
            'subtotal'       => $data['subtotal'] ?? 0,
            'total'          => $data['total'] ?? 0,
            'items'          => $data['items'],
        ]);

        $redirect = $data['status'] === 'confirmed'
            ? route('user.booking.cure.list')
            : null;

        return response()->json([
            'success'  => true,
            'message'  => $data['status'] === 'confirmed' ? 'طرح درمان تأیید شد.' : 'پیش‌نویس ذخیره شد.',
            'id'       => $cure->id,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Delete a treatment plan.
     */
    public function destroy(TreatmentPlan $cure)
    {
        abort_unless(
            auth()->user()->can('booking.cure.delete') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $cure->delete();

        return redirect()->route('user.booking.cure.list')
            ->with('success', 'طرح درمان حذف شد.');
    }
}
