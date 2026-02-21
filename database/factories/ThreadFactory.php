<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thread>
 */
class ThreadFactory extends Factory
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
            'title' => fake()->sentence(5),
            'body' => fake()->paragraphs(2, true),
            'tags' => fake()->randomElements(['discussion', 'question', 'tip', 'experience', 'support', 'resource'], rand(0, 3)),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
