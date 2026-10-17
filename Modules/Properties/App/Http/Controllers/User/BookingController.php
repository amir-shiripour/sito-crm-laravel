<?php

namespace Modules\Properties\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyRentalBooking;
use Modules\Properties\Entities\PropertyHost;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Services\RentalBookingService;

class BookingController extends Controller
{
    protected RentalBookingService $bookingService;

    public function __construct(RentalBookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    /**
     * بررسی سطح دسترسی کاربر جاری (مدیر پلتفرم یا میزبان)
     */
    protected function getAccessContext(): array
    {
        $user = auth()->user();
        $canManageAll = $user->hasRole(['super-admin', 'admin'])
            || $user->can('properties.manage')
            || $user->can('properties.bookings.manage')
            || $user->can('properties.bookings.view')
            || $user->can('properties.view.all');

        $host = PropertyHost::where('user_id', $user->id)->first();
        $isHost = $user->hasRole('property_host') || !is_null($host);

        if (!$canManageAll && !$isHost) {
            abort(403, 'شما دسترسی لازم برای مشاهده و مدیریت رزرواسیون را ندارید.');
        }

        return [$user, $canManageAll, $host];
    }

    /**
     * لیست رزرواسیون اقامتگاه‌ها در پنل کاربری و CRM با کنترل نقش و مجوز
     */
    public function index(Request $request)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        $query = PropertyRentalBooking::with(['property.primaryImage', 'host', 'client']);

        // اعمال محدوده بر اساس نقش کاربر
        if (!$canManageAll) {
            if (!$host) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('host_id', $host->id);
            }
        } elseif ($request->filled('host_id') && $request->get('host_id') !== 'all') {
            $query->where('host_id', $request->get('host_id'));
        }

        // فیلتر اقامتگاه خاص
        if ($request->filled('property_id') && $request->get('property_id') !== 'all') {
            $query->where('property_id', $request->get('property_id'));
        }

        // فیلتر وضعیت پرداخت
        $paymentStatus = $request->get('payment_status', 'all');
        if ($paymentStatus && $paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        // فیلتر بازه تاریخ ورود
        if ($dateFrom = $request->get('date_from')) {
            try {
                $carbonFrom = str_contains($dateFrom, '/')
                    ? \Morilog\Jalali\CalendarUtils::createCarbonFromJalaliString($dateFrom)->startOfDay()
                    : \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $query->where('check_in_date', '>=', $carbonFrom->format('Y-m-d'));
            } catch (\Throwable $e) {}
        }
        if ($dateTo = $request->get('date_to')) {
            try {
                $carbonTo = str_contains($dateTo, '/')
                    ? \Morilog\Jalali\CalendarUtils::createCarbonFromJalaliString($dateTo)->endOfDay()
                    : \Carbon\Carbon::parse($dateTo)->endOfDay();
                $query->where('check_in_date', '<=', $carbonTo->format('Y-m-d'));
            } catch (\Throwable $e) {}
        }

        $status = $request->get('status', 'all');
        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $query->whereIn('status', [
                    PropertyRentalBooking::STATUS_PENDING_HOST,
                    PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    PropertyRentalBooking::STATUS_CONFIRMED,
                    PropertyRentalBooking::STATUS_CHECKED_IN,
                ]);
            } else {
                $query->where('status', $status);
            }
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

        // شمارش‌ها با توجه به فیلترها و سطح دسترسی
        $countQuery = PropertyRentalBooking::query();
        if (!$canManageAll) {
            $countQuery->where('host_id', $host?->id ?? 0);
        } elseif ($request->filled('host_id') && $request->get('host_id') !== 'all') {
            $countQuery->where('host_id', $request->get('host_id'));
        }

        if ($request->filled('property_id') && $request->get('property_id') !== 'all') {
            $countQuery->where('property_id', $request->get('property_id'));
        }
        if ($paymentStatus && $paymentStatus !== 'all') {
            $countQuery->where('payment_status', $paymentStatus);
        }

        $counts = [
            'all' => (clone $countQuery)->count(),
            'pending_host' => (clone $countQuery)->where('status', PropertyRentalBooking::STATUS_PENDING_HOST)->count(),
            'awaiting_payment' => (clone $countQuery)->where('status', PropertyRentalBooking::STATUS_AWAITING_PAYMENT)->count(),
            'confirmed' => (clone $countQuery)->where('status', PropertyRentalBooking::STATUS_CONFIRMED)->count(),
            'checked_in' => (clone $countQuery)->where('status', PropertyRentalBooking::STATUS_CHECKED_IN)->count(),
            'completed' => (clone $countQuery)->where('status', PropertyRentalBooking::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $countQuery)->where('status', 'like', 'cancelled%')->count(),
        ];

        // واکشی لیست میزبان‌ها با تعداد رزروها جهت استفاده در سلکتور پیشرفته
        $hostsList = $canManageAll
            ? PropertyHost::withCount('rentalBookings')->orderBy('display_name')->get(['id', 'display_name', 'phone', 'avatar'])
            : collect();

