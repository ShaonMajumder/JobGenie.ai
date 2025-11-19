<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'status' => 'active',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
            'renews_at' => now()->endOfMonth(),
        ];
    }
}
