<?php

namespace Modules\Settings\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class GapGPTLog extends Model
{
    protected $table = 'gapgpt_logs';

    protected $fillable = [
        'user_id',
        'model',
        'prompt',
        'response',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'duration_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'prompt' => 'array', // اگر درخواست به صورت JSON ذخیره شود
        'response' => 'array', // اگر پاسخ به صورت JSON ذخیره شود
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
