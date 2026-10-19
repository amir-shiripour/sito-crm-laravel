<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;

class BookingClientSchedule extends Model
{
    use HasFactory;

    protected $table = 'booking_client_schedules';

    protected $fillable = [
        'client_id',
        'weekly_schedule',
        'preferred_days',
        'preferred_shift',
        'max_appointments_per_week',
        'temp_absence_active',
        'temp_absence_start',
        'temp_absence_end',
        'temp_absence_reason',
        'general_notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'weekly_schedule' => 'array',
        'preferred_days' => 'array',
        'temp_absence_active' => 'boolean',
        'temp_absence_start' => 'date',
        'temp_absence_end' => 'date',
        'max_appointments_per_week' => 'integer',
    ];

    public const DAYS = [
        0 => ['key' => 'saturday', 'name' => 'شنبه', 'short' => 'ش', 'is_weekend' => false],
        1 => ['key' => 'sunday', 'name' => 'یکشنبه', 'short' => 'ی', 'is_weekend' => false],
        2 => ['key' => 'monday', 'name' => 'دوشنبه', 'short' => 'د', 'is_weekend' => false],
        3 => ['key' => 'tuesday', 'name' => 'سه‌شنبه', 'short' => 'س', 'is_weekend' => false],
        4 => ['key' => 'wednesday', 'name' => 'چهارشنبه', 'short' => 'چ', 'is_weekend' => false],
        5 => ['key' => 'thursday', 'name' => 'پنجشنبه', 'short' => 'پ', 'is_weekend' => true],
        6 => ['key' => 'friday', 'name' => 'جمعه', 'short' => 'ج', 'is_weekend' => true],
    ];

