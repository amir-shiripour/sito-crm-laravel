<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;

class Proforma extends Model
{
    use HasFactory;

    protected $table = 'accounting_proformas';

    protected $fillable = [
        'client_id',
        'proforma_number',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
        'total_amount' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProformaItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the next proforma number based on settings.
     *
     * @return string
     */
    public static function getNextProformaNumber(): string
    {
        $prefix = AccountingSetting::getValue('proforma.numbering_prefix', 'PF');
        $separator = AccountingSetting::getValue('proforma.numbering_separator', '-');
        $length = (int) AccountingSetting::getValue('proforma.numbering_length', 4);
        $includeYear = (bool) AccountingSetting::getValue('proforma.numbering_include_year', true);

        $year = $includeYear ? (new Jalalian(now()->year, now()->month, now()->day))->format('y') : null;

        $query = self::query();
        $searchPattern = $prefix;
        if ($year) {
            $searchPattern .= $separator . $year;
        }
        $query->where('proforma_number', 'like', "{$searchPattern}{$separator}%");

        $latestProforma = $query->orderBy('proforma_number', 'desc')->first();

        $newNumericPart = 1;
        if ($latestProforma) {
            $parts = explode($separator, $latestProforma->proforma_number);
            $lastNumericPart = (int) end($parts);
            $newNumericPart = $lastNumericPart + 1;
        }

        $paddedNumber = str_pad($newNumericPart, $length, '0', STR_PAD_LEFT);

        $finalParts = [$prefix];
        if ($year) {
            $finalParts[] = $year;
        }
        $finalParts[] = $paddedNumber;

        return implode($separator, $finalParts);
    }
}
