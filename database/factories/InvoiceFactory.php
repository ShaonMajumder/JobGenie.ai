<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'status' => 'paid',
            'number' => 'INV-'.$this->faker->unique()->numerify('####'),
            'amount_subscription' => 29.0,
            'amount_ai_usage' => 0.0,
            'amount_total' => 29.0,
            'currency' => 'USD',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'paid_at' => now(),
        ];
    }
}