        // واکشی لیست اقامتگاه‌ها جهت سلکتور اقامتگاه
        $propQuery = \Modules\Properties\Entities\Property::query();
        if (!$canManageAll) {
            $propQuery->where('host_id', $host?->id ?? 0);
        } elseif ($request->filled('host_id') && $request->get('host_id') !== 'all') {
            $propQuery->where('host_id', $request->get('host_id'));
        }
        $propertiesList = $propQuery->orderBy('title')->get(['id', 'title', 'code', 'city', 'host_id']);

        $selectedHostId = $request->get('host_id', 'all');
        $selectedPropertyId = $request->get('property_id', 'all');
        $selectedPaymentStatus = $paymentStatus;
        $selectedDateFrom = $request->get('date_from', '');
        $selectedDateTo = $request->get('date_to', '');

        // شمارش تعداد فیلترهای فعال جهت نمایش در نشانگر
        $activeFiltersCount = 0;
        if ($selectedHostId !== 'all') $activeFiltersCount++;
        if ($selectedPropertyId !== 'all') $activeFiltersCount++;
        if ($selectedPaymentStatus !== 'all') $activeFiltersCount++;
        if (!empty($selectedDateFrom)) $activeFiltersCount++;
        if (!empty($selectedDateTo)) $activeFiltersCount++;
        if ($request->filled('search')) $activeFiltersCount++;

        return view('properties::user.bookings.index', compact(
            'bookings',
            'counts',
            'status',
            'canManageAll',
            'host',
            'hostsList',
            'propertiesList',
            'selectedHostId',
            'selectedPropertyId',
            'selectedPaymentStatus',
            'selectedDateFrom',
            'selectedDateTo',
            'activeFiltersCount'
        ));
    }

    /**
     * مشاهده جزئیات رزرو با کنترل دسترسی
     */
    public function show(PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای مشاهده این رزرو را ندارید.');
        }

        $booking->load(['property.primaryImage', 'host', 'client']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = $currency === 'IRR' ? 'ریال' : 'تومان';

        return view('properties::user.bookings.show', compact('booking', 'currencyLabel', 'canManageAll', 'host'));
    }

    /**
     * تأیید درخواست رزرو (توسط میزبان یا مدیر)
     */
    public function approve(PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای تأیید این رزرو را ندارید.');
        }

        try {
            $this->bookingService->approveByHost($booking);
            return back()->with('success', "درخواست رزرو {$booking->booking_code} با موفقیت تأیید شد و جهت پرداخت به مهمان اطلاع‌رسانی گردید.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * رد درخواست رزرو (توسط میزبان یا مدیر)
     */
    public function reject(Request $request, PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای رد این رزرو را ندارید.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ], [
            'rejection_reason.required' => 'درج علت رد رزرو الزامی است.'
        ]);

        try {
            $this->bookingService->rejectByHost($booking, $request->input('rejection_reason'));
            return back()->with('success', "درخواست رزرو {$booking->booking_code} رد شد.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ثبت ورود مهمان (Check-in)
     */
    public function checkIn(PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای ثبت ورود این مهمان را ندارید.');
        }

        try {
            $this->bookingService->checkIn($booking);
            return back()->with('success', "ورود مهمان برای رزرو {$booking->booking_code} با موفقیت ثبت شد.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ثبت خروج و تکمیل اقامت (Complete / Check-out)
     */
    public function complete(PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای ثبت تکمیل این رزرو را ندارید.');
        }

        try {
            $this->bookingService->complete($booking);
            return back()->with('success', "اقامت رزرو {$booking->booking_code} پایان یافته و وضعیت به تکمیل‌شده تغییر یافت.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تأیید دستی و قطعی‌سازی رزرو و پرداخت (مدیر سامانه)
     */
    public function confirm(Request $request, PropertyRentalBooking $booking)
    {
        [$user, $canManageAll] = $this->getAccessContext();

        if (!$canManageAll) {
            abort(403, 'تنها مدیران سامانه مجاز به ثبت دستی پرداخت و قطعی‌سازی هستند.');
        }

        if ($booking->status === PropertyRentalBooking::STATUS_PENDING_HOST) {
            return back()->with('error', 'ابتدا باید درخواست رزرو توسط میزبان یا مدیر تأیید شود، سپس امکان تأیید دستی و تسویه وجود دارد.');
        }

        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

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
     * لغو رزرو (توسط مدیر یا میزبان)
     */
    public function cancel(Request $request, PropertyRentalBooking $booking)
    {
        [$user, $canManageAll, $host] = $this->getAccessContext();

        if (!$canManageAll && (!$host || $booking->host_id !== $host->id)) {
            abort(403, 'شما دسترسی لازم برای لغو این رزرو را ندارید.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ], [
            'cancellation_reason.required' => 'درج علت لغو رزرو الزامی است.'
        ]);

        $actorType = $canManageAll ? 'admin' : 'host';

        $this->bookingService->cancelBooking(
            $booking,
            $request->input('cancellation_reason'),
            $actorType,
            $user->id
        );

        return back()->with('success', "رزرو {$booking->booking_code} با موفقیت لغو و تقویم آزاد شد.");
    }
}
