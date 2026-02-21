<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Protocol>
 */
class ProtocolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'tags' => fake()->randomElements(['research', 'clinical', 'protocol', 'study', 'trial', 'medical', 'health', 'science'], rand(2, 4)),
            'author' => fake()->name(),
            'average_rating' => 0,
        ];
    }
}
