<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The associated model.
     */
    protected $model = User::class;

    /**
     * Shared password instance.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Custom fields consistent with your User model
            'native_currency' => 'USD',
            'recent_salary' => fake()->randomFloat(2, 30000, 200000),
            'resume_text' => fake()->paragraphs(3, true),
            'timezone' => fake()->timezone(),
            'is_admin' => false,
        ];
    }

    /**
     * Mark the user as unverified.
     */
    public function unverified(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }

    /**
     * Mark user as admin.
     */
    public function admin(): static
    {
        return $this->state([
            'is_admin' => true,
        ]);
    }
}
