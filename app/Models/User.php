<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'native_currency',
        'recent_salary',
        'resume_text',
        'timezone',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'recent_salary' => 'decimal:2',
            'is_admin' => 'boolean',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function jobSessions(): HasMany
    {
        return $this->hasMany(JobSession::class);
    }

    public function jobConversations(): HasMany
    {
        return $this->hasMany(JobConversation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function aiUsageRecords(): HasMany
    {
        return $this->hasMany(AiUsageRecord::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', Subscription::ACTIVE_STATUSES)
            ->latest('current_period_start')
            ->first();
    }

    public function currentPlan(): ?SubscriptionPlan
    {
        return $this->activeSubscription()?->plan;
    }

    public function hasActiveSubscription(): bool
    {
        return (bool) $this->activeSubscription();
    }

    public function remainingTokensForCurrentPeriod(): int
    {
        $subscription = $this->activeSubscription();
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan || ! $plan->includesTokens()) {
            return 0;
        }

        $periodStart = $subscription->current_period_start ?? now()->startOfMonth();
        $periodEnd = $subscription->current_period_end ?? now()->endOfMonth();

        $used = $this->aiUsageRecords()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_tokens');

        return max(0, (int) $plan->ai_included_tokens_monthly - (int) $used);
    }
}
