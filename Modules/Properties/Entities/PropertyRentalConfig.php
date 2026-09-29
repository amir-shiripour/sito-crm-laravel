<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyRentalConfig extends Model
{
    use HasFactory;

    protected $table = 'property_rental_configs';

    protected $fillable = [
        'property_id',
        'base_guests',
        'max_guests',
        'bedrooms',
        'double_beds',
        'single_beds',
        'bathrooms',
        'price_per_night',
        'price_weekend',
        'price_holiday',
        'extra_guest_fee',
        'cleaning_fee',
        'check_in_time',
        'check_out_time',
        'min_stay_nights',
        'house_rules',
        'rental_amenities',
        'instant_booking',
    ];

    protected $casts = [
        'base_guests' => 'integer',
        'max_guests' => 'integer',
        'bedrooms' => 'integer',
        'double_beds' => 'integer',
        'single_beds' => 'integer',
        'bathrooms' => 'integer',
        'min_stay_nights' => 'integer',
        'price_per_night' => 'decimal:0',
        'price_weekend' => 'decimal:0',
        'price_holiday' => 'decimal:0',
        'extra_guest_fee' => 'decimal:0',
        'cleaning_fee' => 'decimal:0',
        'house_rules' => 'array',
        'rental_amenities' => 'array',
        'instant_booking' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * محاسبه هزینه برای تعداد شب و مهمان در یک تاریخ مشخص
     */
    public function calculateStayPrice(int $nights = 1, int $guests = 1, bool $isWeekend = false, bool $isHoliday = false): float
    {
        $pricePerNight = (float) $this->price_per_night;

        if ($isHoliday && $this->price_holiday > 0) {
            $pricePerNight = (float) $this->price_holiday;
        } elseif ($isWeekend && $this->price_weekend > 0) {
            $pricePerNight = (float) $this->price_weekend;
        }

        $totalBase = $pricePerNight * $nights;

        // محاسبه نفر اضافه
        $extraGuests = max(0, $guests - (int) $this->base_guests);
        $totalExtra = 0;
        if ($extraGuests > 0 && $this->extra_guest_fee > 0) {
            $totalExtra = $extraGuests * (float) $this->extra_guest_fee * $nights;
        }

        $cleaning = (float) $this->cleaning_fee;

        return $totalBase + $totalExtra + $cleaning;
    }
}
