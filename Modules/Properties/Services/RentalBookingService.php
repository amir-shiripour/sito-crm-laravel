<?php

namespace Modules\Properties\Services;

use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalBooking;
use Modules\Properties\Entities\PropertyRentalBlock;
use Modules\Properties\Entities\PropertyRentalConfig;
use Modules\Properties\Entities\PropertyRentalPrice;
use Modules\Properties\Entities\PropertySetting;
use Modules\Clients\Entities\Client;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalBookingService
{
    /**
     * استانداردسازی تاریخ ورودی (تبدیل جلالی یا میلادی به Carbon)
     */
    public function parseDate(string $dateStr): Carbon
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabicDigits  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $clean = str_replace($persianDigits, $englishDigits, $dateStr);
        $clean = str_replace($arabicDigits, $englishDigits, $clean);
        $clean = trim($clean);

        // اگر تاریخ به فرمت جلالی YYYY/MM/DD یا YYYY-MM-DD باشد
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $clean, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];

            if ($year >= 1300 && $year <= 1500) {
                return (new Jalalian($year, $month, $day))->toCarbon()->startOfDay();
            }
        }

        return Carbon::parse($clean)->startOfDay();
    }

    /**
     * قالب‌بندی تاریخ Carbon به رشته جلالی
     */
    public function formatJalali(Carbon $date): string
    {
        return Jalalian::fromCarbon($date)->format('Y/m/d');
    }

    /**
     * محاسبه پیش‌فاکتور دقیق رزرو (محاسبه شب‌ها، تعطیلات، نفرات اضافه و کمیسیون)
     */
    public function calculateQuote(Property $property, string $checkInStr, string $checkOutStr, int $guestsCount = 1): array
    {
        $checkIn = $this->parseDate($checkInStr);
        $checkOut = $this->parseDate($checkOutStr);

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            throw ValidationException::withMessages([
                'check_out' => 'تاریخ خروج باید بعد از تاریخ ورود باشد.'
            ]);
        }

        $nightsCount = $checkIn->diffInDays($checkOut);
        if ($nightsCount <= 0) {
            throw ValidationException::withMessages([
                'check_out' => 'حداقل مدت اقامت باید ۱ شب باشد.'
            ]);
        }

        $config = $property->rentalConfig;
        $basePrice = $config ? (float)$config->price_per_night : 0;
        $minStay = $config ? ($config->min_stay_nights ?? 1) : 1;
        $maxGuests = $config ? ($config->max_guests ?? 4) : 4;
        $baseGuests = $config ? ($config->base_guests ?? 2) : 2;
        $extraGuestFee = $config ? (float)($config->extra_guest_fee ?? 0) : 0;
        $cleaningFee = $config ? (float)($config->cleaning_fee ?? 0) : 0;

        if ($nightsCount < $minStay) {
            throw ValidationException::withMessages([
                'check_out' => "حداقل مدت اقامت مجاز برای این اقامتگاه {$minStay} شب می‌باشد."
            ]);
        }

        if ($guestsCount > $maxGuests) {
            throw ValidationException::withMessages([
                'guests_count' => "حداکثر ظرفیت پذیرش این اقامتگاه {$maxGuests} نفر است."
            ]);
        }

        // بررسی مسدودی‌های تقویم و رزروهای قبلی
        $conflictBlocks = PropertyRentalBlock::where('property_id', $property->id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($sub) use ($checkIn, $checkOut) {
                    $sub->where('start_date', '<', $checkOut->format('Y-m-d'))
                        ->where('end_date', '>', $checkIn->format('Y-m-d'));
                });
            })
            ->exists();

        if ($conflictBlocks) {
            throw ValidationException::withMessages([
                'dates' => 'یک یا چند شب از تاریخ‌های انتخابی شما در تقویم اقامتگاه مسدود یا رزرو شده است.'
            ]);
        }

        // واکشی قیمت‌های فصلی / مناسبتی
        $seasonalPrices = PropertyRentalPrice::where('property_id', $property->id)
            ->where('start_date', '<=', $checkOut->format('Y-m-d'))
            ->where('end_date', '>=', $checkIn->format('Y-m-d'))
            ->get();

        // واکشی تعطیلات رسمی پلتفرم
        $officialHolidayMap = [];
        try {
            $officialHolidays = PropertySetting::get('official_holidays', []);
            if (is_array($officialHolidays)) {
                $officialHolidayMap = $officialHolidays;
            }
        } catch (\Throwable $e) {}

        $nightsBreakdown = [];
        $nightsTotal = 0;
        $curr = $checkIn->copy();

        for ($i = 0; $i < $nightsCount; $i++) {
            $dateStr = $curr->format('Y-m-d');
            $jDate = Jalalian::fromCarbon($curr);
            $jalaliStr = $jDate->format('Y/m/d');

            // بررسی اولویت قیمت فصلی
            $customPrice = $seasonalPrices->first(function ($p) use ($dateStr) {
                return $dateStr >= $p->start_date->format('Y-m-d') && $dateStr <= $p->end_date->format('Y-m-d');
            });

            $nightPrice = $basePrice;
            $rateType = 'normal';
            $title = null;

            if ($customPrice) {
                $nightPrice = (float) $customPrice->price_per_night;
                $rateType = 'custom';
                $title = $customPrice->title;
            } elseif ($config) {
                $rateInfo = $config->resolveNightRateCategory($curr, $officialHolidayMap);
                $rateType = $rateInfo['price_type'];
                if ($rateType === 'holiday') {
                    $nightPrice = (float) $config->price_holiday;
                    $title = $rateInfo['holiday_title'] ?? 'تعطیلات رسمی و پیک';
                } elseif ($rateType === 'weekend') {
                    $nightPrice = (float) $config->price_weekend;
                    $title = 'نرخ آخر هفته';
                } else {
                    $nightPrice = (float) $config->price_per_night;
                }
            }

            $nightsTotal += $nightPrice;
            $nightsBreakdown[] = [
                'date' => $dateStr,
                'jalali_date' => $jalaliStr,
                'price' => $nightPrice,
                'rate_type' => $rateType,
                'title' => $title,
            ];

            $curr->addDay();
        }

        // محاسبه نفرات اضافه
        $extraGuestsCount = max(0, $guestsCount - $baseGuests);
        $extraGuestsFeeTotal = $extraGuestsCount * $extraGuestFee * $nightsCount;

        // مبلغ کل
        $grandTotal = $nightsTotal + $extraGuestsFeeTotal + $cleaningFee;

        // محاسبه سهم کمیسیون پلتفرم و میزبان
        $commissionPercent = 10.0;
        if ($property->host) {
            $commissionPercent = (float) $property->host->effective_commission_rate;
        } else {
            $commissionPercent = (float) PropertySetting::get('rental_default_commission', 10);
        }

        $commissionAmount = round(($grandTotal * $commissionPercent) / 100);
        $hostPayoutAmount = max(0, $grandTotal - $commissionAmount);

        // درصد پیش‌پرداخت (پیش‌فرض کل مبلغ، یا درصد تنظیمی)
        $prepaymentPercent = (float) PropertySetting::get('rental_prepayment_percent', 100);
        if ($prepaymentPercent > 0 && $prepaymentPercent < 100) {
            $prepaymentAmount = round(($grandTotal * $prepaymentPercent) / 100);
        } else {
            $prepaymentAmount = $grandTotal;
        }

        return [
            'check_in_date'        => $checkIn->format('Y-m-d'),
            'check_out_date'       => $checkOut->format('Y-m-d'),
            'check_in_jalali'      => $this->formatJalali($checkIn),
            'check_out_jalali'     => $this->formatJalali($checkOut),
            'nights_count'         => $nightsCount,
            'guests_count'         => $guestsCount,
            'base_guests'          => $baseGuests,
            'extra_guests_count'   => $extraGuestsCount,
            'nights_breakdown'     => $nightsBreakdown,
            'nights_total'         => $nightsTotal,
            'extra_guests_fee'     => $extraGuestsFeeTotal,
            'cleaning_fee'         => $cleaningFee,
            'grand_total'          => $grandTotal,
            'prepayment_amount'    => $prepaymentAmount,
            'commission_percent'   => $commissionPercent,
            'commission_amount'    => $commissionAmount,
            'host_payout_amount'   => $hostPayoutAmount,
            'instant_booking'      => (bool)($config?->instant_booking ?? false),
        ];
    }

    /**
     * ایجاد درخواست رزرو جدید
     */
    public function createBooking(Property $property, Client $client, array $data): PropertyRentalBooking
    {
        $quote = $this->calculateQuote(
            $property,
            $data['check_in_date'],
            $data['check_out_date'],
            (int)($data['guests_count'] ?? 1)
        );

        $host = $property->host;
        if (!$host) {
            throw ValidationException::withMessages([
                'host' => 'میزبان این اقامتگاه در سامانه تعیین نشده است.'
            ]);
        }

        return DB::transaction(function () use ($property, $host, $client, $quote, $data) {
            $isInstant = $quote['instant_booking'];
            $defaultStatus = $isInstant ? PropertyRentalBooking::STATUS_AWAITING_PAYMENT : PropertyRentalBooking::STATUS_PENDING_HOST;

            // مهلت پرداخت (اگر رزرو آنی باشد)
            $deadlineHours = (int) PropertySetting::get('rental_payment_deadline_hours', 2);
            $paymentDeadline = $isInstant ? now()->addHours(max(1, $deadlineHours)) : null;

            $booking = PropertyRentalBooking::create([
                'booking_code'        => PropertyRentalBooking::generateBookingCode($property->id),
                'property_id'         => $property->id,
                'host_id'             => $host->id,
                'client_id'           => $client->id,
                'check_in_date'       => $quote['check_in_date'],
                'check_out_date'      => $quote['check_out_date'],
                'check_in_jalali'     => $quote['check_in_jalali'],
                'check_out_jalali'    => $quote['check_out_jalali'],
                'nights_count'        => $quote['nights_count'],
                'guests_count'        => $quote['guests_count'],
                'extra_guests_count'  => $quote['extra_guests_count'],
                'nights_total'        => $quote['nights_total'],
                'extra_guests_fee'    => $quote['extra_guests_fee'],
                'cleaning_fee'        => $quote['cleaning_fee'],
                'discount_amount'     => 0,
                'grand_total'         => $quote['grand_total'],
                'prepayment_amount'   => $quote['prepayment_amount'],
                'paid_amount'         => 0,
                'commission_percent'  => $quote['commission_percent'],
                'commission_amount'   => $quote['commission_amount'],
                'host_payout_amount'  => $quote['host_payout_amount'],
                'status'              => $defaultStatus,
                'payment_status'      => PropertyRentalBooking::PAYMENT_UNPAID,
                'payment_deadline_at' => $paymentDeadline,
                'host_action_at'      => $isInstant ? now() : null,
                'notes'               => $data['notes'] ?? null,
                'guest_full_name'     => $data['guest_full_name'] ?? $client->full_name,
                'guest_phone'         => $data['guest_phone'] ?? $client->phone,
                'guest_national_code' => $data['guest_national_code'] ?? $client->national_code,
                'meta'                => [
                    'nights_breakdown' => $quote['nights_breakdown'],
                    'created_source'   => 'web_single_property',
                    'instant_booking'  => $isInstant,
                ],
            ]);

            $this->dispatchWorkflow('rental_booking_created', $booking);

            return $booking;
        });
    }

    /**
     * اجرای موتور گردش کار بدون هاردکد کردن اعلان‌ها یا پیامک‌ها
     */
    protected function dispatchWorkflow(string $eventKey, PropertyRentalBooking $booking, array $payload = []): void
    {
        try {
            if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
                $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
                $engine->start($eventKey, 'RENTAL_BOOKING', $booking->id, array_merge([
                    'booking_id'   => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'property_id'  => $booking->property_id,
                    'client_id'    => $booking->client_id,
                    'host_id'      => $booking->host_id,
                    'status'       => $booking->status,
                ], $payload));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("[RentalBookingService] Workflow dispatch error: {$e->getMessage()}");
        }
    }

    /**
     * تأیید درخواست رزرو توسط میزبان
     */
    public function approveByHost(PropertyRentalBooking $booking, int $paymentDeadlineHours = 2, ?string $hostNote = null): PropertyRentalBooking
    {
        if (!$booking->canBeApprovedByHost()) {
            throw new \RuntimeException('این رزرو در وضعیتی نیست که توسط میزبان تأیید شود.');
        }

        // بررسی مجدد تداخل تقویم
        $conflict = PropertyRentalBlock::where('property_id', $booking->property_id)
            ->where('start_date', '<', $booking->check_out_date->format('Y-m-d'))
            ->where('end_date', '>', $booking->check_in_date->format('Y-m-d'))
            ->exists();

        if ($conflict) {
            throw new \RuntimeException('تاریخ‌های این رزرو با سایر رزروها یا مسدودی‌های تقویم تداخل دارد.');
        }

        $meta = $booking->meta ?? [];
        if ($hostNote) {
            $meta['host_approval_note'] = $hostNote;
        }

        $booking->update([
            'status'              => PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
            'payment_deadline_at' => now()->addHours(max(1, $paymentDeadlineHours)),
            'host_action_at'      => now(),
            'meta'                => $meta,
        ]);

        $this->dispatchWorkflow('rental_booking_approved', $booking);

        return $booking;
    }

    /**
     * رد درخواست رزرو توسط میزبان
     */
    public function rejectByHost(PropertyRentalBooking $booking, string $reason): PropertyRentalBooking
    {
        if (!$booking->canBeRejectedByHost()) {
            throw new \RuntimeException('این رزرو در وضعیتی نیست که رد شود.');
        }

        $booking->update([
            'status'              => PropertyRentalBooking::STATUS_REJECTED,
            'host_action_at'      => now(),
            'cancellation_reason' => $reason,
        ]);

        $this->dispatchWorkflow('rental_booking_rejected', $booking, ['reason' => $reason]);

        return $booking;
    }

    /**
     * تأیید قطعی رزرو پس از پرداخت و مسدودسازی خودکار تقویم
     *
     * @param PropertyRentalBooking $booking
     * @param string|array $paymentMethodOrData
     * @param float|null $amount
     * @return PropertyRentalBooking
     */
    public function confirmBooking(PropertyRentalBooking $booking, string|array $paymentMethodOrData = 'online', ?float $amount = null): PropertyRentalBooking
    {
        $paymentMethod = 'online';
        $transactionId = null;
        $note = null;

        if (is_array($paymentMethodOrData)) {
            $paymentMethod = $paymentMethodOrData['payment_method'] ?? 'online';
            $paid = isset($paymentMethodOrData['paid_amount']) ? (float)$paymentMethodOrData['paid_amount'] : ($amount ?? (float)$booking->grand_total);
            $transactionId = $paymentMethodOrData['transaction_id'] ?? null;
            $note = $paymentMethodOrData['note'] ?? null;
        } else {
            $paymentMethod = $paymentMethodOrData;
            $paid = $amount ?? (float)$booking->prepayment_amount;
        }

        return DB::transaction(function () use ($booking, $paymentMethod, $paid, $transactionId, $note) {
            $paymentStatus = $paid >= (float)$booking->grand_total 
                ? PropertyRentalBooking::PAYMENT_PAID 
                : PropertyRentalBooking::PAYMENT_PARTIALLY_PAID;

            $updateData = [
                'status'         => PropertyRentalBooking::STATUS_CONFIRMED,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'paid_amount'    => $paid,
                'confirmed_at'   => now(),
            ];

            if ($transactionId) {
                $updateData['transaction_id'] = $transactionId;
            }
            if ($note && empty($booking->notes)) {
                $updateData['notes'] = $note;
            }

            $booking->update($updateData);

            // مسدودسازی خودکار در جدول تقویم
            PropertyRentalBlock::create([
                'property_id' => $booking->property_id,
                'booking_id'  => $booking->id,
                'start_date'  => $booking->check_in_date,
                'end_date'    => $booking->check_out_date,
                'reason'      => 'رزرو قطعی: ' . $booking->booking_code,
            ]);

            $this->dispatchWorkflow('rental_booking_confirmed', $booking);

            return $booking;
        });
    }

    /**
     * لغو رزرو و آزادسازی تقویم
     */
    public function cancelBooking(PropertyRentalBooking $booking, ?string $reasonOrActor = null, ?string $actorType = 'guest', $actorId = null): PropertyRentalBooking
    {
        $reason = null;
        $cancelledBy = 'guest';

        // اگر پارامتر دوم actorType بود (مثلاً 'guest', 'host', 'admin')
        if (in_array($reasonOrActor, ['guest', 'host', 'admin'])) {
            $cancelledBy = $reasonOrActor;
            $reason = $actorType;
        } else {
            // اگر پارامتر دوم علت لغو بود
            $reason = $reasonOrActor;
            $cancelledBy = in_array($actorType, ['guest', 'host', 'admin']) ? $actorType : 'guest';
        }

        $targetStatus = match($cancelledBy) {
            'host'  => PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
            'admin' => PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
            default => PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
        };

        return DB::transaction(function () use ($booking, $targetStatus, $reason, $cancelledBy) {
            $booking->update([
                'status'              => $targetStatus,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
            ]);

            // آزادسازی خودکار بلاک‌های تقویم مرتبط با این رزرو
            PropertyRentalBlock::where('booking_id', $booking->id)->delete();

            $this->dispatchWorkflow('rental_booking_cancelled', $booking, [
                'cancelled_by' => $cancelledBy,
                'reason'       => $reason,
            ]);

            return $booking;
        });
    }

    /**
     * ثبت ورود مسافر
     */
    public function checkIn(PropertyRentalBooking $booking): PropertyRentalBooking
    {
        $booking->update([
            'status' => PropertyRentalBooking::STATUS_CHECKED_IN,
        ]);

        $this->dispatchWorkflow('rental_booking_checked_in', $booking);

        return $booking;
    }

    /**
     * ثبت تخلیه و پایان اقامت
     */
    public function complete(PropertyRentalBooking $booking): PropertyRentalBooking
    {
        $booking->update([
            'status' => PropertyRentalBooking::STATUS_COMPLETED,
        ]);

        $this->dispatchWorkflow('rental_booking_completed', $booking);

        return $booking;
    }

    /**
     * بررسی و انقضای رزروهای معوقه پرداخت
     */
    public function expireOverdueBookings(): int
    {
        $overdueBookings = PropertyRentalBooking::where('status', PropertyRentalBooking::STATUS_AWAITING_PAYMENT)
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<', now())
            ->get();

        $count = 0;
        foreach ($overdueBookings as $b) {
            $this->cancelBooking($b, 'admin', 'انقضای مهلت پرداخت');
            $b->update(['status' => PropertyRentalBooking::STATUS_EXPIRED]);
            $count++;
        }

        return $count;
    }
}
