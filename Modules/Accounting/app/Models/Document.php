<?php

namespace Modules\Accounting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Entities\Client; // Import Client model

class Document extends Model
{
    protected $table = 'accounting_documents';

    protected $fillable = [
        'bank_id',
        'category_id',
        'client_id',
        'type',
        'amount',
        'document_date',
        'description',
        'payment_method', // Added
        'reference_number', // Already there in DB maybe, but let's be sure
        'attachment', // Added
        'documentable_id',
        'documentable_type',
    ];

    protected $casts = [
        'document_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the bank that owns the document.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    /**
     * Get the category that owns the document.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the client associated with the document.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the parent documentable model.
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
