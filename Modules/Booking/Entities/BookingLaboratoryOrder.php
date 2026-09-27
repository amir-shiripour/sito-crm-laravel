<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Entities\Client;
use Modules\Booking\App\Models\TreatmentPlan;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class BookingLaboratoryOrder extends Model
{
    use SoftDeletes;

    public const LAB_TYPE_EXTERNAL = 'external';
    public const LAB_TYPE_IN_HOUSE = 'in_house';

    public const CATEGORY_UNITS_1_2    = 'units_1_2';
    public const CATEGORY_UNITS_3_6    = 'units_3_6';
    public const CATEGORY_UNITS_7_11   = 'units_7_11';
    public const CATEGORY_FULL_JAW     = 'full_jaw';
    public const CATEGORY_NODENTIA     = 'nodentia';
    public const CATEGORY_IN_HOUSE_DIGITAL = 'in_house_digital';
    public const CATEGORY_CUSTOM       = 'custom';

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_OVERDUE     = 'overdue';
    public const STATUS_RECEIVED    = 'received';
    public const STATUS_DELIVERED   = 'delivered';
    public const STATUS_CANCELED    = 'canceled';

    public const FULL_JAW_BASE_RIM          = 'base_rim';
    public const FULL_JAW_PMMA              = 'pmma';
    public const FULL_JAW_PORCELAIN_TRY_IN  = 'porcelain_try_in';
    public const FULL_JAW_FINAL_GLAZE       = 'final_glaze';

    protected $table = 'booking_laboratory_orders';

    protected $fillable = [
        'order_number',
        'client_id',
        'patient_name',
        'patient_file_number',
        'appointment_id',
        'treatment_plan_id',
        'doctor_id',
        'technician_id',
        'lab_type',
        'lab_partner_name',
        'category_type',
        'full_jaw_phase',
        'has_pmma',
        'units_count',
        'teeth_numbers',
        'status',
        'sent_at',
        'expected_delivery_at',
        'received_at',
        'notes',
        'parent_order_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'has_pmma'             => 'boolean',
        'units_count'          => 'integer',
        'sent_at'              => 'datetime',
        'expected_delivery_at' => 'datetime',
        'received_at'          => 'datetime',
    ];

    /* ---------------- Relationships ---------------- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_order_id');
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(self::class, 'parent_order_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(BookingLaboratoryStage::class, 'order_id')->orderBy('sort_order');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(BookingLaboratoryDailyLog::class, 'order_id')->orderByDesc('log_date');
    }

    /* ---------------- Status & Overdue Logic ---------------- */

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED || !is_null($this->received_at);
    }

    public function isOverdue(): bool
    {
        if ($this->isReceived() || $this->status === self::STATUS_DELIVERED || $this->status === self::STATUS_CANCELED) {
            return false;
        }

        // Check if any stage's due_at has passed and is not completed
        foreach ($this->stages as $stage) {
            if (!$stage->is_completed && $stage->due_at && $stage->due_at->isPast()) {
                return true;
            }
        }

        if ($this->expected_delivery_at && $this->expected_delivery_at->isPast()) {
            return true;
        }

        return false;
    }

    public function getCategoryLabelAttribute(): string
    {
        $dynamicName = BookingSetting::getLaboratoryCategoryName($this->lab_type, $this->category_type);
        if ($dynamicName) {
            return $dynamicName;
        }

        return match ($this->category_type) {
            self::CATEGORY_UNITS_1_2        => '۱ تا ۲ واحدی',
            self::CATEGORY_UNITS_3_6        => '۳ تا ۶ واحدی',
            self::CATEGORY_UNITS_7_11       => $this->has_pmma ? '۷ تا ۱۱ واحدی (با PMMA)' : '۷ تا ۱۱ واحدی',
            self::CATEGORY_FULL_JAW         => 'فول فک (' . ($this->full_jaw_phase_label ?? 'چند مرحله‌ای') . ')',
            self::CATEGORY_NODENTIA         => 'متد نودنشیا',
            self::CATEGORY_IN_HOUSE_DIGITAL => 'دیجیتال داخل مطب',
            default                         => 'سفارشی',
        };
    }

    public function getFullJawPhaseLabelAttribute(): ?string
    {
        return match ($this->full_jaw_phase) {
            self::FULL_JAW_BASE_RIM         => 'بیس ریم',
            self::FULL_JAW_PMMA             => 'پی‌ام‌ای (PMMA)',
            self::FULL_JAW_PORCELAIN_TRY_IN => 'تست پرسلن',
            self::FULL_JAW_FINAL_GLAZE      => 'ونیر / گلیز نهایی',
            default                         => null,
        };
    }

    public function getSentAtJalaliAttribute(): string
    {
        if (!$this->sent_at) return '';
        try {
            return Jalalian::fromCarbon($this->sent_at)->format('Y/m/d');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getExpectedDeliveryAtJalaliAttribute(): ?string
    {
        if (!$this->expected_delivery_at) return null;
        try {
            return Jalalian::fromCarbon($this->expected_delivery_at)->format('Y/m/d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getReceivedAtJalaliAttribute(): ?string
    {
        if (!$this->received_at) return null;
        try {
            return Jalalian::fromCarbon($this->received_at)->format('Y/m/d H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getCreatedAtJalaliAttribute(): string
    {
        if (!$this->created_at) return '';
        try {
            return Jalalian::fromCarbon($this->created_at)->format('Y/m/d H:i');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getGroupedTeethAttribute(): array
    {
        if (empty($this->teeth_numbers)) {
            return ['UR' => [], 'UL' => [], 'LR' => [], 'LL' => []];
        }

        $bookingSettings = BookingSetting::current();
        $numberingSystem = $bookingSettings->cure_tooth_numbering_system ?? 'universal';

        $palmerMap = [
            1 => ['num' => 7, 'pos' => 'UR'], 2 => ['num' => 6, 'pos' => 'UR'], 3 => ['num' => 5, 'pos' => 'UR'], 4 => ['num' => 4, 'pos' => 'UR'],
            5 => ['num' => 3, 'pos' => 'UR'], 6 => ['num' => 2, 'pos' => 'UR'], 7 => ['num' => 1, 'pos' => 'UR'],
            8 => ['num' => 1, 'pos' => 'UL'], 9 => ['num' => 2, 'pos' => 'UL'], 10 => ['num' => 3, 'pos' => 'UL'], 11 => ['num' => 4, 'pos' => 'UL'],
            12 => ['num' => 5, 'pos' => 'UL'], 13 => ['num' => 6, 'pos' => 'UL'], 14 => ['num' => 7, 'pos' => 'UL'],
            15 => ['num' => 7, 'pos' => 'LR'], 16 => ['num' => 6, 'pos' => 'LR'], 17 => ['num' => 5, 'pos' => 'LR'], 18 => ['num' => 4, 'pos' => 'LR'],
            19 => ['num' => 3, 'pos' => 'LR'], 20 => ['num' => 2, 'pos' => 'LR'], 21 => ['num' => 1, 'pos' => 'LR'],
            22 => ['num' => 1, 'pos' => 'LL'], 23 => ['num' => 2, 'pos' => 'LL'], 24 => ['num' => 3, 'pos' => 'LL'], 25 => ['num' => 4, 'pos' => 'LL'],
            26 => ['num' => 5, 'pos' => 'LL'], 27 => ['num' => 6, 'pos' => 'LL'], 28 => ['num' => 7, 'pos' => 'LL']
        ];

        $fdiMap = [
            1 => ['num' => 17, 'pos' => 'UR'], 2 => ['num' => 16, 'pos' => 'UR'], 3 => ['num' => 15, 'pos' => 'UR'], 4 => ['num' => 14, 'pos' => 'UR'],
            5 => ['num' => 13, 'pos' => 'UR'], 6 => ['num' => 12, 'pos' => 'UR'], 7 => ['num' => 11, 'pos' => 'UR'],
            8 => ['num' => 21, 'pos' => 'UL'], 9 => ['num' => 22, 'pos' => 'UL'], 10 => ['num' => 23, 'pos' => 'UL'], 11 => ['num' => 24, 'pos' => 'UL'],
            12 => ['num' => 25, 'pos' => 'UL'], 13 => ['num' => 26, 'pos' => 'UL'], 14 => ['num' => 27, 'pos' => 'UL'],
            15 => ['num' => 47, 'pos' => 'LR'], 16 => ['num' => 46, 'pos' => 'LR'], 17 => ['num' => 45, 'pos' => 'LR'], 18 => ['num' => 44, 'pos' => 'LR'],
            19 => ['num' => 43, 'pos' => 'LR'], 20 => ['num' => 42, 'pos' => 'LR'], 21 => ['num' => 41, 'pos' => 'LR'],
            22 => ['num' => 31, 'pos' => 'LL'], 23 => ['num' => 32, 'pos' => 'LL'], 24 => ['num' => 33, 'pos' => 'LL'], 25 => ['num' => 34, 'pos' => 'LL'],
            26 => ['num' => 35, 'pos' => 'LL'], 27 => ['num' => 36, 'pos' => 'LL'], 28 => ['num' => 37, 'pos' => 'LL']
        ];

        $toothMap = ($numberingSystem === 'fdi') ? $fdiMap : $palmerMap;

        $fdiToPos = [];
        foreach ($fdiMap as $id => $val) {
            $fdiToPos[$val['num']] = [
                'num' => ($numberingSystem === 'fdi' ? $val['num'] : $palmerMap[$id]['num']),
                'pos' => $val['pos']
            ];
        }

        $rawTeeth = array_filter(array_map('trim', preg_split('/[,\s\-]+/', (string) $this->teeth_numbers)), fn($v) => $v !== '');
        
        $grouped = ['UR' => [], 'UL' => [], 'LR' => [], 'LL' => []];

        foreach ($rawTeeth as $t) {
            $num = (int)$t;
            if ($num <= 0) continue;

            if (isset($toothMap[$num])) {
                $info = $toothMap[$num];
                $grouped[$info['pos']][] = ['num' => $info['num'], 'id' => $num];
            } elseif (isset($fdiToPos[$num])) {
                $info = $fdiToPos[$num];
                $grouped[$info['pos']][] = ['num' => $info['num'], 'id' => $num];
            } else {
                $pos = ($num <= 7) ? 'UR' : 'UL';
                $grouped[$pos][] = ['num' => $num, 'id' => $num];
            }
        }

        // Sort UR/LR descending so they grow outwards from midline (left to right)
        usort($grouped['UR'], fn($a, $b) => $b['num'] <=> $a['num']);
        usort($grouped['LR'], fn($a, $b) => $b['num'] <=> $a['num']);

        // Sort UL/LL ascending so they grow outwards from midline (right to left)
        usort($grouped['UL'], fn($a, $b) => $a['num'] <=> $b['num']);
        usort($grouped['LL'], fn($a, $b) => $a['num'] <=> $b['num']);

        return $grouped;
    }

    public function getHasTeethAttribute(): bool
    {
        $grouped = $this->grouped_teeth;
        return !empty($grouped['UR']) || !empty($grouped['UL']) || !empty($grouped['LR']) || !empty($grouped['LL']);
    }

    public function getCurrentStageAttribute(): ?BookingLaboratoryStage
    {
        return $this->stages->firstWhere('is_completed', false);
    }

    public function getNextStageAttribute(): ?BookingLaboratoryStage
    {
        $uncompleted = $this->stages->where('is_completed', false)->values();
        return $uncompleted->get(1);
    }

    public function getCompletedStagesCountAttribute(): int
    {
        return $this->stages->where('is_completed', true)->count();
    }

    public function getTotalStagesCountAttribute(): int
    {
        return $this->stages->count();
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->total_stages_count;
        if ($total === 0) {
            return $this->isReceived() ? 100 : 0;
        }
        return (int) round(($this->completed_stages_count / $total) * 100);
    }
}
