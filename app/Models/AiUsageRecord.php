<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'job_session_id',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost_input',
        'cost_output',
        'cost_total',
        'currency',
        'billed_invoice_id',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost_input' => 'decimal:6',
        'cost_output' => 'decimal:6',
        'cost_total' => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function jobSession(): BelongsTo
    {
        return $this->belongsTo(JobSession::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'billed_invoice_id');
    }
}
