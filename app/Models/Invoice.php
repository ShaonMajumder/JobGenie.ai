<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'killbill_invoice_id',
        'stripe_payment_intent_id',
        'number',
        'status',
        'amount_subscription',
        'amount_ai_usage',
        'amount_total',
        'currency',
        'period_start',
        'period_end',
        'due_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount_subscription' => 'decimal:2',
        'amount_ai_usage' => 'decimal:2',
        'amount_total' => 'decimal:2',
        'metadata' => 'array',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function aiUsageRecords(): HasMany
    {
        return $this->hasMany(AiUsageRecord::class, 'billed_invoice_id');
    }
}
