<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->jobTitle(),
            'company_name' => $this->faker->company(),
            'company_website' => $this->faker->url(),
            'location' => $this->faker->city(),
            'work_type' => 'remote',
            'job_type' => 'full_time',
            'job_description' => $this->faker->paragraph(),
            'baseline_salary' => 80000,
            'baseline_currency' => 'USD',
            'status' => 'not_applied',
        ];
    }
}
