<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyRentalBooking;
use Modules\Properties\Services\RentalBookingService;
use Modules\Properties\Entities\PropertySetting;

class ClientRentalBookingController extends Controller
{
    protected RentalBookingService $bookingService;

    public function __construct(RentalBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * لیست رزروهای اقامتگاهی کلاینت
     */
    public function index(Request $request)
    {
        $client = auth('client')->user();
        if (!$client) {
            abort(403);
        }

        $query = PropertyRentalBooking::with(['property.primaryImage', 'host'])
            ->where('client_id', $client->id);

        $status = $request->get('status', 'all');
        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $query->whereIn('status', [
                    PropertyRentalBooking::STATUS_PENDING_HOST,
                    PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    PropertyRentalBooking::STATUS_CONFIRMED,
                    PropertyRentalBooking::STATUS_CHECKED_IN,
                ]);
            } elseif ($status === 'history') {
                $query->whereIn('status', [
                    PropertyRentalBooking::STATUS_COMPLETED,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
                    PropertyRentalBooking::STATUS_REJECTED,
                    PropertyRentalBooking::STATUS_EXPIRED,
                ]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // شمارش‌ها برای تب‌های فیلتر
        $counts = [
            'all' => PropertyRentalBooking::where('client_id', $client->id)->count(),
            'active' => PropertyRentalBooking::where('client_id', $client->id)
                ->whereIn('status', [
                    PropertyRentalBooking::STATUS_PENDING_HOST,
                    PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    PropertyRentalBooking::STATUS_CONFIRMED,
                    PropertyRentalBooking::STATUS_CHECKED_IN,
                ])->count(),
            'pending_host' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_PENDING_HOST)->count(),
            'awaiting_payment' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_AWAITING_PAYMENT)->count(),
            'confirmed' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_CONFIRMED)->count(),
            'history' => PropertyRentalBooking::where('client_id', $client->id)
                ->whereIn('status', [
                    PropertyRentalBooking::STATUS_COMPLETED,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
                    PropertyRentalBooking::STATUS_REJECTED,
                    PropertyRentalBooking::STATUS_EXPIRED,
                ])->count(),
        ];

        return view('clients::portal.rental-bookings.index', compact('bookings', 'counts', 'status'));
    }

    /**
     * مشاهده جزئیات و ووچر رزرو
     */
    public function show(PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            abort(403, 'شما به این رزرو دسترسی ندارید.');
        }

        $booking->load(['property.primaryImage', 'host']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = $currency === 'IRR' ? 'ریال' : 'تومان';

        return view('clients::portal.rental-bookings.show', compact('booking', 'currencyLabel'));
    }

    /**
     * چاپ و ووچر دیجیتال رسمی رزرو
     */
    public function print(PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            abort(403);
        }

        $booking->load(['property', 'host']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = $currency === 'IRR' ? 'ریال' : 'تومان';

        return view('clients::portal.rental-bookings.print', compact('booking', 'currencyLabel'));
    }

    /**
     * پرداخت وجه رزرو
     */
    public function pay(Request $request, PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        if (!in_array($booking->status, [PropertyRentalBooking::STATUS_AWAITING_PAYMENT, PropertyRentalBooking::STATUS_PENDING_HOST])) {
            return response()->json(['success' => false, 'message' => 'این رزرو در وضعیت قابل پرداخت نمی‌باشد.'], 422);
        }

        // شبیه‌سازی / ثبت موفق پرداخت و قطعی شدن فوری رزرو
        $transactionId = 'PAY-' . strtoupper(\Illuminate\Support\Str::random(10));
        
        $this->bookingService->confirmBooking($booking, [
            'payment_method' => $request->input('payment_method', 'online'),
            'transaction_id' => $transactionId,
            'paid_amount' => $booking->total_price,
            'actor_type' => 'client',
            'actor_id' => $client->id,
            'note' => 'پرداخت آنلاین توسط کلاینت در پرتال'
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد و رزرو شما قطعی گردید.',
                'redirect_url' => route('client.rental-bookings.show', $booking->id)
            ]);
        }

        return redirect()->route('client.rental-bookings.show', $booking->id)
            ->with('success', 'پرداخت با موفقیت انجام شد و رزرو شما قطعی گردید.');
    }

    /**
     * لغو رزرو توسط مهمان
     */
    public function cancel(Request $request, PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        try {
            $this->bookingService->cancelBooking(
                $booking,
                $request->input('cancellation_reason'),
                'guest',
                $client->id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'رزرو شما با موفقیت لغو شد.'
                ]);
            }

            return redirect()->route('client.rental-bookings.show', $booking->id)
                ->with('success', 'رزرو شما با موفقیت لغو شد.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
