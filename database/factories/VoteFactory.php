<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'voteable_id' => 1, // Will be overridden in seeder
            'voteable_type' => \App\Models\Protocol::class, // Will be overridden in seeder
            'value' => fake()->randomElement([-1, 1]),
        ];
    }
}
