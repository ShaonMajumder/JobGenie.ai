<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'currency',
        'billing_interval',
        'ai_included_tokens_monthly',
        'billing_mode',
        'allow_overage',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'ai_included_tokens_monthly' => 'integer',
        'allow_overage' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isPrepaid(): bool
    {
        return $this->billing_mode === 'prepaid';
    }

    public function isPostpaid(): bool
    {
        return $this->billing_mode === 'postpaid';
    }

    public function includesTokens(): bool
    {
        return $this->ai_included_tokens_monthly > 0;
    }

    public function allowsOverage(): bool
    {
        return (bool) $this->allow_overage;
    }
}
