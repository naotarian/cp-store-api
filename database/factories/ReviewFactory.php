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
            'shop_id' => \App\Models\Shop::factory(),
            'user_id' => \App\Models\User::factory(),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'comment' => $this->faker->optional(0.8)->paragraph(),
        ];
    }
}
