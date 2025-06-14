<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Shop;
use App\Models\ShopAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => \Illuminate\Support\Str::ulid(),
            'shop_id' => Shop::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'conditions' => $this->faker->sentence(),
            'notes' => $this->faker->sentence(),
            'image_url' => $this->faker->imageUrl(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