    public const SHIFTS = [
        'any' => 'هر زمانی / منعطف',
        'morning' => 'شیفت صبح (۰۸:۰۰ الی ۱۲:۰۰)',
        'noon' => 'شیفت ظهر (۱۲:۰۰ الی ۱۶:۰۰)',
        'evening' => 'شیفت عصر (۱۶:۰۰ الی ۲۰:۰۰)',
        'night' => 'شیفت شب (۲۰:۰۰ به بعد)',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Checks if the client is currently in a temporary absence period (e.g. travel/surgery).
     */
    public function isInTemporaryAbsence(): bool
    {
        if (!$this->temp_absence_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->temp_absence_start && $this->temp_absence_end) {
            return $today->betweenIncluded($this->temp_absence_start, $this->temp_absence_end);
        }

        if ($this->temp_absence_start && !$this->temp_absence_end) {
            return $today->greaterThanOrEqualTo($this->temp_absence_start);
        }

        if (!$this->temp_absence_start && $this->temp_absence_end) {
            return $today->lessThanOrEqualTo($this->temp_absence_end);
        }

        return true;
    }

    /**
     * Jalali formatted absence range.
     */
    public function getJalaliAbsenceRangeAttribute(): ?string
    {
        if (!$this->temp_absence_start && !$this->temp_absence_end) {
            return null;
        }

        $start = $this->temp_absence_start ? Jalalian::fromCarbon($this->temp_absence_start)->format('Y/m/d') : 'نامشخص';
        $end = $this->temp_absence_end ? Jalalian::fromCarbon($this->temp_absence_end)->format('Y/m/d') : 'تا اطلاع ثانوی';

        return "از {$start} تا {$end}";
    }

    /**
     * Generates a readable summary text for the client availability in Persian.
     */
    public function getSummaryTextAttribute(): string
    {
        if ($this->isInTemporaryAbsence()) {
            $reason = $this->temp_absence_reason ? " ({$this->temp_absence_reason})" : '';
            return "⛔ عدم حضور موقت بیمار{$reason} - {$this->jalali_absence_range}";
        }

        $schedule = $this->weekly_schedule ?? self::getDefaultWeekSchedule();

        $freeDays = [];
        $customDays = [];
        $busyDays = [];

        foreach (self::DAYS as $idx => $meta) {
            $dayData = $schedule[$idx] ?? ['status' => 'all_day_free', 'slots' => []];
            $status = $dayData['status'] ?? 'custom';

            if ($status === 'all_day_free') {
                $freeDays[] = $meta['name'];
            } elseif ($status === 'all_day_busy') {
                $busyDays[] = $meta['name'];
            } else {
                $slots = $dayData['slots'] ?? [];
                if (!empty($slots)) {
                    $times = collect($slots)->map(fn($s) => ($s['start'] ?? '') . ' تا ' . ($s['end'] ?? ''))->implode('، ');
                    $customDays[] = "{$meta['name']} ({$times})";
                }
            }
        }

        $parts = [];

        if (!empty($this->preferred_shift) && $this->preferred_shift !== 'any' && isset(self::SHIFTS[$this->preferred_shift])) {
            $parts[] = self::SHIFTS[$this->preferred_shift];
        }

        if (count($freeDays) === 6 && in_array('جمعه', $busyDays)) {
            $parts[] = 'شنبه تا پنجشنبه آزاد کامل (جمعه تعطیل)';
        } elseif (count($freeDays) === 7) {
            $parts[] = 'تمام ایام هفته بدون محدودیت آزاد';
        } elseif (count($busyDays) === 7) {
            $parts[] = 'در حال حاضر در هیچ روزی امکان پذیرش نوبت ندارد';
        } else {
            if (!empty($freeDays)) {
                $parts[] = 'آزاد کامل: ' . implode('، ', $freeDays);
            }
            if (!empty($customDays)) {
                $parts[] = implode(' | ', $customDays);
            }
            if (!empty($busyDays) && count($busyDays) <= 3) {
                $parts[] = 'عدم امکان: ' . implode('، ', $busyDays);
            }
        }

        return !empty($parts) ? implode(' — ', $parts) : 'برنامه هفتگی تنظیم نشده است';
    }

    /**
     * Generates a standard blank/free 7-day schedule (all days free except Friday).
     */
    public static function getDefaultWeekSchedule(): array
    {
        $schedule = [];
        foreach (self::DAYS as $idx => $meta) {
            $isFriday = ($idx === 6);
            $schedule[$idx] = [
                'day_name' => $meta['name'],
                'status' => $isFriday ? 'all_day_busy' : 'all_day_free',
                'slots' => [],
                'note' => $isFriday ? 'تعطیل هفتگی' : '',
            ];
        }
        return $schedule;
    }

    /**
     * Returns pre-defined schedules based on presets.
     */
    public static function getPresetSchedule(string $preset): array
    {
        $schedule = [];

        switch ($preset) {
            case 'employee':
                // کارمند اداری: شنبه تا چهارشنبه عصر ۱۶:۰۰ تا ۲۱:۰۰، پنجشنبه ۰۹:۰۰ تا ۱۴:۰۰، جمعه تعطیل
                foreach (self::DAYS as $idx => $meta) {
                    if ($idx >= 0 && $idx <= 4) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '16:00', 'end' => '21:00', 'type' => 'preferred', 'note' => 'بعد از ساعت اداری']
                            ],
                            'note' => 'شاغل اداری در طول روز',
                        ];
                    } elseif ($idx === 5) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '09:00', 'end' => '14:00', 'type' => 'available', 'note' => 'نیمه‌وقت صبح']
                            ],
                            'note' => '',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'تعطیل هفتگی',
                        ];
                    }
                }
                break;

            case 'student':
                // دانشجو: روزهای زوج عصر + پنجشنبه صبح
                foreach (self::DAYS as $idx => $meta) {
                    if (in_array($idx, [0, 2, 4])) { // شنبه، دوشنبه، چهارشنبه
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '15:30', 'end' => '20:30', 'type' => 'preferred', 'note' => 'بعد از کلاس‌ها']
                            ],
                            'note' => '',
                        ];
                    } elseif ($idx === 5) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '10:00', 'end' => '15:00', 'type' => 'available', 'note' => '']
                            ],
                            'note' => '',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'دانشگاه و مشغله تحصیلی',
                        ];
                    }
                }
                break;

            case 'morning_only':
                // فقط شیفت صبح: شنبه تا پنجشنبه ۰۸:۳۰ تا ۱۳:۰۰
                foreach (self::DAYS as $idx => $meta) {
                    if ($idx <= 5) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '08:30', 'end' => '13:00', 'type' => 'preferred', 'note' => 'فقط صبح']
                            ],
                            'note' => '',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'تعطیل',
                        ];
                    }
                }
                break;

            case 'evening_only':
                // فقط شیفت عصر: شنبه تا چهارشنبه ۱۶:۰۰ تا ۲۱:۰۰
                foreach (self::DAYS as $idx => $meta) {
                    if ($idx <= 4) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '16:00', 'end' => '21:00', 'type' => 'preferred', 'note' => 'فقط شیفت عصر']
                            ],
                            'note' => '',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'آخر هفته تعطیل',
                        ];
                    }
                }
                break;

            case 'all_free':
                // تمام‌وقت آزاد: بدون محدودیت
                foreach (self::DAYS as $idx => $meta) {
                    $schedule[$idx] = [
                        'day_name' => $meta['name'],
                        'status' => 'all_day_free',
                        'slots' => [],
                        'note' => 'بدون محدودیت ساعتی و شغلی',
                    ];
                }
                break;

            case 'even_days':
                // فقط روزهای زوج (شنبه، دوشنبه، چهارشنبه)
                foreach (self::DAYS as $idx => $meta) {
                    if (in_array($idx, [0, 2, 4])) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '09:00', 'end' => '13:00', 'type' => 'available', 'note' => 'صبح'],
                                ['start' => '16:00', 'end' => '20:30', 'type' => 'preferred', 'note' => 'عصر'],
                            ],
                            'note' => 'فقط روزهای زوج',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'امکان حضور ندارد',
                        ];
                    }
                }
                break;

            case 'odd_days':
                // فقط روزهای فرد (یکشنبه، سه‌شنبه، پنجشنبه)
                foreach (self::DAYS as $idx => $meta) {
                    if (in_array($idx, [1, 3, 5])) {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'custom',
                            'slots' => [
                                ['start' => '09:00', 'end' => '13:00', 'type' => 'available', 'note' => 'صبح'],
                                ['start' => '16:00', 'end' => '20:30', 'type' => 'preferred', 'note' => 'عصر'],
                            ],
                            'note' => 'فقط روزهای فرد',
                        ];
                    } else {
                        $schedule[$idx] = [
                            'day_name' => $meta['name'],
                            'status' => 'all_day_busy',
                            'slots' => [],
                            'note' => 'امکان حضور ندارد',
                        ];
                    }
                }
                break;

            default:
                $schedule = self::getDefaultWeekSchedule();
                break;
        }

        return $schedule;
    }
}
