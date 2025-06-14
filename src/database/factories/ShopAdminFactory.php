<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\ShopAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShopAdmin>
 */
class ShopAdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShopAdmin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => fake()->randomElement(['root', 'manager', 'staff']),
            'is_active' => true,
            'phone' => fake()->phoneNumber(),
            'notes' => fake()->optional()->sentence(),
            'last_login_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * ルート管理者の状態
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'root',
        ]);
    }

    /**
     * マネージャーの状態
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }

    /**
     * スタッフの状態
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'staff',
        ]);
    }

    /**
     * 非アクティブな状態
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * メール未認証の状態
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
