<?php

namespace Database\Factories;

use App\Models\AiUsageRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiUsageRecord>
 */
class AiUsageRecordFactory extends Factory
{
    protected $model = AiUsageRecord::class;

    public function definition(): array
    {
        $input = 1000;
        $output = 500;

        return [
            'user_id' => User::factory(),
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'input_tokens' => $input,
            'output_tokens' => $output,
            'total_tokens' => $input + $output,
            'cost_input' => 0.15,
            'cost_output' => 0.07,
            'cost_total' => 0.22,
            'currency' => 'USD',
        ];
    }
}
