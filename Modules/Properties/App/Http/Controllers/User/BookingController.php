<?php

namespace Modules\Properties\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyRentalBooking;
use Modules\Properties\Services\RentalBookingService;
use Modules\Properties\Entities\PropertySetting;

class BookingController extends Controller
{
    protected RentalBookingService $bookingService;

    public function __construct(RentalBookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    /**
     * لیست جامع رزرواسیون اقامتگاه‌ها در پنل مدیریت CRM
     */
    public function index(Request $request)
    {
        $query = PropertyRentalBooking::with(['property.primaryImage', 'host', 'client']);

        $status = $request->get('status', 'all');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('guest_full_name', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('host', function ($hq) use ($search) {
                      $hq->where('display_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $counts = [
            'all' => PropertyRentalBooking::count(),
            'pending_host' => PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_PENDING_HOST)->count(),
            'awaiting_payment' => PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_AWAITING_PAYMENT)->count(),
            'confirmed' => PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_CONFIRMED)->count(),
            'checked_in' => PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_CHECKED_IN)->count(),
            'completed' => PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_COMPLETED)->count(),
            'cancelled' => PropertyRentalBooking::where('status', 'like', 'cancelled%')->count(),
        ];

        return view('properties::user.bookings.index', compact('bookings', 'counts', 'status'));
    }

    /**
     * مشاهده جزئیات رزرو در CRM
     */
    public function show(PropertyRentalBooking $booking)
    {
        $booking->load(['property.primaryImage', 'host', 'client']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = $currency === 'IRR' ? 'ریال' : 'تومان';

        return view('properties::user.bookings.show', compact('booking', 'currencyLabel'));
    }

    /**
     * تأیید دستی و قطعی‌سازی رزرو توسط ادمین CRM
     */
    public function confirm(Request $request, PropertyRentalBooking $booking)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $user = auth()->user();

        $this->bookingService->confirmBooking($booking, [
            'payment_method' => $request->input('payment_method'),
            'transaction_id' => $request->input('transaction_id') ?: ('ADM-' . strtoupper(\Illuminate\Support\Str::random(8))),
            'paid_amount' => $booking->total_price,
            'actor_type' => 'admin',
            'actor_id' => $user->id,
            'note' => $request->input('admin_note', 'تأیید و ثبت پرداخت دستی توسط مدیر سامانه')
        ]);

        return back()->with('success', "رزرو {$booking->booking_code} با موفقیت قطعی و تاریخ‌ها در تقویم مسدود شدند.");
    }

    /**
     * لغو رزرو توسط ادمین CRM
     */
    public function cancel(Request $request, PropertyRentalBooking $booking)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        $user = auth()->user();

        $this->bookingService->cancelBooking(
            $booking,
            $request->input('cancellation_reason'),
            'admin',
            $user->id
        );

        return back()->with('success', "رزرو {$booking->booking_code} توسط مدیر لغو و تقویم آزاد شد.");
    }
}
