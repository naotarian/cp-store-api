<?php

namespace Database\Factories;

use App\Models\CouponIssue;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\ShopAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CouponIssue>
 */
class CouponIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDateTime = $this->faker->dateTimeBetween('now', '+1 hour');
        $endDateTime = $this->faker->dateTimeBetween($startDateTime, '+2 hours');

        return [
            'id' => \Illuminate\Support\Str::ulid(),
            'shop_id' => Shop::factory(),
            'coupon_id' => Coupon::factory(),
            'issue_type' => $this->faker->randomElement(['manual', 'batch_generated']),
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'status' => 'active',
            'is_active' => true,
            'max_acquisitions' => $this->faker->numberBetween(10, 1000),
            'current_acquisitions' => 0,
            'issued_by' => ShopAdmin::factory(),
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the issue is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'stopped',
        ]);
    }

    /**
     * Indicate that the issue is batch generated.
     */
    public function batchGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'issue_type' => 'batch_generated',
        ]);
    }

    /**
     * Indicate that the issue is manual.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'issue_type' => 'manual',
        ]);
    }
}
