<?php

namespace Database\Factories;

use App\Models\CouponSchedule;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\ShopAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CouponSchedule>
 */
class CouponScheduleFactory extends Factory
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
            'coupon_id' => Coupon::factory(),
            'created_by' => ShopAdmin::factory(),
            'schedule_name' => $this->faker->sentence(3),
            'day_type' => $this->faker->randomElement(['daily', 'weekdays', 'weekends', 'custom']),
            'custom_days' => null,
            'start_time' => '10:00',
            'end_time' => '18:00',
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'is_active' => true,
            'last_batch_processed_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the schedule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the schedule is for weekdays only.
     */
    public function weekdays(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_type' => 'weekdays',
        ]);
    }

    /**
     * Indicate that the schedule is for weekends only.
     */
    public function weekends(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_type' => 'weekends',
        ]);
    }

    /**
     * Indicate that the schedule has custom days.
     */
    public function customDays(array $days): static
    {
        return $this->state(fn (array $attributes) => [
            'day_type' => 'custom',
            'custom_days' => json_encode($days),
        ]);
    }
}
