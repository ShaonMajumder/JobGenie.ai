<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['Free', 'Pro', 'Team']).' '.$this->faker->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 999)),
            'description' => $this->faker->sentence(),
            'price_monthly' => $this->faker->randomFloat(2, 0, 199),
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'ai_included_tokens_monthly' => $this->faker->randomElement([0, 20000, 200000]),
            'billing_mode' => 'postpaid',
            'allow_overage' => true,
            'is_active' => true,
        ];
    }

    public function prepaid(): self
    {
        return $this->state(fn () => [
            'billing_mode' => 'prepaid',
            'allow_overage' => false,
        ]);
    }
}
