<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'protocol_id' => \App\Models\Protocol::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'feedback' => fake()->optional()->paragraph(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
